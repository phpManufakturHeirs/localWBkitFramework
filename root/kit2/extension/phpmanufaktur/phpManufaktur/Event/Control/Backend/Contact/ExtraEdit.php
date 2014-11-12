<?php

/**
 * Event
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de/Event
 * @copyright 2013 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\Event\Control\Backend\Contact;

use Silex\Application;
use phpManufaktur\Event\Control\Backend\Backend;
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
                'namespace' => '@phpManufaktur/Event/Template',
                'edit' => 'admin/contact/edit.extra.twig'
            ),
            'route' => array(
                'action' => '/admin/event/contact/extra/edit?usage='.self::$usage,
                'list' => '/admin/event/contact/extra/list?usage='.self::$usage
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

    public function exec(Application $app, $type_id=null)
    {
        $this->initialize($app);
        if (!is_null($type_id)) {
            $this->setTypeID($type_id);
        }
        $extra = array(
            'usage' => self::$usage,
            'toolbar' => $this->getToolbar('contact_edit')
        );
        return $this->SimpleExtraFieldEdit->exec($extra);
    }

}
