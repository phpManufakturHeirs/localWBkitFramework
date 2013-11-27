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
use Symfony\Component\Security\Core\User\User as SymfonyUser;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use phpManufaktur\Basic\Data\Security\Users as FrameworkUser;
use phpManufaktur\Basic\Data\CMS\Users as CMSuser;

class Account
{
    protected $app = null;
    protected $FrameworkUser = null;
    protected $CMSuser = null;

    public function __construct(Application $app)
    {
        $this->app = $app;
        $this->FrameworkUser = new FrameworkUser($app);
        $this->CMSuser = new CMSuser($app);
    }

    /**
     * Check if the user is authenticated
     *
     * @return boolean
     */
    public function isAuthenticated()
    {
        $token = $this->app['security']->getToken();
        if (is_null($token)) {
            return false;
        }
        $user = $token->getUser();
        return ($user == 'anon.') ? false : true;
    }

    /**
     * Return the display name of the authenticated user or ANONYMOUS otherwise
     *
     * @return string|unknown
     */
    public function getDisplayName()
    {
        $token = $this->app['security']->getToken();
        if (is_null($token))
            return 'ANONYMOUS';

        $user = $token->getUser();

        if ($user == 'anon.') {
            return 'ANONYMOUS';
        }
        // get the user record
        if (false === ($user_data = $this->FrameworkUser->selectUser($user->getUsername()))) {
            // user not found!
            return 'ANONYMOUS';
        }
        $display_name = (isset($user_data['displayname']) && ! empty($user_data['displayname'])) ? $user_data['displayname'] : $user_data['username'];
        return $display_name;
    }

    /**
     * Return the USER name of the authenticated user or ANONYMOUS otherwise
     *
     * @return string
     */
    public function getUserName()
    {
        $token = $this->app['security']->getToken();
        if (is_null($token))
            return 'ANONYMOUS';

        $user = $token->getUser();

        return ($user == 'anon.') ? 'ANONYMOUS' : $user->getUsername();
    }

    /**
     * Return the user data record of the given user or false if the user does
     * not exists
     *
     * @param string $username
     * @return boolean|Ambigous <boolean, multitype:unknown >
     */
    public function getUserData($username)
    {
        return $this->FrameworkUser->selectUser($username);
    }

    /**
     * Check if the given CMS user has administrator privileges at the CMS
     *
     * @param string $username
     * @return boolean
     */
    public function checkUserIsCMSAdministrator($username)
    {
        $is_admin = false;
        return (!$this->CMSuser->selectUser($username, $is_admin) || !$is_admin) ? false : true;
    }

    /**
     * Check if the user has as account at the kitFramework
     *
     * @param string $username
     */
    public function checkUserHasFrameworkAccount($username)
    {
        return $this->FrameworkUser->existsUser($username);
    }

    /**
     * Login the user with the given roles into a secured area
     *
     * @param string $username
     * @param array $roles
     * @param string $area_name
     */
    public function loginUserToSecureArea($username, $roles, $secure_area_name='general')
    {
        $user = new SymfonyUser($username,'', $roles, true, true, true, true);
        $token = new UsernamePasswordToken($user, null, $secure_area_name, $user->getRoles());
        $this->app['security']->setToken($token);
        $this->app['session']->set('_security_'.$secure_area_name, serialize($token));
        // update account
        $data = array(
            'last_login' => date('Y-m-d H:i:s')
        );
        $this->FrameworkUser->updateUser($username, $data);
    }

    /**
     * Get the CMS account of the given user
     *
     * @param string $username
     * @return array|boolean
     */
    public function getUserCMSAccount($username)
    {
        return $this->CMSuser->selectUser($username);
    }

    /**
     * Create a kitFramework account
     *
     * @param string $username
     * @param string $email
     * @param string $password
     * @param array|string $roles
     * @param string $displayname
     */
    public function createAccount($username, $email, $password, $roles, $displayname='')
    {
        $data = array(
            'username' => $username,
            'email' => $email,
            'password' => $this->FrameworkUser->encodePassword($password),
            'displayname' => ($displayname != '') ? $displayname : $username,
            'roles' => $roles
        );
        $this->FrameworkUser->insertUser($data);
    }

    /**
     * Check if a role is granted for the user
     *
     * @param string $role
     */
    public function isGranted($role)
    {
        return $this->app['security']->isGranted($role);
    }

    /**
     * Encode the given password for usage with the user record
     *
     * @param string $password
     */
    public function encodePassword($password)
    {
        return $this->FrameworkUser->encodePassword($password);
    }

    /**
     * Update the user account data
     *
     * @param string $username
     * @param array $data
     */
    public function updateUserData($username, $data)
    {
        $this->FrameworkUser->updateUser($username, $data);
    }

    /**
     * Check the login for the given user and password, can also return the
     * associates roles of the user as an array
     *
     * @param string $username
     * @param string $password
     * @param array reference $roles
     * @return boolean
     */
    public function checkLogin($username, $password, &$roles=array())
    {
        return $this->FrameworkUser->checkLogin($username, $password, $roles);
    }

    /**
     * Create a new GUID for the user and return it
     *
     * @param string $username
     * @param boolean $guid_check ignore the GUID check
     * @return boolean|string FALSE if GUID was last changed within 24 hours
     */
    public function createGUID($username, $guid_check=true)
    {
        if (false === ($data = $this->FrameworkUser->createNewGUID($username, $guid_check))) {
            return false;
        }
        else {
            return $data['guid'];
        }
    }

    /**
     * Get the user data by the given GUID
     *
     * @param string $guid
     * @return boolean|array FALSE if not exists, array with user data on success
     */
    public function getUserByGUID($guid)
    {
        return $this->FrameworkUser->selectUserByGUID($guid);
    }

}
