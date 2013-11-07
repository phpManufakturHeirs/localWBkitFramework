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

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class EmbeddedAdministration
{
    protected $app = null;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * Called by the iframe embedded in the CMS. The encoded CMS information
     * will be read, provided as session (CMS_TYPE, CMS_VERSION, CMS_LOCALE and
     * CMS_USERNAME), detect the usage (framework or specified CMS) and execute
     * the specified route. The route get as parameter the usage.
     *
     * @param string $route_to
     * @param string $encoded_cms_information
     * @return Request
     * @link https://github.com/phpManufaktur/kitFramework/wiki/Extensions-%23-Embedded-Administration Embedded Administration
     */
    public function route($route_to, $encoded_cms_information)
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

        if (!$this->app['account']->checkUserIsCMSAdministrator($cms['username'])) {
            // the user is no CMS Administrator, deny access!
            return $this->app['twig']->render($this->app['utils']->getTemplateFile(
                '@phpManufaktur/Basic/Template',
                'framework/admins.only.twig'),
                array(
                    'usage' => $usage
            ));
        }

        if (!$this->app['account']->checkUserHasFrameworkAccount($cms['username'])) {
            // this user does not exists in the kitFramework User database
            $subRequest = Request::create('/login/first/cms', 'POST', array(
                'usage' => $usage,
                'username' => $cms['username'],
                'roles' => array('ROLE_ADMIN'),
                'auto_login' => true,
                'secured_area' => 'general',
                'redirect' => $route_to
            ));
            return $this->app->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
        }

        // auto login the CMS user into the secured area with admin privileges
        $this->app['account']->loginUserToSecureArea($cms['username'], array('ROLE_ADMIN'));

        // sub request to the starting point of Event
        $subRequest = Request::create($route_to, 'GET', array('usage' => $usage));
        return $this->app->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
    }

}
