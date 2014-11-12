<?php

/**
 * Event
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de/Event
 * @copyright 2013 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\Event\Control\Backend;

use Silex\Application;
use phpManufaktur\Event\Control\Backend\Backend;
use phpManufaktur\Event\Data\Event\Event as EventData;

class EventList extends Backend {

    protected static $event_id = -1;
    protected $EventData = null;
    protected static $route = null;
    protected static $columns = null;
    protected static $rows_per_page = null;
    protected static $select_status = null;
    protected static $order_by = null;
    protected static $order_direction = null;
    protected static $current_page = null;
    protected static $max_pages = null;

    /**
     * Constructor
     *
     * @param Application $app can be NULL
     */
    public function __construct(Application $app=null)
    {
        parent::__construct($app);
        if (!is_null($app)) {
            $this->initialize($app);
        }
    }

    /**
     * Initialize the parent class Backend and the class EventList
     *
     * @see \phpManufaktur\Event\Control\Backend\Backend::initialize()
     * @param Application $app
     */
    protected function initialize(Application $app)
    {
        parent::initialize($app);
        $this->EventData = new EventData($this->app);

        try {
            // search for the config file in the template directory
            $cfg_file = $this->app['utils']->getTemplateFile('@phpManufaktur/Event/Template', 'admin/list.event.json', '', true);
            // get the columns to show in the list
            $cfg = $this->app['utils']->readJSON($cfg_file);
            self::$columns = isset($cfg['columns']) ? $cfg['columns'] : $this->EventData->getColumns();
            self::$rows_per_page = isset($cfg['list']['rows_per_page']) ? $cfg['list']['rows_per_page'] : 100;
            self::$select_status = isset($cfg['list']['select_status']) ? $cfg['list']['select_status'] : array('ACTIVE', 'LOCKED');
            self::$order_by = isset($cfg['list']['order']['by']) ? $cfg['list']['order']['by'] : array('event_id');
            self::$order_direction = isset($cfg['list']['order']['direction']) ? $cfg['list']['order']['direction'] : 'ASC';
        } catch (\Exception $e) {
            // the config file does not exists - use all available columns
            self::$columns = $this->EventData->getColumns();
            self::$rows_per_page = 100;
            self::$select_status = array('ACTIVE', 'LOCKED');
            self::$order_by = array('event_id');
            self::$order_direction = 'ASC';
        }
        self::$current_page = 1;
        self::$route =  array(
            'pagination' => '/admin/event/list/page/{page}?order={order}&direction={direction}&usage='.self::$usage,
            'edit' => '/admin/event/edit/id/{event_id}?usage='.self::$usage,
            'search' => '/admin/event/search?usage='.self::$usage
        );
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

    protected function getList(&$list_page, $rows_per_page, $select_status=null, &$max_pages=null, $order_by=null, $order_direction='ASC')
    {
        // count rows
        $count_rows = $this->EventData->count($select_status, in_array('pack_recurring', self::$columns));
        if ($count_rows < 1) {
            // nothing to do ...
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

        return $this->EventData->selectList($limit_from, $rows_per_page, $select_status, $order_by, $order_direction, self::$columns);
    }

    /**
     * Execute class as controller
     *
     * @param Application $app
     * @return string rendered Event List
     * @todo cleanupEvents() disabled due problems - but function is needed!
     */
    public function exec(Application $app, $page=null)
    {
        $this->initialize($app);
        if (!is_null($page)) {
            $this->setCurrentPage($page);
        }

        // cleanup events
        // $this->EventData->cleanupEvents();

        $order_by = explode(',', $this->app['request']->get('order', implode(',', self::$order_by)));
        $order_direction = $this->app['request']->get('direction', self::$order_direction);

        $events = $this->getList(self::$current_page, self::$rows_per_page, self::$select_status, self::$max_pages, $order_by, $order_direction);

        return $this->app['twig']->render($this->app['utils']->getTemplateFile(
            '@phpManufaktur/Event/Template', 'admin/list.event.twig'),
            array(
                'usage' => self::$usage,
                'toolbar' => $this->getToolbar('event_list'),
                'events' => $events,
                'columns' => self::$columns,
                'current_page' => self::$current_page,
                'route' => self::$route,
                'order_by' => $order_by,
                'order_direction' => strtolower($order_direction),
                'last_page' => self::$max_pages,
                'alert' => $this->getAlert()
            ));
    }

}
