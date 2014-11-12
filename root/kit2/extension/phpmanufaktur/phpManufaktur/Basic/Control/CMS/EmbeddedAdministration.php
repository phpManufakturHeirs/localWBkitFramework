<?php

/**
 * kitFramework::Basic
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de
 * @copyright 2013 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\Basic\Control\CMS;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use phpManufaktur\Basic\Control\Pattern\Alert;

class EmbeddedAdministration extends Alert
{
    /*
    protected $app = null;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }
*/
    /**
     * Called by the iframe embedded in the CMS. The encoded CMS information
     * will be read, provided as session (CMS_TYPE, CMS_VERSION, CMS_LOCALE and
     * CMS_USERNAME), detect the usage (framework or specified CMS) and execute
     * the specified route. The route get as parameter the usage.
     *
     * @param string $route_to
     * @param string $encoded_cms_information
     * @param $string $granted_role default = 'ROLE_ADMIN', the role which is needed at minimum
     * @return Request
     * @link https://github.com/phpManufaktur/kitFramework/wiki/Extensions-%23-Embedded-Administration Embedded Administration
     */
    public function route($route_to, $encoded_cms_information, $granted_role='ROLE_ADMIN')
    {
        if (false === ($decoded_information = base64_decode($encoded_cms_information))) {
            throw new \Exception("Can't decode the CMS Base64 information parameter!");
        }
        if (false === ($cms = json_decode($decoded_information, true))) {
            throw new \Exception("JSON decoding error!");
        }

        if (!isset($cms['locale']) || !isset($cms['username'])) {
            throw new \Exception("CMS information is incomplete, at minimum needed are locale and username!");
        }

        // save them partial into session
        $this->app['session']->set('CMS_LOCALE', $cms['locale']);
        $this->app['session']->set('CMS_USERNAME', $cms['username']);

        $usage = ($cms['target'] == 'cms') ? CMS_TYPE : 'framework';

        // is the user a CMS Admin?
        $is_admin = $this->app['account']->checkUserIsCMSAdministrator($cms['username']);

        if (($granted_role == 'ROLE_ADMIN') && !$is_admin) {
            // the user is no CMS Administrator, deny access!
            $this->setAlert('Sorry, but only Administrators are allowed to access this kitFramework extension.',
                array(), self::ALERT_TYPE_WARNING);
            return $this->app['twig']->render($this->app['utils']->getTemplateFile(
                '@phpManufaktur/Basic/Template', 'framework/alert.twig'),
                array(
                    'usage' => $usage,
                    'title' => 'Access denied',
                    'alert' => $this->getAlert()
            ));
        }

        if (!$this->app['account']->checkUserHasFrameworkAccount($cms['username'])) {
            // this user does not exists in the kitFramework User database
            $subRequest = Request::create('/login/first/cms', 'POST', array(
                'usage' => $usage,
                'username' => $cms['username'],
                'roles' => $is_admin ? array('ROLE_ADMIN') : array($granted_role),
                'auto_login' => true,
                'secured_area' => 'general',
                'redirect' => $route_to
            ));
            return $this->app->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
        }

        // get the userdata
        $user = $this->app['account']->getUserData($cms['username']);
        $user_roles = (strpos($user['roles'], ',')) ? explode(',', $user['roles']) : array(trim($user['roles']));
        $this->app['account']->loginUserToSecureArea($cms['username'], $user_roles);

        if (!$this->app['account']->isGranted($granted_role)) {
            // user is not granted for the given role
            $this->setAlert('You are not allowed to access this resource!', array(), self::ALERT_TYPE_WARNING);
            return $this->app['twig']->render($this->app['utils']->getTemplateFile(
                '@phpManufaktur/Basic/Template',
                'framework/alert.twig'),
                array(
                    'usage' => $usage,
                    'title' => 'Insufficient user role',
                    'alert' => $this->getAlert()
                ));
        }

        // sub request to the starting point
        $subRequest = Request::create($route_to, 'GET', array('usage' => $usage));
        return $this->app->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
    }

}
