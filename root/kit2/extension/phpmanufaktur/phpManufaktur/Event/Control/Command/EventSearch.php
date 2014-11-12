<?php

/**
 * Event
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de/Event
 * @copyright 2013 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\Event\Control\Command;

use Silex\Application;
use phpManufaktur\Basic\Control\kitCommand\Basic;
use phpManufaktur\Event\Data\Event\Group;
use phpManufaktur\Event\Data\Event\EventSearch as Search;

class EventSearch extends Basic
{

    /**
     * Return the search results from search dialog
     *
     * @param Application $app
     * @return string
     */
    public function controllerSearch(Application $app)
    {
        // init BASIC
        $this->initParameters($app);

        if (null == ($search = $this->app['request']->get('search'))) {
            $this->setAlert('Please specify a search term!');
            return $this->controllerDialog($app);
        }
        else {
            $groups = $this->app['request']->get('groups');
            $SearchData = new Search($app);
            if (false === ($events = $SearchData->search($search, $groups, 'ACTIVE', '=', 'event_date_from', 'ASC', true, true))) {
                $this->setAlert('No hits for the search term <i>%search%</i>!', array('%search%' => $search));
                return $this->controllerDialog($app);
            }
            else {
                $this->setAlert('%count% hits for the search term </i>%search%</i>.', array('%count%' => count($events), '%search%' => $search));
            }
        }

        // get the parameters
        $parameter = $this->getCommandParameters();

        $group_ids = '';
        if (isset($parameter['group'])) {
            $EventGroup = new Group($app);
            if (strpos($parameter['group'], ',')) {
                $groups = explode(',', $parameter['group']);
                $grp_ids = array();
                foreach ($groups as $group) {
                    $group = trim($group);
                    if (empty($group)) continue;
                    $grp_ids[] = $EventGroup->getGroupID(trim($group));
                }
                $group_ids = implode(',', $grp_ids);
            }
            else {
                $group_ids = $EventGroup->getGroupID(trim($parameter['group']));
            }
        }

        // get the configuration
        $config = $this->app['utils']->readConfiguration(MANUFAKTUR_PATH.'/Event/config.event.json');

        return $this->app['twig']->render($this->app['utils']->getTemplateFile(
            '@phpManufaktur/Event/Template',
            "command/event.search.result.twig",
            $this->getPreferredTemplateStyle()),
            array(
                'basic' => $this->getBasicSettings(),
                'events' => $events,
                'parameter' => $parameter,
                'config' => $config,
                'groups' => $group_ids
            ));
    }

    /**
     * Show a dialog to search for events, called by class /Control/Command/Action
     *
     * @param Application $app
     * @return string search dialog
     */
    public function controllerDialog(Application $app)
    {
        // init BASIC
        $this->initParameters($app);

        // get the parameters
        $parameter = $this->getCommandParameters();

        $group_ids = '';
        if (isset($parameter['group'])) {
            $EventGroup = new Group($app);
            if (strpos($parameter['group'], ',')) {
                $groups = explode(',', $parameter['group']);
                $grp_ids = array();
                foreach ($groups as $group) {
                    $group = trim($group);
                    if (empty($group)) continue;
                    $grp_ids[] = $EventGroup->getGroupID(trim($group));
                }
                $group_ids = implode(',', $grp_ids);
            }
            else {
                $group_ids = $EventGroup->getGroupID(trim($parameter['group']));
            }
        }

        return $this->app['twig']->render($this->app['utils']->getTemplateFile(
            '@phpManufaktur/Event/Template',
            "command/event.search.twig",
            $this->getPreferredTemplateStyle()),
            array(
                'basic' => $this->getBasicSettings(),
                'parameter' => $parameter,
                'groups' => $group_ids,
            ));
    }
}
