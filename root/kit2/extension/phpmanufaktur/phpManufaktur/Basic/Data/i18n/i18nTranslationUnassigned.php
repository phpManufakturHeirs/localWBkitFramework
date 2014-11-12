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
class i18nTranslationUnassigned
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
        self::$table_name = FRAMEWORK_TABLE_PREFIX.'basic_i18n_translation_unassigned';
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
      `unassigned_id` INT(11) NOT NULL AUTO_INCREMENT,
      `file_path` TEXT NOT NULL,
      `file_md5` VARCHAR(64) NOT NULL DEFAULT '',
      `extension` VARCHAR(64) NOT NULL DEFAULT '',
      `locale_locale` VARCHAR(2) NOT NULL DEFAULT 'EN',
      `locale_source` TEXT NOT NULL,
      `locale_source_plain` TEXT NOT NULL,
      `locale_md5` VARCHAR(64) NOT NULL DEFAULT '',
      `locale_type` ENUM ('DEFAULT', 'CUSTOM', 'METRIC') NOT NULL DEFAULT 'DEFAULT',
      `translation_text` TEXT NOT NULL,
      `translation_text_plain` TEXT NOT NULL,
      `translation_md5` VARCHAR(64) NOT NULL DEFAULT '',
      `timestamp` TIMESTAMP,
      PRIMARY KEY (`unassigned_id`)
    )
    COMMENT='Unassigned locale sources for the i18nEditor',
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
     * Truncate the table
     */
    public function truncateTable()
    {
        $this->app['db.utils']->truncateTable(self::$table_name);
    }

    /**
     * Insert a new record
     */
    public function insert($data)
    {
        try {
            $data['locale_source_plain'] = isset($data['locale_source']) ? $this->app['utils']->specialCharsToAsciiChars(strip_tags($data['locale_source']), true) : '';
            $data['translation_text_plain'] = isset($data['translation_text']) ? $this->app['utils']->specialCharsToAsciiChars(strip_tags($data['translation_text']), true) : '';
            $insert = array();
            foreach ($data as $key => $value) {
                $insert[$key] = is_string($value) ? $this->app['utils']->sanitizeText($value) : $value;
            }
            $this->app['db']->insert(self::$table_name, $insert);
            return $this->app['db']->lastInsertId();
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Count all records
     *
     * @throws \Exception
     */
    public function count()
    {
        try {
            $SQL = "SELECT COUNT(*) FROM `".self::$table_name."`";
            return $this->app['db']->fetchColumn($SQL);
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Select all unassigned records, ordered by `locale_source`.
     * Return FALSE if no records exists
     *
     * @throws \Exception
     * @return Ambigous <boolean, array>
     */
    public function selectAll()
    {
        try {
            $SQL = "SELECT * FROM `".self::$table_name."` ORDER BY `locale_source_plain` ASC";
            $results = $this->app['db']->fetchAll($SQL);
            $unassigneds = array();
            if (is_array($results)) {
                foreach ($results as $result) {
                    $unassigned = array();
                    foreach ($result as $key => $value) {
                        $unassigned[$key] = is_string($value) ? $this->app['utils']->unsanitizeText($value) : $value;
                        if ($key == 'file_path') {
                            $unassigned[$key] = realpath($value);
                        }
                    }
                    $unassigneds[] = $unassigned;
                }
            }
            return (!empty($unassigneds)) ? $unassigneds : false;
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Select all non custom translations which are not assigned
     *
     * @throws \Exception
     * @return Ambigous <boolean, array>
     */
    public function selectAllNonCustom()
    {
        try {
            $SQL = "SELECT * FROM `".self::$table_name."` WHERE `locale_type`!='CUSTOM' ORDER BY `locale_source_plain` ASC";
            $results = $this->app['db']->fetchAll($SQL);
            $unassigneds = array();
            if (is_array($results)) {
                foreach ($results as $result) {
                    $unassigned = array();
                    foreach ($result as $key => $value) {
                        $unassigned[$key] = is_string($value) ? $this->app['utils']->unsanitizeText($value) : $value;
                        if ($key == 'file_path') {
                            $unassigned[$key] = realpath($value);
                        }
                    }
                    $unassigneds[] = $unassigned;
                }
            }
            return (!empty($unassigneds)) ? $unassigneds : false;
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Select all custom translations which are not assigned
     *
     * @throws \Exception
     * @return Ambigous <boolean, array>
     */
    public function selectAllCustom()
    {
        try {
            $SQL = "SELECT * FROM `".self::$table_name."` WHERE `locale_type`='CUSTOM' ORDER BY `locale_source_plain` ASC";
            $results = $this->app['db']->fetchAll($SQL);
            $unassigneds = array();
            if (is_array($results)) {
                foreach ($results as $result) {
                    $unassigned = array();
                    foreach ($result as $key => $value) {
                        $unassigned[$key] = is_string($value) ? $this->app['utils']->unsanitizeText($value) : $value;
                        if ($key == 'file_path') {
                            $unassigned[$key] = realpath($value);
                        }
                    }
                    $unassigneds[] = $unassigned;
                }
            }
            return (!empty($unassigneds)) ? $unassigneds : false;
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }
    /**
     * Select the record for the given ID. Return FALSE if record not exists.
     *
     * @param integer $unassigned_id
     * @throws \Exception
     * @return Ambigous <boolean, array>
     */
    public function select($unassigned_id)
    {
        try {
            $SQL = "SELECT * FROM `".self::$table_name."` WHERE `unassigned_id`=$unassigned_id";
            $result = $this->app['db']->fetchAssoc($SQL);
            $unassigned = array();
            if (is_array($result)) {
                foreach ($result as $key => $value) {
                    $unassigned[$key] = is_string($value) ? $this->app['utils']->unsanitizeText($value) : $value;
                }
            }
            return (!empty($unassigned)) ? $unassigned : false;
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Select unassigned translations by the given file MD5
     *
     * @param string $file_md5
     * @throws \Exception
     * @return Ambigous <boolean, array>
     */
    public function selectByFileMD5($file_md5)
    {
        try {
            $SQL = "SELECT * FROM `".self::$table_name."` WHERE `file_md5`='$file_md5'";
            $results = $this->app['db']->fetchAll($SQL);
            $unassigneds = array();
            if (is_array($results)) {
                foreach ($results as $result) {
                    $unassigned = array();
                    foreach ($result as $key => $value) {
                        $unassigned[$key] = is_string($value) ? $this->app['utils']->unsanitizeText($value) : $value;
                        if ($key == 'file_path') {
                            $unassigned[$key] = realpath($value);
                        }
                    }
                    $unassigneds[] = $unassigned;
                }
            }
            return (!empty($unassigneds)) ? $unassigneds : false;
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Update the record for the given ID
     *
     * @param integer $unassigned_id
     * @param array $data
     * @throws \Exception
     */
    public function update($unassigned_id, $data)
    {
        try {
            if (isset($data['locale_source'])) {
                $data['locale_source_plain'] = $this->app['utils']->specialCharsToAsciiChars(strip_tags($data['locale_source']), true);
            }
            if (isset($data['translation_text'])) {
                $data['translation_text_plain'] = $this->app['utils']->specialCharsToAsciiChars(strip_tags($data['translation_text']), true);
            }
            $update = array();
            foreach ($data as $key => $value) {
                $update[$key] = is_string($value) ? $this->app['utils']->sanitizeText($value) : $value;
            }
            $this->app['db']->update(self::$table_name, $update, array('unassigned_id' => $unassigned_id));
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * Delete the record for the given ID
     *
     * @param integer $unassigned_id
     * @throws \Exception
     */
    public function delete($unassigned_id)
    {
        try {
            $this->app['db']->delete(self::$table_name, array('unassigned_id' => $unassigned_id));
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Check if a locale with the given checksum exists
     *
     * @param string $md5
     * @throws \Exception
     * @return Ambigous <boolean, integer>
     */
    public function existsMD5($md5)
    {
        try {
            $SQL = "SELECT `unassigned_id` FROM `".self::$table_name."` WHERE `locale_md5`='$md5'";
            $unassigned_id = $this->app['db']->fetchColumn($SQL);
            return ($unassigned_id > 0) ? $unassigned_id : false;
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }
}
