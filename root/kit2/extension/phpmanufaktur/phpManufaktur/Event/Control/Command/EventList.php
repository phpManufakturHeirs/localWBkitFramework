<?php

/**
 * Event
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://addons.phpmanufaktur.de/event
 * @copyright 2013 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\Event\Control\Command;

use Silex\Application;
use phpManufaktur\Basic\Control\kitCommand\Basic;
use phpManufaktur\Event\Data\Event\EventFilter as EventFilterData;

class EventList extends Basic
{
    public function exec(Application $app)
    {
        // init BASIC
        $this->initParameters($app);

        // get the parameters
        $parameter = $this->getCommandParameters();

        // get the configuration
        $config = $this->app['utils']->readConfiguration(MANUFAKTUR_PATH.'/Event/config.event.json');

        $EventFilter = new EventFilterData($app);

        // the EventList does not support the EVENT_ID session
        $app['session']->remove('EVENT_ID');

        $filter = array();
        if (isset($parameter['filter']) && !empty($parameter['filter'])) {
            if (strpos($parameter['filter'], '|')) {
                $filters = explode('|', $parameter['filter']);
                foreach ($filters as $item) {
                    $item = trim($item);
                    if (strpos($item, '=')) {
                        list($key, $value) = explode('=', $item);
                        if (empty($value)) continue;
                        $filter[strtolower(trim($key))] = trim($value);
                    }
                }
            }
            elseif (strpos($parameter['filter'], '=')) {
                // only one filter
                list($key, $value) = explode('=', $parameter['filter']);
                $filter[strtolower(trim($key))] = trim($value);
            }
        }

        $parameter['rating'] = (isset($parameter['rating']) &&
            ((strtolower($parameter['rating']) == 'false') || ($parameter['rating'] == 0))) ? false : true;



        $messages = array();
        $SQL = '';
        if (false === ($events = $EventFilter->filter($filter, $messages, $SQL))) {
            foreach ($messages as $message) {
                $this->setMessage($message);
            }
            $this->setMessage('No results for this filter!');
        }

        $this->app['monolog']->addDebug("[EventFilter] SQL: $SQL",
            array(__METHOD__, __LINE__));
        $this->app['monolog']->addDebug("[EventFilter] Hits: ".count($events),
            array(__METHOD__, __LINE__));

        return $this->app['twig']->render($this->app['utils']->getTemplateFile(
            '@phpManufaktur/Event/Template',
            "command/event.list.default.twig",
            $this->getPreferredTemplateStyle()),
            array(
                'basic' => $this->getBasicSettings(),
                'events' => $events,
                'parameter' => $parameter,
                'config' => $config
            ));
    }
}
