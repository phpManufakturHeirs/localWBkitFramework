<?php

/**
 * Contact
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de/contact
 * @copyright 2013 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\Contact\Data\Contact;

use Silex\Application;

class Country
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
        self::$table_name = FRAMEWORK_TABLE_PREFIX.'contact_country';
    }

    /**
     * Create the COUNTRY table
     *
     * @throws \Exception
     */
    public function createTable()
    {
        $table = self::$table_name;
        $SQL = <<<EOD
    CREATE TABLE IF NOT EXISTS `$table` (
        `country_id` INT(11) NOT NULL AUTO_INCREMENT,
        `country_code` VARCHAR(3) NOT NULL DEFAULT '',
        `country_name` VARCHAR(128) NOT NULL DEFAULT 'NO COUNTRY',
        PRIMARY KEY (`country_id`),
        UNIQUE INDEX `country_code` (`country_code` ASC)
        )
    COMMENT='The country list for the contact application'
    ENGINE=InnoDB
    AUTO_INCREMENT=1
    DEFAULT CHARSET=utf8
    COLLATE='utf8_general_ci'
EOD;
        try {
            $this->app['db']->query($SQL);
            $this->app['monolog']->addInfo("Created table 'contact_country'", array(__METHOD__, __LINE__));
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
            $this->app['monolog']->addInfo("Drop table 'contact_country'", array(__METHOD__, __LINE__));
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Initialize the communication usage list with the defaults from /countries.json
     *
     * @throws \Exception
     */
    public function initCountryList()
    {
        try {
            // get the number of countries in the list
            $count = $this->app['db']->fetchColumn("SELECT COUNT(`country_id`) FROM `".self::$table_name."`");
            if ($count < 1) {
                // no entries!
                $json_import = MANUFAKTUR_PATH.'/Contact/Data/Setup/Import/countries.json';
                if (!file_exists($json_import)) {
                    throw new \Exception("Can't read the country definition list: $json_import");
                }
                $countries = $this->app['utils']->readJSON($json_import);
                foreach ($countries as $country) {
                    $this->app['db']->insert(self::$table_name, array(
                        'country_code' => $country['country_code'],
                        'country_name' => $this->app['utils']->sanitizeText($country['country_name'])
                    ));
                }
            }
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Check if the given country code exists
     *
     * @param string $country_code
     * @throws \Exception
     * @return boolean
     */
    public function existsCountryCode($country_code)
    {
        try {
            $SQL = "SELECT `country_code` FROM `".self::$table_name."` WHERE `country_code`='$country_code'";
            $result = $this->app['db']->fetchColumn($SQL);
            return ($country_code === $result) ? true : false;
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Select the full country name by the given country code
     *
     * @param string $country_code
     * @throws \Exception
     */
    public function selectCountry($country_code) {
        try {
            $SQL = "SELECT `country_name` FROM `".self::$table_name."` WHERE `country_code`='$country_code'";
            return $this->app['db']->fetchColumn($SQL);
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Return a array with all countries, prepared for usage with TWIG
     *
     * @throws \Exception
     * @return array
     */
    public function getArrayForTwig()
    {
        try {
            $SQL = "SELECT `country_code`, `country_name` FROM `".self::$table_name."` WHERE `country_code` != '' ORDER BY `country_name` ASC";
            $countries = $this->app['db']->fetchAll($SQL);
            $result = array();
            foreach ($countries as $country) {
                $result[$country['country_code']] = $this->app['utils']->unsanitizeText($country['country_name']);
            }
            return $result;
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

}