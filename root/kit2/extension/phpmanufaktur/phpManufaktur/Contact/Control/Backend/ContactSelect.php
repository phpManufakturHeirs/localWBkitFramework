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
use phpManufaktur\Contact\Control\Dialog\Simple\ContactSelect as SimpleContactSelect;

class ContactSelect extends Backend {

    protected $SimpleContactSelect = null;

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
        $this->SimpleContactSelect = new SimpleContactSelect($this->app, array(
            'template' => array(
                'namespace' => '@phpManufaktur/Contact/Template',
                'message' => 'backend/message.twig',
                'select' => 'backend/admin/contact.select.twig'
            ),
            'route' => array(
                'action' => '/admin/contact/backend/select?usage='.self::$usage,
                'contact' => array(
                    'person' => array(
                        'create' => '/admin/contact/backend/person/edit?usage='.self::$usage,
                        'edit' => '/admin/contact/backend/person/edit/id/{contact_id}?usage='.self::$usage
                    ),
                    'company' => array(
                        'create' => '/admin/contact/backend/company/edit?usage='.self::$usage,
                        'edit' => '/admin/contact/backend/company/edit/id/{contact_id}?usage='.self::$usage
                    )
                )
            )
        ));
    }

    public function setContactID($contact_id)
    {
        $this->SimpleContactSelect->setContactID($contact_id);
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
        return $this->SimpleContactSelect->exec($extra);
    }

}
