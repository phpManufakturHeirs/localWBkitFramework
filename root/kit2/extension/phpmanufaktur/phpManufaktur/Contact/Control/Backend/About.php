<?php

/**
 * Event
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://addons.phpmanufaktur.de/event
 * @copyright 2013 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\Contact\Control\Backend;

use phpManufaktur\Contact\Control\Backend\Backend;
use Silex\Application;

class About extends Backend {

    /**
     * Show the about dialog for Event
     *
     * @return string rendered dialog
     */
    public function exec(Application $app)
    {
        $this->initialize($app);

        $extension = $this->app['utils']->readJSON(MANUFAKTUR_PATH.'/Contact/extension.json');

        return $this->app['twig']->render($this->app['utils']->getTemplateFile('@phpManufaktur/Contact/Template', 'backend/admin/about.twig'),
            array(
                'usage' => self::$usage,
                'toolbar' => $this->getToolbar('about'),
                'extension' => $extension
            ));
    }

}
