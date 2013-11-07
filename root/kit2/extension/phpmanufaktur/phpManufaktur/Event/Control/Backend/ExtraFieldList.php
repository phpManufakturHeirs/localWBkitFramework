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
use phpManufaktur\Event\Data\Event\ExtraType;
use Silex\Application;

class ExtraFieldList extends Backend {

    protected $ExtraType = null;

    public function __construct(Application $app=null)
    {
        parent::__construct($app);        
    }

    protected function initialize(Application $app)
    {
        parent::initialize($app);
        $this->ExtraType = new ExtraType($this->app);
    }
    
    public function exec(Application $app)
    {
        $this->initialize($app);
        $fields = $this->ExtraType->selectAll();

        return $this->app['twig']->render($this->app['utils']->getTemplateFile('@phpManufaktur/Event/Template', 'backend/extra.field.list.twig'),
            array(
                'usage' => self::$usage,
                'toolbar' => $this->getToolbar('group'),
                'message' => $this->getMessage(),
                'fields' => $fields
            ));
    }

}