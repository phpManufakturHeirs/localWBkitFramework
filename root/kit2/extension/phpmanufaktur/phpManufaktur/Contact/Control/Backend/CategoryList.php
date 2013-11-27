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
use phpManufaktur\Contact\Control\Dialog\Simple\CategoryList as SimpleCategoryList;

class CategoryList extends Backend {

    protected $SimpleCategoryList = null;

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
        $this->SimpleCategoryList = new SimpleCategoryList($this->app, array(
            'template' => array(
                'namespace' => '@phpManufaktur/Contact/Template',
                'message' => 'backend/message.twig',
                'list' => 'backend/admin/contact.category.list.twig'
            ),
            'route' => array(
                'create' => '/admin/contact/backend/category/create?usage='.self::$usage,
                'edit' => '/admin/contact/backend/category/edit/id/{category_id}?usage='.self::$usage
            )
        ));
    }

    public function controller(Application $app)
    {
        $this->initialize($app);
        $extra = array(
            'usage' => self::$usage,
            'toolbar' => $this->getToolbar('categories')
        );
        return $this->SimpleCategoryList->exec($extra);
    }

}
