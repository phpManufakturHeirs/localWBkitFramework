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
use phpManufaktur\Event\Control\Backend\Backend;
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
                'namespace' => '@phpManufaktur/Event/Template',
                'settings' => 'admin/contact/list.contact.json',
                'list' => 'admin/contact/list.contact.twig'
            ),
            'route' => array(
                'pagination' => '/admin/event/contact/list/page/{page}?order={order}&direction={direction}&usage='.self::$usage,
                'contact' => array(
                    'person' => '/admin/event/contact/person/edit/id/{contact_id}?usage='.self::$usage,
                    'company' => '/admin/event/contact/company/edit/id/{contact_id}?usage='.self::$usage,
                    'search' => '/admin/event/contact/search?usage='.self::$usage
                )
            )
        );
        $this->SimpleContactList = new SimpleContactList($this->app, $options);
    }

    public function setCurrentPage($page)
    {
        $this->SimpleContactList->setCurrentPage($page);
    }

    public function exec(Application $app, $page=null)
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
