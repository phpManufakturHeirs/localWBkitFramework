<?php

/**
 * Event
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://addons.phpmanufaktur.de/event
 * @copyright 2013 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\Contact\Control\Backend;

use Silex\Application;
use phpManufaktur\Contact\Control\Backend\Backend;
use phpManufaktur\Contact\Control\Dialog\Simple\ContactList as SimpleContactList;

class ContactList extends Backend {

    protected $SimpleContactList = null;

    public function __construct(Application $app=null)
    {
        parent::__construct($app);
        if (!is_null($app)) {
            $this->initialize($app);
        }
    }

    protected function initialize(Application $app)
    {
        parent::initialize($app);
        $options = array(
            'template' => array(
                'namespace' => '@phpManufaktur/Contact/Template',
                'settings' => 'backend/admin/contact.list.json',
                'message' => 'backend/message.twig',
                'list' => 'backend/admin/contact.list.twig'
            ),
            'route' => array(
                'pagination' => '/admin/contact/backend/list/page/{page}?order={order}&direction={direction}&usage='.self::$usage,
                'contact' => array(
                    'person' => '/admin/contact/backend/person/edit/id/{contact_id}?usage='.self::$usage,
                    'company' => '/admin/contact/backend/company/edit/id/{contact_id}?usage='.self::$usage,
                    'search' => '/admin/contact/backend/search?usage='.self::$usage
                )
            )
        );
        $this->SimpleContactList = new SimpleContactList($this->app, $options);
    }

    public function setCurrentPage($page)
    {
        $this->SimpleContactList->setCurrentPage($page);
    }

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
