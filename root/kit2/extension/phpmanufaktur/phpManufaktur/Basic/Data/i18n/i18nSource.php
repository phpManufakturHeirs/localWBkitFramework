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
class i18nSource
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
        self::$table_name = FRAMEWORK_TABLE_PREFIX.'basic_i18n_source';
    }

    /**
     * Create the table 'basic_locale_source'
     *
     * @throws \Exception
     */
    public function createTable()
    {
        $table = self::$table_name;
        $SQL = <<<EOD
    CREATE TABLE IF NOT EXISTS `$table` (
      `locale_id` INT(11) NOT NULL AUTO_INCREMENT,
      `locale_source` TEXT NOT NULL,
      `locale_source_plain` TEXT NOT NULL,
      `locale_locale` VARCHAR(2) NOT NULL DEFAULT 'EN',
      `locale_md5` VARCHAR (64) NOT NULL DEFAULT '',
      `locale_remark` TEXT NOT NULL,
      `timestamp` TIMESTAMP,
      PRIMARY KEY (`locale_id`),
      UNIQUE (`locale_md5`)
    )
    COMMENT='Locale source files for the localeEditor'
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
     * Check if a locale with the given checksum exists
     *
     * @param string $md5
     * @throws \Exception
     * @return Ambigous <boolean, integer>
     */
    public function existsMD5($md5)
    {
        try {
            $SQL = "SELECT `locale_id` FROM `".self::$table_name."` WHERE `locale_md5`='$md5'";
            $locale_id = $this->app['db']->fetchColumn($SQL);
            return ($locale_id > 0) ? $locale_id : false;
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
            $data['locale_source_plain'] = isset($data['locale_source']) ? $this->app['utils']->specialCharsToAsciiChars(strip_tags($data['locale_source']), true) : '';
            $insert = array();
            foreach ($data as $key => $value) {
                $insert[$key] = (is_string($value)) ? $this->app['utils']->sanitizeText($value) : $value;
            }
            if (!isset($insert['locale_source']) || empty($insert['locale_source'])) {
                throw new \Exception("The field 'locale_source' must contain a locale string!");
            }
            if (!isset($insert['locale_remark'])) {
                $insert['locale_remark'] = '';
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
     * Get widowed locales which are no longer referenced in any file
     *
     * @throws \Exception
     * @return Ambigous <boolean, array>
     */
    public function selectWidowed()
    {
        try {
            $SQL = "SELECT * FROM `".self::$table_name."` WHERE `locale_id` ".
                "NOT IN (SELECT `locale_id` FROM `".FRAMEWORK_TABLE_PREFIX."basic_i18n_reference`)";
            $results = $this->app['db']->fetchAll($SQL);
            $widowed = array();
            if (is_array($results)) {
                foreach ($results as $result) {
                    $widow = array();
                    foreach ($result as $key => $value) {
                        $widow[$key] = is_string($value) ? $this->app['utils']->unsanitizeText($value) : $value;
                    }
                    $widowed[] = $widow;
                }
            }
            return (!empty($widowed)) ? $widowed : false;
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Delete the record with the given locale ID
     *
     * @param integer $locale_id
     * @throws \Exception
     */
    public function delete($locale_id)
    {
        try {
            $this->app['db']->delete(self::$table_name, array('locale_id' => $locale_id));
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Return all locale sources
     *
     * @throws \Exception
     * @return Ambigous <boolean, array>
     */
    public function selectAll($order_by=null, $order_direction=null, $tab=null)
    {
        try {
            $SQL = "SELECT * FROM `".self::$table_name."`";
            if (!is_null($tab)) {
                $SQL .= " WHERE `locale_source_plain` ";
                switch ($tab) {
                    case 'a-c':
                        $SQL .= "RLIKE '^[A-C]'"; break;
                    case 'd-f':
                        $SQL .= "RLIKE '^[D-F]'"; break;
                    case 'g-i':
                        $SQL .= "RLIKE '^[G-I]'"; break;
                    case 'j-l':
                        $SQL .= "RLIKE '^[J-L]'"; break;
                    case 'm-p':
                        $SQL .= "RLIKE '^[M-P]'"; break;
                    case 'q-s':
                        $SQL .= "RLIKE '^[Q-S]'"; break;
                    case 't':
                        $SQL .= "RLIKE '^[T]'"; break;
                    case 'u-z':
                        $SQL .= "RLIKE '^[U-Z]'"; break;
                    case 'special':
                    default:
                        $SQL .= "NOT RLIKE '^[A-Z]'";
                        break;
                }
            }
            if (!is_null($order_by)) {
                $SQL .= " ORDER BY `$order_by` ";
                if (!is_null($order_direction)) {
                    $SQL .= "$order_direction";
                }
                else {
                    $SQL .= "ASC";
                }
            }

            $results = $this->app['db']->fetchAll($SQL);
            $sources = array();
            foreach ($results as $result) {
                $item = array();
                foreach ($result as $key => $value) {
                    $item[$key] = is_string($value) ? $this->app['utils']->unsanitizeText($value) : $value;
                }
                $sources[] = $item;
            }
            return (!empty($sources)) ? $sources : false;
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Select the record for the given locale ID
     *
     * @param integer $locale_id
     * @throws \Exception
     * @return Ambigous <boolean, array>
     */
    public function select($locale_id)
    {
        try {
            $SQL = "SELECT * FROM `".self::$table_name."` WHERE `locale_id`=$locale_id";
            $result = $this->app['db']->fetchAssoc($SQL);
            $source = array();
            if (is_array($result)) {
                foreach ($result as $key => $value) {
                    $source[$key] = is_string($value) ? $this->app['utils']->unsanitizeText($value) : $value;
                }
            }
            return (!empty($source)) ? $source : false;
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Update the record for the given locale ID
     *
     * @param integer $locale_id
     * @param array $data
     * @throws \Exception
     */
    public function update($locale_id, $data)
    {
        try {
            $check = array('locale_id', 'timestamp');
            foreach ($check as $key) {
                if (isset($data[$key])) {
                    unset($data[$key]);
                }
            }
            if (isset($data['locale_source'])) {
                $data['locale_source_plain'] = $this->app['utils']->specialCharsToAsciiChars(strip_tags($data['locale_source']), true);
            }
            $update = array();
            foreach ($data as $key => $value) {
                $update[$key] = is_string($value) ? $this->app['utils']->sanitizeText($value) : $value;
            }
            if (!empty($update)) {
                $this->app['db']->update(self::$table_name, $update, array('locale_id' => $locale_id));
            }
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }
}
