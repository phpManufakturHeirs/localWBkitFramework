<?php

/**
 * kitFramework
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de
 * @copyright 2013 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\Basic\Control\Account\Dialog;

use phpManufaktur\Basic\Control\Pattern\Alert;
use Silex\Application;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class SwitchedUserRoles extends Alert
{
    protected static $usage = null;

    /**
     * (non-PHPdoc)
     * @see \phpManufaktur\Basic\Control\Pattern\Alert::initialize()
     */
    protected  function initialize(Application $app) {
        parent::initialize($app);
        self::$usage = $app['request']->get('usage', 'framework');
    }

    /**
     * The admin was switched to the user with the given ID and can now see
     * the real active account roles for this user.
     *
     * @param Application $app
     * @param unknown $id
     * @throws AccessDeniedException
     */
    public function ControllerSwitchedUserRoles(Application $app, $id)
    {
        $this->initialize($app);

        if (!$app['account']->isAuthenticated()) {
            throw new AccessDeniedException();
        }

        $roles = array();
        $roles_counted = 0;
        if ($id > 0) {
            // process roles
            foreach ($this->app['account']->getAvailableRoles() as $role) {
                $is_granted = $app['account']->isGranted($role);
                $roles[$role] = array(
                    'role' => $role,
                    'is_granted' => $is_granted
                );
                if ($is_granted) {
                    $roles_counted++;
                }
            }
        }
        else {
            $this->setAlert('Missing the user ID!', array(), self::ALERT_TYPE_WARNING);
        }

        if ($roles_counted == 0) {
            $this->setAlert('There are no roles assigned to this user.', array(), self::ALERT_TYPE_INFO);
        }
        else {
            $this->setAlert('This user are assigned %count% roles.', array('%count%' => $roles_counted), self::ALERT_TYPE_INFO);
        }

        return $app['twig']->render($app['utils']->getTemplateFile(
            '@phpManufaktur/Basic/Template', 'framework/accounts.switch.user.roles.twig'),
            array(
                'account_id' => $id,
                'usage' => self::$usage,
                'alert' => $this->getAlert(),
                'roles' => $roles,
                'roles_counted' => $roles_counted
            ));
    }

    /**
     * Switch back to the admin dialog to edit the user account
     *
     * @param Application $app
     * @param integer $id
     */
    public function ControllerSwitchedUserRolesExit(Application $app, $id)
    {
        $this->initialize($app);

        $subRequest = Request::create('/admin/accounts/edit/'.$id, 'GET',
            array('usage' => self::$usage));
        return $app->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
    }
}
