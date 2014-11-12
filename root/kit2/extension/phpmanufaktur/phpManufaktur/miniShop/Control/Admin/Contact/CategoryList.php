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
use phpManufaktur\Contact\Control\Dialog\Simple\CategoryList as SimpleCategoryList;

class CategoryList extends Admin {

    protected $SimpleCategoryList = null;

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
        $this->SimpleCategoryList = new SimpleCategoryList($this->app, array(
            'template' => array(
                'namespace' => '@phpManufaktur/miniShop/Template',
                'list' => 'admin/contact/list.category.twig'
            ),
            'route' => array(
                'edit' => '/admin/minishop/contact/category/edit/id/{category_id}?usage='.self::$usage,
                'create' => '/admin/minishop/contact/category/edit?usage='.self::$usage
            )
        ));
    }

    /**
     * Controller for the Contact Category List
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
        return $this->SimpleCategoryList->exec($extra);
    }

}
