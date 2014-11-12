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

class Extra
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
        self::$table_name = FRAMEWORK_TABLE_PREFIX.'event_extra';
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
        $SQL = <<<EOD
    CREATE TABLE IF NOT EXISTS `$table` (
        `extra_id` INT(11) NOT NULL AUTO_INCREMENT,
        `extra_type_id` INT(11) DEFAULT NULL,
        `extra_type_name` VARCHAR(64) NOT NULL DEFAULT '',
        `group_id` INT(11) NOT NULL DEFAULT '-1',
        `event_id` INT(11) NOT NULL DEFAULT '-1',
        `extra_type_type` ENUM('TEXT','HTML','VARCHAR','INT','FLOAT','DATE','DATETIME') NOT NULL DEFAULT 'VARCHAR',
        `extra_text` TEXT NOT NULL,
        `extra_html` TEXT NOT NULL,
        `extra_varchar` VARCHAR(255) NOT NULL DEFAULT '',
        `extra_int` INT(11) NOT NULL DEFAULT '0',
        `extra_float` FLOAT NOT NULL DEFAULT '0',
        `extra_date` DATE NOT NULL DEFAULT '0000-00-00',
        `extra_datetime` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
        `extra_time` TIME NOT NULL DEFAULT '00:00:00',
        `extra_timestamp` TIMESTAMP,
        PRIMARY KEY (`extra_id`),
        INDEX (`group_id`)
        )
    COMMENT='The table for extra fields'
    ENGINE=InnoDB
    AUTO_INCREMENT=1
    DEFAULT CHARSET=utf8
    COLLATE='utf8_general_ci'
EOD;
        try {
            $this->app['db']->query($SQL);
            $this->app['monolog']->addInfo("Created table 'event_extra'", array(__METHOD__, __LINE__));
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
            $this->app['monolog']->addInfo("Drop table 'event_extra'", array(__METHOD__, __LINE__));
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Select all extra fields which are associated to the given group ID
     *
     * @param integer $group_id
     * @throws \Exception
     * @return array selected extra records
     */
    public function selectByGroupID($group_id)
    {
        try {
            $SQL = "SELECT * FROM `".self::$table_name."` WHERE `group_id`='$group_id'";
            $results = $this->app['db']->fetchAll($SQL);
            $extras = array();
            foreach ($results as $extra) {
                $record = array();
                foreach ($extra as $key => $value) {
                    $record[$key] = is_string($value) ? $this->app['utils']->unsanitizeText($value) : $value;
                }
                $extras[] = $record;
            }
            return $extras;
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Insert a new extra record
     *
     * @param array $data
     * @param reference integer $extra_id
     * @throws \Exception
     */
    public function insert($data, &$extra_id=null)
    {
        try {
            $insert = array();
            foreach ($data as $key => $value) {
                if (($key == 'extra_id') || ($key == 'extra_timestamp')) continue;
                $insert[$this->app['db']->quoteIdentifier($key)] = is_string($value) ? $this->app['utils']->unsanitizeText($value) : $value;
            }
            $this->app['db']->insert(self::$table_name, $insert);
            $extra_id = $this->app['db']->lastInsertId();
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Select all extra fields associated to the given $event_id
     *
     * @param integer $event_id
     * @throws \Exception
     * @return array selected extra records
     */
    public function selectByEventID($event_id)
    {
        try {
            $SQL = "SELECT * FROM `".self::$table_name."` WHERE `event_id`='$event_id'";
            $results = $this->app['db']->fetchAll($SQL);
            $extras = array();
            foreach ($results as $extra) {
                $record = array();
                foreach ($extra as $key => $value) {
                    $record[$key] = is_string($value) ? $this->app['utils']->unsanitizeText($value) : $value;
                }
                switch ($record['extra_type_type']) {
                    case 'TEXT':
                        $record['extra_value'] = $record['extra_text']; break;
                    case 'HTML':
                        $record['extra_value'] = $record['extra_html']; break;
                    case 'VARCHAR';
                        $record['extra_value'] = $record['extra_varchar']; break;
                    case 'INT':
                        $record['extra_value'] = $record['extra_int']; break;
                    case 'FLOAT':
                        $record['extra_value'] = $record['extra_float']; break;
                    case 'DATE':
                        $record['extra_value'] = $record['extra_date']; break;
                    case 'DATETIME':
                        $record['extra_value'] = $record['extra_datetime']; break;
                    case 'TIME':
                        $record['extra_value'] = $record['extra_time']; break;
                    default:
                        throw new \Exception("Unknown extra field type: {$record['extra_type_type']}");
                }
                $extras[] = $record;
            }
            return $extras;
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Update all submitted data for the extra records associated with $event_id
     *
     * @param array $data
     * @param integer $event_id
     * @throws \Exception
     */
    public function updateByEventID($data, $event_id)
    {
        try {
            $extra_fields = $this->selectByEventID($event_id);
            $data_keys = array_keys($data);
            foreach ($extra_fields as $extra) {
                if (in_array('extra_'.$extra['extra_type_name'], $data_keys)) {

                    $extra_data = array();
                    switch ($extra['extra_type_type']) {
                        case 'TEXT':
                            $extra_data['extra_text'] = $this->app['utils']->sanitizeText(strip_tags($data['extra_'.$extra['extra_type_name']]));
                            break;
                        case 'HTML':
                            $extra_data['extra_html'] = $this->app['utils']->sanitizeText($data['extra_'.$extra['extra_type_name']]);
                            break;
                        case 'VARCHAR':
                            $extra_data['extra_varchar'] = $this->app['utils']->sanitizeText(strip_tags($data['extra_'.$extra['extra_type_name']]));
                            break;
                        case 'INT':
                            $extra_data['extra_int'] = $this->app['utils']->str2int($data['extra_'.$extra['extra_type_name']]);
                            break;
                        case 'FLOAT':
                            $extra_data['extra_float'] = $this->app['utils']->str2float($data['extra_'.$extra['extra_type_name']]);
                            break;
                        case 'DATE':
                            $extra_data['extra_date'] = date('Y-m-d', strtotime($data['extra_'.$extra['extra_type_name']]));
                            break;
                        case 'DATETIME':
                            $extra_data['extra_datetime'] = date('Y-m-d H:i:s', strtotime($data['extra_'.$extra['extra_type_name']]));
                            break;
                        case 'TIME':
                            $extra_data['extra_time'] = date('H:i:s', strtotime($data['extra_'.$extra['extra_type_name']]));
                            break;
                        default:
                            throw new \Exception("The extra type {$extra['extra_type_type']} is unknown!");
                    }
                    $this->app['db']->update(self::$table_name, $extra_data, array('extra_id' => $extra['extra_id']));
                }
            }
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

}
