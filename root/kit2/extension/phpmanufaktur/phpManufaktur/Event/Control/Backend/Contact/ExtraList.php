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
use phpManufaktur\Contact\Control\Dialog\Simple\ExtraFieldList as SimpleExtraFieldList;

class ExtraList extends Backend {

    protected $SimpleExtraFieldList = null;

    protected function initialize(Application $app)
    {
        parent::initialize($app);
        $this->SimpleExtraFieldList = new SimpleExtraFieldList($this->app, array(
            'template' => array(
                'namespace' => '@phpManufaktur/Event/Template',
                'list' => 'admin/contact/list.extra.twig'
            ),
            'route' => array(
                'edit' => '/admin/event/contact/extra/edit/id/{type_id}?usage='.self::$usage,
                'create' => '/admin/event/contact/extra/edit?usage='.self::$usage
            )
        ));
    }

    public function exec(Application $app)
    {
        $this->initialize($app);
        $extra = array(
            'usage' => self::$usage,
            'toolbar' => $this->getToolbar('contact_edit')
        );
        return $this->SimpleExtraFieldList->exec($extra);
    }

}
