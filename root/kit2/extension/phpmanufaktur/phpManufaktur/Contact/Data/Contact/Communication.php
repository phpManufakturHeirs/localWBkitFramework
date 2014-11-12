<?php

/**
 * Contact
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de/Contact
 * @copyright 2013 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\Contact\Data\Contact;

use Silex\Application;

class Communication
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
        self::$table_name = FRAMEWORK_TABLE_PREFIX.'contact_communication';
    }

    /**
     * Create the COMMNUNICATION table
     *
     * @throws \Exception
     */
    public function createTable()
    {
        $table = self::$table_name;
        $table_contact = FRAMEWORK_TABLE_PREFIX.'contact_contact';
        $table_communication_type = FRAMEWORK_TABLE_PREFIX.'contact_communication_type';
        $table_communication_usage = FRAMEWORK_TABLE_PREFIX.'contact_communication_usage';

        $foreign_key_1 = self::$table_name.'_ibfk_1';
        $foreign_key_2 = self::$table_name.'_ibfk_2';
        $foreign_key_3 = self::$table_name.'_ibfk_3';
        $foreign_key_4 = self::$table_name.'_ibfk_4';

        $SQL = <<<EOD
    CREATE TABLE IF NOT EXISTS `$table` (
      `communication_id` INT(11) NOT NULL AUTO_INCREMENT ,
      `contact_id` INT(11) NOT NULL DEFAULT '-1' ,
      `communication_type` VARCHAR(32) NOT NULL DEFAULT 'NONE' ,
      `communication_usage` VARCHAR(32) NOT NULL DEFAULT 'PRIMARY',
      `communication_value` TEXT NOT NULL ,
      `communication_status` ENUM('ACTIVE','LOCKED','DELETED') NOT NULL DEFAULT 'ACTIVE' ,
      `communication_timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP ,
      PRIMARY KEY (`communication_id`) ,
      INDEX `contact_id` (`contact_id` ASC) ,
      INDEX `communication_usage_idx` (`communication_usage` ASC) ,
      INDEX `communication_type_idx` (`communication_type` ASC) ,
      CONSTRAINT `$foreign_key_1`
        FOREIGN KEY (`contact_id` )
        REFERENCES `$table_contact` (`contact_id` )
        ON DELETE CASCADE,
      CONSTRAINT `$foreign_key_3`
        FOREIGN KEY (`communication_usage` )
        REFERENCES `$table_communication_usage` (`communication_usage_name` )
        ON DELETE CASCADE
        ON UPDATE CASCADE,
      CONSTRAINT `$foreign_key_4`
        FOREIGN KEY (`communication_type` )
        REFERENCES `$table_communication_type` (`communication_type_name` )
        ON DELETE CASCADE
        ON UPDATE CASCADE
    )
    COMMENT='The communication table'
    ENGINE=InnoDB
    AUTO_INCREMENT=1
    DEFAULT CHARSET=utf8
    COLLATE='utf8_general_ci'
EOD;
        try {
            $this->app['db']->query($SQL);
            $this->app['monolog']->addInfo("Created table 'contact_communication'", array(__METHOD__, __LINE__));
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
            $this->app['monolog']->addInfo("Drop table 'contact_communication'", array(__METHOD__, __LINE__));
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Return a default (empty) COMMUNICATION record
     *
     * @return array
     */
    public function getDefaultRecord()
    {
        return array(
            'communication_id' => -1,
            'contact_id' => -1,
            'communication_type' => 'NONE',
            'communication_usage' => 'OTHER',
            'communication_value' => '',
            'communication_status' => 'ACTIVE',
            'communication_timestamp' => '0000-00-00 00:00:00'
        );
    }

    /**
     * Check if a record with $contact_id, $type and $value exists.
     *
     * @param integer $contact_id
     * @param string $type
     * @param string $value
     * @throws \Exception
     * @return boolean
     */
    public function exists($contact_id, $type, $value)
    {
        try {
            $type = strtoupper($type);
            $value = $this->app['utils']->sanitizeVariable($value);
            $SQL = "SELECT * FROM `".self::$table_name."` WHERE `contact_id`='$contact_id' AND `communication_type`='$type' AND `communication_value`='$value'";
            $result = $this->app['db']->fetchAssoc($SQL);
            return (is_array($result) && isset($result['contact_id'])) ? true : false;
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Insert a new Communication record with the given $data
     *
     * @param array $data
     * @param reference integer $communication_id
     * @throws \Exception
     */
    public function insert($data, &$communication_id)
    {
        try {
            $insert = array();
            foreach ($data as $key => $value) {
                if (($key == 'communication_id') || ($key == 'communication_timestamp')) continue;
                $insert[$this->app['db']->quoteIdentifier($key)] = is_string($value) ? $this->app['utils']->sanitizeText($value) : $value;
            }
            $this->app['db']->insert(self::$table_name, $insert);
            $communication_id = $this->app['db']->lastInsertId();
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Update a communication record with the given data
     *
     * @param array $data
     * @param integer $communication_id
     * @throws \Exception
     */
    public function update($data, $communication_id)
    {
        try {
            $update = array();
            foreach ($data as $key => $value) {
                if (($key == 'communication_id') || ($key == 'communication_timestamp')) continue;
                $update[$this->app['db']->quoteIdentifier($key)] = is_string($value) ? $this->app['utils']->sanitizeText($value) : $value;
            }
            if (!empty($update)) {
                $this->app['db']->update(self::$table_name, $update, array('communication_id' => $communication_id));
            }
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Mark the given communication ID as DELETED but does not remove the
     * record physically
     *
     * @param integer $communication_id
     * @throws \Exception
     */
    public function delete($communication_id)
    {
        try {
            $this->app['db']->update(self::$table_name, array('communication_status' => 'DELETED'),
                array('communication_id' => $communication_id));
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Select the value of the record with the given communication ID
     *
     * @param integer $communication_id
     * @throws \Exception
     */
    public function selectValue($communication_id)
    {
        try {
            $SQL = "SELECT `communication_value` FROM `".self::$table_name."` WHERE `communication_id`='$communication_id'";
            return $this->app['db']->fetchColumn($SQL);
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    public function select($communication_id)
    {
        try {
            $SQL = "SELECT * FROM `".self::$table_name."` WHERE `communication_id`='$communication_id'";
            $result = $this->app['db']->fetchAssoc($SQL);
            if (!isset($result['communication_id'])) {
                return false;
            }
            $communication = array();
            foreach ($result as $key => $value) {
                $communication[$key] = is_string($value) ? $this->app['utils']->unsanitizeText($value) : $value;
            }
            return $communication;
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Return all COMMUNICATION records for the given Contact ID
     *
     * @param integer $contact_id
     * @param string $status
     * @param string $status_operator
     * @throws \Exception
     * @return array|boolean
     */
    public function selectByContactID($contact_id, $status='DELETED', $status_operator='!=')
    {
        try {
            $SQL = "SELECT * FROM `".self::$table_name."` WHERE `contact_id`='$contact_id' AND `communication_status`{$status_operator}'{$status}'";
            $results = $this->app['db']->fetchAll($SQL);
            if (is_array($results)) {
                $communication = array();
                $level = 0;
                foreach ($results as $result) {
                    foreach ($result as $key => $value) {
                        $communication[$level][$key] = is_string($value) ? $this->app['utils']->unsanitizeText($value) : $value;
                    }
                    $level++;
                }
                return $communication;
            }
            else {
                return false;
            }
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }


}
