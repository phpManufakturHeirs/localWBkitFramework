<?php

/**
 * kfHelloWorld
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de
 * @copyright 2013 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace thirdParty\HelloWorld\Data;

use Silex\Application;

class HelloWorld
{

    protected $app = null;
    protected static $table_name = null;

    public function __construct(Application $app)
    {
        $this->app = $app;
        self::$table_name = FRAMEWORK_TABLE_PREFIX.'hello_world';
    }

    /**
     * Create the table 'hello_world'
     *
     * @throws \Exception
     */
    public function createTable ()
    {
        $table = self::$table_name;
        $SQL = <<<EOD
    CREATE TABLE IF NOT EXISTS `$table` (
      `id` INT(11) NOT NULL AUTO_INCREMENT,
      `title` ENUM ('mister', 'lady') NOT NULL DEFAULT 'mister',
      `first_name` VARCHAR(64) NOT NULL DEFAULT '',
      `last_name` VARCHAR(64) NOT NULL DEFAULT '',
      `birthday` DATE NOT NULL DEFAULT '0000-00-00',
      `email` VARCHAR(255) NOT NULL DEFAULT '',
      `timestamp` TIMESTAMP,
      PRIMARY KEY (`id`)
    )
    COMMENT='Example table for the Hello World extension'
    ENGINE=InnoDB
    AUTO_INCREMENT=1
    DEFAULT CHARSET=utf8
    COLLATE='utf8_general_ci'
EOD;
        try {
            $this->app['db']->query($SQL);
            $this->app['monolog']->addDebug("Created table '".self::$table_name."' for the Hello World extension");
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
            $this->app['monolog']->addInfo("Drop table 'hello_world'", array(__METHOD__, __LINE__));
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Insert a new record into the Hello World table and return the ID of the new record
     *
     * @param array $data
     * @throws \Exception
     * @return integer ID of the new record
     */
    public function insert($data)
    {
        try {
            $insert = array();
            foreach ($data as $key => $value) {
                // we quote any keys and sanitize all variables of type string!
                $insert[$this->app['db']->quoteIdentifier($key)] = (is_string($value)) ? $this->app['utils']->sanitizeVariable($value) : $value;
            }
            $this->app['db']->insert(self::$table_name, $insert);
            // return the last inserted ID
            return $this->app['db']->lastInsertId();
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function select($id)
    {
        try {
            $SQL = "SELECT * FROM `".self::$table_name."` WHERE `id`='$id'";
            $data = $this->app['db']->fetchAssoc($SQL);
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e->getMessage());
        }
        $result = array();
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $result[$key] = (is_string($value)) ? $this->app['utils']->unsanitizeText($value) : $value;
            }
        }
        return $result;
    }
}
