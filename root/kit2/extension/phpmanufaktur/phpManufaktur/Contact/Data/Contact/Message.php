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

class Message
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
        self::$table_name = FRAMEWORK_TABLE_PREFIX.'contact_message';
    }

    /**
     * Create the NOTE table
     *
     * @throws \Exception
     */
    public function createTable()
    {
        $table = self::$table_name;
        $table_contact = FRAMEWORK_TABLE_PREFIX.'contact_contact';

        $SQL = <<<EOD
    CREATE TABLE IF NOT EXISTS `$table` (
        `message_id` INT(11) NOT NULL AUTO_INCREMENT,
        `contact_id` INT(11) NOT NULL DEFAULT '-1',
        `application_name` VARCHAR(80) NOT NULL DEFAULT 'NONE',
        `application_marker_type` VARCHAR(80) NOT NULL DEFAULT '',
        `application_marker_id` INT(11) NOT NULL DEFAULT '-1',
        `message_title` VARCHAR(255) NOT NULL DEFAULT '',
        `message_type` ENUM('TEXT', 'HTML') NOT NULL DEFAULT 'TEXT',
        `message_content` TEXT NOT NULL,
        `message_date` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
        `message_status` ENUM('ACTIVE', 'LOCKED', 'DELETED') NOT NULL DEFAULT 'ACTIVE',
        `message_timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP ,
        PRIMARY KEY (`message_id`) ,
        INDEX `contact_id` (`contact_id` ASC) ,
        CONSTRAINT
            FOREIGN KEY (`contact_id` )
            REFERENCES `$table_contact` (`contact_id` )
            ON DELETE CASCADE
        )
    COMMENT='The notes for the contact table'
    ENGINE=InnoDB
    AUTO_INCREMENT=1
    DEFAULT CHARSET=utf8
    COLLATE='utf8_general_ci'
EOD;
        try {
            $this->app['db']->query($SQL);
            $this->app['monolog']->addInfo("Created table 'contact_message'", array(__METHOD__, __LINE__));
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Drop table - switching check for foreign keys off before executing
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
            $this->app['monolog']->addInfo("Drop table 'contact_message'", array(__METHOD__, __LINE__));
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Return a default (empty) NOTE record
     *
     * @return array
     */
    public function getDefaultRecord()
    {
        return array(
            'message_id' => -1,
            'contact_id' => -1,
            'application_name' => 'NONE',
            'application_marker_type' => '',
            'application_marker_id' => -1,
            'message_title' => '',
            'message_type' => 'TEXT',
            'message_content' => '',
            'message_date' => '0000-00-00 00:00:00',
            'message_status' => 'ACTIVE',
            'message_timestamp' => '0000-00-00 00:00:00'
        );
    }

    /**
     * Select a message by the given note_id
     * Return FALSE if the record does not exists
     *
     * @param integer $message_id
     * @throws \Exception
     * @return multitype:array|boolean
     */
    public function select($message_id)
    {
        try {
            $SQL = "SELECT * FROM `".self::$table_name."` WHERE `message_id`='$message_id'";
            $result = $this->app['db']->fetchAssoc($SQL);
            if (is_array($result) && isset($result['message_id'])) {
                $message = array();
                foreach ($result as $key => $value) {
                    $message[$key] = is_string($value) ? $this->app['utils']->unsanitizeText($value) : $value;
                }
                return $message;
            }
            else {
                return false;
            }
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Insert a new record in the MESSAGE table
     *
     * @param array $data
     * @param reference integer $message_id
     * @throws \Exception
     */
    public function insert($data, &$message_id=null)
    {
        try {
            $insert = array();
            $TextOnly = (isset($data['message_type']) && ($data['message_type'] === 'HTML')) ? false : true;
            foreach ($data as $key => $value) {
                if (($key == 'message_id') || ($key == 'message_timestamp')) continue;
                    if ($TextOnly && ($key === 'message_content')) {
                        $value = strip_tags($value);
                    }
                $insert[$this->app['db']->quoteIdentifier($key)] = is_string($value) ? $this->app['utils']->sanitizeText($value) : $value;
            }
            $this->app['db']->insert(self::$table_name, $insert);
            $message_id = $this->app['db']->lastInsertId();
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Return all MESSAGES for the given Contact ID
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
            $SQL = "SELECT * FROM `".self::$table_name."` WHERE `contact_id`='$contact_id' AND `message_status`{$status_operator}'{$status}'";
            $results = $this->app['db']->fetchAll($SQL);
            if (is_array($results)) {
                $message = array();
                $level = 0;
                foreach ($results as $result) {
                    foreach ($result as $key => $value) {
                        $message[$level][$key] = is_string($value) ? $this->app['utils']->unsanitizeText($value) : $value;
                    }
                    $level++;
                }
                return $message;
            }
            else {
                return false;
            }
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Mark the given $message_id as deleted but does not delete the record physically
     *
     * @param integer $message_id
     * @throws \Exception
     */
    public function delete($message_id)
    {
        try {
            $this->app['db']->update(self::$table_name, array('message_status' => 'DELETED'), array('message_id' => $message_id));
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Update the message record for the given ID
     *
     * @param array $data
     * @param integer $message_id
     * @throws \Exception
     */
    public function update($data, $message_id)
    {
        try {
            $update = array();
            foreach ($data as $key => $value) {
                if (($key == 'message_id') || ($key == 'message_timestamp')) continue;
                $update[$this->app['db']->quoteIdentifier($key)] = is_string($value) ? $this->app['utils']->sanitizeText($value) : $value;
            }
            if (!empty($update)) {
                $this->app['db']->update(self::$table_name, $update, array('message_id' => $message_id));
            }
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }
}
