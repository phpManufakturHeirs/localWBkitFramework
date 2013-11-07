<?php

/**
 * Event
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de/FacebookGallery
 * @copyright 2013 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\Event\Data\Event;

use Silex\Application;
use phpManufaktur\Event\Data\Event\Description;
use phpManufaktur\Contact\Data\Contact\Contact;
use phpManufaktur\Event\Control\Command\EventICal;
use phpManufaktur\Event\Control\Command\EventQRCode;
use phpManufaktur\CommandCollection\Data\Rating\Rating as RatingData;
use phpManufaktur\CommandCollection\Data\Rating\RatingIdentifier;
use Carbon\Carbon;

class Event
{

    protected $app = null;
    protected static $table_name = null;
    protected $Description = null;
    protected $ExtraGroup = null;
    protected $ExtraType = null;
    protected $Extra = null;
    protected $Contact = null;
    protected $iCal = null;
    protected $QRCode = null;
    protected $Subscription = null;
    protected $RatingIdentifier = null;
    protected $RatingData = null;

    /**
     * Constructor
     *
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
        self::$table_name = FRAMEWORK_TABLE_PREFIX.'event_event';
        $this->Description = new Description($app);
        $this->ExtraGroup = new ExtraGroup($app);
        $this->ExtraType = new ExtraType($app);
        $this->Extra = new Extra($app);
        $this->Contact = new Contact($app);
        $this->iCal = new EventICal($app);
        $this->QRCode = new EventQRCode($app);
        $this->Subscription = new Subscription($app);
        $this->RatingData = new RatingData($app);
        $this->RatingIdentifier = new RatingIdentifier($app);
    }

    /**
     * Create the EVENT table
     *
     * @throws \Exception
     */
    public function createTable()
    {
        $table = self::$table_name;
        $group_table = FRAMEWORK_TABLE_PREFIX.'event_group';
        $SQL = <<<EOD
    CREATE TABLE IF NOT EXISTS `$table` (
        `event_id` INT(11) NOT NULL AUTO_INCREMENT,
        `group_id` INT(11) NOT NULL DEFAULT '-1',
        `event_type` ENUM('EVENT', 'DATE', 'TASK') NOT NULL DEFAULT 'EVENT',
        `event_organizer` INT(11) NOT NULL DEFAULT '-1',
        `event_location` INT(11) NOT NULL DEFAULT '-1',
        `event_date_from` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
        `event_date_to` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
        `event_publish_from` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
        `event_publish_to` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
        `event_costs` FLOAT NOT NULL DEFAULT '0',
        `event_participants_max` INT(11) NOT NULL DEFAULT '-1',
        `event_deadline` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
        `event_url` TEXT NOT NULL,
        `event_status` ENUM('ACTIVE', 'LOCKED', 'DELETED') NOT NULL DEFAULT 'ACTIVE',
        `event_timestamp` TIMESTAMP,
        PRIMARY KEY (`event_id`),
        FOREIGN KEY (`group_id`) REFERENCES $group_table(`group_id`) ON DELETE CASCADE
        )
    COMMENT='The main event table'
    ENGINE=InnoDB
    AUTO_INCREMENT=1
    DEFAULT CHARSET=utf8
    COLLATE='utf8_general_ci'
EOD;
        try {
            $this->app['db']->query($SQL);
            $this->app['monolog']->addInfo("Created table 'event_event'", array(__METHOD__, __LINE__));
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
            $this->app['monolog']->addInfo("Drop table 'event_event'", array(__METHOD__, __LINE__));
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Get a default (empty) record for a event
     *
     * @return array
     */
    public function getDefaultRecord()
    {
        return array(
            'event_id' => -1,
            'group_id' => -1,
            'event_type' => 'EVENT',
            'event_organizer' => -1,
            'event_location' => -1,
            'event_date_from' => '0000-00-00 00:00:00',
            'event_date_to' => '0000-00-00 00:00:00',
            'event_publish_from' => '0000-00-00 00:00:00',
            'event_publish_to' => '0000-00-00 00:00:00',
            'event_url' => '',
            'event_costs' => 0,
            'event_participants_max' => -1,
            'event_deadline' => '0000-00-00 00:00:00',
            'event_status' => 'ACTIVE',
            'event_timestamp' => '0000-00-00 00:00:00'
        );
    }

    /**
     * Return the column names of the event table
     *
     * @throws \Exception
     * @return multitype:unknown
     */
    public function getColumns()
    {
        try {
            $result = $this->app['db']->fetchAll("SHOW COLUMNS FROM `".self::$table_name."`");
            $columns = array();
            foreach ($result as $column) {
                $columns[] = $column['Field'];
            }
            return $columns;
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Count the records in the table
     *
     * @param array $status flags, i.e. array('ACTIVE','LOCKED')
     * @throws \Exception
     * @return integer number of records
     */
    public function count($status=null)
    {
        try {
            $SQL = "SELECT COUNT(*) FROM `".self::$table_name."`";
            if (is_array($status) && !empty($status)) {
                $SQL .= " WHERE ";
                $use_status = false;
                if (is_array($status) && !empty($status)) {
                    $use_status = true;
                    $SQL .= '(';
                    $start = true;
                    foreach ($status as $stat) {
                        if (!$start) {
                            $SQL .= " OR ";
                        }
                        else {
                            $start = false;
                        }
                        $SQL .= "`event_status`='$stat'";
                    }
                    $SQL .= ')';
                }
            }
            return $this->app['db']->fetchColumn($SQL);
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Select a list from the event table in paging view
     *
     * @param integer $limit_from start selection at position
     * @param integer $rows_per_page select max. rows per page
     * @param array $select_status tags, i.e. array('ACTIVE','LOCKED')
     * @param array $order_by fields to order by
     * @param string $order_direction 'ASC' (default) or 'DESC'
     * @throws \Exception
     * @return array selected records
     */
    public function selectList($limit_from, $rows_per_page, $select_status=null, $order_by=null, $order_direction='ASC', $columns)
    {
        try {
            $event = self::$table_name;
            $desc = FRAMEWORK_TABLE_PREFIX.'event_description';
            $SQL = "SELECT * FROM `$event`, `$desc` WHERE $event.event_id=$desc.event_id";
            if (is_array($select_status) && !empty($select_status)) {
                $SQL .= " AND ";
                $use_status = false;
                if (is_array($select_status) && !empty($select_status)) {
                    $use_status = true;
                    $SQL .= '(';
                    $start = true;
                    foreach ($select_status as $stat) {
                        if (!$start) {
                            $SQL .= " OR ";
                        }
                        else {
                            $start = false;
                        }
                        $SQL .= "`event_status`='$stat'";
                    }
                    $SQL .= ')';
                }
            }
            if (is_array($order_by) && !empty($order_by)) {
                $SQL .= " ORDER BY ";
                $start = true;
                foreach ($order_by as $by) {
                    if (!$start) {
                        $SQL .= ", ";
                    }
                    else {
                        $start = false;
                    }
                    if ($by == 'event_id') {
                        $by = "$event.event_id";
                    }
                    $SQL .= "$by";
                }
                $SQL .= " $order_direction";
            }
            $SQL .= " LIMIT $limit_from, $rows_per_page";
            $result = $this->app['db']->fetchAll($SQL);
            $events = array();
            $participants_array = array('event_participants_confirmed', 'event_participants_pending', 'event_participants_canceled');
            foreach ($result as $evt) {
                $event = array();
                foreach ($columns as $column) {
                    if (in_array($column, $participants_array)) {
                        switch ($column) {
                            case 'event_participants_confirmed':
                                $event[$column] = $this->Subscription->countParticipants($evt['event_id']);
                                break;
                            case 'event_participants_pending':
                                $evt[$column] = $this->Subscription->countParticipants($evt['event_id'], 'PENDING');
                                break;
                            case 'event_participants_canceled':
                                $evt[$column] = $this->Subscription->countParticipants($evt['event_id'], 'CANCELED');
                                break;
                        }
                    }
                    else {
                        $event[$column] = $evt[$column];
                    }
                }
                $events[] = $event;
            }
            return $events;
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }


    /**
     * Select a event record by the given event_id
     * Return FALSE if the record does not exists
     *
     * @param integer $event_id
     * @throws \Exception
     * @return multitype:array|boolean
     */
    public function __select($event_id)
    {
        try {
            $SQL = "SELECT * FROM `".self::$table_name."` WHERE `event_id`='$event_id'";
            $result = $this->app['db']->fetchAssoc($SQL);
            if (is_array($result) && isset($result['event_id'])) {
                $event = array();
                foreach ($result as $key => $value) {
                    $event[$key] = is_string($value) ? $this->app['utils']->unsanitizeText($value) : $value;
                }
                return $event;
            }
            else {
                return false;
            }
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Get the rating data for the given event ID
     *
     * @param integer $event_id
     * @return multitype:boolean Ambigous <number, unknown> unknown
     */
    protected function getRatingData($event_id)
    {
        if (null === ($identifier = $this->RatingIdentifier->selectByTypeID('EVENT', $event_id))) {
            // create new record
            $data = array(
                'identifier_type_name' => 'EVENT',
                'identifier_type_id' => $event_id,
                'identifier_mode' => 'IP'
            );
            $identifier_id = -1;
            $this->RatingIdentifier->insert($data, $identifier_id);
            $identifier = $this->RatingIdentifier->select($identifier_id);
        }

        $average = $this->RatingData->getAverage($identifier['identifier_id']);

        $is_disabled = false;

        $checksum = md5($_SERVER['REMOTE_ADDR']);
        if (false !== ($check = $this->RatingData->selectByChecksum($identifier['identifier_id'], $checksum))) {
            $Carbon = new Carbon($check[0]['rating_confirmation']);
            if ($Carbon->diffInHours() <= 24) {
                // this IP has rated within the last 24 hours, so we lock it.
                $is_disabled = true;
            }
        }

        return array(
            'identifier_id' => $identifier['identifier_id'],
            'is_disabled' => $is_disabled,
            'average' => isset($average['average']) ? $average['average'] : 0,
            'count' => isset($average['count']) ? $average['count'] : 0
        );
    }

    /**
     * Select the complete Event record for the given $event_id
     *
     * @param integer $event_id
     * @param bool $rating if true return also the rating information, otherwise null
     * @throws \Exception
     * @return array on success, boolean false if $event_id does not exists
     */
    public function selectEvent($event_id, $rating=true)
    {
        try {
            $event = self::$table_name;
            $desc = FRAMEWORK_TABLE_PREFIX.'event_description';
            $SQL = "SELECT * FROM `$event`, `$desc` WHERE $event.event_id=$desc.event_id AND $event.event_id='$event_id'";
            $result = $this->app['db']->fetchAssoc($SQL);
            if (is_array($result) && isset($result['event_id'])) {
                $event = array();
                foreach ($result as $key => $value) {
                    $event[$key] = is_string($value) ? $this->app['utils']->unsanitizeText($value) : $value;
                }
                // check for extra fields
                $event['extra_fields'] = $this->Extra->selectByEventID($event_id);
                // check for contacts
                $event['contact']['organizer'] = array();
                if ($event['event_organizer'] > 0) {
                    if (false === $event['contact']['organizer'] = $this->Contact->selectContact($event['event_organizer'])) {
                        // contact does not exists!
                        $event['contact']['organizer'] = array();
                        $this->app['monolog']->addInfo(str_replace('The record with the ID %id% does not exists!', '%id%', $event['event_organizer']));
                    }
                }
                $event['contact']['location'] = array();
                if ($event['event_location'] > 0) {
                    if (false === $event['contact']['location'] = $this->Contact->selectContact($event['event_location'])) {
                        // contact does not exists!
                        $event['contact']['location'] = array();
                        $this->app['monolog']->addInfo(str_replace('The record with the ID %id% does not exists!', '%id%', $event['event_location']));
                    }
                }
                // check the subscriptions
                $event['participants'] = array(
                    'confirmed' => $this->Subscription->countParticipants($event_id),
                    'pending' => $this->Subscription->countParticipants($event_id, 'PENDING'),
                    'canceled' => $this->Subscription->countParticipants($event_id, 'CANCELED')
                );

                $event['rating'] = ($rating) ? $this->getRatingData($event_id) : null;

                // return complete event record
                return $event;
            }
            else {
                return false;
            }
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Insert ONLY a new event record
     *
     * @param array $data
     * @param reference integer $event_id
     * @throws \Exception
     */
    public function __insert($data, &$event_id=null)
    {
        try {
            $insert = array();
            foreach ($data as $key => $value) {
                if (($key == 'event_id') || ($key == 'event_timestamp')) continue;
                $insert[$this->app['db']->quoteIdentifier($key)] = is_string($value) ? $this->app['utils']->sanitizeText($value) : $value;
            }
            $this->app['db']->insert(self::$table_name, $insert);
            $event_id = $this->app['db']->lastInsertId();
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Insert a complete Event record with all given  $data
     * @param array $data
     * @param integer reference $event_id the new ID
     * @throws \Exception
     */
    public function insertEvent($data, &$event_id=null, $skip_ical_qrcode=false)
    {
        try {
            $insert_event = array();
            $insert_description = array();
            $keys_event = array_keys($this->getDefaultRecord());
            $keys_description = array_keys($this->Description->getDefaultRecord());

            foreach ($data as $key => $value) {
                if (($key == 'event_id') || ($key == 'event_timestamp') || ($key == 'description_timestamp')) continue;
                if (in_array($key, $keys_event)) {
                    $insert_event[$key] = is_string($value) ? $this->app['utils']->sanitizeText($value) : $value;
                }
                elseif (in_array($key, $keys_description)) {
                    $insert_description[$key] = $value; //is_string($value) ? $this->app['utils']->unsanitizeText($value) : $value;
                }
            }
            // insert event record
            $this->app['db']->insert(self::$table_name, $insert_event);

            $event_id = $this->app['db']->lastInsertId();


            if ($event_id > 0) {
                // check the description fields
                $insert_description['event_id'] = $event_id;
                if (!isset($insert_description['description_title']))
                    $insert_description['description_title'] = '';
                if (!isset($insert_description['description_short']))
                    $insert_description['description_short'] = '';
                if (!isset($insert_description['description_long']))
                    $insert_description['description_long'] = '';
                // insert a description record
                $this->Description->insert($insert_description);
            }
            // insert additional fields
            // select all type IDs for this event group
            $extra_fields = $this->ExtraGroup->selectTypeIDByGroupID($data['group_id']);
            // loop through the extra fields
            foreach ($extra_fields as $extra_field_id) {
                // get the extra type information
                $extra_type = $this->ExtraType->select($extra_field_id);
                // create empty extra record for this event ID
                $data = array(
                    'extra_type_id' => $extra_field_id,
                    'extra_type_name' => $extra_type['extra_type_name'],
                    'group_id' => $data['group_id'],
                    'event_id' => $event_id,
                    'extra_type_type' => $extra_type['extra_type_type'],
                    'extra_text' => '',
                    'extra_html' => '',
                    'extra_varchar' => '',
                    'extra_int' => '0',
                    'extra_float' => '0',
                    'extra_date' => '0000-00-00',
                    'extra_datetime' => '0000-00-00 00:00:00',
                    'extra_time' => '00:00:00'
                );
                $this->Extra->insert($data);
            }

            if (!$skip_ical_qrcode) {
                // create iCal file
                $this->iCal->CreateICalFile($this, $event_id);
                // create QRCode
                $this->QRCode->create($event_id);
            }

        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Select all available Event Records
     *
     * @param string $status can be 'DELETED', 'ACTIVE' or 'LOCKED'
     * @param string $status_operator can be '!=' or '='
     * @throws \Exception
     * @return array Event records
     */
    public function selectAll($status='DELETED', $status_operator='!=')
    {
        try {
            $event = self::$table_name;
            $desc = FRAMEWORK_TABLE_PREFIX.'event_description';
            $SQL = "SELECT * FROM `$event`, `$desc` WHERE $event.event_id=$desc.event_id AND `event_status`{$status_operator}'{$status}' ORDER BY `event_date_from` DESC";
            $results = $this->app['db']->fetchAll($SQL);
            $groups = array();
            if (is_array($results)) {
                foreach ($results as $result) {
                    $record = array();
                    foreach ($result as $key => $value) {
                        $record[$key] = is_string($value) ? $this->app['utils']->unsanitizeText($value) : $value;
                    }
                    $groups[] = $record;
                }
            }
            return $groups;
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    public function selectAllIDs($start_id=0, $status='DELETED', $status_operator='!=')
    {
        try {
            $SQL = "SELECT `event_id` FROM `".self::$table_name."` WHERE `event_id` >= '$start_id' AND `event_status`{$status_operator}'{$status}' ORDER BY `event_id` ASC";
            return $this->app['db']->fetchAll($SQL);
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Update a complete Event record with all $data for the given $event_id
     *
     * @param array $data
     * @param integer $event_id
     * @throws \Exception
     */
    public function updateEvent($data, $event_id)
    {
        try {
            $update_event = array();
            $update_description = array();
            $keys_event = array_keys($this->getDefaultRecord());
            $keys_description = array_keys($this->Description->getDefaultRecord());
            foreach ($data as $key => $value) {
                if (($key == 'event_id') || ($key == 'event_timestamp') || ($key == 'description_timestamp')) continue;
                if (in_array($key, $keys_event)) {
                    $update_event[$this->app['db']->quoteIdentifier($key)] = is_string($value) ? $this->app['utils']->sanitizeText($value) : $value;
                }
                elseif (in_array($key, $keys_description)) {
                    $update_description[$this->app['db']->quoteIdentifier($key)] = is_string($value) ? $this->app['utils']->sanitizeText($value) : $value;
                }
            }
            if (!empty($update_event)) {
                // update event
                $this->app['db']->update(self::$table_name, $update_event, array('event_id' => $event_id));
            }
            if (!empty($update_description)) {
                // update description
                $this->app['db']->update(FRAMEWORK_TABLE_PREFIX.'event_description', $update_description, array('event_id' => $event_id));
            }
            // update extra fields
            $this->Extra->updateByEventID($data, $event_id);

            // create/update iCal file
            $this->iCal->CreateICalFile($this, $event_id);

            // create/update QR-Code file
            $this->QRCode->create($event_id);

        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Remove incomplete and invalid events physically
     *
     * @throws \Exception
     */
    public function cleanupEvents()
    {
        try {
            // delete all events with invalid start and end date
            $this->app['db']->delete(self::$table_name, array(
                'event_date_from' => '0000-00-00 00:00:00', 'event_date_to' => '0000-00-00 00:00:00'));
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

}
