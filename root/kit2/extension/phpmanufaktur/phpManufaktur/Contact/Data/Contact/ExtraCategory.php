<?php

/**
 * Event
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de/contact
 * @copyright 2013 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\Contact\Data\Contact;

use Silex\Application;

class ExtraCategory
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
        self::$table_name = FRAMEWORK_TABLE_PREFIX.'contact_extra_category';
        $this->CategoryType = new CategoryType($app);
    }

    /**
     * Create the table
     *
     * @throws \Exception
     */
    public function createTable()
    {
        $table = self::$table_name;
        $table_extra_type = FRAMEWORK_TABLE_PREFIX.'contact_extra_type';
        $table_category = FRAMEWORK_TABLE_PREFIX.'contact_category_type';
        $SQL = <<<EOD
    CREATE TABLE IF NOT EXISTS `$table` (
        `extra_category_id` INT(11) NOT NULL AUTO_INCREMENT,
        `extra_type_id` INT(11) DEFAULT NULL,
        `category_type_id` INT(11) DEFAULT NULL,
        `category_type_name` VARCHAR(64) NOT NULL DEFAULT '',
        `extra_category_timestamp` TIMESTAMP,
        PRIMARY KEY (`extra_category_id`),
        INDEX (`category_type_id`, `extra_type_id`, `category_type_name`),
        CONSTRAINT
            FOREIGN KEY (`extra_type_id`)
            REFERENCES $table_extra_type(`extra_type_id`)
            ON DELETE CASCADE,
        CONSTRAINT
            FOREIGN KEY (`category_type_id`)
            REFERENCES $table_category (`category_type_id`)
            ON DELETE CASCADE,
        CONSTRAINT
            FOREIGN KEY (`category_type_name`)
            REFERENCES $table_category (`category_type_name`)
            ON DELETE CASCADE
        )
    COMMENT='The table to assign extra fields to a contact category'
    ENGINE=InnoDB
    AUTO_INCREMENT=1
    DEFAULT CHARSET=utf8
    COLLATE='utf8_general_ci'
EOD;
        try {
            $this->app['db']->query($SQL);
            $this->app['monolog']->addInfo("Created table 'contact_extra_category'", array(__METHOD__, __LINE__));
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
            $this->app['monolog']->addInfo("Drop table 'contact_extra_category'", array(__METHOD__, __LINE__));
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Return all available Type IDs for the given $category_type_id
     *
     * @param integer $category_type_id
     * @throws \Exception
     * @return array with ExtraType IDs
     */
    public function selectTypeIDByCategoryTypeID($category_type_id)
    {
        try {
            $SQL = "SELECT `extra_type_id` FROM `".self::$table_name."` WHERE `category_type_id`='$category_type_id'";
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
     * Insert a new record
     *
     * @param integer $extra_type_id
     * @param integer $category_type_id
     * @param reference integer $id
     * @throws \Exception
     */
    public function insert($extra_type_id, $category_type_id, &$id=null)
    {
        try {
            // get the category type record for further information
            $type = $this->CategoryType->select($category_type_id);
            $data = array(
                'extra_type_id' => $extra_type_id,
                'category_type_id' => $category_type_id,
                'category_type_name' => $type['category_type_name']
            );
            $this->app['db']->insert(self::$table_name, $data);
            $id = $this->app['db']->lastInsertId();
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Delete a specified ExtraType ID for the given $category_type_id
     *
     * @param integer $extra_type_id
     * @param integer $category_type_id
     * @throws \Exception
     */
    public function deleteTypeByCategoryTypeID($extra_type_id, $category_type_id)
    {
        try {
            $this->app['db']->delete(self::$table_name, array(
                'extra_type_id' => $extra_type_id,
                'category_type_id' => $category_type_id
            ));
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }
}
