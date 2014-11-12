<?php

/**
 * Event
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de/Event
 * @copyright 2013 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\Event\Control\Backend;

use phpManufaktur\Event\Control\Backend\Backend;
use Silex\Application;
use phpManufaktur\Event\Data\Event\Subscription;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use phpManufaktur\Event\Data\Event\EventSearch;
use phpManufaktur\Event\Data\Event\Event as EventData;
use phpManufaktur\Basic\Control\Pattern\Alert;
use phpManufaktur\Contact\Data\Contact\Message;

class Subscribe extends Backend {

    protected $EventSearch = null;
    protected $EventData = null;
    protected $SubscriptionData = null;

    protected function initialize(Application $app)
    {
        parent::initialize($app);
        $this->EventSearch = new EventSearch($app);
        $this->EventData = new EventData($app);
        $this->SubscriptionData = new Subscription($app);
    }

    /**
     * Get the search field for the Contact
     *
     */
    protected function getSearchContactFormFields()
    {
        return $this->app['form.factory']->createBuilder('form')
            ->add('search_contact', 'text', array(
            ));
    }

    /**
     * Form: Select the contact
     *
     * @param array $contacts
     */
    protected function getSelectContactFormFields($contacts=array())
    {
        $select_contacts = array();
        foreach ($contacts as $contact) {
            $select_contacts[$contact['contact_id']] = sprintf('%s [%s] %s %s %s',
                $contact['contact_name'],
                $contact['communication_email'],
                $contact['address_zip'],
                $contact['address_city'],
                $contact['address_street']
            );
        }
        return $this->app['form.factory']->createBuilder('form')
            ->add('contact_id', 'choice', array(
                'choices' => $select_contacts,
                'empty_value' => '- please select -',
                'expanded' => false,
                'required' => true,
                'label' => 'Contact search'
        ));
    }

    /**
     * Get the search field for the Event
     *
     * @param array $data
     */
    protected function getSearchEventFormFields($data=array())
    {
        return $this->app['form.factory']->createBuilder('form', $data)
        ->add('contact_id', 'hidden', array(
            'data' => isset($data['contact_id']) ? $data['contact_id'] : -1
        ))
        ->add('search_event', 'text');
    }

    /**
     * Get the final subscription form fields
     *
     * @param array $data
     */
    protected function getSubscriptionFormFields($data=array())
    {
        $event_array = array();
        if (isset($data['events'])) {
            foreach ($data['events'] as $event) {
                $event_array[$event['event_id']] = sprintf('[%05d] %s - %s',
                    $event['event_id'],
                    date($this->app['translator']->trans('DATE_FORMAT'), strtotime($event['event_date_from'])),
                    $event['description_title']
                );
            }
        }

        if (isset($data['contact_id'])) {
            $contact = $this->app['contact']->selectOverview($data['contact_id']);
        }

        return $this->app['form.factory']->createBuilder('form', $data)
        ->add('contact_id', 'hidden', array(
            'data' => isset($data['contact_id']) ? $data['contact_id'] : -1
        ))
        ->add('subscriber', 'text', array(
            'data' => isset($contact['contact_name']) ? $contact['contact_name'] : '',
            'disabled' => true,
            'required' => false
        ))
        ->add('event_id', 'choice', array(
            'choices' => isset($event_array) ? $event_array : null,
            'empty_value' => '- please select -',
            'label' => 'Select event'
        ))
        ->add('remark', 'textarea', array(
            'data' => isset($data['remark']) ? $data['remark'] : '',
            'required' => false
        ))
        ;
    }

    /**
     * Add the subscription to the database
     *
     * @param Application $app
     * @return string
     */
    public function ControllerFinishSubscription(Application $app)
    {
        $this->initialize($app);

        $data = $this->app['request']->request->get('form');

        if (!isset($data['contact_id']) || !isset($data['event_id'])) {
            // general error (timeout, CSFR ...)
            $this->setAlert('The form is not valid, please check your input and try again!', array(),
                self::ALERT_TYPE_DANGER, true, array('form_errors' => $form->getErrorsAsString(),
                    'method' => __METHOD__, 'line' => __LINE__));
            $subRequest = Request::create('/admin/event/subscription/add/start', 'GET', array('usage' => self::$usage));
            return $app->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
        }

        // handle the subscription
        if (false === ($event = $this->EventData->selectEvent($data['event_id']))) {
            throw new \Exception('The Event with the ID '.$data['event_id'].' does not exists!');
        }

        $message_id = -1;
        if (isset($data['remark']) && !empty($data['remark'])) {
            // insert a new Message
            $message_id = $this->app['contact']->addMessage(
                $data['contact_id'],
                $event['description_title'],
                $data['remark'],
                'Event',
                'Subscription',
                $data['event_id']
            );
        }

        $data = array(
            'event_id' => $data['event_id'],
            'contact_id' => $data['contact_id'],
            'message_id' => $message_id,
            'subscription_participants' => 1,
            'subscription_date' => date('Y-m-d H:i:s'),
            'subscription_guid' => $this->app['utils']->createGUID(),
            'subscription_status' => 'CONFIRMED'
        );
        $this->SubscriptionData->insert($data);

        $this->setAlert('The subscription was successfull inserted.');
        $subRequest = Request::create('/admin/event/subscription', 'GET', array('usage' => self::$usage));
        return $app->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
    }

    /**
     * Check the submitted event search term and show a selection for a event
     *
     * @param Application $app
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function ControllerSearchEvent(Application $app)
    {
        $this->initialize($app);

        $fields = $this->getSearchEventFormFields();
        $form = $fields->getForm();

        if ('POST' == $this->app['request']->getMethod()) {
            // the form was submitted, bind the request
            $form->bind($this->app['request']);
            if ($form->isValid()) {
                $data = $form->getData();
                // check the term in event search
                if (false === ($events = $this->EventSearch->search($data['search_event']))) {
                    // no hits for the search term
                    $this->setAlert('No hits for the search term <i>%search%</i>!',
                        array('%search%' => $data['search_event']));
                    $fields = $this->getSearchEventFormFields($data);
                    $form = $fields->getForm();
                    return $this->app['twig']->render($this->app['utils']->getTemplateFile(
                        '@phpManufaktur/Event/Template', 'admin/event.add.subscription.twig'),
                        array(
                            'usage' => self::$usage,
                            'toolbar' => $this->getToolbar('subscription'),
                            'alert' => $this->getAlert(),
                            'form' => $form->createView()
                        ));
                }
                else {
                    $data['events'] = $events;
                    $fields = $this->getSubscriptionFormFields($data);
                    $form = $fields->getForm();
                    return $this->app['twig']->render($this->app['utils']->getTemplateFile(
                        '@phpManufaktur/Event/Template', 'admin/form.add.subscription.twig'),
                        array(
                            'usage' => self::$usage,
                            'toolbar' => $this->getToolbar('subscription'),
                            'alert' => $this->getAlert(),
                            'form' => $form->createView()
                        ));
                }
            }
            else {
                // general error (timeout, CSFR ...)
                $this->setAlert('The form is not valid, please check your input and try again!', array(),
                    self::ALERT_TYPE_DANGER, true, array('form_errors' => $form->getErrorsAsString(),
                        'method' => __METHOD__, 'line' => __LINE__));
                $subRequest = Request::create('/admin/event/subscription/add/start', 'GET', array('usage' => self::$usage));
                return $app->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
            }
        }
        else {
            // general error (timeout, CSFR ...)
            $this->setAlert('The form is not valid, please check your input and try again!', array(),
                self::ALERT_TYPE_DANGER, true, array('form_errors' => $form->getErrorsAsString(),
                    'method' => __METHOD__, 'line' => __LINE__));
            $subRequest = Request::create('/admin/event/subscription/add/start', 'GET', array('usage' => self::$usage));
            return $app->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
        }
    }

    /**
     * Controller to add a selected contact and show the search field for an event
     *
     * @param Application $app
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function ControllerAddContact(Application $app)
    {
        $this->initialize($app);

        $fields = $this->getSearchEventFormFields();
        $form = $fields->getForm();

        if ('POST' == $this->app['request']->getMethod()) {
            // the form was submitted, bind the request
            $form->bind($this->app['request']);
            if ($form->isValid()) {
                $data = $form->getData();
                $fields = $this->getSearchEventFormFields($data);
                $form = $fields->getForm();
                return $this->app['twig']->render($this->app['utils']->getTemplateFile(
                    '@phpManufaktur/Event/Template', 'admin/event.add.subscription.twig'),
                    array(
                        'usage' => self::$usage,
                        'toolbar' => $this->getToolbar('subscription'),
                        'alert' => $this->getAlert(),
                        'form' => $form->createView()
                    ));
            }
            else {
                // general error (timeout, CSFR ...)
                $this->setAlert('The form is not valid, please check your input and try again!', array(),
                    self::ALERT_TYPE_DANGER, true, array('form_errors' => $form->getErrorsAsString(),
                        'method' => __METHOD__, 'line' => __LINE__));
                $subRequest = Request::create('/admin/event/subscription/add/start', 'GET', array('usage' => self::$usage));
                return $app->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
            }
        }
        else {
            // general error (timeout, CSFR ...)
            $this->setAlert('The form is not valid, please check your input and try again!', array(),
                self::ALERT_TYPE_DANGER, true, array('form_errors' => $form->getErrorsAsString(),
                    'method' => __METHOD__, 'line' => __LINE__));
            $subRequest = Request::create('/admin/event/subscription/add/start', 'GET', array('usage' => self::$usage));
            return $app->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
        }

    }

    /**
     * Start Controller to subscribe a contact to an event
     *
     * @param Application $app
     */
    public function ControllerAddSubscriptionStart(Application $app)
    {
        $this->initialize($app);

        $fields = $this->getSearchContactFormFields();
        $form = $fields->getForm();

        if ('POST' == $this->app['request']->getMethod()) {
            // the form was submitted, bind the request
            $form->bind($this->app['request']);
            if ($form->isValid()) {
                $data = $form->getData();
                if (false === ($contacts = $app['contact']->searchContact($data['search_contact']))) {
                    $this->setAlert('No hits for the search term <i>%search%</i>!', array('%search%' => $data['search_contact']));
                }
                else {
                    $fields = $this->getSelectContactFormFields($contacts);
                    $form = $fields->getForm();
                    return $this->app['twig']->render($this->app['utils']->getTemplateFile(
                        '@phpManufaktur/Event/Template', 'admin/contact.add.subscription.twig'),
                        array(
                            'usage' => self::$usage,
                            'toolbar' => $this->getToolbar('subscription'),
                            'alert' => $this->getAlert(),
                            'form' => $form->createView()
                        ));
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
            // set the intro text
            $this->setAlert('Please search for the contact you want to subscribe to an event or add a new contact, if you are shure that the person does not exists in Contacts.');
        }

        return $this->app['twig']->render($this->app['utils']->getTemplateFile(
            '@phpManufaktur/Event/Template', 'admin/start.add.subscription.twig'),
            array(
                'usage' => self::$usage,
                'toolbar' => $this->getToolbar('subscription'),
                'alert' => $this->getAlert(),
                'form' => $form->createView()
            ));
    }

    /**
     * Show the list with the subscriptions
     *
     * @return string rendered dialog
     */
    public function ControllerList(Application $app)
    {
        $this->initialize($app);

        $SubscriptionData = new Subscription($app);
        $subscriptions = $SubscriptionData->selectList(100, 30);

        return $this->app['twig']->render($this->app['utils']->getTemplateFile(
            '@phpManufaktur/Event/Template', 'admin/list.subscription.twig'),
            array(
                'usage' => self::$usage,
                'toolbar' => $this->getToolbar('subscription'),
                'subscriptions' => $subscriptions,
                'alert' => $this->getAlert()
            ));
    }

    /**
     * Get the final subscription form fields
     *
     * @param array $data
     */
    protected function getSubscriptionEditFields($data=array())
    {
        $remark = '';
        if (isset($data['subscription']['message_id']) && ($data['subscription']['message_id'] > 0)) {
            $MessageData = new Message($this->app);
            if (false === ($msg = $MessageData->select($data['subscription']['message_id']))) {
                throw new \Exception('Missing the message ID '.$data['subscription']['message_id']);
            }
            $remark = $msg['message_content'];
        }

        return $this->app['form.factory']->createBuilder('form', $data)
        ->add('subscription_id', 'hidden', array(
            'data' => isset($data['subscription']['subscription_id']) ? $data['subscription']['subscription_id'] : -1
        ))
        ->add('status', 'choice', array(
            'expanded' => false,
            'multiple' => false,
            'choices' => array(
                'PENDING' => $this->app['translator']->trans('Pending'),
                'CONFIRMED' => $this->app['translator']->trans('Confirmed'),
                'CANCELED' => $this->app['translator']->trans('Canceled'),
                'LOCKED' => $this->app['translator']->trans('Locked')
            ),
            'data' => isset($data['subscription']['subscription_status']) ? $data['subscription']['subscription_status'] : 'LOCKED'
        ))

        ->add('remark', 'textarea', array(
            'data' => $remark,
            'required' => false
        ))
        ;
    }

    public function ControllerEditSubscription(Application $app, $subscription_id)
    {
        $this->initialize($app);

        if (false === ($subscription = $this->SubscriptionData->select($subscription_id))) {
            $this->setAlert('The Subscription with the ID %subscription_id% does not exists!',
                array('%subscription_id%' => $subscription_id), Alert::ALERT_TYPE_DANGER);
            return $this->ControllerList($app);
        }
        if (false === ($contact = $this->app['contact']->selectOverview($subscription['contact_id']))) {
            $this->setAlert("The contact with the ID %contact_id% does not exists!",
                array('%contact_id%' => $subscription['contact_id']), Alert::ALERT_TYPE_DANGER);
            return $this->ControllerList($app);
        }
        if (false === ($event = $this->EventData->selectEvent($subscription['event_id'], false))) {
            $this->setAlert('The record with the ID %id% does not exists!',
                array('%id%' => $subscription['event_id']), Alert::ALERT_TYPE_DANGER);
            return $this->ControllerList($app);
        }

        $data = array(
            'subscription' => $subscription,
            'contact' => $contact,
            'event' => $event
        );

        $fields = $this->getSubscriptionEditFields($data);
        $form = $fields->getForm();

        return $this->app['twig']->render($this->app['utils']->getTemplateFile(
            '@phpManufaktur/Event/Template', 'admin/edit.subscription.twig'),
            array(
                'usage' => self::$usage,
                'toolbar' => $this->getToolbar('subscription'),
                'alert' => $this->getAlert(),
                'form' => $form->createView(),
                'data' => $data
            ));
    }

    public function ControllerCheckSubscription(Application $app)
    {
        $this->initialize($app);

        $fields = $this->getSubscriptionEditFields();
        $form = $fields->getForm();

        $form->bind($this->app['request']);
        if ($form->isValid()) {
            $data = $form->getData();
            if (false === ($subscription = $this->SubscriptionData->select($data['subscription_id']))) {
                $this->setAlert('The Subscription with the ID %subscription_id% does not exists!',
                    array('%subscription_id%' => $data['subscription_id']), Alert::ALERT_TYPE_DANGER);
                return $this->ControllerList($app);
            }

            $MessageData = new Message($this->app);

            $remark = '';
            if ($subscription['message_id'] > 0) {
                if (false === ($msg = $MessageData->select($subscription['message_id']))) {
                    throw new \Exception('Missing the message ID '.$subscription['message_id']);
                }
                $remark = $msg['message_content'];
            }

            if (($data['status'] != $subscription['subscription_status']) || ($data['remark'] != $remark)) {
                if ($subscription['message_id'] > 0) {
                    // update the message record
                    $MessageData->update(array('message_content' => $data['remark']), $subscription['message_id']);
                }
                else {
                    // create a new message record
                    if (false === ($event = $this->EventData->selectEvent($subscription['event_id'], false))) {
                        $this->setAlert('The record with the ID %id% does not exists!',
                            array('%id%' => $subscription['event_id']), Alert::ALERT_TYPE_DANGER);
                        return $this->ControllerList($app);
                    }
                    $MessageData->insert(array(
                        'contact_id' => $subscription['contact_id'],
                        'application_name' => 'Event',
                        'application_marker_type' => 'Subscription',
                        'application_marker_id' => $subscription['event_id'],
                        'message_title' => isset($event['description_title']) ? $event['description_title'] : '',
                        'message_content' => strip_tags($data['remark']),
                        'message_date' => date('Y-m-d H:i:s')),
                        $subscription['message_id']);
                }
                $this->SubscriptionData->update($data['subscription_id'], array(
                    'subscription_status' => $data['status'],
                    'message_id' => $subscription['message_id']
                ));
                $this->setAlert('The subscription was successfull updated');
            }
        }
        else {
            // general error (timeout, CSFR ...)
            $this->setAlert('The form is not valid, please check your input and try again!', array(),
                self::ALERT_TYPE_DANGER, true, array('form_errors' => $form->getErrorsAsString(),
                    'method' => __METHOD__, 'line' => __LINE__));
        }
        return $this->ControllerList($app);
    }

}
