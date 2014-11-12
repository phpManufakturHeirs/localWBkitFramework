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
use phpManufaktur\Contact\Control\Dialog\Simple\TagList as SimpleTagList;

class TagList extends Backend {

    protected $SimpleTagList = null;

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
        $this->SimpleTagList = new SimpleTagList($this->app, array(
            'template' => array(
                'namespace' => '@phpManufaktur/Event/Template',
                'list' => 'admin/contact/list.tag.twig'
            ),
            'route' => array(
                'edit' => '/admin/event/contact/tag/edit/id/{tag_id}?usage='.self::$usage,
                'create' => '/admin/event/contact/tag/edit?usage='.self::$usage
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
        return $this->SimpleTagList->exec($extra);
    }

}
