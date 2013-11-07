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

class Tag
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
        self::$table_name = FRAMEWORK_TABLE_PREFIX.'contact_tag';
    }

    /**
     * Create the Tag table
     *
     * @throws \Exception
     */
    public function createTable()
    {
        $table = self::$table_name;
        $table_tag_type = FRAMEWORK_TABLE_PREFIX.'contact_tag_type';
        $table_contact = FRAMEWORK_TABLE_PREFIX.'contact_contact';
        $foreign_key_1 = self::$table_name.'_ibfk_1';
        $foreign_key_2 = self::$table_name.'_ibfk_2';
        $SQL = <<<EOD
    CREATE TABLE IF NOT EXISTS `$table` (
        `tag_id` INT(11) NOT NULL AUTO_INCREMENT,
        `contact_id` INT(11) NOT NULL DEFAULT '-1',
        `tag_name` VARCHAR(32) NOT NULL DEFAULT '',
        `tag_timestamp` TIMESTAMP,
        PRIMARY KEY (`tag_id`),
        INDEX (`contact_id`, `tag_name`),
        CONSTRAINT `$foreign_key_1`
            FOREIGN KEY (`tag_name`)
            REFERENCES `$table_tag_type` (`tag_name`)
            ON DELETE CASCADE
            ON UPDATE CASCADE,
        CONSTRAINT `$foreign_key_2`
            FOREIGN KEY (`contact_id`)
            REFERENCES `$table_contact` (`contact_id`)
            ON DELETE CASCADE
        )
    COMMENT='The tags for the contact table'
    ENGINE=InnoDB
    AUTO_INCREMENT=1
    DEFAULT CHARSET=utf8
    COLLATE='utf8_general_ci'
EOD;
        try {
            $this->app['db']->query($SQL);
            $this->app['monolog']->addInfo("Created table 'contact_tag'", array(__METHOD__, __LINE__));
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

    public function getDefaultRecord()
    {
        return array(
            'tag_id' => -1,
            'contact_id' => -1,
            'tag_name' => '',
            'tag_timestamp' => '0000-00-00 00:00:00'
        );
    }

    /**
     * Select a TAG record by the given tag_id
     * Return FALSE if the record does not exists
     *
     * @param integer $tag_id
     * @throws \Exception
     * @return multitype:array|boolean
     */
    public function select($tag_id)
    {
        try {
            $SQL = "SELECT * FROM `".self::$table_name."` WHERE `tag_id`='$tag_id'";
            $result = $this->app['db']->fetchAssoc($SQL);
            if (is_array($result) && isset($result['tag_id'])) {
                $contact = array();
                foreach ($result as $key => $value) {
                    $contact[$key] = is_string($value) ? $this->app['utils']->unsanitizeText($value) : $value;
                }
                return $contact;
            }
            else {
                return false;
            }
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    public function delete($tag_name)
    {
        try {
            $this->app['db']->delete(self::$table_name, array('tag_name' => $tag_name));
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Return all TAGS for the given Contact ID
     *
     * @param integer $contact_id
     * @param string $status
     * @param string $status_operator
     * @throws \Exception
     * @return array|boolean
     */
    public function selectByContactID($contact_id)
    {
        try {
            $SQL = "SELECT * FROM `".self::$table_name."` WHERE `contact_id`='$contact_id'";
            $results = $this->app['db']->fetchAll($SQL);
            if (is_array($results)) {
                $tags = array();
                $level = 0;
                foreach ($results as $result) {
                    foreach ($result as $key => $value) {
                        $tags[$level][$key] = is_string($value) ? $this->app['utils']->unsanitizeText($value) : $value;
                    }
                    $level++;
                }
                return $tags;
            }
            else {
                return false;
            }
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    public function isTagAlreadySet($tag_name, $contact_id)
    {
        try {
            $SQL = "SELECT `tag_name` FROM `".self::$table_name."` WHERE `contact_id`='$contact_id' && `tag_name`='$tag_name'";
            $result = $this->app['db']->fetchcolumn($SQL);
            return ($tag_name == $result) ? true : false;
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Insert a new TAG record
     *
     * @param array $data
     * @param reference integer $tag_id
     * @throws \Exception
     */
    public function insert($data, &$tag_id=null)
    {
        try {
            $insert = array();
            foreach ($data as $key => $value) {
                if (($key == 'tag_id') || ($key == 'tag_timestamp')) continue;
                $insert[$this->app['db']->quoteIdentifier($key)] = is_string($value) ? $this->app['utils']->unsanitizeText($value) : $value;
            }
            $this->app['db']->insert(self::$table_name, $insert);
            $tag_id = $this->app['db']->lastInsertId();
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

}
