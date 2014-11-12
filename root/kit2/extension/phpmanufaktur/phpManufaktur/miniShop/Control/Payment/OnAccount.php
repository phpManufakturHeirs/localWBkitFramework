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
use phpManufaktur\miniShop\Control\Command\Order;

class OnAccount extends Payment
{

    /**
     * (non-PHPdoc)
     * @see \phpManufaktur\miniShop\Control\Payment\Payment::initParameters()
     */
    protected function initParameters(Application $app, $parameter_id=-1)
    {
        parent::initParameters($app, $parameter_id);

        self::$payment_method = 'ON_ACCOUNT';
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
            '@phpManufaktur/miniShop/Template', 'command/mail/customer/onaccount.twig',
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
            $this->setAlert("Can't send mail to %recipients%.", array(
                '%recipients%' => implode(',', $failedRecipients)), self::ALERT_TYPE_WARNING);
            return false;
        }

        // send a email to the dealer
        $body = $this->app['twig']->render($this->app['utils']->getTemplateFile(
            '@phpManufaktur/miniShop/Template', 'command/mail/dealer/onaccount.twig',
            $this->getPreferredTemplateStyle()),
            array(
                'order_data' => unserialize($order['data']),
                'order' => $order,
                'config' => self::$config,
                'permalink_base_url' => CMS_URL.self::$config['permanentlink']['directory'],
                'contact' => $contact
            ));

        $message = \Swift_Message::newInstance()
            ->setSubject($this->app['translator']->trans('miniShop order by on account payment'))
            ->setFrom(array(SERVER_EMAIL_ADDRESS => SERVER_EMAIL_NAME))
            ->setTo(SERVER_EMAIL_ADDRESS)
            ->setReplyTo($contact['communication_email'])
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

    /**
     * Start the payment method On Account
     *
     * @param integer $contact_id
     * @throws \Exception
     * @return boolean
     */
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
            // remove the current basket
            $this->Basket->removeBasket();
            $date = date($this->app['translator']->trans('DATETIME_FORMAT'), strtotime($pending['order_timestamp']));
            $link = CMS_URL.self::$config['permanentlink']['directory'].'/order/send/'.$pending['id'];
            $this->setAlert('<p>We are sorry, but there exists already a pending order of date <strong>%date%</strong>. Please confirm or discard this order before creating a new one.</p><p>We can <a href="%link%" target="_parent">send you again the confirmation mail</a>.</p>',
                array('%date%' => $date, '%link%' => $link), self::ALERT_TYPE_WARNING);
            return false;
        }

        // create a order data record
        $order_id = $this->createOrderRecord($contact_id, $status);

        // remove the current basket
        $this->Basket->removeBasket();


        if (($status === 'CONFIRMED') && $this->sendOrderConfirmation($order_id)) {
            $this->setAlert('Thank you for the order, we have send you a confirmation mail.',
                array(), self::ALERT_TYPE_SUCCESS);
        }
        else {
            $Order = new Order($this->app);
            if ($Order->sendAccountConfirmation($order_id)) {
                $this->setAlert('Thank you for the order. We have send you a email with a confirmation link, please use this link to finish your order.',
                    array(), self::ALERT_TYPE_SUCCESS);
            }
        }

        return true;
    }
}
