<?php

/**
 * kitFramework::Basic
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de
 * @copyright 2013 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\Basic\Data;

use Silex\Application;

/**
 * Data table for the extension catalog for the kitFramework
 *
 * @author Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 *
 */
class ExtensionCatalog
{
    protected $app = null;
    protected static $table_name = null;

    public function __construct(Application $app)
    {
        $this->app = $app;
        self::$table_name = FRAMEWORK_TABLE_PREFIX.'basic_extension_catalog';
    }

    /**
     * Create the table 'extension_catalog'
     *
     * @throws \Exception
     */
    public function createTable ()
    {
        $table = self::$table_name;
        $SQL = <<<EOD
    CREATE TABLE IF NOT EXISTS `$table` (
      `id` INT(11) NOT NULL AUTO_INCREMENT,
      `guid` VARCHAR(64) NOT NULL DEFAULT '',
      `name` VARCHAR(64) NOT NULL DEFAULT '',
      `category` VARCHAR(64) NOT NULL DEFAULT '',
      `group` VARCHAR(64) NOT NULL DEFAULT '',
      `release` VARCHAR(16) NOT NULL DEFAULT '',
      `release_status` VARCHAR(64) NOT NULL DEFAULT 'undefined',
      `date` DATE NOT NULL DEFAULT '0000-00-00',
      `info` TEXT NOT NULL,
        `logo_blob` BLOB NOT NULL,
      `logo_type` ENUM ('jpg','png') NOT NULL DEFAULT 'jpg',
      `logo_width` INT NOT NULL DEFAULT '0',
      `logo_height` INT NOT NULL DEFAULT '0',
      `logo_size` INT NOT NULL DEFAULT '0',
      `timestamp` TIMESTAMP,
      PRIMARY KEY (`id`),
      UNIQUE (`guid`)
    )
    COMMENT='The extension catalog table for the kitFramework'
    ENGINE=InnoDB
    AUTO_INCREMENT=1
    DEFAULT CHARSET=utf8
    COLLATE='utf8_general_ci'
EOD;
        try {
            $this->app['db']->query($SQL);
            $this->app['monolog']->addDebug("Created table '".self::$table_name."' for the class ExtensionCatalog");
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e->getMessage(), 0, $e);
        }
    } // createTable()

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
     * Select a extension record by the given GUID
     *
     * @param string $guid
     * @throws \Exception
     * @return Ambigous <number, NULL>
     */
    public function selectIDbyGUID($guid)
    {
        try {
            $SQL = "SELECT `id` FROM `".self::$table_name."` WHERE `guid`='$guid'";
            $result = $this->app['db']->fetchAssoc($SQL);
            return (is_array($result) && isset($result['id'])) ? (int) $result['id'] : null;
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * Select by ID
     *
     * @param integer $id
     * @throws \Exception
     * @return Ambigous <boolean, unknown>
     */
    public function select($id)
    {
        try {
            $SQL = "SELECT * FROM `".self::$table_name."` WHERE `id`='$id'";
            $result = $this->app['db']->fetchAssoc($SQL);
            return (is_array($result) && isset($result['id'])) ? $result : false;
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * Insert a new record into the extension catalog
     *
     * @param array $data
     * @throws \Exception
     */
    public function insert($data)
    {
        try {
            $insert = array();
            foreach ($data as $key => $value) {
                // quote the keys!
                $insert[$this->app['db']->quoteIdentifier($key)] = (is_string($value)) ? $this->app['utils']->sanitizeVariable($value) : $value;
            }
            $this->app['db']->insert(self::$table_name, $insert);
            return $this->app['db']->lastInsertId();
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * Update the record for given ID with the $data
     *
     * @param integer $id
     * @param array $data
     * @throws \Exception
     */
    public function update($id, $data)
    {
        try {
            $update = array();
            foreach ($data as $key => $value)
                // quote the keys!
                $update[$this->app['db']->quoteIdentifier($key)] = (is_string($value)) ? $this->app['utils']->sanitizeVariable($value) : $value;
            $this->app['db']->update(self::$table_name, $update, array('id' => $id));
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * Select all records from the extension dialog and order them by the given
     * 'order_by' parameter
     *
     * @param string $order_by
     * @throws \Exception
     */
    public function selectAll($order_by='name')
    {
        try {
            $SQL = "SELECT * FROM `".self::$table_name."` ORDER BY `$order_by` ASC";
            return $this->app['db']->fetchAll($SQL);
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * Select a extension record by the given group and extension name
     *
     * @param string $group
     * @param string $name
     * @throws \Exception
     * @return Ambigous <boolean, unknown>
     */
    public function selectByGroupAndName($group, $name)
    {
        try {
            $SQL = "SELECT * FROM `".self::$table_name."` WHERE `group`='$group' AND `name`='$name'";
            $result = $this->app['db']->fetchAssoc($SQL);
            return (isset($result['id'])) ? $result : false;
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e->getMessage());
        }
    }
}
