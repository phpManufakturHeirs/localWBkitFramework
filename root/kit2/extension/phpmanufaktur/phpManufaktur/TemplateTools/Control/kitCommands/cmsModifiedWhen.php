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
use phpManufaktur\TemplateTools\Control\cmsFunctions;

class cmsModifiedWhen
{

    /**
     * Controller for the kitCommand cms_modified_when. Return a formatted
     * date/time string with the last modification of the CMS.
     *
     * @param Application $app
     * @throws \InvalidArgumentException
     * @return string
     */
    public function Controller(Application $app)
    {
        $params = $app['request']->request->all();

        $format = isset($params['parameter']['format']) ? $params['parameter']['format'] : 'DATETIME_FORMAT';
        if (isset($params['parameter']['locale'])) {
            $locale = $params['parameter']['locale'];
        }
        else {
            $locale = isset($params['cms']['locale']) ? $params['cms']['locale'] : 'en';
        }

        $cmsFunctions = new cmsFunctions($app);
        return $cmsFunctions->cms_modified_when($format, $locale, false);
    }
}
