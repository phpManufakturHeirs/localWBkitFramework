<?php

/**
 * FacebookGallery
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de/FacebookGallery
 * @copyright 2013 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\FacebookGallery\Data\Setup;

use Silex\Application;

class Setup
{
    /**
     * Controller to execute the Setup for the FacebookGallery
     *
     * @param Application $app
     */
    public function ControllerSetup(Application $app)
    {
        return $app['translator']->trans('Successfull updated the extension %extension%.',
            array('%extension%' => 'FacebookGallery'));
    }
}
