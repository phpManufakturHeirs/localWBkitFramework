<?php

/**
 * Contact
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de/Contact
 * @copyright 2013 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\Contact\Control\Dialog\Simple;

use Silex\Application;
use phpManufaktur\Contact\Data\Contact\Overview;
use phpManufaktur\Contact\Control\Dialog\Simple\ContactList as ContactListControl;

class Search extends Dialog {

    protected $ContactListControl = null;
    protected static $columns = null;

    /**
     * Constructor
     *
     * @param Application $app
     */
    public function __construct(Application $app=null, $options=null)
    {
        parent::__construct($app);
        if (!is_null($app)) {
            $this->initialize($app, $options);
        }
    }

    /**
     * (non-PHPdoc)
     * @see \phpManufaktur\Contact\Control\Alert::initialize()
     */
    protected function initialize(Application $app, $options=null)
    {
        parent::initialize($app);

        $this->ContactListControl = new ContactListControl($this->app);

        $this->setOptions(array(
            'template' => array(
                'namespace' => isset($options['template']['namespace']) ? $options['template']['namespace'] : '@phpManufaktur/Contact/Template',
                'settings' => isset($options['template']['settings']) ? $options['template']['settings'] : 'pattern/admin/simple/list.contact.json',
                'search' => isset($options['template']['search']) ? $options['template']['search'] : 'pattern/admin/simple/list.search.twig'
            ),
            'route' => array(
                'contact' => array(
                    'person' => isset($options['route']['contact']['person']) ? $options['route']['contact']['person'] : '/admin/contact/person/edit/id/{contact_id}',
                    'company' => isset($options['route']['contact']['company']) ? $options['route']['contact']['company'] : '/admin/contact/company/edit/id/{contact_id}',
                    'search' => isset($options['route']['contact']['search']) ? $options['route']['contact']['search'] : '/admin/contact/search'
                )
            )
        ));

        try {
            // search for the config file in the template directory
            $cfg_file = $this->app['utils']->getTemplateFile(self::$options['template']['namespace'], self::$options['template']['settings'], '', true);

            // get the columns to show in the list
            $cfg = $this->app['utils']->readJSON($cfg_file);

            self::$columns = isset($cfg['columns']) ? $cfg['columns'] : $this->ContactListControl->getColumns();
        } catch (\Exception $e) {
            // the config file does not exists - use all available columns
            self::$columns = $this->ContactListControl->getColumns();
        }

    }

    /**
     * Controller for the search list and dialog
     *
     * @param Application $app
     */
    public function controller(Application $app)
    {
        $this->app = $app;
        $this->initialize();
        return $this->exec();
    }

    /**
     * Execute the search
     *
     * @param string $extra
     */
    public function exec($extra = null)
    {
        if (null == ($search = $this->app['request']->get('search'))) {
            $this->setAlert('Please specify a search term!', array(), self::ALERT_TYPE_WARNING);
            $contacts = array();
        }
        else {
            $Overview = new Overview($this->app);
            if (false === ($contacts = $Overview->searchContact($search))) {
                $contacts = array();
                $this->setAlert('No hits for the search term <i>%search%</i>!', array('%search%' => $search));
            }
            else {
                $this->setAlert('%count% hits for the search term </i>%search%</i>.',
                    array('%count%' => count($contacts), '%search%' => $search), self::ALERT_TYPE_SUCCESS);
            }
        }

        return $this->app['twig']->render($this->app['utils']->getTemplateFile(self::$options['template']['namespace'],
            self::$options['template']['search']),
            array(
                'alert' => $this->getAlert(),
                'list' => $contacts,
                'columns' => self::$columns,
                'route' => self::$options['route'],
                'extra' => $extra,
                'usage' => isset($extra['usage']) ? $extra['usage'] : $this->app['request']->get('usage', 'framework')
            ));
    }
}
