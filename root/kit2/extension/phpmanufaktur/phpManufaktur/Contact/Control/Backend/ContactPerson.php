<?php

/**
 * Contact
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://addons.phpmanufaktur.de/event
 * @copyright 2013 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\Contact\Control\Backend;

use Silex\Application;
use phpManufaktur\Contact\Control\Backend\Backend;
use phpManufaktur\Contact\Control\Dialog\Simple\ContactPerson as SimpleContactPerson;

class ContactPerson extends Backend {

    protected $SimpleContactPerson = null;

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
        $this->SimpleContactPerson = new SimpleContactPerson($this->app, array(
            'template' => array(
                'namespace' => '@phpManufaktur/Contact/Template',
                'message' => 'backend/message.twig',
                'contact' => 'backend/admin/contact.person.twig'
            ),
            'route' => array(
                'action' => '/admin/contact/backend/person/edit?usage='.self::$usage,
                'category' => '/admin/contact/backend/category/list?usage='.self::$usage,
                'title' => '/admin/contact/backend/title/list?usage='.self::$usage,
                'tag' => '/admin/contact/backend/tag/list?usage='.self::$usage
            )
        ));
    }

    public function setContactID($contact_id)
    {
        $this->SimpleContactPerson->setContactID($contact_id);
    }

    public function controller(Application $app, $contact_id=null)
    {
        $this->initialize($app);
        if (!is_null($contact_id)) {
            $this->setContactID($contact_id);
        }
        $extra = array(
            'usage' => self::$usage,
            'toolbar' => $this->getToolbar('contact_edit')
        );
        return $this->SimpleContactPerson->exec($extra);
    }

}
