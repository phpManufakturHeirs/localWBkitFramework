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
use phpManufaktur\Event\Data\Event\Event;
use Carbon\Carbon;
use phpManufaktur\CommandCollection\Data\Comments\Comments;


class EventFilter
{
    protected $app = null;
    protected $Event = null;
    protected $Comments = null;

    /**
     * Constructor for the Event filter
     *
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
        $this->Event = new Event($app);
        $this->Comments = new Comments($app);
    }


    /**
     * The filter function for events
     *
     * @param array $filter
     * @param array reference $messages
     * @param string $SQL
     * @param boolean $comments_info
     * @param string $comments_type
     * @throws \Exception
     * @return Ambigous <boolean, array>
     * @link https://gist.github.com/hertsch/6289857#param_filter
     */
    public function filter($filter=array(), &$messages=array(), &$SQL='', $comments_info=false, $comments_type='EVENT')
    {
        try {
            // SQL body
            $SQL = "SELECT `event_id` FROM `".FRAMEWORK_TABLE_PREFIX."event_event`, `".
                FRAMEWORK_TABLE_PREFIX."contact_overview`, `".FRAMEWORK_TABLE_PREFIX."event_group` ".
                "WHERE `event_location`=`contact_id` AND `".FRAMEWORK_TABLE_PREFIX."event_event`.`group_id`=".
                "`".FRAMEWORK_TABLE_PREFIX."event_group`.`group_id` ";

            if (empty($filter)) {
                // no filter defined - select all active events one week back and four weeks ahead

                // set actual date
                $dt = Carbon::now();
                $today = $dt->toDateTimeString();
                // go a week back
                $dt->subWeek();
                $start_date = $dt->toDateTimeString();
                // add 5 weeks (4 weeks from today)
                $dt->addWeeks(5);
                $end_date = $dt->toDateTimeString();

                $SQL .= "AND `event_publish_from` <= '$today' AND `event_publish_to` >= '$today' ";
                $SQL .= "AND (`event_date_from` >= '$start_date' OR `event_date_to` >= '$start_date') ";
                $SQL .= "AND `event_date_from` <= '$end_date' ";
            }

            $strict_mode = (isset($filter['mode']) && (strtolower($filter['mode']) == 'strict')) ? true : false;
            $ignore_publish = (isset($filter['publish']) && (strtolower($filter['publish']) == 'ignore')) ? true : false;

            if (isset($filter['actual'])) {
                $skip = false;

                if (!strpos($filter['actual'], ',')) {
                    if ((strtolower($filter['actual']) == 'current') ||
                        (is_numeric($filter['actual']) && (intval($filter['actual']) == 0))) {
                        // use the current day and add 14 days ahead
                        $dt = Carbon::now();
                        $dt->startOfDay();
                        $start_date = $dt->toDateTimeString();
                        $dt->addDays(14);
                        $dt->endOfDay();
                        $end_date = $dt->toDateTimeString();
                    }
                    elseif (is_numeric($filter['actual'])) {
                        $dt = Carbon::now();
                        $dt->startOfDay();
                        $dt->addDays(intval($filter['actual']));
                        $start_date = $dt->toDateTimeString();
                        $dt->addDays(14);
                        $dt->endOfDay();
                        $end_date = $dt->toDateTimeString();
                    }
                    else {
                        // filter is not valid
                        $skip = true;
                        $messages[] = $this->app['translator']->trans("The filter 'actual' must be numeric or contain the keyword 'current' as first parameter.");
                    }
                }
                else {
                    list($start, $end) = explode(',', $filter['actual']);
                    $start = strtolower(trim($start));
                    $end = trim($end);
                    if (($start == 'current') || (is_numeric($start) && (intval($start) == 0))) {
                        $dt = Carbon::now();
                        $dt->startOfDay();
                        $start_date = $dt->toDateTimeString();
                    }
                    elseif (is_numeric($start)) {
                        $dt = Carbon::now();
                        $dt->startOfDay();
                        $dt->addDays(intval($start));
                        $start_date = $dt->toDateTimeString();
                    }
                    else {
                        // filter is not valid
                        $skip = true;
                        $messages[] = $this->app['translator']->trans("The filter 'actual' must be numeric or contain the keyword 'current' as first parameter.");
                    }
                    if (!$skip && is_numeric($end)) {
                        if (intval($end) < 1) {
                            // filter is not valid
                            $skip = true;
                            $messages[] = $this->app['translator']->trans("The second parameter for the filter 'actual' must be positive integer value.");
                        }
                        else {
                            $dt = Carbon::now();
                            $dt->addDays(intval($end));
                            $dt->endOfDay();
                            $end_date = $dt->toDateTimeString();
                        }
                    }
                    elseif (strtolower($end) == 'year') {
                        $dt = Carbon::now();
                        $dt->month(12);
                        $dt->day(31);
                        $dt->endOfDay();
                        $end_date = $dt->toDateTimeString();
                    }
                    elseif (strtolower($end) == 'month') {
                        $dt = Carbon::now();
                        $dt->endOfMonth();
                        $dt->endOfDay();
                        $end_date = $dt->toDateTimeString();
                    }
                    elseif (strtolower($end) == 'week') {
                        $dt = Carbon::now();
                        $dt->endOfWeek();
                        $dt->endOfDay();
                        $end_date = $dt->toDateTimeString();
                    }
                    elseif (!is_numeric($end)) {
                        // filter is not valid
                        $skip = true;
                        $messages[] = $this->app['translator']->trans("The second parameter for the filter 'actual' must be a positive integer value.");
                    }
                }

                if (!$skip) {
                    if ($strict_mode) {
                        $SQL .= "AND `event_date_from` BETWEEN '$start_date' AND '$end_date' ";
                    }
                    else {
                        $SQL .= "AND (('$start_date' BETWEEN `event_date_from` AND `event_date_to`) OR ";
                        $SQL .= "(`event_date_from` BETWEEN '$start_date' AND '$end_date') OR ";
                        $SQL .= "(`event_date_to` BETWEEN '$start_date' AND '$end_date')) ";
                    }
                    if (!$ignore_publish) {
                        $SQL .= "AND ('$start_date' BETWEEN `event_publish_from` AND `event_publish_to`) ";
                    }
                    // unset the date filters
                    unset($filter['month']);
                    unset($filter['year']);
                    unset($filter['day']);
                }
            }

            if (isset($filter['day'])) {
                // filter for the DAY
                $skip = false;
                if (strtolower($filter['day']) == 'current') {
                    // set actual date
                    $dt = Carbon::now();
                    $dt->startOfDay();
                    $start_date = $dt->toDateTimeString();
                    $dt->endOfDay();
                    $end_date = $dt->toDateTimeString();
                }
                elseif (is_numeric($filter['day'])) {
                    // filter for the given DAY
                    $dt = Carbon::now();
                    $dt->startOfDay();
                    $day = intval($filter['day']);
                    $month = (isset($filter['month']) && is_numeric($filter['month'])) ? intval($filter['month']) : $dt->month;
                    $year = (isset($filter['year']) && is_numeric($filter['year'])) ? intval($filter['year']) : $dt->year;
                    $dt->setDate($year, $month, $day);
                    $start_date = $dt->toDateTimeString();
                    $dt->endOfDay();
                    $end_date = $dt->toDateTimeString();
                }
                else {
                    // filter is not valid
                    $skip = true;
                    $messages[] = $this->app['translator']->trans('The filter for <em>day</em> must be numeric or contain the keyword <em>current</em>');
                }

                if (!$skip) {
                    if ($strict_mode) {
                        $SQL .= "AND `event_date_from` BETWEEN '$start_date' AND '$end_date' ";
                    }
                    else {
                        $SQL .= "AND (('$start_date' BETWEEN `event_date_from` AND `event_date_to`) OR ";
                        $SQL .= "(`event_date_from` BETWEEN '$start_date' AND '$end_date') OR ";
                        $SQL .= "(`event_date_to` BETWEEN '$start_date' AND '$end_date')) ";
                    }
                    if (!$ignore_publish) {
                        $SQL .= "AND ('$start_date' BETWEEN `event_publish_from` AND `event_publish_to`) ";
                    }
                    // unset the date filters
                    unset($filter['month']);
                    unset($filter['year']);
                    unset($filter['day']);
                }
            }

            if (isset($filter['month'])) {
                // filter for the month
                $skip = false;

                if (strtolower($filter['month']) == 'current') {
                    // set the actual date
                    $dt = Carbon::now();
                    $dt->startOfDay();
                    $year = (isset($filter['year']) && is_numeric($filter['year'])) ? intval($filter['year']) : $dt->year;
                    if ($year < 100) {
                        $year += 2000;
                    }
                    $dt->year($year);
                    // first day of the month
                    $dt->startOfMonth();
                    $start_date = $dt->toDateTimeString();
                    $dt->endOfMonth();
                    $end_date = $dt->toDateTimeString();
                }
                elseif (is_numeric($filter['month'])) {
                    $dt = Carbon::now();
                    $dt->startOfDay();
                    $dt->month(intval($filter['month']));
                    $year = (isset($filter['year']) && is_numeric($filter['year'])) ? intval($filter['year']) : $dt->year;
                    if ($year < 100) {
                        $year += 2000;
                    }
                    $dt->year($year);
                    // first day of the month
                    $dt->startOfMonth();
                    $start_date = $dt->toDateTimeString();
                    $dt->endOfMonth();
                    $end_date = $dt->toDateTimeString();
                }
                else {
                    // filter is not valid
                    $skip = true;
                    $messages[] = $this->app['translator']->trans('The filter for <em>month</em> must be numeric or contain the keyword <em>current</em>');
                }

                if (!$skip) {
                    if ($strict_mode) {
                        $SQL .= "AND `event_date_from` BETWEEN '$start_date' AND '$end_date' ";
                    }
                    else {
                        $SQL .= "AND ((`event_date_from` BETWEEN '$start_date' AND '$end_date') OR ";
                        $SQL .= "(`event_date_to` BETWEEN '$start_date' AND '$end_date')) ";
                    }

                    if ($ignore_publish) {
                        $SQL .= "AND ('$start_date' BETWEEN `event_publish_from` AND `event_publish_to`) ";
                    }
                    // unset the date filters
                    unset($filter['month']);
                    unset($filter['year']);
                    unset($filter['day']);
                }
            }

            if (isset($filter['year'])) {
                // filter for the year
                $skip = false;

                if (strtolower($filter['year']) == 'current') {
                    // set the date, based from the actual day
                    $dt = Carbon::now();
                    $dt->startOfDay();
                    $dt->setDateTime($dt->year, 1, 1, 0, 0, 0);
                    $start_date = $dt->toDateTimeString();
                    $dt->setDateTime($dt->year, 12, 31, 23, 59, 59);
                    $end_date = $dt->toDateTimeString();
                }
                elseif (is_numeric($filter['year'])) {
                    $year = ($filter['year'] < 100) ? 2000 + intval($filter['year']) : intval($filter['year']);
                    $dt = Carbon::now();
                    $dt->setDateTime($year, 1, 1, 0, 0, 0);
                    $start_date = $dt->toDateTimeString();
                    $dt->setDateTime($year, 12, 31, 23, 59, 59);
                    $end_date = $dt->toDateTimeString();
                }
                else {
                    // filter is not valid
                    $skip = true;
                    $messages[] = $this->app['translator']->trans('The filter for <em>year</em> must be numeric or contain the keyword <em>current</em>');
                }

                if (!$skip) {
                    if ($strict_mode) {
                        $SQL .= "AND `event_date_from` BETWEEN '$start_date' AND '$end_date' ";
                    }
                    else {
                        $SQL .= "AND ((`event_date_from` BETWEEN '$start_date' AND '$end_date') OR ";
                        $SQL .= "(`event_date_to` BETWEEN '$start_date' AND '$end_date')) ";
                    }

                    if ($ignore_publish) {
                        $SQL .= "AND ('$start_date' BETWEEN `event_publish_from` AND `event_publish_to`) ";
                    }
                    // unset the date filters
                    unset($filter['month']);
                    unset($filter['year']);
                    unset($filter['day']);
                }
            }

            if (isset($filter['country'])) {
                // add filter for the country code
                $SQL .= "AND `address_country_code`='".strtoupper($filter['country'])."' ";
            }

            if (isset($filter['area'])) {
                // add filter for the area
                if (strpos($filter['area'], ',')) {
                    // multiple areas
                    $areas = explode(',', $filter['area']);
                    $SQL .= "AND (";
                    $start = true;
                    foreach ($areas as $area) {
                        if (trim($area) == '') continue;
                        if (!$start) {
                            $SQL .= " OR ";
                        }
                        $SQL .= "`address_area`='".$this->app['utils']->utf8_entities(trim($area))."'";
                        $start = false;
                    }
                    $SQL .= ") ";
                }
                else {
                    $SQL .= "AND `address_area`='".$this->app['utils']->utf8_entities($filter['area'])."' ";
                }
            }

            if (isset($filter['state'])) {
                // add filter for the state
                if (strpos($filter['state'], ',')) {
                    // multiple states
                    $states = explode(',', $filter['state']);
                    $SQL .= "AND (";
                    $start = true;
                    foreach ($states as $state) {
                        if (trim($state) == '') continue;
                        if (!$start) {
                            $SQL .= " OR ";
                        }
                        $SQL .= "`address_state` LIKE '".$this->app['utils']->utf8_entities(trim($state))."%'";
                        $start = false;
                    }
                    $SQL .= ") ";
                }
                else {
                    $SQL .= "AND `address_state` LIKE '".$this->app['utils']->utf8_entities($filter['state'])."%' ";
                }
            }

            if (isset($filter['zip'])) {
                // add filter for the ZIP code
                if (strpos($filter['zip'], ',')) {
                    // multiple zips
                    $zips = explode(',', $filter['zip']);
                    $SQL .= "AND (";
                    $start = true;
                    foreach ($zips as $zip) {
                        if (trim($zip) == '') continue;
                        if (strtolower($zip) == 'null') {
                            $zip = '0';
                        }
                        if (!$start) {
                            $SQL .= " OR ";
                        }
                        else {
                            $start = false;
                        }
                        $SQL .= "`address_zip` LIKE '".trim($zip)."%'";
                    }
                    $SQL .= ") ";
                }
                elseif (strtolower(trim($filter['zip'])) == 'null') {
                    $SQL .= "AND `address_zip` LIKE '0%' ";
                }
                else {
                    $SQL .= "AND `address_zip` LIKE '".$filter['zip']."%' ";
                }
            }

            if (isset($filter['city'])) {
                // add filter for the city
                if (strpos($filter['city'], ',')) {
                    // multiple cities
                    $cities = explode(',', $filter['city']);
                    $SQL .= "AND (";
                    $start = true;
                    foreach ($cities as $city) {
                        if (trim($city) == '') continue;
                        if (!$start) {
                            $SQL .= " OR ";
                        }
                        else {
                            $start = false;
                        }
                        $SQL .= "`address_city` LIKE '".$this->app['utils']->utf8_entities($city)."%'";
                    }
                    $SQL .= ") ";
                }
                else {
                    $SQL .= "AND `address_city` LIKE '".$this->app['utils']->utf8_entities($filter['city'])."%' ";
                }
            }

            if (isset($filter['group'])) {
                // add filter for the group
                if (strpos($filter['group'], ',')) {
                    // multiple groups
                    $groups = explode(',', $filter['group']);
                    $SQL .= "AND (";
                    $start = true;
                    foreach ($groups as $group) {
                        if (trim($group) == '') continue;
                        if (!$start) {
                            $SQL .= " OR ";
                        }
                        else {
                            $start = false;
                        }
                        $SQL .= "`group_name` = '".trim($group)."'";
                    }
                    $SQL .= ") ";
                }
                else {
                    $SQL .= "AND `group_name` = '".$filter['group']."' ";
                }
            }

            if (isset($filter['status'])) {
                // filter for the STATUS
                $SQL .= "AND `event_status`='".strtoupper($filter['status'])."' ";
            }
            else {
                // set STATUS to ACTIVE
                $SQL .= "AND `event_status`='ACTIVE' ";
            }

            if (isset($filter['order_by'])) {
                // filter for the ORDER BY statement
                if (strpos($filter['order_by'], ',')) {
                    // order by multiple fields (simple solution)
                    $order_bys = explode(',', $filter['order_by']);
                    $SQL .= "ORDER BY ";
                    $start = true;
                    foreach ($order_bys as $order_by) {
                        if (trim($order_by) == '') continue;
                        if (!$start) $SQL .= ", ";
                        $SQL .= "`".trim($order_by)."`";
                        $start = false;
                    }
                    $SQL .= " ";
                }
                else {
                    // simply order by one field
                    $SQL .= "ORDER BY `".strtolower($filter['order_by'])."` ";
                }
            }
            else {
                // default ORDER BY
                $SQL .= "ORDER BY `event_date_from` ";
            }
            if (isset($filter['order_direction'])) {
                if (in_array(strtoupper($filter['order_direction']), array('ASC', 'DESC'))) {
                    $SQL .= strtoupper($filter['order_direction'])." ";
                }
                else {
                    // invalid value, set default
                    $SQL .= "ASC ";
                    $messages[] = $this->app['translator']->trans("The value for 'order_direction' can be 'ASC' (ascending) or 'DESC' (descending)");
                }
            }

            if (isset($filter['limit'])) {
                if (is_numeric($filter['limit'])) {
                    $SQL .= "LIMIT ".intval($filter['limit']);
                }
                else {
                    // LIMIT must be numeric
                    $messages[] = $this->app['translator']->trans("The 'limit' filter must be a integer value.");
                }
            }

            // execute the filter and get all event IDs
            $results = $this->app['db']->fetchAll($SQL);
            $events = array();
            foreach ($results as $result) {
                // loop through the events and get all details
                $event = $this->Event->selectEvent($result['event_id']);
                if ($comments_info) {
                    // add information about comments
                    $event['comments']['count'] = $this->Comments->countComments($comments_type, $result['event_id']);
                }
                $events[] = $event;
            }
            // return the event array or false
            return (!empty($events)) ? $events : false;
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }
}
