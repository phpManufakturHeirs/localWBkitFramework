<?php

/**
 * Event
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de/Event
 * @copyright 2013 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\Event\Control\Backend;

use phpManufaktur\Event\Control\Backend\Backend;
use phpManufaktur\Event\Data\Event\ExtraType;
use Silex\Application;

class ExtraFieldList extends Backend {

    protected $ExtraType = null;

    protected function initialize(Application $app)
    {
        parent::initialize($app);
        $this->ExtraType = new ExtraType($this->app);
    }

    public function exec(Application $app)
    {
        $this->initialize($app);
        $fields = $this->ExtraType->selectAll();

        return $this->app['twig']->render($this->app['utils']->getTemplateFile(
            '@phpManufaktur/Event/Template', 'admin/list.extra.twig'),
            array(
                'usage' => self::$usage,
                'toolbar' => $this->getToolbar('group'),
                'alert' => $this->getAlert(),
                'fields' => $fields
            ));
    }

}
