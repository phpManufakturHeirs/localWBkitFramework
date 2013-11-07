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

class Category
{

    protected $app = null;
    protected static $table_name = null;
    protected $CategoryType = null;

    /**
     * Constructor
     *
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
        self::$table_name = FRAMEWORK_TABLE_PREFIX.'contact_category';
        $this->CategoryType = new CategoryType($this->app);
    }

    /**
     * Create the CATEGORY TYPE table
     *
     * @throws \Exception
     */
    public function createTable()
    {
        $table = self::$table_name;
        $table_contact = FRAMEWORK_TABLE_PREFIX.'contact_contact';
        $table_category = FRAMEWORK_TABLE_PREFIX.'contact_category_type';

        $foreign_key_1 = self::$table_name.'_ibfk_1';
        $foreign_key_2 = self::$table_name.'_ibfk_2';

        $SQL = <<<EOD
    CREATE TABLE IF NOT EXISTS `$table` (
        `category_id` INT(11) NOT NULL AUTO_INCREMENT,
        `contact_id` INT(11) NOT NULL DEFAULT '-1',
        `category_type_name` VARCHAR(64) NOT NULL DEFAULT 'NO_CATEGORY',
        `category_type_id`INT(11) NOT NULL DEFAULT '-1',
        `category_timestamp` TIMESTAMP,
        PRIMARY KEY (`category_id`),
        INDEX `contact_id` (`contact_id`, `category_type_name`, `category_type_id`) ,
        CONSTRAINT `$foreign_key_1`
            FOREIGN KEY (`contact_id` )
            REFERENCES `$table_contact` (`contact_id` )
            ON DELETE CASCADE,
        CONSTRAINT `$foreign_key_2`
            FOREIGN KEY (`category_type_name` )
            REFERENCES `$table_category` (`category_type_name` )
            ON DELETE CASCADE
        )
    COMMENT='The category table for the contacts'
    ENGINE=InnoDB
    AUTO_INCREMENT=1
    DEFAULT CHARSET=utf8
    COLLATE='utf8_general_ci'
EOD;
        try {
            $this->app['db']->query($SQL);
            $this->app['monolog']->addInfo("Created table 'contact_category_type'", array(__METHOD__, __LINE__));
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
            'category_id' => -1,
            'contact_id' => -1,
            'category_type_name' => '',
            'category_type_id' => -1,
            'category_timestamp' => '0000-00-00 00:00:00'
        );
    }

    /**
     * Return all CATEGORIES for the given Contact ID
     *
     * @param integer $contact_id
     * @throws \Exception
     * @return array|boolean
     */
    public function selectByContactID($contact_id)
    {
        try {
            $SQL = "SELECT * FROM `".self::$table_name."` WHERE `contact_id`='$contact_id'";
            $results = $this->app['db']->fetchAll($SQL);
            if (is_array($results)) {
                $category = array();
                $level = 0;
                foreach ($results as $result) {
                    foreach ($result as $key => $value) {
                        $category[$level][$key] = is_string($value) ? $this->app['utils']->unsanitizeText($value) : $value;
                    }
                    $level++;
                }
                return $category;
            }
            else {
                return false;
            }
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Insert a new CATEGORY record
     *
     * @param array $data
     * @param reference integer $category_id
     * @throws \Exception
     */
    public function insert($data, &$category_id=null)
    {
        try {
            $insert = array();
            foreach ($data as $key => $value) {
                if (($key == 'category_id') || ($key == 'category_timestamp')) continue;
                $insert[$key] = is_string($value) ? $this->app['utils']->unsanitizeText($value) : $value;
            }
            if (!isset($insert['category_type_id']) && !isset($insert['category_type_name'])) {
                throw new \Exception('The category type ID or the category type NAME must be set!');
            }
            if (!isset($insert['contact_id']) || ($insert['contact_id'] < 1)) {
                throw new \Exception('A valid contact ID must be set!');
            }
            if (isset($insert['category_type_id']) && !isset($insert['cateory_type_name'])) {
                $type = $this->CategoryType->select($insert['category_type_id']);
                $insert['category_type_name'] = $type['category_type_name'];
            }
            if (isset($insert['category_type_name']) && !isset($insert['category_type_id'])) {
                $type = $this->CategoryType->selectByName($insert['category_type_name']);
                $insert['category_type_id'] = $type['category_type_id'];
            }
            $this->app['db']->insert(self::$table_name, $insert);
            $category_id = $this->app['db']->lastInsertId();
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    public function delete($category_id)
    {
        try {
            $this->app['db']->delete(self::$table_name, array('category_id' => $category_id));
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    public function selectCategoryTypeID($category_id)
    {
        try {
            $SQL = "SELECT `category_type_id` FROM `".self::$table_name."` WHERE `category_id`='$category_id'";
            return $this->app['db']->fetchColumn($SQL);
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

}
