<?php

/**
 * kitFramework::Basic
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de
 * @copyright 2013 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\Basic\Data\Security;

use Silex\Application;

class AdminAction
{
    protected $app = null;
    protected static $table_name = null;

    public function __construct (Application $app)
    {
        $this->app = $app;
        self::$table_name = FRAMEWORK_TABLE_PREFIX.'basic_admin_action';
    } // __construct()

    /**
     * Create the table 'admin_action'
     *
     * @throws \Exception
     */
    public function createTable ()
    {
        $table = self::$table_name;
        $SQL = <<<EOD
    CREATE TABLE IF NOT EXISTS `$table` (
      `id` INT(11) NOT NULL AUTO_INCREMENT,
      `user_id` INT(11) NOT NULL DEFAULT -1,
      `user_name` VARCHAR(64) NOT NULL DEFAULT '',
      `user_email` VARCHAR(128) NOT NULL DEFAULT '',
      `guid` VARCHAR(64) NOT NULL DEFAULT '',
      `status` ENUM('PENDING', 'DONE') NOT NULL DEFAULT 'PENDING',
      `role_action` TEXT NOT NULL,
      `status_action` VARCHAR(64) NOT NULL DEFAULT '',
      `redirect_url` TEXT NOT NULL,
      `timestamp` TIMESTAMP,
      PRIMARY KEY (`id`),
      UNIQUE (`guid`)
    )
    COMMENT='The administrator-user action table for the kitFramework'
    ENGINE=InnoDB
    AUTO_INCREMENT=1
    DEFAULT CHARSET=utf8
    COLLATE='utf8_general_ci'
EOD;
        try {
            $this->app['db']->query($SQL);
            $this->app['monolog']->addDebug("Created table '".self::$table_name."'", array(__METHOD__, __LINE__));
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
            $this->app['monolog']->addDebug("Drop table 'basic_users'", array(__METHOD__, __LINE__));
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Insert a new AdminAction record
     *
     * @param array $data
     * @param string $action_id
     * @throws \Exception
     */
    public function insert($data, &$action_id=null)
    {
        try {
            $insert = array();
            foreach ($data as $key => $value) {
                if (($key == 'id') || ($key == 'timestamp')) continue;
                $insert[$this->app['db']->quoteIdentifier($key)] = is_string($value) ? $this->app['utils']->sanitizeText($value) : $value;
            }
            $this->app['db']->insert(self::$table_name, $insert);
            $action_id = $this->app['db']->lastInsertId();
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Select a AdminAction by GUID
     *
     * @param string $guid
     * @throws \Exception
     * @return Ambigous <boolean, array>
     */
    public function selectByGUID($guid)
    {
        try {
            $SQL = "SELECT * FROM `".self::$table_name."` WHERE `guid`='$guid'";
            $result = $this->app['db']->fetchAssoc($SQL);
            return (isset($result['id'])) ? $result : false;
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Update AdminAction record
     *
     * @param array $data
     * @param integer $id
     * @throws \Exception
     */
    public function update($data, $id)
    {
        try {
            $update = array();
            foreach ($data as $key => $value)
                $update[$this->app['db']->quoteIdentifier($key)] = (is_string($value)) ? $this->app['utils']->sanitizeText($value) : $value;
            $this->app['db']->update(self::$table_name, $update, array('id' => $id));
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }
}
