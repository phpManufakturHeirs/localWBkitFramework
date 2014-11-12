<?php

/**
 * Contact
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de/Contact
 * @copyright 2013 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\Contact\Control\Backend;

use Silex\Application;
use phpManufaktur\Contact\Control\Backend\Backend;
use phpManufaktur\Contact\Control\Dialog\Simple\TagEdit as SimpleTagEdit;

class TagEdit extends Backend {

    protected $SimpleTagEdit = null;

    /**
     * Constructor
     *
     * @param Application $app
     */
    public function __construct(Application $app=null)
    {
        parent::__construct($app);
        if (!is_null($app)) {
            $this->initialize($app);
        }
    }

    /**
     * (non-PHPdoc)
     * @see \phpManufaktur\Contact\Control\Backend\Backend::initialize()
     */
    protected function initialize(Application $app)
    {
        parent::initialize($app);
        $this->SimpleTagEdit = new SimpleTagEdit($this->app, array(
            'template' => array(
                'namespace' => '@phpManufaktur/Contact/Template',
                'alert' => 'pattern/alert.twig',
                'edit' => 'admin/edit.tag.twig'
            ),
            'route' => array(
                'action' => '/admin/contact/tag/edit?usage='.self::$usage,
                'list' => '/admin/contact/tag/list?usage='.self::$usage
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

    /**
     * Controller to edit a Contact tag
     *
     * @param Application $app
     * @param string $tag_id
     */
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
