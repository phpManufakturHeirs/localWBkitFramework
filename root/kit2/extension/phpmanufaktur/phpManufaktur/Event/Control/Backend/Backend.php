<?php

/**
 * Event
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de/Event
 * @copyright 2013 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\Event\Control\Backend;

use Silex\Application;
use phpManufaktur\Basic\Control\Pattern\Alert;
use phpManufaktur\Event\Control\Configuration;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class Backend extends Alert
{

    protected static $usage = null;
    protected static $config = null;

    /**
     *
     * @param Application $app
     */
    protected function initialize(Application $app)
    {
        parent::initialize($app);

        $cms = $this->app['request']->get('usage');
        self::$usage = is_null($cms) ? 'framework' : $cms;
        // set the locale from the CMS locale
        if (self::$usage != 'framework') {
            $app['translator']->setLocale($this->app['session']->get('CMS_LOCALE', 'en'));
        }
        $Configuration = new Configuration($app);
        self::$config = $Configuration->getConfiguration();
    }

    /**
     * Get the toolbar for all backend dialogs
     *
     * @param string $active dialog
     * @return multitype:multitype:string boolean
     */
    public function getToolbar($active) {
        $toolbar = array();
        foreach (self::$config['nav_tabs']['order'] as $tab) {
            switch ($tab) {
                case 'event_list':
                    $toolbar[$tab] = array(
                        'name' => 'event_list',
                        'text' => $this->app['translator']->trans('Event list'),
                        'hint' => $this->app['translator']->trans('List of all active events'),
                        'link' => FRAMEWORK_URL.'/admin/event/list',
                        'active' => ($active == 'event_list')
                    );
                    break;
                case 'event_edit':
                    $toolbar[$tab] = array(
                        'name' => 'event_edit',
                        'text' => ($active === 'event_edit') ? $this->app['translator']->trans('Edit event') : $this->app['translator']->trans('Create event'),
                        'hint' => $this->app['translator']->trans('Create or edit a event'),
                        'link' => FRAMEWORK_URL.'/admin/event/edit',
                        'active' => ($active == 'event_edit')
                    );
                    break;
                case 'subscription':
                    $toolbar[$tab] = array(
                        'name' => 'subscription',
                        'text' => $this->app['translator']->trans('Subscriptions'),
                        'hint' => $this->app['translator']->trans('List of all subscriptions for events'),
                        'link' => FRAMEWORK_URL.'/admin/event/subscription',
                        'active' => ($active == 'subscription')
                    );
                    break;
                case 'propose':
                    $toolbar[$tab] = array(
                        'name' => 'propose',
                        'text' => $this->app['translator']->trans('Proposes'),
                        'hint' => $this->app['translator']->trans('List of actual submitted proposes for events'),
                        'link' => FRAMEWORK_URL.'/admin/event/propose',
                        'active' => ($active == 'propose')
                    );
                    break;
                case 'contact_list':
                    $toolbar[$tab] = array(
                        'name' => 'contact_list',
                        'text' => $this->app['translator']->trans('Contact list'),
                        'hint' => $this->app['translator']->trans('List of all available contacts (Organizer, Locations, Participants)'),
                        'link' => FRAMEWORK_URL.'/admin/event/contact/list',
                        'active' => ($active == 'contact_list')
                    );
                    break;
                case 'contact_edit':
                    $toolbar[$tab] = array(
                        'name' => 'contact_edit',
                        'text' => ($active === 'contact_edit') ? $this->app['translator']->trans('Edit contact') : $this->app['translator']->trans('Create contact'),
                        'hint' => $this->app['translator']->trans('Create or edit a contact record'),
                        'link' => FRAMEWORK_URL.'/admin/event/contact/select',
                        'active' => ($active == 'contact_edit')
                    );
                    break;
                case 'group':
                    $toolbar[$tab] = array(
                        'name' => 'event_groups',
                        'text' => $this->app['translator']->trans('Groups'),
                        'hint' => $this->app['translator']->trans('List of all available event groups'),
                        'link' => FRAMEWORK_URL.'/admin/event/group/list',
                        'active' => ($active == 'group')
                    );
                    break;
                case 'about':
                    $toolbar[$tab] = array(
                        'name' => 'about',
                        'text' => $this->app['translator']->trans('About'),
                        'hint' => $this->app['translator']->trans('Information about the Event extension'),
                        'link' => FRAMEWORK_URL.'/admin/event/about',
                        'active' => ($active == 'about')
                        );
                    break;
            }
        }
        return $toolbar;
    }

    /**
     * Controller to select the default navigation tab.
     *
     * @param Application $app
     * @throws \Exception
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function ControllerSelectDefaultTab(Application $app)
    {
        $this->initialize($app);

        switch (self::$config['nav_tabs']['default']) {
            case 'about':
                $route = '/admin/event/about';
                break;
            case 'event_list':
                $route = '/admin/event/list';
                break;
            case 'event_edit':
                $route = '/admin/event/edit';
                break;
            case 'subscription':
                $route = '/admin/event/subscription';
                break;
            case 'propose':
                $route = '/admin/event/propose';
                break;
            case 'contact_list':
                $route = '/admin/event/contact/list';
                break;
            case 'contact_edit':
                $route = '/admin/event/contact/select';
                break;
            case 'group':
                $route = '/admin/event/group/list';
                break;
            default:
                throw new \Exception('Invalid default nav_tab in configuration: '.self::$config['nav_tabs']['default']);
        }

        $subRequest = Request::create($route, 'GET', array('usage' => self::$usage));
        return $app->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
    }
}
