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
use phpManufaktur\Basic\Control\kitCommand\Help;

class wysiwygContent
{

    /**
     * Controller for the kitCommand wysiwyg_content.
     * Return the content of the given WYSIWYG Section
     *
     * @param Application $app
     * @throws \InvalidArgumentException
     * @return string
     */
    public function Controller(Application $app)
    {
        $parameter = $app['request']->request->get('parameter');

        if (isset($parameter['section_id'])) {
            $cmsFunctions = new cmsFunctions($app);
            return $cmsFunctions->wysiwyg_content($parameter['section_id'], false);
        }

        // missing the parameter - show the helpfile
        $Help = new Help($app);
        return $Help->getContent(MANUFAKTUR_PATH.'/TemplateTools/command.wysiwyg_content.json');
    }
}
