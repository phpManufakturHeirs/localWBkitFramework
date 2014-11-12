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
class i18nTranslationFile
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
        self::$table_name = FRAMEWORK_TABLE_PREFIX.'basic_i18n_translation_file';
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
        $table_translation = FRAMEWORK_TABLE_PREFIX.'basic_i18n_translation';
        $SQL = <<<EOD
    CREATE TABLE IF NOT EXISTS `$table` (
      `file_id` INT(11) NOT NULL AUTO_INCREMENT,
      `translation_id` INT(11) NOT NULL DEFAULT -1,
      `locale_id` INT(11) NOT NULL DEFAULT -1,
      `locale_locale` VARCHAR(2) NOT NULL DEFAULT 'EN',
      `locale_type` ENUM ('DEFAULT', 'CUSTOM', 'METRIC') NOT NULL DEFAULT 'DEFAULT',
      `extension` VARCHAR(64) NOT NULL DEFAULT '',
      `file_path` TEXT NOT NULL,
      `file_md5` VARCHAR(64) NOT NULL DEFAULT '',
      `timestamp` TIMESTAMP,
      PRIMARY KEY (`file_id`),
      INDEX (`locale_id`, `translation_id`),
      CONSTRAINT
        FOREIGN KEY (`locale_id`)
        REFERENCES `$table_source` (`locale_id`)
        ON DELETE CASCADE,
      CONSTRAINT
        FOREIGN KEY (`translation_id`)
        REFERENCES `$table_translation` (`translation_id`)
        ON DELETE CASCADE
    )
    COMMENT='Locale Translations'
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
     * Insert a new record - return the new file ID
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
            if (empty($insert)) {
                throw new \Exception('The received data record is empty!');
            }
            $this->app['db']->insert(self::$table_name, $insert);
            return $this->app['db']->lastInsertId();
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Select a translation file record by translation ID, locale and extension name
     *
     * @param string $translation_id
     * @param string $locale
     * @param string $extension
     * @throws \Exception
     * @return Ambigous <boolean, array>
     */
    public function selectByExtension($translation_id, $locale, $extension)
    {
        try {
            $SQL = "SELECT * FROM `".self::$table_name."` WHERE `translation_id`=$translation_id AND ".
                "`locale_locale`='$locale' AND `extension`='$extension'";
            $result = $this->app['db']->fetchAssoc($SQL);
            $file = array();
            if (is_array($result)) {
                foreach ($result as $key => $value) {
                    $file[$key] = is_string($value) ? $this->app['utils']->unsanitizeText($value) : $value;
                }
            }
            return (!empty($file)) ? $file : false;
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Select records by the given translation ID and locale
     *
     * @param integer $translation_id
     * @param string $locale
     * @throws \Exception
     * @return Ambigous <boolean, array>
     */
    public function selectByTranslationID($translation_id, $locale)
    {
        try {
            $SQL = "SELECT * FROM `".self::$table_name."` WHERE `translation_id`=$translation_id AND `locale_locale`='$locale'";
            $results = $this->app['db']->fetchAll($SQL);
            $files = array();
            if (is_array($results)) {
                foreach ($results as $result) {
                    $file = array();
                    foreach ($result as $key => $value) {
                        $file[$key] = is_string($value) ? $this->app['utils']->unsanitizeText($value) : $value;
                    }
                    $files[] = $file;
                }
            }
            return (!empty($files)) ? $files : false;
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Select all records for the given ID and locale.
     * Return FALSE if no record match.
     *
     * @param integer $locale_id
     * @param string $locale
     * @throws \Exception
     * @return Ambigous <boolean, array>
     */
    public function selectByLocaleID($locale_id, $locale)
    {
        try {
            $SQL = "SELECT * FROM `".self::$table_name."` WHERE `locale_id`=$locale_id AND `locale_locale`='$locale'";
            $results = $this->app['db']->fetchAll($SQL);
            $files = array();
            if (is_array($results)) {
                foreach ($results as $result) {
                    $file = array();
                    foreach ($result as $key => $value) {
                        $file[$key] = is_string($value) ? $this->app['utils']->unsanitizeText($value) : $value;
                    }
                    $files[] = $file;
                }
            }
            return (!empty($files)) ? $files : false;
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Select the translation file by the path MD5
     *
     * @param string $path_md5
     * @throws \Exception
     * @return Ambigous <boolean, array>
     */
    public function selectByPathMD5($path_md5)
    {
        try {
            $SQL = "SELECT * FROM `".self::$table_name."` WHERE `file_md5`='$path_md5'";
            $results = $this->app['db']->fetchAll($SQL);
            $files = array();
            if (is_array($results)) {
                foreach ($results as $result) {
                    $file = array();
                    foreach ($result as $key => $value) {
                        $file[$key] = is_string($value) ? $this->app['utils']->unsanitizeText($value) : $value;
                    }
                    $files[] = $file;
                }
            }
            return (!empty($files)) ? $files : false;
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Delete the record for the given file ID
     *
     * @param integer $file_id
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
     * Delete the record(s) for the given translation ID
     *
     * @param integer $translation_id
     * @throws \Exception
     */
    public function deleteByTranslationID($translation_id)
    {
        try {
            $this->app['db']->delete(self::$table_name, array('translation_id' => $translation_id));
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

}
