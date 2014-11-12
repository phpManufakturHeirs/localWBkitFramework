<?php

/**
 * Event
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de/Event
 * @copyright 2013 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\Event\Control\Command;

use Silex\Application;
use phpManufaktur\Basic\Control\kitCommand\Basic;
use phpManufaktur\Event\Data\Event\Group as EventGroup;
use phpManufaktur\Contact\Data\Contact\Overview;
use phpManufaktur\Event\Data\Event\OrganizerTag;
use phpManufaktur\Event\Data\Event\LocationTag;
use phpManufaktur\Event\Data\Event\Event;
use phpManufaktur\Event\Data\Event\Propose as ProposeData;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\Response;
use phpManufaktur\Event\Control\Configuration;
use phpManufaktur\Contact\Control\Configuration as ContactConfiguration;

class Propose extends Basic
{

    /**
     * Controller: The admin confirm the event and allow publishing
     *
     * @param Application $app
     * @param string $guid
     * @throws \Exception
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function controllerAdminPublish(Application $app, $guid)
    {
        $this->initParameters($app);

        $ProposeData = new ProposeData($app);
        if (false === ($propose = $ProposeData->selectAdminGUID($guid))) {
            return new Response("The GUID $guid does not exists!");
        }

        // get the actual URL
        $actual_url = $this->getCMSpageURL();

        // set the permanent link as CMS page URI!
        $this->setCMSpageURL($propose['command_url']);
        // redirect to itself
        $this->setRedirectRoute('/event/propose/publish/'.$guid);
        // activate redirection
        $this->setRedirectActive(true);

        if ($actual_url != $propose['command_url']) {
            // reload this page to fold the iframe into the CMS before performing anything
            $app['monolog']->addInfo(sprintf("Reload route %s into CMS URL %s before performing anything",
                '/event/propose/publish/'.$guid, $this->getCMSpageURL()));
            return $app['twig']->render($app['utils']->getTemplateFile('@phpManufaktur/Basic/Template', 'kitcommand/reload.twig'),
                array('basic' => $this->getBasicSettings()));
        }

        if ($propose['admin_status'] != 'PENDING') {
            return new Response($app['translator']->trans('This activation link was already used and is no longer valid!'));
        }

        // we have to activate all data of this proposed event
        if ($propose['new_organizer_id'] > 0) {
            $data = array(
                'contact' => array(
                    'contact_id' => $propose['new_organizer_id'],
                    'contact_status' => 'ACTIVE'
                )
            );
            if (false === ($app['contact']->update($data, $propose['new_organizer_id']))) {
                throw new \Exception(strip_tags($app['contact']->getAlert()));
            }
        }
        if ($propose['new_location_id'] > 0) {
            $data = array(
                'contact' => array(
                    'contact_id' => $propose['new_location_id'],
                    'contact_status' => 'ACTIVE'
                )
            );
            if (false === ($app['contact']->update($data, $propose['new_location_id']))) {
                throw new \Exception(strip_tags($app['contact']->getAlert()));
            }
        }

        $contact = $app['contact']->selectOverview($propose['submitter_id']);

        $EventData = new Event($app);

        $data = array(
            'event_status' => 'ACTIVE'
        );
        $EventData->updateEvent($data, $propose['new_event_id']);

        $event = $EventData->selectEvent($propose['new_event_id'], false);

        // set the status of the propose to CONFIRMED
        $data = array(
            'admin_status' => 'CONFIRMED',
            'admin_status_when' => date('Y-m-d H:i:s')
        );
        $ProposeData->update($propose['id'], $data);

        $body = $this->app['twig']->render($this->app['utils']->getTemplateFile(
            '@phpManufaktur/Event/Template',
            'command/mail/propose/submitter.published.twig',
            $this->getPreferredTemplateStyle()),
            array(
                'event' => $event
            )
        );

        // send a email to the contact
        $message = \Swift_Message::newInstance()
        ->setSubject($this->app['translator']->trans('Proposed event: %event%', array('%event%' => $event['description_title'])))
        ->setFrom(array(SERVER_EMAIL_ADDRESS => SERVER_EMAIL_NAME))
        ->setTo(array($contact['communication_email'] => $contact['contact_name']))
        ->setBody($body)
        ->setContentType('text/html');
        // send the message
        $failedRecipients = null;
        if (!$this->app['mailer']->send($message, $failedRecipients))  {
            throw new \Exception("Can't send mail to: ".implode(',', $failedRecipients));
        }

        return new Response($app['translator']->trans('The event with the title %title% was published.', array('%title%' => $event['description_title'])));

    }

    /**
     * Controller: The admin reject the event and delete all data
     *
     * @param Application $app
     * @param string $guid
     * @throws \Exception
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function controllerAdminReject(Application $app, $guid)
    {
        $this->initParameters($app);

        $ProposeData = new ProposeData($app);
        if (false === ($propose = $ProposeData->selectAdminGUID($guid))) {
            return new Response("The GUID $guid does not exists!");
        }

        // get the actual URL
        $actual_url = $this->getCMSpageURL();

        // set the permanent link as CMS page URI!
        $this->setCMSpageURL($propose['command_url']);
        // redirect to itself
        $this->setRedirectRoute('/event/propose/reject/'.$guid);
        // activate redirection
        $this->setRedirectActive(true);

        if ($actual_url != $propose['command_url']) {
            // reload this page to fold the iframe into the CMS before performing anything
            $app['monolog']->addInfo(sprintf("Reload route %s into CMS URL %s before performing anything",
                '/event/propose/reject/'.$guid, $this->getCMSpageURL()));
            return $app['twig']->render($app['utils']->getTemplateFile('@phpManufaktur/Basic/Template', 'kitcommand/reload.twig'),
                array('basic' => $this->getBasicSettings()));
        }

        if ($propose['admin_status'] != 'PENDING') {
            return new Response($app['translator']->trans('This activation link was already used and is no longer valid!'));
        }


        // we have to remove all data of this proposed event
        if ($propose['new_organizer_id'] > 0) {
            $data = array(
                'contact' => array(
                    'contact_id' => $propose['new_organizer_id'],
                    'contact_status' => 'DELETED'
                )
            );
            if (false === ($app['contact']->update($data, $propose['new_organizer_id']))) {
                throw new \Exception(strip_tags($app['contact']->getAlert()));
            }
        }
        if ($propose['new_location_id'] > 0) {
            $data = array(
                'contact' => array(
                    'contact_id' => $propose['new_location_id'],
                    'contact_status' => 'DELETED'
                )
            );
            if (false === ($app['contact']->update($data, $propose['new_location_id']))) {
                throw new \Exception(strip_tags($app['contact']->getAlert()));
            }
        }

        $contact = $app['contact']->selectOverview($propose['submitter_id']);

        $EventData = new Event($app);

        $data = array(
            'event_status' => 'DELETED'
        );
        $EventData->updateEvent($data, $propose['new_event_id']);

        $event = $EventData->selectEvent($propose['new_event_id'], false);

        // set the status of the propose to CANCELLED
        $data = array(
            'admin_status' => 'REJECTED',
            'admin_status_when' => date('Y-m-d H:i:s')
        );
        $ProposeData->update($propose['id'], $data);

        $body = $this->app['twig']->render($this->app['utils']->getTemplateFile(
            '@phpManufaktur/Event/Template',
            'command/mail/propose/submitter.rejected.twig',
            $this->getPreferredTemplateStyle()),
            array(
                'event' => $event
                )
            );

        // send a email to the contact
        $message = \Swift_Message::newInstance()
        ->setSubject($this->app['translator']->trans('Proposed event: %event%', array('%event%' => $event['description_title'])))
        ->setFrom(array(SERVER_EMAIL_ADDRESS => SERVER_EMAIL_NAME))
        ->setTo(array($contact['communication_email'] => $contact['contact_name']))
        ->setBody($body)
        ->setContentType('text/html');
        // send the message
        $failedRecipients = null;
        if (!$this->app['mailer']->send($message, $failedRecipients))  {
            throw new \Exception("Can't send mail to: ".implode(',', $failedRecipients));
        }

        return new Response($app['translator']->trans('The event with the title %title% was rejected.', array('%title%' => $event['description_title'])));
    }

    /**
     * Send a email to the administrator to activate the event with an activation link
     *
     * @param integer $submitter_id
     * @throws \Exception
     */
    protected function sendAdminActivation($propose_id)
    {
        $ProposeData = new ProposeData($this->app);
        if (false === ($propose = $ProposeData->select($propose_id))) {
            throw new \Exception('Missing the propose data record');
        }

        if (false === ($contact = $this->app['contact']->selectOverview($propose['submitter_id']))) {
            throw new \Exception('Missing contact record for the submitter ID '.$propose['submitter_id']);
        }

        $EventData = new Event($this->app);
        if (false === ($event = $EventData->selectEvent($propose['new_event_id'], false))) {
            throw new \Exception('Missing the event data');
        }

        $body = $this->app['twig']->render($this->app['utils']->getTemplateFile(
            '@phpManufaktur/Event/Template',
            'command/mail/propose/admin.twig',
            $this->getPreferredTemplateStyle()),
            array(
                'contact' => $contact,
                'propose' => $propose,
                'event' => $event,
                'action' => array(
                    'link' => array(
                        'publish' => FRAMEWORK_URL.'/event/propose/publish/'.$propose['admin_guid'],
                        'reject' => FRAMEWORK_URL.'/event/propose/reject/'.$propose['admin_guid']
                    )
                )
            ));

        $ConfigData = new Configuration($this->app);
        $config = $ConfigData->getConfiguration();

        $mail_to = $config['event']['propose']['confirm']['mail_to'];
        if (in_array('provider', $mail_to)) {
            $mail_to[] = SERVER_EMAIL_ADDRESS;
            unset($mail_to[array_search('provider', $mail_to)]);
        }

        // send a email to the contact
        $message = \Swift_Message::newInstance()
        ->setSubject($this->app['translator']->trans('Proposed event: %event%', array('%event%' => $event['description_title'])))
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
    }

    public function controllerSubmitterActivate(Application $app, $guid)
    {
        $this->initParameters($app);

        $ProposeData = new ProposeData($app);
        if (false === ($propose = $ProposeData->selectSubmitterGUID($guid))) {
            return new Response("The GUID $guid does not exists!");
        }

        // get the actual URL
        $actual_url = $this->getCMSpageURL();

        // set the permanent link as CMS page URI!
        $this->setCMSpageURL($propose['command_url']);
        // redirect to itself
        $this->setRedirectRoute('/event/propose/confirm/'.$guid);
        // activate redirection
        $this->setRedirectActive(true);

        if ($actual_url != $propose['command_url']) {
            // reload this page to fold the iframe into the CMS before performing anything
            $app['monolog']->addInfo(sprintf("Reload route %s into CMS URL %s before performing anything",
                '/event/propose/confirm/'.$guid, $this->getCMSpageURL()));
            return $app['twig']->render($app['utils']->getTemplateFile('@phpManufaktur/Basic/Template', 'kitcommand/reload.twig'),
                array('basic' => $this->getBasicSettings()));
        }

        if ($propose['submitter_status'] != 'PENDING') {
            return new Response($app['translator']->trans('This activation link was already used and is no longer valid!'));
        }

        // confirm the submitter contact record!
        $submitter = $app['contact']->select($propose['submitter_id']);
        if ($submitter['contact']['contact_id'] < 1) {
            throw new \Exception('The submitter contact record with the ID '.$propose['submitter_id'].' does not exists!');
        }
        if ($submitter['contact']['contact_status'] == 'PENDING') {
            $data = array(
                'contact' => array(
                    'contact_id' => $propose['submitter_id'],
                    'contact_status' => 'ACTIVE'
                )
            );
            if (false === ($app['contact']->update($data, $propose['submitter_id']))) {
                throw new \Exception(strip_tags($app['contact']->getAlert()));
            }
        }
        elseif ($submitter['contact']['contact_status'] != 'ACTIVE') {
            // this contact is not active, perhaps a problem?
            $app['monolog']->addCritical('The contact with the ID '.$propose['submitter_id'].' is not ACTIVE but try to execute an activation link.',
                array(__METHOD__, __LINE__));
            return new Response($app['translator']->trans('Your contact record is locked, so we can not perform any action. Please contact the administrator'));
        }

        // change propose status
        $data = array(
            'submitter_status' => 'CONFIRMED',
            'submitter_status_when' => date('Y-m-d H:i:s'),
            'admin_status' => 'PENDING',
            'admin_status_when' => date('Y-m-d H:i:s')
        );
        $ProposeData->update($propose['id'], $data);

        $EventData = new Event($app);
        if (false === ($event = $EventData->selectEvent($propose['new_event_id']))) {
            throw new \Exception('Missing the event data');
        }

        // send a email to the administrator
        $this->sendAdminActivation($propose['id']);

        return $this->app['twig']->render($this->app['utils']->getTemplateFile(
            '@phpManufaktur/Event/Template',
            "command/event.propose.submitter.confirm.twig",
            $this->getPreferredTemplateStyle()),
            array(
                'basic' => $this->getBasicSettings(),
                'propose' => $propose,
                'event' => $event
            ));
    }

    /**
     * Controller: Submitter cancel the proposed event and stop publishing
     *
     * @param Application $app
     * @param string $guid
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function controllerSubmitterCancelled(Application $app, $guid)
    {
        $this->initParameters($app);

        $ProposeData = new ProposeData($app);
        if (false === ($propose = $ProposeData->selectSubmitterGUID($guid))) {
            return new Response("The GUID $guid does not exists!");
        }

        // get the actual URL
        $actual_url = $this->getCMSpageURL();

        // set the permanent link as CMS page URI!
        $this->setCMSpageURL($propose['command_url']);
        // redirect to itself
        $this->setRedirectRoute('/event/propose/cancel/'.$guid);
        // activate redirection
        $this->setRedirectActive(true);

        if ($actual_url != $propose['command_url']) {
            // reload this page to fold the iframe into the CMS before performing anything
            $app['monolog']->addInfo(sprintf("Reload route %s into CMS URL %s before performing anything",
                '/event/propose/cancel/'.$guid, $this->getCMSpageURL()));
            return $app['twig']->render($app['utils']->getTemplateFile('@phpManufaktur/Basic/Template', 'kitcommand/reload.twig'),
                array('basic' => $this->getBasicSettings()));
        }

        if ($propose['submitter_status'] != 'PENDING') {
            return new Response($app['translator']->trans('This activation link was already used and is no longer valid!'));
        }

        // also if the event is cancelled we confirm the submitter contact record!
        $submitter = $app['contact']->select($propose['submitter_id']);
        if ($submitter['contact']['contact_id'] < 1) {
            throw new \Exception('The submitter contact record with the ID '.$propose['submitter_id'].' does not exists!');
        }
        if ($submitter['contact']['contact_status'] == 'PENDING') {
            $data = array(
                'contact' => array(
                    'contact_id' => $propose['submitter_id'],
                    'contact_status' => 'ACTIVE'
                )
            );
            if (false === ($app['contact']->update($data, $propose['submitter_id']))) {
                throw new \Exception(strip_tags($app['contact']->getAlert()));
            }
        }
        elseif ($submitter['contact']['contact_status'] != 'ACTIVE') {
            // this contact is not active, perhaps a problem?
            $app['monolog']->addCritical('The contact with the ID '.$propose['submitter_id'].' is not ACTIVE but try to execute an activation link.',
                array(__METHOD__, __LINE__));
            return new Response($app['translator']->trans('Your contact record is locked, so we can not perform any action. Please contact the administrator'));
        }

        // we have to remove all data of this proposed event
        if ($propose['new_organizer_id'] > 0) {
            $data = array(
                'contact' => array(
                    'contact_id' => $propose['new_organizer_id'],
                    'contact_status' => 'DELETED'
                )
            );
            if (false === ($app['contact']->update($data, $propose['new_organizer_id']))) {
                throw new \Exception(strip_tags($app['contact']->getAlert()));
            }
        }
        if ($propose['new_location_id'] > 0) {
            $data = array(
                'contact' => array(
                    'contact_id' => $propose['new_location_id'],
                    'contact_status' => 'DELETED'
                )
            );
            if (false === ($app['contact']->update($data, $propose['new_location_id']))) {
                throw new \Exception(strip_tags($app['contact']->getAlert()));
            }
        }

        $EventData = new Event($app);
        $data = array(
            'event_status' => 'DELETED'
        );
        $EventData->updateEvent($data, $propose['new_event_id']);

        // set the status of the propose to CANCELLED
        $data = array(
            'submitter_status' => 'CANCELLED',
            'submitter_status_when' => date('Y-m-d H:i:s')
        );
        $ProposeData->update($propose['id'], $data);

        // get the event data
        $event = $EventData->selectEvent($propose['new_event_id'], false);

        return $this->app['twig']->render($this->app['utils']->getTemplateFile(
            '@phpManufaktur/Event/Template',
            "command/event.propose.submitter.cancel.twig",
            $this->getPreferredTemplateStyle()),
            array(
                'basic' => $this->getBasicSettings(),
                'propose' => $propose,
                'event' => $event
            ));
    }

    /**
     * Send a email to the submitter to activate the event with an activation link
     *
     * @param integer $submitter_id
     * @throws \Exception
     */
    protected function sendSubmitterConfirmation($submitter_id)
    {
        if (false === ($contact = $this->app['contact']->selectOverview($submitter_id))) {
            throw new \Exception('Missing contact recorde for the submitter ID '.$submitter_id);
        }

        $ProposeData = new ProposeData($this->app);
        if (false === ($propose = $ProposeData->select($this->app['session']->get('propose_id')))) {
            throw new \Exception('Missing the propose data record (Session invalid?)');
        }

        $EventData = new Event($this->app);
        if (false === ($event = $EventData->selectEvent($this->app['session']->get('event_id'), false))) {
            throw new \Exception('Missing the event data (Session invalid?)');
        }

        $body = $this->app['twig']->render($this->app['utils']->getTemplateFile(
            '@phpManufaktur/Event/Template',
            'command/mail/propose/submitter.twig',
            $this->getPreferredTemplateStyle()),
            array(
                'contact' => $contact,
                'propose' => $propose,
                'event' => $event,
                'action' => array(
                    'link' => array(
                        'publish' => FRAMEWORK_URL.'/event/propose/confirm/'.$propose['submitter_guid'],
                        'cancel' => FRAMEWORK_URL.'/event/propose/cancel/'.$propose['submitter_guid']
                    )
                )
            ));

        // send a email to the contact
        $message = \Swift_Message::newInstance()
        ->setSubject($this->app['translator']->trans('Proposed event: %event%', array('%event%' => $event['description_title'])))
        ->setFrom(array(SERVER_EMAIL_ADDRESS => SERVER_EMAIL_NAME))
        ->setTo(array($contact['communication_email'] => $contact['contact_name']))
        ->setBody($body)
        ->setContentType('text/html');
        // send the message
        $failedRecipients = null;
        if (!$this->app['mailer']->send($message, $failedRecipients))  {
            throw new \Exception("Can't send mail to: ".implode(',', $failedRecipients));
        }
    }

    /**
     * Controller check the email address of the submitter, send an activation
     * link and display a dialog to change/complete the user data
     *
     * @param Application $app
     * @throws \Exception
     * @return string
     */
    public function controllerSubmitterConfirm(Application $app)
    {
        $this->initParameters($app);

        // get the form fields
        $request = $this->app['request']->request->get('form', array());
        if (!isset($request['email'])) {
            throw new \Exception("Missing the email address!");
        }
        if (!isset($request['email_type'])) {
            throw new \Exception('Missing the email_type!');
        }

        if (false === ($contact_id = $app['contact']->existsLogin($request['email']))) {
            // create a new contact
            $data = array(
                'contact' => array(
                    'contact_id' => -1,
                    'contact_type' => $request['email_type'],
                    'contact_status' => 'PENDING'
                ),
                'person' => array(
                    array(
                        'person_id' => -1,
                        'contact_id' => -1
                    )
                ),
                'company' => array(
                    array(
                        'company_id' => -1,
                        'contact_id' => -1
                    )
                ),
                'communication' => array(
                    array(
                        'communication_id' => -1,
                        'contact_id' => -1,
                        'communication_type' => 'EMAIL',
                        'communication_usage' => 'PRIMARY',
                        'communication_value' => strtolower($request['email'])
                    )
                )
            );
            if (!$app['contact']->insert($data, $contact_id)) {
                throw new \Exception(strip_tags($app['contact']->getAlert()));
            }
        }

        // update the propose record
        $ProposeData = new ProposeData($app);
        $data = array(
            'submitter_id' => $contact_id,
            'command_url' => $this->getCMSpageURL(),
            'submitter_status_when' => date('Y-m-d H:i:s'),
            'submitted_when' => date('Y-m-d H:i:s')
        );
        $ProposeData->update($app['session']->get('propose_id'), $data);

        // send confirmation mail to the submitter
        $this->sendSubmitterConfirmation($contact_id);

        // show confirmation dialog

        return $this->app['twig']->render($this->app['utils']->getTemplateFile(
            '@phpManufaktur/Event/Template',
            "command/event.propose.submitter.send.twig",
            $this->getPreferredTemplateStyle()),
            array(
                'basic' => $this->getBasicSettings(),
            ));
    }

    /**
     * Controller confirm the new event and display a dialog to type in the
     * email address of the submitter
     *
     * @param Application $app
     */
    public function controllerSubmitter(Application $app)
    {
        $this->initParameters($app);

        // get the form fields
        $contact = $this->app['request']->request->get('form', array());

        $fields = $this->app['form.factory']->createBuilder('form')
        ->add('email', 'email', array(
            'data' => isset($contact['email']) ? $contact['email'] : ''
        ))
        ->add('email_type', 'choice', array(
            'expanded' => true,
            'multiple' => false,
            'required' => true,
            'choices' => array(
                'PERSON' => $this->app['translator']->trans('personal email address'),
                'COMPANY' => $this->app['translator']->trans('regular email address of a company, institution or association')
            ),
            'label' => 'email usage',
            'data' => isset($contact['email_type']) ? $contact['email_type'] : 'PERSON'
        ))
        ;

        $form = $fields->getForm();

        return $this->app['twig']->render($this->app['utils']->getTemplateFile(
            '@phpManufaktur/Event/Template',
            "command/event.propose.submitter.twig",
            $this->getPreferredTemplateStyle()),
            array(
                'basic' => $this->getBasicSettings(),
                'form' => $form->createView(),
                'route' => array(
                    'submitter' => array(
                        'confirm' => '/event/propose/submitter/confirm'
                    )
                )
            ));
    }

    /**
     * Controller to check the event data, save the record and step to
     * the proposer record
     *
     * @param Application $app
     * @return string dialog
     */
    public function controllerEventCheck(Application $app)
    {
        $this->initParameters($app);

        // get the form fields
        $event = $app['request']->request->get('form', array());

        // get the configuration
        $config = $app['utils']->readConfiguration(MANUFAKTUR_PATH.'/Event/config.event.json');

        // check the event data
        if ($config['event']['description']['title']['required'] &&
            (!isset($event['description_title']) || (strlen(trim($event['description_title'])) < $config['event']['description']['title']['min_length']))) {
            $this->setAlert('Please type in a title with %minimum% characters at minimum.',
                array('%minimum%' => $config['event']['description']['title']['min_length']));
            return $this->controllerEvent($app);
        }
        elseif (!isset($event['description_title'])) {
            $event['description_title'] = '';
        }
        if ($config['event']['description']['short']['required'] &&
            (!isset($event['description_short']) || (strlen(trim($event['description_short'])) < $config['event']['description']['short']['min_length']))) {
            $this->setAlert('Please type in a short description with %minimum% characters at minimum.',
                array('%minimum%' => $config['event']['description']['short']['min_length']));
            return $this->controllerEvent($app);
        }
        elseif (!isset($event['description_short'])) {
            $event['description_short'] = '';
        }
        if ($config['event']['description']['long']['required'] &&
            (!isset($event['description_long']) || (strlen(trim($event['description_long'])) < $config['event']['description']['long']['min_length']))) {
            $this->setAlert('Please type in a long description with %minimum% characters at minimum.',
                array('%minimum%' => $config['event']['description']['long']['min_length']));
            return $this->controllerEvent($app);
        }
        elseif (!isset($event['description_long'])) {
            $event['description_long'] = '';
        }

        if (!$config['event']['date']['event_date_from']['allow_date_in_past'] &&
            (strtotime($event['event_date_from']) < time())) {
            $this->setAlert('It is not allowed that the event start in the past!');
            return $this->controllerEvent($app);
        }

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
            $this->setAlert('The event start date is behind the event end date!');
            return $this->controllerEvent($app);
        }
        if (strtotime($event['event_publish_to']) < strtotime($event['event_date_from'])) {
            $this->setAlert('The publishing date ends before the event starts, this is not allowed!');
            return $this->controllerEvent($app);
        }
        if (strtotime($event['event_deadline']) > strtotime($event['event_date_from'])) {
            $this->setAlert('The deadline ends after the event start date!');
            return $this->controllerEvent($app);
        }

        // ok - save the event
        $data = array(
            'group_id' => $this->app['session']->get('group_id'),
            'event_type' => 'EVENT',
            'event_organizer' => $this->app['session']->get('organizer_id'),
            'event_location' => $this->app['session']->get('location_id'),
            'event_costs' => isset($event['event_costs']) ? $this->app['utils']->str2float($event['event_costs']) : 0,
            'event_participants_max' => isset($event['event_participants_max']) ? $this->app['utils']->str2int($event['event_participants_max']) : -1,
            'event_status' => 'LOCKED',
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

        // create a new event
        $EventData = new Event($app);
        $event_id = -1;

        $EventData->insertEvent($data, $event_id);

        // save event ID to session
        $this->app['session']->set('event_id', $event_id);

        // save the data to the propose record
        $ProposeData = new ProposeData($app);
        $data = array(
            'new_event_id' => $event_id
        );
        if (null == ($propose_id = $app['session']->get('propose_id'))) {
            // create a new propose record
            $ProposeData->insert($data, $propose_id);
            // set the session for further usage
            $app['session']->set('propose_id', $propose_id);
        }
        else {
            // update existing propose record
            $ProposeData->update($propose_id, $data);
        }

        return $this->controllerSubmitter($app);
    }

    /**
     * Controller to create a event
     *
     * @param Application $app
     * @return string dialog
     */
    public function controllerEvent(Application $app)
    {
        $this->initParameters($app);

        // get the organizer data
        if (false === ($organizer = $app['contact']->selectOverview($this->app['session']->get('organizer_id', -1)))) {
            throw new \Exception("The Organizer does not exists!");
        }
        // get the location data
        if (false === ($location = $app['contact']->selectOverview($this->app['session']->get('location_id', -1)))) {
            throw new \Exception("The Location does not exists!");
        }

        $ConfigData = new Configuration($app);
        $config = $ConfigData->getConfiguration();

        // get the form fields
        $event = $this->app['request']->request->get('form', array());

        $fields = $this->app['form.factory']->createBuilder('form')
        ->add('event_id', 'hidden', array(
            'data' => -1
        ))
        ->add('event_organizer', 'hidden', array(
            'data' => $this->app['session']->get('organizer_id', -1)
        ))
        ->add('event_location', 'hidden', array(
            'data' => $this->app['session']->get('location_id', -1)
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

        return $this->app['twig']->render($this->app['utils']->getTemplateFile(
            '@phpManufaktur/Event/Template',
            "command/event.propose.event.create.twig",
            $this->getPreferredTemplateStyle()),
            array(
                'parameter' => $this->getCommandParameters(),
                'basic' => $this->getBasicSettings(),
                'form' => $form->createView(),
                'organizer' => $organizer,
                'location' => $location,
                'route' => array(
                    'event' => array(
                        'check' => '/event/propose/event/check'
                    )
                ),
                'config' => $config
            ));
    }

    /**
     * Controller set the contact ID for the location and redirect to search
     * a organizer record
     *
     * @param Application $app
     * @param integer $contact_id
     * @return string
     */
    public function controllerLocationID(Application $app, $contact_id)
    {
        $app['session']->set('location_id', $contact_id);
        return $this->controllerSearchOrganizer($app);
    }

    /**
     * Controller to select a location
     *
     * @param Application $app
     * @throws \Exception
     * @return string
     */
    public function controllerSelectLocation(Application $app)
    {
        $this->initParameters($app);

        // get the form fields
        $form_request = $this->app['request']->request->get('form', array());

        // check the search term
        if (!isset($form_request['new_location']) && (!isset($form_request['search']) || empty($form_request['search']))) {
            $this->setAlert('Please search for for a location or select the checkbox to create a new one.');
            return $this->controllerSearchOrganizer($app);
        }

        if (isset($form_request['new_location'])) {
            // create a new location record
            return $this->createContact('location', $this->app['session']->get('group_id'));
        }

        // get the TAGS assigned to the location
        $LocationTag = new LocationTag($app);
        if (false === ($tags = $LocationTag->selectTagNamesByGroupID($this->app['session']->get('group_id')))) {
            throw new \Exception("Missing the location TAG names for the group ID ".$this->app['session']->get('group_id'));
        }

        $Overview = new Overview($app);
        if (false === ($locations = $Overview->searchContact($form_request['search'], $tags, 'ACTIVE', '='))) {
            // no search result
            $this->setAlert('There exists no locations who fits to the search term %search%', array('%search%' => $form_request['search']));
            return $this->controllerSearchLocation($app);
        }

        // show the list with the matching organizers
        return $this->app['twig']->render($this->app['utils']->getTemplateFile(
            '@phpManufaktur/Event/Template',
            "command/event.propose.location.select.twig",
            $this->getPreferredTemplateStyle()),
            array(
                'basic' => $this->getBasicSettings(),
                'locations' => $locations,
                'search_term' => $form_request['search'],
                'route' => array(
                    'location' => array(
                        'search' => '/event/propose/location/search',
                        'create' => '/event/propose/location/create/group/'.$this->app['session']->get('group_id'),
                        'id' => '/event/propose/location/id/{contact_id}'
                    )
                )
            ));

    }

    /**
     * Controller display a dialog to search for a location or create a new one
     *
     * @param Application $app
     */
    public function controllerSearchLocation(Application $app)
    {
        $this->initParameters($app);

        // get the parameters
        $parameter = $this->getCommandParameters();

        // check if a event group isset
        $form_request = $this->app['request']->request->get('form', array());
        if (isset($form_request['event_group'])) {
            $group_id = $form_request['event_group'];
        }
        elseif (isset($parameter['group'])) {
            if (is_numeric($parameter['group'])) {
                $group_id = intval($parameter['group']);
            }
            else {
                $EventGroup = new EventGroup($app);
                if (false === ($group_id = $EventGroup->getGroupID($parameter['group']))) {
                    $Message = new Message($app);
                    return $Message->render($this->app['translator']->trans('The event group with the name %group% does not exists!'),
                        array('%group%' => $parameter['group']), 'group[]', array(), true);
                }
            }
        }

        $app['session']->set('group_id', $group_id);

        $fields = $this->app['form.factory']->createBuilder('form')
        ->add('search', 'text', array(
            'label' => 'Search Location',
            'required' => false
        ))
        ->add('event_group', 'hidden', array(
            'data' => $group_id
        ))
        ->add('new_location', 'checkbox', array(
            'required' => false
        ));
        $form = $fields->getForm();

        return $this->app['twig']->render($this->app['utils']->getTemplateFile(
            '@phpManufaktur/Event/Template',
            "command/event.propose.location.search.twig",
            $this->getPreferredTemplateStyle()),
            array(
                'basic' => $this->getBasicSettings(),
                'form' => $form->createView(),
                'route' => array(
                    'location' => array(
                        'select' => '/event/propose/location/select'
                    )
                )
            ));
    }

    /**
     * Controller to get the organizer ID and step to the location search
     *
     * @param Application $app
     * @param integer $contact_id
     * @return string
     */
    public function controllerOrganizerID(Application $app, $contact_id)
    {
        $app['session']->set('organizer_id', $contact_id);
        return $this->controllerEvent($app);
        //return $this->controllerSearchLocation($app);
    }

    /**
     * Check a new contact, insert it, set session and redirect to the next step
     *
     * @param Application $app
     * @throws \Exception
     * @return string
     */
    public function controllerContactCheck(Application $app)
    {
        $this->initParameters($app);

        $Configuration = new Configuration($app);
        $config = $Configuration->getConfiguration();

        $request = $app['request']->request->get('form');

        // some checks before creating the contact
        if ($request['contact_type'] == 'PERSON') {
            // check for a PERSON contact
            if ((($request['create_type'] == 'location') &&
                $config['event']['location']['required']['name'] &&
                empty($request['person_last_name'])) ||
                (($request['create_type'] == 'organizer') && empty($request['person_last_name']))) {
                $this->setAlert('You have selected <i>natural person</i> as contact type, so please give us the last name of the person.');
                return $this->createContact($request['create_type'], $request['group_id']);
            }
        }
        else {
            // check for COMPANY contact
            if ((($request['create_type'] == 'location') &&
                $config['event']['location']['required']['name'] &&
                empty($request['company_name'])) ||
                (($request['create_type'] == 'organizer') && empty($request['company_name']))) {
                $this->setAlert('You have selected <i>Company, Institution or Association</i> as contact type, so please give us the name');
                return $this->createContact($request['create_type'], $request['group_id']);
            }
        }

        if (empty($request['email']) && empty($request['phone']) && empty($request['url'])) {
            if (($request['create_type'] == 'location') && !$config['event']['location']['required']['communication']) {
                // skip ...
            }
            else {
                // at least we need one communication way
                $this->setAlert('At least we need one communication channel, so please tell us a email address, phone or a URL');
                return $this->createContact($request['create_type'], $request['group_id']);
            }
        }

        if ($request['create_type'] == 'organizer') {
            // get the TAGS assigned to the organizer
            $OrganizerTag = new OrganizerTag($app);
            if (false === ($group_tags = $OrganizerTag->selectTagNamesByGroupID($request['group_id']))) {
                throw new \Exception("Missing the organizer TAG names for the group ID ".$request['group_id']);
            }
        }
        else {
            // get the TAGS assigned to the organizer
            $LocationTag = new LocationTag($app);
            if (false === ($group_tags = $LocationTag->selectTagNamesByGroupID($request['group_id']))) {
                throw new \Exception("Missing the location TAG names for the group ID ".$request['group_id']);
            }
        }

        $tags = array();
        foreach ($group_tags as $tag) {
            $tags[] = array(
                'contact_id' => -1,
                'tag_name' => $tag
            );
        }

        if (!empty($request['email'])) {
            $login = strtolower($request['email']);
        }
        elseif ($request['contact_type'] == 'PERSON') {
            if (!empty($request['person_last_name'])) {
                $login = sprintf('%s_%s', strtolower($request['person_last_name']), date('zHi'));
            }
            elseif (!empty($request['address_zip'])) {
                // create fragmentary address
                $login = sprintf('%s_%s_%s%s', $request['address_zip'], strtolower($request['address_city']), date('zHi'),
                    $config['contact']['fragmentary']['login']['suffix']);
            }
            else {
                // create fragmentary address
                $login = sprintf('%s_%s%s', strtolower($request['address_city']), date('zHi'),
                    $config['contact']['fragmentary']['login']['suffix']);
            }
        }
        else {
            if (!empty($request['company_name'])) {
                $login = sprintf('%s_%s', strtolower($request['company_name']), date('zHi'));
            }
            elseif (!empty($request['address_zip'])) {
                // create fragmentary address
                $login = sprintf('%s_%s_%s%s', $request['address_zip'], strtolower($request['address_city']), date('zHi'),
                    $config['contact']['fragmentary']['login']['suffix']);
            }
            else {
                // create fragmentary address
                $login = sprintf('%s_%s%s', strtolower($request['address_city']), date('zHi'),
                    $config['contact']['fragmentary']['login']['suffix']);
            }
        }

        $data = array(
            'contact' => array(
                'contact_id' => -1,
                'contact_type' => $request['contact_type'],
                'contact_name' => null,
                'contact_login' => $login,
                'contact_status' => 'PENDING'
            ),
            'tag' => $tags,
            'company' => array(
                array(
                    'company_id' => -1,
                    'contact_id' => -1,
                    'company_name' => $request['company_name'],
                    'company_department' => $request['company_department']
                )
            ),
            'person' => array(
                array(
                    'person_id' => -1,
                    'contact_id' => -1,
                    'person_gender' => $request['person_gender'],
                    'person_first_name' => $request['person_first_name'],
                    'person_last_name' => $request['person_last_name']
                )
            ),
            'communication' => array(
                array(
                    'communication_id' => -1,
                    'contact_id' => -1,
                    'communication_type' => 'EMAIL',
                    'communication_usage' => 'PRIMARY',
                    'communication_value' => strtolower($request['email'])
                ),
                array(
                    'communication_id' => -1,
                    'contact_id' => -1,
                    'communication_type' => 'PHONE',
                    'communication_usage' => 'PRIMARY',
                    'communication_value' => $request['phone']
                ),
                array(
                    'communication_id' => -1,
                    'contact_id' => -1,
                    'communication_type' => 'URL',
                    'communication_usage' => 'PRIMARY',
                    'communication_value' => $request['url']
                ),

            ),
            'address' => array(
                array(
                    'address_id' => -1,
                    'contact_id' => -1,
                    'address_type' => 'PRIMARY',
                    'address_street' => $request['address_street'],
                    'address_zip' => $request['address_zip'],
                    'address_city' => $request['address_city'],
                    'address_state' => $request['address_state'],
                    'address_country_code' => $request['address_country']
                )
            ),
            'note' => array(
                array(
                    'note_id' => -1,
                    'contact_id' => -1,
                    'note_title' => 'Remarks',
                    'note_type' => 'TEXT',
                    'note_content' => $request['note']
                )
            )
        );

        $contact_id = -1;
        if (false === ($app['contact']->insert($data, $contact_id))) {
            // don't return as message to to visitor, better throw an error
            throw new \Exception(strip_tags($app['contact']->getAlert()));
        }

        $ProposeData = new ProposeData($app);

        if ($request['create_type'] == 'organizer') {
            // set session for 'organizer_id'
            $app['session']->set('organizer_id', $contact_id);
            $data = array(
                'new_organizer_id' => $contact_id
            );
            if (null == ($propose_id = $app['session']->get('propose_id'))) {
                // create a new propose record
                $ProposeData->insert($data, $propose_id);
                // set the session for further usage
                $app['session']->set('propose_id', $propose_id);
            }
            else {
                // update existing propose record
                $ProposeData->update($propose_id, $data);
            }
            return $this->controllerEvent($app);
        }
        else {
            // set session for 'location_id'
            $app['session']->set('location_id', $contact_id);
            $data = array(
                'new_location_id' => $contact_id
            );
            if (null == ($propose_id = $app['session']->get('propose_id'))) {
                // create a new propose record
                $ProposeData->insert($data, $propose_id);
                // set the session for further usage
                $app['session']->set('propose_id', $propose_id);
            }
            else {
                // update existing propose record
                $ProposeData->update($propose_id, $data);
            }
            return $this->controllerSearchOrganizer($app);
        }
    }

    /**
     * Dialog to create a new contact of type 'organizer' or 'location' for the
     * specified event group ID
     *
     * @param string $type maybe 'organizer' or 'location'
     * @param integer $group_id event group ID
     * @return string contact dialog
     */
    protected function createContact($type, $group_id)
    {
        $request = $this->app['request']->request->get('form');

        $ContactConfiguration = new ContactConfiguration($this->app);
        $contactConfig = $ContactConfiguration->getConfiguration();

        $zip_required = false;
        if ($type == 'location') {
            $Configuration = new Configuration($this->app);
            $config = $Configuration->getConfiguration();
            $zip_required = $config['event']['location']['required']['zip'];
        }

        $fields = $this->app['form.factory']->createBuilder('form')
        ->add('create_type', 'hidden', array(
            'data' => $type
        ))
        ->add('group_id', 'hidden', array(
            'data' => $group_id
        ))
        ->add('contact_type', 'choice', array(
            'expanded' => true,
            'multiple' => false,
            'required' => true,
            'choices' => array(
                'PERSON' => 'natural person',
                'COMPANY' => 'company, institution or association'
            ),
            'data' => isset($request['contact_type']) ? $request['contact_type'] : 'COMPANY'
         ))
        // person - visible form fields
        ->add('person_gender', 'choice', array(
            'choices' => array('MALE' => 'male', 'FEMALE' => 'female'),
            'expanded' => true,
            'label' => 'Gender',
            'data' => isset($request['person_gender']) ? $request['person_gender'] : 'MALE',
            'required' => false
        ))
        ->add('person_first_name', 'text', array(
            'required' => false,
            'label' => 'First name',
            'data' => isset($request['person_first_name']) ? $request['person_first_name'] : ''
        ))
        ->add('person_last_name', 'text', array(
            'required' => false,
            'label' => 'Last name',
            'data' => isset($request['person_last_name']) ? $request['person_last_name'] : ''
        ))
        ->add('company_name', 'text', array(
            'required' => false,
            'label' => 'Company name',
            'data' => isset($request['company_name']) ? $request['company_name'] : ''
        ))
        ->add('company_department', 'text', array(
            'required' => false,
            'label' => 'Company department',
            'data' => isset($request['company_department']) ? $request['company_department'] : ''
        ))
        ->add('email', 'email', array(
            'required' => false,
            'data' => isset($request['email']) ? $request['email'] : ''
        ))
        ->add('phone', 'text', array(
            'required' => false,
            'data' => isset($request['phone']) ? $request['phone'] : ''
        ))
        ->add('url', 'url', array(
            'required' => false,
            'label' => 'Homepage',
            'data' => isset($request['url']) ? $request['url'] : ''
        ))
        ->add('address_street', 'text', array(
            'required' => false,
            'label' => 'Street',
            'data' => isset($request['address_street']) ? $request['address_street'] : ''
        ))
        ->add('address_zip', 'text', array(
            'required' => $zip_required,
            'label' => 'Zip',
            'data' => isset($request['address_zip']) ? $request['address_zip'] : ''
        ))
        ->add('address_city', 'text', array(
            'required' => true,
            'label' => 'City',
            'data' => isset($request['address_city']) ? $request['address_city'] : ''
        ))
        ->add('address_state', 'text', array(
            'required' => false,
            'label' => 'State',
            'data' => isset($request['address_state']) ? $request['address_state'] : ''
        ))
        ->add('address_country', 'choice', array(
            'choices' => $this->app['contact']->getCountryArrayForTwig(),
            'empty_value' => '- please select -',
            'expanded' => false,
            'multiple' => false,
            'required' => true,
            'label' => 'Country',
            'data' => isset($request['address_country']) ? $request['address_country'] : null,
            'preferred_choices' => $contactConfig['countries']['preferred']
        ))
        ->add('note', 'textarea', array(
            'required' => false,
            'data' => isset($request['note']) ? $request['note'] : ''
        ))
        ;

        $form = $fields->getForm();

        return $this->app['twig']->render($this->app['utils']->getTemplateFile(
            '@phpManufaktur/Event/Template',
            "command/event.propose.contact.create.twig",
            $this->getPreferredTemplateStyle()),
            array(
                'parameter' => $this->getCommandParameters(),
                'basic' => $this->getBasicSettings(),
                'form' => $form->createView(),
                'route' => array(
                    'contact' => array(
                        'check' => '/event/propose/contact/check'
                    )
                )
            ));
    }

    /**
     * Controller to create a new contact of type 'organizer'
     *
     * @param Application $app
     * @param integer $group_id event group ID
     * @return string dialog to create a contact
     */
    public function controllerCreateOrganizer(Application $app, $group_id)
    {
        $this->initParameters($app);
        return $this->createContact('organizer', $group_id);
    }

    /**
     * Controller to create a new contact of type 'location'
     *
     * @param Application $app
     * @param integer $group_id
     * @return string
     */
    public function controllerCreateLocation(Application $app, $group_id)
    {
        $this->initParameters($app);
        return $this->createContact('location', $group_id);
    }

    /**
     * Use a unkown organizer instead of a regular record
     *
     * @throws \Exception
     * @return string dialog create event
     */
    protected function checkUnknownOrganizer()
    {
        $Configuration = new Configuration($this->app);
        $config = $Configuration->getConfiguration();

        if (false === ($organizer_id = $this->app['contact']->existsLogin($config['event']['organizer']['unknown']['identifier']))) {
            // missing the contact record for unknown organizers, create it!

            // get the TAGS assigned to the organizer
            $OrganizerTag = new OrganizerTag($this->app);
            if (false === ($group_tags = $OrganizerTag->selectTagNamesByGroupID($this->app['session']->get('group_id')))) {
                throw new \Exception("Missing the organizer TAG names for the group ID ".$this->app['session']->get('group_id'));
            }

            $contact_id = ($organizer_id > 0) ? $organizer_id : -1;

            $tags = array();
            foreach ($group_tags as $tag) {
                $tags[] = array(
                    'contact_id' => $contact_id,
                    'tag_name' => $tag
                );
            }

            $data = array(
                'contact' => array(
                    'contact_id' => $contact_id,
                    'contact_type' => 'COMPANY',
                    'contact_name' => $config['event']['organizer']['unknown']['identifier'],
                    'contact_login' => $config['event']['organizer']['unknown']['identifier'],
                    'contact_status' => 'ACTIVE'
                ),
                'tag' => $tags,
                'company' => array(
                    array(
                        'company_id' => -1,
                        'contact_id' => -1,
                        'company_name' => $config['event']['organizer']['unknown']['identifier']
                    )
                ),
            );

            $organizer_id = -1;
            if (false === ($this->app['contact']->insert($data, $organizer_id))) {
                // don't return as message to to visitor, better throw an error
                throw new \Exception(strip_tags($this->app['contact']->getAlert()));
            }
        }
        $status = $this->app['contact']->getStatus($config['event']['organizer']['unknown']['identifier']);

        if ($status != 'ACTIVE') {
            // the unknown organizer is not active - update the record
            $data = array(
                'contact' => array(
                    'contact_id' => $organizer_id,
                    'contact_status' => 'ACTIVE'
                )
            );
            $has_changed = false;
            if (!$this->app['contact']->update($data, $organizer_id, $has_changed, true)) {
                // don't return as message to to visitor, better throw an error
                throw new \Exception(strip_tags($this->app['contact']->getAlert()));
            }
        }

        // set session for 'organizer_id'
        $this->app['session']->set('organizer_id', $organizer_id);
        $data = array(
            'new_organizer_id' => $organizer_id
        );
        $ProposeData = new ProposeData($this->app);
        if (null == ($propose_id = $this->app['session']->get('propose_id'))) {
            // create a new propose record
            $ProposeData->insert($data, $propose_id);
            // set the session for further usage
            $this->app['session']->set('propose_id', $propose_id);
        }
        else {
            // update existing propose record
            $ProposeData->update($propose_id, $data);
        }
        return $this->controllerEvent($this->app);
    }

    /**
     * Controller to select a organizer from the list, created by search results
     *
     * @param Application $app
     * @throws \Exception
     * @return string dialog to select organizer
     */
    public function controllerSelectOrganizer(Application $app)
    {
        $this->initParameters($app);

        // get the form fields
        $form_request = $this->app['request']->request->get('form', array());

        // check the search term
        if ((!isset($form_request['new_organizer']) && (!isset($form_request['unknown_organizer']))) &&
            (!isset($form_request['search']) || empty($form_request['search']))) {
            $this->setAlert('Please search for for a organizer or select the checkbox to create a new one.');
            return $this->controllerSearchOrganizer($app);
        }

        if (isset($form_request['new_organizer'])) {
            // create a new organizer record
            return $this->createContact('organizer', $this->app['session']->get('group_id'));
        }

        if (isset($form_request['unknown_organizer'])) {
            // unknown organizer
            return $this->checkUnknownOrganizer();
        }

        // get the TAGS assigned to the organizer
        $OrganizerTag = new OrganizerTag($app);
        if (false === ($tags = $OrganizerTag->selectTagNamesByGroupID($this->app['session']->get('group_id')))) {
            throw new \Exception("Missing the organizer TAG names for the group ID ".$form_request['event_group']);
        }

        $Overview = new Overview($app);
        if (false === ($organizers = $Overview->searchContact($form_request['search'], $tags, 'ACTIVE', '='))) {
            // no search result
            $this->setAlert('There exists no organizer who fits to the search term %search%', array('%search%' => $form_request['search']));
            return $this->controllerSearchOrganizer($app);
        }

        // show the list with the matching organizers
        return $this->app['twig']->render($this->app['utils']->getTemplateFile(
            '@phpManufaktur/Event/Template',
            "command/event.propose.organizer.select.twig",
            $this->getPreferredTemplateStyle()),
            array(
                'basic' => $this->getBasicSettings(),
                'organizers' => $organizers,
                'search_term' => $form_request['search'],
                'route' => array(
                    'organizer' => array(
                        'search' => '/event/propose/organizer/search',
                        'create' => '/event/propose/organizer/create/group/'.$this->app['session']->get('group_id'),
                        'id' => '/event/propose/organizer/id/{contact_id}'
                    )
                )
            ));
    }

    /**
     * Controller check the submitted group
     *
     * @param Application $app
     */
    public function controllerSearchOrganizer(Application $app)
    {
        $this->initParameters($app);

        $fields = $this->app['form.factory']->createBuilder('form')
        ->add('search', 'text', array(
            'label' => 'Search Organizer',
            'required' => false
        ))
        ->add('unknown_organizer', 'checkbox', array(
            'required' => false
        ))
        ->add('new_organizer', 'checkbox', array(
            'required' => false
        ));
        $form = $fields->getForm();

        return $this->app['twig']->render($this->app['utils']->getTemplateFile(
            '@phpManufaktur/Event/Template',
            "command/event.propose.organizer.search.twig",
            $this->getPreferredTemplateStyle()),
            array(
                'basic' => $this->getBasicSettings(),
                'form' => $form->createView(),
                'route' => array(
                    'organizer' => array(
                        'select' => '/event/propose/organizer/select'
                    )
                )
            ));
    }


    /**
     * Controller to select a event group
     *
     * @param Application $app
     */
    public function controllerSelectGroup(Application $app)
    {
        $this->initParameters($app);

        $EventGroup = new EventGroup($app);

        $fields = $this->app['form.factory']->createBuilder('form')
        // contact - hidden fields
        ->add('event_group', 'choice', array(
            'choices' => $EventGroup->getArrayForTwig(),
            'empty_value' => '- please select -',
            'expanded' => false,
            'label' => 'Select event group',
        ));
        $form = $fields->getForm();

        return $this->app['twig']->render($this->app['utils']->getTemplateFile(
            '@phpManufaktur/Event/Template',
            "command/event.propose.group.twig",
            $this->getPreferredTemplateStyle()),
            array(
                'basic' => $this->getBasicSettings(),
                'form' => $form->createView(),
                'route' => array(
                    'organizer' => array(
                        'search' => '/event/propose/organizer/search'
                    ),
                    'location' => array(
                        'search' => '/event/propose/location/search'
                    )
                )
            ));
    }

    /**
     * General execution for a propose. This Controller will be directly
     * executed from class Action.
     *
     * @param Application $app
     * @return string Dialog
     */
    public function exec(Application $app)
    {
        // init BASIC
        $this->initParameters($app);

        // get the parameters
        $parameter = $this->getCommandParameters();

        // remove all SESSION variables former used for the propose
        $app['session']->remove('group_id');
        $app['session']->remove('location_id');
        $app['session']->remove('organizer_id');
        $app['session']->remove('event_id');
        $app['session']->remove('propose_id');

        if (!isset($parameter['group'])) {
            // must first select a group
            return $this->controllerSelectGroup($app);
        }

        // select the location for the proposed event
        return $this->controllerSearchLocation($app);

        // select the organizer for the proposed event
        return $this->controllerSearchOrganizer($app);
    }

}
