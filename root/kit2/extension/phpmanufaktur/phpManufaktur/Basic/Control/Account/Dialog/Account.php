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

use Silex\Application;
use phpManufaktur\Basic\Control\Pattern\Alert;
use Symfony\Component\Form\Form;

class Account extends Alert
{
    protected static $usage = null;
    protected static $user_id = null;
    protected static $user_name = null;
    protected static $user_data = null;

    /**
     * Initialize the class
     *
     * @param Application $app
     */
    protected function initialize(Application $app) {
        parent::initialize($app);
        self::$usage = $app['request']->get('usage', 'framework');

        if (!$app['account']->isAuthenticated()) {
            throw new \Exception('Illegal access - user is not authenticated!');
        }

        self::$user_name = $app['account']->getUserName();

        if (false === (self::$user_data = $app['account']->getUserData(self::$user_name))) {
            throw new \Exception('Got no data for user '.self::$user_name.'!');
        }

        self::$user_id = self::$user_data['id'];
    }

    /**
     * Create the form with form factory
     *
     * @param array $data
     */
    protected function getAccountForm($data = array())
    {
        $form = $this->app['form.factory']->createBuilder('form')
        ->add('id', 'hidden', array(
            'data' => isset($data['id']) ? $data['id'] : -1
        ))
        ->add('username', 'text', array(
            'data' => isset($data['username']) ? $data['username'] : '',
            'read_only' => !$this->app['account']->isGranted('ROLE_ADMIN')
        ))
        ->add('email', 'email', array(
            'data' => isset($data['email']) ? $data['email'] : ''
        ))
        ->add('displayname', 'text', array(
            'data' => isset($data['displayname']) ? $data['displayname'] : ''
        ))
        ->add('password', 'password', array(
            'always_empty' => true,
            'required' => false
        ))
        ->add('password_repeat', 'password', array(
            'always_empty' => true,
            'required' => false
        ))
        ;
        return $form->getForm();
    }

    /**
     * Render the form and return the completed Twig template
     *
     * @param  $form
     */
    protected function renderAccountForm(Form $form)
    {
        return $this->app['twig']->render($this->app['utils']->getTemplateFile(
            '@phpManufaktur/Basic/Template', 'framework/account.twig'),
            array(
                'usage' => self::$usage,
                'alert' => $this->getAlert(),
                'form' => $form->createView(),
                'action' => '/user/account/edit/check'
            ));
    }

    protected function checkAccountForm(&$data=array())
    {
        // get the form
        $form = $this->getAccountForm();
        // get the requested data
        $form->bind($this->app['request']);

        if ($form->isValid()) {
            // the form is valid
            $account = $form->getData();
            $data = array();
            $checked = true;
            $change_password = false;

            if ($account['id'] != self::$user_id) {
                // security leak - the IDs differ!
                $this->setAlert('The form seems to be compromitted, can not check the data!',
                    array(), self::ALERT_TYPE_DANGER, array(__METHOD__, __LINE__));
                return false;
            }

            if ($account['username'] != self::$user_data['username']) {
                if ($this->app['account']->checkUserHasFrameworkAccount($account['username'], self::$user_data['id'])) {
                    $checked = false;
                    $this->setAlert('The username %username% is already in use, please select another one!',
                        array('%username%' => $account['username']), self::ALERT_TYPE_WARNING);
                }
            }

            if ($account['email'] != self::$user_data['email']) {
                if ($this->app['account']->checkUserHasFrameworkAccount($account['email'], self::$user_data['id'])) {
                    $checked = false;
                    $this->setAlert('The email address %email% is already used by another account!',
                        array('%email%' => $account['email']), self::ALERT_TYPE_WARNING);
                }
            }

            if ($account['displayname'] != self::$user_data['displayname']) {
                if ($this->app['account']->existsDisplayName($account['displayname'], self::$user_data['id'])) {
                    $checked = false;
                    $this->setAlert('The displayname %displayname% is already in use by another user, please select another one!',
                        array('%displayname%' => $account['displayname']), self::ALERT_TYPE_WARNING);
                }
            }

            if (!empty($account['password'])) {
                if ($account['password'] == $account['password_repeat']) {
                    // the passwords are identical ...
                    if ($this->app['utils']->passwordStrength($account['password']) < 3) {
                        // password is not strong enough!
                        $checked = false;
                        $this->setAlert('The password you have typed in is not strength enough. Please choose a password at minimun 8 characters long, containing lower and uppercase characters, numbers and special chars. Spaces are not allowed.',
                            array(), self::ALERT_TYPE_WARNING);
                    }
                    else {
                        // ok - change the password!
                        $change_password = true;
                    }
                }
                else {
                    // passwords are not identical
                    $checked = false;
                    $this->setAlert('The both passwords you have typed in does not match, please try again!',
                        array(), self::ALERT_TYPE_WARNING);
                }
            }

            if ($checked) {
                // update the account record
                if (($account['username'] != self::$user_data['username']) || ($account['email'] != self::$user_data['email']) ||
                    ($account['displayname'] != self::$user_data['displayname']) || $change_password) {
                    $data = array(
                        'id' => $account['id'],
                        'username' => $account['username'],
                        'email' => $account['email'],
                        'displayname' => $account['displayname']
                    );
                    if ($change_password) {
                        $data['password'] = $this->app['account']->encodePassword($account['password']);
                    }
                    $this->app['account']->updateUserDataByID($account['id'], $data);
                    $this->setAlert('Your account was succesfull updated.', array(), self::ALERT_TYPE_SUCCESS);
                }
                else {
                    $data = $account;
                    $this->setAlert('The account was not changed.', array(), self::ALERT_TYPE_INFO);
                }
                return true;
            }
            else {
                $data = $account;
                return false;
            }
        }
        else {
            // general error (timeout, CSFR ...)
            $this->setAlert('The form is not valid, please check your input and try again!', array(),
                self::ALERT_TYPE_DANGER, true, array('form_errors' => $form->getErrorsAsString(),
                    'method' => __METHOD__, 'line' => __LINE__));
            return false;
        }
    }

    /**
     * Return the Account dialog
     *
     * @param Application $app
     * @return string dialog
     */
    public function ControllerAccountEdit(Application $app)
    {
        $this->initialize($app);

        $form = $this->getAccountForm(self::$user_data);
        return $this->renderAccountForm($form);
    }

    /**
     * Check changes of the account and update the record if needed
     *
     * @param Application $app
     */
    public function ControllerAccountEditCheck(Application $app)
    {
        $this->initialize($app);

        $data = array();
        $this->checkAccountForm($data);

        $form = $this->getAccountForm($data);
        return $this->renderAccountForm($form);
    }

}
