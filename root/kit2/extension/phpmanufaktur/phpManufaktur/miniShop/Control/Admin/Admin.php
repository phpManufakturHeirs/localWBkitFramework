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
use phpManufaktur\miniShop\Control\Configuration;
use phpManufaktur\Basic\Control\Pattern\Alert;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class Admin extends Alert
{

    protected static $usage = null;
    protected static $usage_param = null;
    protected static $config = null;

    /**
     * Initialize the class with the needed parameters
     *
     * @param Application $app
     */
    protected function initialize(Application $app)
    {
        parent::initialize($app);

        $cms = $this->app['request']->get('usage');
        self::$usage = is_null($cms) ? 'framework' : $cms;
        self::$usage_param = (self::$usage != 'framework') ? '?usage='.self::$usage : '';
        // set the locale from the CMS locale
        if (self::$usage != 'framework') {
            $app['translator']->setLocale($this->app['session']->get('CMS_LOCALE', 'en'));
        }
        $Configuration = new Configuration($app);
        self::$config = $Configuration->getConfiguration();
    }

    /**
     * Get the toolbar for all backend dialogs
     *
     * @param string $active dialog
     * @return array
     */
    public function getToolbar($active) {
        $toolbar = array();
        foreach (self::$config['nav_tabs']['order'] as $tab) {
            switch ($tab) {
                case 'about':
                    $toolbar[$tab] = array(
                        'name' => 'about',
                        'text' => $this->app['translator']->trans('About'),
                        'hint' => $this->app['translator']->trans('Information about the miniShop extension'),
                        'link' => FRAMEWORK_URL.'/admin/minishop/about',
                        'active' => ($active == 'about')
                    );
                    break;
                case 'base':
                    $toolbar[$tab] = array(
                        'name' => 'base',
                        'text' => $this->app['translator']->trans('Base configurations'),
                        'hint' => $this->app['translator']->trans('Define and edit base configurations for the miniShop'),
                        'link' => FRAMEWORK_URL.'/admin/minishop/base/list',
                        'active' => ($active == 'base')
                    );
                    break;
                case 'group':
                    $toolbar[$tab] = array(
                        'name' => 'group',
                        'text' => $this->app['translator']->trans('Article groups'),
                        'hint' => $this->app['translator']->trans('Define and edit the article groups for the miniShop'),
                        'link' => FRAMEWORK_URL.'/admin/minishop/group/list',
                        'active' => ($active == 'group')
                    );
                    break;
                case 'article':
                    $toolbar[$tab] = array(
                        'name' => 'article',
                        'text' => $this->app['translator']->trans('Article'),
                        'hint' => $this->app['translator']->trans('Create or edit article for the miniShop'),
                        'link' => FRAMEWORK_URL.'/admin/minishop/article/list',
                        'active' => ($active === 'article')
                    );
                    break;
                case 'orders':
                    $toolbar[$tab] = array(
                        'name' => 'orders',
                        'text' => $this->app['translator']->trans('Orders'),
                        'hint' => $this->app['translator']->trans('View all orders you have received'),
                        'link' => FRAMEWORK_URL.'/admin/minishop/order/list',
                        'active' => ($active == 'orders')
                    );
                    break;
                case 'contact_list':
                    $toolbar[$tab] = array(
                        'name' => 'contact_list',
                        'text' => $this->app['translator']->trans('Customer list'),
                        'hint' => $this->app['translator']->trans('List of all available contacts'),
                        'link' => FRAMEWORK_URL.'/admin/minishop/contact/list',
                        'active' => ($active == 'contact_list')
                    );
                    break;
                case 'contact_edit':
                    $toolbar[$tab] = array(
                        'name' => 'contact_edit',
                        'text' => ($active === 'contact_edit') ? $this->app['translator']->trans('Edit customer') : $this->app['translator']->trans('Create customer'),
                        'hint' => $this->app['translator']->trans('Create or edit a customer record'),
                        'link' => FRAMEWORK_URL.'/admin/minishop/contact/select',
                        'active' => ($active == 'contact_edit')
                    );
                    break;
            }
        }
        return $toolbar;
    }

    /**
     * Controller to select the default navigation tab.
     *
     * @param Application $app
     * @throws \Exception
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function ControllerSelectDefaultTab(Application $app)
    {
        $this->initialize($app);

        switch (self::$config['nav_tabs']['default']) {
            case 'about':
                $route = '/admin/minishop/about';
                break;
            case 'base':
                $route = '/admin/minishop/base/list';
                break;
            case 'group':
                $route = '/admin/minishop/group/list';
                break;
            case 'article':
                $route = '/admin/minishop/article/list';
                break;
            case 'orders':
                $route = '/admin/minishop/order/list';
                break;
            case 'contact_list':
                $route = '/admin/minishop/contact/list';
                break;
            case 'contact_edit':
                $route = '/admin/minishop/contact/select';
                break;
            default:
                throw new \Exception('Invalid default nav_tab in configuration: '.self::$config['nav_tabs']['default']);
        }

        $subRequest = Request::create($route, 'GET', array('usage' => self::$usage));
        return $app->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
    }
 }
