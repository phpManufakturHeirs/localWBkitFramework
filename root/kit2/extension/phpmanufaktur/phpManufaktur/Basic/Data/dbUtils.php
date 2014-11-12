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
class dbUtils {

    protected $app = null;

    /**
     * Constructor
     *
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * Delete table - switching check for foreign keys off before executing
     *
     * @throws \Exception
     */
    public function dropTable($table)
    {
        try {
            $SQL = <<<EOD
    SET foreign_key_checks = 0;
    DROP TABLE IF EXISTS `$table`;
    SET foreign_key_checks = 1;
EOD;
            $this->app['db']->query($SQL);
            $this->app['monolog']->addInfo("Drop table '$table'", array(__METHOD__, __LINE__));
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Check wether InnoDB support is available or not
     *
     * @throws \Exception
     * @return boolean
     */
    public function isInnoDBsupported()
    {
        try {
            $SQL = "SELECT SUPPORT FROM INFORMATION_SCHEMA.ENGINES WHERE ENGINE = 'InnoDB'";
            $result = $this->app['db']->fetchColumn($SQL);
            return ($result != 'NO');
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Return a valid version string for the MySQL client version,
     * using mysqli_get_client_version()
     *
     * @return string
     */
    public function getMySQLversion()
    {
        // for version 4.1.6 return 40106;
        $mysqlVersion =  mysqli_get_client_version();
        //create mysql version string to check it
        $mainVersion = (int)($mysqlVersion/10000);
        $a = $mysqlVersion - ($mainVersion*10000);
        $minorVersion = (int)($a/100);
        $subVersion = $a - ($minorVersion*100);
        return $mainVersion.'.'.$minorVersion.'.'.$subVersion;
    }

    /**
     * Check if the given column exists in the table
     *
     * @param string $table
     * @param string $column_name
     * @return boolean
     */
    public function columnExists($table, $column_name)
    {
        try {
            $query = $this->app['db']->query("DESCRIBE `$table`");
            while (false !== ($row = $query->fetch())) {
                if ($row['Field'] == $column_name) return true;
            }
            return false;
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Check if the given $table exists
     *
     * @param string $table
     * @throws \Exception
     * @return boolean
     */
    public function tableExists($table)
    {
        try {
            $query = $this->app['db']->query("SHOW TABLES LIKE '$table'");
            return (false !== ($row = $query->fetch())) ? true : false;
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Check if the $value exists in the ENUM array of the $field
     *
     * @param string $table
     * @param string $field
     * @param string $value
     * @throws \Exception
     * @return boolean|NULL NULL if ENUM not exists in $field or TRUE|FALSE for $value
     */
    public function enumValueExists($table, $field, $value)
    {
        try {
            $SQL = "SHOW COLUMNS FROM `$table` WHERE FIELD = '$field'";
            $result = $this->app['db']->fetchAssoc($SQL);
            if (isset($result['Type']) && (false !== strpos($result['Type'], "enum('"))) {
                $enum = str_replace(array("enum('", "')", "''"), array('', '', "'"), $result['Type']);
                $check = explode("','", $enum);
                return in_array($value, $check);
            }
            return null;
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Get the ENUM values of the given $table / $field
     *
     * @param string $table
     * @param string $field
     * @throws \Exception
     * @return Ambigous <boolean, array>
     */
    public function getEnumValues($table, $field)
    {
        try {
            $SQL = "SHOW COLUMNS FROM `$table` WHERE FIELD = '$field'";
            $result = $this->app['db']->fetchAssoc($SQL);
            $enum_array = array();
            if (isset($result['Type']) && (false !== strpos($result['Type'], "enum('"))) {
                $enum = str_replace(array("enum('", "')", "''"), array('', '', "'"), $result['Type']);
                $enum_array = explode("','", $enum);
            }
            return (!empty($enum_array)) ? $enum_array : false;
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Get the SET values of the given table and field
     *
     * @param string $table
     * @param string $field
     * @throws \Exception
     * @return Ambigous <boolean, array>
     */
    public function getSetValues($table, $field)
    {
        try {
            $SQL = "SHOW COLUMNS FROM `$table` WHERE FIELD = '$field'";
            $result = $this->app['db']->fetchAssoc($SQL);
            $set_array = array();
            if (isset($result['Type']) && (false !== strpos($result['Type'], "set('"))) {
                $set = str_replace(array("set('", "')", "''"), array('', '', "'"), $result['Type']);
                $set_array = explode("','", $set);
            }
            return (!empty($set_array)) ? $set_array : false;
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Get the columns of the given table
     *
     * @param string $table
     * @throws \Exception
     * @return array
     */
    public function getColumns($table)
    {
        try {
            $result = $this->app['db']->fetchAll("SHOW COLUMNS FROM `$table`");
            $columns = array();
            foreach ($result as $column) {
                $columns[] = $column['Field'];
            }
            return $columns;
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Truncate the given table. This function will disable foreign keys before
     * operate and enable them after the truncation
     *
     * @param string $table
     * @throws \Exception
     */
    public function truncateTable($table)
    {
        try {
            $SQL = <<<EOD
    SET foreign_key_checks = 0;
    TRUNCATE TABLE `$table`;
    SET foreign_key_checks = 1;
EOD;
            $this->app['db']->query($SQL);
            $this->app['monolog']->addInfo("Truncate table '$table'", array(__METHOD__, __LINE__));
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

}
