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
class i18nTranslation
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
        self::$table_name = FRAMEWORK_TABLE_PREFIX.'basic_i18n_translation';
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
        $SQL = <<<EOD
    CREATE TABLE IF NOT EXISTS `$table` (
      `translation_id` INT(11) NOT NULL AUTO_INCREMENT,
      `locale_id` INT(11) NOT NULL DEFAULT -1,
      `locale_locale` VARCHAR(2) NOT NULL DEFAULT 'EN',
      `locale_source` TEXT NOT NULL,
      `locale_source_plain` TEXT NOT NULL,
      `locale_md5` VARCHAR(64) NOT NULL DEFAULT '',
      `translation_text` TEXT NOT NULL,
      `translation_text_plain` TEXT NOT NULL,
      `translation_md5` VARCHAR(64) NOT NULL DEFAULT '',
      `translation_remark` TEXT NOT NULL,
      `translation_status` ENUM ('PENDING', 'TRANSLATED', 'CONFLICT', 'WIDOWED') NOT NULL DEFAULT 'PENDING',
      `timestamp` TIMESTAMP,
      PRIMARY KEY (`translation_id`),
      INDEX (`locale_id`),
      CONSTRAINT
        FOREIGN KEY (`locale_id`)
        REFERENCES `$table_source` (`locale_id`)
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
     * Check if the given locale ID exists for the language.
     *
     * @param unknown $locale_id
     * @throws \Exception
     * @return boolean
     */
    public function existsLocaleID($locale_id, $locale)
    {
        try {
            $SQL = "SELECT `locale_id` FROM `".self::$table_name."` WHERE `locale_id`=$locale_id AND `locale_locale`='$locale'";
            $id = $this->app['db']->fetchColumn($SQL);
            return ($id > 0);
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Insert a new translation record and return the last inserted ID
     *
     * @param array $data
     * @throws \Exception
     */
    public function insert($data)
    {
        try {
            $data['locale_source_plain'] = isset($data['locale_source']) ?  $this->app['utils']->specialCharsToAsciiChars(strip_tags($data['locale_source']), true) : '';
            $data['translation_text_plain'] = isset($data['translation_text']) ? $this->app['utils']->specialCharsToAsciiChars(strip_tags($data['translation_text']), true) : '';
            $insert = array();
            foreach ($data as $key => $value) {
                $insert[$key] = (is_string($value)) ? $this->app['utils']->sanitizeText($value) : $value;
            }
            $checks = array('locale_source', 'translation_text', 'translation_remark');
            foreach ($checks as $check) {
                if (!isset($insert[$key])) {
                    $insert[$key] = '';
                }
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
     * Get widowed translations which are no longer referenced in the sources
     *
     * @throws \Exception
     * @return Ambigous <boolean, array>
     */
    public function selectWidowed()
    {
        try {
            $SQL = "SELECT `locale_id` FROM `".self::$table_name."` WHERE `locale_id` ".
                "NOT IN (SELECT `locale_id` FROM `".FRAMEWORK_TABLE_PREFIX."basic_i18n_source`)";
            $widowed = $this->app['db']->fetchAll($SQL);
            return (!empty($widowed)) ? $widowed : false;
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Delete all records with the given locale ID
     *
     * @param integer $locale_id
     * @throws \Exception
     */
    public function deleteLocaleID($locale_id)
    {
        try {
            $this->app['db']->delete(self::$table_name, array('locale_id' => $locale_id));
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Delete the record with the given translation ID
     *
     * @param integer $translation_id
     * @throws \Exception
     */
    public function delete($translation_id)
    {
        try {
            $this->app['db']->delete(self::$table_name, array('translation_id' => $translation_id));
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Check if the given checksum for the locale exists. Return the translation ID or FALSE
     *
     * @param string $md5
     * @param string $locale
     * @throws \Exception
     * @return Ambigous <boolean, unknown>
     */
    public function existsMD5($md5, $locale)
    {
        try {
            $SQL = "SELECT `translation_id` FROM `".self::$table_name."` WHERE `locale_md5`='$md5' AND `locale_locale`='$locale'";
            $locale_id = $this->app['db']->fetchColumn($SQL);
            return ($locale_id > 0) ? $locale_id : false;
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Select the record for the given translation ID. Return FALSE if record not exists.
     *
     * @param integer $translation_id
     * @throws \Exception
     * @return Ambigous <boolean, array>
     */
    public function select($translation_id)
    {
        try {
            $SQL = "SELECT * FROM `".self::$table_name."` WHERE `translation_id`=$translation_id";
            $result = $this->app['db']->fetchAssoc($SQL);
            $translation = array();
            if (is_array($result)) {
                foreach ($result as $key => $value) {
                    $translation[$key] = is_string($value) ? $this->app['utils']->unsanitizeText($value) : $value;
                }
            }
            return (!empty($translation)) ? $translation : false;
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Count all CONFLICTS (locale independend)
     *
     * @throws \Exception
     * @return integer
     */
    public function countConflicts()
    {
        try {
            $SQL = "SELECT COUNT(`translation_status`) AS 'conflicts' FROM `".self::$table_name."` WHERE `translation_status`='CONFLICT'";
            $result = $this->app['db']->fetchColumn($SQL);
            return ($result > 0) ? $result : 0;
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Update the record for the given translation ID
     *
     * @param integer $translation_id
     * @param array $data
     * @throws \Exception
     */
    public function update($translation_id, $data)
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
            $this->app['db']->update(self::$table_name, $update, array('translation_id' => $translation_id));
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * Return all records which translation_status are set to CONFLICT.
     * Return FALSE if no conflict exists
     *
     * @throws \Exception
     * @return Ambigous <boolean, array>
     */
    public function selectConflicts()
    {
        try {
            $SQL = "SELECT * FROM `".self::$table_name."` WHERE `translation_status`='CONFLICT'";
            $results = $this->app['db']->fetchAll($SQL);
            $conflicts = array();
            if (is_array($results)) {
                foreach ($results as $result) {
                    $conflict = array();
                    foreach ($result as $key => $value) {
                        $conflict[$key] = is_string($value) ? $this->app['utils']->unsanitizeText($value) : $value;
                    }
                    $conflicts[] = $conflict;
                }
            }
            return (!empty($conflicts)) ? $conflicts : false;
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Select all duplicate translation entries
     *
     * @throws \Exception
     * @return integer
     */
    public function selectDuplicates()
    {
        try {
            $translation = self::$table_name;
            $file = FRAMEWORK_TABLE_PREFIX.'basic_i18n_translation_file';

            $SQL = "SELECT *, COUNT(`$file`.`locale_id`) AS 'duplicate' FROM `$translation` ".
                "LEFT JOIN `$file` ON `$file`.`translation_id`=`$translation`.`translation_id` ".
                "WHERE `$file`.`locale_type`='DEFAULT' AND `$translation`.`translation_status`='TRANSLATED' ".
                "GROUP BY `$file`.`locale_locale`, `$file`.`locale_id` HAVING COUNT(`$file`.`locale_id`) > 1";

            $results = $this->app['db']->fetchAll($SQL);
            $duplicates = array();
            if (is_array($results)) {
                foreach ($results as $result) {
                    $duplicate = array();
                    foreach ($result as $key => $value) {
                        $duplicate[$key] = is_string($value) ? $this->app['utils']->unsanitizeText($value) : $value;
                    }
                    $duplicates[] = $duplicate;
                }
            }
            return (!empty($duplicates)) ? $duplicates : 0;
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    public function selectTranslationStatus($locale)
    {
        try {
            $SQL = "SELECT COUNT(*) AS 'total', ".
                "SUM(CASE WHEN `translation_status`='TRANSLATED' THEN 1 ELSE 0 END) AS 'translated', ".
                "SUM(CASE WHEN `translation_status`='PENDING' THEN 1 ELSE 0 END) AS 'pending', ".
                "SUM(CASE WHEN `translation_status`='CONFLICT' THEN 1 ELSE 0 END) AS 'conflicts' ".
                "FROM `".self::$table_name."` WHERE `locale_locale`='$locale'";
            return $this->app['db']->fetchAssoc($SQL);
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Select all pending translations for the given locale. Return FALSE if
     * no pending translations exists
     *
     * @param string $locale
     * @throws \Exception
     * @return Ambigous <boolean, array>
     */
    public function selectPendings($locale)
    {
        try {
            $SQL = "SELECT * FROM `".self::$table_name."` WHERE `locale_locale`='$locale'  AND `translation_status`='PENDING'".
                "ORDER BY `locale_source_plain` ASC";
            $results = $this->app['db']->fetchAll($SQL);
            $pendings = array();
            if (is_array($results)) {
                foreach ($results as $result) {
                    $pending = array();
                    foreach ($result as $key => $value) {
                        $pending[$key] = is_string($value) ? $this->app['utils']->unsanitizeText($value) : $value;
                    }
                    $pendings[] = $pending;
                }
            }
            return (!empty($pendings)) ? $pendings : false;
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Select translation by the given file path MD5
     *
     * @param string $md5
     * @throws \Exception
     * @return Ambigous <boolean, array>
     */
    public function selectByFileMD5($md5)
    {
        try {
            $translation = self::$table_name;
            $file = FRAMEWORK_TABLE_PREFIX.'basic_i18n_translation_file';

            $SQL = "SELECT * FROM `$translation` ".
                "LEFT JOIN `$file` ON `$file`.`translation_id`=`$translation`.`translation_id` ".
                "WHERE `file_md5`='$md5' ORDER BY `translation_text_plain` ASC";
            $results = $this->app['db']->fetchAll($SQL);

            $translations = array();
            if (is_array($results)) {
                foreach ($results as $result) {
                    $translation = array();
                    foreach ($result as $key => $value) {
                        $translation[$key] = is_string($value) ? $this->app['utils']->unsanitizeText($value) : $value;
                    }
                    $translations[] = $translation;
                }
            }
            return (!empty($translations)) ? $translations : false;
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Select translations with the status TRANSLATED
     *
     * @param string $locale
     * @throws \Exception
     * @return Ambigous <boolean, array>
     */
    public function selectTranslated($locale, $type=null)
    {
        try {
            $translation = self::$table_name;
            $file = FRAMEWORK_TABLE_PREFIX.'basic_i18n_translation_file';

            $SQL = "SELECT * FROM `$translation` ".
                "LEFT JOIN `$file` ON `$file`.`translation_id`=`$translation`.`translation_id` ".
                "WHERE `$translation`.`locale_locale`='$locale' AND `translation_status`='TRANSLATED' ";

            if (is_null($type)) {
                $SQL .= "AND (`$file`.`locale_type`='DEFAULT' OR `$file`.`locale_type`='METRIC') ";
            }

            $SQL .= "ORDER BY `translation_text_plain` ASC";

            $results = $this->app['db']->fetchAll($SQL);
            $translateds = array();
            if (is_array($results)) {
                foreach ($results as $result) {
                    $translated = array();
                    foreach ($result as $key => $value) {
                        $translated[$key] = is_string($value) ? $this->app['utils']->unsanitizeText($value) : $value;
                    }
                    $translateds[] = $translated;
                }
            }
            return (!empty($translateds)) ? $translateds : false;
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Select translations with the status TRANSLATED and the locale type CUSTOM
     *
     * @param string $locale
     * @throws \Exception
     * @return Ambigous <boolean, array>
     */
    public function selectTranslatedCustom($locale)
    {
        try {
            $translation = self::$table_name;
            $file = FRAMEWORK_TABLE_PREFIX.'basic_i18n_translation_file';

            $SQL = "SELECT * FROM `$translation` ".
                "LEFT JOIN `$file` ON `$file`.`translation_id`=`$translation`.`translation_id` ".
                "WHERE `$translation`.`locale_locale`='$locale' AND `translation_status`='TRANSLATED' ".
                "AND `locale_type`='CUSTOM' ORDER BY `translation_text_plain` ASC";

            $results = $this->app['db']->fetchAll($SQL);
            $translateds = array();
            if (is_array($results)) {
                foreach ($results as $result) {
                    $translated = array();
                    foreach ($result as $key => $value) {
                        $translated[$key] = is_string($value) ? $this->app['utils']->unsanitizeText($value) : $value;
                    }
                    $translateds[] = $translated;
                }
            }
            return (!empty($translateds)) ? $translateds : false;
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }
}
