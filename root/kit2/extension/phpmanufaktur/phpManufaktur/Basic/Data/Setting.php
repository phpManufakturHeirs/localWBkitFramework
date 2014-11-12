<?php

/**
 * kitFramework::Basic
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://addons.phpmanufaktur.de/propangas24
 * @copyright 2013 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\Basic\Data;

use Silex\Application;

/**
 * Data table for basic settings and configuration information of the kitFramework
 *
 * @author Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 *
 */
class Setting {

    protected $app = null;
    protected static $table_name = null;

    protected $default_values = array(
        'extension_catalog_release' => '0.10',
        'extension_catalog_update' => 'auto' // possible: auto, manual
    );

    /**
     * Constructor
     *
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
        self::$table_name = FRAMEWORK_TABLE_PREFIX.'basic_setting';
    }

    /**
     * Create the table for the settings
     *
     * @throws \Exception
     */
    public function createTable()
    {
        $table = self::$table_name;
        $SQL = <<<EOD
        CREATE TABLE IF NOT EXISTS `$table` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `name` VARCHAR(64) NOT NULL DEFAULT '',
            `value` VARCHAR(255) NOT NULL DEFAULT '',
            `timestamp` TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE (`name`)
            )
        COMMENT='General settings for kitFramework::Basic'
        ENGINE=InnoDB
        AUTO_INCREMENT=1
        DEFAULT CHARSET=utf8
        COLLATE='utf8_general_ci'
EOD;
        try {
            $this->app['db']->query($SQL);
            $this->app['monolog']->addDebug("Created table '".self::$table_name."'");
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e->getMessage());
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
            $this->app['monolog']->addDebug("Drop table '".self::$table_name."'", array(__METHOD__, __LINE__));
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Insert the default values to the table
     *
     * @throws \Exception
     */
    public function insertDefaultValues()
    {
        try {
            foreach ($this->default_values as $name => $value) {
                $SQL = "INSERT INTO `".self::$table_name."` (`name`, `value`) VALUE ('$name', '$value') ON DUPLICATE KEY UPDATE `value`='$value'";
                $this->app['db']->query($SQL);
            }
            $this->app['monolog']->addDebug("Inserted default values into table '".self::$table_name."'");
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * Insert a new name => value pair
     *
     * @param string $name
     * @param string $value
     * @throws \Exception
     */
    public function insert($name, $value)
    {
        try {
            $insert = array(
                'name' => $name,
                'value' => $this->app['utils']->sanitizeText($value)
            );
            $this->app['db']->insert(self::$table_name, $insert);
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * Delete the record for the given name
     *
     * @param string $name
     * @throws \Exception
     */
    public function deleteByName($name)
    {
        try {
            $this->app['db']->delete(self::$table_name, array('name' => $name));
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * Check if the record with the given name exists
     *
     * @param string $name
     * @throws \Exception
     * @return boolean
     */
    public function exists($name)
    {
        try {
            $SQL = "SELECT `id` FROM `".self::$table_name."` WHERE `name`='$name'";
            $result = $this->app['db']->fetchColumn($SQL);
            return ($result > 0);
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * Select the setting for 'name'
     *
     * @param string $name
     * @throws \Exception
     * @return NULL|unknown
     */
    public function select($name)
    {
        try {
            $SQL = "SELECT `value` FROM `".self::$table_name."` WHERE `name`='$name'";
            $result = $this->app['db']->fetchAssoc($SQL);
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e->getMessage());
        }
        if (!is_array($result) || !isset($result['value']))
            return null;
        return (is_string($result['value'])) ? $this->app['utils']->unsanitizeText($result['value']) : $result['value'];
    }

    /**
     * Update 'name' with the given value
     *
     * @param string $name
     * @param mixed $value
     * @throws \Exception
     */
    public function update($name, $value)
    {
        try {
            $data = array(
                'value' => (is_string($value)) ? $this->app['utils']->sanitizeVariable($value) : $value
            );
            $this->app['db']->update(self::$table_name, $data, array('name' => $name));
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e->getMessage());
        }
    }

}
