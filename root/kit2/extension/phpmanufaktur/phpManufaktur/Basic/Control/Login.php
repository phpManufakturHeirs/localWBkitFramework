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
use Symfony\Component\HttpFoundation\Request;

class Login
{
    public function exec(Application $app, Request $request)
    {
        return $app['twig']->render($app['utils']->getTemplateFile(
            '@phpManufaktur/Basic/Template',
            'framework/login.twig'), array(
            'error' => $app['security.last_error']($request),
            'last_username' => $app['session']->get('_security.last_username'),
        ));
    }
}
