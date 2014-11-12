<?php

/**
 * Library
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de/Library
 * @copyright 2014 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\Library\Control\Command;

use Silex\Application;
use phpManufaktur\Basic\Control\kitCommand\Basic;

class LibraryInfo extends Basic
{
    /**
     * Initialize the iFrame for the LibraryInfo
     *
     * @param Application $app
     */
    public function ControllerLibraryFrame(Application $app)
    {
        $this->initParameters($app);
        return $this->createIFrame('/library/info');
    }

    /**
     * Controller to execute the LibraryInfo kitCommand
     *
     * @param Application $app
     */
    public function ControllerLibraryInfo(Application $app)
    {
        $this->initParameters($app);

        $extension = $this->app['utils']->readJSON(MANUFAKTUR_PATH.'/Library/extension.json');

        return $this->app['twig']->render($this->app['utils']->getTemplateFile(
            '@phpManufaktur/Library/Template', 'library.info.twig',
            $this->getPreferredTemplateStyle()),
            array(
                'basic' => $this->getBasicSettings(),
                'extension' => $extension
            ));
    }
}
