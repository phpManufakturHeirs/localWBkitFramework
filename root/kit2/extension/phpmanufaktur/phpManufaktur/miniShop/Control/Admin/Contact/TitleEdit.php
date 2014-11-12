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
use phpManufaktur\Contact\Control\Dialog\Simple\TitleEdit as SimpleTitleEdit;

class TitleEdit extends Admin {

    protected $SimpleTitleEdit = null;

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
        $this->SimpleTitleEdit = new SimpleTitleEdit($this->app, array(
            'template' => array(
                'namespace' => '@phpManufaktur/miniShop/Template',
                'edit' => 'admin/contact/edit.title.twig'
            ),
            'route' => array(
                'action' => '/admin/minishop/contact/title/edit?usage='.self::$usage,
                'list' => '/admin/minishop/contact/title/list?usage='.self::$usage
            )
        ));
    }

    /**
     * @param number $title_id
     */
    public function setTitleID($title_id)
    {
        $this->SimpleTitleEdit->setTitleID($title_id);
    }

    /**
     * Controller to create and edit person title fields
     *
     * @param Application $app
     * @param integer $title_id
     */
    public function Controller(Application $app, $title_id=null)
    {
        $this->initialize($app);
        if (!is_null($title_id)) {
            $this->setTitleID($title_id);
        }
        $extra = array(
            'usage' => self::$usage,
            'toolbar' => $this->getToolbar('contact_edit')
        );
        return $this->SimpleTitleEdit->exec($extra);
    }

}
