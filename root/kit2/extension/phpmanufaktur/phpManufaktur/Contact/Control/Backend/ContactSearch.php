<?php

/**
 * Contact
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de/Contact
 * @copyright 2013 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\Contact\Control\Backend;

use Silex\Application;
use phpManufaktur\Contact\Control\Backend\Backend;
use phpManufaktur\Contact\Control\Dialog\Simple\Search as SimpleSearch;

class ContactSearch extends Backend {

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
     * @see \phpManufaktur\Contact\Control\Backend\Backend::initialize()
     */
    protected function initialize(Application $app)
    {
        parent::initialize($app);
        $options = array(
            'template' => array(
                'namespace' => '@phpManufaktur/Contact/Template',
                'settings' => 'admin/list.contact.json',
                'alert' => 'pattern/alert.twig',
                'search' => 'admin/list.search.twig'
            ),
            'route' => array(
                'contact' => array(
                    'person' => '/admin/contact/person/edit/id/{contact_id}?usage='.self::$usage,
                    'company' => '/admin/contact/company/edit/id/{contact_id}?usage='.self::$usage,
                    'search' => '/admin/contact/search?usage='.self::$usage
                )
            )
        );
        $this->SimpleSearch = new SimpleSearch($this->app, $options);
    }

    /**
     * Controller for the Search
     *
     * @param Application $app
     */
    public function controller(Application $app)
    {
        $this->initialize($app);
        $extra = array(
            'usage' => self::$usage,
            'toolbar' => $this->getToolbar('contact_list')
        );
        return $this->SimpleSearch->exec($extra);
    }

}
