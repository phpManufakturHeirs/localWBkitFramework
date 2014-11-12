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

class Protocol
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
        self::$table_name = FRAMEWORK_TABLE_PREFIX.'contact_protocol';
    }

    /**
     * Create the Tag table
     *
     * @throws \Exception
     */
    public function createTable()
    {
        $table = self::$table_name;
        $table_contact = FRAMEWORK_TABLE_PREFIX.'contact_contact';
        $SQL = <<<EOD
    CREATE TABLE IF NOT EXISTS `$table` (
        `protocol_id` INT(11) NOT NULL AUTO_INCREMENT,
        `contact_id` INT(11) NOT NULL DEFAULT '-1',
        `protocol_date` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
        `protocol_text` TEXT NOT NULL,
        `protocol_originator` VARCHAR(64) NOT NULL DEFAULT '',
        `protocol_timestamp` TIMESTAMP,
        PRIMARY KEY (`protocol_id`),
        INDEX (`contact_id`),
        CONSTRAINT
            FOREIGN KEY (`contact_id`)
            REFERENCES `$table_contact` (`contact_id`)
            ON DELETE CASCADE
        )
    COMMENT='The protocol for the contact table'
    ENGINE=InnoDB
    AUTO_INCREMENT=1
    DEFAULT CHARSET=utf8
    COLLATE='utf8_general_ci'
EOD;
        try {
            $this->app['db']->query($SQL);
            $this->app['monolog']->addInfo("Created table 'contact_tag'", array(__METHOD__, __LINE__));
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
            $this->app['monolog']->addInfo("Drop table 'contact_protocol'", array(__METHOD__, __LINE__));
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Insert a protocol entry in regular way
     *
     * @param array $data
     * @param integer reference $protocol_id
     * @throws \Exception
     */
    public function insert($data, &$protocol_id)
    {
        try {
            $insert = array();
            foreach ($data as $key => $value) {
                if (($key == 'protocol_id') || ($key == 'protocol_timestamp')) continue;
                $insert[$this->app['db']->quoteIdentifier($key)] = is_string($value) ? $this->app['utils']->sanitizeText($value) : $value;
            }
            $this->app['db']->insert(self::$table_name, $insert);
            $protocol_id = $this->app['db']->lastInsertId();
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Insert a protocol entry. Set actual date and actual user if possible.
     *
     * @param integer $contact_id
     * @param string $protocol_text
     * @param string $protocol_date
     * @param string $protocol_originator
     * @param integer reference $protocol_id
     */
    public function addInfo($contact_id, $protocol_text, $protocol_date='0000-00-00 00:00:00', $protocol_originator='SYSTEM', &$protocol_id=-1)
    {
        if ($protocol_date == '0000-00-00 00:00:00') {
            $protocol_date = date('Y-m-d H:i:s');
        }
        if (($protocol_originator == 'SYSTEM') && $this->app['account']->isAuthenticated()) {
            // if the user is authenticated use his displayname instead
            $protocol_originator = $this->app['account']->getDisplayName();
        }

        $data = array(
            'contact_id' => $contact_id,
            'protocol_text' => $protocol_text,
            'protocol_originator' => $protocol_originator,
            'protocol_date' => $protocol_date
        );
        $this->insert($data, $protocol_id);
    }

  }
