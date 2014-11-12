<?php

/**
 * Event
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de/Contact
 * @copyright 2013 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\Contact\Control\Backend;

use Silex\Application;
use phpManufaktur\Contact\Control\Backend\Backend;
use phpManufaktur\Contact\Control\Dialog\Simple\ContactList as SimpleContactList;

class ContactList extends Backend {

    protected $SimpleContactList = null;

    /**
     * Constructor
     *
     * @param Application $app
     */
    public function __construct(Application $app=null)
    {
        parent::__construct($app);
        if (!is_null($app)) {
            $this->initialize($app);
        }
    }

    /**
     * (non-PHPdoc)
     * @see \phpManufaktur\Contact\Control\Backend\Backend::initialize()
     */
    protected function initialize(Application $app)
    {
        parent::initialize($app);
        $options = array(
            'template' => array(
                'namespace' => '@phpManufaktur/Contact/Template',
                'settings' => 'admin/list.contact.json',
                'alert' => 'pattern/alert.twig',
                'list' => 'admin/list.contact.twig'
            ),
            'route' => array(
                'pagination' => '/admin/contact/list/page/{page}?order={order}&direction={direction}&usage='.self::$usage,
                'contact' => array(
                    'person' => '/admin/contact/person/edit/id/{contact_id}?usage='.self::$usage,
                    'company' => '/admin/contact/company/edit/id/{contact_id}?usage='.self::$usage,
                    'search' => '/admin/contact/search?usage='.self::$usage
                )
            )
        );
        $this->SimpleContactList = new SimpleContactList($this->app, $options);
    }

    /**
     * Set the current page of the table
     *
     * @param integer $page
     */
    public function setCurrentPage($page)
    {
        $this->SimpleContactList->setCurrentPage($page);
    }

    /**
     * Controller for the Contact List
     *
     * @param Application $app
     * @param integer $page
     */
    public function controller(Application $app, $page=null)
    {
        $this->initialize($app);
        if (!is_null($page)) {
            $this->setCurrentPage($page);
        }
        $extra = array(
            'usage' => self::$usage,
            'toolbar' => $this->getToolbar('contact_list')
        );
        return $this->SimpleContactList->exec($extra);
    }

}
