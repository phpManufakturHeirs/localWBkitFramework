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
use phpManufaktur\Contact\Data\Contact\Overview;
use phpManufaktur\Contact\Data\Contact\Message;
use Carbon\Carbon;

class Subscription
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
        self::$table_name = FRAMEWORK_TABLE_PREFIX.'event_subscription';
    }

    /**
     * Create the SUBSCRIPTION table
     *
     * @throws \Exception
     */
    public function createTable()
    {
        $table = self::$table_name;
        $table_event = FRAMEWORK_TABLE_PREFIX.'event_event';
        $SQL = <<<EOD
    CREATE TABLE IF NOT EXISTS `$table` (
        `subscription_id` INT(11) NOT NULL AUTO_INCREMENT,
        `event_id` INT(11) NOT NULL DEFAULT '-1',
        `contact_id` INT(11) NOT NULL DEFAULT '-1',
        `message_id` INT(11) NOT NULL DEFAULT '-1',
        `subscription_participants` INT(11) NOT NULL DEFAULT '0',
        `subscription_date` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
        `subscription_guid` VARCHAR(64) NOT NULL DEFAULT '',
        `subscription_confirmation` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
        `subscription_status` ENUM('PENDING','CONFIRMED','CANCELED','LOCKED','DELETED') NOT NULL DEFAULT 'PENDING',
        `subscription_timestamp` TIMESTAMP,
        PRIMARY KEY (`subscription_id`),
        INDEX (`event_id`, `contact_id`, `message_id`),
        UNIQUE (`subscription_guid`),
        CONSTRAINT
            FOREIGN KEY (`event_id`)
            REFERENCES $table_event (`event_id`)
            ON DELETE CASCADE
        )
    COMMENT='Images associated to Events'
    ENGINE=InnoDB
    AUTO_INCREMENT=1
    DEFAULT CHARSET=utf8
    COLLATE='utf8_general_ci'
EOD;
        try {
            $this->app['db']->query($SQL);
            $this->app['monolog']->addInfo("Created table 'event_subscription'", array(__METHOD__, __LINE__));
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
            $this->app['monolog']->addInfo("Drop table 'event_subscription'", array(__METHOD__, __LINE__));
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }
    /**
     * Get a default record for a subscription
     *
     * @param integer $event_id
     * @return multitype:number string unknown
     */
    public function getDefaultRecord($event_id=-1)
    {
        return array(
            'subscription_id' => -1,
            'event_id' => $event_id,
            'contact_id' => -1,
            'message_id' => -1,
            'subscription_participants' => 0,
            'subscription_date' => '0000-00-00 00:00:00',
            'subscription_guid' => '',
            'subscription_confirmation' => '0000-00-00 00:00:00',
            'subscription_status' => 'PENDING',
            'subscription_timestamp' => '0000-00-00 00:00:00'
        );
    }

    /**
     * Insert a new subscription
     *
     * @param array $data
     * @param reference integer $subscription_id
     * @throws \Exception
     */
    public function insert($data, &$subscription_id=null)
    {
        try {
            $insert = array();
            foreach ($data as $key => $value) {
                if (($key == 'subscription_id') || ($key == 'subscription_timestamp')) continue;
                $insert[$this->app['db']->quoteIdentifier($key)] = is_string($value) ? $this->app['utils']->unsanitizeText($value) : $value;
            }
            if (!isset($data['event_id'])) {
                throw new \Exception("Missing the Event ID, can't insert the subscription!");
            }
            $this->app['db']->insert(self::$table_name, $insert);
            $subscription_id = $this->app['db']->lastInsertId();
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Select all subsriptions for the given Event ID
     *
     * @param integer $event_id
     * @throws \Exception
     * @return multitype:multitype:unknown
     */
    public function selectByEventID($event_id, $status='CONFIRMED', $status_operator='=')
    {
        try {
            $SQL = "SELECT * FROM `".self::$table_name."` WHERE `event_id`='$event_id' AND `subscription_status`$status_operator'$status'";
            return $this->app['db']->fetchAll($SQL);
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    public function countParticipants($event_id, $status='CONFIRMED', $status_operator='=')
    {
        try {
            $SQL = "SELECT COUNT(`subscription_id`) AS `participants` FROM `".self::$table_name."` WHERE `event_id`='$event_id' AND `subscription_status`$status_operator'$status'";
            return $this->app['db']->fetchColumn($SQL);
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Check if the contact ID is already subscribed for the given event ID.
     * Return the subscription ID or FALSE if not subscribed.
     *
     * @param integer $event_id
     * @param integer $contact_id
     * @throws \Exception
     * @return Ambigous <boolean, unknown>
     */
    public function isAlreadySubscribedForEvent($event_id, $contact_id)
    {
        try {
            $SQL = "SELECT `subscription_id` FROM `".self::$table_name."` WHERE `event_id`='$event_id' AND `contact_id`='$contact_id'";
            $subscription_id = $this->app['db']->fetchColumn($SQL);
            return ($subscription_id > 0) ? $subscription_id : false;
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Select a record by the subscription ID
     *
     * @param integer $subscription_id
     * @throws \Exception
     */
    public function select($subscription_id)
    {
        try {
            $SQL = "SELECT * FROM `".self::$table_name."` WHERE `subscription_id`='$subscription_id'";
            $result = $this->app['db']->fetchAssoc($SQL);
            return (isset($result['subscription_id'])) ? $result : false;
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Select a subscription record by the given GUID
     *
     * @param string $guid
     * @throws \Exception
     * @return Ambigous <boolean, array> the record on success otherwise false
     */
    public function selectGUID($guid)
    {
        try {
            $SQL = "SELECT * FROM `".self::$table_name."` WHERE `subscription_guid`='$guid'";
            $result = $this->app['db']->fetchAssoc($SQL);
            return (isset($result['subscription_id'])) ? $result : false;
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Update a subscription record
     *
     * @param integer $subscription_id
     * @param array $data
     * @throws \Exception
     */
    public function update($subscription_id, $data)
    {
        try {
            $this->app['db']->update(self::$table_name, $data, array('subscription_id' => $subscription_id));
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Return a list with subscriptions
     *
     * @param integer $limit
     * @throws \Exception
     * @return array
     */
    public function selectList($limit=150, $add_days_to_event=-1)
    {
        if ($add_days_to_event < 1) {
            $add_days_to_event = 0;
        }
        $event_table = FRAMEWORK_TABLE_PREFIX.'event_event';
        $subscription_table = self::$table_name;
        $date = Carbon::create();
        $date->subDays($add_days_to_event);
        $date_to = $date->toDateTimeString();
        $SQL = "SELECT * FROM `$subscription_table`, `$event_table` WHERE $subscription_table.event_id=$event_table.event_id ".
            "AND $event_table.event_date_to >= '$date_to' ORDER BY `subscription_date` DESC LIMIT $limit";

        $results = $this->app['db']->fetchAll($SQL);
        $subscriptions = array();
        $EventData = new Event($this->app);
        $ContactOverview = new Overview($this->app);
        $MessageData = new Message($this->app);
        foreach ($results as $subscription) {
            if (false === ($event = $EventData->selectEvent($subscription['event_id'], false))) {
                throw new \Exception('Missing the event ID '.$subscription['event_id']);
            }
            if (false === ($contact = $ContactOverview->select($subscription['contact_id']))) {
                throw new \Exception('Missing the contact ID '.$subscription['contact_id']);
            }
            $message = '';
            if ($subscription['message_id'] > 0) {
                if (false === ($msg = $MessageData->select($subscription['message_id']))) {
                    throw new \Exception('Missing the message ID '.$subscription['message_id']);
                }
                $message = $msg['message_content'];
            }
            $subscriptions[] = array(
                'subscription' => $subscription,
                'contact' => $contact,
                'event' => $event,
                'message' => $message
            );
        }
        return $subscriptions;
    }
}
