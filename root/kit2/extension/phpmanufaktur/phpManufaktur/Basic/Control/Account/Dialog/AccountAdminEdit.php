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
use Symfony\Component\HttpFoundation\Response;
use phpManufaktur\Basic\Data\Security\Users;

class AccountAdminEdit extends Alert
{
    protected static $usage = null;
    protected static $user_id = null;
    protected static $user_data = null;
    protected $UserData = null;

    /**
     * (non-PHPdoc)
     * @see \phpManufaktur\Basic\Control\Pattern\Alert::initialize()
     */
    protected  function initialize(Application $app) {
        parent::initialize($app);

        self::$usage = $app['request']->get('usage', 'framework');

        $this->UserData = new Users($app);
    }

    static function array_values_recursive($array)
    {
        $temp = array();
        foreach ($array as $key => $value) {
            if (is_numeric($key)) {
                $temp[] = is_array($value) ? self::array_values_recursive($value) : $value;
            }
            else {
                $temp[$key] = is_array($value) ? self::array_values_recursive($value) : $value;
            }
        }
        return $temp;
    }

    /**
     * Create the form with form factory
     *
     * @param array $data
     * @return Form
     */
    protected function getAccountForm($data = array())
    {
        $roles = array();
        foreach ($this->app['account']->getAvailableRoles() as $role) {
            $role_name = $this->app['utils']->humanize($role);
            $roles[$role] = $this->app['translator']->trans($role_name);
        }

        $form = $this->app['form.factory']->createBuilder('form')
        ->add('id', 'hidden', array(
            'data' => isset($data['id']) ? $data['id'] : -1
        ))
        ->add('status', 'choice', array(
            'choices' => array('ACTIVE' => 'Active', 'LOCKED' => 'Locked'),
            'empty_value' => '- please select -',
            'expanded' => false,
            'data' => isset($data['status']) ? $data['status'] : null
        ))
        ->add('delete_account', 'checkbox', array(
            'required' => false,
            'label' => 'Delete this account irrevocable'
        ))
        ->add('username', 'text', array(
            'data' => isset($data['username']) ? $data['username'] : ''
        ))
        ->add('email', 'email', array(
            'data' => isset($data['email']) ? $data['email'] : ''
        ))
        ->add('displayname', 'text', array(
            'data' => isset($data['displayname']) ? $data['displayname'] : ''
        ))
        ->add('roles', 'choice', array(
            'choices' => $roles,
            'empty_value' => '- please select -',
            'expanded' => true,
            'multiple' => true,
            'required' => false,
            'data' => isset($data['roles']) ? (is_array($data['roles']) ? $data['roles'] : explode(',', $data['roles'])) : null
        ))
        ->add('password', 'password', array(
            'always_empty' => true,
            'required' => ($data['id'] < 1)
        ))
        ->add('password_repeat', 'password', array(
            'always_empty' => true,
            'required' => ($data['id'] < 1)
        ))
        ->add('send_email', 'checkbox', array(
            'required' => false,
            'label' => ($data['id'] > 0) ? $this->app['translator']->trans('Send email (only if the password has changed)') : $this->app['translator']->trans('Send account info to the user')
        ))
        ;
        if (!isset($data['id']) || ($data['id'] < 1)) {
            $form->remove('delete_account');
        }
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
            '@phpManufaktur/Basic/Template', 'framework/accounts.edit.twig'),
            array(
                'usage' => self::$usage,
                'alert' => $this->getAlert(),
                'form' => $form->createView(),
                'action' => '/admin/accounts/edit/check'
            ));
    }

    /**
     * Send the user an account information
     *
     * @param array $account
     * @param string $password
     */
    protected function sendAccountInfo($account, $password)
    {
        // create the email body
        $body = $this->app['twig']->render($this->app['utils']->getTemplateFile(
            '@phpManufaktur/Basic/Template', 'framework/mail/account.info.twig'),
            array(
                'account' => $account,
                'password' => $password
            ));
        // create the message
        $message = \Swift_Message::newInstance()
        ->setSubject($this->app['translator']->trans('kitFramework User Account'))
        ->setFrom(array(SERVER_EMAIL_ADDRESS))
        ->setTo(array($account['email']))
        ->setBody($body)
        ->setContentType('text/html');
        // send the message
        $failures = array();
        if ($this->app['mailer']->send($message, $failures) != 1) {
            foreach ($failures as $failure) {
                $this->setAlert("Can't send the email to %email%!", array('%email%' => $failure), self::ALERT_TYPE_WARNING);
            }
            return false;
        }
        return true;
    }

    /**
     * Check the Account record and insert or update it.
     *
     * @param array reference $data
     * @return boolean
     */
    protected function checkAccountForm(&$data=array())
    {
        // get the form
        $form = $this->getAccountForm($data);
        // get the requested data
        $form->bind($this->app['request']);

        if ($form->isValid()) {
            // the form is valid
            $account = $form->getData();
            $data = array();
            $checked = true;
            $change_password = false;

            if (isset($account['delete_account']) && ($account['delete_account'] == 1)) {
                // delete this account!
                $this->UserData->delete($account['id']);
                $this->setAlert('The account for the user %name% was successfull deleted.',
                    array('%name%' => $account['username']), self::ALERT_TYPE_SUCCESS);
                // return with a message
                return $this->app['twig']->render($this->app['utils']->getTemplateFile(
                    '@phpManufaktur/Basic/Template', 'framework/accounts.deleted.twig'),
                    array(
                        'usage' => self::$usage,
                        'alert' => $this->getAlert()
                    ));
            }

            if ($account['id'] < 1) {
                // create a new account
                $checked = true;
                if ($this->app['account']->checkUserHasFrameworkAccount($account['username'])) {
                    $checked = false;
                    $this->setAlert('The username %username% is already in use, please select another one!',
                        array('%username%' => $account['username']), self::ALERT_TYPE_WARNING);
                }
                if ($this->app['account']->checkUserHasFrameworkAccount($account['email'])) {
                    $checked = false;
                    $this->setAlert('The email address %email% is already used by another account!',
                        array('%email%' => $account['email']), self::ALERT_TYPE_WARNING);
                }
                if (!$checked) {
                    $data = $account;
                    return false;
                }
                $data = array(
                    'username' => $account['username'],
                    'email' => $account['email'],
                    'displayname' => $account['displayname'],
                    'status' => $account['status'],
                    'roles' => implode(',', $account['roles']),
                    'password' => $this->app['account']->encodePassword($account['password']),
                );
                $account['id'] = $this->UserData->insertUser($data);
                if (isset($account['send_email']) && ($account['send_email'] == 1)) {
                    // send a email to the account user
                    if ($this->sendAccountInfo($account, $account['password'])) {
                        $this->setAlert('Send a account information to the user %name%',
                            array('%name%' => $data['displayname']), self::ALERT_TYPE_SUCCESS);
                    }
                }
                $data = $account;
                $this->setAlert('Successfull created a account for the user %name%.',
                    array('%name%' => $account['displayname']), self::ALERT_TYPE_SUCCESS);
                return true;
            }

            // get the actual account data for comparison with the form
            if (false === (self::$user_data = $this->UserData->select($account['id']))) {
                throw new \Exception('Got no data for user '.$account['username'].'!');
            }
            // get the previous assinged roles
            $previous_roles = array();
            if (strpos(self::$user_data['roles'], ',')) {
                $previous_roles = explode(',', self::$user_data['roles']);
            }
            elseif (!empty(self::$user_data['roles'])) {
                $previous_roles[] = self::$user_data['roles'];
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

            $change_roles = false;
            if ($account['roles'] != $previous_roles) {
                $change_roles = true;
            }

            if ($checked) {
                if (($account['username'] != self::$user_data['username']) || ($account['email'] != self::$user_data['email']) ||
                    ($account['displayname'] != self::$user_data['displayname']) || ($account['status'] != self::$user_data['status']) ||
                    $change_password || $change_roles) {
                    // update the account record
                    $data = array(
                        'id' => $account['id'],
                        'username' => $account['username'],
                        'email' => $account['email'],
                        'displayname' => $account['displayname'],
                        'status' => $account['status'],
                        'roles' => implode(',', $account['roles'])
                    );
                    if ($change_password) {
                        $data['password'] = $this->app['account']->encodePassword($account['password']);
                    }
                    $this->app['account']->updateUserDataByID($account['id'], $data);
                    if ($change_password && (isset($account['send_email']) && ($account['send_email'] == 1))) {
                        // send a email to the account user
                        if ($this->sendAccountInfo($account, $account['password'])) {
                            $this->setAlert('Send a account information to the user %name%',
                                array('%name%' => $data['displayname']), self::ALERT_TYPE_SUCCESS);
                        }
                    }
                    $this->setAlert('The account was succesfull updated.', array(), self::ALERT_TYPE_SUCCESS);
                }
                else {
                    // nothing has changed
                    $data = $account;
                    $this->setAlert('The account was not changed.', array(), self::ALERT_TYPE_INFO);
                }
                return true;
            }
            else {
                // nothing to do, assign the submitted account data to the form
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
     * Controller to edit or create a kitFramework Account
     *
     * @param Application $app
     * @param mixed $id account ID or USERNAME
     * @return Response
     */
    public function ControllerAccountEdit(Application $app, $id)
    {
        $this->initialize($app);

        if (is_numeric($id)) {
            if ($id < 1) {
                // create a new account
                $data = array('id' => -1);
            }
            else {
                if (false === ($data = $this->UserData->select($id))) {
                    $this->setAlert('The account with the ID %id% does not exists!',
                        array('%id%' => $id), self::ALERT_TYPE_WARNING);
                    $data = array();
                }
            }
        }
        elseif (false === ($data = $this->UserData->selectUser($id))) {
            $this->setAlert('The account with the username or email address %name% does not exists!',
                    array('%name%' => $id), self::ALERT_TYPE_WARNING);
            $data = array();
        }

        $form = $this->getAccountForm($data);
        return $this->renderAccountForm($form);
    }

    /**
     * Control the submitted form and add or update the user account
     *
     * @param Application $app
     */
    public function ControllerAccountEditCheck(Application $app)
    {
        $this->initialize($app);

        $data = array('id' => -1);
        $check = $this->checkAccountForm($data);
        if (!is_bool($check)) {
            // the function return a rendered result
            return $check;
        }

        $form = $this->getAccountForm($data);
        return $this->renderAccountForm($form);
    }
}
