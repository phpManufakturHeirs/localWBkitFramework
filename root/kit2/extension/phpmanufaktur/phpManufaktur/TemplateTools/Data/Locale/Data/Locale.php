<?php

/**
 * TemplateTools
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de/TemplateTools
 * @copyright 2014 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\TemplateTools\Data\Locale\Data;

use Silex\Application;

class Locale
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
        self::$table_name = FRAMEWORK_TABLE_PREFIX.'template_tools_locale';
    }

    /**
     * Create the LOCALE table
     *
     * @throws \Exception
     */
    public function createTable()
    {
        $table = self::$table_name;
        $SQL = <<<EOD
    CREATE TABLE IF NOT EXISTS `$table` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `code` VARCHAR(3) NOT NULL DEFAULT '',
        `name` VARCHAR(128) NOT NULL DEFAULT '',
        `native_name` VARCHAR(128) NOT NULL DEFAULT '',
        PRIMARY KEY (`id`),
        UNIQUE INDEX `code` (`code` ASC)
        )
    COMMENT='The LOCALE table used by the TemplateTools'
    ENGINE=InnoDB
    AUTO_INCREMENT=1
    DEFAULT CHARSET=utf8
    COLLATE='utf8_general_ci'
EOD;
        try {
            $this->app['db']->query($SQL);
            $this->app['monolog']->addInfo("Created table 'template_tools_locale'", array(__METHOD__, __LINE__));
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

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
            $this->app['monolog']->addInfo("Drop table 'template_tools_locale'", array(__METHOD__, __LINE__));
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Initialize the LOCALES with the defaults from /locales.json
     *
     * @throws \Exception
     */
    public function initLocaleList()
    {
        try {
            // get the number of locales in the list
            $count = $this->app['db']->fetchColumn("SELECT COUNT(`id`) FROM `".self::$table_name."`");
            if ($count < 1) {
                // no entries!
                $json_import = MANUFAKTUR_PATH.'/TemplateTools/Data/Setup/Import/locales.json';
                if (!file_exists($json_import)) {
                    throw new \Exception("Can't read the locales definition list: $json_import");
                }
                $locales = $this->app['utils']->readJSON($json_import);
                foreach ($locales as $locale) {
                    $this->app['db']->insert(self::$table_name, array(
                        'code' => $this->app['utils']->sanitizeText($locale['code']),
                        'name' => $this->app['utils']->sanitizeText($locale['name']),
                        'native_name' => $this->app['utils']->sanitizeText($locale['nativeName'])
                    ));
                }
            }
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Check if the given locale exists
     *
     * @param string $locale
     * @throws \Exception
     * @return boolean
     */
    public function existsCode($locale)
    {
        try {
            $locale = strtolower(trim($locale));
            $SQL = "SELECT `code` FROM `".self::$table_name."` WHERE `code`='$locale'";
            $result = $this->app['db']->fetchColumn($SQL);
            return ($result == $locale);
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Get the international name for the given locale code
     *
     * @param string $locale
     * @throws \Exception
     * @return string|boolean
     */
    public function getName($locale)
    {
        try {
            $locale = strtolower(trim($locale));
            $SQL = "SELECT `code`, `name` FROM `".self::$table_name."` WHERE `code`='$locale'";
            $result = $this->app['db']->fetchAssoc($SQL);
            if (isset($result['code']) && ($result['code'] == $locale)) {
                return $this->app['tools']->unsanitizeText($result['name']);
            }
            return false;
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Get the native name for the given locale code
     *
     * @param string $locale
     * @throws \Exception
     * @return string|boolean
     */
    public function getNativeName($locale)
    {
        try {
            $locale = strtolower(trim($locale));
            $SQL = "SELECT `code`, `native_name` FROM `".self::$table_name."` WHERE `code`='$locale'";
            $result = $this->app['db']->fetchAssoc($SQL);
            if (isset($result['code']) && ($result['code'] == $locale)) {
                return $this->app['tools']->unsanitizeText($result['native_name']);
            }
            return false;
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }
}
