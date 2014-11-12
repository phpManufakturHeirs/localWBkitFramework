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
use phpManufaktur\Contact\Control\Dialog\Simple\ExtraFieldList as SimpleExtraFieldList;

class ExtraList extends Admin {

    protected $SimpleExtraFieldList = null;

    /**
     * (non-PHPdoc)
     * @see \phpManufaktur\miniShop\Control\Admin\Admin::initialize()
     */
    protected function initialize(Application $app)
    {
        parent::initialize($app);
        $this->SimpleExtraFieldList = new SimpleExtraFieldList($this->app, array(
            'template' => array(
                'namespace' => '@phpManufaktur/miniShop/Template',
                'list' => 'admin/contact/list.extra.twig'
            ),
            'route' => array(
                'edit' => '/admin/minishop/contact/extra/edit/id/{type_id}?usage='.self::$usage,
                'create' => '/admin/minishop/contact/extra/edit?usage='.self::$usage
            )
        ));
    }

    /**
     * Controller for the list with Contact Extra Fields
     * @param Application $app
     */
    public function Controller(Application $app)
    {
        $this->initialize($app);
        $extra = array(
            'usage' => self::$usage,
            'toolbar' => $this->getToolbar('contact_edit')
        );
        return $this->SimpleExtraFieldList->exec($extra);
    }

}
