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
use phpManufaktur\Contact\Control\Dialog\Simple\ExtraFieldEdit as SimpleExtraFieldEdit;

class ExtraEdit extends Admin {

    protected $SimpleExtraFieldEdit = null;

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
        $this->SimpleExtraFieldEdit = new SimpleExtraFieldEdit($this->app, array(
            'template' => array(
                'namespace' => '@phpManufaktur/miniShop/Template',
                'edit' => 'admin/contact/edit.extra.twig'
            ),
            'route' => array(
                'action' => '/admin/minishop/contact/extra/edit?usage='.self::$usage,
                'list' => '/admin/minishop/contact/extra/list?usage='.self::$usage
            )
        ));
    }

    /**
     * @param number $type_id
     */
    public function setTypeID($type_id)
    {
        $this->SimpleExtraFieldEdit->setTypeID($type_id);
    }

    /**
     * Controller to Create and Edit Extra Fields
     *
     * @param Application $app
     * @param integer $type_id
     */
    public function Controller(Application $app, $type_id=null)
    {
        $this->initialize($app);
        if (!is_null($type_id)) {
            $this->setTypeID($type_id);
        }
        $extra = array(
            'usage' => self::$usage,
            'toolbar' => $this->getToolbar('contact_edit')
        );
        return $this->SimpleExtraFieldEdit->exec($extra);
    }

}
