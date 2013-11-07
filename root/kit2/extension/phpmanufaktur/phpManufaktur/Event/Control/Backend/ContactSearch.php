<?php

/**
 * Event
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://addons.phpmanufaktur.de/event
 * @copyright 2013 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\Event\Control\Backend;

use Silex\Application;
use phpManufaktur\Event\Control\Backend\Backend;
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
                'namespace' => '@phpManufaktur/Event/Template',
                'settings' => 'backend/contact.list.json',
                'message' => 'backend/message.twig',
                'search' => 'backend/contact.search.twig'
            ),
            'route' => array(
                'contact' => array(
                    'person' => '/admin/event/contact/person/edit/id/{contact_id}?usage='.self::$usage,
                    'company' => '/admin/event/contact/company/edit/id/{contact_id}?usage='.self::$usage,
                    'search' => '/admin/event/contact/search?usage='.self::$usage
                )
            )
        );
        $this->SimpleSearch = new SimpleSearch($this->app, $options);
    }

    public function exec(Application $app)
    {
        $this->initialize($app);
        $extra = array(
            'usage' => self::$usage,
            'toolbar' => $this->getToolbar('contact_list')
        );
        return $this->SimpleSearch->exec($extra);
    }

}
