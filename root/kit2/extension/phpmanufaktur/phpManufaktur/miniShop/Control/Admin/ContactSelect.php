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
use phpManufaktur\miniShop\Control\Admin\Admin;
use phpManufaktur\Contact\Control\Dialog\Simple\ContactSelect as SimpleContactSelect;

class ContactSelect extends Admin {

    protected $SimpleContactSelect = null;

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
        $this->SimpleContactSelect = new SimpleContactSelect($this->app, array(
            'template' => array(
                'namespace' => '@phpManufaktur/miniShop/Template',
                'select' => 'admin/contact/select.contact.twig'
            ),
            'route' => array(
                'action' => '/admin/minishop/contact/select?usage='.self::$usage,
                'contact' => array(
                    'person' => array(
                        'create' => '/admin/minishop/contact/person/edit?usage='.self::$usage,
                        'edit' => '/admin/minishop/contact/person/edit/id/{contact_id}?usage='.self::$usage
                    ),
                    'company' => array(
                        'create' => '/admin/minishop/contact/company/edit?usage='.self::$usage,
                        'edit' => '/admin/minishop/contact/company/edit/id/{contact_id}?usage='.self::$usage
                    )
                )
            )
        ));
    }

    /**
     * Set the ID for the current contact
     *
     * @param integer $contact_id
     */
    public function setContactID($contact_id)
    {
        $this->SimpleContactSelect->setContactID($contact_id);
    }

    /**
     * Controller for the Contact Selection
     *
     * @param Application $app
     * @param integer $contact_id
     */
    public function Controller(Application $app, $contact_id=null)
    {
        $this->initialize($app);
        if (!is_null($contact_id)) {
            $this->setContactID($contact_id);
        }
        $extra = array(
            'usage' => self::$usage,
            'toolbar' => $this->getToolbar('contact_edit')
        );
        return $this->SimpleContactSelect->exec($extra);
    }

}
