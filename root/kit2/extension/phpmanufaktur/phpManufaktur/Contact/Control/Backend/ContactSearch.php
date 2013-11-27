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
use phpManufaktur\Contact\Control\Dialog\Simple\Search as SimpleSearch;

class ContactSearch extends Backend {

    protected $SimpleSearch = null;

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
        $options = array(
            'template' => array(
                'namespace' => '@phpManufaktur/Contact/Template',
                'settings' => 'backend/admin/contact.list.json',
                'message' => 'backend/message.twig',
                'search' => 'backend/admin/contact.search.twig'
            ),
            'route' => array(
                'contact' => array(
                    'person' => '/admin/contact/backend/person/edit/id/{contact_id}?usage='.self::$usage,
                    'company' => '/admin/contact/backend/company/edit/id/{contact_id}?usage='.self::$usage,
                    'search' => '/admin/contact/backend/search?usage='.self::$usage
                )
            )
        );
        $this->SimpleSearch = new SimpleSearch($this->app, $options);
    }

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
