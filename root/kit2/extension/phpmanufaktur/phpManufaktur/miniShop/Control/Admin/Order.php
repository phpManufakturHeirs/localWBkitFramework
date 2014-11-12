<?php

/**
 * miniShop
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de/miniShop
 * @copyright 2014 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\miniShop\Control\Admin;

use Silex\Application;
use phpManufaktur\miniShop\Data\Shop\Order as DataOrder;

/**
 * miniShop admin order class
 **/
class Order extends Admin
{
    protected $dataOrder = null;

    /**
     * initializes the order class
     *
     * @access protected
     * @param  object    $app
     * @return void
     **/
    protected function initialize(Application $app)
    {
        parent::initialize($app);

        $this->dataOrder = new DataOrder($app);
    }

    /**
     * view controller
     *
     * @access public
     * @return HTML   (renders template)
     **/
    public function ControllerView(Application $app, $order_id)
    {
        $this->initialize($app);

        if (false === ($order = $this->dataOrder->select($order_id))) {
            $this->setAlert('There exists no order with the ID %order_id%!',
                array('%order_id%' => $order_id), self::ALERT_TYPE_DANGER);
        }
        else {
            $order['data'] = unserialize($order['data']);
            if (false === ($contact = $app['contact']->selectOverview($order['contact_id']))) {
                $this->setAlert('The contact with the ID %contact_id% does no longer exists!',
                    array('%contact_id%' => $order['contact_id']), self::ALERT_TYPE_DANGER);
                $contact = $app['contact']->getDefaultRecord();
            }
        }


        return $this->app['twig']->render($this->app['utils']->getTemplateFile(
            '@phpManufaktur/miniShop/Template', 'admin/view.order.twig'),
            array(
                'usage' => self::$usage,
                'usage_param' => self::$usage_param,
                'toolbar' => $this->getToolbar('orders'),
                'alert' => $this->getAlert(),
                'config' => self::$config,
                'order' => $order,
                'contact' => $contact,
                'permalink_base_url' => CMS_URL.self::$config['permanentlink']['directory']
            ));
    }

    /**
     * Controller to show a list with all orders
     *
     * @param Application $app
     */
    public function Controller(Application $app)
    {
        $this->initialize($app);

        $orders = array();
        if (false !== ($results = $this->dataOrder->selectAll(self::$config['order']['admin']['list']['max_days']))) {
            foreach ($results as $result) {
                // inject the contact data
                if (false === ($result['contact'] = $this->app['contact']->selectOverview($result['contact_id']))) {
                    $this->setAlert('Contact ID <strong>%contact_id%</strong> assigned to order ID <strong>%order_id%</strong> does no longer exists!',
                        array('%contact_id%' => $result['contact_id'], '%order_id%' => $result['id']), self::ALERT_TYPE_WARNING);
                    $result['contact'] = $this->app['contact']->getDefaultRecord();
                }
                if ($result['contact']['contact_status'] === 'DELETED') {
                    $this->setAlert('Contact ID <strong>%contact_id%</strong> assigned to order ID <strong>%order_id%</strong> is marked as <strong>DELETED</strong>.',
                        array('%contact_id%' => $result['contact_id'], '%order_id%' => $result['id']), self::ALERT_TYPE_WARNING);
                }
                $orders[] = $result;
            }
        }

        return $this->app['twig']->render($this->app['utils']->getTemplateFile(
            '@phpManufaktur/miniShop/Template', 'admin/list.order.twig'),
            array(
                'usage' => self::$usage,
                'usage_param' => self::$usage_param,
                'toolbar' => $this->getToolbar('orders'),
                'alert' => $this->getAlert(),
                'config' => self::$config,
                'orders' => $orders
            ));
    }
}
