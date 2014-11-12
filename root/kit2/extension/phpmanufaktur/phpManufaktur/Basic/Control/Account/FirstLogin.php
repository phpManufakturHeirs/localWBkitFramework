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
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use phpManufaktur\Basic\Control\Pattern\Alert;

class FirstLogin extends Alert
{
    protected static $user = null;
    protected static $redirect = null;
    protected static $usage = null;
    protected static $roles = null;
    protected static $auto_login = null;
    protected static $secured_area = null;

    /**
     * Create the login form
     *
     * @return FormBuilder form
     */
    protected function getLoginForm()
    {
        return $this->app['form.factory']->createBuilder('form')
        ->add('name', 'text', array(
            'label' => 'Username',
            'data' => self::$user['username']
        ))
        ->add('username', 'hidden', array(
            'data' => self::$user['username']
        ))
        ->add('password', 'password', array(
            'attr' => array('autofocus' => 'autofocus')
        ))
        ->add('email', 'hidden', array(
            'data' => self::$user['email']
        ))
        ->add('display_name', 'hidden', array(
            'data' => self::$user['display_name']
        ))
        ->add('usage', 'hidden', array(
            'data' => self::$usage
        ))
        ->add('redirect', 'hidden', array(
            'data' => self::$redirect
        ))
        ->add('auto_login', 'hidden', array(
            'data' => self::$auto_login
        ))
        ->add('roles', 'hidden', array(
            'data' => is_array(self::$roles) ? implode(',', self::$roles) : self::$roles
        ))
        ->add('secured_area', 'hidden', array(
            'data' => self::$secured_area
        ))
        ->getForm();
    }

    /**
     * Create a dialog for the first login from the CMS into the kitFramework
     *
     * @param Application $app
     * @throws \Exception
     */
    public function controllerCMSLogin(Application $app)
    {
        $this->initialize($app);

        // set the locale from the CMS locale
        $app['translator']->setLocale($app['session']->get('CMS_LOCALE', 'en'));

        if (null === (self::$redirect = $app['request']->request->get('redirect', null))) {
            throw new \Exception('Missing the POST parameter `redirect`');
        }
        if (null === (self::$usage = $app['request']->request->get('usage', null))) {
            throw new \Exception('Missing the POST parameter `usage`');
        }
        self::$auto_login = $app['request']->request->get('auto_login', false);
        self::$secured_area = $app['request']->request->get('secured_area', 'general');
        if (null === (self::$roles = $app['request']->request->get('roles', null))) {
            throw new \Exception('Missing the POST parameter `roles`');
        }
        if (null === ($username = $app['request']->request->get('username', null))) {
            throw new \Exception('Missing the POST parameter `username`');
        }
        if (false === (self::$user = $app['account']->getUserCMSAccount($username))) {
            throw new \Exception("The CMS user $username does not exists!");
        }

        $form = $this->getLoginForm();

        return $app['twig']->render($app['utils']->getTemplateFile(
            '@phpManufaktur/Basic/Template',
            'framework/first.login.twig'),
            array(
                'usage' => self::$usage,
                'form' => $form->createView(),
                'alert' => $this->getAlert()
            ));
    }

    /**
     * Check the login, create a kitFramework user, auto login the user and
     * redirect to the given route
     *
     * @param Application $app
     * @throws \Exception
     */
    public function controllerCheckCMSLogin(Application $app)
    {
        $this->initialize($app);

        $form = $this->getLoginForm($app);
        $form->bind($app['request']);

        if ($form->isValid()) {
            $check = $form->getData();
            // check if the password is identical with the CMS account
            if (false === ($cmsUserData = $app['account']->getUserCMSAccount($check['username']))) {
                // terrible wrong - user does not exists
                throw new \Exception("The user {$check['username']} does not exists.");
            }
            if ($check['name'] != $check['username']) {
                // user has changed the given login name!
                $this->setAlert("You must login as user '%username%'!",
                    array('%username%' => $check['username']), self::ALERT_TYPE_WARNING);
                return $app['twig']->render($app['utils']->getTemplateFile(
                    '@phpManufaktur/Basic/Template',
                    'framework/first.login.twig'),
                    array(
                        'usage' => $check['usage'],
                        'form' => $form->createView(),
                        'alert' => $this->getAlert()
                    ));
            }
            if (md5($check['password']) != $cmsUserData['password']) {
                // the password is not identical
                $this->setAlert('The password you typed in is not correct, please try again.',
                    array(), self::ALERT_TYPE_WARNING);
                return $app['twig']->render($app['utils']->getTemplateFile(
                    '@phpManufaktur/Basic/Template',
                    'framework/first.login.twig'),
                    array(
                        'usage' => $check['usage'],
                        'form' => $form->createView(),
                        'alert' => $this->getAlert()
                    ));
            }

            $roles = (strpos($check['roles'], ',')) ? explode(',', $check['roles']) : array($check['roles']);

            // create a kitFramework account
            $app['account']->createAccount(
                $check['username'],
                $check['email'],
                $check['password'],
                $roles,
                $check['display_name']
            );

            if ($check['auto_login']) {
                // auto login the CMS user into the secured area with admin privileges
                $app['account']->loginUserToSecureArea($check['username'], $roles);
            }

            // sub request to the redirect
            $subRequest = Request::create($check['redirect'], 'GET', array('usage' => $check['usage']));
            return $app->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
        }
        else {
            throw new \Exception("Ooops - the form is not valid, please try it again!");
        }
    }
}
