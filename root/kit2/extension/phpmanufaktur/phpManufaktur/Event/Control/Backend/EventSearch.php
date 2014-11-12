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
use phpManufaktur\Event\Data\Event\EventSearch as Search;

class EventSearch extends Backend {

    protected static $route = null;
    protected static $columns = null;
    protected $EventData = null;

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
        } catch (\Exception $e) {
            // the config file does not exists - use all available columns
            self::$columns = $this->EventData->getColumns();
        }
        self::$route =  array(
            'edit' => '/admin/event/edit/id/{event_id}?usage='.self::$usage,
            'search' => '/admin/event/search?usage='.self::$usage
        );
    }

    /**
     * Execute class as controller
     *
     * @param Application $app
     * @return string rendered Event List
     */
    public function exec(Application $app)
    {
        $this->initialize($app);

        if (null == ($search = $this->app['request']->get('search', null))) {
            $this->setAlert('Please specify a search term!', array(), self::ALERT_TYPE_INFO);
            $events = array();
        }
        else {
            $SearchData = new Search($app);
            if (false === ($events = $SearchData->search($search))) {
                $events = array();
                $this->setAlert('No hits for the search term <i>%search%</i>!',
                    array('%search%' => $search), self::ALERT_TYPE_INFO);
            }
            else {
                $this->setAlert('%count% hits for the search term </i>%search%</i>.',
                    array('%count%' => count($events), '%search%' => $search), self::ALERT_TYPE_SUCCESS);
            }
        }

        return $this->app['twig']->render($this->app['utils']->getTemplateFile(
            '@phpManufaktur/Event/Template', 'admin/list.search.twig'),
            array(
                'usage' => self::$usage,
                'toolbar' => $this->getToolbar('event_list'),
                'alert' => $this->getAlert(),
                'events' => $events,
                'columns' => self::$columns,
                'route' => self::$route,
            ));
    }

}
