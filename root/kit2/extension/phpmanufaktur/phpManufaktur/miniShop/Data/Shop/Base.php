<?php

/**
 * miniShop
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de/miniShop
 * @copyright 2014 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\miniShop\Data\Shop;

use Silex\Application;

class Base
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
        self::$table_name = FRAMEWORK_TABLE_PREFIX.'minishop_base';
    }

    /**
     * Create the table
     *
     * @throws \Exception
     */
    public function createTable()
    {
        $table = self::$table_name;
        $SQL = <<<EOD
    CREATE TABLE IF NOT EXISTS `$table` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `name` VARCHAR(64) NOT NULL DEFAULT 'DEFAULT',
        `description` TEXT NOT NULL,
        `target_page_link` VARCHAR(256) NOT NULL DEFAULT '',
        `locale` VARCHAR(2) NOT NULL DEFAULT 'EN',
        `currency_iso` VARCHAR(3) NOT NULL DEFAULT 'EUR',
        `article_value_added_tax` FLOAT(11) NOT NULL DEFAULT 0,
        `article_price_type` ENUM ('NET_PRICE', 'GROSS_PRICE') DEFAULT 'GROSS_PRICE',
        `article_limit` INT(11) NOT NULL DEFAULT 99,
        `order_minimum_price` FLOAT(11) NOT NULL DEFAULT 0,
        `shipping_type` ENUM('FLATRATE', 'ARTICLE', 'NONE') DEFAULT 'FLATRATE',
        `shipping_flatrate` FLOAT(11) NOT NULL DEFAULT 0,
        `shipping_article` ENUM ('HIGHEST', 'LOWEST', 'SUM_UP') DEFAULT 'HIGHEST',
        `shipping_value_added_tax` FLOAT(11) NOT NULL DEFAULT 0,
        `payment_methods` SET ('ADVANCE_PAYMENT', 'ON_ACCOUNT', 'PAYPAL') NOT NULL DEFAULT 'ADVANCE_PAYMENT,PAYPAL',
        `terms_conditions_link` VARCHAR(256) NOT NULL DEFAULT '',
        `status` ENUM ('ACTIVE', 'LOCKED', 'DELETED') DEFAULT 'ACTIVE',
        `timestamp` TIMESTAMP,
        PRIMARY KEY (`id`),
        UNIQUE (`name`)
        )
    COMMENT='The base table for the miniShop'
    ENGINE=InnoDB
    AUTO_INCREMENT=1
    DEFAULT CHARSET=utf8
    COLLATE='utf8_general_ci'
EOD;
        try {
            $this->app['db']->query($SQL);
            $this->app['monolog']->addInfo('Created table '.$table, array(__METHOD__, __LINE__));
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Drop the table
     */
    public function dropTable()
    {
        $this->app['db.utils']->dropTable(self::$table_name);
    }

    /**
     * Select all bases entries, exclude deleted records
     *
     * @throws \Exception
     * @return Ambigous <boolean, array>
     */
    public function selectAll()
    {
        try {
            $SQL = "SELECT * FROM `".self::$table_name."` WHERE `status` != 'DELETED' ORDER BY `name` ASC";
            $results = $this->app['db']->fetchAll($SQL);
            $bases = array();
            if (is_array($results)) {
                foreach ($results as $result) {
                    $item = array();
                    foreach ($result as $key => $value) {
                        $item[$key] = (is_string($value)) ? $this->app['utils']->unsanitizeText($value) : $value;
                    }
                    $bases[] = $item;
                }
            }
            return (!empty($bases)) ? $bases : false;
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Select all active bases entries
     *
     * @throws \Exception
     * @return Ambigous <boolean, array>
     */
    public function selectAllActive()
    {
        try {
            $SQL = "SELECT * FROM `".self::$table_name."` WHERE `status`='ACTIVE' ORDER BY `name` ASC";
            $results = $this->app['db']->fetchAll($SQL);
            $bases = array();
            if (is_array($results)) {
                foreach ($results as $result) {
                    $item = array();
                    foreach ($result as $key => $value) {
                        $item[$key] = (is_string($value)) ? $this->app['utils']->unsanitizeText($value) : $value;
                    }
                    $bases[] = $item;
                }
            }
            return (!empty($bases)) ? $bases : false;
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Count all not deleted records
     *
     * @throws \Exception
     */
    public function count()
    {
        try {
            return $this->app['db']->fetchColumn("SELECT * FROM `".self::$table_name."` WHERE `status`!='DELETED'");
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Count all active base configuration records
     *
     * @throws \Exception
     */
    public function countActive()
    {
        try {
            return $this->app['db']->fetchColumn("SELECT * FROM `".self::$table_name."` WHERE `status`='ACTIVE'");
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Retrurn array with all available base names
     *
     * @throws \Exception
     * @return Ambigous <boolean, array>
     */
    public function selectBaseNames()
    {
        try {
            $SQL = "SELECT `name` FROM `".self::$table_name."` WHERE `status` != 'DELETED' ORDER BY `name` ASC";
            $results = $this->app['db']->fetchAll($SQL);
            $names = array();
            if (is_array($results)) {
                foreach ($results as $result) {
                    $names[$result['name']] = $this->app['utils']->humanize($result['name']);
                }
            }
            return (!empty($names)) ? $names : false;
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Get the values of the article price type emum field
     *
     * @return Ambigous <boolean, array>
     */
    public function getArticlePriceTypes()
    {
        return $this->app['db.utils']->getEnumValues(self::$table_name, 'article_price_type');
    }

    /**
     * Get the values for the shipping types
     *
     * @return Ambigous <boolean, array>
     */
    public function getShippingTypes()
    {
        return $this->app['db.utils']->getEnumValues(self::$table_name, 'shipping_type');
    }

    /**
     * Get the values for the base status
     *
     * @return Ambigous <boolean, array>
     */
    public function getStatusTypes()
    {
        return $this->app['db.utils']->getEnumValues(self::$table_name, 'status');
    }

    /**
     * Get the payment methods for the miniShop
     *
     * @return Ambigous <boolean, array>
     */
    public function getPaymentMethods()
    {
        return $this->app['db.utils']->getSetValues(self::$table_name, 'payment_methods');
    }

    /**
     * Check if the given $name is already in use.
     *
     * @param string $name
     * @param integer $ignore_id optional specify an ID to ignore
     * @throws \Exception
     * @return boolean
     */
    public function existsName($name, $ignore_id=null)
    {
        try {
            $SQL = "SELECT `id` FROM `".self::$table_name."` WHERE `name`='$name' AND `status`!= 'DELETED'";
            if (!is_null($ignore_id)) {
                $SQL .= " AND `id` != $ignore_id";
            }
            $id = $this->app['db']->fetchColumn($SQL);
            return ($id > 0);
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
            $insert = array();
            foreach ($data as $key => $value) {
                $insert[$key] = (is_string($value)) ? $this->app['utils']->sanitizeText($value) : $value;
            }
            if (isset($insert['id'])) {
                unset($insert['id']);
            }
            if (!isset($insert['description'])) {
                $insert['description'] = '';
            }
            $this->app['db']->insert(self::$table_name, $insert);
            return $this->app['db']->lastInsertId();
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Select the record with the given ID
     *
     * @param integer $id
     * @throws \Exception
     * @return Ambigous <boolean, array>
     */
    public function select($id)
    {
        try {
            $SQL = "SELECT * FROM `".self::$table_name."` WHERE `id`=$id";
            $result = $this->app['db']->fetchAssoc($SQL);
            $base = array();
            if (is_array($result)) {
                foreach ($result as $key => $value) {
                    $base[$key] = is_string($value) ? $this->app['utils']->unsanitizeText($value) : $value;
                }
            }
            return (!empty($base)) ? $base : false;
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Get the base ID by the given base name
     *
     * @param string $name
     * @throws \Exception
     * @return Ambigous <boolean, unknown>
     */
    public function getIDbyName($name)
    {
        try {
            $SQL = "SELECT `id` FROM `".self::$table_name."` WHERE `name`='$name'";
            $id = $this->app['db']->fetchColumn($SQL);
            return ($id > 0) ? $id : false;
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Update the record with the given ID
     *
     * @param integer $id
     * @param array $data
     * @throws \Exception
     */
    public function update($id, $data)
    {
        try {
            $check = array('id', 'timestamp');
            foreach ($check as $key) {
                if (isset($data[$key])) {
                    unset($data[$key]);
                }
            }
            $update = array();
            foreach ($data as $key => $value) {
                if (is_null($value)) {
                    continue;
                }
                $update[$key] = is_string($value) ? $this->app['utils']->sanitizeText($value) : $value;
            }
            if (!empty($update)) {
                $this->app['db']->update(self::$table_name, $update, array('id' => $id));
            }
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Delete the record with the given ID
     *
     * @param integer $id
     * @throws \Exception
     */
    public function delete($id)
    {
        try {
            $this->app['db']->delete(self::$table_name, array('id' => $id));
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }
}
