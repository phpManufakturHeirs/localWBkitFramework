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
                'namespace' => '@phpManufaktur/Contact/Template',
                'message' => 'backend/message.twig',
                'list' => 'backend/admin/contact.tag.list.twig'
            ),
            'route' => array(
                'create' => '/admin/contact/backend/tag/edit?usage='.self::$usage,
                'edit' => '/admin/contact/backend/tag/edit/id/{tag_id}?usage='.self::$usage
            )
        ));
    }

    public function controller(Application $app)
    {
        $this->initialize($app);
        $extra = array(
            'usage' => self::$usage,
            'toolbar' => $this->getToolbar('tags')
        );
        return $this->SimpleTagList->exec($extra);
    }

}
