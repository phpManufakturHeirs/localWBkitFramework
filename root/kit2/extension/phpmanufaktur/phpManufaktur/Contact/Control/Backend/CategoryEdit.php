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
use phpManufaktur\Contact\Control\Dialog\Simple\CategoryEdit as SimpleCategoryEdit;

class CategoryEdit extends Backend {

    protected $SimpleCategoryEdit = null;

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
        $this->SimpleCategoryEdit = new SimpleCategoryEdit($this->app, array(
            'template' => array(
                'namespace' => '@phpManufaktur/Contact/Template',
                'message' => 'backend/message.twig',
                'edit' => 'backend/admin/contact.category.edit.twig'
            ),
            'route' => array(
                'action' => '/admin/contact/backend/category/create?usage='.self::$usage,
                'extra' => '/admin/contact/backend/extra/list?usage='.self::$usage
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

    public function controller(Application $app, $category_id=null)
    {
        $this->initialize($app);
        if (!is_null($category_id)) {
            $this->setCategoryID($category_id);
        }
        $extra = array(
            'usage' => self::$usage,
            'toolbar' => $this->getToolbar('categories')
        );
        return $this->SimpleCategoryEdit->exec($extra);
    }

}
