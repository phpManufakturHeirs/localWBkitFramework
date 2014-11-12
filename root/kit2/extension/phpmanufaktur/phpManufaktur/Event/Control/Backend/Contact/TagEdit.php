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
use phpManufaktur\Contact\Control\Dialog\Simple\TagEdit as SimpleTagEdit;

class TagEdit extends Backend {

    protected $SimpleTagEdit = null;

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
        $this->SimpleTagEdit = new SimpleTagEdit($this->app, array(
            'template' => array(
                'namespace' => '@phpManufaktur/Event/Template',
                'edit' => 'admin/contact/edit.tag.twig'
            ),
            'route' => array(
                'action' => '/admin/event/contact/tag/edit?usage='.self::$usage,
                'list' => '/admin/event/contact/tag/list?usage='.self::$usage
            )
        ));
    }

    /**
     * @param number $tag_id
     */
    public function setTagID($tag_id)
    {
        $this->SimpleTagEdit->setTagID($tag_id);
    }

    public function exec(Application $app, $tag_id=null)
    {
        $this->initialize($app);
        if (!is_null($tag_id)) {
            $this->setTagID($tag_id);
        }
        $extra = array(
            'usage' => self::$usage,
            'toolbar' => $this->getToolbar('contact_edit')
        );
        return $this->SimpleTagEdit->exec($extra);
    }

}
