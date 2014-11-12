<?php

/**
 * Event
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de/Event
 * @copyright 2014 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\Event\Data\Event;

use Silex\Application;
use Carbon\Carbon;

class RecurringEvent
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
        self::$table_name = FRAMEWORK_TABLE_PREFIX.'event_recurring_event';
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
        `recurring_id` INT(11) NOT NULL AUTO_INCREMENT,
        `parent_event_id` INT(11) NOT NULL DEFAULT -1,
        `recurring_type` ENUM('NONE','DAY','WEEK','MONTH','YEAR') NOT NULL DEFAULT 'NONE',
        `day_type` ENUM('DAILY','WORKDAYS') NOT NULL DEFAULT 'DAILY',
        `day_sequence` INT(11) NOT NULL DEFAULT 1,
        `week_sequence` INT(11) NOT NULL DEFAULT 1,
        `week_day` ENUM('MONDAY','TUESDAY','WEDNESDAY','THURSDAY','FRIDAY','SATURDAY','SUNDAY') NOT NULL DEFAULT 'MONDAY',
        `month_type` ENUM('SEQUENCE','PATTERN') NOT NULL DEFAULT 'SEQUENCE',
        `month_sequence_day` INT(11) NOT NULL DEFAULT 1,
        `month_sequence_month` INT(11) NOT NULL DEFAULT 1,
        `month_pattern_type` ENUM('FIRST','SECOND','THIRD','FOURTH','LAST','FIRST_THIRD','SECOND_FOURTH','SECOND_LAST') NOT NULL DEFAULT 'FIRST',
        `month_pattern_day` ENUM('MONDAY','TUESDAY','WEDNESDAY','THURSDAY','FRIDAY','SATURDAY','SUNDAY') NOT NULL DEFAULT 'MONDAY',
        `month_pattern_sequence` INT(11) NOT NULL DEFAULT 1,
        `year_repeat` INT(11) NOT NULL DEFAULT 1,
        `year_type` ENUM('SEQUENCE','PATTERN') NOT NULL DEFAULT 'SEQUENCE',
        `year_sequence_day` INT(11) NOT NULL DEFAULT 1,
        `year_sequence_month` ENUM('JANUARY','FEBRUARY','MARCH','APRIL','MAY','JUNE','JULY','AUGUST','SEPTEMBER','OCTOBER','NOVEMBER','DECEMBER') NOT NULL DEFAULT 'JANUARY',
        `year_pattern_type` ENUM('FIRST','SECOND','THIRD','FOURTH','LAST') NOT NULL DEFAULT 'FIRST',
        `year_pattern_day` ENUM('MONDAY','TUESDAY','WEDNESDAY','THURSDAY','FRIDAY','SATURDAY','SUNDAY') NOT NULL DEFAULT 'MONDAY',
        `year_pattern_month` ENUM('JANUARY','FEBRUARY','MARCH','APRIL','MAY','JUNE','JULY','AUGUST','SEPTEMBER','OCTOBER','NOVEMBER','DECEMBER') NOT NULL DEFAULT 'JANUARY',
        `recurring_date_end` DATE NOT NULL DEFAULT '0000-00-00',
        `exclude_dates` TEXT NOT NULL,
        `recurring_timestamp` TIMESTAMP,
        PRIMARY KEY (`recurring_id`),
        INDEX (`parent_event_id`)
        )
    COMMENT='Control table for recurring events'
    ENGINE=InnoDB
    AUTO_INCREMENT=1
    DEFAULT CHARSET=utf8
    COLLATE='utf8_general_ci'
EOD;
        try {
            $this->app['db']->query($SQL);
            $this->app['monolog']->addInfo("Created table 'event_recurring_event'", array(__METHOD__, __LINE__));
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
        $this->app['db.utils']->dropTable(self::$table_name);
    }

    /**
     * Select a recurring event by the given event ID
     *
     * @param integer $event_id
     * @throws \Exception
     * @return Ambigous <boolean, array> FALSE if the record does not exists
     */
    public function selectByEventID($event_id)
    {
        try {
            $SQL = "SELECT * FROM `".self::$table_name."` WHERE `parent_event_id`=$event_id";
            $result = $this->app['db']->fetchAssoc($SQL);
            return (isset($result['parent_event_id']) && ($result['parent_event_id'] == $event_id)) ? $result : false;
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Select a recurring event by the given recurring ID
     *
     * @param integer $recurring_id
     * @throws \Exception
     * @return Ambigous <boolean, array> FALSE if the record does not exists
     */
    public function select($recurring_id)
    {
        try {
            $SQL = "SELECT * FROM `".self::$table_name."` WHERE `recurring_id`=$recurring_id";
            $result = $this->app['db']->fetchAssoc($SQL);
            return (isset($result['recurring_id']) && ($result['recurring_id'] == $recurring_id)) ? $result : false;
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }
    /**
     * Prepare the data record before inserting or updating
     *
     * @param array $data
     * @throws \Exception
     * @return array
     */
    protected function prepareData($data)
    {
        $recurring = array();
        foreach ($data as $key => $value) {
            switch ($key) {
                case 'parent_event_id':
                    $recurring[$key] = $value;
                    break;
                case 'recurring_type':
                    if (is_null($value)) {
                        throw new \Exception("The field `$key` can not be NULL!");
                    }
                    $recurring[$key] = $value;
                    break;
                case 'exclude_dates':
                    $recurring[$key] = !is_null($value) ? $value : '';
                    break;
                case 'day_sequence':
                case 'day_type':
                case 'week_sequence':
                case 'week_day':
                case 'month_type':
                case 'month_sequence_day':
                case 'month_sequence_month':
                case 'month_pattern_type':
                case 'month_pattern_day':
                case 'month_pattern_sequence':
                case 'year_repeat':
                case 'year_type':
                case 'year_sequence_day':
                case 'year_sequence_month':
                case 'year_pattern_type':
                case 'year_pattern_day':
                case 'year_pattern_month':
                case 'recurring_date_end':
                    if (!is_null($value)) {
                        $recurring[$key] = $value;
                    }
                    break;
            }
        }
        if (!isset($recurring['exclude_dates'])) {
            $recurring['exclude_dates'] = '';
        }
        return $recurring;
    }

    /**
     * Insert a new recurring event data record
     *
     * @param array $recurring
     * @param integer reference $recurring_id
     * @throws \Exception
     * @return integer inserted recurring ID
     */
    public function insert($data, &$recurring_id=null)
    {
        try {
            if (!isset($data['parent_event_id'])) {
                throw new \Exception('Missing the field `parent_event_id`!');
            }
            if (false !== ($check = $this->selectByEventID($data['parent_event_id']))) {
                throw new \Exception('There exists already a recurring event for the `parent_event_id` '.
                    $data['parent_event_id'].'!');
            }

            $this->app['db']->insert(self::$table_name, $this->prepareData($data));
            $recurring_id = $this->app['db']->lastInsertId();
            return $recurring_id;
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Update an existing recurring event with the given recurring ID
     *
     * @param integer $recurring_id
     * @param array $data
     * @throws \Exception
     */
    public function update($recurring_id, $data)
    {
        try {
            $this->app['db']->update(self::$table_name, $this->prepareData($data), array('recurring_id' => $recurring_id));
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }



    /**
     * Get a readable sentence to describe the recurring event
     *
     * @param integer $recurring_id
     * @throws \Exception
     * @return Ambigous <boolean, string> FALSE if the record does not exists
     */
    public function getReadableCurringEvent($recurring_id)
    {
        try {
            $SQL = "SELECT * FROM `".self::$table_name."` WHERE `recurring_id`='$recurring_id'";
            $result = $this->app['db']->fetchAssoc($SQL);

            if (!isset($result['recurring_id'])) {
                // this is not an recurring event
                return false;
            }

            $exclude_dates = '.';
            if (!empty($result['exclude_dates'])) {
                if (strpos($result['exclude_dates'], ',')) {
                    $items = explode(',', $result['exclude_dates']);
                    $dates = array();
                    foreach ($items as $item) {
                        $dt = Carbon::createFromFormat('Y-m-d', $item);
                        $dates[] = $dt->format($this->app['translator']->trans('DATE_FORMAT'));
                    }
                }
                else {
                    $dt = Carbon::createFromFormat('Y-m-d', $result['exclude_dates']);
                    $dates = array($dt->format($this->app['translator']->trans('DATE_FORMAT')));
                }
                $exclude_dates = $this->app['translator']->trans('but not at %dates%.',
                    array('%dates%' => implode(', ', $dates)));
            }

            switch ($result['recurring_type']) {
                case 'DAY':
                    if ($result['day_type'] == 'WORKDAYS') {
                        return $this->app['translator']->trans('The event [%event_id%] will be repeated at each workday%exclude%',
                            array('%event_id%' => $result['parent_event_id'], '%exclude%' => $exclude_dates));
                    }
                    else {
                        return $this->app['translator']->trans('The event [%event_id%] will be repeated each %day_sequence% day(s)%exclude%',
                            array('%event_id%' => $result['parent_event_id'], '%day_sequence%' => $result['day_sequence'],
                            '%exclude%' => $exclude_dates));
                    }
                case 'WEEK':
                    $week_day = $this->app['utils']->humanize($result['week_day']);
                    return $this->app['translator']->trans('The event [%event_id%] will be repeated each %week_sequence% week(s) at %week_day%%exclude%',
                        array('%event_id%' => $result['parent_event_id'], '%week_sequence%' => $result['week_sequence'],
                            '%week_day%' => $this->app['translator']->trans($week_day),
                                '%exclude%' => $exclude_dates));
                case 'MONTH':
                    if ($result['month_type'] == 'SEQUENCE') {
                        return $this->app['translator']->trans('The event [%event_id%] will be repeated at the %month_day%. day of each %month_sequence%. month%exclude%',
                            array('%event_id%' => $result['parent_event_id'], '%month_day%' => $result['month_sequence_day'],
                                '%month_sequence%' => $result['month_sequence_month'], '%exclude%' => $exclude_dates));
                    }
                    else {
                        // month type = PATTERN
                        $month_pattern_day = $this->app['utils']->humanize($result['month_pattern_day']);
                        return $this->app['translator']->trans('The event [%event_id%] will be repeated at %pattern_type% %pattern_day% of each %pattern_sequence%. month%exclude%',
                            array('%event_id%' => $result['parent_event_id'],
                                '%pattern_type%' => $this->app['translator']->trans(strtolower($result['month_pattern_type'])),
                                '%pattern_day%' => $this->app['translator']->trans($month_pattern_day),
                                '%pattern_sequence%' => $result['month_pattern_sequence'],
                                '%exclude%' => $exclude_dates
                            ));
                    }
                case 'YEAR':
                    if ($result['year_type'] == 'SEQUENCE') {
                        $year_sequence_month = $this->app['utils']->humanize($result['year_sequence_month']);
                        return $this->app['translator']->trans('The event [%event_id%] will be repeated each %year_repeat%. year at %month_day%. %month_name%%exclude%',
                            array('%event_id%' => $result['parent_event_id'], '%year_repeat%' => $result['year_repeat'],
                                '%month_day%' => $result['year_sequence_day'],
                                '%month_name%' => $this->app['translator']->trans($year_sequence_month),
                                '%exclude%' => $exclude_dates));
                    }
                    else {
                        // year type == PATTERN
                        $year_pattern_type = $this->app['utils']->humanize($result['year_pattern_type']);
                        $year_pattern_day = $this->app['utils']->humanize($result['year_pattern_day']);
                        $year_pattern_month = $this->app['utils']->humanize($result['year_pattern_month']);
                        return $this->app['translator']->trans('The event [%event_id%] will be repeated each %year_repeat%. year at %pattern_type% %pattern_day% of %pattern_month%%exclude%',
                            array('%event_id%' => $result['parent_event_id'],
                                '%year_repeat%' => $result['year_repeat'],
                                '%pattern_type%' => $this->app['translator']->trans($year_pattern_type),
                                '%pattern_day%' => $this->app['translator']->trans($year_pattern_day),
                                '%pattern_month%' => $this->app['translator']->trans($year_pattern_month),
                                '%exclude%' => $exclude_dates
                            ));
                    }
                default:
                    return $this->app['translator']->trans("Don't know how to handle the recurring type %type%.",
                        array('%type%' => $result['recurring_type']));
            }
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Delete the given recurring ID
     *
     * @param integer $recurring_id
     * @throws \Exception
     */
    public function delete($recurring_id)
    {
        try {
            $this->app['db']->delete(self::$table_name, array('recurring_id' => $recurring_id));
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Check if the new data are different from the saved data
     *
     * @param integer $recurring_id
     * @param array $new_data
     * @throws \Exception
     * @return boolean
     */
    public function hasChanged($recurring_id, $new_data)
    {
        try {
            if (false === ($data = $this->select($recurring_id))) {
                return false;
            }

            foreach ($data as $key => $value) {
                if (($key == 'recurring_timestamp')) {
                    continue;
                }
                if (isset($new_data[$key]) && ($new_data[$key] != $data[$key])) {
                    return true;
                }
            }
            return false;
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Return the day of week integer aquivalent
     *
     * @param string $day
     * @throws \Exception
     * @return number
     */
    public function getDayOfWeekInteger($day)
    {
        switch (strtoupper($day)) {
            case 'MONDAY': return 1;
            case 'TUESDAY': return 2;
            case 'WEDNESDAY': return 3;
            case 'THURSDAY': return 4;
            case 'FRIDAY': return 5;
            case 'SATURDAY': return 6;
            case 'SUNDAY': return 0;
            default:
                throw new \Exception('Invalid string value for the day of week: '.$day);
        }
    }

    /**
     * Return the string aquivalent of the DayOfWeek integer
     *
     * @param integer $day
     * @return string
     */
    public function getDayOfWeekString($day)
    {
        switch ($day) {
            case 0: return 'SUNDAY';
            case 1: return 'MONDAY';
            case 2: return 'TUESDAY';
            case 3: return 'WEDNESDAY';
            case 4: return 'THURSDAY';
            case 5: return 'FRIDAY';
            case 6: return 'SATURDAY';
            default:
                throw new \Exception('Invalid integer value for the day of week: '.$day);
        }
    }

}
