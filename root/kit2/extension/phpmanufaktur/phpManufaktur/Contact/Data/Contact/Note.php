<?php

/**
 * Contact
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de/contact
 * @copyright 2013 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\Contact\Data\Contact;

use Silex\Application;

class Note
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
        self::$table_name = FRAMEWORK_TABLE_PREFIX.'contact_note';
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

        $foreign_key_1 = self::$table_name.'_ibfk_1';

        $SQL = <<<EOD
    CREATE TABLE IF NOT EXISTS `$table` (
        `note_id` INT(11) NOT NULL AUTO_INCREMENT,
        `contact_id` INT(11) NOT NULL DEFAULT '-1',
        `note_title` VARCHAR(255) NOT NULL DEFAULT '',
        `note_type` ENUM('TEXT', 'HTML') NOT NULL DEFAULT 'TEXT',
        `note_content` TEXT NOT NULL,
        `note_originator` VARCHAR(64) NOT NULL DEFAULT 'SYSTEM',
        `note_date` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
        `note_status` ENUM('ACTIVE', 'LOCKED', 'DELETED') NOT NULL DEFAULT 'ACTIVE',
        `note_timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP ,
        PRIMARY KEY (`note_id`) ,
        INDEX `contact_id` (`contact_id` ASC) ,
        CONSTRAINT `$foreign_key_1`
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
            $this->app['monolog']->addInfo("Created table 'contact_note'", array(__METHOD__, __LINE__));
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
            $this->app['monolog']->addInfo("Drop table 'contact_tag'", array(__METHOD__, __LINE__));
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
            'note_id' => -1,
            'contact_id' => -1,
            'note_title' => '',
            'note_type' => 'TEXT',
            'note_content' => '',
            'note_status' => 'ACTIVE',
            'note_timestamp' => '0000-00-00 00:00:00'
        );
    }

    /**
     * Select a note by the given note_id
     * Return FALSE if the record does not exists
     *
     * @param integer $contact_id
     * @throws \Exception
     * @return multitype:array|boolean
     */
    public function select($note_id)
    {
        try {
            $SQL = "SELECT * FROM `".self::$table_name."` WHERE `note_id`='$note_id'";
            $result = $this->app['db']->fetchAssoc($SQL);
            if (is_array($result) && isset($result['note_id'])) {
                $note = array();
                foreach ($result as $key => $value) {
                    $note[$key] = is_string($value) ? $this->app['utils']->unsanitizeText($value) : $value;
                }
                return $note;
            }
            else {
                return false;
            }
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Insert a new record in the NOTE table
     *
     * @param array $data
     * @param reference integer $note_id
     * @throws \Exception
     */
    public function insert($data, &$note_id=null)
    {
        try {
            $insert = array();
            $TextOnly = (isset($data['note_type']) && ($data['note_type'] === 'HTML')) ? false : true;
            foreach ($data as $key => $value) {
                if (($key == 'note_id') || ($key == 'note_timestamp')) continue;
                    if ($TextOnly && ($key === 'note_content')) {
                        $value = strip_tags($value);
                    }
                $insert[$this->app['db']->quoteIdentifier($key)] = is_string($value) ? $this->app['utils']->sanitizeText($value) : $value;
            }
            $this->app['db']->insert(self::$table_name, $insert);
            $note_id = $this->app['db']->lastInsertId();
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Return all NOTES for the given Contact ID
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
            $SQL = "SELECT * FROM `".self::$table_name."` WHERE `contact_id`='$contact_id' AND `note_status`{$status_operator}'{$status}'";
            $results = $this->app['db']->fetchAll($SQL);
            if (is_array($results)) {
                $note = array();
                $level = 0;
                foreach ($results as $result) {
                    foreach ($result as $key => $value) {
                        $note[$level][$key] = is_string($value) ? $this->app['utils']->unsanitizeText($value) : $value;
                    }
                    $level++;
                }
                return $note;
            }
            else {
                return false;
            }
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Mark the given $note_id as deleted but does not delete the record physically
     *
     * @param integer $note_id
     * @throws \Exception
     */
    public function delete($note_id)
    {
        try {
            $this->app['db']->update(self::$table_name, array('note_status' => 'DELETED'), array('note_id' => $note_id));
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Update the note record for the given ID
     *
     * @param array $data
     * @param integer $note_id
     * @throws \Exception
     */
    public function update($data, $note_id)
    {
        try {
            $update = array();
            foreach ($data as $key => $value) {
                if (($key == 'note_id') || ($key == 'note_timestamp')) continue;
                $update[$this->app['db']->quoteIdentifier($key)] = is_string($value) ? $this->app['utils']->sanitizeText($value) : $value;
            }
            if (!empty($update)) {
                $this->app['db']->update(self::$table_name, $update, array('note_id' => $note_id));
            }
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }
}
