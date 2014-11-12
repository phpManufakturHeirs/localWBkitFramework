<?php

/**
 * Contact
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de/Contact
 * @copyright 2013 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\Contact\Control\Dialog\Simple;

use Silex\Application;
use phpManufaktur\Contact\Data\Contact\Overview;

class ContactList extends Dialog {

    protected $Overview = null;
    protected static $columns = null;
    protected static $rows_per_page = null;
    protected static $select_status = null;
    protected static $select_type = null;
    protected static $max_pages = null;
    protected static $current_page = null;
    protected static $order_by = null;
    protected static $order_direction = null;

    /**
     * Constructor
     *
     * @param Application $app
     */
    public function __construct(Application $app=null, $options=null)
    {
        parent::__construct($app);
        if (!is_null($app)) {
            $this->initialize($app, $options);
        }
        $this->Overview = new Overview($this->app);
    }

    /**
     * (non-PHPdoc)
     * @see \phpManufaktur\Contact\Control\Alert::initialize()
     */
    protected function initialize(Application $app, $options=null)
    {
        parent::initialize($app);

        $this->Overview = new Overview($app);

        $this->setOptions(array(
            'template' => array(
                'namespace' => isset($options['template']['namespace']) ? $options['template']['namespace'] : '@phpManufaktur/Contact/Template',
                'settings' => isset($options['template']['settings']) ? $options['template']['settings'] : 'pattern/admin/simple/list.contact.json',
                'list' => isset($options['template']['list']) ? $options['template']['list'] : 'pattern/admin/simple/list.contact.twig'
            ),
            'route' => array(
                'pagination' => isset($options['route']['pagination']) ? $options['route']['pagination'] : '/admin/contact/list/page/{page}?order={order}&direction={direction}',
                'contact' => array(
                    'person' => isset($options['route']['contact']['person']) ? $options['route']['contact']['person'] : '/admin/contact/person/id/{contact_id}',
                    'company' => isset($options['route']['contact']['company']) ? $options['route']['contact']['company'] : '/admin/contact/company/id/{contact_id}',
                    'search' => isset($options['route']['contact']['search']) ? $options['route']['contact']['search'] : '/admin/contact/search'
                )
            )
        ));

        try {
            // search for the config file in the template directory
            $cfg_file = $this->app['utils']->getTemplateFile(self::$options['template']['namespace'], self::$options['template']['settings'], '', true);
            // get the columns to show in the list
            $cfg = $this->app['utils']->readJSON($cfg_file);
            self::$columns = isset($cfg['columns']) ? $cfg['columns'] : $this->ContactListControl->getColumns();
            self::$rows_per_page = isset($cfg['list']['rows_per_page']) ? $cfg['list']['rows_per_page'] : 100;
            self::$select_status = isset($cfg['list']['select_status']) ? $cfg['list']['select_status'] : array('ACTIVE', 'LOCKED');
            self::$select_type = isset($cfg['list']['select_type']) ? $cfg['list']['select_type'] : array('PERSON', 'COMPANY');
            self::$order_by = isset($cfg['list']['order']['by']) ? $cfg['list']['order']['by'] : array('contact_id');
            self::$order_direction = isset($cfg['list']['order']['direction']) ? $cfg['list']['order']['direction'] : 'ASC';
        } catch (\Exception $e) {
            // the config file does not exists - use all available columns
            self::$columns = $this->Overview->getColumns();
            self::$rows_per_page = 100;
            self::$select_status = array('ACTIVE', 'LOCKED');
            self::$select_type = array('PERSON', 'COMPANY');
            self::$order_by = array('contact_id');
            self::$order_direction = 'ASC';
        }
        self::$current_page = 1;
    }

    /**
     * Get the contact list in paging mode
     *
     * @param integer $list_page
     * @param integer $rows_per_page
     * @param string $select_status
     * @param integer $max_pages
     * @param string $order_by
     * @param string $order_direction
     * @param array $select_type
     * @return NULL|Ambigous <multitype:, boolean, multitype:multitype:unknown  >
     */
    protected function getList(&$list_page, $rows_per_page, $select_status=null, &$max_pages=null, $order_by=null, $order_direction='ASC', $select_type=null)
    {
        // count rows
        $count_rows = $this->Overview->count($select_status, $select_type);
        if ($count_rows < 1) {
            return null;
        }
        $max_pages = ceil($count_rows/$rows_per_page);
        if ($list_page < 1) {
            $list_page = 1;
        }
        if ($list_page > $max_pages) {
            $list_page = $max_pages;
        }
        $limit_from = ($list_page * $rows_per_page) - $rows_per_page;

        return $this->Overview->selectList($limit_from, $rows_per_page, $select_status, $order_by, $order_direction, $select_type);
    }

    /**
     * Set the current page for the table
     *
     * @param integer $page
     */
    public function setCurrentPage($page)
    {
        self::$current_page = $page;
    }

    /**
     * Default controller for the contact list
     *
     * @param Application $app
     * @param string $page
     * @return string
     */
    public function controller(Application $app, $page=null)
    {
        $this->app = $app;
        $this->initialize();
        if (!is_null($page)) {
            $this->setCurrentPage($page);
        }
        return $this->exec();
    }

    /**
     * Return the complete contact list
     *
     * @param null|array $extra additional parameters for the template
     * @return string contact list
     */
    public function exec($extra=null)
    {
        $order_by = explode(',', $this->app['request']->get('order', implode(',', self::$order_by)));
        $order_direction = $this->app['request']->get('direction', self::$order_direction);

        $list = $this->getList(
            self::$current_page,
            self::$rows_per_page,
            self::$select_status,
            self::$max_pages,
            $order_by,
            $order_direction,
            self::$select_type);

        return $this->app['twig']->render($this->app['utils']->getTemplateFile(
            self::$options['template']['namespace'], self::$options['template']['list']),
            array(
                'alert' => $this->getAlert(),
                'list' => $list,
                'columns' => self::$columns,
                'route' => self::$options['route'],
                'current_page' => self::$current_page,
                'last_page' => self::$max_pages,
                'order_by' => $order_by,
                'order_direction' => strtolower($order_direction),
                'extra' => $extra,
                'usage' => isset($extra['usage']) ? $extra['usage'] : $this->app['request']->get('usage', 'framework')
            ));
    }
}
