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
use phpManufaktur\Contact\Control\Dialog\Simple\ExtraFieldEdit as SimpleExtraFieldEdit;

class ExtraEdit extends Backend {

    protected $SimpleExtraFieldEdit = null;

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
        $this->SimpleExtraFieldEdit = new SimpleExtraFieldEdit($this->app, array(
            'template' => array(
                'namespace' => '@phpManufaktur/Contact/Template',
                'message' => 'backend/message.twig',
                'edit' => 'backend/admin/contact.extra.edit.twig'
            ),
            'route' => array(
                'action' => '/admin/contact/backend/extra/create?usage='.self::$usage
            )
        ));
    }

    /**
     * @param number $type_id
     */
    public function setTypeID($type_id)
    {
        $this->SimpleExtraFieldEdit->setTypeID($type_id);
    }

    public function controller(Application $app, $type_id=null)
    {
        $this->initialize($app);
        if (!is_null($type_id)) {
            $this->setTypeID($type_id);
        }
        $extra = array(
            'usage' => self::$usage,
            'toolbar' => $this->getToolbar('extra_fields')
        );
        return $this->SimpleExtraFieldEdit->exec($extra);
    }

}
