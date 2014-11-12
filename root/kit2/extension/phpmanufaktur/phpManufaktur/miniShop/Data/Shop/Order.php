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
use Carbon\Carbon;

class Order
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
        self::$table_name = FRAMEWORK_TABLE_PREFIX.'minishop_order';
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
        `guid` VARCHAR(64) NOT NULL DEFAULT '',
        `contact_id` INT(11) NOT NULL DEFAULT -1,
        `data` TEXT NOT NULL,
        `order_timestamp` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
        `confirmation_timestamp` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
        `order_total` FLOAT(11) NOT NULL DEFAULT 0,
        `payment_method` VARCHAR(64) NOT NULL DEFAULT 'UNKNOWN',
        `transaction_id` VARCHAR(256) NOT NULl DEFAULT 'NONE',
        `status` ENUM('PENDING','CONFIRMED','DELETED') NOT NULL DEFAULT 'PENDING',
        `timestamp` TIMESTAMP,
        PRIMARY KEY (`id`),
        UNIQUE (`guid`)
        )
    COMMENT='The order table for the miniShop'
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
     * Insert a new order
     *
     * @param array $data
     * @throws \Exception
     */
    public function insert($data)
    {
        try {
            $this->app['db']->insert(self::$table_name, $data);
            return $this->app['db']->lastInsertId();
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Check if a PENDING order exists for the given contact ID
     *
     * @param integer $contact_id
     * @throws \Exception
     * @return Ambigous <boolean, array>
     */
    public function existsPendingForContactID($contact_id)
    {
        try {
            $SQL = "SELECT * FROM `".self::$table_name."` WHERE `contact_id`=$contact_id AND `status`='PENDING'";
            $result = $this->app['db']->fetchAssoc($SQL);
            return (isset($result['id'])) ? $result : false;
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Select the record by the given ID
     *
     * @param integer $order_id
     * @throws \Exception
     * @return Ambigous <boolean, array>
     */
    public function select($order_id)
    {
        try {
            $SQL = "SELECT * FROM `".self::$table_name."` WHERE `id`=$order_id";
            $result = $this->app['db']->fetchAssoc($SQL);
            return (isset($result['id'])) ? $result : false;
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Select a order by the GUID
     *
     * @param string $guid
     * @throws \Exception
     * @return Ambigous <boolean, array>
     */
    public function selectByGUID($guid)
    {
        try {
            $SQL = "SELECT * FROM `".self::$table_name."` WHERE `guid`='$guid'";
            $result = $this->app['db']->fetchAssoc($SQL);
            return (isset($result['id'])) ? $result : false;
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Update the record with the given ID
     *
     * @param integer $order_id
     * @param array $data
     * @throws \Exception
     */
    public function update($order_id, $data)
    {
        try {
            $check = array('id', 'timestamp');
            foreach ($check as $key) {
                if (isset($data[$key])) {
                    unset($data[$key]);
                }
            }
            $this->app['db']->update(self::$table_name, $data, array('id' => $order_id));
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Select all orders, except deleted, sort by order timestamp, descending
     *
     * @throws \Exception
     * @return Ambigous <boolean, array>
     */
    public function selectAll($max_days=0)
    {
        try {
            if ($max_days > 0) {
                $dt = new Carbon();
                $dt->subDays($max_days);
                $ts = $dt->format('Y-m-d H:i:s');
                $SQL = "SELECT * FROM `".self::$table_name."` WHERE `status` != 'DELETED' AND ".
                    "`order_timestamp` > '$ts' ORDER BY `order_timestamp` DESC";
            }
            else {
                $SQL = "SELECT * FROM `".self::$table_name."` WHERE `status` != 'DELETED' ORDER BY `order_timestamp` DESC";
            }
            $results = $this->app['db']->fetchAll($SQL);
            $orders = array();
            if (is_array($results)) {
                foreach ($results as $result) {
                    $result['data'] = unserialize($result['data']);
                    $orders[] = $result;
                }
            }
            return (!empty($orders)) ? $orders : false;
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }
}
