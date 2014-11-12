<?php

/**
 * kitFramework
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de
 * @copyright 2013 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\Basic\Control\Account;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use phpManufaktur\Basic\Control\Pattern\Alert;

class Login extends Alert
{

    /**
     * Show the login dialog
     *
     * @param Application $app
     * @param Request $request
     */
    public function exec(Application $app, Request $request)
    {
        $this->initialize($app);

        if ('' != ($error = $app['security.last_error']($request))) {
            $this->setAlert($error, array(), self::ALERT_TYPE_WARNING);
        }

        return $app['twig']->render($app['utils']->getTemplateFile(
            '@phpManufaktur/Basic/Template', 'framework/login.twig'),
            array(
                'alert' => $this->getAlert(),
                'last_username' => $app['session']->get('_security.last_username'),
        ));
    }
}
