<?php

/**
 * miniShop
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de/miniShop
 * @copyright 2014 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\miniShop\Data\Shop;

use Silex\Application;

class Group
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
        self::$table_name = FRAMEWORK_TABLE_PREFIX.'minishop_group';
    }

    /**
     * Create the table
     *
     * @throws \Exception
     */
    public function createTable()
    {
        $table = self::$table_name;
        $table_base = FRAMEWORK_TABLE_PREFIX.'minishop_base';

        $SQL = <<<EOD
    CREATE TABLE IF NOT EXISTS `$table` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `name` VARCHAR(64) NOT NULL DEFAULT 'DEFAULT',
        `description` TEXT NOT NULL,
        `base_id` INT(11) NOT NULL DEFAULT -1,
        `base_name` VARCHAR(64) NOT NULL DEFAULT '',
        `status` ENUM ('ACTIVE', 'LOCKED', 'DELETED') DEFAULT 'ACTIVE',
        `timestamp` TIMESTAMP,
        PRIMARY KEY (`id`),
        UNIQUE (`name`),
        INDEX (`base_id`, `base_name`),
        CONSTRAINT
            FOREIGN KEY (`base_id`)
            REFERENCES `$table_base` (`id`)
            ON DELETE CASCADE,
        CONSTRAINT
            FOREIGN KEY (`base_name`)
            REFERENCES `$table_base` (`name`)
            ON DELETE CASCADE
        )
    COMMENT='The article group table for the miniShop'
    ENGINE=InnoDB
    AUTO_INCREMENT=1
    DEFAULT CHARSET=utf8
    COLLATE='utf8_general_ci'
EOD;
        try {
            $this->app['db']->query($SQL);
            $this->app['monolog']->addInfo('Created table '.$table, array(__METHOD__, __LINE__));
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Drop the table
     */
    public function dropTable()
    {
        $this->app['db.utils']->dropTable(self::$table_name);
    }

    /**
     * Get the values for the group status
     *
     * @return Ambigous <boolean, array>
     */
    public function getStatusTypes()
    {
        return $this->app['db.utils']->getEnumValues(self::$table_name, 'status');
    }

    /**
     * Count all not deleted records
     *
     * @throws \Exception
     */
    public function count()
    {
        try {
            return $this->app['db']->fetchColumn("SELECT * FROM `".self::$table_name."` WHERE `status`!='DELETED'");
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Select all groups for the miniShop
     *
     * @throws \Exception
     * @return Ambigous <boolean, array>
     */
    public function selectAll()
    {
        try {
            $SQL = "SELECT * FROM `".self::$table_name."` WHERE `status` != 'DELETED' ORDER BY `name` ASC";
            $results = $this->app['db']->fetchAll($SQL);
            $groups = array();
            if (is_array($results)) {
                foreach ($results as $result) {
                    $item = array();
                    foreach ($result as $key => $value) {
                        $item[$key] = (is_string($value)) ? $this->app['utils']->unsanitizeText($value) : $value;
                    }
                    $groups[] = $item;
                }
            }
            return (!empty($groups)) ? $groups : false;
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Select all active groups for the given base configuration name
     *
     * @param string $base_name
     * @throws \Exception
     * @return Ambigous <boolean, array>
     */
    public function selectAllActiveByBase($base_name)
    {
        try {
            $SQL = "SELECT * FROM `".self::$table_name."` WHERE `status`='ACTIVE' AND `base_name`='$base_name' ORDER BY `name` ASC";
            $results = $this->app['db']->fetchAll($SQL);
            $groups = array();
            if (is_array($results)) {
                foreach ($results as $result) {
                    $item = array();
                    foreach ($result as $key => $value) {
                        $item[$key] = (is_string($value)) ? $this->app['utils']->unsanitizeText($value) : $value;
                    }
                    $groups[] = $item;
                }
            }
            return (!empty($groups)) ? $groups : false;
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Select the article group with the given ID
     *
     * @param integer $id
     * @throws \Exception
     * @return Ambigous <boolean, array>
     */
    public function select($id)
    {
        try {
            $SQL = "SELECT * FROM `".self::$table_name."` WHERE `id`=$id";
            $result = $this->app['db']->fetchAssoc($SQL);
            $group = array();
            if (is_array($result)) {
                foreach ($result as $key => $value) {
                    $group[$key] = is_string($value) ? $this->app['utils']->unsanitizeText($value) : $value;
                }
            }
            return (!empty($group)) ? $group : false;
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Check if the given $name is already in use.
     *
     * @param string $name
     * @param integer $ignore_id optional specify an ID to ignore
     * @throws \Exception
     * @return boolean
     */
    public function existsName($name, $ignore_id=null)
    {
        try {
            $SQL = "SELECT `id` FROM `".self::$table_name."` WHERE `name`='$name' AND `status`!= 'DELETED'";
            if (!is_null($ignore_id)) {
                $SQL .= " AND `id` != $ignore_id";
            }
            $id = $this->app['db']->fetchColumn($SQL);
            return ($id > 0);
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Insert a new record
     *
     * @param array $data
     * @throws \Exception
     */
    public function insert($data)
    {
        try {
            $insert = array();
            foreach ($data as $key => $value) {
                $insert[$key] = (is_string($value)) ? $this->app['utils']->sanitizeText($value) : $value;
            }
            if (isset($insert['id'])) {
                unset($insert['id']);
            }
            if (!isset($insert['description'])) {
                $insert['description'] = '';
            }
            $this->app['db']->insert(self::$table_name, $insert);
            return $this->app['db']->lastInsertId();
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Update the record with the given ID
     *
     * @param integer $id
     * @param array $data
     * @throws \Exception
     */
    public function update($id, $data)
    {
        try {
            $check = array('id', 'timestamp');
            foreach ($check as $key) {
                if (isset($data[$key])) {
                    unset($data[$key]);
                }
            }
            $update = array();
            foreach ($data as $key => $value) {
                if (is_null($value)) {
                    continue;
                }
                $update[$key] = is_string($value) ? $this->app['utils']->sanitizeText($value) : $value;
            }
            if (!empty($update)) {
                $this->app['db']->update(self::$table_name, $update, array('id' => $id));
            }
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Delete the record with the given ID
     *
     * @param integer $id
     * @throws \Exception
     */
    public function delete($id)
    {
        try {
            $this->app['db']->delete(self::$table_name, array('id' => $id));
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }



}
