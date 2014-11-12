<?php

/**
 * Event
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de/Event
 * @copyright 2013 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\Event\Control\Command;

use Silex\Application;
use Carbon\Carbon;
use phpManufaktur\Event\Data\Event\Event as EventData;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
use Symfony\Component\BrowserKit\Response;

require_once MANUFAKTUR_PATH.'/Event/Control/Include/iCalcreator/iCalcreator.class.php';

class EventICal
{
    protected $app = null;
    protected static $config = null;

    /**
     * Constructor
     *
     * @param Application $app
     */
    public function __construct(Application $app=null)
    {
        if (!is_null($app)) {
            $this->initialize($app);
        }
    }

    /**
     * Initialize EventICal
     *
     * @param Application $app
     */
    protected function initialize(Application $app)
    {
        $this->app = $app;
        // get the configuration
        self::$config = $this->app['utils']->readConfiguration(MANUFAKTUR_PATH.'/Event/config.event.json');
    }

    /**
     * Create a iCal file from the given event.
     * Uses the settings in /Event/config.event.json
     *
     * @param array $event
     * @throws \Exception
     * @return boolean
     */
    public function CreateICalFile(EventData $EventData, $event_id)
    {
        try {
            if (!self::$config['ical']['active']) {
                // iCal is not active
                $this->app['monolog']->addDebug("Skipped iCal creation for event ID $event_id");
                return true;
            }
            // get the event
            $event = $EventData->selectEvent($event_id);

            // init iCalcreator
            $vCal = new \vcalendar(array(
                'unique_id' => 'kit2.phpmanufaktur.de',
                'language' => $this->app['translator']->getLocale()
            ));
            $evt = &$vCal->newComponent('vevent');
            $evt->setProperty('class', 'PUBLIC');
            $evt->setProperty('priority', 0);
            $evt->setProperty('status', 'CONFIRMED');
            $evt->setProperty('summary', $event['description_title']);

            // no html tags, convert all entities
            $description = html_entity_decode(strip_tags($event['description_short']));
            $evt->setProperty('description', $description);

            // init Carbon
            $Date = new Carbon($event['event_date_from']);
            $evt->setProperty('dtstart',$Date->year,$Date->month,$Date->day, $Date->hour, $Date->minute, $Date->second);
            $Date->setTimestamp(strtotime($event['event_date_to']));
            $evt->setProperty('dtend',$Date->year,$Date->month,$Date->day, $Date->hour, $Date->minute, $Date->second);

            // set location
            if ($event['contact']['location']['contact']['contact_type'] == 'COMPANY') {
                $location = $event['contact']['location']['company'][0]['company_name'];
            }
            else {
                $location = $event['contact']['location']['person'][0]['person_last_name'];
            }
            $evt->setProperty('location', $location);

            // create the calender
            $ical_data = $vCal->createCalendar();

            // check directory
            $path = FRAMEWORK_PATH.self::$config['ical']['framework']['path'];
            if (!$this->app['filesystem']->exists($path)) {
                $this->app['filesystem']->mkdir($path);
            }
            // create ical file
            $ical_file = sprintf('%s/%d.ics', $path, $event['event_id']);
            if (!file_put_contents($ical_file, $ical_data)) {
                throw new \Exception("Can't create the file $ical_file.");
            }

            // add a log entry
            $this->app['monolog']->addInfo("Created $ical_file");

            return true;
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }

    /**
     * This controller return the iCal file for the desired event ID
     *
     * @param Application $app
     * @param integer $event_id
     * @throws FileNotFoundException
     * @throws \Exception
     */
    public function ControllerGetICalFile(Application $app, $event_id)
    {
        // initialize class
        $this->initialize($app);

        if (isset(self::$config['ical']['active']) && self::$config['ical']['active'] && isset(self::$config['ical']['framework']['path'])) {
            $file = FRAMEWORK_PATH.self::$config['ical']['framework']['path'].'/'.$event_id.'.ics';
            if ($this->app['filesystem']->exists($file)) {
                $stream = function () use ($file) {
                    readfile($file);
                };
                return $this->app->stream($stream, 200, array(
                    'Content-Type' => 'text/calendar',
                    'Content-length' => filesize($file),
                    'Content-Disposition' => sprintf('inline; filename="event_%05d.ics"', $event_id)
                ));
            }
            throw new FileNotFoundException(basename($file));
        }
        throw new \Exception("Sorry, but this iCal file is not available!");
    }

    /**
     * Rebuild all iCal files for all events
     *
     * @param Application $app
     * @return \Symfony\Component\BrowserKit\Response
     */
    public function ControllerRebuildAllICalFiles(Application $app)
    {
        $script_start = microtime(true);
        $max_execution_time = ini_get('max_execution_time');

        // initialize class
        $this->initialize($app);

        $start_at_id = $this->app['session']->get('REBUILD_ICAL_START', 0);
        $rebuild_total = $this->app['session']->get('REBUILD_ICAL_TOTAL', 0);

        if ($start_at_id > 0) {
            $this->app['monolog']->addInfo('Continue rebuilding all iCal files');
        }
        else {
            $this->app['monolog']->addInfo('Start rebuilding all iCal files');
        }

        if (!self::$config['ical']['active']) {
            $response = "Creating iCal files is not active, abort command.";
            $this->app['monolog']->addInfo($response);
            return new Response($response);
        }

        $EventData = new EventData($app);
        $events = $EventData->selectAllIDs($start_at_id);

        if ($start_at_id == 0) {
            // remove the existing iCal directory and all files
            $this->app['filesystem']->remove(FRAMEWORK_PATH.self::$config['ical']['framework']['path']);
            $this->app['monolog']->addInfo("Remove the existing iCal directory and all iCal files");
        }

        foreach ($events as $event) {
            if (((microtime(true) - $script_start) + 5) > $max_execution_time) {
                // abort the script to avoid a timeout
                $this->app['session']->set('REBUILD_ICAL_START', $event['event_id']);
                $this->app['session']->set('REBUILD_ICAL_TOTAL', $rebuild_total);
                $response = sprintf("Abort the rebuilding of iCal files at event ID %d to avoid a timeout, just reload this page to continue.", $event['event_id']);
                $this->app['monolog']->addInfo($response);
                return new Response($response);
            }
            if ($start_at_id > 0) {
                if ($start_at_id < $event['event_id']) continue;
                $start_at_id = 0;
                $this->app['session']->remove('REBUILD_ICAL_START');
            }
            $this->CreateICalFile($EventData, $event['event_id']);
            $rebuild_total++;
        }

        $this->app['session']->remove('REBUILD_ICAL_TOTAL');

        $response = "Created $rebuild_total iCal files.";
        $this->app['monolog']->addInfo($response);
        return new Response($response);
    }
}
