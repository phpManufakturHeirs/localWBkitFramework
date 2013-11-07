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
use phpManufaktur\Event\Control\Backend\Backend;
use phpManufaktur\Contact\Control\Dialog\Simple\ContactCompany as SimpleContactCompany;

class ContactCompany extends Backend {

    protected $SimpleContactCompany = null;

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
        $this->SimpleContactCompany = new SimpleContactCompany($this->app, array(
            'template' => array(
                'namespace' => '@phpManufaktur/Event/Template',
                'message' => 'backend/message.twig',
                'contact' => 'backend/contact.company.twig'
            ),
            'route' => array(
                'action' => '/admin/event/contact/company/edit?usage='.self::$usage,
                'category' => '/admin/event/contact/category/list?usage='.self::$usage,
                'tag' => '/admin/event/contact/tag/list?usage='.self::$usage
            )
        ));
    }
    
    public function setContactID($contact_id)
    {
        $this->SimpleContactCompany->setContactID($contact_id);
    }

    public function exec(Application $app, $contact_id=null)
    {
        $this->initialize($app);
        if (!is_null($contact_id)) {
            $this->setContactID($contact_id);
        }
        $extra = array(
            'usage' => self::$usage,
            'toolbar' => $this->getToolbar('contact_edit')
        );
        return $this->SimpleContactCompany->exec($extra);
    }

}