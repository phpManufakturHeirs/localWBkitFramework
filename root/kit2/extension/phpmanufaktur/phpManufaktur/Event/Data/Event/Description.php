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

class Description
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
        self::$table_name = FRAMEWORK_TABLE_PREFIX.'event_description';
    }

    /**
     * Create the EVENT table
     *
     * @throws \Exception
     */
    public function createTable()
    {
        $table = self::$table_name;
        $table_event = FRAMEWORK_TABLE_PREFIX.'event_event';
        $SQL = <<<EOD
    CREATE TABLE IF NOT EXISTS `$table` (
        `description_id` INT(11) NOT NULL AUTO_INCREMENT,
        `event_id` INT(11) NOT NULL DEFAULT '-1',
        `description_title` VARCHAR(255) NOT NULL DEFAULT '',
        `description_short` TEXT NOT NULL,
        `description_long` TEXT NOT NULL,
        `description_timestamp` TIMESTAMP,
        PRIMARY KEY (`description_id`),
        INDEX (`event_id`),
        CONSTRAINT
            FOREIGN KEY (`event_id`)
            REFERENCES $table_event (`event_id`)
            ON DELETE CASCADE
        )
    COMMENT='The descriptions table for Events'
    ENGINE=InnoDB
    AUTO_INCREMENT=1
    DEFAULT CHARSET=utf8
    COLLATE='utf8_general_ci'
EOD;
        try {
            $this->app['db']->query($SQL);
            $this->app['monolog']->addInfo("Created table 'event_description'", array(__METHOD__, __LINE__));
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
            $this->app['monolog']->addInfo("Drop table 'event_description'", array(__METHOD__, __LINE__));
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }
    /**
     * Get the default record for the DESCRIPTION
     *
     * @param integer $event_id
     * @return multitype:number string unknown
     */
    public function getDefaultRecord($event_id=-1)
    {
        return array(
            'description_id' => -1,
            'event_id' => $event_id,
            'description_title' => '',
            'description_short' => '',
            'description_long' => '',
            'description_timestamp' => '0000-00-00 00:00:00'
        );
    }

    /**
     * Insert a new description
     *
     * record
     *
     * @param array $data
     * @param reference integer $event_id
     * @throws \Exception
     */
    public function insert($data, &$description_id=null)
    {
        try {
            $insert = array();
            foreach ($data as $key => $value) {
                if (($key == 'description_id') || ($key == 'description_timestamp')) continue;
                $insert[$this->app['db']->quoteIdentifier($key)] = is_string($value) ? $this->app['utils']->sanitizeText($value) : $value;
            }
            $this->app['db']->insert(self::$table_name, $insert);
            $description_id = $this->app['db']->lastInsertId();
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }
}
