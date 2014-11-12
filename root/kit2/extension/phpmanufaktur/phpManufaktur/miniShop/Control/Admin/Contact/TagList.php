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
use phpManufaktur\Contact\Control\Dialog\Simple\TagList as SimpleTagList;

class TagList extends Admin {

    protected $SimpleTagList = null;

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
        $this->SimpleTagList = new SimpleTagList($this->app, array(
            'template' => array(
                'namespace' => '@phpManufaktur/miniShop/Template',
                'list' => 'admin/contact/list.tag.twig'
            ),
            'route' => array(
                'edit' => '/admin/minishop/contact/tag/edit/id/{tag_id}?usage='.self::$usage,
                'create' => '/admin/minishop/contact/tag/edit?usage='.self::$usage
            )
        ));
    }

    /**
     * Controller for the Contact Tag List
     *
     * @param Application $app
     */
    public function Controller(Application $app)
    {
        $this->initialize($app);
        $extra = array(
            'usage' => self::$usage,
            'toolbar' => $this->getToolbar('contact_edit')
        );
        return $this->SimpleTagList->exec($extra);
    }

}
