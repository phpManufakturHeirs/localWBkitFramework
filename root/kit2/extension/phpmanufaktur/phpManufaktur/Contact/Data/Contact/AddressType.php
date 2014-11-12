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

class AddressType
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
        self::$table_name = FRAMEWORK_TABLE_PREFIX.'contact_address_type';
    }

    /**
     * Create the ADDRESS TYPE table
     *
     * @throws \Exception
     */
    public function createTable()
    {
        $table = self::$table_name;
        $SQL = <<<EOD
    CREATE TABLE IF NOT EXISTS `$table` (
        `address_type_id` INT(11) NOT NULL AUTO_INCREMENT,
        `address_type_name` VARCHAR(32) NOT NULL DEFAULT '',
        `address_type_description` VARCHAR(255) NOT NULL DEFAULT '',
        PRIMARY KEY (`address_type_id`),
        UNIQUE (`address_type_name`)
        )
    COMMENT='The address type definition table'
    ENGINE=InnoDB
    AUTO_INCREMENT=1
    DEFAULT CHARSET=utf8
    COLLATE='utf8_general_ci'
EOD;
        try {
            $this->app['db']->query($SQL);
            $this->app['monolog']->addInfo("Created table 'contact_address_type'", array(__METHOD__, __LINE__));
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
            $this->app['monolog']->addInfo("Drop table 'contact_address'", array(__METHOD__, __LINE__));
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Initialize the address type list with the defaults from /communication.types.json
     *
     * @throws \Exception
     */
    public function initAddressTypeList()
    {
        try {
            // get the number of titles in the list
            $count = $this->app['db']->fetchColumn("SELECT COUNT(`address_type_id`) FROM `".self::$table_name."`");
            if ($count < 1) {
                // no entries!
                $json_import = MANUFAKTUR_PATH.'/Contact/Data/Setup/Import/address.types.json';
                if (!file_exists($json_import)) {
                    throw new \Exception("Can't read the address type definition list: $json_import", array(__METHOD__, __LINE__));
                }
                $types = $this->app['utils']->readJSON($json_import);
                foreach ($types as $type) {
                    $this->app['db']->insert(self::$table_name, array(
                        'address_type_name' => $type['type'],
                        'address_type_description' => $this->app['utils']->sanitizeText($type['description'])
                    ));
                }
            }
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Check if the given address type exists
     *
     * @param string $type_name
     * @throws \Exception
     * @return boolean
     */
    public function existsAdressType($type_name)
    {
        try {
            $type_name = strtoupper($type_name);
            $SQL = "SELECT `address_type_name` FROM `".self::$table_name."` WHERE `address_type_name`='$type_name'";
            $type = $this->app['db']->fetchColumn($SQL);
            return ($type === $type_name);
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Insert a new address type
     *
     * @param string $type_name
     * @param string $type_description
     * @param integer reference $address_type_id
     * @throws \Exception
     */
    public function insertAddressType($type_name, $type_description, &$address_type_id=-1)
    {
        try {
            $this->app['db']->insert(self::$table_name, array(
                'address_type_name' => strtoupper($type_name),
                'address_type_description' => $this->app['utils']->sanitizeText($type_description)
            ));
            $address_type_id = $this->app['db']->lastInsertId();
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Delete the given address type
     *
     * @param string $type_name
     * @throws \Exception
     */
    public function deleteAddressType($type_name)
    {
        try {
            $this->app['db']->delete(self::$table_name, array('address_type_name' => $type_name));
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

}
