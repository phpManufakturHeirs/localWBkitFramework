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

use Silex\Application;
use phpManufaktur\Basic\Control\kitCommand\Basic;
use phpManufaktur\Event\Data\Event\Subscription;
use phpManufaktur\Contact\Data\Contact\Contact as ContactData;
use phpManufaktur\Contact\Data\Contact\Overview;
use phpManufaktur\Contact\Data\Contact\Protocol;
use phpManufaktur\Contact\Control\Contact as ContactControl;
use phpManufaktur\Basic\Data\Security\Users;
use phpManufaktur\Event\Data\Event\Event;

class ConfirmSubscription extends Basic
{
    protected static $guid = null;
    protected static $config = null;
    protected $ContactData = null;
    protected $ContactOverview = null;
    protected $ContactProtocol = null;
    protected $Users = null;
    protected $EventData = null;
    protected $ContactControl = null;
    protected $EventTools = null;

    protected function initParameters(Application $app, $parameter_id=-1)
    {
        // init parent
        parent::initParameters($app);

        // check the permanent link
        self::$config = $app['utils']->readConfiguration(MANUFAKTUR_PATH.'/Event/config.event.json');
        if (!isset(self::$config['permalink']['cms']['url']) || empty(self::$config['permalink']['cms']['url'])) {
            throw new \Exception('Missing the permanent link definition in config.event.json!');
        }

        $this->ContactData = new ContactData($app);
        $this->ContactControl = new ContactControl($app);
        $this->ContactOverview = new Overview($app);
        $this->ContactProtocol = new Protocol($app);
        $this->Users = new Users($app);
        $this->EventData = new Event($app);
        $this->EventTools = new Tools($app);

        $actual_url = $this->getCMSpageURL();

        // set the permanent link as CMS page URI!
        $this->setCMSpageURL(self::$config['permalink']['cms']['url']);
        // redirect to itself
        $this->setRedirectRoute('/event/subscribe/guid/'.self::$guid);
        // activate redirection
        $this->setRedirectActive(true);

        if ($actual_url != self::$config['permalink']['cms']['url']) {
            // reload this page to fold it into the CMS before performing anything
            return 'RELOAD';
        }
    }

    /**
     * Check the submitted GUID and activate the CONTACT and the EVENT record
     *
     * @param Application $app
     * @param string $guid
     * @throws \Exception
     */
    public function exec(Application $app, $guid)
    {
        // set the GUID
        self::$guid = $guid;

        if ('RELOAD' == $this->initParameters($app)) {
            $app['session']->set('COMMAND_LOCKED', true);
            // reload this page to fold the iframe into the CMS before performing anything
            $app['monolog']->addInfo(sprintf("Reload route %s into CMS URL %s before performing anything",
                '/event/subscribe/guid/'.self::$guid, $this->getCMSpageURL()));
            return $app['twig']->render($app['utils']->getTemplateFile('@phpManufaktur/Basic/Template', 'kitcommand/reload.twig'),
                array('basic' => $this->getBasicSettings()));
        }
        if ($app['session']->get('COMMAND_LOCKED', false)) {
            $app['session']->remove('COMMAND_LOCKED');
            return $app['twig']->render($app['utils']->getTemplateFile('@phpManufaktur/Basic/Template', 'kitcommand/null.twig'),
                array('basic' => $this->getBasicSettings()));
        }
        $SubscriptionData = new Subscription($app);
        if (false === ($subscription = $SubscriptionData->selectGUID($guid))) {
            $message = $app['translator']->trans('The submitted GUID %guid% does not exists.', array('%guid%' => self::$guid));
            $this->app['monolog']->addInfo('[ConfirmSubscription] '.$message);
            return $app['twig']->render($app['utils']->getTemplateFile(
                '@phpManufaktur/Event/Template',
                'command/message.twig',
                $this->getPreferredTemplateStyle()),
                array(
                    'basic' => $this->getBasicSettings(),
                    'title' => 'Checking the GUID identifier',
                    'message' => $message
                    ));
        }

        if ($subscription['subscription_status'] == 'CONFIRMED') {
            // the subscription was already confirmed
            $event = $this->EventData->selectEvent($subscription['event_id']);
            return $app['twig']->render($app['utils']->getTemplateFile(
                '@phpManufaktur/Event/Template',
                'command/message.twig',
                $this->getPreferredTemplateStyle()),
                array(
                    'basic' => $this->getBasicSettings(),
                    'title' => 'Checking the GUID identifier',
                    'message' => $app['translator']->trans('Your subscription for the event %event% is already confirmed.',
                        array('%event%' => $event['description_title']))
                ));
        }
        elseif ($subscription['subscription_status'] != 'PENDING') {
            // the status of the event is not as expected PENDING, perhaps a problem?
            $message = $app['translator']->trans('The status (%subscription_status%) of your subscription #%subscription_id% is ambiguous, the program can not confirm your subscription. Please contact the <a href="%email%">webmaster</a>.',
                array('%subscription_id%' => $subscription['subscription_id'],
                    '%email%' => SERVER_EMAIL_ADDRESS,
                    '%subscription_status%' => $subscription['subscription_status']));
            $app['monolog']->addInfo('[ConfirmSubscription] '.$message);
            return $app['twig']->render($app['utils']->getTemplateFile(
                '@phpManufaktur/Event/Template',
                'command/message.twig',
                $this->getPreferredTemplateStyle()),
                array(
                    'basic' => $this->getBasicSettings(),
                    'title' => 'Checking the GUID identifier',
                    'message' => $message
                ));
        }

        // get the main contact data
        if (false === ($contact = $this->ContactData->select($subscription['contact_id']))) {
            // the contact ID does not exists
            throw new \Exception("[ConfirmSubscription] The contact ID {$subscription['contact_id']} does not exists!");
        }
        if ($contact['contact_type'] != 'PERSON') {
            // at the moment we have support only for PERSONs
            throw new \Exception('[ConfirmSubscription] At the time there are only contacts of type PERSON supported!');
        }

        $eventRecord = $this->EventData->selectEvent($subscription['event_id']);
        $contactRecord = $this->ContactControl->select($subscription['contact_id']);

        // check the contact?
        if (self::$config['contact']['confirm']['double_opt_in']) {
            if ($contact['contact_status'] == 'PENDING') {
                try {
                    $data = array(
                        'contact_status' => 'ACTIVE'
                    );
                    $app['db']->beginTransaction();
                    // update the contact data
                    $this->ContactData->update($data, $subscription['contact_id']);
                    // update the overview
                    $this->ContactOverview->refresh($subscription['contact_id']);
                    // add info to protocol
                    $this->ContactProtocol->addInfo($subscription['contact_id'], 'The contact activated by confirmation link.');

                    // add contact to the framework users
                    if (!$this->Users->existsUser($contact['contact_login'])) {
                        $password = $app['utils']->createPassword();
                        $data = array(
                            'username' => $contact['contact_login'],
                            'email' => $contact['contact_login'],
                            'password' => $this->Users->encodePassword($password),
                            'displayname' => $contact['contact_name'],
                            'roles' => 'ROLE_EVENT_USER'
                        );
                        $this->Users->insertUser($data);
                    }
                    else {
                        $user = $this->Users->selectUser($contact['contact_login']);
                        $roles = explode(',', $user['roles']);
                        if (!in_array('ROLE_EVENT_USER', $roles)) {
                            $roles[] = 'ROLE_EVENT_USER';
                            $data = array(
                                'roles' => implode(',', $roles)
                            );
                            $this->Users->updateUser($contact['contact_login'], $data);
                        }
                    }
                    // commit the transaction
                    $app['db']->commit();

                } catch (\Exception $e) {
                    $app['db']->rollback();
                    throw new \Exception($e);
                }

                $check_array = self::$config['contact']['confirm']['mail_to'];
                unset($check_array['contact']);
                if (!empty($check_array)) {
                    // send a information about the new contact to the members in $check_array
                    $body = $this->app['twig']->render($this->app['utils']->getTemplateFile(
                        '@phpManufaktur/Event/Template', 'command/mail/distribution/new.contact.twig', $this->getPreferredTemplateStyle()),
                        array(
                            'basic' => $this->getBasicSettings(),
                            'contact' => $contactRecord,
                            'event' => $eventRecord
                        ));
                    // create the message
                    $to_array = $this->EventTools->getEMailArrayFromTypeArray($eventRecord, $contactRecord, $check_array);
                    if (!empty($to_array)) {
                        $message = \Swift_Message::newInstance()
                        ->setSubject($contactRecord['contact']['contact_login'])
                        ->setFrom(array(SERVER_EMAIL_ADDRESS))
                        ->setTo($to_array)
                        ->setBody($body)
                        ->setContentType('text/html');
                        // send the message
                        $this->app['mailer']->send($message);
                    }
                }
            }
            elseif ($contact['contact_status'] != 'ACTIVE') {
                // contact status is neither PENDING nor ACTIVE, perhaps a problem?
                $message = $app['translator']->trans(
                    'The status for the contact with the ID %contact_id% is ambiguous, the program can not activate the account. Please contact the <a href="%email%">webmaster</a>.',
                    array('%contact_id%' => $contact['contact_id'], '%email%' => SERVER_EMAIL_ADDRESS));
                $app['monolog']->addInfo('[ConfirmSubscription] '.$message);
                return $app['twig']->render($app['utils']->getTemplateFile(
                    '@phpManufaktur/Event/Template',
                    'command/message.twig',
                    $this->getPreferredTemplateStyle()),
                    array(
                        'basic' => $this->getBasicSettings(),
                        'title' => 'Checking the GUID identifier',
                        'message' => $message
                    ));
            }
            else {
                // the contact is already active - nothing to do ...
            }

            // after activating the contact we activate the subscription
            $data = array(
                'subscription_status' => 'CONFIRMED',
                'subscription_confirmation' => date('Y-m-d H:is')
            );
            $SubscriptionData->update($subscription['subscription_id'], $data);

            $check_array = self::$config['event']['subscription']['confirm']['mail_to'];
            unset($check_array['contact']);
            if (!empty($check_array)) {
                // send a information about the new event subscription to the members in $check_array
                $body = $this->app['twig']->render($this->app['utils']->getTemplateFile(
                    '@phpManufaktur/Event/Template', 'command/mail/distribution/subscribe.event.twig',
                    $this->getPreferredTemplateStyle()),
                    array(
                        'basic' => $this->getBasicSettings(),
                        'contact' => $contactRecord,
                        'event' => $eventRecord
                    ));
                // create the message
                $to_array = $this->EventTools->getEMailArrayFromTypeArray($eventRecord, $contactRecord, $check_array);
                if (!empty($to_array)) {
                    $message = \Swift_Message::newInstance()
                    ->setSubject($contactRecord['contact']['contact_login'])
                    ->setFrom(array(SERVER_EMAIL_ADDRESS))
                    ->setTo($to_array)
                    ->setBody($body)
                    ->setContentType('text/html');
                    // send the message
                    $this->app['mailer']->send($message);
                }
            }
        }

        if (self::$config['event']['subscription']['confirm']['double_opt_in']) {
            if (!self::$config['contact']['confirm']['double_opt_in'] && ($contact['contact_status'] != 'ACTIVE')) {
                // the contact status is not ACTIVE, perhaps a problem?
                $message = $app['translator']->trans(
                    'The status for the contact with the ID %contact_id% is ambiguous, the program can not activate the account. Please contact the <a href="%email%">webmaster</a>.',
                    array('%contact_id%' => $contact['contact_id'], '%email%' => SERVER_EMAIL_ADDRESS));
                $app['monolog']->addInfo('[ConfirmSubscription] '.$message);
                return $app['twig']->render($app['utils']->getTemplateFile(
                    '@phpManufaktur/Event/Template',
                    'command/message.twig',
                    $this->getPreferredTemplateStyle()),
                    array(
                        'basic' => $this->getBasicSettings(),
                        'title' => 'Checking the GUID identifier',
                        'message' => $message
                    ));
            }
            // activate the subscription
            $data = array(
                'subscription_status' => 'CONFIRMED',
                'subscription_confirmation' => date('Y-m-d H:is')
            );
            $SubscriptionData->update($subscription['subscription_id'], $data);

            $check_array = self::$config['event']['subscription']['confirm']['mail_to'];
            unset($check_array['contact']);
            if (!empty($check_array)) {
                // send a information about the new event subscription to the members in $check_array
                $body = $this->app['twig']->render($this->app['utils']->getTemplateFile(
                    '@phpManufaktur/Event/Template', 'command/mail/distribution/subscribe.event.twig',
                    $this->getPreferredTemplateStyle()),
                    array(
                        'basic' => $this->getBasicSettings(),
                        'contact' => $contactRecord,
                        'event' => $eventRecord
                    ));
                // create the message
                $to_array = $this->EventTools->getEMailArrayFromTypeArray($eventRecord, $contactRecord, $check_array);
                if (!empty($to_array)) {
                    $message = \Swift_Message::newInstance()
                    ->setSubject($contactRecord['contact']['contact_login'])
                    ->setFrom(array(SERVER_EMAIL_ADDRESS))
                    ->setTo($to_array)
                    ->setBody($body)
                    ->setContentType('text/html');
                    // send the message
                    $this->app['mailer']->send($message);
                }
            }
        }

        // return the subscription dialog
        return $app['twig']->render($app['utils']->getTemplateFile(
            '@phpManufaktur/Event/Template',
            'command/subscribe.guid.twig',
            $this->getPreferredTemplateStyle()),
            array(
                'basic' => $this->getBasicSettings(),
                'confirm' => array(
                    'contact' => self::$config['event']['subscription']['confirm']['double_opt_in'],
                    'subscription' => self::$config['event']['subscription']['confirm']['double_opt_in']
                ),
                'event' => $eventRecord,
                'contact' => $contactRecord
            ));
    }
}
