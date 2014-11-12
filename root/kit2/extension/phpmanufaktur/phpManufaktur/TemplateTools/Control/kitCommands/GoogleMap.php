<?php

/**
 * TemplateTools
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de/TemplateTools
 * @copyright 2014 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\TemplateTools\Control\kitCommands;

use Silex\Application;
use phpManufaktur\Basic\Control\kitCommand\Help;

class GoogleMap
{

    /**
     * Controller for the kitCommand google_map.
     * Return a formatted a Google Map for the desired location
     *
     * @param Application $app
     * @throws \InvalidArgumentException
     * @return string
     */
    public function Controller(Application $app)
    {
        $params = $app['request']->request->all();
        $parameter = $params['parameter'];

        if (!isset($parameter['locale']) || empty($parameter['locale'])) {
            $parameter['locale'] = $params['cms']['locale'];
        }

        if ((!isset($parameter['street']) || empty($parameter['street'])) &&
            (!isset($parameter['zip']) || empty($parameter['zip'])) &&
            (!isset($parameter['city']) || empty($parameter['city']))) {
            // no location defined - show the help instead!
            $Help = new Help($app);
            return $Help->getContent(MANUFAKTUR_PATH.'/TemplateTools/command.google_map.json');
        }

        return $app['twig']->render(
            // show the Google Map
            '@phpManufaktur/TemplateTools/Pattern/classic/function/googlemap/google.map.twig',
            array('parameter' => $parameter));
    }

}
