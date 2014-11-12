<?php

/**
 * kitFramework
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de
 * @copyright 2014 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\Basic\Control\Account;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use phpManufaktur\Basic\Control\Pattern\Alert;

class RoleEntryPoints extends Alert
{

    /**
     * Controller to create a dialog with all possible entry points for the user
     *
     * @param Application $app
     */
    public function ControllerRoleEntryPoints(Application $app)
    {
        $this->initialize($app);

        if (!$app['account']->isAuthenticated()) {
            // user must login first!
            $subRequest = Request::create('/login');
            return $app->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
        }

        // get all entry points for this user
        $entry_points = $app['account']->getUserRolesEntryPoints();
        $count = count($entry_points, COUNT_RECURSIVE);

        if ($count < 1) {
            // the user is not allowed to access any entry point!
            if ($app['account']->isGranted('ROLE_USER')) {
                // ... but is allowed to access his account, so switch to account
                $subRequest = Request::create('/user/account');
                return $app->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
            }
            $this->setAlert('Sorry, but you are not allowed to access any entry point!', array(), self::ALERT_TYPE_WARNING);
        }
        elseif ($count < 14) {
            // reset() returns the first entry of the array
            $point = reset($entry_points);
            $subRequest = Request::create($point[0]['route'], 'GET',
                array('usage' => $this->app['request']->get('usage', 'framework')));
            return $app->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
        }
        else {
            // greetings to the user ...
            $this->setAlert('Welcome back, %user%! Please select the entry point you want to use.',
                array('%user%' => $app['account']->getDisplayName(), self::ALERT_TYPE_SUCCESS));
        }

        return $this->app['twig']->render($this->app['utils']->getTemplateFile(
            '@phpManufaktur/Basic/Template',
            'framework/account.entry.points.twig'),
            array(
                'usage' => $this->app['request']->get('usage', 'framework'),
                'alert' => $this->getAlert(),
                'entry_points' => $entry_points
            ));
    }
}
