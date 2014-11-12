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

class Update
{
    /**
     * Controller to execute the Update for the FacebookGallery
     *
     * @param Application $app
     */
    public function ControllerUpdate(Application $app)
    {
        $remove = array(
            MANUFAKTUR_PATH.'/FacebookGallery/Template/default/error.twig',
            MANUFAKTUR_PATH.'/FacebookGallery/Template/default/help.twig',
            MANUFAKTUR_PATH.'/FacebookGallery/Template/fullpage'
        );

        $app['filesystem']->remove($remove);

        return $app['translator']->trans('Successfull updated the extension %extension%.',
            array('%extension%' => 'FacebookGallery'));
    }
}
