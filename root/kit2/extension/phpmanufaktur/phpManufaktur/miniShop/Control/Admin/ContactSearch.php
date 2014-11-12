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
use phpManufaktur\Contact\Control\Dialog\Simple\Search as SimpleSearch;

class ContactSearch extends Admin {

    protected $SimpleSearch = null;

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
                'search' => 'admin/contact/list.search.twig'
            ),
            'route' => array(
                'contact' => array(
                    'person' => '/admin/minishop/contact/person/edit/id/{contact_id}?usage='.self::$usage,
                    'company' => '/admin/minishop/contact/company/edit/id/{contact_id}?usage='.self::$usage,
                    'search' => '/admin/minishop/contact/search?usage='.self::$usage
                )
            )
        );
        $this->SimpleSearch = new SimpleSearch($this->app, $options);
    }

    /**
     * Controller for the Contact Search
     *
     * @param Application $app
     */
    public function Controller(Application $app)
    {
        $this->initialize($app);
        $extra = array(
            'usage' => self::$usage,
            'toolbar' => $this->getToolbar('contact_list')
        );
        return $this->SimpleSearch->exec($extra);
    }

}
