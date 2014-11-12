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

use Symfony\Component\Security\Core\Encoder\BasePasswordEncoder;
use phpManufaktur\Basic\Data\CMS\Users as cmsUsers;
use phpManufaktur\Basic\Data\Security\Users as frameworkUsers;
use Silex\Application;

/**
 * manufakturPasswordEncoder is based on the origin MessageDigestPasswordEncoder
 *
 * @author Fabien Potencier <fabien@symfony.com>
 * @author Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 */
class manufakturPasswordEncoder extends BasePasswordEncoder
{

    private $algorithm;
    private $encodeHashAsBase64;
    private $iterations;
    private $app;

    /**
     * Constructor.
     *
     * @param string $algorithm The digest algorithm to use
     * @param Boolean $encodeHashAsBase64 Whether to base64 encode the password hash
     * @param integer $iterations The number of iterations to use to stretch the password hash
     */
    public function __construct (Application $app, $algorithm = 'sha512', $encodeHashAsBase64 = true, $iterations = 5000)
    {
        $this->algorithm = $algorithm;
        $this->encodeHashAsBase64 = $encodeHashAsBase64;
        $this->iterations = $iterations;
        $this->app = $app;
    } // __construct()

    /**
     * Encode the password
     *
     * @see \Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface::encodePassword()
     */
    public function encodePassword ($raw, $salt)
    {
        if (! in_array($this->algorithm, hash_algos(), true)) {
            throw new \LogicException(sprintf('The algorithm "%s" is not supported.', $this->algorithm));
        }

        $salted = $this->mergePasswordAndSalt($raw, $salt);
        $digest = hash($this->algorithm, $salted, true);

        // "stretch" hash
        for ($i = 1; $i < $this->iterations; $i ++) {
            $digest = hash($this->algorithm, $digest . $salted, true);
        }

        return $this->encodeHashAsBase64 ? base64_encode($digest) : bin2hex($digest);
    } // encodePassword()

    /**
     * Check wether the password is valid or not.
     * Create a new user from CMS data if the check fail, the user not in the
     * framework user table but as CMS user.
     *
     * @see \Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface::isPasswordValid()
     */
    public function isPasswordValid ($encoded, $raw, $salt)
    {
        if ($this->comparePasswords($encoded, $this->encodePassword($raw, $salt)))
            return true;

        // check if a CMS user exists - the username is probably transported via $encoded
        $cmsUser = new cmsUsers($this->app);
        $isAdmin = false;
        if (false === ($user = $cmsUser->selectUser($encoded, $isAdmin)))
            return false;
        // the record exists but is the password correct?
        if (md5($raw) != $user['password'])
            return false;
        // ok - the user exists and the password is correct
        $this->app['monolog']->addDebug("encoded: $encoded - raw: $raw - ");

        $frameworkUsers = new frameworkUsers($this->app);
        $data = array(
            'username' => $user['username'],
            'email' => $user['email'],
            'displayname' => $this->app['utils']->unsanitizeText($user['display_name']),
            'password' => $this->encodePassword($raw, $salt),
            'roles' => $isAdmin ? 'ROLE_ADMIN' : 'ROLE_USER'
        );
        $frameworkUsers->insertUser($data);
        return true;
    } // isPasswordValid()

} // class manufakturPasswordEncoder
