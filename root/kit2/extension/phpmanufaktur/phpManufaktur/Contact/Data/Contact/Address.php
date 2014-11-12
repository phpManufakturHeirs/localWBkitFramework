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

class Address
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
        self::$table_name = FRAMEWORK_TABLE_PREFIX.'contact_address';
    }

    /**
     * Create the ADDRESS table
     *
     * @throws \Exception
     */
    public function createTable()
    {
        $table = self::$table_name;
        $table_contact = FRAMEWORK_TABLE_PREFIX.'contact_contact';
        $table_country = FRAMEWORK_TABLE_PREFIX.'contact_country';
        $table_address_type = FRAMEWORK_TABLE_PREFIX.'contact_address_type';

        $foreign_key_1 = self::$table_name.'_ibfk_1';
        $foreign_key_2 = self::$table_name.'_ibfk_2';
        $foreign_key_3 = self::$table_name.'_ibfk_3';
        $foreign_key_4 = self::$table_name.'_ibfk_4';

        $SQL = <<<EOD
    CREATE TABLE IF NOT EXISTS `$table` (
        `address_id` INT(11) NOT NULL AUTO_INCREMENT,
        `contact_id` INT(11) NOT NULL DEFAULT '-1',
        `address_type` VARCHAR(32) NOT NULL DEFAULT 'PRIMARY',
        `address_identifier` VARCHAR(64) NOT NULL DEFAULT '',
        `address_description` TEXT NOT NULL,
        `address_street` VARCHAR(128) NOT NULL DEFAULT '',
        `address_appendix_1` VARCHAR(128) NOT NULL DEFAULT '',
        `address_appendix_2` VARCHAR(128) NOT NULL DEFAULT '',
        `address_zip` VARCHAR(32) NOT NULL DEFAULT '',
        `address_city` VARCHAR(128) NOT NULL DEFAULT '',
        `address_area` VARCHAR(128) NOT NULL DEFAULT '',
        `address_state` VARCHAR(128) NOT NULL DEFAULT '',
        `address_country_code` VARCHAR(8) NOT NULL DEFAULT '',
        `address_status` ENUM('ACTIVE', 'LOCKED', 'DELETED') NOT NULL DEFAULT 'ACTIVE',
        `address_timestamp` TIMESTAMP,
        PRIMARY KEY (`address_id`),
        INDEX `contact_id` (`contact_id` ASC) ,
        INDEX `country_code_idx` (`address_country_code` ASC) ,
        INDEX `address_type_idx` (`address_type` ASC) ,
        CONSTRAINT
            FOREIGN KEY (`contact_id` )
            REFERENCES `$table_contact` (`contact_id` )
            ON DELETE CASCADE,
        CONSTRAINT
            FOREIGN KEY (`address_country_code` )
            REFERENCES `$table_country` (`country_code` )
            ON UPDATE CASCADE,
        CONSTRAINT
            FOREIGN KEY (`address_type` )
            REFERENCES `$table_address_type` (`address_type_name` )
            ON UPDATE CASCADE
        )
    COMMENT='The contact address table'
    ENGINE=InnoDB
    AUTO_INCREMENT=1
    DEFAULT CHARSET=utf8
    COLLATE='utf8_general_ci'
EOD;
        try {
            $this->app['db']->query($SQL);
            $this->app['monolog']->addInfo("Created table 'contact_address'", array(__METHOD__, __LINE__));
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
     * Get a default (empty) ADDRESS record
     *
     * @return array
     */
    public function getDefaultRecord()
    {
        return array(
            'address_id' => -1,
            'contact_id' => -1,
            'address_type' => 'OTHER',
            'address_identifier' => '',
            'address_description' => '',
            'address_street' => '',
            'address_appendix_1' => '',
            'address_appendix_2' => '',
            'address_zip' => '',
            'address_city' => '',
            'address_area' => '',
            'address_state' => '',
            'address_country_code' => '',
            'address_status' => 'ACTIVE',
            'address_timestamp' => '0000-00-00 00:00:00'
        );
    }

    /**
     * Insert a new ADDRESS record
     *
     * @param array $data
     * @param reference integer $address_id
     * @throws \Exception
     */
    public function insert($data, &$address_id=null)
    {
        try {
            $insert = array();
            foreach ($data as $key => $value) {
                if (($key == 'address_id') || ($key == 'address_timestamp')) continue;
                $insert[$this->app['db']->quoteIdentifier($key)] = is_string($value) ? $this->app['utils']->sanitizeText($value) : $value;
            }
            $this->app['db']->insert(self::$table_name, $insert);
            $address_id = $this->app['db']->lastInsertId();
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Return all ADDRESS records for the given Contact ID
     *
     * @param integer $contact_id
     * @param string $status
     * @param string $status_operator
     * @throws \Exception
     * @return array|boolean
     */
    public function selectByContactID($contact_id, $status='DELETED', $status_operator='!=')
    {
        try {
            $SQL = "SELECT * FROM `".self::$table_name."` WHERE `contact_id`='$contact_id' AND `address_status`{$status_operator}'{$status}'";
            $results = $this->app['db']->fetchAll($SQL);
            if (is_array($results)) {
                $address = array();
                $level = 0;
                foreach ($results as $result) {
                    foreach ($result as $key => $value) {
                        $address[$level][$key] = is_string($value) ? $this->app['utils']->unsanitizeText($value) : $value;
                    }
                    $level++;
                }
                return $address;
            }
            else {
                return false;
            }
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Return a address record for the giben address_id.
     *
     * @param integer $address_id
     * @throws \Exception
     * @return multitype:unknown |boolean
     */
    public function select($address_id)
    {
        try {
            $SQL = "SELECT * FROM `".self::$table_name."` WHERE `address_id`='$address_id'";
            $result = $this->app['db']->fetchAssoc($SQL);
            if (is_array($result)) {
                $address = array();
                foreach ($result as $key => $value) {
                    $address[$key] = is_string($value) ? $this->app['utils']->unsanitizeText($value) : $value;
                }
                return $address;
            }
            else {
                return false;
            }
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Mark the given $address_id as deleted but does not delete the record physically
     *
     * @param integer $address_id
     * @throws \Exception
     */
    public function delete($address_id)
    {
        try {
            $this->app['db']->update(self::$table_name, array('address_status' => 'DELETED'), array('address_id' => $address_id));
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Update the address record for the given ID
     *
     * @param array $data
     * @param integer $address_id
     * @throws \Exception
     */
    public function update($data, $address_id)
    {
        try {
            $update = array();
            foreach ($data as $key => $value) {
                if (($key == 'address_id') || ($key == 'address_timestamp')) continue;
                $update[$this->app['db']->quoteIdentifier($key)] = is_string($value) ? $this->app['utils']->sanitizeText($value) : $value;
            }
            if (!empty($update)) {
                $this->app['db']->update(self::$table_name, $update, array('address_id' => $address_id));
            }
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }
}
