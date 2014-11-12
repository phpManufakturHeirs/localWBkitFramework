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
use phpManufaktur\miniShop\Data\Shop\Basket as DataBasket;

class Basket extends CommandBasic
{
    protected static $basket = array();
    protected static $identifier = null;

    protected $dataBasket = null;

    /**
     * (non-PHPdoc)
     * @see \phpManufaktur\Basic\Control\Pattern\Alert::initialize()
     */
    protected function initParameters(Application $app, $parameter_id=-1)
    {
        parent::initParameters($app, $parameter_id);

        self::$identifier = md5($_SERVER['REMOTE_ADDR']);

        $this->dataBasket = new DataBasket($app);
        $this->dataBasket->cleanup();
        // get the current basket
        self::$basket = $this->getBasket();
    }

    /**
     * Get the basket content
     *
     * @return array
     */
    public function getBasket()
    {
        return $this->dataBasket->selectBasket(self::$identifier);
    }

    /**
     * Save the basket to the basket table
     *
     * @param array $basket
     */
    public function setBasket()
    {
        if ($this->dataBasket->existsIdentifier(self::$identifier)) {
            $this->dataBasket->updateBasket(self::$identifier, self::$basket);
        }
        else {
            $this->dataBasket->insertBasket(self::$identifier, self::$basket);
        }
    }

    /**
     * Remove the whole basket
     */
    public function removeBasket()
    {
        $this->dataBasket->removeBasket(self::$identifier);
    }

    /**
     * Update the basket with the given article data
     *
     * @param array $data
     */
    public function updateBasket($data)
    {
        // create a checksum for comparison
        $md5 = md5(serialize($data));

        $add_article = true;
        foreach (self::$basket as $existing_md5 => $article) {
            if (($article['id'] === $data['id']) &&
                ($article['article_variant_values'] === $data['article_variant_values']) &&
                ($article['article_variant_values_2'] === $data['article_variant_values_2'])) {
                $data['quantity'] += $article['quantity'];
                unset(self::$basket[$existing_md5]);
                $add_article = false;
                if ($data['quantity'] > 0) {
                    self::$basket[$md5] = $data;
                    $this->setAlert('Changed quantity for the article <strong>%article%</strong> to <strong>%quantity%</strong>.',
                        array('%quantity%' => $data['quantity'], '%article%' => $data['article_name']), self::ALERT_TYPE_SUCCESS);
                }
                else {
                    $this->setAlert('Removed the article <strong>%article%</strong> from the basket.',
                        array('%article%' => $data['article_name']), self::ALERT_TYPE_SUCCESS);
                }
                $this->setBasket();
                break;
            }
        }
        if ($add_article) {
            if (!isset(self::$basket[$md5])) {
                if ($data['quantity'] > 0) {
                    $data['variant_value'] = null;
                    if (!is_null($data['article_variant_values'])) {
                        $article = $this->dataArticle->select($data['id']);
                        $variant_values = array();
                        $items = explode("\r\n", $article['article_variant_values']);
                        foreach ($items as $item) {
                            $item = trim($item);
                            if (!empty($item)) {
                                $variant_values[] = $item;
                            }
                        }
                        if (!empty($variant_values)) {
                            $data['variant_value'] = $variant_values[$data['article_variant_values']];
                        }
                    }
                    $data['variant_value_2'] = null;
                    if (!is_null($data['article_variant_values_2'])) {
                        $article = $this->dataArticle->select($data['id']);
                        $variant_values = array();
                        $items = explode("\r\n", $article['article_variant_values_2']);
                        foreach ($items as $item) {
                            $item = trim($item);
                            if (!empty($item)) {
                                $variant_values[] = $item;
                            }
                        }
                        if (!empty($variant_values)) {
                            $data['variant_value_2'] = $variant_values[$data['article_variant_values_2']];
                        }
                    }
                    self::$basket[$md5] = $data;
                    $this->setAlert('Added article <strong>%article%</strong> to the basket.',
                        array('%article%' => $data['article_name']), self::ALERT_TYPE_SUCCESS);
                    $this->setBasket();
                }
                else {
                    $this->setAlert('Invalid quantity, ignored article.', array(), self::ALERT_TYPE_WARNING);
                }
            }
            else {
                $this->setAlert('The selected article is already in your basket.', array(), self::ALERT_TYPE_INFO);
            }
        }
    }

    /**
     * Get the form to change article quantities in the basket
     *
     * @param array $basket
     */
    protected function getBasketForm()
    {
        $form = $this->app['form.factory']->createBuilder('form', null, array('csrf_protection' => false));

        foreach (self::$basket as $key => $item) {
            $form->add($key, 'text', array(
                'data' => $item['quantity']
            ));
        }

        return $form->getForm();
    }

    /**
     * Create a complete order record will all informations from basket, articles and calculation
     *
     * @return array
     */
    public function CreateOrderDataFromBasket()
    {
        $order = array(
            'base' => null,
            'articles' => array(),
            'sub_total' => 0,
            'vat_total' => 0,
            'sum_total' => 0,
            'shipping_total' => null
        );

        foreach (self::$basket as $key => $item) {
            $article = $this->dataArticle->select($item['id']);
            if (is_null($order['base'])) {
                $order['base'] = $this->dataBase->select($article['base_id']);
            }
            $article['basket_id'] = $key;
            $article['quantity'] = $item['quantity'];
            $article['variant_value'] = isset($item['variant_value']) ? $item['variant_value'] : null;
            $article['variant_value_2'] = isset($item['variant_value_2']) ? $item['variant_value_2'] : null;
            $article['subtotal'] = $article['quantity'] * $article['article_price'];

            // add subtotal
            $order['sub_total'] += $article['subtotal'];
            // check shipping
            if ($order['base']['shipping_type'] == 'ARTICLE') {
                if (($order['base']['shipping_article'] == 'HIGHEST') && (is_null($order['shipping_total']) || ($article['shipping_cost'] > $order['shipping_total']))) {
                    $order['shipping_total'] = $article['shipping_cost'];
                }
                elseif (($order['base']['shipping_article'] == 'LOWEST') && (is_null($order['shipping_total']) || ($article['shipping_cost'] < $order['shipping_total']))) {
                    $order['shipping_total'] = $article['shipping_cost'];
                }
                elseif ($order['base']['shipping_article'] == 'SUM_UP') {
                    $order['shipping_total'] += $article['shipping_cost'];
                }
            }
            $order['articles'][] = $article;
        }

        if ($order['base']['shipping_type'] == 'FLATRATE') {
            $order['shipping_total'] = $order['base']['shipping_flatrate'];
        }

        if ($order['base']['article_value_added_tax'] > 0) {
            if ($order['base']['article_price_type'] === 'NET_PRICE') {
                $order['vat_total'] = ($order['sub_total']/100)*$order['base']['article_value_added_tax'];
                $order['sum_total'] = $order['sub_total'] + $order['vat_total'] + $order['shipping_total'];
            }
            else {
                // Grossprice
                $order['vat_total'] = ($order['sub_total']/(100+$order['base']['article_value_added_tax']))*$order['base']['article_value_added_tax'];
                $order['sum_total'] = $order['sub_total'] + $order['shipping_total'];
            }
        }

        return $order;
    }

    /**
     * Controller to view the current basket
     *
     * @param Application $app
     * @return string
     */
    public function ControllerBasketView(Application $app)
    {
        $this->initParameters($app);

        self::$parameter = $this->getCommandParameters();

        // check wether to use the minishop.css or not
        self::$parameter['load_css'] = (isset(self::$parameter['load_css']) && ((self::$parameter['load_css'] == 0) || (strtolower(self::$parameter['load_css']) == 'false'))) ? false : true;
        // disable the jquery check?
        self::$parameter['check_jquery'] = (isset(self::$parameter['check_jquery']) && ((self::$parameter['check_jquery'] == 0) || (strtolower(self::$parameter['check_jquery']) == 'false'))) ? false : true;

        $base = null;

        $GET = $this->getCMSgetParameters();
        if (isset($GET['basket'])) {
            // maybe the quantities has changed!
            $check = self::$basket;
            $changed = false;
            $removed = false;
            foreach ($check as $key => $item) {
                if (isset($GET['basket'][$key]) && ($GET['basket'][$key] != $item['quantity'])) {
                    if ($GET['basket'][$key] < 1) {
                        if (is_null($base)) {
                            // get the base configuration now, the basket maybe empty after removing this article!
                            $article = $this->dataArticle->select($item['id']);
                            $base = $this->dataBase->select($article['base_id']);
                        }
                        unset(self::$basket[$key]);
                        $this->setAlert('Removed article <strong>%article%</strong> from your shopping basket',
                            array('%article%' => $item['article_name']), self::ALERT_TYPE_SUCCESS);
                        $removed = true;
                    }
                    else {
                        self::$basket[$key]['quantity'] = $GET['basket'][$key];
                        $this->setAlert('Changed the quantity for article <strong>%article%</strong> from <strong>%old_quantity%</strong> to <strong>%new_quantity%</strong>.',
                            array('%article%' => $item['article_name'], '%old_quantity%' => $item['quantity'], '%new_quantity%' => $GET['basket'][$key]),
                            self::ALERT_TYPE_SUCCESS);
                        $changed = true;
                    }
                }
            }
            if ($changed || $removed) {
                $this->setBasket();
            }
            else {
                $this->setAlert('Your shopping basket has not changed.', array(), self::ALERT_TYPE_INFO);
            }
        }
        $form = $this->getBasketForm();

        $order = null;
        if (empty(self::$basket)) {
            $this->setAlert('Your shopping basket is empty.', array(), self::ALERT_TYPE_INFO);
        }
        else {
            $order = $this->CreateOrderDataFromBasket();
            $base = $order['base'];
        }

        return $this->app['twig']->render($this->app['utils']->getTemplateFile(
            '@phpManufaktur/miniShop/Template', 'command/basket/view.twig',
            $this->getPreferredTemplateStyle()),
            array(
                'basic' => $this->getBasicSettings(),
                'alert' => $this->getAlert(),
                'config' => self::$config,
                'permalink_base_url' => CMS_URL.self::$config['permanentlink']['directory'],
                'form' => $form->createView(),
                'order' => $order,
                'shop_url' => CMS_URL.$base['target_page_link']
            ));
    }
}
