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
use phpManufaktur\Basic\Control\kitCommand\Basic;
use phpManufaktur\miniShop\Control\Configuration;
use phpManufaktur\miniShop\Control\Command\Basket;
use phpManufaktur\miniShop\Data\Shop\Order as DataOrder;

class Payment extends Basic
{
    protected static $config = null;
    protected static $parameter = null;
    protected static $payment_method = null;

    protected static $usage = null;
    protected static $usage_param = null;

    protected $Basket = null;
    protected $dataOrder = null;

    /**
     * (non-PHPdoc)
     * @see \phpManufaktur\Basic\Control\kitCommand\Basic::initParameters()
     */
    protected function initParameters(Application $app, $parameter_id=-1)
    {
        parent::initParameters($app, $parameter_id);

        $cms = $this->app['request']->get('usage');
        self::$usage = is_null($cms) ? 'framework' : $cms;
        self::$usage_param = (self::$usage != 'framework') ? '?usage='.self::$usage : '';
        // set the locale from the CMS locale
        if (self::$usage != 'framework') {
            $app['translator']->setLocale($this->app['session']->get('CMS_LOCALE', 'en'));
        }

        $Configuration = new Configuration($app);
        self::$config = $Configuration->getConfiguration();

        self::$payment_method = 'UNKNOWN';

        $this->Basket = new Basket($app);
        $this->dataOrder = new DataOrder($app);

        // get the kitCommand parameters
        self::$parameter = $this->getCommandParameters();

        // check the CMS GET parameters
        $GET = $this->getCMSgetParameters();
        if (isset($GET['command']) && ($GET['command'] == 'minishop')
            && isset($GET['action']) && ($GET['action'] == 'order')) {
            // the command and parameters are set as GET from the CMS
            foreach ($GET as $key => $value) {
                if ($key == 'command') {
                    continue;
                }
                self::$parameter[$key] = $value;
            }
            $this->setCommandParameters(self::$parameter);
        }

        if (isset(self::$parameter['alert'])) {
            $this->setAlertUnformatted(base64_decode(self::$parameter['alert']));
        }

        // check wether to use the flexcontent.css or not
        self::$parameter['load_css'] = (isset(self::$parameter['load_css']) && ((self::$parameter['load_css'] == 0) || (strtolower(self::$parameter['load_css']) == 'false'))) ? false : true;
        // disable the jquery check?
        self::$parameter['check_jquery'] = (isset(self::$parameter['check_jquery']) && ((self::$parameter['check_jquery'] == 0) || (strtolower(self::$parameter['check_jquery']) == 'false'))) ? false : true;

    }


    protected function createOrderRecord($contact_id, $status)
    {
        $order = $this->Basket->CreateOrderDataFromBasket();
        $data = array(
            'guid' => $this->app['utils']->createGUID(),
            'contact_id' => $contact_id,
            'data' => serialize($order),
            'order_timestamp' => date('Y-m-d H:i:s'),
            'confirmation_timestamp' => ($status === 'CONFIRMED') ? date('Y-m-d H:i:s') : '0000-00-00 00:00:00',
            'order_total' => $order['sum_total'],
            'payment_method' => self::$payment_method,
            'status' => $status
        );
        return $this->dataOrder->insert($data);
    }
}
