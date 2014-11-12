<?php

/**
 * kitFramework
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de
 * @copyright 2013 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\Basic\Control\Account\Dialog;

use Silex\Application;
use phpManufaktur\Basic\Control\Pattern\Alert;
use phpManufaktur\Basic\Data\Security\Users;

class AccountAdminList extends Alert
{
    protected $UserData = null;
    protected static $settings = null;
    protected static $usage = null;
    protected static $route = null;

    /**
     * (non-PHPdoc)
     * @see \phpManufaktur\Basic\Control\Pattern\Alert::initialize()
     */
    protected  function initialize(Application $app) {
        parent::initialize($app);
        $this->UserData = new Users($app);

        self::$usage = $app['request']->get('usage', 'framework');

        $cfg_file = $this->app['utils']->getTemplateFile('@phpManufaktur/Basic/Template', 'framework/accounts.list.json', '', true);
        self::$settings = $app['utils']->readJSON($cfg_file);

        self::$route =  array(
            'pagination' => '/admin/accounts/list/{page}?order={order}&direction={direction}&usage='.self::$usage,
            'edit' => '/admin/accounts/edit/{account_id}?usage='.self::$usage,
            'search' => '/admin/accounts/search?usage='.self::$usage
        );
    }

    /**
     * Generate a list of accounts for the given page
     *
     * @param integer reference $list_page
     * @param integer reference $max_pages
     * @return NULL|multitype:multitype:unknown
     */
    protected function getAccountList(&$list_page, &$max_pages, $order_by, $order_direction)
    {
        if (($count_rows = $this->UserData->count()) < 1) {
            // nothing to do ...
            return null;
        }

        $max_pages = ceil($count_rows/self::$settings['list']['rows_per_page']);
        if ($list_page < 1) {
            $list_page = 1;
        }
        if ($list_page > $max_pages) {
            $list_page = $max_pages;
        }
        $limit_from = ($list_page * self::$settings['list']['rows_per_page']) - self::$settings['list']['rows_per_page'];

        return $this->UserData->selectList(
            $limit_from,
            self::$settings['list']['rows_per_page'],
            self::$settings['columns'],
            $order_by,
            $order_direction
        );
    }

    /**
     * Controller to create a list with all kitFramework Accounts
     *
     * @param Application $app
     * @param integer $page
     */
    public function ControllerAccountList(Application $app, $page)
    {
        $this->initialize($app);

        $order_by = explode(',', $this->app['request']->get('order', implode(',', self::$settings['list']['order']['by'])));
        $order_direction = $this->app['request']->get('direction', self::$settings['list']['order']['direction']);

        $max_pages = 1;
        $accounts = $this->getAccountList($page, $max_pages, $order_by, $order_direction);

        return $this->app['twig']->render($this->app['utils']->getTemplateFile(
            '@phpManufaktur/Basic/Template', 'framework/accounts.list.twig'),
            array(
                'usage' => self::$usage,
                'accounts' => $accounts,
                'columns' => self::$settings['columns'],
                'current_page' => $page,
                'route' => self::$route,
                'order_by' => $order_by,
                'order_direction' => strtolower($order_direction),
                'last_page' => $max_pages,
                'alert' => $this->getAlert()
            ));
    }
}
