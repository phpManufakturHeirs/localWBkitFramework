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
use phpManufaktur\Event\Data\Event\Subscription;
use phpManufaktur\Contact\Data\Contact\Message as MessageData;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use phpManufaktur\Event\Data\Event\Event as EventData;
use phpManufaktur\Event\Data\Event\RecurringEvent as RecurringEventData;
use phpManufaktur\Event\Data\Event\ParticipantTag;
use Carbon\Carbon;

class Subscribe extends Basic
{
    protected $MessageData = null;
    protected $Event = null;
    protected static $event_id = -1;
    protected static $redirect = null;
    protected static $contact_id = -1;
    protected static $parameter = null;
    protected $SubscriptionData = null;
    protected $EventData = null;
    protected static $config = null;
    protected $EventTools = null;
    protected $RecurringData = null;
    protected $EventParticipantTag = null;

    /**
     * (non-PHPdoc)
     * @see \phpManufaktur\Basic\Control\kitCommand\Basic::initParameters()
     */
    protected function initParameters(Application $app, $parameter_id=-1)
    {
        parent::initParameters($app, $parameter_id);

        $parameters = $this->getCommandParameters();

        // check the CMS GET parameters
        $GET = $this->getCMSgetParameters();
        if (isset($GET['command']) && ($GET['command'] == 'event')) {
            foreach ($GET as $key => $value) {
                if ($key == 'command') continue;
                $parameters[$key] = $value;
            }
            $this->setCommandParameters($parameters);
        }
        self::$parameter = $this->getCommandParameters();


        self::$parameter['captcha'] = (isset(self::$parameter['captcha']) &&
            ((strtolower(self::$parameter['captcha']) == 'false') || (self::$parameter['captcha'] == 0))) ? false : true;


        $this->MessageData = new MessageData($app);
        $this->SubscriptionData = new Subscription($app);
        $this->EventData = new EventData($app);
        $this->EventTools = new Tools($app);
        $this->RecurringData = new RecurringEventData($app);
        $this->EventParticipantTag = new ParticipantTag($app);

        self::$config = $app['utils']->readConfiguration(MANUFAKTUR_PATH.'/Event/config.event.json');
    }

    /**
     * Get the form fields
     *
     * @param array $subscribe
     * @return form.factory fields
     */
    protected function getFormFields($subscribe=array())
    {
        // get the communication types and values
        $email = $this->app['contact']->getDefaultCommunicationRecord();
        $phone = $this->app['contact']->getDefaultCommunicationRecord();
        $cell = $this->app['contact']->getDefaultCommunicationRecord();

        $form = $this->app['form.factory']->createBuilder('form')
        // contact - hidden fields
        ->add('event_id', 'hidden', array(
            'data' => self::$event_id
        ))
        ->add('redirect', 'hidden', array(
            'data' => self::$redirect
        ))
        ->add('contact_type', 'hidden', array(
            'data' => 'PERSON'
        ))
        ->add('contact_id', 'hidden', array(
            'data' => self::$contact_id
        ))
        ->add('person_id', 'hidden', array(
            'data' => isset($subscribe['person_id']) ? $subscribe['person_id'] : -1
        ));

        if (!isset(self::$config['event']['subscription']['contact']['email']['enabled']) ||
            !isset(self::$config['event']['subscription']['contact']['email']['required']) ||
            !self::$config['event']['subscription']['contact']['email']['enabled'] ||
            !self::$config['event']['subscription']['contact']['email']['required']) {
            // the email field must be always set and enabled and required!
            throw new \Exception($this->app['translator']->trans(
                'The email field must be always set for the subscription form and always enabled and required! Please check the config.event.json!'));
        }
        foreach (self::$config['event']['subscription']['contact'] as $field) {
            if ($field['enabled']) {
                switch ($field['name']) {
                    case 'person_gender':
                        $form->add($field['name'], 'choice', array(
                            'choices' => array('MALE' => 'Male', 'FEMALE' => 'Female'),
                            'expanded' => true,
                            'label' => 'Gender',
                            'required' => $field['required'],
                            'data' => isset($subscribe[$field['name']]) ? $subscribe[$field['name']] : $field['default']
                        ));
                        break;
                    case 'email':
                        $form->add('email_id', 'hidden', array(
                            'data' => isset($subscribe['email_id']) ? $subscribe['email_id'] : -1
                        ));
                        $form->add($field['name'], 'email', array(
                            'required' => true, // always required!
                            'label' => 'E-Mail',
                            'data' => isset($subscribe[$field['name']]) ? $subscribe[$field['name']] : ''
                        ));
                        break;
                    case 'phone':
                        $form->add('phone_id', 'hidden', array(
                            'data' => isset($subscribe['phone_id']) ? $subscribe['phone_id'] : -1
                        ));
                        $form->add($field['name'], 'text', array(
                            'required' => $field['required'],
                            'label' => $this->app['utils']->humanize($field['name']),
                            'data' => isset($subscribe[$field['name']]) ? $subscribe[$field['name']] : ''
                        ));
                        break;
                    case 'cell':
                        $form->add('cell_id', 'hidden', array(
                            'data' => isset($subscribe['cell_id']) ? $subscribe['cell_id'] : -1
                        ));
                        $form->add($field['name'], 'text', array(
                            'required' => $field['required'],
                            'label' => $this->app['utils']->humanize($field['name']),
                            'data' => isset($subscribe[$field['name']]) ? $subscribe[$field['name']] : ''
                        ));
                        break;
                    case 'street':
                    case 'city':
                    case 'zip':
                    case 'person_first_name':
                    case 'person_last_name':
                        $form->add($field['name'], 'text', array(
                            'required' => $field['required'],
                            'data' =>  isset($subscribe[$field['name']]) ? $subscribe[$field['name']] : ''
                        ));
                        break;
                    case 'country':
                        $form->add($field['name'], 'choice', array(
                            'choices' => $this->app['contact']->getCountryArrayForTwig(),
                            'empty_value' => '- please select -',
                            'expanded' => false,
                            'multiple' => false,
                            'required' => $field['required'],
                            'label' => $this->app['utils']->humanize($field['name']),
                            'data' => isset($subscribe[$field['name']]) ? $subscribe[$field['name']] : $field['default'],
                            'preferred_choices' => $field['preferred']
                        ));
                        break;
                    case 'birthday':
                        $form->add($field['name'], 'text', array(
                            'required' => $field['required'],
                            'label' => $this->app['utils']->humanize($field['name']),
                            'data' => (isset($subscribe['birthday']) && !empty($subscribe['birthday']) && ($subscribe['birthday'] != '0000-00-00')) ? date($this->app['translator']->trans('DATE_FORMAT'), strtotime($subscribe['birthday'])) : '',
                        ));
                        break;
                    default:
                        throw new \Exception($this->app['translator']->trans(
                            'The field with the name %name% is not supported.',
                            array('%name%' => $field['name'])));
                }
            }
        }


        // add the subscription field and the message field
        $form->add('subscribe', 'choice', array(
            'choices' => array('yes' => 'Subscribe to event'),
            'expanded' => true,
            'multiple' => true,
            'required' => true,
            'label' => '&nbsp;', // suppress label
            'data' => isset($subscribe['subscribe']) ? array('yes') : null
        ));
        $form->add('message', 'textarea', array(
            'required' => false,
            'data' => isset($subscribe['message']) ? $subscribe['message'] : ''
        ));

        if (self::$config['event']['subscription']['terms']['enabled']) {
            $form->add(self::$config['event']['subscription']['terms']['name'], 'choice', array(
                'choices' => array('yes' => $this->app['translator']->trans(self::$config['event']['subscription']['terms']['label'],
                    array('%url%' => self::$config['event']['subscription']['terms']['url']))),
                'expanded' => true,
                'multiple' => true,
                'required' => self::$config['event']['subscription']['terms']['required'],
                'label' => '&nbsp;', // suppress label
                'data' => isset($subscribe[self::$config['event']['subscription']['terms']['name']]) ? array('yes') : null
            ));
        }

        return $form;

    }

    /**
     * Check the form and handle the subscription with all needed steps
     *
     * @param Application $app
     * @throws \Exception
     */
    public function check(Application $app)
    {
        $this->initParameters($app);

        $subscribe_fields = $this->getFormFields();
        // get the form
        $form = $subscribe_fields->getForm();

        $form->bind($this->app['request']);

        if ($form->isValid() && (false !== ($recaptcha_check = $app['recaptcha']->isValid()))) {
            $subscribe = $form->getData();
            self::$event_id = $subscribe['event_id'];
            self::$contact_id = $subscribe['contact_id'];

            // contact ID isset or email address already exists as login
            if ((self::$contact_id > 0) || (false !== (self::$contact_id = $this->app['contact']->existsLogin($subscribe['email'])))) {
                // select the contact record
                $contact = $this->app['contact']->select(self::$contact_id);
                if ($contact['contact']['contact_type'] == 'COMPANY') {
                    // this is a COMPANY address !
                    $this->setAlert('The email address %email% is associated with a company contact record. At the moment you can only subscribe to a event with your personal email address!',
                        array('%email%' => $contact['contact']['contact_login']), true);
                    // unset email address
                    unset($subscribe['email']);
                    // set contact ID to -1 to enable a new submission
                    self::$contact_id = -1;
                    // return the subscribe form
                    return $this->getSubscribeForm($subscribe);
                }

                if ($contact['contact']['contact_status'] != 'ACTIVE') {
                    // this contact is not ACTIVE, so we disallow the subscription - set message and write to logifle!
                    $this->setAlert('The status of your address record is actually %status%, so we can not accept your subscription. Please contact the <a href="mailto:%email%">webmaster</a>.',
                        array('%status%' => $contact['contact']['contact_status'], '%email%' => SERVER_EMAIL_ADDRESS), true);
                    // return to the calling dialog
                    $subRequest = Request::create($subscribe['redirect'], 'GET', array('pid' => $this->getParameterID()));
                    return $app->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
                }

                // check if the contact data are changed
                if ((isset($subscribe['person_gender']) && ($contact['person'][0]['person_gender'] != $subscribe['person_gender'])) ||
                    (isset($subscribe['person_first_name']) && ($contact['person'][0]['person_first_name'] != $subscribe['person_first_name'])) ||
                    (isset($subscribe['person_last_name']) && ($contact['person'][0]['person_last_name'] != $subscribe['person_last_name']))) {
                    // update the contact record
                    $data = array(
                        'contact' => array(
                            'contact_id' => self::$contact_id
                        ),
                        'person' => array(
                            array(
                                'person_id' => $contact['person'][0]['person_id'])
                        )
                    );
                    if (isset($subscribe['person_gender'])) {
                        $data['person'][0]['person_gender'] = $subscribe['person_gender'];
                    }
                    if (isset($subscribe['person_first_name'])) {
                        $data['person'][0]['person_first_name'] = $subscribe['person_first_name'];
                    }
                    if (isset($subscribe['person_last_name'])) {
                        $data['person'][0]['person_last_name'] = $subscribe['person_last_name'];
                    }

                    if (!$this->app['contact']->update($data, self::$contact_id)) {
                        // something went wrong, throw an error
                        throw new \Exception($this->app['contact']->getAlert());
                    }
                    $this->setAlert('The contact record was successfull updated.');
                }

                // check the communication entries
                if (isset($subscribe['phone']) && !empty($subscribe['phone'])) {
                    $checked = false;
                    foreach ($contact['communication'] as $communication) {
                        if (($communication['communication_type'] == 'PHONE') &&
                            ($communication['communication_usage'] == 'PRIMARY')) {
                            $checked = true;
                            if ($subscribe['phone'] != $communication['communication_value']) {
                                $data = array(
                                    'contact' => array(
                                        'contact_id' => self::$contact_id
                                    ),
                                    'communication' => array(
                                        array(
                                            'communication_id' => $communication['communication_id'],
                                            'communication_value' => $subscribe['phone']
                                        )
                                    )
                                );
                                $this->app['contact']->update($data, self::$contact_id);
                            }
                        }
                    }
                    if (!$checked) {
                        // insert a new phone record
                        $data = array(
                            'contact' => array(
                                'contact_id' => self::$contact_id
                            ),
                            'communication' => array(
                                array(
                                    'communication_id' => -1,
                                    'communication_type' => 'PHONE',
                                    'communication_usage' => 'PRIMARY',
                                    'communication_value' => $subscribe['phone']
                                )
                            )
                        );
                        $this->app['contact']->update($data, self::$contact_id);
                    }
                }

                if (isset($subscribe['cell']) && !empty($subscribe['cell'])) {
                    $checked = false;
                    foreach ($contact['communication'] as $communication) {
                        if (($communication['communication_type'] == 'CELL') &&
                        ($communication['communication_usage'] == 'PRIMARY')) {
                            $checked = true;
                            if ($subscribe['cell'] != $communication['communication_value']) {
                                $data = array(
                                    'contact' => array(
                                        'contact_id' => self::$contact_id
                                    ),
                                    'communication' => array(
                                        array(
                                            'communication_id' => $communication['communication_id'],
                                            'communication_value' => $subscribe['cell']
                                        )
                                    )
                                );
                                $this->app['contact']->update($data, self::$contact_id);
                            }
                        }
                    }
                    if (!$checked) {
                        // insert a new phone record
                        $data = array(
                            'contact' => array(
                                'contact_id' => self::$contact_id
                            ),
                            'communication' => array(
                                array(
                                    'communication_id' => -1,
                                    'communication_type' => 'CELL',
                                    'communication_usage' => 'PRIMARY',
                                    'communication_value' => $subscribe['cell']
                                )
                            )
                        );
                        $this->app['contact']->update($data, self::$contact_id);
                    }
                }

                if (isset($subscribe['birthday']) && !empty($subscribe['birthday']) &&
                    ($subscribe['birthday'] != '0000-00-00')) {
                    $dt = Carbon::createFromFormat($this->app['translator']->trans('DATE_FORMAT'), $subscribe['birthday']);
                    $birthday = $dt->toDateTimeString();

                    if ($birthday != $contact['person'][0]['person_birthday']) {
                        $data = array(
                            'contact' => array(
                                'contact_id' => self::$contact_id
                            ),
                            'person' => array(
                                array(
                                    'person_id' => $contact['person'][0]['person_id'],
                                    'person_birthday' => $birthday
                                )
                            )
                        );
                        $this->app['contact']->update($data, self::$contact_id);
                    }
                }

                if ((isset($subscribe['street']) && !empty($subscribe['street']) && ($subscribe['street'] != $contact['address'][0]['address_street'])) ||
                    (isset($subscribe['zip']) && !empty($subscribe['zip']) && ($subscribe['zip'] != $contact['address'][0]['address_zip'])) ||
                    (isset($subscribe['city']) && !empty($subscribe['city']) && ($subscribe['city'] != $contact['address'][0]['address_city'])) ||
                    (isset($subscribe['country']) && !empty($subscribe['country']) && ($subscribe['country'] != $contact['address'][0]['address_country_code']))) {
                    $data = array(
                        'contact' => array(
                            'contact_id' => self::$contact_id
                        ),
                        'address' => array(
                            array(
                                'address_id' => $contact['address'][0]['address_id'],
                                'address_type' => 'PRIMARY',
                                'address_street' => (isset($subscribe['street']) && !empty($subscribe['street'])) ? $subscribe['street'] : $contact['address'][0]['address_street'],
                                'address_zip' => (isset($subscribe['zip']) && !empty($subscribe['zip'])) ? $subscribe['zip'] : $contact['address'][0]['address_zip'],
                                'address_city' => (isset($subscribe['city']) && !empty($subscribe['city'])) ? $subscribe['city'] : $contact['address'][0]['address_city'],
                                'address_country_code' => (isset($subscribe['country']) && !empty($subscribe['country'])) ? $subscribe['country'] : $contact['address'][0]['address_country_code']
                            )
                        )
                    );
                    $this->app['contact']->update($data, self::$contact_id);
                }

                // this is no new record
                $new_contact = false;
            }
            else {
                // insert a new contact record for the PERSON
                $contact = array();

                $contact['contact']['contact_id'] = self::$contact_id;
                $contact['contact']['contact_type'] = 'PERSON';
                $contact['contact']['contact_status'] = self::$config['contact']['confirm']['double_opt_in'] ? 'PENDING' : 'ACTIVE';
                $contact['contact']['contact_login'] = strtolower($subscribe['email']);

                if (isset($subscribe['person_first_name']) && !empty($subscribe['person_first_name']) &&
                    isset($subscribe['person_last_name']) && !empty($subscribe['person_last_name'])) {
                    $contact['contact']['contact_name'] = $subscribe['person_first_name'].' '.$subscribe['person_last_name'];
                }
                elseif (isset($subscribe['person_last_name']) && !empty($subscribe['person_last_name'])) {
                    $contact['contact']['contact_name'] = $subscribe['person_last_name'];
                }
                else {
                    $contact['contact']['contact_name'] = strtolower($subscribe['email']);
                }

                if (isset($subscribe['birthday']) && !empty($subscribe['birthday']) && ($subscribe['birthday'] != '0000-00-00')) {
                    $dt = Carbon::createFromFormat($this->app['translator']->trans('DATE_FORMAT'), $subscribe['birthday']);
                    $birthday = $dt->toDateTimeString();
                }
                else {
                    $birthday = '0000-00-00';
                }

                $contact['person'] = array(
                    array(
                        'person_id' => -1,
                        'person_gender' => isset($subscribe['person_gender']) ? $subscribe['person_gender'] : self::$config['event']['subscription']['contact']['gender']['default'],
                        'person_first_name' => isset($subscribe['person_first_name']) ? $subscribe['person_first_name'] : '',
                        'person_last_name' => isset($subscribe['person_last_name']) ? $subscribe['person_last_name'] : '',
                        'person_birthday' => $birthday
                    )
                );

                $contact['communication'] = array(
                    array(
                        'communication_id' => -1,
                        'communication_type' => 'EMAIL',
                        'communication_usage' => 'PRIMARY',
                        'communication_value' => strtolower($subscribe['email'])
                    )
                );

                if (isset($subscribe['phone']) && !empty($subscribe['phone'])) {
                    $contact['communication'] = array(
                        array(
                            'communication_id' => -1,
                            'communication_type' => 'PHONE',
                            'communication_usage' => 'PRIMARY',
                            'communication_value' => trim($subscribe['phone'])
                        )
                    );
                }

                if (isset($subscribe['cell']) && !empty($subscribe['cell'])) {
                    $contact['communication'] = array(
                        array(
                            'communication_id' => -1,
                            'communication_type' => 'CELL',
                            'communication_usage' => 'PRIMARY',
                            'communication_value' => trim($subscribe['cell'])
                        )
                    );
                }

                if (isset($subscribe['street']) || isset($subscribe['zip']) ||
                    isset($subscribe['city']) || isset($subscribe['country'])) {
                    $contact['address'] = array(
                        array(
                            'address_id' => -1,
                            'address_type' => 'PRIMARY',
                            'address_street' => isset($subscribe['street']) ? $subscribe['street'] : '',
                            'address_zip' => isset($subscribe['zip']) ? $subscribe['zip'] : '',
                            'address_city' => isset($subscribe['city']) ? $subscribe['city'] : '',
                            'address_country_code' => isset($subscribe['country']) ? $subscribe['country'] : self::$config['event']['subscription']['contact']['country']['default']
                        )
                    );
                }

                if (!$this->app['contact']->insert($contact, self::$contact_id)) {
                    // something went wrong, throw an error
                    throw new \Exception($this->app['contact']->getAlert());
                }
                $contact['contact']['contact_id'] = self::$contact_id;
                // this is a new contact
                $new_contact = true;
            }

            // get the event data
            $event = $this->EventData->selectEvent(self::$event_id);

            // get the tags for participants
            if (false !== ($tags = $this->EventParticipantTag->selectTagNamesByGroupID($event['group_id']))) {
                foreach ($tags as $tag) {
                    if (!$this->app['contact']->issetContactTag($tag, self::$contact_id)) {
                        // add this tag to the contact record of the participant
                        $this->app['contact']->setContactTag($tag, self::$contact_id);
                    }
                }
            }

            // check if the subscriber is already registered
            if (false !== ($subscription_id = $this->SubscriptionData->isAlreadySubscribedForEvent(self::$event_id, self::$contact_id))) {
                $subscription_data = $this->SubscriptionData->select($subscription_id);

                $this->setAlert('You have already subscribed to this Event at %datetime%, you can not subscribe again.',
                    array('%datetime%' => date($this->app['translator']->trans('DATETIME_FORMAT'))));
                // return to the calling dialog
                $subRequest = Request::create($subscribe['redirect'], 'GET', array('pid' => $this->getParameterID()));
                return $app->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
            }

            // check recurring events
            $recurring = array();
            $recurring_events = array();
            $this->checkRecurringEvent($event);

            // check message
            $message_id = -1;
            if (!empty($subscribe['message'])) {
                // insert the message
                $data = array(
                    'contact_id' => self::$contact_id,
                    'application_name' => 'Event',
                    'application_marker_type' => 'Subscription',
                    'application_marker_id' => self::$event_id,
                    'message_title' => isset($event['description_title']) ? $event['description_title'] : '',
                    'message_content' => strip_tags($subscribe['message']),
                    'message_date' => date('Y-m-d H:i:s'),
                );
                $this->MessageData->insert($data, $message_id);
            }

            // insert subscription
            $guid = $this->app['utils']->createGUID();
            $data = array(
                'event_id' => self::$event_id,
                'contact_id' => self::$contact_id,
                'message_id' => $message_id,
                'subscription_participants' => 1, // only one person at the moment!
                'subscription_date' => date('Y-m-d H:i:s'),
                'subscription_guid' => $guid,
                // if any double_opt_in confirmation is needed the status must be PENDING
                'subscription_status' => (self::$config['event']['subscription']['confirm']['double_opt_in'] ||
                    (self::$config['contact']['confirm']['double_opt_in'] && $new_contact)) ? 'PENDING' : 'CONFIRMED',
            );

            $subscription_id = -1;
            $this->SubscriptionData->insert($data, $subscription_id);

            // confirm the subscription ?
            if (($new_contact && in_array('contact', self::$config['contact']['confirm']['mail_to'])) ||
                (in_array('contact', self::$config['event']['subscription']['confirm']['mail_to']))) {
                // send a mail to the contact
                $body = $this->app['twig']->render($this->app['utils']->getTemplateFile(
                    '@phpManufaktur/Event/Template', 'command/mail/contact/subscribe.confirm.twig', $this->getPreferredTemplateStyle()),
                    array(
                        'basic' => $this->getBasicSettings(),
                        'contact' => $contact,
                        'event' => $event,
                        'recurring' => $recurring,
                        'recurring_events' => $recurring_events,
                        'confirm' => array(
                            'email' => ($new_contact && self::$config['contact']['confirm']['double_opt_in']),
                            'subscription' => self::$config['event']['subscription']['confirm']['double_opt_in']
                        ),
                        'link' => array(
                            'confirm' => FRAMEWORK_URL.'/event/subscribe/guid/'.$guid,
                            'event' => FRAMEWORK_URL.'/event/perma/id/'.self::$event_id
                        )
                    ));
                // create the message
                $message = \Swift_Message::newInstance()
                ->setSubject($event['description_title'])
                ->setFrom(array(SERVER_EMAIL_ADDRESS => SERVER_EMAIL_NAME))
                ->setTo(array($contact['contact']['contact_login']))
                ->setBody($body)
                ->setContentType('text/html');
                // send the message
                $this->app['mailer']->send($message);
                if (($new_contact && self::$config['contact']['confirm']['double_opt_in']) ||
                    self::$config['event']['subscription']['confirm']['double_opt_in']) {
                    $this->setAlert('Thank you for your subscription. We have send you an email, please use the submitted confirmation link to confirm your email address and to activate your subscription!');
                }
                else {
                    // no confirmation needed
                    $this->setAlert('Thank you for your subscription, we have send you a receipt at your email address.');
                }
            }

            if ($new_contact && ($contact['contact']['contact_status'] == 'ACTIVE')) {
                $check_array = self::$config['contact']['confirm']['mail_to'];
                unset($check_array['contact']);
                if (!empty($check_array)) {
                    // send a information about the new contact to the members in $check_array
                    $body = $this->app['twig']->render($this->app['utils']->getTemplateFile(
                        '@phpManufaktur/Event/Template', 'command/mail/distribution/new.contact.twig', $this->getPreferredTemplateStyle()),
                        array(
                            'basic' => $this->getBasicSettings(),
                            'contact' => $contact,
                            'event' => $event,
                            'recurring' => $recurring,
                            'recurring_events' => $recurring_events
                        ));
                    // create the message
                    $to_array = $this->EventTools->getEMailArrayFromTypeArray($event, $contact, $check_array);
                    if (!empty($to_array)) {
                        $message = \Swift_Message::newInstance()
                        ->setSubject($contact['contact']['contact_login'])
                        ->setFrom(array(SERVER_EMAIL_ADDRESS => SERVER_EMAIL_NAME))
                        ->setTo($to_array)
                        ->setBody($body)
                        ->setContentType('text/html');
                        // send the message
                        $this->app['mailer']->send($message);
                    }
                }
            }

            if (!self::$config['event']['subscription']['confirm']['double_opt_in'] &&
                ($contact['contact']['contact_status'] == 'ACTIVE')) {
                $check_array = self::$config['event']['subscription']['confirm']['mail_to'];
                unset($check_array['contact']);
                if (!empty($check_array)) {
                    // send a information about the new event subscription to the members in $check_array
                    $body = $this->app['twig']->render($this->app['utils']->getTemplateFile(
                        '@phpManufaktur/Event/Template', 'command/mail/distribution/subscribe.event.twig',
                        $this->getPreferredTemplateStyle()),
                        array(
                            'basic' => $this->getBasicSettings(),
                            'contact' => $contact,
                            'event' => $event,
                            'recurring' => $recurring,
                            'recurring_events' => $recurring_events
                        ));
                    // create the message
                    $to_array = $this->EventTools->getEMailArrayFromTypeArray($event, $contact, $check_array);
                    if (!empty($to_array)) {
                        $message = \Swift_Message::newInstance()
                        ->setSubject($contact['contact']['contact_login'])
                        ->setFrom(array(SERVER_EMAIL_ADDRESS => SERVER_EMAIL_NAME))
                        ->setTo($to_array)
                        ->setBody($body)
                        ->setContentType('text/html');
                        // send the message
                        $this->app['mailer']->send($message);
                    }
                }
            }

            // return to the calling dialog
            $subRequest = Request::create($subscribe['redirect'], 'GET', array('pid' => $this->getParameterID()));
            return $app->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
        }
        else {
            if (!$recaptcha_check) {
                // ReCaptcha error
                $this->setAlert($app['recaptcha']->getLastError());
            }
            else {
                // invalid form submission
                $this->setAlert('The form is not valid, please check your input and try again!', array(),
                self::ALERT_TYPE_DANGER, true, array('form_errors' => $form->getErrorsAsString(),
                    'method' => __METHOD__, 'line' => __LINE__));
            }
            // return the subscribe form
            return $this->getSubscribeForm();
        }
    }

    /**
     * Check if this is a recurring event and set the desired data
     *
     * @param array reference $event
     * @param array reference $recurring
     * @param array reference $recurring_events
     */
    protected function checkRecurringEvent(&$event, &$recurring=array(), &$recurring_events=array())
    {
        if ($event['event_recurring_id'] > 0) {
            if (false !== ($recurring = $this->RecurringData->select($event['event_recurring_id']))) {
                // first get the parent event record
                if (false !== ($event = $this->EventData->selectEvent($recurring['parent_event_id']))) {
                    // get all active recurring events
                    if (false !== ($items = $this->EventData->selectRecurringEvents($recurring['recurring_id']))) {
                        foreach ($items as $item) {
                            $route = base64_encode('/event/id/'.$event['event_id'].'/view/recurring');
                            $item['link']['subscribe'] = FRAMEWORK_URL.'/event/subscribe/id/'.$item['event_id'].'/redirect/'.$route.'?pid='.$this->getParameterID();
                            $recurring_events[] = $item;
                        }
                    }
                }
            }
        }
    }

    /**
     * Create the complete subscribe form
     *
     * @param array $subscribe already existing subscribe data
     */
    protected function getSubscribeForm($subscribe=array())
    {
        $subscribe_fields = $this->getFormFields($subscribe);
        // get the form
        $form = $subscribe_fields->getForm();

        $recurring = array();
        $recurring_events = array();
        if (false !== ($event = $this->EventData->selectEvent(self::$event_id))) {
            $this->checkRecurringEvent($event, $recurring, $recurring_events);
        }

        return $this->app['twig']->render($this->app['utils']->getTemplateFile(
            '@phpManufaktur/Event/Template', 'command/subscribe.twig', $this->getPreferredTemplateStyle()),
            array(
                'basic' => $this->getBasicSettings(),
                'parameter' => self::$parameter,
                'message' => $this->getAlert(),
                'form' => $form->createView(),
                'event' => $event,
                'recurring' => $recurring,
                'recurring_events' => $recurring_events
            ));
    }

    /**
     * Subscribe for the given event
     *
     * @param Application $app
     * @throws \Exception
     * @return string dialog or result
     */
    public function exec(Application $app, $event_id, $redirect)
    {
        $this->initParameters($app);
        self::$event_id = $event_id;
        self::$redirect = base64_decode($redirect);
        // create the subscribe form
        return $this->getSubscribeForm();
    }
}
