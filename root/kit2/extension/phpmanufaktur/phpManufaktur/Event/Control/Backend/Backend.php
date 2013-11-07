<?php

/**
 * Event
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://addons.phpmanufaktur.de/event
 * @copyright 2013 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\Event\Control\Backend;

use Silex\Application;

class Backend {

    protected $app = null;
    protected static $usage = null;
    protected static $usage_param = null;
    protected static $message = '';

    /**
     * Constructor
     */
    public function __construct(Application $app=null) {
        if (!is_null($app)) {
            $this->initialize($app);
        }
    }

    /**
     * Initialize the class with the needed parameters
     *
     * @param Application $app
     */
    protected function initialize(Application $app)
    {
        $this->app = $app;
        $cms = $this->app['request']->get('usage');
        self::$usage = is_null($cms) ? 'framework' : $cms;
        self::$usage_param = (self::$usage != 'framework') ? '?usage='.self::$usage : '';
        // set the locale from the CMS locale
        if (self::$usage != 'framework') {
            $app['translator']->setLocale($this->app['session']->get('CMS_LOCALE', 'en'));
        }
    }

    /**
     * Get the toolbar for all backend dialogs
     *
     * @param string $active dialog
     * @return multitype:multitype:string boolean
     */
    public function getToolbar($active) {
        $toolbar_array = array(
            'event_list' => array(
                'text' => 'Event list',
                'hint' => 'List of all active events',
                'link' => FRAMEWORK_URL.'/admin/event/list'.self::$usage_param,
                'active' => ($active == 'event_list')
            ),
            'event_edit' => array(
                'text' => 'Event',
                'hint' => 'Create a new event',
                'link' => FRAMEWORK_URL.'/admin/event/edit'.self::$usage_param,
                'active' => ($active == 'event_edit')
            ),
            'registration' => array(
                'text' => 'Registrations',
                'hint' => 'List of all registrations for events',
                'link' => FRAMEWORK_URL.'/admin/event/registration'.self::$usage_param,
                'active' => ($active == 'registration')
            ),
            'propose' => array(
                'text' => 'Proposes',
                'hint' => 'List of actual submitted proposes for events',
                'link' => FRAMEWORK_URL.'/admin/event/propose'.self::$usage_param,
                'active' => ($active == 'propose')
            ),
            'contact_list' => array(
                'text' => 'Contact list',
                'hint' => 'List of all available contacts (Organizer, Locations, Participants)',
                'link' => FRAMEWORK_URL.'/admin/event/contact/list'.self::$usage_param,
                'active' => ($active == 'contact_list')
            ),
            'contact_edit' => array(
                'text' => 'Contact',
                'hint' => 'Create a new contact',
                'link' => FRAMEWORK_URL.'/admin/event/contact/select'.self::$usage_param,
                'active' => ($active == 'contact_edit')
            ),
            'group' => array(
                'text' => 'Groups',
                'hint' => 'List of all available event groups',
                'link' => FRAMEWORK_URL.'/admin/event/group/list'.self::$usage_param,
                'active' => ($active == 'group')
            ),

            'about' => array(
                'text' => 'About',
                'hint' => 'Information about the Event extension',
                'link' => FRAMEWORK_URL.'/admin/event/about'.self::$usage_param,
                'active' => ($active == 'about')
                ),
        );
        return $toolbar_array;
    }

    /**
     * @return the $message
     */
    public function getMessage ()
    {
        return self::$message;
    }

      /**
     * @param string $message
     */
    public function setMessage($message, $params=array())
    {
        self::$message .= $this->app['twig']->render($this->app['utils']->getTemplateFile('@phpManufaktur/Event/Template', 'backend/message.twig'),
            array('message' => $this->app['translator']->trans($message, $params)));
    }

    public function clearMessage()
    {
        self::$message = '';
    }

    /**
     * Check if a message is active
     *
     * @return boolean
     */
    public function isMessage()
    {
        return !empty(self::$message);
    }
 }
