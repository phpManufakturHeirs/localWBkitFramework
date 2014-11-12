<?php

/**
 * Event
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de/Event
 * @copyright 2013 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\Event\Data\Import\kitEvent;

use Silex\Application;
use phpManufaktur\Contact\Data\Import\KeepInTouch\KeepInTouch;

class kitEvent
{

    protected $app = null;
    protected $KeepInTouch = null;

    public function __construct(Application $app)
    {
        $this->app = $app;
        $this->KeepInTouch = new KeepInTouch($app);
    }

    /**
     * Check if the given $table exists
     *
     * @param string $table
     * @throws \Exception
     * @return boolean
     */
    protected function tableExists($table)
    {
        try {
            $query = $this->app['db']->query("SHOW TABLES LIKE '$table'");
            return (false !== ($row = $query->fetch())) ? true : false;
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Check if table mod_kit_event exists
     *
     * @return boolean
     */
    public function existsKitEvent()
    {
        return $this->tableExists(CMS_TABLE_PREFIX.'mod_kit_event');
    }

    /**
     * Check the installed kitEvent Release
     *
     * @throws \Exception
     * @return string release number
     */
    public function getKitEventRelease()
    {
        try {
            $SQL = "SELECT `version` FROM `".CMS_TABLE_PREFIX."addons` WHERE `name`='kitEvent'";
            return $this->app['db']->fetchColumn($SQL);
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    public function getAllKitEventIDs($start_id=1)
    {
        try {
            $SQL = "SELECT `evt_id` FROM `".CMS_TABLE_PREFIX."mod_kit_event` WHERE (`evt_status`='1' OR `evt_status`='0') ".
                "AND `evt_id` >= '$start_id' ORDER BY `evt_id` ASC";
            return $this->app['db']->fetchAll($SQL);
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Count all active and locked kitEvent records
     *
     * @throws \Exception
     * @return integer number of KIT records
     */
    public function countKitEventRecords()
    {
        try {
            $SQL = "SELECT COUNT(`evt_id`) AS total FROM `".CMS_TABLE_PREFIX."mod_kit_event` WHERE (`evt_status`='1' OR `evt_status`='0')";
            return $this->app['db']->fetchColumn($SQL);
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    public function getEventRecord($event_id)
    {
        try {
            $SQL = "SELECT * FROM `".CMS_TABLE_PREFIX."mod_kit_event` WHERE `evt_id`='$event_id' AND (`evt_status`='1' OR `evt_status`='0')";
            $result = $this->app['db']->fetchAssoc($SQL);
            if (!is_array($result)) {
                return false;
            }
            $event = array();
            foreach ($result as $key => $value) {
                $event[$key] = is_string($value) ? $this->app['utils']->unsanitizeText($value) : $value;
            }
            return $event;
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Get the kitEvent Groups
     *
     * @throws \Exception
     * @return multitype:multitype:unknown
     */
    public function getEventGroups()
    {
        try {
            $SQL = "SELECT * FROM `".CMS_TABLE_PREFIX."mod_kit_event_group` WHERE (`group_status`='1' OR `group_status`='0')";
            $results = $this->app['db']->fetchAll($SQL);
            $groups = array();
            foreach ($results as $result) {
                $group = array();
                foreach ($result as $key => $value) {
                    $group[$key] = is_string($value) ? $this->app['utils']->unsanitizeText($value) : $value;
                }
                $groups[] = $group;
            }
            return $groups;
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Try to get the Event Group ID from the given kitEvent Group ID
     *
     * @param integer $grp_id
     * @throws \Exception
     * @return integer Event Group ID or FALSE if fail
     */
    public function getEventGroupID4kitEventGroupID($grp_id)
    {
        try {
            $SQL = "SELECT `group_name` FROM `".CMS_TABLE_PREFIX."mod_kit_event_group` WHERE `group_id`='$grp_id'";
            $name = $this->app['db']->fetchColumn($SQL);
            $group_name = $this->KeepInTouch->createIdentifier($name);
            $SQL = "SELECT `group_id` FROM `".FRAMEWORK_TABLE_PREFIX."event_group` WHERE `group_name`='$group_name'";
            return $this->app['db']->fetchColumn($SQL);
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Get the event by the given item ID
     *
     * @param integer $item_id
     * @throws \Exception
     * @return boolean|array
     */
    public function getEventItem($item_id)
    {
        try {
            $SQL = "SELECT * FROM `".CMS_TABLE_PREFIX."mod_kit_event_item` WHERE `item_id`='$item_id'";
            $result = $this->app['db']->fetchAssoc($SQL);
            if (!isset($result['item_id'])) {
                return false;
            }
            $item = array();
            foreach ($result as $key => $value) {
                $item[$key] = is_string($value) ? $this->app['utils']->unsanitizeText($value) : $value;
            }
            return $item;
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    public function isAlreadyImported($organizer_id, $location_id, $event_date_from, $event_date_to, $event_title)
    {
        try {
            $event = FRAMEWORK_TABLE_PREFIX.'event_event';
            $desc = FRAMEWORK_TABLE_PREFIX.'event_description';
            $SQL = "SELECT $event.event_id FROM `$event`, `$desc` WHERE $event.event_id=$desc.event_id AND $event.event_organizer='$organizer_id' AND ".
                "$event.event_location='$location_id' AND $event.event_date_from='$event_date_from' AND $event.event_date_to='$event_date_to' AND ".
                "$desc.description_title='$event_title'";
            $event_id = $this->app['db']->fetchColumn($SQL);
            return (is_numeric($event_id) && ($event_id > 0)) ? $event_id : false;
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

}
