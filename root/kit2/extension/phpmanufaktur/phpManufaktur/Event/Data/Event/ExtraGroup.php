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

class ExtraGroup
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
        self::$table_name = FRAMEWORK_TABLE_PREFIX.'event_extra_group';
    }

    /**
     * Create the EVENT table
     *
     * @throws \Exception
     */
    public function createTable()
    {
        $table = self::$table_name;
        $table_extra_type = FRAMEWORK_TABLE_PREFIX.'event_extra_type';
        $table_group = FRAMEWORK_TABLE_PREFIX.'event_group';
        $SQL = <<<EOD
    CREATE TABLE IF NOT EXISTS `$table` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `extra_type_id` INT(11) DEFAULT NULL,
        `group_id` INT(11) DEFAULT NULL,
        `timestamp` TIMESTAMP,
        PRIMARY KEY (`id`),
        CONSTRAINT
            FOREIGN KEY (`extra_type_id`)
            REFERENCES $table_extra_type(`extra_type_id`)
            ON DELETE CASCADE,
        CONSTRAINT
            FOREIGN KEY (`group_id`)
            REFERENCES $table_group (`group_id`)
            ON DELETE CASCADE
        )
    COMMENT='The table to assign extra fields to event groups'
    ENGINE=InnoDB
    AUTO_INCREMENT=1
    DEFAULT CHARSET=utf8
    COLLATE='utf8_general_ci'
EOD;
        try {
            $this->app['db']->query($SQL);
            $this->app['monolog']->addInfo("Created table 'event_extra_group'", array(__METHOD__, __LINE__));
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
            $this->app['monolog']->addInfo("Drop table 'event_extra_group'", array(__METHOD__, __LINE__));
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Insert a new record
     *
     * @param array $data
     * @param reference integer $id
     * @throws \Exception
     */
    public function insert($extra_type_id, $group_id, &$id=null)
    {
        try {
            $data = array(
                'extra_type_id' => $extra_type_id,
                'group_id' => $group_id
            );
            $this->app['db']->insert(self::$table_name, $data);
            $id = $this->app['db']->lastInsertId();
            // get the extra field description record
            $SQL = "SELECT * FROM `".FRAMEWORK_TABLE_PREFIX."event_extra_type` WHERE `extra_type_id`='$extra_type_id'";
            $extra_type = $this->app['db']->fetchAssoc($SQL);
            if (!isset($extra_type['extra_type_id'])) {
                throw new \Exception("Missing the type description ID $extra_type_id");
            }
            // now check if already exists records for events with the similiar group_id
            $SQL = "SELECT `event_id` FROM `".FRAMEWORK_TABLE_PREFIX."event_event` WHERE `group_id`='$group_id'";

            $events = $this->app['db']->fetchAll($SQL);
            foreach ($events as $event) {
                // create empty extra record for this event ID
                $data = array(
                    'extra_type_id' => $extra_type_id,
                    'extra_type_name' => $extra_type['extra_type_name'],
                    'group_id' => $group_id,
                    'event_id' => $event['event_id'],
                    'extra_type_type' => $extra_type['extra_type_type'],
                    'extra_text' => '',
                    'extra_html' => '',
                    'extra_varchar' => '',
                    'extra_int' => '0',
                    'extra_float' => '0',
                    'extra_date' => '0000-00-00',
                    'extra_datetime' => '0000-00-00 00:00:00',
                    'extra_time' => '00:00:00'
                );
                $this->app['db']->insert(FRAMEWORK_TABLE_PREFIX.'event_extra', $data);
            }
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Return all available Type IDs for the given $group_id
     *
     * @param integer $group_id
     * @throws \Exception
     * @return array with ExtraType IDs
     */
    public function selectTypeIDByGroupID($group_id)
    {
        try {
            $SQL = "SELECT `extra_type_id` FROM `".self::$table_name."` WHERE `group_id`='$group_id'";
            $results = $this->app['db']->fetchAll($SQL);
            $types = array();
            foreach ($results as $type) {
                $types[] = $type['extra_type_id'];
            }
            return $types;
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Delete a specified ExtraType ID for the given $group_id
     *
     * @param integer $extra_type_id
     * @param integer $group_id
     * @throws \Exception
     */
    public function deleteTypeByGroup($extra_type_id, $group_id)
    {
        try {
            // delete the extra type
            $this->app['db']->delete(self::$table_name, array(
                'extra_type_id' => $extra_type_id,
                'group_id' => $group_id
            ));
            // delete all associated records in table event_extra
            $this->app['db']->delete(FRAMEWORK_TABLE_PREFIX.'event_extra', array(
                'extra_type_id' => $extra_type_id,
                'group_id' => $group_id
            ));
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Select all records for the given $extra_type_id
     *
     * @param integer $extra_type_id
     * @throws \Exception
     */
    public function selectTypeID($extra_type_id)
    {
        try {
            $SQL = "SELECT * FROM `".self::$table_name."` WHERE `extra_type_id`='$extra_type_id'";
            return $this->app['db']->fetchAll($SQL);
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }
}
