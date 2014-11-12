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
class i18nReference
{
    protected $app = null;
    protected static $table_name = null;

    public function __construct(Application $app)
    {
        $this->app = $app;
        self::$table_name = FRAMEWORK_TABLE_PREFIX.'basic_i18n_reference';
    }

    /**
     * Create the table 'basic_locale_reference'
     *
     * @throws \Exception
     */
    public function createTable()
    {
        $table = self::$table_name;
        $table_source = FRAMEWORK_TABLE_PREFIX.'basic_i18n_source';
        $table_file = FRAMEWORK_TABLE_PREFIX.'basic_i18n_scan_file';
        $SQL = <<<EOD
    CREATE TABLE IF NOT EXISTS `$table` (
      `reference_id` INT(11) NOT NULL AUTO_INCREMENT,
      `locale_id` INT(11) NOT NULL DEFAULT -1,
      `file_id` INT(11) NOT NULL DEFAULT -1,
      `line_number` INT(11) NOT NULL DEFAULT -1,
      `locale_usage` ENUM('FORM_FIELD','FORM_LABEL','ALERT','TRANSLATOR','TWIG','UNKNOWN') NOT NULL DEFAULT 'UNKNOWN',
      `timestamp` TIMESTAMP,
      PRIMARY KEY (`reference_id`),
      INDEX (`locale_id`, `file_id`),
      CONSTRAINT
        FOREIGN KEY (`locale_id`)
        REFERENCES `$table_source` (`locale_id`)
        ON DELETE CASCADE,
      CONSTRAINT
        FOREIGN KEY (`file_id`)
        REFERENCES `$table_file` (`file_id`)
        ON DELETE CASCADE
    )
    COMMENT='Locale references for the localeEditor'
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
     * Check if for the given locale ID, file ID and line number a reference exists
     *
     * @param integer $locale_id
     * @param integer $file_id
     * @param integer $line_number
     * @throws \Exception
     * @return Ambigous <boolean, integer>
     */
    public function existsReference($locale_id, $file_id, $line_number)
    {
        try {
            $SQL = "SELECT `reference_id` FROM `".self::$table_name."` WHERE `locale_id`='$locale_id' ".
                "AND `file_id`='$file_id' AND `line_number`='$line_number'";
            $reference_id = $this->app['db']->fetchColumn($SQL);
            return ($reference_id > 0) ? $reference_id : false;
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Delete all references for the given file ID
     *
     * @param integer $file_id
     * @throws \Exception
     */
    public function deleteFileReferences($file_id)
    {
        try {
            $this->app['db']->delete(self::$table_name, array('file_id' => $file_id));
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Insert a new record for the reference
     *
     * @param unknown $insert
     * @throws \Exception
     * @return integer last inserted ID
     */
    public function insert($insert)
    {
        try {
            if (!isset($insert['locale_id']) || !isset($insert['file_id']) || !isset($insert['line_number'])) {
                throw new \Exception('The fields locale_id, file_id and line_number must be set!');
            }
            $this->app['db']->insert(self::$table_name, $insert);
            return $this->app['db']->lastInsertId();
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Count the references (hits) for the given locale ID
     *
     * @param integer $locale_id
     * @throws \Exception
     * @return integer
     */
    public function countReferencesForLocaleID($locale_id)
    {
        try {
            $SQL = "SELECT COUNT(`locale_id`) as 'count' FROM `".self::$table_name."` WHERE `locale_id`=$locale_id";
            $count = $this->app['db']->fetchColumn($SQL);
            return ($count > 0) ? $count : 0;
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Select all references for the given locale ID
     *
     * @param integer $locale_id
     * @throws \Exception
     * @return Ambigous <boolean, array>
     */
    public function selectReferencesForLocaleID($locale_id)
    {
        try {
            $source = self::$table_name;
            $file = FRAMEWORK_TABLE_PREFIX.'basic_i18n_scan_file';
            $SQL = "SELECT * FROM `".self::$table_name."` ".
                "LEFT JOIN `$file` ON `$file`.`file_id` = `$source`.`file_id` ".
                "WHERE `locale_id`=$locale_id";
            $references = $this->app['db']->fetchAll($SQL);
            return (is_array($references) && !empty($references)) ? $references : false;
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }
}
