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
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
use phpManufaktur\Event\Data\Event\Event as EventData;
use Symfony\Component\BrowserKit\Response;


require_once MANUFAKTUR_PATH.'/Event/Control/Include/phpqrcode/qrlib.php';

class EventQRCode
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
     * Initialize class EventQRCode
     *
     * @param Application $app
     */
    public function initialize(Application $app)
    {
        $this->app = $app;
        // get the configuration
        self::$config = $this->app['utils']->readConfiguration(MANUFAKTUR_PATH.'/Event/config.event.json');
    }

    /**
     * Create a QR-Code PNG file for the desired $event_id
     *
     * @param integer $event_id
     * @throws \Exception
     * @return boolean
     */
    public function create($event_id)
    {
        if (!isset(self::$config['qrcode']['active']) || !isset(self::$config['qrcode']['framework']['path']['link']) ||
            !isset(self::$config['qrcode']['framework']['path']['ical']) || !isset(self::$config['qrcode']['settings']['content']) ||
            !isset(self::$config['qrcode']['settings']['size']) || !isset(self::$config['qrcode']['settings']['error_correction']) ||
            !isset(self::$config['qrcode']['settings']['margin'])) {
            throw new \Exception("The QR-Code settings in config.event.json are invalid, missing items, please check the configuration.");
        }

        if (!self::$config['qrcode']['active']) {
            // QR-Code is not active
            $this->app['monolog']->addDebug("Skipped QR-Code creation for event ID $event_id");
            return true;
        }

        if (strtolower(self::$config['qrcode']['settings']['content']) == 'ical') {
            // use iCal data as content for the QR-Code
            $this->app['monolog']->addInfo("Start creation of a QR-Code with iCal content for event ID $event_id");
            if (!isset(self::$config['ical']['active']) || !self::$config['ical']['active']) {
                // ical creation must be active!
                throw new \Exception("To create QR-Codes with iCal information as content the iCal creation must also enabled in config.event.json.");
            }
            // create ical filename
            if (!isset(self::$config['ical']['framework']['path']) || empty(self::$config['ical']['framework']['path'])) {
                throw new \Exception("Missing the path to iCal files, please check the config.event.json.");
            }
            $ical_file = FRAMEWORK_PATH.self::$config['ical']['framework']['path']."/$event_id.ics";
            if (!file_exists($ical_file)) {
                throw new \Exception("The iCal file $ical_file does not exists!");
            }
            if (false === ($content = file_get_contents($ical_file))) {
                throw new \Exception("Can't read the iCal file $ical_file.");
            }
            $qrcode_path = FRAMEWORK_PATH.self::$config['qrcode']['framework']['path']['ical'];
        }
        else {
            // use a permalink as content for the QR-Code
            if (!isset(self::$config['permalink']['cms']['url']) || empty(self::$config['permalink']['cms']['url'])) {
                throw new \Exception("To create a QR-Code with a permalink, the permalink target must be defined in the config.event.json.");
            }
            $content = FRAMEWORK_URL."/event/id/$event_id";
            $qrcode_path = FRAMEWORK_PATH.self::$config['qrcode']['framework']['path']['link'];
        }

        // check the directory
        if (!$this->app['filesystem']->exists($qrcode_path)) {
            $this->app['filesystem']->mkdir($qrcode_path);
        }
        $qrcode_file = $qrcode_path."/$event_id.png";

        // get the settings
        $error_correction = self::$config['qrcode']['settings']['error_correction'];
        $size = self::$config['qrcode']['settings']['size'];
        $margin = self::$config['qrcode']['settings']['margin'];

        $QRCode = new \QRcode();
        $QRCode->png($content, $qrcode_file, $error_correction, $size, $margin);

        $this->app['monolog']->addInfo("QR-Code for event ID $event_id successfull created.");
        return true;
    }

    /**
     * This Controller return the QR-Code PNG for the desired event ID
     *
     * @param Application $app
     * @param integer $event_id
     * @throws FileNotFoundException
     * @throws \Exception
     */
    public function ControllerGetQRCodeFile(Application $app, $event_id)
    {
        $this->initialize($app);
        if (isset(self::$config['qrcode']['active']) && self::$config['qrcode']['active'] && isset(self::$config['qrcode']['settings']['content'])) {
            if (self::$config['qrcode']['settings']['content'] == 'ical') {
                $file = FRAMEWORK_PATH.self::$config['qrcode']['framework']['path']['ical']."/$event_id.png";
            }
            else {
                $file = FRAMEWORK_PATH.self::$config['qrcode']['framework']['path']['link']."/$event_id.png";
            }
            if ($this->app['filesystem']->exists($file)) {
                $stream = function () use ($file) {
                    readfile($file);
                };
                return $this->app->stream($stream, 200, array(
                    'Content-Type' => 'image/png',
                    'Content-length' => filesize($file),
                    'Content-Disposition' => sprintf('inline; filename="event_%05d.png"', $event_id)
                ));
            }
            throw new FileNotFoundException(basename($file));
        }
        throw new \Exception("Sorry, but this QR-Code is not available!");
    }

    /**
     * Rebuild the QR-Codes for all events
     *
     * @param Application $app
     * @return \Symfony\Component\BrowserKit\Response
     */
    public function ControllerRebuildAllQRCodeFiles(Application $app)
    {
        // log script start
        $script_start = microtime(true);

        // initialize class
        $this->initialize($app);

        if (!self::$config['qrcode']['active']) {
            $this->app['session']->remove('REBUILD_QRCODE_TOTAL');
            $this->app['session']->remove('REBUILD_QRCODE_START');
            $response = "Creating QR-Code files is not active, abort command.";
            $this->app['monolog']->addInfo($response);
            return new Response($response);
        }

        if (isset(self::$config['general']['max_execution_time'])) {
            ini_set('max_execution_time', self::$config['general']['max_execution_time']);
        }
        $max_execution_time = ini_get('max_execution_time');

        $start_at_id = $this->app['session']->get('REBUILD_QRCODE_START', 0);
        $rebuild_total = $this->app['session']->get('REBUILD_QRCODE_TOTAL', 0);

        if ($start_at_id > 0) {
            $this->app['monolog']->addInfo('Continue rebuilding all QR-Code files');
        }
        else {
            $this->app['monolog']->addInfo('Start rebuilding all QR-Code files');
        }

        $EventData = new EventData($app);
        $events = $EventData->selectAllIDs($start_at_id);

        if ($start_at_id == 0) {
            // remove the existing QR-Code directory and all files
            $this->app['filesystem']->remove(FRAMEWORK_PATH.self::$config['qrcode']['framework']['path']['ical']);
            $this->app['filesystem']->remove(FRAMEWORK_PATH.self::$config['qrcode']['framework']['path']['link']);
            $this->app['monolog']->addInfo("Remove the existing QR-Code directories and all QR-Code files");
        }

        foreach ($events as $event) {
            if (((microtime(true) - $script_start) + 5) > $max_execution_time) {
                // abort the script to avoid a timeout
                $this->app['session']->set('REBUILD_QRCODE_START', $event['event_id']);
                $this->app['session']->set('REBUILD_QRCODE_TOTAL', $rebuild_total);
                $response = sprintf("Abort the rebuilding of QR-Codes at event ID %d to avoid a timeout, just reload this page to continue.", $event['event_id']);
                $this->app['monolog']->addInfo($response);
                return new Response($response);
            }
            if ($start_at_id > 0) {
                if ($start_at_id < $event['event_id']) continue;
                $start_at_id = 0;
                $this->app['session']->remove('REBUILD_QRCODE_START');
            }
            $this->create($event['event_id']);
            $rebuild_total++;
        }

        $this->app['session']->remove('REBUILD_QRCODE_TOTAL');

        $response = "Created $rebuild_total QR-Code files.";
        $this->app['monolog']->addInfo($response);
        return new Response($response);
    }

}
