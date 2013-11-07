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

class CategoryType
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
        self::$table_name = FRAMEWORK_TABLE_PREFIX.'contact_category_type';
    }

    /**
     * Create the CATEGORY TYPE table
     *
     * @throws \Exception
     */
    public function createTable()
    {
        $table = self::$table_name;
        $SQL = <<<EOD
    CREATE TABLE IF NOT EXISTS `$table` (
        `category_type_id` INT(11) NOT NULL AUTO_INCREMENT,
        `category_type_name` VARCHAR(64) NOT NULL DEFAULT '',
        `category_type_description` VARCHAR(255) NOT NULL DEFAULT '',
        PRIMARY KEY (`category_type_id`),
        UNIQUE (`category_type_name`)
        )
    COMMENT='The category type definition table'
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

    /**
     * Initialize the category type list with the defaults from /communication.types.json
     *
     * @throws \Exception
     */
    public function initCategoryTypeList()
    {
        try {
            // get the number of titles in the list
            $count = $this->app['db']->fetchColumn("SELECT COUNT(`category_type_id`) FROM `".self::$table_name."`");
            if ($count < 1) {
                // no entries!
                $json_import = MANUFAKTUR_PATH.'/Contact/Data/Setup/Import/category.json';
                if (!file_exists($json_import)) {
                    throw new \Exception("Can't read the category type definition list: $json_import", array(__METHOD__, __LINE__));
                }
                $types = $this->app['utils']->readJSON($json_import);
                foreach ($types as $type) {
                    $this->app['db']->insert(self::$table_name, array(
                        'category_type_name' => $type['type'],
                        'category_type_description' => $this->app['utils']->sanitizeText($type['description'])
                    ));
                }
            }
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Return a array with all categories prepared for usage with TWIG
     *
     * @throws \Exception
     * @return array
     */
    public function getArrayForTwig()
    {
        try {
            $SQL = "SELECT * FROM `".self::$table_name."` ORDER BY `category_type_name` ASC";
            $categories = $this->app['db']->fetchAll($SQL);
            $result = array();
            foreach ($categories as $category) {
                $result[$category['category_type_name']] = ucfirst(strtolower($category['category_type_name']));
            }
            return $result;
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Check if the desired CATEGORY exists
     *
     * @param string $category_type_name
     * @throws \Exception
     * @return boolean
     */
    public function existsCategory($category_type_name)
    {
        try {
            $SQL = "SELECT `category_type_name` FROM `".self::$table_name."` WHERE `category_type_name`='$category_type_name'";
            $result = $this->app['db']->fetchColumn($SQL);
            return ($result == $category_type_name) ? true : false;
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Select all category types and return an ascending ordered array
     *
     * @throws \Exception
     * @return Ambigous <multitype:, unknown>
     */
    public function selectAll()
    {
        try {
            $SQL = "SELECT * FROM `".self::$table_name."` ORDER BY `category_type_name` ASC";
            $results = $this->app['db']->fetchAll($SQL);
            $categories = array();
            $level = 0;
            foreach ($results as $result) {
                foreach ($result as $key => $value) {
                    $categories[$level][$key] = is_string($value) ? $this->app['utils']->unsanitizeText($value) : $value;
                }
                $level++;
            }
            return $categories;
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Select the desired category type by ID
     *
     * @param integer $category_type_id
     * @throws \Exception
     * @return array|boolean associated array or false
     */
    public function select($category_type_id)
    {
        try {
            $SQL = "SELECT * FROM `".self::$table_name."` WHERE `category_type_id`='$category_type_id'";
            $result = $this->app['db']->fetchAssoc($SQL);
            if (is_array($result) && isset($result['category_type_name'])) {
                $category = array();
                foreach ($result as $key => $value) {
                    $category[$key] = is_string($value) ? $this->app['utils']->unsanitizeText($value) : $value;
                }
                return $category;
            }
            return false;
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Select the desired category type by the name
     *
     * @param string $category_type_name
     * @throws \Exception
     * @return array|boolean associated array or false
     */
    public function selectByName($category_type_name)
    {
        try {
            $SQL = "SELECT * FROM `".self::$table_name."` WHERE `category_type_name`='$category_type_name'";
            $result = $this->app['db']->fetchAssoc($SQL);
            if (is_array($result) && isset($result['category_type_name'])) {
                $category = array();
                foreach ($result as $key => $value) {
                    $category[$key] = is_string($value) ? $this->app['utils']->unsanitizeText($value) : $value;
                }
                return $category;
            }
            return false;
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }


    /**
     * Delete the desired category type ID and all associated category names from
     * the category table. Use transaction.
     *
     * @param integer $category_type_id
     * @throws \Exception
     * @return boolean
     */
    public function delete($category_type_id)
    {
        try {
            // begin transaction
            $this->app['db']->beginTransaction();

            // first we need the tag name
            if (false === ($category_type = $this->select($category_type_id))) {
                // category ID does not exists, rollback ...
                $this->app['db']->rollback();
                return false;
            }
            $Category = new Category($this->app);
            // delete all categories assigned to contacts
            $Category->delete($category_type['category_type_name']);

            // delete the category type
            $this->app['db']->delete(self::$table_name, array('category_type_id' => $category_type_id));

            // commit transaction
            $this->app['db']->commit();
            return true;
        } catch (\Doctrine\DBAL\DBALException $e) {
            // rollback ...
            $this->app['db']->rollback();
            throw new \Exception($e);
        }
    }

    /**
     * Insert a new CATEGORY TYPE record
     *
     * @param array $data
     * @param reference integer $category_id
     * @throws \Exception
     */
    public function insert($data, &$category_type_id=null)
    {
        try {
            $insert = array();
            foreach ($data as $key => $value) {
                if ($key == 'category_type_id') continue;
                $insert[$this->app['db']->quoteIdentifier($key)] = is_string($value) ? $this->app['utils']->unsanitizeText($value) : $value;
            }
            $this->app['db']->insert(self::$table_name, $insert);
            $category_type_id = $this->app['db']->lastInsertId();
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Update the CATEGORY TYPE for the given ID
     *
     * @param array $data
     * @param integer $category_type_id
     * @throws \Exception
     */
    public function update($data, $category_type_id)
    {
        try {
            $update = array();
            foreach ($data as $key => $value) {
                if (($key == 'category_type_id') || ($key == 'category_type_name')) continue;
                $update[$this->app['db']->quoteIdentifier($key)] = is_string($value) ? $this->app['utils']->sanitizeText($value) : $value;
            }
            if (!empty($update)) {
                $this->app['db']->update(self::$table_name, $update, array('category_type_id' => $category_type_id));
            }
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

}
