<?php

/**
 * miniShop
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de/miniShop
 * @copyright 2014 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\miniShop\Control\Admin\Contact;

use Silex\Application;
use phpManufaktur\miniShop\Control\Admin\Admin;
use phpManufaktur\Contact\Control\Dialog\Simple\TagEdit as SimpleTagEdit;

class TagEdit extends Admin {

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
     * @see \phpManufaktur\miniShop\Control\Admin\Admin::initialize()
     */
    protected function initialize(Application $app)
    {
        parent::initialize($app);
        $this->SimpleTagEdit = new SimpleTagEdit($this->app, array(
            'template' => array(
                'namespace' => '@phpManufaktur/miniShop/Template',
                'edit' => 'admin/contact/edit.tag.twig'
            ),
            'route' => array(
                'action' => '/admin/minishop/contact/tag/edit?usage='.self::$usage,
                'list' => '/admin/minishop/contact/tag/list?usage='.self::$usage
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
     * Controller to Create and edit Contact Tags
     *
     * @param Application $app
     * @param string $tag_id
     */
    public function Controller(Application $app, $tag_id=null)
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
