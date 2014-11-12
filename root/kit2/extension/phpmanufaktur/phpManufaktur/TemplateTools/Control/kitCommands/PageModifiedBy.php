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

class PageModifiedBy
{

    /**
     * Controller for the kitCommand page_modified_by.
     * Return the display name of the user who has last modified the given page
     *
     * @param Application $app
     * @throws \InvalidArgumentException
     * @return string
     */
    public function Controller(Application $app)
    {
        $params = $app['request']->request->all();

        $page_id = isset($params['parameter']['page_id']) ? $params['parameter']['page_id'] : $params['cms']['page_id'];

        if (isset($params['parameter']['locale'])) {
            $locale = $params['parameter']['locale'];
        }
        else {
            $locale = isset($params['cms']['locale']) ? $params['cms']['locale'] : 'en';
        }

        $cmsFunctions = new cmsFunctions($app);
        return $cmsFunctions->page_modified_by($page_id, $locale, false);
    }

}
