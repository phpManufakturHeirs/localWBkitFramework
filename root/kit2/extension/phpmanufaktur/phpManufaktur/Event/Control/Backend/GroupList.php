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

use phpManufaktur\Event\Control\Backend\Backend;
use phpManufaktur\Event\Data\Event\Group as GroupData;
use Silex\Application;

class GroupList extends Backend {

    protected $GroupData = null;

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
        $this->GroupData = new GroupData($this->app);
    }
    
    public function exec(Application $app)
    {
        $this->initialize($app);
        $groups = $this->GroupData->selectAll();

        return $this->app['twig']->render($this->app['utils']->getTemplateFile('@phpManufaktur/Event/Template', 'backend/group.list.twig'),
            array(
                'usage' => self::$usage,
                'toolbar' => $this->getToolbar('group'),
                'message' => $this->getMessage(),
                'groups' => $groups
            ));
    }

}