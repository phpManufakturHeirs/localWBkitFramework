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
use phpManufaktur\miniShop\Control\Configuration;
use phpManufaktur\Basic\Control\kitCommand\Basic;
use phpManufaktur\miniShop\Data\Shop\Article as DataArticle;

class ActionBasket extends Basic
{
    protected static $config = null;
    protected static $parameter = null;


    protected $Basket = null;
    protected $dataArticle = null;

    protected function initParameters(Application $app, $parameter_id=-1)
    {
        parent::initParameters($app, $parameter_id);

        $Configuration = new Configuration($app);
        self::$config = $Configuration->getConfiguration();

        $this->Basket = new Basket($app);
        $this->dataArticle = new DataArticle($app);

        self::$parameter = $this->getCommandParameters();
    }

    /**
     * Get the small form to add this article to the basket
     *
     * @param array $data
     */
    protected function getOrderForm($data=array())
    {

        $title_quantity = $this->app['translator']->trans('Quantity to order');

        $form = $this->app['form.factory']->createBuilder('form', null, array('csrf_protection' => false, 'attr' => array('target' => '_parent')))
        ->add('id', 'hidden', array(
            'data' => isset($data['id']) ? $data['id'] : -1
        ))
        ->add('article_name', 'hidden', array(
            'data' => isset($data['article_name']) ? $data['article_name'] : ''
        ))
        ->add('permanent_link', 'hidden', array(
            'data' => isset($data['permanent_link']) ? $data['permanent_link'] : ''
        ))
        ->add('quantity', 'text', array(
            'data' => isset($data['quantity']) ? $data['quantity'] : 1,
            'label' => 'Quantity',
            'attr' => array(
                'class' => 'form-control input-sm',
                'title' => $title_quantity
            )
        ));

        if (isset($data['article_variant_values']) && !empty($data['article_variant_values']) &&
            isset($data['article_variant_name']) && !empty($data['article_variant_name'])) {

            $variant_values = array();
            $items = explode("\r\n", $data['article_variant_values']);
            foreach ($items as $item) {
                $item = trim($item);
                if (!empty($item)) {
                    $variant_values[] = $item;
                }
            }
            if (!empty($variant_values)) {
                $empty_value = $this->app['translator']->trans($data['article_variant_name']);
                $empty_value = sprintf('- %s -', $empty_value);
                $form->add('article_variant_name', 'hidden', array(
                    'data' => $data['article_variant_name']
                ))
                ->add('article_variant_values', 'choice', array(
                    'choices' => $variant_values,
                    'empty_value' => $empty_value,
                    'attr' => array(
                        'class' => 'form-control input-sm'
                    )
                ));
            }
        }
        else {
            $form->add('article_variant_name', 'hidden')
            ->add('article_variant_values', 'hidden');
        }

        if (isset($data['article_variant_values_2']) && !empty($data['article_variant_values_2']) &&
            isset($data['article_variant_name_2']) && !empty($data['article_variant_name_2'])) {
            $variant_values = array();
            $items = explode("\r\n", $data['article_variant_values_2']);
            foreach ($items as $item) {
                $item = trim($item);
                if (!empty($item)) {
                    $variant_values[] = $item;
                }
            }
            if (!empty($variant_values)) {
                $empty_value = $this->app['translator']->trans($data['article_variant_name_2']);
                $empty_value = sprintf('- %s -', $empty_value);
                $form->add('article_variant_name_2', 'hidden', array(
                    'data' => $data['article_variant_name_2']
                ))
                ->add('article_variant_values_2', 'choice', array(
                    'choices' => $variant_values,
                    'empty_value' => $empty_value,
                    'attr' => array(
                        'class' => 'form-control input-sm'
                    )
                ));
            }
        }
        else {
            $form->add('article_variant_name_2', 'hidden')
            ->add('article_variant_values_2', 'hidden');
        }

        return $form->getForm();
    }


    public function ControllerBasket(Application $app)
    {
        $this->initParameters($app);

        return $this->app['twig']->render($this->app['utils']->getTemplateFile(
            '@phpManufaktur/miniShop/Template', 'command/basket/basket.twig',
            $this->getPreferredTemplateStyle()),
            array(
                'basic' => $this->getBasicSettings(),
                'basket' => $this->Basket->getBasket(),
                'alert' => $this->getAlert(),
                'config' => self::$config,
                'permalink_base_url' => CMS_URL.self::$config['permanentlink']['directory']
            ));
    }

    public function ControllerBasketControl(Application $app)
    {
        $this->initParameters($app);

        if (!isset(self::$parameter['article_id'])) {
            $this->setAlert('Please submit a article ID!', array(), self::ALERT_TYPE_DANGER);
            return $this->promptAlert();
        }
        elseif (false === ($article = $this->dataArticle->select(self::$parameter['article_id']))) {
            $this->setAlert('The record with the ID %id% does not exist!',
                array('%id%' => self::$parameter['id']), self::ALERT_TYPE_DANGER);
            return $this->promptAlert();
        }

        $form = $this->getOrderForm($article);

        return $this->app['twig']->render($this->app['utils']->getTemplateFile(
            '@phpManufaktur/miniShop/Template', 'command/basket/control.twig',
            $this->getPreferredTemplateStyle()),
            array(
                'form' => $form->createView(),
                'article' => $article,
                'basic' => $this->getBasicSettings(),
                'basket' => $this->Basket->getBasket(),
                'alert' => $this->getAlert(),
                'config' => self::$config,
                'permalink_base_url' => CMS_URL.self::$config['permanentlink']['directory']
            ));
    }

    public function ControllerBasketControlAdd(Application $app)
    {
        $this->initParameters($app);

        // we need the route to the article
        $request_form = $this->app['request']->request->get('form');
        if (!isset($request_form['permanent_link']) || empty($request_form['permanent_link'])) {
            throw new \Exception('Invalid form submission, stopped script.');
        }
        $subdirectory = parse_url(CMS_URL, PHP_URL_PATH);
        $article_route = $subdirectory.self::$config['permanentlink']['directory'].'/article/'.$request_form['permanent_link'];

        // now we handle the order form
        $form = $this->getOrderForm();
        $form->bind($this->app['request']);

        if ($form->isValid()) {
            // the form is valid
            $data = $form->getData();
            // update the basket with the article data
            $this->Basket->updateBasket($data);
        }
        else {
            // general error (timeout, CSFR ...)
            $this->setAlert('The form is not valid, please check your input and try again!', array(),
                self::ALERT_TYPE_DANGER, true, array('form_errors' => $form->getErrorsAsString(),
                    'method' => __METHOD__, 'line' => __LINE__));
        }

        $url = CMS_URL.self::$config['permanentlink']['directory'].'/article/'.$request_form['permanent_link'];
        $url .= '?'.http_build_query(array('alert' => base64_encode($this->getAlert())), '', '&');
        return $app->redirect($url);
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
            $check = $this->Basket->basket;
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
                        unset($this->Basket->basket[$key]);
                        $this->setAlert('Removed article <strong>%article%</strong> from your shopping basket',
                            array('%article%' => $item['article_name']), self::ALERT_TYPE_SUCCESS);
                        $removed = true;
                    }
                    else {
                        $this->Basket->basket[$key]['quantity'] = $GET['basket'][$key];
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
        if (empty($this->Basket->basket)) {
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
                'alert' => $this->getAlert(),
                'config' => self::$config,
                'permalink_base_url' => CMS_URL.self::$config['permanentlink']['directory'],
                'form' => $form->createView(),
                'order' => $order,
                'shop_url' => CMS_URL.$base['target_page_link']
            ));
    }

    /**
     * Get the form to change article quantities in the basket
     *
     * @param array $basket
     */
    protected function getBasketForm()
    {
        $form = $this->app['form.factory']->createBuilder('form', null, array('csrf_protection' => false));
        $Basket = $this->Basket;
        foreach ($Basket::basket as $key => $item) {
            $form->add($key, 'text', array(
                'data' => $item['quantity']
            ));
        }

        return $form->getForm();
    }



}
