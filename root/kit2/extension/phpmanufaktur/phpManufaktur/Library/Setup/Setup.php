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

class Setup
{
    protected $app = null;

    /**
     * Create the latest directory for the Libraries
     *
     * @throws \Exception
     */
    public function CreateLatestDirectory(Application $app)
    {
        $json = $app['utils']->readJSON(MANUFAKTUR_PATH.'/Library/extension.json');
        if (!isset($json['library']['library'])) {
            throw new \Exception('Invalid extension.json for the Library!');
        }

        // build array for recursion
        $libraries = $json['library']['library'];
        $jquery = $json['library']['library']['jquery']['library'];
        unset($libraries['jquery']);
        $libraries = array_merge($libraries, $jquery);

        foreach ($libraries as $library) {
            if ($app['filesystem']->exists(FRAMEWORK_PATH.$library['path'].'/latest')) {
                $app['filesystem']->remove(FRAMEWORK_PATH.$library['path'].'/latest');
            }
            $release = $library['releases'][count($library['releases'])-1];
            $app['filesystem']->mirror(FRAMEWORK_PATH.$library['path'].'/'.$release, FRAMEWORK_PATH.$library['path'].'/latest');
            $app['monolog']->addDebug('Create /latest directory for '.$library['name']);
        }
    }

    /**
     * Controller to execute the setup process for the Library
     *
     * @param Application $app
     */
    public function ControllerSetup(Application $app)
    {
        $this->app = $app;

        $this->CreateLatestDirectory($app);

        return $app['translator']->trans('Successfull configured the kitFramework Library.');
    }
}
