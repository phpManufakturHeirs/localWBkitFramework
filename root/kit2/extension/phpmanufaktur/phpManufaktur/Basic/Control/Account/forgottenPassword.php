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

use Symfony\Component\Validator\Constraints as Assert;
use phpManufaktur\Basic\Data\Security\Users;
use Silex\Application;
use phpManufaktur\Basic\Control\Pattern\Alert;

/**
 * Display a dialog and enable the user to get a new password
 *
 * @author Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 *
 */
class forgottenPassword extends Alert
{
    protected static $usage = null;

    /**
     * Initialize the class
     *
     * @param Application $app
     */
    protected function initialize(Application $app) {
        parent::initialize($app);
        self::$usage = $app['request']->get('usage', 'framework');
    }

    /**
     * Display a dialog to enter the email address and order a new password
     *
     * @param string $message
     * @return string dialog
     */
    public function dialogForgottenPassword(Application $app)
    {
        $this->initialize($app);

        $form = $app['form.factory']->createBuilder('form')
        ->add('email', 'email')
        ->getForm();
        return $app['twig']->render($app['utils']->getTemplateFile(
            '@phpManufaktur/Basic/Template', 'framework/password.forgotten.twig'),
            array(
                'form' => $form->createView(),
                'alert' => $this->getAlert(),
                'usage' => self::$usage
        ));
    }

    /**
     * User entered a email address and get now a link to enter a new password
     *
     * @return string dialog
     */
    public function dialogResetPassword(Application $app)
    {
        $this->initialize($app);

        // get the form values
        $form = $app['request']->get('form');
        // validate the email
        $errors = $app['validator']->validateValue($form['email'], new Assert\Email());
        if (count($errors) > 0) {
            // invalid email submitted
            foreach ($errors as $error) {
                $this->setAlert($error->getMessage(), array(), self::ALERT_TYPE_WARNING);
            }
            return $this->dialogForgottenPassword($app);
        }
        $Users = new Users($app);
        if (false === ($user = $Users->selectUser($form['email']))) {
            $this->setAlert('There exists no user with the submitted email address.', array(),
                self::ALERT_TYPE_WARNING, array(__METHOD__, __LINE__));
            return $this->dialogForgottenPassword($app);
        }
        if ($user['status'] !== 'ACTIVE') {
            if ($user['last_login'] === '0000-00-00 00:00:00') {
                $this->setAlert('Your account is locked, but it seems that you have not activated your account. Please use the activation link you have received.',
                    array(), self::ALERT_TYPE_WARNING);
            }
            else {
                $this->setAlert('Your account is locked, please contact the webmaster.',
                    array(), self::ALERT_TYPE_WARNING);
            }
            return $this->dialogForgottenPassword($app);
        }
        // email address is valid, so we can create a new GUID and send a mail
        $guid_check = ($user['last_login'] !== '0000-00-00 00:00:00');
        if (false === ($guid = $Users->createNewGUID($form['email'], $guid_check))) {
            $this->setAlert("Can't create a new GUID as long the last GUID is not expired. You must wait 24 hours between the creation of new passwords.",
                array(), self::ALERT_TYPE_WARNING, array(__METHOD__, __LINE__));
            return $this->dialogForgottenPassword($app);
        }

        // create the email body
        $body = $app['twig']->render($app['utils']->getTemplateFile(
            '@phpManufaktur/Basic/Template', 'framework/mail/password.create.twig'),
            array('name' => $user['displayname'],
                'server' => FRAMEWORK_URL,
                'reset_password_url' => FRAMEWORK_URL.'/password/create/'.$guid['guid']
            ));
        // create the message
        $message = \Swift_Message::newInstance()
        ->setSubject($app['translator']->trans('kitFramework password reset'))
        ->setFrom(array(SERVER_EMAIL_ADDRESS))
        ->setTo(array($form['email']))
        ->setBody($body)
        ->setContentType('text/html');
        // send the message
        $app['mailer']->send($message);

        // show a response dialog
        return $app['twig']->render($app['utils']->getTemplateFile(
            '@phpManufaktur/Basic/Template', 'framework/password.create.twig'),
            array(
                'email' => $form['email'],
                'usage' => self::$usage,
                'alert' => $this->getAlert()
        ));
    }

    /**
     * Dialog to create a new password after verification of the submitted GUID
     *
     * @param string $guid
     * @return string dialog
     */
    public function dialogCreatePassword(Application $app, $guid)
    {
        $this->initialize($app);

        // first check the GUID
        $Users = new Users($app);

        if (false === ($user = $Users->selectUserByGUID($guid))) {
            // GUID does not exists
            $this->setAlert('Sorry, but the submitted GUID is invalid. Please contact the webmaster.',
                array(), self::ALERT_TYPE_WARNING);
            return $app['twig']->render($app['utils']->getTemplateFile(
                '@phpManufaktur/Basic/Template', 'framework/alert.twig'),
                array(
                    'title' => 'Create a new password',
                    'alert' => $this->getAlert(),
                    'usage' => self::$usage
                ));
        }
        if ($user['guid_status'] != 'ACTIVE') {
            // the GUID was already used
            $this->setAlert('The submitted GUID was already used and is no longer valid.<br />Please <a href="%password_forgotten%">order a new link</a>.',
                array('%password_forgotten%' => FRAMEWORK_URL.'/password/forgotten'), self::ALERT_TYPE_WARNING);
            return $app['twig']->render($app['utils']->getTemplateFile(
                '@phpManufaktur/Basic/Template', 'framework/alert.twig'),
                array(
                    'title' => 'Create a new password',
                    'alert' => $this->getAlert(),
                    'usage' => self::$usage
                ));
        }
        // check if the GUID is used within 24 hours
        $d = strtotime($user['guid_timestamp']);
        $limit = mktime(date('H', $d) + Users::getGuidWaitHoursBetweenResets(),
            date('i', $d), date('s', $d), date('m', $d), date('d', $d), date('Y', $d));
        if (time() > $limit) {
            // the GUID is expired
            $this->setAlert('The submitted GUID is expired and no longer valid.<br />Please <a href="%password_forgotten%">order a new link</a>.',
                array('%password_forgotten%' => FRAMEWORK_URL.'/password/forgotten'), self::ALERT_TYPE_WARNING);
            return $app['twig']->render($app['utils']->getTemplateFile(
                '@phpManufaktur/Basic/Template', 'framework/alert.twig'),
                array(
                    'title' => 'Create a new password',
                    'alert' => $this->getAlert(),
                    'usage' => self::$usage
                    )
                );
        }
        // ok - the GUID is valid, so lock the GUID and show the dialog to create a password
        $data = array(
            'guid_status' => 'LOCKED'
        );
        // update record
        $Users->updateUser($user['username'], $data);

        $form = $app['form.factory']->createBuilder('form')
        ->add('password', 'repeated', array(
            'type' => 'password',
            'required' => true,
            'first_options' => array('label' => 'Password'),
            'second_options' => array('label' => 'Repeat Password'),
            ))
        ->add('username', 'hidden', array(
            'data' => $user['username']
            ))
        ->getForm();

        $this->setAlert("Hello %name%, you want to change your password, so please type in a new one, repeat it and submit the form. If you won't change your password just leave this dialog.",
            array('%name%' => $user['displayname']), self::ALERT_TYPE_INFO);
        return $app['twig']->render($app['utils']->getTemplateFile(
            '@phpManufaktur/Basic/Template', 'framework/password.set.twig'),
            array(
                'form' => $form->createView(),
                'alert' => $this->getAlert(),
                'usage' => self::$usage
            ));
    }

    /**
     * Retype a new password
     *
     * @param Application $app
     */
    public function dialogRetypePassword(Application $app)
    {
        $this->initialize($app);
        // get the form values
        $form = $app['request']->get('form');

        if ($form['password']['first'] != $form['password']['second']) {
            // the passwords does not match
            $this->setAlert('The both passwords you have typed in does not match, please try again!',
                array(), self::ALERT_TYPE_WARNING);
        }
        elseif ($app['utils']->passwordStrength($form['password']['first']) < 3) {
            // the password is not strength enough
            $this->setAlert('The password you have typed in is not strength enough. Please choose a password at minimun 8 characters long, containing lower and uppercase characters, numbers and special chars. Spaces are not allowed.',
                array(), self::ALERT_TYPE_WARNING);
        }
        else {
            // change the password and prompt info
            $passwordEncoder = new manufakturPasswordEncoder($app);
            // we don't use "salt"
            $password = $passwordEncoder->encodePassword($form['password']['first'], '');

            // update the user data
            $Users = new Users($app);
            $Users->updateUser($form['username'], array('password' => $password));
            // return a info message and leave the dialog
            $this->setAlert('The password for the kitFramework was successfull changed. You can now <a href="%login%">login using the new password</a>.',
                array('%login%' => FRAMEWORK_URL.'/login'), self::ALERT_TYPE_SUCCESS);
            return $app['twig']->render($app['utils']->getTemplateFile(
                '@phpManufaktur/Basic/Template', 'framework/alert.twig'),
                array(
                    'title' => $this->app['translator']->trans('Password changed'),
                    'alert' => $this->getAlert(),
                    'usage' => self::$usage
                ));
        }

        // changing the password was not successfull, show again the dialog
        $form = $app['form.factory']->createBuilder('form')
        ->add('password', 'repeated', array(
            'type' => 'password',
            'required' => true,
            'first_options' => array('label' => 'Password'),
            'second_options' => array('label' => 'Repeat Password'),
            ))
        ->add('username', 'hidden', array(
            'data' => $form['username']
            ))
        ->getForm();

        return $app['twig']->render($app['utils']->getTemplateFile(
            '@phpManufaktur/Basic/Template', 'framework/password.set.twig'),
            array(
                'form' => $form->createView(),
                'alert' => $this->getAlert(),
                'usage' => self::$usage
            ));
    }

}
