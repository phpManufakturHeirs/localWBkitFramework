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
use phpManufaktur\Basic\Control\kitCommand\Basic;
use phpManufaktur\Event\Data\Event\Event as EventData;
use phpManufaktur\Basic\Data\CMS\Page;
use phpManufaktur\Event\Data\Event\RecurringEvent as RecurringEventData;

class Event extends Basic
{
    protected $EventData = null;
    protected $RecurringData = null;
    protected $Message = null;
    protected static $parameter = null;
    protected static $event_id = -1;
    protected static $config;

    /**
     * (non-PHPdoc)
     * @see \phpManufaktur\Basic\Control\kitCommand\Basic::initParameters()
     */
    protected function initParameters(Application $app, $parameter_id=-1, $event_id=null)
    {
        // init parent
        parent::initParameters($app, $parameter_id);

        // init Event
        $parameters = $this->getCommandParameters();

        if (!is_null($event_id)) {
            $parameters['id'] = $event_id;
        }

        $GET = $this->getCMSgetParameters();
        if (isset($GET['cmd']) && ($GET['cmd'] == 'event')) {
            // check the CMS GET parameters only if cmd=event isset!
            foreach ($GET as $key => $value) {
                if (($key == 'pid') || ($key == 'parameter_id')) continue;
                $parameters[$key] = $value;
            }
        }
        $this->setCommandParameters($parameters);
        self::$parameter = $this->getCommandParameters();

        // get the configuration
        self::$config = $this->app['utils']->readConfiguration(MANUFAKTUR_PATH.'/Event/config.event.json');

        // general check for parameters
        self::$parameter['map'] = (isset(self::$parameter['map'])) ? true : false;
        $this->checkParameterLink();
        $this->checkParameterQRCode();
        // use rating?
        self::$parameter['rating'] = (isset(self::$parameter['rating']) &&
            ((strtolower(self::$parameter['rating']) == 'false') || (self::$parameter['rating'] == 0))) ? false : true;

        $this->EventData = new EventData($app);
        $this->Message = new Message($app);
        $this->RecurringData = new RecurringEventData($app);
    }

    /**
     * (non-PHPdoc)
     * @see \phpManufaktur\Basic\Control\kitCommand\Basic::setRedirectRoute()
     */
    public function setRedirectRoute($route)
    {
        parent::setRedirectActive(true);
        parent::setRedirectRoute($route);

        $this->Message->setRedirectActive(false);
        $this->Message->setRedirectRoute($this->getRedirectRoute());
    }

    /**
     * Return a dialog for the given Event ID and the view to use
     *
     * @param integer $event_id
     * @param string $view - possible: small, detail, custom
     */
    protected function selectID($event_id, $view='small')
    {
        if (false === ($event = $this->EventData->selectEvent($event_id))) {
            return $this->Message->render('The record with the ID %id% does not exists!', array('%id%' => $event_id));
        }
        // check the view
        if (isset(self::$parameter['view'])) {
            $view = strtolower(self::$parameter['view']);
        }
        if (!in_array($view, array('small', 'detail', 'custom', 'recurring'))) {
            // undefined view!
            return $this->Message->render('The view <b>%view%</b> does not exists for the action event!',
                array('%view%' => $view));
        }

        $recurring = array();
        $recurring_events = array();
        $recurring_count = 0;
        if ($view == 'recurring') {
            if ($event['event_recurring_id'] < 1) {
                // no recurring event
                $view = 'detail';
            }
            elseif (false !== ($recurring = $this->RecurringData->select($event['event_recurring_id']))) {
                // this is a recurring event
                // first get the parent event record
                if (false === ($event = $this->EventData->selectEvent($recurring['parent_event_id']))) {
                    return $this->Message->render('The record with the ID %id% does not exists!', array('%id%' => $recurring['parent_event_id']));
                }
                // get all active recurring events
                if (false === ($items = $this->EventData->selectRecurringEvents($recurring['recurring_id']))) {
                    return $this->Message->render('No active events for the recurring ID %id%!',
                        array('%id%' => $recurring['recurring_id']));
                }
                foreach ($items as $item) {
                    $route = base64_encode('/event/id/'.$event_id.'/view/'.$view);
                    $item['link']['subscribe'] = FRAMEWORK_URL.'/event/subscribe/id/'.$item['event_id'].'/redirect/'.$route.'?pid='.$this->getParameterID();
                    $recurring_events[] = $item;
                }
                // count all recurring events
                $recurring_count = $this->EventData->countRecurringEvents($event['event_recurring_id']);
            }
            else {
                // recurring ID does not exists
                $view = 'detail';
                $this->setAlert('The record with the ID %id% does not exists!',
                    array('%id%' => $event['event_recurring_id']), self::ALERT_TYPE_DANGER, true, array(__METHOD__, __LINE__));
            }
        }

        // set redirect route
        $this->setRedirectRoute("/event/id/$event_id");
        $this->setRedirectActive(true);
        $this->setPageTitle(strip_tags($event['description_title']));
        // return the event dialog
        return $this->app['twig']->render($this->app['utils']->getTemplateFile(
            '@phpManufaktur/Event/Template',
            "command/event.view.$view.twig",
            $this->getPreferredTemplateStyle()),
            array(
                'basic' => $this->getBasicSettings(),
                'event' => $event,
                'parameter' => self::$parameter,
                'config' => self::$config,
                'recurring' => $recurring,
                'recurring_events' => $recurring_events,
                'recurring_count' => $recurring_count
            ));
    }

    /**
     * Check the given link parameter, step through, mark active status, set the URL
     * and set the target of the link
     *
     * @throws \Exception
     */
    protected function checkParameterLink()
    {
        if (!isset(self::$parameter['link'])) {
            $links = array();
        }
        elseif (strpos(self::$parameter['link'], ',')) {
            $links = array();
            foreach (explode(',', self::$parameter['link']) as $link) {
                $links[] = strtolower(trim($link));
            }
        }
        else {
            $links = array(strtolower(trim(self::$parameter['link'])));
        }

        if (in_array('perma', $links)) {
            // rewrite the shorthand 'perma' to 'permalink'
            $links[] = 'permanent';
            unset($links['perma']);
        }

        self::$parameter['link'] = array();

        $event_id = (isset(self::$parameter['id'])) ? self::$parameter['id'] : null;

        $available_links = array('detail', 'ical', 'map', 'permanent', 'subscribe', 'edit');
        foreach ($available_links as $link) {
            self::$parameter['link'][$link]['target'] = '_self';
            switch ($link) {
                case 'detail':
                    self::$parameter['link'][$link]['active'] = in_array($link, $links);
                    if (isset(self::$parameter['redirect'])) {
                        self::$parameter['link'][$link]['target'] = '_top';
                        if (is_numeric(self::$parameter['redirect'])) {
                            // get the URL from the CMS PAGE ID
                            $Page = new Page($this->app);
                            $url = $Page->getURL(self::$parameter['redirect']);
                        }
                        else {
                            // use the submitted URL
                            $url = self::$parameter['redirect'];
                        }
                        // submit no PID but the CMD identifier and the ID of the event
                        self::$parameter['link'][$link]['url'] = sprintf('%s%s%s', $url, strpos($url, '?') ? '&' : '?',
                            http_build_query(array(
                                'cmd' => 'event',
                                'id' => $event_id
                            ), '', '&'));
                    }
                    else {
                        // use the route to event ID
                        self::$parameter['link'][$link]['url'] = FRAMEWORK_URL."/event/id/$event_id/view/detail?pid=".$this->getParameterID();
                    }
                    break;
                case 'ical':
                    self::$parameter['link'][$link]['active'] = (!is_null($event_id) && in_array($link, $links));
                    self::$parameter['link'][$link]['url'] = !is_null($event_id) ? FRAMEWORK_URL."/event/ical/$event_id" : null;
                    break;
                case 'map':
                    self::$parameter['link'][$link]['active'] = in_array($link, $links);
                    // the url for the map will be set within the template
                    self::$parameter['link'][$link]['url'] = null;
                    break;
                case 'permanent':
                    self::$parameter['link'][$link]['active'] = (!is_null($event_id) && in_array($link, $links));
                    self::$parameter['link'][$link]['url'] = !is_null($event_id) ? FRAMEWORK_URL."/event/perma/id/$event_id" : null;
                    self::$parameter['link'][$link]['target'] = '_top';
                    break;
                case 'subscribe':
                    self::$parameter['link'][$link]['active'] = (!is_null($event_id) && in_array($link, $links));
                    $view = isset(self::$parameter['view']) ? strtolower(self::$parameter['view']) : 'small';
                    $route = base64_encode('/event/id/'.$event_id.'/view/'.$view);
                    self::$parameter['link'][$link]['url'] = !is_null($event_id) ? FRAMEWORK_URL."/event/subscribe/id/$event_id/redirect/$route?pid=".$this->getParameterID() : null;
                    break;
                case 'edit':
                    self::$parameter['link'][$link]['active'] = (!is_null($event_id) && in_array($link, $links));
                    $view = isset(self::$parameter['view']) ? strtolower(self::$parameter['view']) : 'small';
                    $route = base64_encode('/event/id/'.$event_id.'/view/'.$view);
                    self::$parameter['link'][$link]['url'] = !is_null($event_id) ? FRAMEWORK_URL.'/event/edit/id/'.$event_id.'/redirect/'.$route.'?pid='.$this->getParameterID() : null;
                    break;
                default:
                    throw new \Exception("The link $link is not defined!");
            }
        }
    }

    /**
     * Check the parameter for the QR-Code, get the URL and the size of the PNG
     *
     * @return boolean
     */
    protected function checkParameterQRCode()
    {
        $use_qrcode = (isset(self::$parameter['qrcode']));

        unset(self::$parameter['qrcode']);

        self::$parameter['qrcode'] = array(
            'active' => false,
            'url' => null,
            'width' => 0,
            'height' => 0
        );

        if ($use_qrcode) {
            if (!self::$config['qrcode']['active']) {
                $this->setAlert('Using qrcode[] is not enabled in config.event.json!');
                return false;
            }
            $subdir = (self::$config['qrcode']['settings']['content'] == 'ical') ? 'ical' : 'link';
            if (file_exists(FRAMEWORK_PATH.self::$config['qrcode']['framework']['path'][$subdir]."/".self::$parameter['id'].".png")) {
                list($width, $height) = getimagesize(FRAMEWORK_PATH.self::$config['qrcode']['framework']['path'][$subdir]."/".self::$parameter['id'].".png");
            }
            else {
                $this->setAlert('The QR-Code file does not exists, please rebuild all QR-Code files.');
                return false;
            }
            self::$parameter['qrcode'] = array(
                'active' => true,
                'url' => FRAMEWORK_URL.'/event/qrcode/'.self::$parameter['id'],
                'width' => $width,
                'height' => $height
            );
        }
        return true;
    }

    /**
     * Will be called from the action handler in class \Action
     *
     * @param Application $app
     */
    public function exec(Application $app)
    {
        $this->initParameters($app);
        if (isset(self::$parameter['id'])) {
            // set the event ID as SESSION parameter for usage by other extensions
            $this->app['session']->set('EVENT_ID', self::$parameter['id']);

            return $this->selectID(self::$parameter['id']);
        }
        else {
            // no parameter which can be processed
            $Message = new Message($this->app);
            return $Message->render('Missing a second parameter corresponding to the parameter <i>action[event]</i>');
        }
    }

    /**
     * Controller to process the Event with the given ID
     * Route: /event/id/{event_id}
     * Route: /event/id/{event_id}/view/{view}
     *
     * @param Application $app
     * @param integer $event_id
     * @return string event dialog
     */
    public function ControllerSelectID(Application $app, $event_id, $view='detail')
    {
        $this->initParameters($app, -1, $event_id);
        self::$parameter['view'] = $view;
        self::$parameter['id'] = $event_id;
        return $this->selectID($event_id, $view);
    }

    /**
     * Controller to process the permanent link for the given Event ID
     * Route: /event/perma/id/{event_id}
     *
     * @param Application $app
     * @param integer $event_id
     * @throws \Exception
     */
    public function ControllerSelectPermaLinkID(Application $app, $event_id)
    {
        // init parent and class
        $this->initParameters($app, -1, $event_id);
        if (isset(self::$config['permalink']['cms']['url']) && !empty(self::$config['permalink']['cms']['url'])) {

            // set the event ID as SESSION parameter for usage by other extensions
            $this->app['session']->set('EVENT_ID', $event_id);

            $redirect = sprintf('%s%s%s', self::$config['permalink']['cms']['url'], strpos(self::$config['permalink']['cms']['url'], '?') ? '&' : '?',
                http_build_query(array(
                    'cmd' => 'event',
                    'id' => $event_id
                ), '', '&'));
            // check if a scroll_to_id parameter exists
            if ($this->isSetFrameScrollToID()) {
                $redirect .= '&fsti='.$this->getFrameScrollToID();
            }
            // redirect - no direct call
            return $this->app->redirect($redirect);
        }
        else {
            throw new \Exception("Please specifiy a permalink URL for the CMS in config.event.json.");
        }
    }


}
