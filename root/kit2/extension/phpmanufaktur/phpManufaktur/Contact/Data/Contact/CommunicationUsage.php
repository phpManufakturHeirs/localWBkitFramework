<?php

/**
 * Contact
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de/Contact
 * @copyright 2013 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\Contact\Data\Contact;

use Silex\Application;

class CommunicationUsage
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
        self::$table_name = FRAMEWORK_TABLE_PREFIX.'contact_communication_usage';
    }

    /**
     * Create the COMMUNICATION USAGE table
     *
     * @throws \Exception
     */
    public function createTable()
    {
        $table = self::$table_name;
        $SQL = <<<EOD
    CREATE TABLE IF NOT EXISTS `$table` (
        `communication_usage_id` INT(11) NOT NULL AUTO_INCREMENT,
        `communication_usage_name` VARCHAR(32) NOT NULL DEFAULT '',
        `communication_usage_description` VARCHAR(255) NOT NULL DEFAULT '',
        PRIMARY KEY (`communication_usage_id`),
        UNIQUE (`communication_usage_name`)
        )
    COMMENT='The communication usage definition table'
    ENGINE=InnoDB
    AUTO_INCREMENT=1
    DEFAULT CHARSET=utf8
    COLLATE='utf8_general_ci'
EOD;
        try {
            $this->app['db']->query($SQL);
            $this->app['monolog']->addInfo("Created table 'contact_communication_usage'", array(__METHOD__, __LINE__));
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
            $this->app['monolog']->addInfo("Drop table 'contact_communication_usage'", array(__METHOD__, __LINE__));
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Initialize the communication usage list with the defaults from /communication.usages.json
     *
     * @throws \Exception
     */
    public function initCommunicationUsageList()
    {
        try {
            // get the number of titles in the list
            $count = $this->app['db']->fetchColumn("SELECT COUNT(`communication_usage_id`) FROM `".self::$table_name."`");
            if ($count < 1) {
                // no entries!
                $json_import = MANUFAKTUR_PATH.'/Contact/Data/Setup/Import/communication.usages.json';
                if (!file_exists($json_import)) {
                    throw new \Exception("Can't read the communication usage definition list: $json_import");
                }
                $types = $this->app['utils']->readJSON($json_import);
                foreach ($types as $type) {
                    $this->app['db']->insert(self::$table_name, array(
                        'communication_usage_name' => $type['usage'],
                        'communication_usage_description' => $this->app['utils']->sanitizeText($type['description'])
                    ));
                }
            }
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Check if the communication usage exists
     *
     * @param string $usage
     * @throws \Exception
     * @return boolean
     */
    public function existsUsage($usage)
    {
        try {
            $SQL = "SELECT * FROM `".self::$table_name."` WHERE `communication_usage_name`='".strtoupper($usage)."'";
            $result = $this->app['db']->fetchAssoc($SQL);
            return isset($result['communication_usage_name']);
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Insert a new record
     *
     * @param array $data
     * @param integer reference $communication_usage_id
     * @throws \Exception
     */
    public function insert($data, &$communication_usage_id=-1)
    {
        try {
            $insert = array();
            foreach ($data as $key => $value) {
                if ($key === 'communication_usage_id') continue;
                $insert[$this->app['db']->quoteIdentifier($key)] = is_string($value) ? $this->app['utils']->sanitizeText($value) : $value;
            }
            $this->app['db']->insert(self::$table_name, $insert);
            $communication_usage_id = $this->app['db']->lastInsertId();
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Delete the given $usage - this will also delete associated communication records!
     *
     * @param string $usage
     * @throws \Exception
     */
    public function deleteUsage($usage)
    {
        try {
            $this->app['db']->delete(self::$table_name, array('communication_usage_name' => $usage));
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

}
