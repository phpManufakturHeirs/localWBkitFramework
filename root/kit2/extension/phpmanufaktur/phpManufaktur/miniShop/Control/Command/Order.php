<?php

/**
 * miniShop
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de/miniShop
 * @copyright 2014 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\miniShop\Control\Command;

use Silex\Application;
use phpManufaktur\Contact\Control\Pattern\Form\Contact as ContactForm;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\JsonResponse;
use phpManufaktur\Contact\Data\Contact\CategoryType;
use phpManufaktur\miniShop\Control\Payment\AdvancePayment;
use phpManufaktur\miniShop\Control\Payment\OnAccount;
use phpManufaktur\miniShop\Control\Payment\PayPal;
use phpManufaktur\miniShop\Data\Shop\Order as DataOrder;

class Order extends CommandBasic
{
    protected $Basket = null;
    protected $dataOrder = null;

    protected function initParameters(Application $app, $parameter_id=-1)
    {
        parent::initParameters($app, $parameter_id);

        $this->Basket = new Basket($app);
        $this->dataOrder = new DataOrder($app);
    }

    /**
     * Get the form to select the contact type for the order
     *
     * @return FormFactory
     */
    protected function getContactTypeForm()
    {
        return $this->app['form.factory']->createBuilder('form')
            ->add('form_action', 'hidden', array(
                'data' => 'type'
            ))
            ->add('order_for', 'choice', array(
                'choices' => array(
                    'PERSON' => $this->app['translator']->trans('a private person'),
                    'COMPANY' => $this->app['translator']->trans('a company or a organization')
                ),
                'expanded' => true,
                'data' => isset(self::$config['contact']['field']['default_value']['contact_type']) ? self::$config['contact']['field']['default_value']['contact_type'] : 'PERSON'
            ))
            ->getForm();
    }

    /**
     * Get the form to create / edit a contact
     *
     * @param array $data
     * @param array $order
     * @throws \Exception
     * @return Ambigous <\phpManufaktur\Contact\Control\Pattern\Form\mixed, boolean>
     */
    protected function getContactForm($data=array(), $order)
    {
        // create a contact form
        $ContactForm = new ContactForm($this->app);

        // set default values for the contact form
        foreach (self::$config['contact']['field']['default_value'] as $key => $value) {
            if (!isset($data[$key])) {
                $data[$key] = $value;
            }
        }

        $field = self::$config['contact']['field'];

        // get predefined special fields
        $special = isset($field['special']) ? $field['special'] : array();

        $special[] = array(
            'enabled' => true,
            'name' => 'form_action',
            'type' => 'hidden',
            'data' => 'contact'
        );

        $payments = array();
        foreach (explode(',', $order['base']['payment_methods']) as $payment) {
            if (!empty($payment)) {
                $payments[$payment] = $this->app['utils']->humanize($payment);
            }
        }
        $special[] = array(
            'enabled' => true,
            'name' => 'payment_method',
            'type' => 'choice',
            'choices' => $payments,
            'required' => true,
            'empty_value' => '- please select -',
            'read_only' => false,
            'expanded' => false,
            'multiple' => false,
            'preferred_choices' => array()
        );

        if (!empty($order['base']['terms_conditions_link'])) {
            $special[] = array(
                'enabled' => true,
                'name' => 'terms_and_conditions',
                'type' => 'checkbox',
                'required' => true,
                'label' => $this->app['translator']->trans('I have read and accept the <a href="%url%" target="_blank">terms and conditions</a>',
                    array('%url%' => CMS_URL.$order['base']['terms_conditions_link']))
            );
        }

        $field['special'] = $special;

        if (false === ($form = $ContactForm->getFormContact($data, $field))) {
            throw new \Exception($this->getMessage());
        }
        return $form;
    }

    /**
     * Controller to check the Address type and show the contact form for the
     * next step in order
     *
     * @throws \Exception
     * @return JsonResponse
     */
    public function ControllerContactTypeCheck(Application $app)
    {
        $this->initParameters($app);

        $form = $this->getContactTypeForm();
        $form->bind($this->app['request']);

        if ($form->isValid()) {
            // the form is valid
            $data = $form->getData();

            // get the current order from the basket
            $order = $this->Basket->CreateOrderDataFromBasket();

            $contact_form = $this->getContactForm(array(
                'contact_type' => $data['order_for']), $order);

            return $this->app['twig']->render($this->app['utils']->getTemplateFile(
                '@phpManufaktur/miniShop/Template', 'command/basket/order.twig',
                $this->getPreferredTemplateStyle()),
                array(
                    'basic' => $this->getBasicSettings(),
                    'alert' => $this->getAlert(),
                    'config' => self::$config,
                    'permalink_base_url' => CMS_URL.self::$config['permanentlink']['directory'],
                    'form' => $contact_form->createView(),
                    'order' => $order,
                    'shop_url' => CMS_URL.$order['base']['target_page_link']
                ));
        }
        else {
            // general error (timeout, CSFR ...)
            $this->setAlert('The form is not valid, please check your input and try again!', array(),
                self::ALERT_TYPE_DANGER, true, array('form_errors' => $form->getErrorsAsString(),
                    'method' => __METHOD__, 'line' => __LINE__));
            return $this->ControllerContactType($app);
        }
    }

    /**
     * Get the rendered contact dialog
     *
     * @param array $data
     * @param array $order
     */
    protected function getContactDialog($data, $order)
    {
        $form = $this->getContactForm($data, $order);
        return $this->app['twig']->render($this->app['utils']->getTemplateFile(
            '@phpManufaktur/miniShop/Template', 'command/basket/order.twig',
            $this->getPreferredTemplateStyle()),
            array(
                'basic' => $this->getBasicSettings(),
                'alert' => $this->getAlert(),
                'config' => self::$config,
                'permalink_base_url' => CMS_URL.self::$config['permanentlink']['directory'],
                'form' => $form->createView(),
                'order' => $order,
                'shop_url' => CMS_URL.$order['base']['target_page_link']
            ));
    }

    /**
     * Controller to check the submitted contact. Insert or update the contact
     * record and got to the next step: select the payment method
     *
     * @return JsonResponse
     */
    //protected function CheckContact()
    public function ControllerContactCheck(Application $app)
    {
        $this->initParameters($app);

        // get the current order from the basket
        $order = $this->Basket->CreateOrderDataFromBasket();

        $request = $app['request']->get('form');
        $data = isset($request['contact_type']) ? $data = array('contact_type' => $request['contact_type']) : array();

        $form = $this->getContactForm($data, $order);
        $form->bind($this->app['request']);

        if ($form->isValid()) {
            // the form is valid
            $data = $form->getData();

            $ContactForm = new ContactForm($this->app);
            $validation_errors = array();
            if (false === ($contact = $ContactForm->checkData($data, self::$config['contact']['field'], true, $validation_errors))) {
                // the contact pattern will set an alert - go back to the first dialog
                return $this->ControllerContactType($app);
            }

            $contact_id = $contact['contact']['contact_id'];

            if (!empty($validation_errors)) {
                // one or more values have not passed the validation
                return $this->getContactDialog($data, $order);
            }

            if (($contact_id > 0) && ($contact['contact']['contact_status'] === 'LOCKED')) {
                // this contact record is LOCKED!
                $this->setAlert('Sorry, but we have a problem. Please contact the webmaster and tell him to check the status of the email address %email%.',
                    array('%email%' => $data['communication_email']), self::ALERT_TYPE_DANGER);
                return $this->ControllerContactType($app);
            }

            // check if the tag #minishop exists
            if (!$this->app['contact']->existsTagName('MINISHOP')) {
                $this->app['contact']->createTagName('MINISHOP', 'Indicate that the contact has used the miniShop');
            }

            if (!isset($contact['category'][0]['category_name']) || ($contact['category'][0]['category_name'] === 'UNCHECKED') ||
                ($contact['category'][0]['category_name'] === 'NO_CATEGORY')) {
                // set the category CUSTOMER for this contact
                $dataCategoryType = new CategoryType($this->app);
                if (false !== ($category_type = $dataCategoryType->selectByName('CUSTOMER'))) {
                    $contact['category'][0] = array(
                        'category_id' => -1,
                        'contact_id' => $contact_id,
                        'category_type_id' => $category_type['category_type_id'],
                        'category_type_name' => $category_type['category_type_name']
                    );
                }
            }

            if ($contact_id > 0) {
                // update an existing contact record
                if (false === ($this->app['contact']->update($contact, $contact_id))) {
                    return $this->getContactDialog($data, $order);
                }

            }
            else {
                // insert a new contact record - important, set the status to PENDING
                $contact['contact']['contact_status'] = 'PENDING';
                if (!$this->app['contact']->insert($contact, $contact_id)) {
                    // problem insert the record - go back to the first dialog
                    return $this->getContactDialog($data, $order);
                }
            }

            if (!$this->app['contact']->issetContactTag('MINISHOP', $contact_id)) {
                // set the tag #miniShop for this contact
                $this->app['contact']->setContactTag('MINISHOP', $contact_id);
            }

            if (!isset($data['payment_method'])) {
                throw new \Exception('Missing the payment method!');
            }

            switch ($data['payment_method']) {
                case 'ADVANCE_PAYMENT':
                    $Payment = new AdvancePayment($this->app);
                    $Payment->startPayment($contact_id);
                    break;
                case 'ON_ACCOUNT':
                    $Payment = new OnAccount($this->app);
                    $Payment->startPayment($contact_id);
                    break;
                case 'PAYPAL':
                    // submit the control to the paypal extension
                    $Payment = new PayPal($this->app);
                    return $Payment->startPayment($contact_id);
                default:
                    throw new \Exception('Unknown payment method '.$data['payment']);
            }

            return $this->app['twig']->render($this->app['utils']->getTemplateFile(
                '@phpManufaktur/miniShop/Template', 'command/basket/prompt.twig',
                $this->getPreferredTemplateStyle()),
                array(
                    'basic' => $this->getBasicSettings(),
                    'alert' => $this->getAlert(),
                    'config' => self::$config,
                    'permalink_base_url' => CMS_URL.self::$config['permanentlink']['directory'],
                    'shop_url' => CMS_URL.$order['base']['target_page_link']
                ));
        }
        else {
            // general error (timeout, CSFR ...)
            $this->setAlert('The form is not valid, please check your input and try again!', array(),
                self::ALERT_TYPE_DANGER, true, array('form_errors' => $form->getErrorsAsString(),
                    'method' => __METHOD__, 'line' => __LINE__));
            return $this->getContactDialog(array(), $order);
        }
    }

    /**
     * Controller to select the contact type and start the order
     *
     * @param Application $app
     */
    public function ControllerContactType(Application $app)
    {
        $this->initParameters($app);

        // get the current order from the basket
        $order = $this->Basket->CreateOrderDataFromBasket();

        $form = $this->getContactTypeForm();

        return $this->app['twig']->render($this->app['utils']->getTemplateFile(
            '@phpManufaktur/miniShop/Template', 'command/basket/order.twig',
            $this->getPreferredTemplateStyle()),
            array(
                'basic' => $this->getBasicSettings(),
                'alert' => $this->getAlert(),
                'config' => self::$config,
                'permalink_base_url' => CMS_URL.self::$config['permanentlink']['directory'],
                'form' => $form->createView(),
                'order' => $order,
                'shop_url' => CMS_URL.$order['base']['target_page_link']
            ));
    }

    /**
     * Controller check the submitted GUID, redirect to the desired
     * payment method and handle the order
     *
     * @param Application $app
     * @throws \Exception
     */
    public function ControllerGUID(Application $app)
    {
        $this->initParameters($app);

        $query = $this->getCMSgetParameters();

        if (!isset($query['guid']) || (false === ($order = $this->dataOrder->selectByGUID($query['guid'])))) {
            $app->abort(404, 'The submitted GUID does not exist.');
        }

        if ($order['status'] !== 'PENDING') {
            $app->abort(410, 'The submitted GUID is no longer valid.');
        }

        $data = array(
            'status' => 'CONFIRMED',
            'confirmation_timestamp' => date('Y-m-d H:i:s')
        );
        $this->dataOrder->update($order['id'], $data);

        $status = $app['contact']->getStatus($order['contact_id']);
        if ($status === 'PENDING') {
            $data = array(
                'contact' => array(
                    'contact_status' => 'ACTIVE'
                )
            );
            // change the status to ACTIVE
            $app['contact']->update($data, $order['contact_id']);
        }

        switch ($order['payment_method']) {
            case 'ADVANCE_PAYMENT':
                $AdvancePayment = new AdvancePayment($app);
                if ($AdvancePayment->sendOrderConfirmation($order['id'])) {
                    $this->setAlert('Thank you for the order, we have send you a confirmation mail.',
                        array(), self::ALERT_TYPE_SUCCESS);
                }
                break;
            case 'ON_ACCOUNT':
                $OnAccount = new OnAccount($app);
                if ($OnAccount->sendOrderConfirmation($order['id'])) {
                    $this->setAlert('Thank you for the order, we have send you a confirmation mail.',
                        array(), self::ALERT_TYPE_SUCCESS);
                }
                break;
            default:
                throw new \Exception('Unknown payment method: '.$order['payment_method']);
        }

        // get the params to autoload jQuery and CSS
        $params = $this->getResponseParameter();

        return $this->app->json(array(
            'parameter' => $params,
            'response' => $this->getAlert()
        ));
    }

    /**
     * Controller to send an activation GUID
     *
     * @param Application $app
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function ControllerSendGUID(Application $app, $jsonResponse=false)
    {
        $this->initParameters($app);

        $query = $this->getCMSgetParameters();

        if (!isset($query['order_id']) || (false === ($order = $this->dataOrder->select($query['order_id'])))) {
            $app->abort(404, 'The submitted order ID does not exist.');
        }

        if ($order['status'] !== 'PENDING') {
            $app->abort(410, 'This order was already handled and can not activated again.');
        }

        if ($this->sendAccountConfirmation($query['order_id'])) {
            $this->setAlert('Thank you for the order. We have send you a email with a confirmation link, please use this link to finish your order.',
                array(), self::ALERT_TYPE_SUCCESS);
        }

        if ($jsonResponse) {
            // get the params to autoload jQuery and CSS
            $params = $this->getResponseParameter();

            return $this->app->json(array(
                'parameter' => $params,
                'response' => $this->getAlert()
            ));
        }
        else {
            // unserialize the order data to enable access
            $data = unserialize($order['data']);

            return $this->app['twig']->render($this->app['utils']->getTemplateFile(
                '@phpManufaktur/miniShop/Template', 'command/basket/prompt.twig',
                $this->getPreferredTemplateStyle()),
                array(
                    'basic' => $this->getBasicSettings(),
                    'alert' => $this->getAlert(),
                    'config' => self::$config,
                    'permalink_base_url' => CMS_URL.self::$config['permanentlink']['directory'],
                    'shop_url' => CMS_URL.$data['base']['target_page_link']
                ));
        }
    }



    /**
     * Send a mail to confirm the account
     *
     * @param integer $order_id
     * @throws \Exception
     * @return boolean
     */
    public function sendAccountConfirmation($order_id)
    {
        // get the order
        if (false === ($order = $this->dataOrder->select($order_id))) {
            throw new \Exception("The order with the ID $order_id does not exist!");
        }
        // and the desired contact
        $contact = $this->app['contact']->selectOverview($order['contact_id']);

        // send a confirmation
        $body = $this->app['twig']->render($this->app['utils']->getTemplateFile(
            '@phpManufaktur/miniShop/Template', 'command/mail/customer/double-opt-in.twig',
            $this->getPreferredTemplateStyle()),
            array(
                'order' => $order,
                'config' => self::$config,
                'permalink_base_url' => CMS_URL.self::$config['permanentlink']['directory'],
                'contact' => $contact
            ));

        // send a email to the customer
        $message = \Swift_Message::newInstance()
            ->setSubject($this->app['translator']->trans('Your miniShop order'))
            ->setFrom(SERVER_EMAIL_ADDRESS, SERVER_EMAIL_NAME)
            ->setTo($contact['communication_email'])
            ->setBody($body)
            ->setContentType('text/html');

        // send the message
        $failedRecipients = null;
        if (!$this->app['mailer']->send($message, $failedRecipients))  {
            $this->setAlert("Can't send mail to %recipients%.", array(
                '%recipients%' => implode(',', $failedRecipients)), self::ALERT_TYPE_WARNING);
            return false;
        }

        return true;
    }
}
