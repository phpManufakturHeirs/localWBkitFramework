<?php

/**
 * Event
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://addons.phpmanufaktur.de/event
 * @copyright 2013 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\Event\Control\Command;

use phpManufaktur\Basic\Control\kitCommand\Basic;
use Silex\Application;
use phpManufaktur\Basic\Control\Account\Account;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpFoundation\Request;
use phpManufaktur\Event\Data\Event\Propose;
use phpManufaktur\Contact\Control\Contact;
use phpManufaktur\Contact\Data\Contact\Overview;
use phpManufaktur\Basic\Data\Security\AdminAction;
use phpManufaktur\Event\Control\Configuration;
use Symfony\Component\HttpFoundation\Response;
use phpManufaktur\Basic\Data\Security\Users;
use phpManufaktur\Event\Data\Event\Event;
use Carbon\Carbon;

class Edit extends Basic
{

    protected static $roles_event_edit = array('ROLE_ADMIN','ROLE_EVENT_EDIT_ADMIN','ROLE_EVENT_EDIT_ORGANIZER','ROLE_EVENT_EDIT_LOCATION','ROLE_EVENT_EDIT_SUBMITTER');


    public function controllerEditEventCheck(Application $app)
    {
        $this->initParameters($app);

        // get the form fields
        $event = $app['request']->request->get('form', array());

        if (($app['session']->get('ALLOWED_TO_EDIT_EVENT_ID')) != $event['event_id']) {
            // the user has not the right to edit this event
            throw new \Exception("Not allowed to edit this event ID");
        }

        // get the configuration
        $Configuration = new Configuration($app);
        $config = $Configuration->getConfiguration();

        // check the event data
        if ($config['event']['description']['title']['required'] &&
            (!isset($event['description_title']) || (strlen(trim($event['description_title'])) < $config['event']['description']['title']['min_length']))) {
            $this->setMessage('Please type in a title with %minimum% characters at minimum.',
                array('%minimum%' => $config['event']['description']['title']['min_length']));
            return $this->controllerEditEvent($app);
        }
        elseif (!isset($event['description_title'])) {
            $event['description_title'] = '';
        }
        if ($config['event']['description']['short']['required'] &&
            (!isset($event['description_short']) || (strlen(trim($event['description_short'])) < $config['event']['description']['short']['min_length']))) {
            $this->setMessage('Please type in a short description with %minimum% characters at minimum.',
                array('%minimum%' => $config['event']['description']['short']['min_length']));
            return $this->controllerEditEvent($app);
        }
        elseif (!isset($event['description_short'])) {
            $event['description_short'] = '';
        }
        if ($config['event']['description']['long']['required'] &&
            (!isset($event['description_long']) || (strlen(trim($event['description_long'])) < $config['event']['description']['long']['min_length']))) {
            $this->setMessage('Please type in a long description with %minimum% characters at minimum.',
                array('%minimum%' => $config['event']['description']['long']['min_length']));
            return $this->controllerEditEvent($app);
        }
        elseif (!isset($event['description_long'])) {
            $event['description_long'] = '';
        }

        /*
        if (!$config['event']['date']['event_date_from']['allow_date_in_past'] &&
            (strtotime($event['event_date_from']) < time())) {
            $this->setMessage('It is not allowed that the event start in the past!');
            return $this->controllerEditEvent($app);
        }
        */

        // create date time in the correct format
        $dt = Carbon::createFromFormat($app['translator']->trans('DATETIME_FORMAT'), $event['event_date_from']);
        $event['event_date_from'] = $dt->toDateTimeString();

        $dt = Carbon::createFromFormat($app['translator']->trans('DATETIME_FORMAT'), $event['event_date_to']);
        $event['event_date_to'] = $dt->toDateTimeString();

        if (empty($event['event_publish_from'])) {
            $dt = Carbon::createFromTimestamp(strtotime($event['event_date_from']));
            $dt->subDays($config['event']['date']['event_publish_from']['subtract_days']);
            $dt->startOfDay();
            $event['event_publish_from'] = $dt->toDateTimeString();
        }
        else {
            $dt = Carbon::createFromFormat($app['translator']->trans('DATETIME_FORMAT'), $event['event_publish_from']);
            $event['event_publish_from'] = $dt->toDateTimeString();
        }

        if (empty($event['event_publish_to'])) {
            $dt = Carbon::createFromTimestamp(strtotime($event['event_date_to']));
            $dt->addDays($config['event']['date']['event_publish_to']['add_days']);
            $dt->endOfDay();
            $event['event_publish_to'] = $dt->toDateTimeString();
        }
        else {
            $dt = Carbon::createFromFormat($app['translator']->trans('DATETIME_FORMAT'), $event['event_publish_to']);
            $event['event_publish_to'] = $dt->toDateTimeString();
        }

        if (empty($event['event_deadline'])) {
            $event['event_deadline'] = '0000-00-00 00:00:00';
        }
        else {
            $dt = Carbon::createFromFormat($app['translator']->trans('DATETIME_FORMAT'), $event['event_deadline']);
            $event['event_deadline'] = $dt->toDateTimeString();
        }


        if (strtotime($event['event_date_from']) > strtotime($event['event_date_to'])) {
            $this->setMessage('The event start date is behind the event end date!');
            return $this->controllerEditEvent($app);
        }
        if (strtotime($event['event_publish_to']) < strtotime($event['event_date_from'])) {
            $this->setMessage('The publishing date ends before the event starts, this is not allowed!');
            return $this->controllerEditEvent($app);
        }
        if (strtotime($event['event_deadline']) > strtotime($event['event_date_from'])) {
            $this->setMessage('The deadline ends after the event start date!');
            return $this->controllerEditEvent($app);
        }

        // ok - save the event
        $data = array(
            'event_costs' => isset($event['event_costs']) ? $this->app['utils']->str2float($event['event_costs']) : 0,
            'event_participants_max' => isset($event['event_participants_max']) ? $this->app['utils']->str2int($event['event_participants_max']) : -1,
            'event_date_from' => $event['event_date_from'],
            'event_date_to' => $event['event_date_to'],
            'event_publish_from' => $event['event_publish_from'],
            'event_publish_to' => $event['event_publish_to'],
            'event_deadline' => $event['event_deadline'],
            'description_title' => isset($event['description_title']) ? trim($event['description_title']) : '',
            'description_short' => isset($event['description_short']) ? trim($event['description_short']) : '',
            'description_long' => isset($event['description_long']) ? trim($event['description_long']) : '',
            'event_url' => isset($event['event_url']) ? $event['event_url'] : ''
        );

        $EventData = new Event($app);
        $EventData->updateEvent($data, $event['event_id']);

        $this->setMessage('Event successfull updated');

        return $this->controllerEditEvent($app);
    }

    public function controllerEditEvent(Application $app)
    {
        $this->initParameters($app);

        if (is_null($event = $this->app['request']->request->get('form'))) {
            if (null == ($event_id = $app['request']->request->get('event_id'))) {
                // missing the event ID
                throw new \Exception("Invalid event ID!");
            }
            $EventData = new Event($app);
            $event = $EventData->selectEvent($event_id, false);
        }
        else {
            $event_id = $event['event_id'];
        }

        if (($app['session']->get('ALLOWED_TO_EDIT_EVENT_ID')) != $event_id) {
            // the user has not the right to edit this event
            throw new \Exception("Not allowed to edit this event ID");
        }

        $ConfigData = new Configuration($app);
        $config = $ConfigData->getConfiguration();

        // get the organizer data
        $ContactData = new Contact($app);
        if (false === ($organizer = $ContactData->selectOverview($event['event_organizer']))) {
            throw new \Exception("The Organizer does not exists!");
        }
        // get the location data
        if (false === ($location = $ContactData->selectOverview($event['event_location']))) {
            throw new \Exception("The Location does not exists!");
        }


        $fields = $this->app['form.factory']->createBuilder('form')
        ->add('event_id', 'hidden', array(
            'data' => $event_id
        ))
        ->add('event_organizer', 'hidden', array(
            'data' => $event['event_organizer']
        ))
        ->add('event_location', 'hidden', array(
            'data' => $event['event_location']
        ))
        // Event date
        ->add('event_date_from', 'text', array(
            'attr' => array('class' => 'event_date_from'),
            'data' => (!empty($event['event_date_from']) && ($event['event_date_from'] != '0000-00-00 00:00:00')) ? date($this->app['translator']->trans('DATETIME_FORMAT'), strtotime($event['event_date_from'])) : null,
        ))
        ->add('event_date_to', 'text', array(
            'attr' => array('class' => 'event_date_to'),
            'data' => (!empty($event['event_date_to']) && ($event['event_date_to'] != '0000-00-00 00:00:00')) ? date($this->app['translator']->trans('DATETIME_FORMAT'), strtotime($event['event_date_to'])) : null
        ))
        // Publish from - to
        ->add('event_publish_from', 'text', array(
            'attr' => array('class' => 'event_publish_from'),
            'data' => (!empty($event['event_publish_from']) && ($event['event_publish_from'] != '0000-00-00 00:00:00')) ? date($this->app['translator']->trans('DATETIME_FORMAT'), strtotime($event['event_publish_from'])) : '',
            'label' => 'Publish from',
            'required' => false
        ))
        ->add('event_publish_to', 'text', array(
            'attr' => array('class' => 'event_publish_to'),
            'data' => (!empty($event['event_publish_to']) && ($event['event_publish_to'] != '0000-00-00 00:00:00')) ? date($this->app['translator']->trans('DATETIME_FORMAT'), strtotime($event['event_publish_to'])) : null,
            'label' => 'Publish to',
            'required' => false
        ))
        // Deadline
        ->add('event_deadline', 'text', array(
            'attr' => array('class' => 'event_deadline'),
            'data' => (!empty($event['event_deadline']) && ($event['event_deadline'] != '0000-00-00 00:00:00')) ? date($this->app['translator']->trans('DATETIME_FORMAT'), strtotime($event['event_deadline'])) : null,
            'label' => 'Deadline',
            'required' => false
        ))
        // Participants
        ->add('event_participants_max', 'text', array(
            'label' => 'Participants, maximum',
            'data' => isset($event['event_participants_max']) ? $this->app['utils']->str2int($event['event_participants_max']) : -1,
            'label' => 'Participants maximum',
            'required' => false
        ))
        // Costs
        ->add('event_costs', 'text', array(
            'required' => false,
            'data' => isset($event['event_costs']) ? number_format($this->app['utils']->str2float($event['event_costs']), 2, $this->app['translator']->trans('DECIMAL_SEPARATOR'), $this->app['translator']->trans('THOUSAND_SEPARATOR')) : 0
        ))
        // Event URL
        ->add('event_url', 'url', array(
            'required' => false,
            'data' => isset($event['event_url']) ? $event['event_url'] : ''
        ))
        ->add('description_title', 'text', array(
            'data' => isset($event['description_title']) ? $event['description_title'] : '',
            'label' => 'Title',
            'required' => $config['event']['description']['title']['required']
        ))
        ->add('description_short', 'textarea', array(
            'data' => isset($event['description_short']) ? $event['description_short'] : '',
            'label' => 'Short description',
            'required' => $config['event']['description']['short']['required']
        ))
        ->add('description_long', 'textarea', array(
            'data' => isset($event['description_long']) ? $event['description_long'] : '',
            'label' => 'Long description',
            'required' => $config['event']['description']['long']['required']
        ))
        ;
        $form = $fields->getForm();

        $Account = new Account($app);

        return $this->app['twig']->render($this->app['utils']->getTemplateFile(
            '@phpManufaktur/Event/Template',
            "command/event.edit.dialog.twig",
            $this->getPreferredTemplateStyle()),
            array(
                'basic' => $this->getBasicSettings(),
                'form' => $form->createView(),
                'organizer' => $organizer,
                'location' => $location,
                'route' => array(
                    'event' => array(
                        'check' => '/event/frontend/edit/check'
                    )
                ),
                'config' => $config,
                'displayname' => $Account->getDisplayName(),
                'logout_url' => FRAMEWORK_URL.'/logout?pid='.$this->getParameterID().'&content='.urlencode($app['translator']->trans('Thank you, %name%', array('%name%' => $Account->getDisplayName())))
            ));



        $token = $app['security']->getToken();
        $roles = $token->getRoles();
        print_r($roles);
        return '<a href="'.FRAMEWORK_URL.'/logout?pid='.$this->getParameterID().'&content='.urlencode('<p>Bääääh!</p>').'">logout</a> edit event';
    }

    /**
     * Check if the user is authenticated to edit the given event ID
     *
     * @param Application $app
     * @param integer $event_id
     * @throws \Exception
     * @return boolean
     */
    protected function checkAuthentication(Application $app, $event_id)
    {
        $app['session']->remove('ALLOWED_TO_EDIT_EVENT_ID');

        $Account = new Account($app);

        if (!$Account->isAuthenticated()) {
            $this->setMessage('Your are not authenticated, please login!');
            return false;
        }

        if ($Account->isGranted('ROLE_EVENT_ADMIN') || $Account->isGranted('ROLE_EVENT_SUBMITTER') ||
            $Account->isGranted('ROLE_EVENT_ORGANIZER') || $Account->isGranted('ROLE_EVENT_LOCATION')) {

            if ($Account->isGranted('ROLE_EVENT_ADMIN')) {
                // user is admin
                $app['session']->set('ALLOWED_TO_EDIT_EVENT_ID', $event_id);
                return true;
            }
            $EventData = new Event($app);
            if (false === ($event = $EventData->selectEvent($event_id, false))) {
                throw new \Exception("The event with the ID $event_id does not exists!");
            }
            $username = $Account->getUserName();
            if (false === ($user = $Account->getUserData($username))) {
                throw new \Exception("The user $username does not exists!");
            }

            $ContactOverview = new Overview($app);
            if (false === ($contact = $ContactOverview->selectLogin($username))) {
                throw new \Exception("The contact for $username does not exists!");
            }

            if ($Account->isGranted('ROLE_EVENT_SUBMITTER')) {
                $ProposeData = new Propose($app);
                if ($ProposeData->checkSubmitterCanEdit($contact['contact_id'], $event_id)) {
                    // user has submitted this event
                    $app['session']->set('ALLOWED_TO_EDIT_EVENT_ID', $event_id);
                    return true;
                }
            }

            if ($Account->isGranted('ROLE_EVENT_ORGANIZER')) {
                if ($event['event_organizer'] == $contact['contact_id']) {
                    // user is organizer of the event
                    $app['session']->set('ALLOWED_TO_EDIT_EVENT_ID', $event_id);
                    return true;
                }
            }

            if ($Account->isGranted('ROLE_EVENT_LOCATION')) {
                if ($event['event_location'] == $contact['contact_id']) {
                    // user represent the location of the event
                    $app['session']->set('ALLOWED_TO_EDIT_EVENT_ID', $event_id);
                    return true;
                }
            }
        }
        // no fitting user role!
        $this->setMessage('No fitting user role dectected!');
        return false;
    }

    /**
     * Controller to check the login of a user
     *
     * @param Application $app
     */
    public function controllerLoginCheck(Application $app)
    {
        $this->initParameters($app);

        $check = $app['request']->request->get('form', array());
        if (!isset($check['login']) || !isset($check['password']) ||
            !isset($check['redirect']) || !isset($check['event_id'])) {
            $this->setMessage('Invalid submission, please try again');
            $subRequest = Request::create('/event/frontend/login', 'POST',
                array(
                    'event_id' => isset($check['event_id']) ? $check['event_id'] : -1,
                    'redirect' => isset($check['redirect']) ? $check['redirect'] : '',
                    'pid' => $this->getParameterID()));
            return $app->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
        }

        // now log the user in
        $Account = new Account($app);
        $roles = array();
        if (!$Account->checkLogin($check['login'], $check['password'], $roles)) {
            $this->setMessage('Invalid login');
            $subRequest = Request::create('/event/frontend/login', 'POST',
                array(
                    'event_id' => isset($check['event_id']) ? $check['event_id'] : -1,
                    'redirect' => isset($check['redirect']) ? $check['redirect'] : '',
                    'pid' => $this->getParameterID()));
            return $app->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
        }

        // login the user to the secure area
        $Account->loginUserToSecureArea($check['login'], $roles);

        if (!$this->checkAuthentication($app, $check['event_id'])) {
            // a explaining message should come from the authentication check ...
            $subRequest = Request::create('/event/frontend/login', 'POST',
                array(
                    'event_id' => isset($check['event_id']) ? $check['event_id'] : -1,
                    'redirect' => isset($check['redirect']) ? $check['redirect'] : '',
                    'pid' => $this->getParameterID()));
            return $app->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
        }

        // the user is allowed to edit this event
        $subRequest = Request::create('/event/frontend/edit', 'POST',
            array(
                'event_id' => $check['event_id'],
                'redirect' => $check['redirect'],
                'pid' => $this->getParameterID()));
        return $app->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
    }

    /**
     * Check the email and send a new password to the user
     *
     * @param Application $app
     * @throws \Exception
     */
    public function controllerNewPasswordCheck(Application $app)
    {
        $this->initParameters($app);

        $check = $app['request']->request->get('form', array());

        $Account = new Account($app);
        if (false === ($user = $Account->getUserData($check['login']))) {
            $this->setMessage('The user %user% does not exists!', array('%user%' => $check['login']));
            return $this->controllerLogin($app);
        }

        $password = $app['utils']->createPassword();

        $data = array(
            'password' => $Account->encodePassword($password)
        );
        $Account->updateUserData($check['login'], $data);

        // create email
        $body = $this->app['twig']->render($this->app['utils']->getTemplateFile(
            '@phpManufaktur/Event/Template',
            'command/mail/account/password.twig',
            $this->getPreferredTemplateStyle()),
            array(
                'user' => $user,
                'password' => $password
            ));

        // send a email to the contact
        $message = \Swift_Message::newInstance()
        ->setSubject($app['translator']->trans('New password'))
        ->setFrom(array(SERVER_EMAIL_ADDRESS => SERVER_EMAIL_NAME))
        ->setTo($user['email'])
        ->setBody($body)
        ->setContentType('text/html');
        // send the message
        $failedRecipients = null;
        if (!$this->app['mailer']->send($message, $failedRecipients))  {
            throw new \Exception("Can't send mail to: ".implode(',', $failedRecipients));
        }

        $this->setMessage('We have send you a new password, please check your email account');
        return $this->controllerLogin($app);
    }

    /**
     * Controller to order a new password
     *
     * @param Application $app
     */
    public function controllerNewPasswordDialog(Application $app)
    {
        $this->initParameters($app);

        $fields = $app['form.factory']->createBuilder('form')
        ->add('login', 'text', array(
            'label' => 'Username or email address'
        ))
        ;
        $form = $fields->getForm();

        return $app['twig']->render($this->app['utils']->getTemplateFile(
            '@phpManufaktur/Event/Template',
            'command/event.account.password.twig',
            $this->getPreferredTemplateStyle()),
            array(
                'basic' => $this->getBasicSettings(),
                'form' => $form->createView(),
                'route' => array(
                    'form_action' => FRAMEWORK_URL. '/event/frontend/edit/account/password/check?pid='. $this->getParameterID(),
                )
            ));
    }

    /**
     * Controller show a login dialog
     *
     * @param Application $app
     */
    public function controllerLogin(Application $app)
    {
        $this->initParameters($app);

        $fields = $app['form.factory']->createBuilder('form')
        ->add('event_id', 'hidden', array(
            'data' => $app['request']->request->get('event_id', -1)
        ))
        ->add('redirect', 'hidden', array(
            'data' => $app['request']->request->get('redirect')
        ))
        ->add('login', 'text', array(
            'label' => 'Username or email address'
        ))
        ->add('password', 'password')
        ;
        $form = $fields->getForm();

        return $app['twig']->render($this->app['utils']->getTemplateFile(
            '@phpManufaktur/Event/Template',
            'command/event.edit.login.twig',
            $this->getPreferredTemplateStyle()),
            array(
                'basic' => $this->getBasicSettings(),
                'form' => $form->createView(),
                'route' => array(
                    'form_action' => FRAMEWORK_URL. '/event/frontend/login/check?pid='. $this->getParameterID(),
                    'new_account' => FRAMEWORK_URL.'/event/frontend/account/select/event/'. $app['request']->request->get('event_id', -1) .'/redirect/'. $app['request']->request->get('redirect'). '?pid='. $this->getParameterID(),
                    'new_password' => FRAMEWORK_URL.'/event/frontend/edit/account/password?pid='.$this->getParameterID()
                )
            ));
    }

    /**
     * Controller to activate the desired user role
     *
     * @param Application $app
     * @param string $guid
     * @throws \Exception
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function controllerActivateRole(Application $app, $guid)
    {
        $this->initParameters($app);

        $AdminAction = new AdminAction($app);
        if (false === ($action = $AdminAction->selectByGUID($guid))) {
            // the GUID does not exists
            return new Response($app['translator']->trans('The submitted GUID %guid% does not exists.',
                array('%guid%' => $guid)));
        }
        if ($action['status'] != 'PENDING') {
            // the GUID was already used
            return new Response($app['translator']->trans('This activation link was already used and is no longer valid!'));
        }

        $Account = new Account($app);
        if (false === ($user = $Account->getUserData($action['user_name']))) {
            return new Response($app['translator']->trans('The user %user% does not exists!', array('%user%' => $action['user_name'])));
        }

        // get the actual roles of the user
        $roles = (strpos($user['roles'], ',')) ? explode(',', $user['roles']) : array($user['roles']);
        // add the new role
        $roles[] = $action['role_action'];

        $password = $app['utils']->createPassword();

        $data = array(
            'roles' => implode(',', $roles),
            'password' => $Account->encodePassword($password)
        );
        // update the user
        $Account->updateUserData($action['user_name'], $data);

        // update the AdminAction
        $data = array(
            'status' => 'DONE'
        );
        $AdminAction->update($data, $action['id']);

        // create email
        $body = $this->app['twig']->render($this->app['utils']->getTemplateFile(
            '@phpManufaktur/Event/Template',
            'command/mail/account/activate.role.twig',
            $this->getPreferredTemplateStyle()),
            array(
                'user' => $user,
                'role' => $action['role_action'],
                'password' => $password
        ));

        // send a email to the contact
        $message = \Swift_Message::newInstance()
        ->setSubject($app['translator']->trans('Change account rights'))
        ->setFrom(array(SERVER_EMAIL_ADDRESS => SERVER_EMAIL_NAME))
        ->setTo($user['email'])
        ->setBody($body)
        ->setContentType('text/html');
        // send the message
        $failedRecipients = null;
        if (!$this->app['mailer']->send($message, $failedRecipients))  {
            throw new \Exception("Can't send mail to: ".implode(',', $failedRecipients));
        }

        return new Response($app['translator']->trans('The action link was successfull executed'));
    }

    /**
     * Controller Admin reject the desired account role
     *
     * @param Application $app
     * @param string $guid
     * @return string
     */
    public function controllerRejectRole(Application $app, $guid)
    {
        $this->initParameters($app);

        $AdminAction = new AdminAction($app);
        if (false === ($action = $AdminAction->selectByGUID($guid))) {
            // the GUID does not exists
            return new Response($app['translator']->trans('The submitted GUID %guid% does not exists.',
                array('%guid%' => $guid)));
        }
        if ($action['status'] != 'PENDING') {
            // the GUID was already used
            return new Response($app['translator']->trans('This activation link was already used and is no longer valid!'));
        }

        $Users = new Users($app);
        if (false === ($user = $Users->selectUser($action['user_name']))) {
            return new Response($app['translator']->trans('The user %user% does not exists!', array('%user%' => $action['user_name'])));
        }

        $data = array(
            'status' => 'DONE'
        );
        $AdminAction->update($data, $action['id']);

        // create email
        $body = $this->app['twig']->render($this->app['utils']->getTemplateFile(
            '@phpManufaktur/Event/Template',
            'command/mail/account/reject.role.twig',
            $this->getPreferredTemplateStyle()),
            array('user' => $user));

        // send a email to the contact
        $message = \Swift_Message::newInstance()
        ->setSubject($app['translator']->trans('Change account rights'))
        ->setFrom(array(SERVER_EMAIL_ADDRESS => SERVER_EMAIL_NAME))
        ->setTo($user['email'])
        ->setBody($body)
        ->setContentType('text/html');
        // send the message
        $failedRecipients = null;
        if (!$this->app['mailer']->send($message, $failedRecipients))  {
            throw new \Exception("Can't send mail to: ".implode(',', $failedRecipients));
        }

        return new Response($app['translator']->trans('The action link was successfull executed'));
    }

    /**
     * Check the submitted email address against accounts and contacts, generate
     * an activation mail for the administrator
     *
     * @param Application $app
     * @return string dialog information about the action
     */
    public function controllerSelectAccountCheck(Application $app)
    {
        $this->initParameters($app);

        $check = $app['request']->request->get('form', array());

        $ContactControl = new Contact($app);
        if (false === ($contact_id = $ContactControl->existsLogin($check['login']))) {
            // this email address is not registered
            $this->setMessage('The email address %email% is not registered. We can only create a account for you '.
                'if there was already a interaction, i.e. you have proposed a event. If you represent an organizer '.
                'or a location and your public email address is not registered, please contact the administrator.',
                array('%email%' => $check['login']));
            // redirect to login and prompt the message
            $subRequest = Request::create('/event/frontend/login', 'POST',
                array(
                    'event_id' => $check['event_id'],
                    'redirect' => $check['redirect'],
                    'pid' => $this->getParameterID()));
            return $app->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
        }

        $ContactOverview = new Overview($app);
        $contact = $ContactOverview->select($contact_id);

        $Account = new Account($app);

        if (false !== ($user = $Account->getUserData($check['login']))) {
            // the user has already an account
            if (false === strpos($user['roles'], ',')) {
                $roles = array($user['roles']);
            }
            else {
                $roles = explode(',', $user['roles']);
            }
            foreach ($roles as $role) {
                if (in_array($role, self::$roles_event_edit)) {
                    // user has already a role which allow to edit events
                    $this->setMessage('You have already the right to edit events (%role%). '.
                        'Please contact the administrator if you want to change or extend your account rights',
                        array('%role%' => $role));
                    // redirect to login and prompt the message
                    $subRequest = Request::create('/event/frontend/login', 'POST',
                        array(
                            'event_id' => $check['event_id'],
                            'redirect' => $check['redirect'],
                            'pid' => $this->getParameterID()));
                    return $app->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
                }
            }
            if (in_array('ROLE_EVENT_USER', $roles)) {
                $Propose = new Propose($app);
                if (false === ($proposes = $Propose->selectBySubmitterID($contact_id))) {
                    // user has never proposed a event
                    $user_status = 'NEVER_PROPOSED_EVENT';
                    $user_proposes = 0;
                }
                else {
                    // user has already proposed one or more events
                    $user_status = 'PROPOSED_EVENT';
                    $user_proposes = count($proposes);
                }
            }
            else {
                // user has a account but no role within EVENT
                $user_status = 'GENERAL_ACCOUNT';
                $user_proposes = 0;
            }
        }
        else {
            // user exists in the Contacts but never interacted with the provider
            $Account->createAccount(
                $contact['communication_email'],
                $contact['communication_email'],
                $app['utils']->createPassword(12),
                'ROLE_EVENT_CONTACT',
                $contact['order_name']);
            $user = $Account->getUserData($contact['communication_email']);
            $user_status = 'CONTACT_ONLY';
            $user_proposes = 0;
        }

        // create a AdminAction record
        $admin_data = array(
            'user_id' => $user['id'],
            'user_name' => $user['username'],
            'user_email' => $user['email'],
            'guid' => $app['utils']->createGUID(),
            'status' => 'PENDING',
            'role_action' => $check['account_type'],
            'status_action' => '',
            'redirect_url' => $this->getCMSpageURL()
        );
        $AdminAction = new AdminAction($app);
        $AdminAction->insert($admin_data);

        $ContactOverview = new Overview($app);
        $contact = $ContactOverview->select($contact_id);

        // create administrator email
        $body = $this->app['twig']->render($this->app['utils']->getTemplateFile(
            '@phpManufaktur/Event/Template',
            'command/mail/account/admin.twig',
            $this->getPreferredTemplateStyle()),
            array(
                'user' => array(
                    'contact' => $contact,
                    'want_account' => $check['account_type'],
                    'status' => $user_status,
                    'proposes' => $user_proposes,
                    'roles' => $roles
                ),
                'action' => array(
                    'link' => array(
                        'activate' => FRAMEWORK_URL.'/event/frontend/edit/account/activate/'.$admin_data['guid'],
                        'reject' => FRAMEWORK_URL.'/event/frontend/edit/account/reject/'.$admin_data['guid']
                    )
                )
            ));

        $ConfigData = new Configuration($app);
        $config = $ConfigData->getConfiguration();

        $mail_to = $config['account']['confirm']['mail_to'];
        if (in_array('provider', $mail_to)) {
            $mail_to[] = SERVER_EMAIL_ADDRESS;
            unset($mail_to[array_search('provider', $mail_to)]);
        }

        // send a email to the contact
        $message = \Swift_Message::newInstance()
        ->setSubject($app['translator']->trans('Change account rights'))
        ->setFrom(array(SERVER_EMAIL_ADDRESS => $contact['contact_name']))
        ->setTo($mail_to)
        ->setReplyTo($contact['communication_email'])
        ->setBody($body)
        ->setContentType('text/html');
        // send the message
        $failedRecipients = null;
        if (!$this->app['mailer']->send($message, $failedRecipients))  {
            throw new \Exception("Can't send mail to: ".implode(',', $failedRecipients));
        }

        return $app['twig']->render($this->app['utils']->getTemplateFile(
            '@phpManufaktur/Event/Template',
            'command/event.account.pending.twig',
            $this->getPreferredTemplateStyle()),
            array(
                'basic' => $this->getBasicSettings()
            ));
    }

    /**
     * Controller to select a new account which should be created for the user
     *
     * @param Application $app
     * @param integer $event_id
     * @param string $redirect base64_encoded
     */
    public function controllerSelectAccount(Application $app, $event_id, $redirect)
    {
        $this->initParameters($app);

        $fields = $app['form.factory']->createBuilder('form')
        ->add('event_id', 'hidden', array(
            'data' => (!is_null($event_id)) ? $event_id : -1
        ))
       ->add('redirect', 'hidden', array(
            'data' => $redirect
        ))
        ->add('account_type', 'choice', array(
            'expanded' => true,
            'multiple' => false,
            'choices' => array(
                'ROLE_EVENT_SUBMITTER' => 'CHOICE_SUBMITTER_ACCOUNT',
                'ROLE_EVENT_ORGANIZER' => 'CHOICE_ORGANIZER_ACCOUNT',
                'ROLE_EVENT_LOCATION' => 'CHOICE_LOCATION_ACCOUNT',
                'ROLE_EVENT_ADMIN' => 'CHOICE_ADMIN_ACCOUNT'
            ),
            'label' => 'Select account type',
            'data' => 'ROLE_EVENT_SUBMITTER'
        ))
        ->add('login', 'text', array(
            'label' => 'Username or email address'
        ))
        ;
        $form = $fields->getForm();

        return $app['twig']->render($this->app['utils']->getTemplateFile(
            '@phpManufaktur/Event/Template',
            'command/event.account.select.twig',
            $this->getPreferredTemplateStyle()),
            array(
                'basic' => $this->getBasicSettings(),
                'form' => $form->createView(),
            ));
    }

    /**
     * Check authentication, route to the event edit dialog if possible, show a
     * login dialog or reject authenticated users which less rights
     *
     * @param Application $app
     * @param integer $event_id
     * @param string $redirect base64_encoded
     */
    public function controllerCheck(Application $app, $event_id, $redirect)
    {
        $this->initParameters($app);

        $Account = new Account($app);

        if ($Account->isAuthenticated()) {
            if ($this->checkAuthentication($app, $event_id)) {
                // the user is allowed to edit this event
                $subRequest = Request::create('/event/frontend/edit', 'POST',
                    array(
                        'event_id' => $event_id,
                        'redirect' => $redirect,
                        'pid' => $this->getParameterID()));
                return $app->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
            }
            else {
                // not allowed to edit this event - message should come from check ...
                $Message = new Message($app);
                return $Message->render($this->getMessage(), array(), 'Edit event', array(), true);
            }

        }
        else {
            // not authenticated, redirect to the login
            $subRequest = Request::create('/event/frontend/login', 'POST',
                array(
                    'event_id' => $event_id,
                    'redirect' => $redirect,
                    'pid' => $this->getParameterID()));
            return $app->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
        }

    }

}
