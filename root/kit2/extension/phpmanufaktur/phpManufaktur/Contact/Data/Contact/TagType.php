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

class TagType
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
        self::$table_name = FRAMEWORK_TABLE_PREFIX.'contact_tag_type';
    }

    /**
     * Create the Tag table
     *
     * @throws \Exception
     */
    public function createTable()
    {
        $table = self::$table_name;
        $SQL = <<<EOD
    CREATE TABLE IF NOT EXISTS `$table` (
        `tag_type_id` INT(11) NOT NULL AUTO_INCREMENT,
        `tag_name` VARCHAR(32) NOT NULL DEFAULT '',
        `tag_description` VARCHAR(255) NOT NULL DEFAULT '',
        `tag_timestamp` TIMESTAMP,
        PRIMARY KEY (`tag_type_id`),
        UNIQUE (`tag_name`)
        )
    COMMENT='The tags types for the contact table'
    ENGINE=InnoDB
    AUTO_INCREMENT=1
    DEFAULT CHARSET=utf8
    COLLATE='utf8_general_ci'
EOD;
        try {
            $this->app['db']->query($SQL);
            $this->app['monolog']->addInfo("Created table 'contact_tag_type'", array(__METHOD__, __LINE__));
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
            $this->app['monolog']->addInfo("Deleted table 'contact_tag_type'", array(__METHOD__, __LINE__));
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    public function getDefaultRecord()
    {
        return array(
            'tag_type_id' => -1,
            'tag_name' => '',
            'tag_description' => '',
            'tag_timestamp' => '0000-00-00 00:00:00'
        );
    }

    /**
     * Select a TagType record by the given tag_type_id
     * Return FALSE if the record does not exists
     *
     * @param integer $tag_type_id
     * @throws \Exception
     * @return multitype:array|boolean
     */
    public function select($tag_type_id)
    {
        try {
            $SQL = "SELECT * FROM `".self::$table_name."` WHERE `tag_type_id`='$tag_type_id'";
            $result = $this->app['db']->fetchAssoc($SQL);
            if (is_array($result) && isset($result['tag_type_id'])) {
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

    /**
     * Insert a new TAG type record
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
                if (($key == 'tag_type_id') || ($key == 'tag_timestamp')) continue;
                $insert[$this->app['db']->quoteIdentifier($key)] = is_string($value) ? $this->app['utils']->unsanitizeText($value) : $value;
            }
            $this->app['db']->insert(self::$table_name, $insert);
            $tag_id = $this->app['db']->lastInsertId();
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Check if the desired TAG Type already existst. Optionally exclude the
     * given tag id from the check
     *
     * @param string $tag_name
     * @param integer $exclude_tag_id
     * @throws \Exception
     * @return boolean
     */
    public function existsTag($tag_name, $exclude_tag_id=null)
    {
        try {
            $SQL = "SELECT `tag_name` FROM `".self::$table_name."` WHERE `tag_name`='$tag_name'";
            if (is_numeric($exclude_tag_id)) {
                $SQL .= " AND `tag_id` != '$exclude_tag_id'";
            }
            $result = $this->app['db']->fetchColumn($SQL);
            return (strtoupper($result) == strtoupper($tag_name)) ? true : false;
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Delete physically the tag type with the given ID. This function also
     * delete all tags which are used in table contact_tag.
     *
     * @param integer $tag_type_id
     * @throws \Exception
     * @return boolean
     */
    public function delete($tag_type_id)
    {
        try {
            $this->app['db']->delete(self::$table_name, array('tag_type_id' => $tag_type_id));
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Update the tag type record for the given ID
     *
     * @param array $data
     * @param integer $tag_id
     * @throws \Exception
     */
    public function update($data, $tag_type_id)
    {
        try {
            $update = array();
            foreach ($data as $key => $value) {
                if (($key == 'tag_type_id') || ($key == 'tag_timestamp')) continue;
                $update[$this->app['db']->quoteIdentifier($key)] = is_string($value) ? $this->app['utils']->sanitizeText($value) : $value;
            }
            if (!empty($update)) {
                $this->app['db']->update(self::$table_name, $update, array('tag_type_id' => $tag_type_id));
            }
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Return a array with all tags, prepared for usage with TWIG
     *
     * @throws \Exception
     * @return array
     */
    public function getArrayForTwig()
    {
        try {
            $SQL = "SELECT * FROM `".self::$table_name."` ORDER BY `tag_name` ASC";
            $results = $this->app['db']->fetchAll($SQL);
            $tags = array();
            foreach ($results as $tag) {
                $tags[$tag['tag_name']] = ucfirst(strtolower($tag['tag_name']));
            }
            return $tags;
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Select all TAGS and return a ascending orderd list by identifier
     *
     * @throws \Exception
     * @return Ambigous <multitype:, unknown>
     */
    public function selectAll()
    {
        try {
            $SQL = "SELECT * FROM `".self::$table_name."` ORDER BY `tag_name` ASC";
            $results = $this->app['db']->fetchAll($SQL);
            $tags = array();
            $level = 0;
            foreach ($results as $result) {
                foreach ($result as $key => $value) {
                    $tags[$level][$key] = is_string($value) ? $this->app['utils']->unsanitizeText($value) : $value;
                }
                $level++;
            }
            return $tags;
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

}
