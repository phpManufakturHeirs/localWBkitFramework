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
use phpManufaktur\miniShop\Data\Shop\Base as DataBase;
use phpManufaktur\Basic\Data\CMS\Page;

class Base extends Admin
{
    protected $dataBase = null;

    /**
     * (non-PHPdoc)
     * @see \phpManufaktur\miniShop\Control\Admin\Admin::initialize()
     */
    protected function initialize(Application $app)
    {
        parent::initialize($app);

        $this->dataBase = new DataBase($app);
    }

/**
     * Get the toolbar for all backend dialogs
     *
     * @param string $active dialog
     * @return array
     */
    public function getBaseToolbar($active) {
        $toolbar = array();
        $base_tabs = array('base', 'banking_account', 'paypal');
        foreach ($base_tabs as $tab) {
            switch ($tab) {
                case 'base':
                    $toolbar[$tab] = array(
                        'name' => 'base',
                        'text' => $this->app['translator']->trans('Base configurations'),
                        'hint' => $this->app['translator']->trans('Define and edit base configurations for the miniShop'),
                        'link' => FRAMEWORK_URL.'/admin/minishop/base/list'.self::$usage_param,
                        'active' => ($active == 'base')
                    );
                    break;
                case 'banking_account':
                    $toolbar[$tab] = array(
                        'name' => 'banking_account',
                        'text' => $this->app['translator']->trans('Banking account'),
                        'hint' => $this->app['translator']->trans('Specify your banking account for the usage with advance payment'),
                        'link' => FRAMEWORK_URL.'/admin/minishop/payment/advance_payment/config'.self::$usage_param,
                        'active' => ($active == 'banking_account')
                    );
                    break;
                case 'paypal':
                    $toolbar[$tab] = array(
                        'name' => 'paypal',
                        'text' => $this->app['translator']->trans('PayPal'),
                        'hint' => $this->app['translator']->trans('Configure your PayPal account'),
                        'link' => FRAMEWORK_URL.'/admin/minishop/payment/paypal/config'.self::$usage_param,
                        'active' => ($active == 'paypal')
                    );
                    break;
            }
        }
        return $toolbar;
    }

    /**
     * Get the form for the miniShop Base definition
     *
     * @param array $data
     */
    protected function getEditForm($data=array())
    {
        $locale_array = array();
        foreach (self::$config['locale'] as $locale) {
            $locale_name = $this->app['utils']->humanize($locale);
            $locale_array[strtoupper($locale)] = $this->app['translator']->trans($locale_name);
        }

        $currency_array = array();
        $currencies = self::$config['currency'];
        asort($currencies);
        foreach ($currencies as $currency) {
            $currency_name = $currency['name'];
            $currency_array[strtoupper($currency['iso'])] = $this->app['translator']->trans($currency_name);
        }

        $status_array = array();
        $types = $this->dataBase->getStatusTypes();
        foreach ($types as $type) {
            $type_name = $this->app['utils']->humanize($type);
            $status_array[$type] = $this->app['translator']->trans($type_name);
        }

        $payment_array = array();
        $types = $this->dataBase->getPaymentMethods();
        foreach ($types as $type) {
            $type_name = $this->app['utils']->humanize($type);
            $payment_array[$type] = $this->app['translator']->trans($type_name);
        }

        if (isset($data['payment_methods']) && (false !== strpos($data['payment_methods'], ','))) {
            $payment_data = explode(',', $data['payment_methods']);
        }
        elseif (isset($data['payment_methods']) && !empty($data['payment_methods'])) {
            $payment_data = array($data['payment_methods']);
        }
        else {
            $payment_data = array();
        }

        $decimal_separator = $this->app['translator']->trans('DECIMAL_SEPARATOR');
        $thousand_separator = $this->app['translator']->trans('THOUSAND_SEPARATOR');

        $article_value_added_tax = isset($data['article_value_added_tax']) ? $data['article_value_added_tax'] : 0;
        $order_minimum_price = isset($data['order_minimum_price']) ? $data['order_minimum_price'] : 0;
        $shipping_flatrate = isset($data['shipping_flatrate']) ? $data['shipping_flatrate'] : 0;
        $shipping_value_added_tax = isset($data['shipping_value_added_tax']) ? $data['shipping_value_added_tax'] : 0;

        $cmsPage = new Page($this->app);
        $pagelist = $cmsPage->getPageLinkList();
        $target_links = array();
        foreach ($pagelist as $link) {
            $target_links[$link['complete_link']] = $link['complete_link'];
        }

        $form = $this->app['form.factory']->createBuilder('form')
        ->add('id', 'hidden', array(
            'data' => isset($data['id']) ? $data['id'] : -1
        ))
        ->add('name', 'text', array(
            'data' => isset($data['name']) ? $data['name'] : ''
        ))
        ->add('status', 'choice', array(
            'choices' => $status_array,
            'empty_value' => false,
            'data' => isset($data['status']) ? $data['status'] : 'ACTIVE'
        ));

        if (isset($data['id']) && ($data['id'] > 0)) {
            $form->add('base_configuration_delete_checkbox', 'checkbox', array(
                'required' => false
            ));
        }
        else {
            $form->add('base_configuration_delete_checkbox', 'hidden');
        }

        $form->add('description', 'textarea', array(
            'data' => isset($data['description']) ? $data['description'] : '',
            'required' => false
        ))
        ->add('target_page_link', 'choice', array(
            'choices' => $target_links,
            'data' => isset($data['target_page_link']) ? $data['target_page_link'] : null,
            'empty_value' => '- please select -'
        ))
        ->add('payment_methods', 'choice', array(
            'choices' => $payment_array,
            'data' => $payment_data,
            'expanded' => true,
            'multiple' => true
        ))
        ->add('locale', 'choice', array(
            'choices' => $locale_array,
            'empty_value' => '- please select -',
            'data' => isset($data['locale']) ? $data['locale'] : null
        ))
        ->add('currency_iso', 'choice', array(
            'choices' => $currency_array,
            'empty_value' => '- please select -',
            'data' => isset($data['currency_iso']) ? $data['currency_iso'] : null
        ))
        ->add('article_value_added_tax', 'text', array(
            'data' => number_format($article_value_added_tax, 2, $decimal_separator, $thousand_separator)
        ))
        ->add('article_price_type', 'choice', array(
            'choices' => array(
                'NET_PRICE' => $this->app['translator']->trans('Net price'),
                'GROSS_PRICE' => $this->app['translator']->trans('Gross price')
            ),
            'empty_value' => false,
            'data' => isset($data['article_price_type']) ? $data['article_price_type'] : 'GROSS_PRICE'
        ))
        ->add('article_limit', 'text', array(
            'data' => isset($data['article_limit']) ? $data['article_limit'] : 99
        ))
        ->add('order_minimum_price', 'text', array(
            'data' => number_format($order_minimum_price, 2, $decimal_separator, $thousand_separator)
        ))
        ->add('shipping_type', 'choice', array(
            'choices' => array(
                'FLATRATE' => $this->app['translator']->trans('Flatrate for shipping and handling'),
                'ARTICLE' => $this->app['translator']->trans('Determined by each article'),
                'NONE' => $this->app['translator']->trans('No shipping')
            ),
            'empty_value' => '- please select -',
            'data' => isset($data['shipping_type']) ? $data['shipping_type'] : 'FLATRATE'
        ))
        ->add('shipping_flatrate', 'text', array(
            'data' => number_format($shipping_flatrate, 2, $decimal_separator, $thousand_separator)
        ))
        ->add('shipping_article', 'choice', array(
            'choices' => array(
                'HIGHEST' => $this->app['translator']->trans('select the highest shipping cost'),
                'LOWEST' => $this->app['translator']->trans('select the lowest shipping cost'),
                'SUM_UP' => $this->app['translator']->trans('sum-up the shipping costs')
            ),
            'empty_value' => '- please select -',
            'data' => isset($data['shipping_article']) ? $data['shipping_article'] : 'HIGHEST'
        ))
        /*
        ->add('shipping_value_added_tax', 'text', array(
            'data' => number_format($shipping_value_added_tax, 2, $decimal_separator, $thousand_separator)
        ))
        */
        ->add('terms_conditions_link', 'choice', array(
            'choices' => $target_links,
            'data' => isset($data['terms_conditions_link']) ? $data['terms_conditions_link'] : null,
            'empty_value' => '- please select -',
            'required' => false
        ))
        ;


        return $form->getForm();
    }

    /**
     * Controller to check the Base dialog
     *
     * @param Application $app
     */
    public function ControllerEditCheck(Application $app)
    {
        $this->initialize($app);

        $form = $this->getEditForm();
        $form->bind($this->app['request']);

        if ($form->isValid()) {
            // the form is valid
            $data = $form->getData();

            if ($data['base_configuration_delete_checkbox']) {
                // delete the article group
                $this->dataBase->delete($data['id']);
                $this->setAlert('The base configuration with the ID %id% has successfull deleted',
                    array('%id%' => $data['id']), self::ALERT_TYPE_SUCCESS);
                return $this->Controller($app);
            }
            else {
                // delete this item to avoid conflicts with the data table
                unset($data['base_configuration_delete_checkbox']);
            }

            // sanitize the name
            $data['name'] = strtoupper($this->app['utils']->sanitizeLink($data['name']));
            $data['name'] = str_replace('-', '_', $data['name']);
            // convert all prices to float values
            $data['article_value_added_tax'] = $this->app['utils']->str2float($data['article_value_added_tax']);
            $data['order_minimum_price'] = $this->app['utils']->str2float($data['order_minimum_price']);
            $data['shipping_flatrate'] = $this->app['utils']->str2float($data['shipping_flatrate']);
            //$data['shipping_value_added_tax'] = $this->app['utils']->str2float($data['shipping_value_added_tax']);

            if (in_array('ADVANCE_PAYMENT', $data['payment_methods']) && (
                empty(self::$config['banking_account']['bank_name']) ||
                empty(self::$config['banking_account']['iban']))) {
                // missing the banking account information
                $this->setAlert('Please configure your banking account before using the payment method <em>Advance Payment</em>.',
                    array(), self::ALERT_TYPE_WARNING);
                unset($data['payment_methods'][array_search('ADVANCE_PAYMENT', $data['payment_methods'])]);
            }

            if (in_array('PAYPAL', $data['payment_methods']) && (
                empty(self::$config['paypal']['email']) || empty(self::$config['paypal']['token']))) {
                // missing the paypal information
                $this->setAlert('Please configure your paypal account before using the payment method <em>PayPal</em>',
                    array(), self::ALERT_TYPE_WARNING);
                unset($data['payment_methods'][array_search('PAYPAL', $data['payment_methods'])]);
            }

            $data['payment_methods'] = implode(',', $data['payment_methods']);
            if (is_null($data['terms_conditions_link'])) {
                $data['terms_conditions_link'] = '';
            }

            if (empty($data['payment_methods'])) {
                // missing the payment method
                $this->setAlert('At least you must specify one payment method!', array(), self::ALERT_TYPE_WARNING);
            }
            elseif ($data['id'] < 1) {
                // this is a new record
                if ($this->dataBase->existsName($data['name'])) {
                    $this->setAlert('The name <strong>%name%</strong> is already in use, please select another one.',
                        array('%name%' => $data['name']), self::ALERT_TYPE_WARNING);
                }
                else {
                    // insert the record
                    $data['id'] = $this->dataBase->insert($data);
                    $this->setAlert('Succesful created a new miniShop Base', array(), self::ALERT_TYPE_SUCCESS);
                }
            }
            else {
                $old = $this->dataBase->select($data['id']);
                if (($old['name'] !== $data['name']) && $this->dataBase->existsName($data['name'], $data['id'])) {
                    $this->setAlert('The name <strong>%name%</strong> is already in use, please select another one.',
                        array('%name%' => $data['name']), self::ALERT_TYPE_WARNING);
                }
                else {
                    $this->dataBase->update($data['id'], $data);
                    $this->setAlert('The miniShop Base has successful updated.', array(), self::ALERT_TYPE_SUCCESS);
                }
            }
            // get the form with the actual data
            $form = $this->getEditForm($data);
        }
        else {
            // general error (timeout, CSFR ...)
            $this->setAlert('The form is not valid, please check your input and try again!', array(),
                self::ALERT_TYPE_DANGER, true, array('form_errors' => $form->getErrorsAsString(),
                    'method' => __METHOD__, 'line' => __LINE__));
        }

        return $this->app['twig']->render($this->app['utils']->getTemplateFile(
            '@phpManufaktur/miniShop/Template', 'admin/edit.base.twig'),
            array(
                'usage' => self::$usage,
                'usage_param' => self::$usage_param,
                'toolbar' => $this->getToolbar('base'),
                'base_toolbar' => $this->getBaseToolbar('base'),
                'alert' => $this->getAlert(),
                'form' => $form->createView()
            ));
    }

    /**
     * Controller to create or edit a base entry for the miniShop
     *
     * @param Application $app
     * @param integer $base_id
     */
    public function ControllerEdit(Application $app, $base_id)
    {
        $this->initialize($app);

        $data = array();
        if ($base_id > 0) {
            if (false === ($data = $this->dataBase->select($base_id))) {
                $this->setAlert('The record with the ID %id% does not exist!',
                    array('%id%' => $base_id), self::ALERT_TYPE_DANGER);
            }
        }
        $form = $this->getEditForm($data);

        return $this->app['twig']->render($this->app['utils']->getTemplateFile(
            '@phpManufaktur/miniShop/Template', 'admin/edit.base.twig'),
            array(
                'usage' => self::$usage,
                'usage_param' => self::$usage_param,
                'toolbar' => $this->getToolbar('base'),
                'base_toolbar' => $this->getBaseToolbar('base'),
                'alert' => $this->getAlert(),
                'form' => $form->createView()
            ));
    }

    /**
     * Show the base list for the miniShop
     *
     * @return string rendered dialog
     */
    public function Controller(Application $app)
    {
        $this->initialize($app);

        $bases = $this->dataBase->selectAll();

        return $this->app['twig']->render($this->app['utils']->getTemplateFile(
            '@phpManufaktur/miniShop/Template', 'admin/list.base.twig'),
            array(
                'usage' => self::$usage,
                'usage_param' => self::$usage_param,
                'toolbar' => $this->getToolbar('base'),
                'base_toolbar' => $this->getBaseToolbar('base'),
                'alert' => $this->getAlert(),
                'config' => self::$config,
                'bases' => $bases
            ));
    }

}
