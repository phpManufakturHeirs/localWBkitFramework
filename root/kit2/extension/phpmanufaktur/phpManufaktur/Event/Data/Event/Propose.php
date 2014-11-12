<?php

/**
 * Event
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de/Event
 * @copyright 2013 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\Event\Data\Event;

use Silex\Application;
use phpManufaktur\Contact\Data\Contact\Overview;

class Propose
{
    protected $app = null;
    protected static $table_name = null;

    /**
     * Constructor
     *
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
        self::$table_name = FRAMEWORK_TABLE_PREFIX.'event_propose';
    }

    /**
     * Create the EVENT table
     *
     * @throws \Exception
     */
    public function createTable()
    {
        $table = self::$table_name;
        $SQL = <<<EOD
    CREATE TABLE IF NOT EXISTS `$table` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `submitter_id` INT(11) NOT NULL DEFAULT '-1',
        `submitted_when` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
        `submitter_status` ENUM('PENDING', 'CONFIRMED', 'CANCELLED') NOT NULL DEFAULT 'PENDING',
        `submitter_status_when` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
        `submitter_guid` VARCHAR(128) NOT NULL DEFAULT '',
        `new_event_id` INT(11) NOT NULL DEFAULT '-1',
        `new_organizer_id` INT(11) NOT NULL DEFAULT '-1',
        `new_location_id` INT(11) NOT NULL DEFAULT '-1',
        `admin_status` ENUM('WAITING','PENDING','REJECTED','CONFIRMED') NOT NULL DEFAULT 'WAITING',
        `admin_status_when` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
        `admin_guid` VARCHAR(128) NOT NULL DEFAULT '',
        `command_url` TEXT NOT NULL,
        `timestamp` TIMESTAMP,
        PRIMARY KEY (`id`),
        INDEX (`new_event_id`, `new_organizer_id`, `new_location_id`),
        UNIQUE (`submitter_guid`, `admin_guid`)
        )
    COMMENT='Propose events'
    ENGINE=InnoDB
    AUTO_INCREMENT=1
    DEFAULT CHARSET=utf8
    COLLATE='utf8_general_ci'
EOD;
        try {
            $this->app['db']->query($SQL);
            $this->app['monolog']->addInfo("Created table 'event_propose'", array(__METHOD__, __LINE__));
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Delete table - switching check for foreign keys off before executing
     *
     * @throws \Exception
     */
    public function dropTable()
    {
        try {
            $table = self::$table_name;
            $SQL = <<<EOD
    SET foreign_key_checks = 0;
    DROP TABLE IF EXISTS `$table`;
    SET foreign_key_checks = 1;
EOD;
            $this->app['db']->query($SQL);
            $this->app['monolog']->addInfo("Drop table 'event_propose'", array(__METHOD__, __LINE__));
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Insert a new propose record.
     * Create a unique GUID for the submitter and the administrator
     *
     * @param array $data
     * @param integer reference $propose_id
     * @throws \Exception
     */
    public function insert($data, &$propose_id)
    {
        try {
            $insert = array();
            foreach ($data as $key => $value) {
                if (($key == 'id') || ($key == 'timestamp') ||
                    ($key == 'submitter_guid') || ($key == 'admin_guid')) {
                    continue;
                }
                $insert[$this->app['db']->quoteIdentifier($key)] = is_string($value) ? $this->app['utils']->sanitizeText($value) : $value;
            }
            $insert['submitter_guid'] = $this->app['utils']->createGUID();
            $insert['admin_guid'] = $this->app['utils']->createGUID();
            $this->app['db']->insert(self::$table_name, $insert);
            $propose_id = $this->app['db']->lastInsertId();
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Update a existing propose record
     *
     * @param integer $propose_id
     * @param array $data
     * @throws \Exception
     */
    public function update($propose_id, $data)
    {
        try {
            $update = array();
            foreach ($data as $key => $value) {
                if (($key == 'id') || ($key == 'timestamp')) continue;
                $update[$this->app['db']->quoteIdentifier($key)] = is_string($value) ? $this->app['utils']->sanitizeText($value) : $value;
            }
            if (!empty($update)) {
                $this->app['db']->update(self::$table_name, $update, array('id' => $propose_id));
            }
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Select a propose record by the given ID
     *
     * @param integer $propose_id
     * @throws \Exception
     * @return boolean|array
     */
    public function select($propose_id)
    {
        try {
            $SQL = "SELECT * FROM `".self::$table_name."` WHERE `id`='$propose_id'";
            $result = $this->app['db']->fetchAssoc($SQL);
            if (!isset($result['id'])) {
                return false;
            }
            $propose = array();
            foreach ($result as $key => $value) {
                $propose[$key] = is_string($value) ? $this->app['utils']->unsanitizeText($value) : $value;
            }
            return $propose;
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Select a propose record by the given submitter GUID
     *
     * @param string $guid
     * @throws \Exception
     * @return boolean|array
     */
    public function selectSubmitterGUID($guid)
    {
        try {
            $SQL = "SELECT * FROM `".self::$table_name."` WHERE `submitter_guid`='$guid'";
            $result = $this->app['db']->fetchAssoc($SQL);
            if (!isset($result['id'])) {
                return false;
            }
            $propose = array();
            foreach ($result as $key => $value) {
                $propose[$key] = is_string($value) ? $this->app['utils']->unsanitizeText($value) : $value;
            }
            return $propose;
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Select a propose record by the given administrator GUID
     *
     * @param string $guid
     * @throws \Exception
     * @return boolean|array
     */
    public function selectAdminGUID($guid)
    {
        try {
            $SQL = "SELECT * FROM `".self::$table_name."` WHERE `admin_guid`='$guid'";
            $result = $this->app['db']->fetchAssoc($SQL);
            if (!isset($result['id'])) {
                return false;
            }
            $propose = array();
            foreach ($result as $key => $value) {
                $propose[$key] = is_string($value) ? $this->app['utils']->unsanitizeText($value) : $value;
            }
            return $propose;
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Select the list of the last limited proposes for events with all additional
     * information about submitter, event, organizer and location
     *
     * @param integer $limit the list
     * @throws \Exception
     * @return array
     */
    public function selectList($limit=150)
    {
        try {
            $SQL = "SELECT * FROM `".self::$table_name."` ORDER BY `submitted_when` DESC LIMIT $limit";
            $results = $this->app['db']->fetchAll($SQL);
            $proposes = array();
            $EventData = new Event($this->app);
            $ContactOverview = new Overview($this->app);
            foreach ($results as $propose) {
                if (($propose['submitter_id'] < 1) || ($propose['new_event_id'] < 1)) {
                    // invalid record, skip!
                    $this->app['monolog']->addDebug(sprintf('The event propose record with the ID %d is invalid and should be removed.', $propose['id']));
                    continue;
                }
                if (false === ($event = $EventData->selectEvent($propose['new_event_id']))) {
                    throw new \Exception('Missing the event ID '.$propose['new_event_id']);
                }
                if (false === ($submitter = $ContactOverview->select($propose['submitter_id']))) {
                    throw new \Exception('Missing the submitter ID '.$propose['submitter_id']);
                }
                $organizer = null;
                if ($propose['new_organizer_id'] > 0) {
                    if (false === ($organizer = $ContactOverview->select($propose['new_organizer_id']))) {
                        throw new \Exception('Missing the organizer ID '.$propose['new_organizer_id']);
                    }
                }
                $location = null;
                if ($propose['new_location_id'] > 0) {
                    if (false === ($location = $ContactOverview->select($propose['new_location_id']))) {
                        throw new \Exception('Missing the location ID '.$propose['new_location_id']);
                    }
                }
                $proposes[] = array(
                    'propose' => $propose,
                    'event' => $event,
                    'submitter' => $submitter,
                    'organizer' => $organizer,
                    'location' => $location
                );
            }
            return $proposes;
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Select proses by the given submitter ID
     *
     * @param integer $submitter_id
     * @throws \Exception
     * @return Ambigous <boolean, multitype:>
     */
    public function selectBySubmitterID($submitter_id)
    {
        try {
            $SQL = "SELECT * FROM `".self::$table_name."` WHERE `submitter_id`='$submitter_id'";
            $results = $this->app['db']->fetchAll($SQL);
            $proposes = array();
            foreach ($results as $result) {
                foreach ($result as $key => $value) {
                    $propose[$key] = is_string($value) ? $this->app['utils']->unsanitizeText($value) : $value;
                }
            }
            return (!empty($proposes)) ? $proposes : false;
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    public function checkSubmitterCanEdit($submitter_id, $event_id) {
        try {
            $SQL = "SELECT `submitter_id` FROM `".self::$table_name."` WHERE `submitter_id`='$submitter_id' AND ".
                "`new_event_id`='$event_id' AND `submitter_status`='CONFIRMED' AND ".
                "`admin_status`='CONFIRMED'";
            $result = $this->app['db']->fetchColumn($SQL);
            return ($result == $submitter_id);
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }
}
