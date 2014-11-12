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

use Silex\Application;
use phpManufaktur\Event\Control\Backend\Backend;
use phpManufaktur\Event\Data\Event\Event as EventData;
use phpManufaktur\Event\Data\Event\Group as EventGroup;
use phpManufaktur\Contact\Control\Contact as ContactControl;
use phpManufaktur\Event\Data\Event\OrganizerTag as EventOrganizerTag;
use phpManufaktur\Event\Data\Event\LocationTag as EventLocationTag;
use phpManufaktur\Event\Data\Event\Description as EventDescription;
use phpManufaktur\Event\Data\Event\Extra;
use phpManufaktur\Event\Data\Event\Images;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use phpManufaktur\Event\Data\Event\RecurringEvent as RecurringEventData;

class EventEdit extends Backend {

    protected static $event_id = -1;
    protected $EventData = null;
    protected $EventGroup = null;
    protected $ContactControl = null;
    protected $EventOrganizerTag = null;
    protected $EventLocationTag = null;
    protected $EventDescription = null;
    protected $Extra = null;
    protected $Images = null;
    protected $RecurringData = null;
    protected static $config = null;

    public function __construct(Application $app=null)
    {
        parent::__construct($app);
        if (!is_null($app)) {
            $this->initialize($app);
        }
    }

    protected function initialize(Application $app)
    {
        parent::initialize($app);
        $this->EventData = new EventData($this->app);
        $this->EventGroup = new EventGroup($this->app);
        $this->ContactControl = new ContactControl($this->app);
        $this->EventLocationTag = new EventLocationTag($this->app);
        $this->EventOrganizerTag = new EventOrganizerTag($this->app);
        $this->EventDescription = new EventDescription($this->app);
        $this->Extra = new Extra($this->app);
        $this->Images = new Images($this->app);
        $this->RecurringData = new RecurringEventData($app);
        self::$config = $this->app['utils']->readConfiguration(MANUFAKTUR_PATH.'/Event/config.event.json');
    }

    public function setEventID($event_id)
    {
        self::$event_id = $event_id;
    }

    /**
     * Create the form fields for the start dialog to create a new event
     *
     * @return $fields FormFactory
     */
    protected function getCreateStartFormFields()
    {
        $fields = $this->app['form.factory']->createBuilder('form')
        ->add('event_id', 'hidden', array(
            'data' => self::$event_id
        ))
        ->add('create_by', 'choice', array(
            'choices' => array(
                'GROUP' => $this->app['translator']->trans('by selecting a event group'),
                'COPY' => $this->app['translator']->trans('by copying from a existing event')),
            'expanded' => true,
            'label' => 'Create a new event',
            'data' => 'GROUP'
        ));
        return $fields;
    }

    /**
     * Return the form to choose a group for a new event
     *
     * @return $fields FormFactory
     */
    protected function getCreateByGroupFormFields()
    {
        $fields = $this->app['form.factory']->createBuilder('form')
        ->add('event_id', 'hidden', array(
            'data' => self::$event_id
        ))
        ->add('select_group', 'choice', array(
            'choices' => $this->EventGroup->getArrayForTwig(),
            'empty_value' => '- please select -',
            'expanded' => false,
            'label' => 'Select event group',
        ))
        ;
        return $fields;
    }

    /**
     * Create the dialog to edit an existing event
     *
     * @param array $event data record
     * @return $fields FormFactory
     */
    protected function getFormFields($event, &$extra_info=array())
    {

        if (false === ($group = $this->EventGroup->select($event['group_id']))) {
            throw new \Exception('The event group with the ID '.$event['group_id']." does not exists!");
        }
        $organizer_tags = $this->EventOrganizerTag->selectTagNamesByGroupID($event['group_id']);
        $location_tags = $this->EventLocationTag->selectTagNamesByGroupID($event['group_id']);

        $fields = $this->app['form.factory']->createBuilder('form')
        ->add('event_id', 'hidden', array(
            'data' => self::$event_id
        ))
        ->add('event_status', 'choice', array(
            'choices' => array('ACTIVE' => 'Active', 'LOCKED' => 'Locked', 'DELETED' => 'Deleted'),
            'empty_value' => false,
            'expanded' => false,
            'required' => true,
            'data' => $event['event_status']
        ))
        ->add('group_id', 'hidden', array(
            'data' => $event['group_id'],
        ))
        ->add('group_name', 'hidden', array(
            'data' => $this->EventGroup->getGroupName($event['group_id'])
        ))

        // Organizer
        ->add('event_organizer', 'choice', array(
            'choices' => $this->ContactControl->getContactsByTagsForTwig($organizer_tags),
            'empty_value' => '- please select -',
            'expanded' => false,
            'required' => true,
            'data' => $event['event_organizer']
        ))

        // Location
        ->add('event_location', 'choice', array(
            'choices' => $this->ContactControl->getContactsByTagsForTwig($location_tags), // explode(',', $group['group_location_contact_tags'])),
            'empty_value' => '- please select -',
            'expanded' => false,
            'required' => true,
            'data' => $event['event_location']
        ))
        // Participants
        ->add('event_participants_max', 'text', array(
            'label' => 'Participants, maximum',
            'data' => $event['event_participants_max'],
            'label' => 'Participants maximum',
            'required' => false
        ))
        ->add('event_participants_confirmed', 'hidden', array(
            'data' => $event['participants']['confirmed'],
        ))
        ->add('event_participants_pending', 'hidden', array(
            'data' => $event['participants']['pending'],
        ))
        ->add('event_participants_canceled', 'hidden', array(
            'data' => $event['participants']['canceled'],
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
            'data' => (!empty($event['event_publish_from']) && ($event['event_publish_from'] != '0000-00-00 00:00:00')) ? date($this->app['translator']->trans('DATETIME_FORMAT'), strtotime($event['event_publish_from'])) : null,
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
        // Costs
        ->add('event_costs', 'text', array(
            'required' => false,
            'data' => number_format($event['event_costs'], 2, $this->app['translator']->trans('DECIMAL_SEPARATOR'), $this->app['translator']->trans('THOUSAND_SEPARATOR'))
        ))
        // Event URL
        ->add('event_url', 'url', array(
            'required' => false,
            'data' => $event['event_url']
        ))
        ->add('description_title', 'text', array(
            'data' => $event['description_title'],
            'label' => 'Title',
            'required' => self::$config['event']['description']['title']['required']
        ))
        ->add('description_short', 'textarea', array(
            'data' => $event['description_short'],
            'label' => 'Short description',
            'required' => self::$config['event']['description']['short']['required']
        ))
        ->add('description_long', 'textarea', array(
            'data' => $event['description_long'],
            'label' => 'Long description',
            'required' => self::$config['event']['description']['long']['required']
        ))
        ;


        // adding the extra fields
        foreach ($event['extra_fields'] as $field) {
            $name= 'extra_'.$field['extra_type_name'];
            switch ($field['extra_type_type']) {
                // determine the form type for the extra field
                case 'TEXT':
                    $value = $field['extra_text'];
                    $form_type = 'textarea';
                    break;
                case 'HTML':
                    $value = $field['extra_html'];
                    $form_type = 'textarea';
                    break;
                case 'VARCHAR':
                    $value = $field['extra_varchar'];
                    $form_type = 'text';
                    break;
                case 'INT':
                    $value = $field['extra_int'];
                    $form_type = 'text';
                    break;
                case 'FLOAT':
                    $value = $field['extra_float'];
                    $form_type = 'text';
                    break;
                case 'DATE':
                    $value = $field['extra_date'];
                    $form_type = 'text';
                    break;
                case 'DATETIME':
                    $value = $field['extra_datetime'];
                    $form_type = 'text';
                    break;
                case 'TIME':
                    $value = $field['extra_time'];
                    $form_type = 'text';
                    break;
                default:
                    throw new \Exception("Undefined extra field type: {$field['extra_type_type']}");
            }

            // add the form field for the extra field
            $fields->add($name, $form_type, array(
                'attr' => array('class' => $name),
                'data' => $value,
                'label' => $this->app['utils']->humanize($field['extra_type_name']),
                'required' => false
            ));

            // extra info for the Twig handling
            $extra_info[] = array(
                'name' => $name,
                'type' => $field['extra_type_type'],
                'value' => $value,
                'id' => $field['extra_id']
            );
        }

        return $fields;
    }

    /**
     * Create a URL to add a image to the event
     *
     * @return string
     */
    protected function createAddImageURL()
    {
        $image_link_param = base64_encode(json_encode(array(
            'redirect' => '/admin/event/image/add/event/'.self::$event_id,
            'start' => '/media/public',
            'mode' => 'public',
            'usage' => self::$usage
        )));
        return FRAMEWORK_URL.'/mediabrowser/init/'.$image_link_param;
    }

    /**
     * Add a image to the event
     *
     * @param Application $app
     * @param integer $event_id
     */
    public function addImage(Application $app, $event_id)
    {
        // initialize the class
        $this->initialize($app);
        // set the event ID
        $this->setEventID($event_id);

        $image = $this->app['request']->get('file', null);

        if (!is_null($image)) {
            list($width, $height) = getimagesize(FRAMEWORK_PATH.$image);
            $data = array(
                'event_id' => $event_id,
                'image_title' => basename($image),
                'image_text' => '',
                'image_path' => $image,
                'image_height' => $height,
                'image_width' => $width
            );
            $this->Images->insert($data);
            $this->setAlert('The image <b>%image%</b> has been added to the event.',
                array('%image%' => basename($image), '%event_id%' => $event_id), self::ALERT_TYPE_SUCCESS);
        }
        else {
            $this->setAlert('No image selected, nothing to do.', array(), self::ALERT_TYPE_INFO);
        }
        return $this->exec($app, $event_id);
    }

    public function deleteImage(Application $app, $image_id, $event_id)
    {
        // initialize the class
        $this->initialize($app);
        // set the event ID
        $this->setEventID($event_id);
        // delete the image
        $this->Images->delete($image_id);
        $this->setAlert('The image with the ID %image_id% was successfull deleted.',
            array('%image_id%' => $image_id), self::ALERT_TYPE_SUCCESS);
        // show event dialog
        return $this->exec($app, $event_id);
    }

    /**
     * The class controller for the EventEdit dialog
     *
     * @param Application $app
     * @param integer $event_id
     * @throws \Exception
     */
    public function exec(Application $app, $event_id=null)
    {
        // initialize the class
        $this->initialize($app);
        if (!is_null($event_id)) {
            // set the given $event_id
            $this->setEventID($event_id);
        }
        $param = $this->app['request']->request->all();
        // check if a event ID isset
        $form_request = $this->app['request']->request->get('form', array());
        if (isset($form_request['event_id'])) {
            self::$event_id = $form_request['event_id'];
        }

        if (null != ($alert = $this->app['request']->query->get('alert'))) {
            $alert_type = $this->app['request']->query->get('alert_type', self::ALERT_TYPE_INFO);
            $this->setAlert($alert, array(), $alert_type);
        }

        // additional information for extra fields
        $extra_info = array();
        // help to detect if this is the first call of the function
        $is_start = false;
        if (self::$event_id < 1) {
            $is_start = true;
            $this->app['session']->set('create_new_event', true);
            if (isset($form_request['create_by'])) {
                if ($form_request['create_by'] == 'COPY') {
                    // show the dialog to copy an existing event into a new one
                    $subRequest = Request::create('/admin/event/copy', 'GET', array('usage' => self::$usage));
                    return $app->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
                }
                else {
                    // show the dialog to select a event group
                    $fields = $this->getCreateByGroupFormFields();
                    $form = $fields->getForm();
                    return $this->app['twig']->render($this->app['utils']->getTemplateFile(
                        '@phpManufaktur/Event/Template', 'admin/group.event.twig'),
                        array(
                            'usage' => self::$usage,
                            'toolbar' => $this->getToolbar('event_edit'),
                            'alert' => $this->getAlert(),
                            'form' => $form->createView()
                        ));
                }
            }
            elseif (isset($form_request['select_group'])) {
                // create a new event using the specified event group
                $data = array(
                    'group_id' => $form_request['select_group'],
                    'event_status' => 'LOCKED'
                );
                // create a new record without iCal and QRCode
                $this->EventData->insertEvent($data, self::$event_id, true);
                // get the event data
                $event = $this->EventData->selectEvent(self::$event_id);
            }
            else {
                // first step - show the start dialog to create a new event
                $fields = $this->getCreateStartFormFields();
                $form = $fields->getForm();
                return $this->app['twig']->render($this->app['utils']->getTemplateFile(
                    '@phpManufaktur/Event/Template', 'admin/create.event.twig'),
                    array(
                        'usage' => self::$usage,
                        'toolbar' => $this->getToolbar('event_edit'),
                        'alert' => $this->getAlert(),
                        'form' => $form->createView()
                    ));
            }
        }
        // select the event data
        else {
            if (false === ($event = $this->EventData->selectEvent(self::$event_id))) {
                $event = $this->EventData->getDefaultRecord();
                $this->setAlert('The record with the ID %id% does not exists!',
                    array('%id%' => self::$event_id), self::ALERT_TYPE_WARNING);
                self::$event_id = -1;
            }
        }

        // create the form fields
        $fields = $this->getFormFields($event, $extra_info);
        // get the form
        $form = $fields->getForm();

        if (!$is_start && ('POST' == $this->app['request']->getMethod())) {
            // the form was submitted, bind the request
            $form->bind($this->app['request']);
            if ($form->isValid()) {
                $event = $form->getData();
                self::$event_id = $event['event_id'];
                $checked = true;

                // check the event data
                if (self::$config['event']['description']['title']['required'] &&
                    (!isset($event['description_title']) || (strlen(trim($event['description_title'])) < self::$config['event']['description']['title']['min_length']))) {
                    $this->setAlert('Please type in a title with %minimum% characters at minimum.',
                        array('%minimum%' => self::$config['event']['description']['title']['min_length']), self::ALERT_TYPE_WARNING);
                    $checked = false;
                }
                elseif (!isset($event['description_title'])) {
                    $event['description_title'] = '';
                }
                if (self::$config['event']['description']['short']['required'] &&
                    (!isset($event['description_short']) || (strlen(trim($event['description_short'])) < self::$config['event']['description']['short']['min_length']))) {
                    $this->setAlert('Please type in a short description with %minimum% characters at minimum.',
                        array('%minimum%' => self::$config['event']['description']['short']['min_length']), self::ALERT_TYPE_WARNING);
                    $checked = false;
                }
                elseif (!isset($event['description_short'])) {
                    $event['description_short'] = '';
                }
                if (self::$config['event']['description']['long']['required'] &&
                    (!isset($event['description_long']) || (strlen(trim($event['description_long'])) < self::$config['event']['description']['long']['min_length']))) {
                    $this->setAlert('Please type in a long description with %minimum% characters at minimum.',
                        array('%minimum%' => self::$config['event']['description']['long']['min_length']), self::ALERT_TYPE_WARNING);
                    $checked = false;
                }
                elseif (!isset($event['description_long'])) {
                    $event['description_long'] = '';
                }

                if ($this->app['session']->get('create_new_event', false) &&
                    !self::$config['event']['date']['event_date_from']['allow_date_in_past'] &&
                    (strtotime($event['event_date_from']) < time())) {
                    $this->setAlert('It is not allowed that the event start in the past!', array(), self::ALERT_TYPE_WARNING);
                    $checked = false;
                }

                // create date time in the correct format
                $dt = Carbon::createFromFormat($this->app['translator']->trans('DATETIME_FORMAT'), $event['event_date_from']);
                $event['event_date_from'] = $dt->toDateTimeString();

                $dt = Carbon::createFromFormat($this->app['translator']->trans('DATETIME_FORMAT'), $event['event_date_to']);
                $event['event_date_to'] = $dt->toDateTimeString();

                if (empty($event['event_publish_from'])) {
                    $dt = Carbon::createFromTimestamp(strtotime($event['event_date_from']));
                    $dt->subDays(self::$config['event']['date']['event_publish_from']['subtract_days']);
                    $dt->startOfDay();
                    $event['event_publish_from'] = $dt->toDateTimeString();
                }
                else {
                    $dt = Carbon::createFromFormat($app['translator']->trans('DATETIME_FORMAT'), $event['event_publish_from']);
                    $event['event_publish_from'] = $dt->toDateTimeString();
                }

                if (empty($event['event_publish_to'])) {
                    $dt = Carbon::createFromTimestamp(strtotime($event['event_date_to']));
                    $dt->addDays(self::$config['event']['date']['event_publish_to']['add_days']);
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
                    $dt = Carbon::createFromFormat($this->app['translator']->trans('DATETIME_FORMAT'), $event['event_deadline']);
                    $event['event_deadline'] = $dt->toDateTimeString();
                }


                if (strtotime($event['event_date_from']) > strtotime($event['event_date_to'])) {
                    $this->setAlert('The event start date is behind the event end date!',
                        array(), self::ALERT_TYPE_WARNING);
                    $checked = false;
                }
                if (strtotime($event['event_publish_to']) < strtotime($event['event_date_from'])) {
                    $this->setAlert('The publishing date ends before the event starts, this is not allowed!',
                        array(), self::ALERT_TYPE_WARNING);
                    $checked = false;
                }
                if (strtotime($event['event_deadline']) > strtotime($event['event_date_from'])) {
                    $this->setAlert('The deadline ends after the event start date!',
                        array(), self::ALERT_TYPE_WARNING);
                    $checked = false;
                }


                if ($checked) {
                    // update an existing event
                    $this->app['session']->remove('create_new_event');
                    $data = array(
                        'event_organizer' => $event['event_organizer'],
                        'event_location' => $event['event_location'],
                        'event_costs' => isset($event['event_costs']) ? $this->app['utils']->str2float($event['event_costs']) : 0,
                        'event_participants_max' => isset($event['event_participants_max']) ? $this->app['utils']->str2int($event['event_participants_max']) : -1,
                        'event_status' => $event['event_status'],
                        'event_date_from' => $event['event_date_from'],
                        'event_date_to' => $event['event_date_to'],
                        'event_publish_from' => $event['event_publish_from'],
                        'event_publish_to' => $event['event_publish_to'],
                        'event_deadline' => $event['event_deadline'],
                        'description_title' => isset($event['description_title']) ? trim($event['description_title']) : '',
                        'description_short' => isset($event['description_short']) ? trim($event['description_short']) : '',
                        'description_long' => isset($event['description_long']) ? trim($event['description_long']) : '',
                        'event_url' => isset($event['event_url']) ? trim($event['event_url']) : ''
                    );
                    foreach ($extra_info as $extra) {
                        $data[$extra['name']] = $event[$extra['name']];
                    }
                    // update all event data
                    $this->EventData->updateEvent($data, self::$event_id);

                    // get the actual event record
                    $event = $this->EventData->selectEvent(self::$event_id);
                    // get the form fields
                    $extra_info = array();
                    $fields = $this->getFormFields($event, $extra_info);
                    // get the form
                    $form = $fields->getForm();
                }
            }
            else {
                // general error (timeout, CSFR ...)
                $this->setAlert('The form is not valid, please check your input and try again!', array(),
                    self::ALERT_TYPE_DANGER, true, array('form_errors' => $form->getErrorsAsString(),
                        'method' => __METHOD__, 'line' => __LINE__));
            }
        }

        $recurring_dates = null;
        if (isset($event['event_recurring_id']) && ($event['event_recurring_id'] > 0)) {
            $this->setAlert($this->RecurringData->getReadableCurringEvent($event['event_recurring_id']));
            $recurring_dates = $this->EventData->selectRecurringDates($event['event_recurring_id']);
        }


        return $this->app['twig']->render($this->app['utils']->getTemplateFile(
            '@phpManufaktur/Event/Template', 'admin/edit.event.twig'),
            array(
                'usage' => self::$usage,
                'toolbar' => $this->getToolbar('event_edit'),
                'alert' => $this->getAlert(),
                'form' => $form->createView(),
                'extra_info' => $extra_info,
                'add_image_url' => $this->createAddImageURL(),
                'images' => $this->Images->selectByEventID(self::$event_id),
                'recurring_dates' => $recurring_dates
            ));
    }

}
