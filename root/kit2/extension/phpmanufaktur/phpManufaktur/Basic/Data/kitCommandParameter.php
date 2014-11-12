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
use Carbon\Carbon;

/**
 * Data table to save Base64 encoded kitCommand parameters
 *
 * @author Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 *
 */
class kitCommandParameter
{
    protected $app = null;
    protected static $table_name = null;

    public function __construct(Application $app)
    {
        $this->app = $app;
        self::$table_name = FRAMEWORK_TABLE_PREFIX.'basic_command_parameter';
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
      `link` VARCHAR(64) NOT NULL DEFAULT '',
      `parameter` TEXT NOT NULL,
      `timestamp` TIMESTAMP,
      PRIMARY KEY (`id`),
      UNIQUE (`link`)
    )
    COMMENT='Table for CMS parameters for the kitCommands'
    ENGINE=InnoDB
    AUTO_INCREMENT=1
    DEFAULT CHARSET=utf8
    COLLATE='utf8_general_ci'
EOD;
        try {
            $this->app['db']->query($SQL);
            $this->app['monolog']->addDebug("Created table '".self::$table_name."' for the class kitCommandParam");
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
     * Select a kitCommand parameter ID (link)
     *
     * @param string $link
     * @param boolean $return_array
     * @throws \Exception
     * @return mixed|boolean
     */
    public function selectParameter($link, $return_array=true)
    {
        try {
            $SQL = "SELECT `parameter` FROM `".self::$table_name."` WHERE `link`='$link'";
            $parameter = $this->app['db']->fetchColumn($SQL);
            if (is_string($parameter)) {
                if ($return_array) {
                    return json_decode($this->app['utils']->unsanitizeText($parameter), true);
                }
                else {
                    return $this->app['utils']->unsanitizeText($parameter);
                }
            }
            return false;
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e->getMessage(), 0, $e);
        }
    }

    /**
     * Insert a new kitCommand parameter record
     *
     * @param array $items
     * @throws \Exception
     * @return integer ID of the new record
     */
    public function insert($items)
    {
        try {
            $insert = array();
            foreach ($items as $key => $value)
                $insert[$key] = (is_string($value)) ? $this->app['utils']->sanitizeVariable($value) : $value;
            $this->app['db']->insert(self::$table_name, $insert);
            return $this->app['db']->lastInsertId();
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * Delete all entries from the table which are older than 48 hours
     *
     * @throws \Exception
     */
    public function cleanup()
    {
        try {
            $dt = Carbon::create();
            $dt->subHours(48);
            $oldest = $dt->toDateTimeString();
            $SQL = "DELETE FROM `".self::$table_name."` WHERE `timestamp` <= '$oldest'";
            $this->app['db']->query($SQL);
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e->getMessage());
        }
    }
}
