<?php

/**
 * kitFramework::Basic
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de
 * @copyright 2014 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\Basic\Data\i18n;

use Silex\Application;

/**
 * Data table for the extension catalog for the kitFramework
 *
 * @author Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 *
 */
class i18nScanFile
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
        self::$table_name = FRAMEWORK_TABLE_PREFIX.'basic_i18n_scan_file';
    }

    /**
     * Create the table 'basic_locale_scan_file'
     *
     * @throws \Exception
     */
    public function createTable()
    {
        $table = self::$table_name;
        $SQL = <<<EOD
    CREATE TABLE IF NOT EXISTS `$table` (
      `file_id` INT(11) NOT NULL AUTO_INCREMENT,
      `file_type` ENUM ('PHP','TWIG') NOT NULL DEFAULT 'PHP',
      `file_path` TEXT NOT NULL,
      `file_md5` VARCHAR (64) NOT NULL DEFAULT '',
      `file_mtime` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
      `file_status` ENUM ('REGISTERED', 'SCANNED') NOT NULl DEFAULT 'REGISTERED',
      `extension` VARCHAR(64) NOT NULL DEFAULT '',
      `template` VARCHAR(64) NOT NULL DEFAULT 'NONE',
      `locale_hits` INT(11) NOT NULL DEFAULT 0,
      `timestamp` TIMESTAMP,
      PRIMARY KEY (`file_id`),
      UNIQUE (`file_md5`)
    )
    COMMENT='ScanFiles table for the localeEditor'
    ENGINE=InnoDB
    AUTO_INCREMENT=1
    DEFAULT CHARSET=utf8
    COLLATE='utf8_general_ci'
EOD;
        try {
            $this->app['db']->query($SQL);
            $this->app['monolog']->addDebug("Created table '".self::$table_name);
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Drop Table
     */
    public function dropTable()
    {
        $this->app['db.utils']->dropTable(self::$table_name);
    }

    /**
     * Check if the given checksum exists. Return the ID on succsess and FALSE on error
     *
     * @param string $path
     * @throws \Exception
     * @return Ambigous <boolean, integer>
     */
    public function existsMD5($md5)
    {
        try {
            $SQL = "SELECT `file_id` FROM `".self::$table_name."` WHERE `file_md5`='$md5'";
            $file_id = $this->app['db']->fetchColumn($SQL);
            return ($file_id > 0) ? $file_id : false;
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Insert a new record
     *
     * @param array $data
     * @throws \Exception
     * @return integer ID of the new record
     */
    public function insert($data)
    {
        if (!isset($data['file_path'])) {
            throw new \Exception("Missing the field 'file_path'");
        }

        try {
            $data['file_path'] = addslashes(realpath($data['file_path']));
            $this->app['db']->insert(self::$table_name, $data);
            return $this->app['db']->lastInsertId();
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Select the record for the given file ID
     *
     * @param integer $file_id
     * @throws \Exception
     * @return Ambigous <boolean, array>
     */
    public function select($file_id)
    {
        try {
            $SQL = "SELECT * FROM `".self::$table_name."` WHERE `file_id`=$file_id";
            $result = $this->app['db']->fetchAssoc($SQL);
            $result['file_path'] = realpath($result['file_path']);
            return (isset($result['file_id'])) ? $result : false;
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Update the record with the given file ID
     *
     * @param integer $file_id
     * @param array $update
     * @throws \Exception
     */
    public function update($file_id, $update)
    {
        try {
            $check = array('file_id', 'timestamp', 'file_path');
            foreach ($check as $key) {
                if (isset($update[$key])) {
                    unset($update[$key]);
                }
            }
            $this->app['db']->update(self::$table_name, $update, array('file_id' => $file_id));
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * Count REGISTERED and SCANNED files and the total locale hits
     *
     * @throws \Exception
     * @return array
     */
    public function selectCount()
    {
        try {
            $SQL = "SELECT SUM(CASE WHEN `file_status`='REGISTERED' THEN 1 ELSE 0 END) AS 'count_registered', ".
                "SUM(CASE WHEN `file_status`='SCANNED' THEN 1 ELSE 0 END) AS 'count_scanned', ".
                "SUM(`locale_hits`) as 'locale_hits' ".
                "FROM `".self::$table_name."`";
            $result = $this->app['db']->fetchAssoc($SQL);
            return $result;
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * Select registered files with the given file type
     *
     * @param string $file_type default 'PHP'
     * @throws \Exception
     * @return Ambigous <boolean, array>
     */
    public function selectRegistered($file_type='PHP')
    {
        try {
            $SQL = "SELECT * FROM `".self::$table_name."` WHERE `file_status`='REGISTERED' AND `file_type`='$file_type'";
            $results = $this->app['db']->fetchAll($SQL);
            return (!empty($results)) ? $results : false;
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * Select all table record
     *
     * @throws \Exception
     * @return Ambigous <boolean, unknown>
     */
    public function selectAll()
    {
        try {
            $SQL = "SELECT * FROM `".self::$table_name."`";
            $results = $this->app['db']->fetchAll($SQL);
            return (!empty($results)) ? $results : false;
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * Select all files of the given file type
     *
     * @param string $type
     * @throws \Exception
     * @return Ambigous <boolean, array>
     */
    public function selectType($type='PHP')
    {
        try {
            $SQL = "SELECT * FROM `".self::$table_name."` WHERE `file_type`='$type'";
            $results = $this->app['db']->fetchAll($SQL);
            return (!empty($results)) ? $results : false;
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * Delete the file with the given file ID
     *
     * @param unknown $file_id
     * @throws \Exception
     */
    public function delete($file_id)
    {
        try {
            $this->app['db']->delete(self::$table_name, array('file_id' => $file_id));
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Get the last modification date for the scanned files, return FALSE on error
     *
     * @throws \Exception
     * @return Ambigous <boolean, datetime>
     */
    public function getLastModificationDateTime()
    {
        try {
            $SQL = "SELECT `file_mtime` FROM `".self::$table_name."` ORDER BY `file_mtime` DESC LIMIT 1";
            $datetime = $this->app['db']->fetchColumn($SQL);
            return (!is_null($datetime)) ? $datetime : false;
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }
}
