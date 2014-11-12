<?php

/**
 * Library
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de/Library
 * @copyright 2014 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\Library\Setup;

use Silex\Application;

class Update
{
    protected $app = null;

    /**
     * Controller to execute the update process for the Library
     *
     * @param Application $app
     */
    public function ControllerUpdate(Application $app)
    {
        $this->app = $app;

        $Setup = new Setup();
        $Setup->CreateLatestDirectory($app);

        return $app['translator']->trans('Successfull configured the kitFramework Library.');
    }
}
