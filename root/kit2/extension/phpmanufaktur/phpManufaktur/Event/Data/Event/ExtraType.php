<?php

/**
 * Event
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de/Event
 * @copyright 2013 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\Event\Data\Event;

use Silex\Application;

class ExtraType
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
        self::$table_name = FRAMEWORK_TABLE_PREFIX.'event_extra_type';
    }

    /**
     * Create the EVENT table
     *
     * @throws \Exception
     */
    public function createTable()
    {
        $table = self::$table_name;
        $SQL = <<<EOD
    CREATE TABLE IF NOT EXISTS `$table` (
        `extra_type_id` INT(11) NOT NULL AUTO_INCREMENT,
        `extra_type_type` ENUM('TEXT','HTML','VARCHAR','INT','FLOAT','DATE','DATETIME','TIME') NOT NULL DEFAULT 'VARCHAR',
        `extra_type_name` VARCHAR(64) NOT NULL DEFAULT '',
        `extra_type_description` TEXT NOT NULL,
        `extra_type_timestamp` TIMESTAMP,
        PRIMARY KEY (`extra_type_id`),
        UNIQUE (`extra_type_name`)
        )
    COMMENT='The table for definition of types for extra fields'
    ENGINE=InnoDB
    AUTO_INCREMENT=1
    DEFAULT CHARSET=utf8
    COLLATE='utf8_general_ci'
EOD;
        try {
            $this->app['db']->query($SQL);
            $this->app['monolog']->addInfo("Created table 'event_extra_type'", array(__METHOD__, __LINE__));
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
            $this->app['monolog']->addInfo("Drop table 'event_extra_type'", array(__METHOD__, __LINE__));
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Get a ExtraType record with the default settings
     *
     * @return array ExtraType
     */
    public function getDefaultRecord()
    {
        return array(
            'extra_type_id' => -1,
            'extra_type_type' => '',
            'extra_type_name' => '',
            'extra_type_description' => '',
            'extra_type_timestamp' => '0000-00-00 00:00:00'
        );
    }

    /**
     * Return a ExtraType array with the types for the usage in Twig
     *
     * @return array
     */
    public function getTypeArrayForTwig()
    {
        return array(
            'TEXT' => $this->app['translator']->trans('Text - plain'),
            'HTML' => $this->app['translator']->trans('Text - HTML'),
            'VARCHAR' => $this->app['translator']->trans('Text - 256 characters'),
            'INT' => $this->app['translator']->trans('Integer'),
            'FLOAT' => $this->app['translator']->trans('Float'),
            'DATE' => $this->app['translator']->trans('Date'),
            'DATETIME' => $this->app['translator']->trans('Date and Time')
        );
    }

    /**
     * Check if the desired Extra Type already existst. Optionally exclude the
     * given ID from the check
     *
     * @param string $type_name
     * @param integer $exclude_type_id
     * @throws \Exception
     * @return boolean
     */
    public function existsTypeName($type_name, $exclude_type_id=null)
    {
        try {
            $SQL = "SELECT `extra_type_name` FROM `".self::$table_name."` WHERE `extra_type_name`='$type_name'";
            if (is_numeric($exclude_type_id)) {
                $SQL .= " AND `extra_type_id` != '$exclude_type_id'";
            }
            $result = $this->app['db']->fetchColumn($SQL);
            return (strtoupper($result) == strtoupper($type_name)) ? true : false;
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Insert a new extra type record
     *
     * @param array $data
     * @param reference integer $type_id
     * @throws \Exception
     */
    public function insert($data, &$type_id=null)
    {
        try {
            $insert = array();
            foreach ($data as $key => $value) {
                if (($key == 'extra_type_id') || ($key == 'extra_type_timestamp')) continue;
                $insert[$this->app['db']->quoteIdentifier($key)] = is_string($value) ? $this->app['utils']->sanitizeText($value) : $value;
            }
            $this->app['db']->insert(self::$table_name, $insert);
            $type_id = $this->app['db']->lastInsertId();
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Update the extra type record for the given ID
     *
     * @param array $data
     * @param integer $type_id
     * @throws \Exception
     */
    public function update($data, $type_id)
    {
        try {
            $update = array();
            foreach ($data as $key => $value) {
                if (($key == 'extra_type_id') || ($key == 'extra_type_timestamp') || ($key == 'extra_type_name')) continue;
                $update[$this->app['db']->quoteIdentifier($key)] = is_string($value) ? $this->app['utils']->sanitizeText($value) : $value;
            }
            if (!empty($update)) {
                $this->app['db']->update(self::$table_name, $update, array('extra_type_id' => $type_id));
            }
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Return a array with all tags, prepared for usage with TWIG
     *
     * @throws \Exception
     * @return array
     */
    public function getArrayForTwig()
    {
        try {
            $SQL = "SELECT * FROM `".self::$table_name."` ORDER BY `extra_type_name` ASC";
            $results = $this->app['db']->fetchAll($SQL);
            $types = array();
            foreach ($results as $type) {
                $types[$type['extra_type_name']] = $this->app['utils']->humanize($type['extra_type_name']);
            }
            return $types;
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Select all available ExtraType records
     *
     * @throws \Exception
     * @return array
     */
    public function selectAll()
    {
        try {
            $results = $this->app['db']->fetchAll("SELECT * FROM `".self::$table_name."` ORDER BY `extra_type_name` ASC");
            $types = array();
            if (is_array($results)) {
                foreach ($results as $result) {
                    $record = array();
                    foreach ($result as $key => $value) {
                        $record[$key] = is_string($value) ? $this->app['utils']->unsanitizeText($value) : $value;
                    }
                    $types[] = $record;
                }
            }
            return $types;
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Select a extra type record by the given extra_type_id
     * Return FALSE if the record does not exists
     *
     * @param integer $extra_type_id
     * @throws \Exception
     * @return multitype:array|boolean
     */
    public function select($extra_type_id)
    {
        try {
            $SQL = "SELECT * FROM `".self::$table_name."` WHERE `extra_type_id`='$extra_type_id'";
            $result = $this->app['db']->fetchAssoc($SQL);
            if (is_array($result) && isset($result['extra_type_id'])) {
                $type = array();
                foreach ($result as $key => $value) {
                    $type[$key] = is_string($value) ? $this->app['utils']->unsanitizeText($value) : $value;
                }
                return $type;
            }
            else {
                return false;
            }
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Select a extra type record by the given extra_type_name
     * Return FALSE if the record does not exists
     *
     * @param integer $extra_type_name
     * @throws \Exception
     * @return multitype:array|boolean
     */
    public function selectName($extra_type_name)
    {
        try {
            $SQL = "SELECT * FROM `".self::$table_name."` WHERE `extra_type_name`='$extra_type_name'";
            $result = $this->app['db']->fetchAssoc($SQL);
            if (is_array($result) && isset($result['extra_type_name'])) {
                $type = array();
                foreach ($result as $key => $value) {
                    $type[$key] = is_string($value) ? $this->app['utils']->unsanitizeText($value) : $value;
                }
                return $type;
            }
            else {
                return false;
            }
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Delete the record with the given $extra_type_id
     *
     * @param integer $extra_type_id
     * @throws \Exception
     */
    public function delete($extra_type_id)
    {
        try {
            $this->app['db']->delete(self::$table_name, array('extra_type_id' => $extra_type_id));
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }
}
