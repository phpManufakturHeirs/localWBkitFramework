<?php

/**
 * Event
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de/Event
 * @copyright 2013 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\Event\Control\Import\kitEvent;

use Silex\Application;
use phpManufaktur\Event\Control\Import\Dialog;
use phpManufaktur\Contact\Data\Import\KeepInTouch\KeepInTouch as KeepInTouchData;
use phpManufaktur\Contact\Control\Contact;
use phpManufaktur\Event\Data\Import\kitEvent\kitEvent as kitEventData;
use phpManufaktur\Event\Data\Event\Group;
use phpManufaktur\Contact\Data\Contact\TagType;
use phpManufaktur\Event\Data\Event\LocationTag;
use phpManufaktur\Event\Data\Event\OrganizerTag;
use phpManufaktur\Event\Data\Event\ParticipantTag;
use phpManufaktur\Event\Data\Event\Event;
use phpManufaktur\Event\Data\Event\Description;

class kitEvent extends Dialog {

    protected static $kit_release = null;
    protected static $import_is_possible = false;
    protected $KeepInTouch = null;
    protected $Contact = null;
    protected static $script_start = null;
    protected static $max_execution_time = 60; // 60 seconds
    protected $kitEvent = null;
    protected static $event_release = null;
    protected $EventGroup = null;
    protected $TagType = null;
    protected $LocationTag = null;
    protected $OrganizerTag = null;
    protected $ParticipantTag = null;
    protected $Event = null;
    protected $Description = null;

    /**
     * Initialize the class
     *
     * @see \phpManufaktur\Event\Control\Import\Dialog::initialize()
     */
    protected function initialize(Application $app)
    {
        parent::initialize($app);

        self::$script_start = microtime(true);
        ini_set('max_execution_time', self::$max_execution_time);

        $this->KeepInTouch = new KeepInTouchData($app);
        $this->Contact = new Contact($app);
        $this->kitEvent = new kitEventData($app);
        $this->EventGroup = new Group($app);
        $this->TagType = new TagType($app);
        $this->LocationTag = new LocationTag($app);
        $this->OrganizerTag = new OrganizerTag($app);
        $this->ParticipantTag = new ParticipantTag($app);
        $this->Event = new Event($app);
        $this->Description = new Description($app);

        if ($this->KeepInTouch->existsKIT()) {
            // KIT exists, check the version
            self::$kit_release = $this->KeepInTouch->getKITrelease();
            if (!is_null(self::$kit_release)) {
                if (version_compare(self::$kit_release, '0.72', '>=')) {
                    // check for kitEvent
                    if ($this->kitEvent->existsKitEvent()) {
                        self::$event_release = $this->kitEvent->getKitEventRelease();
                        if (!is_null(self::$event_release)) {
                            self::$import_is_possible = version_compare(self::$event_release, '0.43', '>=');
                        }
                    }
                }
            }
        }
    }

    /**
     * First step to import data from a kitEvent installation
     *
     * @param Application $app
     * @return string rendered dialog
     */
    public function start(Application $app)
    {
        // initialize the class
        $this->initialize($app);

        $records = 0;
        if (self::$import_is_possible) {
            $records = $this->kitEvent->countKitEventRecords();
            $this->setMessage('Detected a kitEvent installation (Release: %release%) with %count% active or locked events.',
                array('%release%' => self::$event_release, '%count%' => $records));
        }
        else {
            $this->setMessage('There exists no kitEvent installation at the parent CMS!');
        }

        $this->app['session']->set('EVENT_IMPORT_EVENTS_DETECTED', $records);
        $this->app['session']->set('EVENT_IMPORT_EVENTS_IMPORTED', 0);

        return $this->app['twig']->render($this->app['utils']->getTemplateFile(
            '@phpManufaktur/Event/Template', 'import/start.kitevent.twig'),
            array(
                'message' => $this->getMessage(),
                'records' => $records,
                'import_is_possible' => self::$import_is_possible,
                'kit_release' => self::$kit_release,
                'event_release' => self::$event_release
            ));
    }

    /**
     * Import a Event group from kitEvent to Event
     *
     * @param array $group
     * @throws \Exception
     */
    protected function importGroup($group)
    {
        if (!isset($group['group_name'])) {
            throw new \Exception("Missing the group name in the record!");
        }
        $group_name = $this->KeepInTouch->createIdentifier($group['group_name']);
        if (!$this->EventGroup->existsGroupName($group_name)) {
            try {
                // start transaction
                $this->app['db']->beginTransaction();
                $data = array(
                    'group_name' => $group_name,
                    'group_description' => $group['group_desc']
                );
                $group_id = -1;
                $this->EventGroup->insert($data, $group_id);

                // get participant
                $participant = $this->KeepInTouch->getIdentifierValue($group['kit_distribution_participant']);
                $participant = $this->KeepInTouch->createIdentifier($participant);
                if (!$this->TagType->existsTag($participant)) {
                    throw new \Exception("The Tag $participant does not exists, please check settings for the kitEvent Groups");
                }
                $data = array(
                    'group_id' => $group_id,
                    'tag_name' => $participant
                );
                $this->ParticipantTag->insert($data);

                // get organizer
                $organizer = $this->KeepInTouch->getIdentifierValue($group['kit_distribution_organizer']);
                $organizer = $this->KeepInTouch->createIdentifier($organizer);
                if (!$this->TagType->existsTag($organizer)) {
                    throw new \Exception("The Tag $organizer does not exists, please check settings for the kitEvent Groups");
                }
                $data = array(
                    'group_id' => $group_id,
                    'tag_name' => $organizer
                );
                $this->OrganizerTag->insert($data);

                // get location
                $location = $this->KeepInTouch->getIdentifierValue($group['kit_distribution_location']);
                $location = $this->KeepInTouch->createIdentifier($location);
                if (!$this->TagType->existsTag($location)) {
                    throw new \Exception("The Tag $location does not exists, please check settings for the kitEvent Groups");
                }
                $data = array(
                    'group_id' => $group_id,
                    'tag_name' => $location
                );
                $this->LocationTag->insert($data);

                // commit the transaction
                $this->app['db']->commit();
            } catch (\Exception $e) {
                // rollback the transaction
                $this->app['db']->rollback();
                throw new \Exception($e);
            }
        }
    }

    protected function importEvent($event_id)
    {
        $event = $this->kitEvent->getEventRecord($event_id);

        // check the integrity of event data
        $check = array('organizer_id', 'location_id', 'group_id', 'item_id');
        foreach ($check as $key => $value) {
            if ($event[$value] < 1) {
                $this->setMessage('Skipped kitEvent ID %event_id%: No valid value in %field%',
                    array('%event_id%' => $event_id, '%field%' => $value), true);
                return false;
            }
        }
        if (false === ($organizer_id = $this->KeepInTouch->getContactID4KeepInTouchID($event['organizer_id']))) {
            $this->setMessage('Skipped kitEvent ID %event_id%: Can not find the contact ID for the KIT ID %kit_id%.',
                array('%event_id%' => $event_id, '%kit_id%' => $event['organizer_id']), true);
        }

        if (false === ($location_id = $this->KeepInTouch->getContactID4KeepInTouchID($event['location_id']))) {
            $this->setMessage('Skipped kitEvent ID %event_id%: Can not find the contact ID for the KIT ID %kit_id%.',
                array('%event_id%' => $event_id, '%kit_id%' => $event['location_id']), true);
        }

        if (false === ($group_id = $this->kitEvent->getEventGroupID4kitEventGroupID($event['group_id']))) {
            $this->setMessage('Skipped kitEvent ID %event_id%: Can not determine the Event Group ID for the kitEvent Group ID %group_id%.',
                array('%event_id%' => $event_id, '%group_id%' => $event['group_id']), true);
        }

        if (false === ($item = $this->kitEvent->getEventItem($event['item_id']))) {
            $this->setMessage('Skipped kitEvent ID %event_id%: Can not read the items for this event.',
                array('%event_id%' => $event_id), true);
        }


        try {
            // start transaction
            $this->app['db']->beginTransaction();

            $data = array(
                'group_id' => $group_id,
                'event_type' => 'EVENT',
                'event_organizer' => $organizer_id,
                'event_location' => $location_id,
                'event_date_from' => $event['evt_event_date_from'],
                'event_date_to' => $event['evt_event_date_to'],
                'event_publish_from' => $event['evt_publish_date_from'],
                'event_publish_to' => $event['evt_publish_date_to'],
                'event_costs' => $item['item_costs'],
                'event_participants_max' => $event['evt_participants_max'],
                'event_participants_total' => $event['evt_participants_total'],
                'event_deadline' => $event['evt_deadline'],
                'description_title' => $item['item_title'],
                'description_short' => $item['item_desc_short'],
                'description_long' => $item['item_desc_long'],
                'event_status' => ($event['evt_status'] == '1') ? 'ACTIVE' : 'LOCKED'
            );

            $this->Event->insertEvent($data);

            // commit the transaction
            $this->app['db']->commit();
        } catch (\Exception $e) {
            // rollback the transaction
            $this->app['db']->rollback();
            throw new \Exception($e);
        }
        return true;
    }

    /**
     * Import all events from kitEvent into kitFramework Event
     *
     * @param Application $app
     */
    public function import(Application $app)
    {
        // initialize the class
        $this->initialize($app);

        if (is_null($this->app['session']->get('EVENT_IMPORT_EVENTS_DETECTED', null))) {
            // no session set - show the start dialog
            return $this->start($app);
        }

        $this->app['monolog']->addInfo('Start Import from kitEvent');
        $counter = 0;
        $prompt_success = true;
        if (self::$import_is_possible) {
            // execute the import

            $event_ids = $this->kitEvent->getAllKitEventIDs();

            // first handle the kitEvent Groups
            $groups = $this->kitEvent->getEventGroups();
            foreach ($groups as $group) {
                $this->importGroup($group);
            }

            $counter = 0;
            foreach ($event_ids as $event) {
                if (!$this->importEvent($event['evt_id'])) {
                    // dont count
                    continue;
                }
                // increase counter
                $counter++;
                $total = $this->app['session']->get('EVENT_IMPORT_EVENTS_IMPORTED', 0) + $counter;
                $this->app['session']->set('EVENT_IMPORT_EVENTS_IMPORTED', $total);

                if (((microtime(true) - self::$script_start) + 5) > self::$max_execution_time) {
                    // abort import to prevent timeout
                    $this->setMessage('To prevent a timeout of the script the import was aborted after import of %counter% records. Please reload this page to continue the import process.',
                        array('%counter%' => $counter));
                    $this->app['monolog']->addInfo(sprintf('[Import kitEvent] Script aborted after %.3f seconds and %d records to prevent a timeout', microtime(true) - self::$script_start, $counter));
                    $prompt_success = false;
                    break;
                }
            }

            $events_detected = $this->app['session']->get('EVENT_IMPORT_EVENTS_DETECTED', 0);
            $events_imported = $this->app['session']->get('EVENT_IMPORT_EVENTS_IMPORTED', 0);

            if ($prompt_success) {
                $this->setMessage('The import from kitEvent was successfull finished.');
                $this->app['monolog']->addInfo('The import from kitEvent was successfull finished.');

                $this->app['session']->remove('EVENT_IMPORT_EVENTS_DETECTED');
                $this->app['session']->remove('EVENT_IMPORT_EVENTS_IMPORTED');
            }
        }
        else {
            $this->setMessage('There exists no kitEvent installation at the parent CMS!');
        }
        return $this->app['twig']->render($this->app['utils']->getTemplateFile(
            '@phpManufaktur/Event/Template', 'import/import.kitevent.twig'),
            array(
                'message' => $this->getMessage(),
                'events' => array(
                    'detected' => $events_detected,
                    'imported' => $events_imported
                )
            ));
    }
}
