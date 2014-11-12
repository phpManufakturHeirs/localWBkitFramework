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
use phpManufaktur\Contact\Control\Dialog\Simple\CategoryEdit as SimpleCategoryEdit;

class CategoryEdit extends Admin {

    protected $SimpleCategoryEdit = null;

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
        $this->SimpleCategoryEdit = new SimpleCategoryEdit($this->app, array(
            'template' => array(
                'namespace' => '@phpManufaktur/miniShop/Template',
                'edit' => 'admin/contact/edit.category.twig'
            ),
            'route' => array(
                'action' => '/admin/minishop/contact/category/edit?usage='.self::$usage,
                'extra' => '/admin/minishop/contact/extra/list?usage='.self::$usage,
                'list' => '/admin/minishop/contact/category/list?usage='.self::$usage
            )
        ));
    }

    /**
     * @param number $category_id
     */
    public function setCategoryID($category_id)
    {
        $this->SimpleCategoryEdit->setCategoryID($category_id);
    }

    /**
     * Controller to edit a Contact Category
     *
     * @param Application $app
     * @param integer $category_id
     */
    public function Controller(Application $app, $category_id=null)
    {
        $this->initialize($app);
        if (!is_null($category_id)) {
            $this->setCategoryID($category_id);
        }
        $extra = array(
            'usage' => self::$usage,
            'toolbar' => $this->getToolbar('contact_edit')
        );
        return $this->SimpleCategoryEdit->exec($extra);
    }

}
