<?php

/**
 * Event
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://addons.phpmanufaktur.de/event
 * @copyright 2013 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\Event\Control\Backend\Contact;

use Silex\Application;
use phpManufaktur\Event\Control\Backend\Backend;
use phpManufaktur\Contact\Control\Dialog\Simple\TitleList as SimpleTitleList;

class TitleList extends Backend {

    protected $SimpleTitleList = null;

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
        $this->SimpleTitleList = new SimpleTitleList($this->app, array(
            'template' => array(
                'namespace' => '@phpManufaktur/Event/Template',
                'message' => 'backend/message.twig',
                'list' => 'backend/contact.title.list.twig'
            ),
            'route' => array(
                'edit' => '/admin/event/contact/title/edit/id/{title_id}?usage='.self::$usage
            )
        ));
    }
    
    public function exec(Application $app)
    {
        $this->initialize($app);
        $extra = array(
            'usage' => self::$usage,
            'toolbar' => $this->getToolbar('contact_edit')
        );
        return $this->SimpleTitleList->exec($extra);
    }

}