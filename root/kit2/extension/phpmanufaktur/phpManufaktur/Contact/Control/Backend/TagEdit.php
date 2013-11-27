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
                'namespace' => '@phpManufaktur/Contact/Template',
                'message' => 'backend/message.twig',
                'edit' => 'backend/admin/contact.tag.edit.twig'
            ),
            'route' => array(
                'action' => '/admin/contact/backend/tag/edit?usage='.self::$usage
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

    public function controller(Application $app, $tag_id=null)
    {
        $this->initialize($app);
        if (!is_null($tag_id)) {
            $this->setTagID($tag_id);
        }
        $extra = array(
            'usage' => self::$usage,
            'toolbar' => $this->getToolbar('tags')
        );
        return $this->SimpleTagEdit->exec($extra);
    }

}
