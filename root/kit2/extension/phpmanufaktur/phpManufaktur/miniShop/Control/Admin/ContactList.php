<?php

/**
 * miniShop
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de/miniShop
 * @copyright 2014 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\miniShop\Control\Admin;

use Silex\Application;
use phpManufaktur\Contact\Control\Dialog\Simple\ContactList as SimpleContactList;

class ContactList extends Admin
{
    protected $SimpleContactList = null;

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
        $options = array(
            'template' => array(
                'namespace' => '@phpManufaktur/miniShop/Template',
                'settings' => 'admin/contact/list.contact.json',
                'list' => 'admin/contact/list.contact.twig'
            ),
            'route' => array(
                'pagination' => '/admin/minishop/contact/list/page/{page}?order={order}&direction={direction}&usage='.self::$usage,
                'contact' => array(
                    'person' => '/admin/minishop/contact/person/edit/id/{contact_id}?usage='.self::$usage,
                    'company' => '/admin/minishop/contact/company/edit/id/{contact_id}?usage='.self::$usage,
                    'search' => '/admin/minishop/contact/search?usage='.self::$usage
                )
            )
        );
        $this->SimpleContactList = new SimpleContactList($this->app, $options);
    }

    /**
     * Set the current page for the contact list
     *
     * @param integer $page
     */
    public function setCurrentPage($page)
    {
        $this->SimpleContactList->setCurrentPage($page);
    }

    /**
     * Controller for the ContactList
     *
     * @param Application $app
     * @param integer $page
     */
    public function Controller(Application $app, $page=null)
    {
        $this->initialize($app);
        if (!is_null($page)) {
            $this->setCurrentPage($page);
        }
        $extra = array(
            'usage' => self::$usage,
            'toolbar' => $this->getToolbar('contact_list')
        );
        return $this->SimpleContactList->exec($extra);
    }
}

