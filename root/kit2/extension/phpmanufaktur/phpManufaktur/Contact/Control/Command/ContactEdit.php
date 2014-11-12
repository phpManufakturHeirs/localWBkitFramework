<?php

/**
 * Contact
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de/Contact
 * @copyright 2014 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\Contact\Control\Command;

use Silex\Application;
use phpManufaktur\Basic\Control\kitCommand\Basic;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\FormFactory;
use phpManufaktur\Contact\Control\Pattern\Form\Contact as ContactForm;

class ContactEdit extends Basic
{
    protected static $parameter = null;
    protected static $contact_id = null;

    /**
     * (non-PHPdoc)
     * @see \phpManufaktur\Basic\Control\kitCommand\Basic::initParameters()
     */
    protected function initParameters(Application $app, $parameter_id=-1)
    {
        parent::initParameters($app, $parameter_id);

        self::$contact_id = !is_null($app['request']->query->get('contact_id')) ?
            $app['request']->query->get('contact_id') : $app['request']->request->get('contact_id');
    }

    /**
     * Check if the user is authenticated and allowed to edit the desired
     * contact record
     *
     * @return boolean
     */
    protected function isAuthenticated()
    {
        if ($this->app['account']->isAuthenticated() &&
            $this->app['account']->isGranted('ROLE_CONTACT_EDIT_OWN') &&
            (false !== ($email = $this->app['account']->getEMailAddress()))) {
            if (($email == $this->app['contact']->getPrimaryEMailAddress(self::$contact_id)) ||
                $this->app['account']->isGranted('ROLE_CONTACT_EDIT')) {
                return true;
            }
            $this->setAlert('You are authenticated but not allowed to edit this contact', array(), self::ALERT_TYPE_WARNING);
        }
        elseif ($this->app['account']->isAuthenticated()) {
            $this->setAlert('You are authenticated but not allowed to edit this contact', array(), self::ALERT_TYPE_WARNING);
        }
        return false;
    }

    /**
     * Get the form for the login dialog
     *
     * @return FormFactory
     */
    protected function getFormLogin()
    {
        return $this->app['form.factory']->createBuilder('form')
            ->add('contact_id', 'hidden', array(
                'data' => self::$contact_id
            ))
            ->add('email', 'email', array(
                'required' => false
            ))
            ->add('password', 'password', array(
                'required' => false
            ))
            ->add('forgotten', 'checkbox', array(
                'required' => false,
                'label' => 'Forgot your password?'
            ))
            ->getForm();
    }

    /**
     * Controller to prompt the login dialog if the user is not authenticated or
     * is not allowed to edit this contact record
     *
     * @param Application $app
     * @return string
     */
    public function ControllerLogin(Application $app)
    {
        $this->initParameters($app);
        if ($this->isAuthenticated()) {
            return $this->ControllerEdit($app, self::$contact_id);
        }

        $form = $this->getFormLogin();

        return $this->app['twig']->render($this->app['utils']->getTemplateFile(
            '@phpManufaktur/Contact/Template', 'command/edit.login.twig',
            $this->getPreferredTemplateStyle()),
            array(
                'basic' => $this->getBasicSettings(),
                'contact_id' => self::$contact_id,
                'form' => $form->createView()
            ));
    }

    /**
     * Controller to check the user login
     *
     * @param Application $app
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function ControllerLoginCheck(Application $app)
    {
        $this->initParameters($app);
        if ($this->isAuthenticated()) {
            return $this->ControllerEdit($app, self::$contact_id);
        }

        $form = $this->getFormLogin();

        $form->bind($this->app['request']);
        if ($form->isValid()) {
            // get the form data
            $login = $form->getData();
            if (isset($login['forgotten']) && ($login['forgotten'] === true)) {
                $this->setAlert('If you have never got a password or still forgot it, you can order a link to create a new one. Just type in the email address which is assigned to the contact record you want zu change or update and we will send youn an email.',
                    array(), self::ALERT_TYPE_INFO);
                $subRequest = Request::create('/password/forgotten', 'GET',
                array(
                    'usage' => 'contact',
                    'pid' => $this->getParameterID()));
                return $app->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
            }
            self::$contact_id = intval($login['contact_id']);
            $roles = array();
            $errors = $app['validator']->validateValue($login['email'], new Assert\Email());
            if (count($errors) > 0) {
                // invalid email address
                $this->setAlert('The email address %email% is invalid!', array(
                    '%email%' => $login['email']), self::ALERT_TYPE_WARNING);
            }
            elseif (!$app['account']->checkLogin($login['email'], $login['password'], $roles)) {
                // login failed
                $this->setAlert('Please check the username and password and try again!',
                    array(), self::ALERT_TYPE_WARNING);
            }
            else {
                $app['account']->loginUserToSecureArea($login['email'], $roles);
                // subrequest to the edit dialog
                $subRequest = Request::create('/contact/owner/edit/id/'.self::$contact_id, 'GET',
                array(
                    'pid' => $this->getParameterID()));
                return $app->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
            }
        }
        else {
            // general error (timeout, CSFR ...)
            $this->setAlert('The form is not valid, please check your input and try again!', array(),
                self::ALERT_TYPE_DANGER, true, array('form_errors' => $form->getErrorsAsString(),
                    'method' => __METHOD__, 'line' => __LINE__));
        }

        return $this->app['twig']->render($this->app['utils']->getTemplateFile(
            '@phpManufaktur/Contact/Template', 'command/edit.login.twig',
            $this->getPreferredTemplateStyle()),
            array(
                'basic' => $this->getBasicSettings(),
                'contact_id' => self::$contact_id,
                'form' => $form->createView()
            ));
    }


    public function ControllerEditCheck(Application $app)
    {
        // init the parent and parameters
        $this->initParameters($app);

        // important: check the authentication!
        if (!$this->isAuthenticated()) {
            return $this->ControllerLogin($app);
        }

        // get the contact_id
        $request = $this->app['request']->get('form');
        self::$contact_id = (isset($request['contact_id'])) ? $request['contact_id'] : -1;

        // get the contact form
        $ContactForm = new ContactForm($app);
        $data = $ContactForm->getData(self::$contact_id);
        if (false === ($form = $ContactForm->getFormContact($data))) {
            return $this->promptAlert();
        }

        if ('POST' == $this->app['request']->getMethod()) {
            // the form was submitted, bind the request
            $form->bind($this->app['request']);
            if ($form->isValid()) {
                $data = $form->getData();
                if (false !== ($contact = $ContactForm->checkData($data))) {
                    // successfull submission, insert or update the contact
                    self::$contact_id = $contact['contact']['contact_id'];
                    if (self::$contact_id > 0) {
                        // update an existing contact record
                        $data_changed = false;
                        $this->app['contact']->update($contact, self::$contact_id, $data_changed, true);
                    }
                    else {
                        // insert a new contact record
                        $this->app['contact']->insert($contact, self::$contact_id);
                    }
                    // retrieve the current data
                    $data = $ContactForm->getData(self::$contact_id);
                }

            }
            else {
                // general error (timeout, CSFR ...)
                $this->setAlert('The form is not valid, please check your input and try again!', array(),
                    self::ALERT_TYPE_DANGER, true, array('form_errors' => $form->getErrorsAsString(),
                        'method' => __METHOD__, 'line' => __LINE__));
            }
        }
        else {
            // general error (timeout, CSFR ...)
            $this->setAlert('The form is not valid, please check your input and try again!', array(),
                self::ALERT_TYPE_DANGER, true, array('form_errors' => $form->getErrorsAsString(),
                    'method' => __METHOD__, 'line' => __LINE__));
        }

        if (false === ($form = $ContactForm->getFormContact($data))) {
            return $this->promptAlert();
        }

        return $this->app['twig']->render($this->app['utils']->getTemplateFile(
            '@phpManufaktur/Contact/Template', 'command/edit.contact.twig',
            $this->getPreferredTemplateStyle()),
            array(
                'basic' => $this->getBasicSettings(),
                'form' => $form->createView()
            ));
    }

    /**
     * Main controller to edit the given contact record with the desired ID
     *
     * @param Application $app
     * @param integer $contact_id
     * @return string
     */
    public function ControllerEdit(Application $app, $contact_id)
    {
        // init the parent and parameters
        $this->initParameters($app);
        self::$contact_id = $contact_id;

        // important: check the authentication!
        if (!$this->isAuthenticated()) {
            return $this->ControllerLogin($app);
        }

        // get the contact form
        $ContactForm = new ContactForm($app);
        $data = $ContactForm->getData(self::$contact_id);

        if (false === ($form = $ContactForm->getFormContact($data))) {
            return $this->promptAlert();
        }

        return $this->app['twig']->render($this->app['utils']->getTemplateFile(
            '@phpManufaktur/Contact/Template', 'command/edit.contact.twig',
            $this->getPreferredTemplateStyle()),
            array(
                'basic' => $this->getBasicSettings(),
                'form' => $form->createView()
            ));
    }


}
