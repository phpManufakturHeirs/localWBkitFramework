<?php

/**
 * Contact
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de/contact
 * @copyright 2013 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\Contact\Control\Dialog\Simple;

use Silex\Application;
use phpManufaktur\Contact\Control\ContactList as ContactListControl;

class ContactList extends Dialog {

    protected $ContactListControl = null;
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
            $this->initialize($options);
        }
    }

    /**
     * Initialize the Contact List with the given $options or default values
     *
     * @param string $options
     */
    protected function initialize($options=null)
    {
        $this->ContactListControl = new ContactListControl($this->app);

        $this->setOptions(array(
            'template' => array(
                'namespace' => isset($options['template']['namespace']) ? $options['template']['namespace'] : '@phpManufaktur/Contact/Template',
                'settings' => isset($options['template']['settings']) ? $options['template']['settings'] : 'backend/simple/list.contact.json',
                'message' => isset($options['template']['message']) ? $options['template']['message'] : 'backend/message.twig',
                'list' => isset($options['template']['list']) ? $options['template']['list'] : 'backend/simple/list.contact.twig'
            ),
            'route' => array(
                'pagination' => isset($options['route']['pagination']) ? $options['route']['pagination'] : '/admin/contact/simple/contact/list/page/{page}?order={order}&direction={direction}',
                'contact' => array(
                    'person' => isset($options['route']['contact']['person']) ? $options['route']['contact']['person'] : '/admin/contact/simple/contact/person/id/{contact_id}',
                    'company' => isset($options['route']['contact']['company']) ? $options['route']['contact']['company'] : '/admin/contact/simple/contact/company/id/{contact_id}',
                    'search' => isset($options['route']['contact']['search']) ? $options['route']['contact']['search'] : '/admin/contact/simple/search'
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
            self::$columns = $this->ContactListControl->getColumns();
            self::$rows_per_page = 100;
            self::$select_status = array('ACTIVE', 'LOCKED');
            self::$select_type = array('PERSON', 'COMPANY');
            self::$order_by = array('contact_id');
            self::$order_direction = 'ASC';
        }
        self::$current_page = 1;
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

        $list = $this->ContactListControl->getList(self::$current_page, self::$rows_per_page, self::$select_status, self::$max_pages, $order_by, $order_direction, self::$select_type);

        return $this->app['twig']->render($this->app['utils']->getTemplateFile(self::$options['template']['namespace'], self::$options['template']['list']),
            array(
                'message' => $this->getMessage(),
                'list' => $list,
                'columns' => self::$columns,
                'route' => self::$options['route'],
                'current_page' => self::$current_page,
                'last_page' => self::$max_pages,
                'order_by' => $order_by,
                'order_direction' => strtolower($order_direction),
                'extra' => $extra
            ));
    }
}
