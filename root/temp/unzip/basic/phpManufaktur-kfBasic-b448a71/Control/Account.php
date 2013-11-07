<?php

/**
 * kitFramework
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de
 * @copyright 2013 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\Basic\Control;

use Silex\Application;

class Account
{
    /**
     * Return the Account dialog
     */
    public function exec(Application $app)
    {
        return $app['twig']->render($app['utils']->getTemplateFile(
            '@phpManufaktur/Basic/Template',
            'framework/account.twig'),
            array());
    }

}
