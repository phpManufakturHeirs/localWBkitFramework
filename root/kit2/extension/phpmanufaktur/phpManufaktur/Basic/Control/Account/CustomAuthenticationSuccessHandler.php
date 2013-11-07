<?php

/**
 * kitFramework::Basic
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://addons.phpmanufaktur.de/propangas24
 * @copyright 2013 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\Basic\Control\Account;

use Symfony\Component\Security\Http\Authentication\DefaultAuthenticationSuccessHandler;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\HttpUtils;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use phpManufaktur\Basic\Data\Security\Users;
use Silex\Application;

class CustomAuthenticationSuccessHandler extends DefaultAuthenticationSuccessHandler
{
    protected $app = null;

    /**
     * Constructor
     *
     * @param HttpUtils $httpUtils
     * @param array $options
     * @param unknown $database
     */
    public function __construct(HttpUtils $httpUtils, array $options, Application $app)
    {
        parent::__construct($httpUtils, $options);
        $this->app = $app;
    }

    /**
     * (non-PHPdoc)
     * @see \Symfony\Component\Security\Http\Authentication\DefaultAuthenticationSuccessHandler::onAuthenticationSuccess()
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token)
    {
        $user = $token->getUser();
        $data = array(
            'last_login' => date('Y-m-d H:i:s')
        );
        $Users = new Users($this->app);
        $Users->updateUser($user->getUsername(), $data);
        return $this->httpUtils->createRedirectResponse($request, $this->determineTargetUrl($request));
    }
}
