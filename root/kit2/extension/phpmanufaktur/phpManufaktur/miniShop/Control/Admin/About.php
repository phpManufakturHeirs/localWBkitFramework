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

class About extends Admin {

    /**
     * Show the about dialog for the miniShop
     *
     * @return string rendered dialog
     */
    public function Controller(Application $app)
    {
        $this->initialize($app);

        $extension = $this->app['utils']->readJSON(MANUFAKTUR_PATH.'/miniShop/extension.json');

        return $this->app['twig']->render($this->app['utils']->getTemplateFile(
            '@phpManufaktur/miniShop/Template', 'admin/about.twig'),
            array(
                'usage' => self::$usage,
                'toolbar' => $this->getToolbar('about'),
                'extension' => $extension
            ));
    }

}
