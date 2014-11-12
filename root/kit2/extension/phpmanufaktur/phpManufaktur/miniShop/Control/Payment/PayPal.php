<?php

/**
 * miniShop
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de/miniShop
 * @copyright 2014 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\miniShop\Control\Payment;

use Silex\Application;
use phpManufaktur\miniShop\Control\Admin\Base;
use phpManufaktur\miniShop\Control\Configuration;
use phpManufaktur\miniShop\Data\Shop\Order;

class PayPal extends Payment
{

    /**
     * (non-PHPdoc)
     * @see \phpManufaktur\miniShop\Control\Payment\Payment::initParameters()
     */
    protected function initParameters(Application $app, $parameter_id=-1)
    {
        parent::initParameters($app, $parameter_id);

        self::$payment_method = 'PAYPAL';
    }

    public function startPayment($contact_id)
    {
        if (false === ($contact_status = $this->app['contact']->getStatus($contact_id))) {
            throw new \Exception('Can not get the status for the contact ID '.$contact_id);
        }

        if ($contact_status === 'ACTIVE') {
            $status = 'CONFIRMED';
        }
        elseif ($contact_status === 'PENDING') {
            $status = 'PENDING';
        }
        else {
            $this->setAlert('Sorry, but we have a problem. Please contact the webmaster and tell him to check the status of the email address %email%.',
                array('%email%' => $this->app['contact']->getPrimaryEMailAddress($contact_id)),
                self::ALERT_TYPE_DANGER, true, array(__METHOD__, __LINE__));
            return false;
        }

        if (false !== ($pending = $this->dataOrder->existsPendingForContactID($contact_id))) {
            // still delete the existing order
            $this->dataOrder->update($pending['id'], array('status' => 'DELETED'));
            $this->app['monolog']->addDebug("Deleted pending order {$pending['id']} because of an new order and payment via PayPal");
        }

        // create a order data record
        $order_id = $this->createOrderRecord($contact_id, $status);

        // remove the current basket
        $this->Basket->removeBasket();

        $order = $this->dataOrder->select($order_id);
        $order['data'] = unserialize($order['data']);

        $contact = $this->app['contact']->selectOverview($contact_id);

        return $this->app['twig']->render($this->app['utils']->getTemplateFile(
                '@phpManufaktur/miniShop/Template', 'command/basket/paypal.twig',
                $this->getPreferredTemplateStyle()),
                array(
                    'basic' => $this->getBasicSettings(),
                    'alert' => $this->getAlert(),
                    'config' => self::$config,
                    'permalink_base_url' => CMS_URL.self::$config['permanentlink']['directory'],
                    'order' => $order,
                    'contact' => $contact
                ));
    }

    /**
     * Get the PayPal account settings form
     *
     * @param array $data
     */
    protected function getConfigForm($data=array())
    {
        return $this->app['form.factory']->createBuilder('form')
            ->add('sandbox', 'checkbox', array(
                'data' => isset($data['sandbox']) ? $data['sandbox'] : false,
                'required' => false,
                'label' => $this->app['translator']->trans('Sandbox mode')
            ))
            ->add('email', 'email', array(
                'data' => isset($data['email']) ? $data['email'] : '',
                'required' => false
            ))
            ->add('token', 'text', array(
                'data' => isset($data['token']) ? $data['token'] : '',
                'required' => false
            ))
            ->add('logo', 'text', array(
                'data' => isset($data['logo']) ? $data['logo'] : '',
                'required' => false
            ))
            ->getForm();
    }

    /**
     * Controller to view and change the PayPal account settings
     *
     * @param Application $app
     */
    public function ControllerConfig(Application $app)
    {
        $this->initParameters($app);

        $Base = new Base($app);

        $form = $this->getConfigForm(self::$config['paypal']);

        return $this->app['twig']->render($this->app['utils']->getTemplateFile(
            '@phpManufaktur/miniShop/Template', 'admin/edit.paypal.twig'),
            array(
                'usage' => self::$usage,
                'usage_param' => self::$usage_param,
                'toolbar' => $Base->getToolbar('base'),
                'base_toolbar' => $Base->getBaseToolbar('paypal'),
                'alert' => $this->getAlert(),
                'form' => $form->createView()
            ));
    }

    /**
     * Controller to check the settings of the banking account
     *
     * @param Application $app
     */
    public function ControllerConfigCheck(Application $app)
    {
        $this->initParameters($app);

        $form = $this->getConfigForm();
        $form->bind($this->app['request']);

        if ($form->isValid()) {
            // the form is valid
            $data = $form->getData();

            $changed = false;
            foreach (self::$config['paypal'] as $key => $value) {
                if (isset($data[$key]) && ($data[$key] !== $value)) {
                    self::$config['paypal'][$key] = $data[$key];
                    $changed = true;
                }
            }

            if ($changed) {
                $Configuration = new Configuration($app);
                $Configuration->setConfiguration(self::$config);
                $Configuration->saveConfiguration();
                $this->setAlert('The PayPal settings has updated', array(), self::ALERT_TYPE_SUCCESS);
            }
            else {
                $this->setAlert('The PayPal settings has not changed.', array(), self::ALERT_TYPE_INFO);
            }
        }
        else {
            // general error (timeout, CSFR ...)
            $this->setAlert('The form is not valid, please check your input and try again!', array(),
                self::ALERT_TYPE_DANGER, true, array('form_errors' => $form->getErrorsAsString(),
                    'method' => __METHOD__, 'line' => __LINE__));
        }

        $Base = new Base($app);

        return $this->app['twig']->render($this->app['utils']->getTemplateFile(
            '@phpManufaktur/miniShop/Template', 'admin/edit.paypal.twig'),
            array(
                'usage' => self::$usage,
                'usage_param' => self::$usage_param,
                'toolbar' => $Base->getToolbar('base'),
                'base_toolbar' => $Base->getBaseToolbar('paypal'),
                'alert' => $this->getAlert(),
                'form' => $form->createView()
            ));
    }


    public function ControllerCancel(Application $app, $order_id)
    {
        $this->initParameters($app);

        if (false === ($order = $this->dataOrder->select($order_id))) {
            $this->setAlert('The record with the ID %id% does not exist!',
                array('%id%' => $order_id), self::ALERT_TYPE_DANGER);
            return $this->promptAlert();
        }

        // delete the order
        $data = array(
            'status' => 'DELETED'
        );
        $this->dataOrder->update($order_id, $data);

        $this->setAlert('The payment at PayPal was canceled', array(), self::ALERT_TYPE_WARNING);
        return $this->promptAlert();
    }

    /**
     * Send the confirmation mails to the customer and the dealer
     *
     * @param integer $order_id
     * @throws \Exception
     */
    public function sendOrderConfirmation($order_id)
    {
        // get the order
        if (false === ($order = $this->dataOrder->select($order_id))) {
            throw new \Exception("The order with the ID $order_id does not exist!");
        }
        // and the desired contact
        $contact = $this->app['contact']->selectOverview($order['contact_id']);

        // send a confirmation
        $body = $this->app['twig']->render($this->app['utils']->getTemplateFile(
            '@phpManufaktur/miniShop/Template', 'command/mail/customer/paypal.twig',
            $this->getPreferredTemplateStyle()),
            array(
                'order_data' => unserialize($order['data']),
                'order' => $order,
                'config' => self::$config,
                'permalink_base_url' => CMS_URL.self::$config['permanentlink']['directory'],
                'contact' => $contact
            ));

        // send a email to the customer
        $message = \Swift_Message::newInstance()
            ->setSubject($this->app['translator']->trans('Your miniShop order'))
            ->setFrom(array(SERVER_EMAIL_ADDRESS => SERVER_EMAIL_NAME))
            ->setTo($contact['communication_email'])
            ->setBody($body)
            ->setContentType('text/html');

        // send the message
        $failedRecipients = null;
        if (!$this->app['mailer']->send($message, $failedRecipients))  {
            $this->app['monolog']->addError("Can't send mail to ", implode(',', $failedRecipients));
            return false;
        }

        // send a email to the dealer
        $body = $this->app['twig']->render($this->app['utils']->getTemplateFile(
            '@phpManufaktur/miniShop/Template', 'command/mail/dealer/paypal.twig',
            $this->getPreferredTemplateStyle()),
            array(
                'order_data' => unserialize($order['data']),
                'order' => $order,
                'config' => self::$config,
                'permalink_base_url' => CMS_URL.self::$config['permanentlink']['directory'],
                'contact' => $contact
            ));

        $message = \Swift_Message::newInstance()
            ->setSubject($this->app['translator']->trans('miniShop order by PayPal'))
            ->setFrom(array(SERVER_EMAIL_ADDRESS => SERVER_EMAIL_NAME))
            ->setTo(SERVER_EMAIL_ADDRESS)
            ->setReplyTo($contact['communication_email'])
            ->setBody($body)
            ->setContentType('text/html');

        // send the message
        $failedRecipients = null;
        if (!$this->app['mailer']->send($message, $failedRecipients))  {
            $this->app['monolog']->addError("Can't send mail to ", implode(',', $failedRecipients));
            return false;
        }
        return true;
    }

    public function ControllerSuccess(Application $app, $order_id)
    {
        $this->initParameters($app);

        if (false === ($order = $this->dataOrder->select($order_id))) {
            $this->setAlert('The record with the ID %id% does not exist!',
                array('%id%' => $order_id), self::ALERT_TYPE_DANGER);
            return $this->promptAlert();
        }

        $this->setAlert('The PayPal payment was successfull. We will send you a confirmation mail as soon we receive the automated confirmation from PayPal.');
        return $this->promptAlert();
    }

    /**
     * Controller for PayPal IPN
     *
     * @param Application $app
     * @see https://github.com/paypal/ipn-code-samples/blob/master/paypal_ipn.php
     */
    public function ControllerIPN(Application $app, $order_id)
    {
        // dont use initParameters() because we won't init the BASIC class here!
        $this->app = $app;

        $Configuration = new Configuration($app);
        self::$config = $Configuration->getConfiguration();

        self::$payment_method = 'PAYPAL';

        $this->app['monolog']->addDebug(sprintf('Handling PayPal OrderID [%s]',$order_id),array(__METHOD__, __LINE__));

        // Read POST data as proposed at PayPal IPN example
        $raw_post_data = file_get_contents('php://input');
        $raw_post_array = explode('&', $raw_post_data);
        $myPost = array();
        foreach ($raw_post_array as $keyval) {
            $keyval = explode ('=', $keyval);
            if (count($keyval) == 2)
                $myPost[$keyval[0]] = urldecode($keyval[1]);
        }

        $this->app['monolog']->addDebug(var_export($myPost,1),array(__METHOD__, __LINE__));

        $this->dataOrder = new Order($app);

        if (false === ($order = $this->dataOrder->select($order_id))) {
            // the order ID does not exist!
            $app['monolog']->addError("The order with the ID $order_id does not exist!");
            $app->abort(500, 'Invalid order ID!');
        }
        else {
            $this->app['monolog']->addDebug('Order details: '.var_export($order,1),array(__METHOD__, __LINE__));
            if ($order['transaction_id'] !== 'NONE') {
                // this transaction was already handled
                $app['monolog']->addError("The order with the ID $order_id was already handled with the transaction ID {$order['transaction_id']}!");
                $app->abort(500, 'Transaction was already handled!');
            }
        }

        // read the post from PayPal system and add 'cmd'
        $request = 'cmd=_notify-validate';
        if (function_exists('get_magic_quotes_gpc')) {
            $get_magic_quotes_exists = true;
        }

        foreach ($myPost as $key => $value) {
            if (($get_magic_quotes_exists == true) && (get_magic_quotes_gpc() == 1)) {
                $value = urlencode(stripslashes($value));
            }
            else {
                $value = urlencode($value);
            }
            $request .= "&$key=$value";
        }

        if (self::$config['paypal']['sandbox']) {
            $paypal_url = "https://www.sandbox.paypal.com/cgi-bin/webscr";
        }
        else {
            $paypal_url = "https://www.paypal.com/cgi-bin/webscr";
        }

        $this->app['monolog']->addDebug('URL to call: '.$paypal_url);

        // init cURL
        if (false === ($ch = curl_init($paypal_url))) {
            $app['monolog']->addDebug('Got no handle for cURL!');
            $app->abort(500);
        }

        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_FORBID_REUSE, 1);

        // set proxy if needed
        $app['utils']->setCURLproxy($ch);

        // execute cURL
        $res = curl_exec($ch);

        if (curl_errno($ch) != 0) {
            // cURL error - abort
            $app['monolog']->addDebug("Can't connect to PayPal to validate IPN message: " . curl_error($ch));
            curl_close($ch);
            $app->abort(500, 'Connection error');
        }
        else
        {
            $this->app['monolog']->addDebug('curl_exec() result: '.var_export($res,1));
        }

        $app['monolog']->addDebug("HTTP request of validation request:". curl_getinfo($ch, CURLINFO_HEADER_OUT) ." for IPN payload: $request");
        curl_close($ch);

        // Inspect IPN validation result and act accordingly
        if (strcmp($res, "VERIFIED") == 0) {
            $verfied = true;

            if (!isset($myPost['payment_status']) || ($myPost['payment_status'] !== 'Completed')) {
                $app['monolog']->addError("Incorrect payment status: {$myPost['payment_status']} - expected: Completed.", $myPost);
                $verfied = false;
            }

            if (!isset($myPost['txn_id']) ||
                (($myPost['txn_id'] != $order['transaction_id']) &&
                 ($order['transaction_id'] !== 'NONE'))) {
                $app['monolog']->addError("The transaction ID mismatch!", $myPost);
                $verfied = false;
            }

            if (!isset($myPost['business'])) {
                $app['monolog']->addError('Missing the business email address!');
                $verified = false;
            }

            if (strtolower(self::$config['paypal']['email']) !== strtolower($myPost['business'])) {
                $app['monolog']->addError("Invalid business email address: {$myPost['business']}");
                $verified = false;
            }

            if ($verfied) {
                // transaction is OK - update order record
                $data = array(
                    'transaction_id' => $myPost['txn_id'],
                    'status' => 'CONFIRMED'
                );
                $this->dataOrder->update($order_id, $data);

                $status = $app['contact']->getStatus($order['contact_id']);
                if ($status === 'PENDING') {
                    $data = array(
                        'contact' => array(
                            'contact_status' => 'ACTIVE'
                        )
                    );
                    // change the contact status to ACTIVE
                    $app['contact']->update($data, $order['contact_id']);
                }

                // send the confirmation mails to the customer and the provider
                $this->sendOrderConfirmation($order_id);

                $app['monolog']->addDebug('Received PayPal payment',array(__METHOD__, __LINE__));
                return 'OK';
            }
        }
        elseif (strcmp($res, "INVALID") == 0) {
            $app['monolog']->addError("Invalid IPN: $request");
        }

        $app->abort(500, 'Invalid IPN');
    }
}
