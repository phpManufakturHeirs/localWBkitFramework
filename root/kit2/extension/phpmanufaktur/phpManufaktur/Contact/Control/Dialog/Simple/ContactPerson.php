<?php

/**
 * Contact
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de/Contact
 * @copyright 2013 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\Contact\Control\Dialog\Simple;

use Silex\Application;
use phpManufaktur\Contact\Control\Contact as ContactControl;
use phpManufaktur\Contact\Control\Configuration;
use Carbon\Carbon;

class ContactPerson extends Dialog {

    protected static $contact_id = -1;
    protected $ContactControl = null;
    protected static $config = null;

    /**
     * Constructor
     *
     * @param Application $app
     */
    public function __construct(Application $app=null, $options=null)
    {
        parent::__construct($app);

        if (!is_null($app)) {
            $this->initialize($app, $options);
        }
    }

    /**
     * (non-PHPdoc)
     * @see \phpManufaktur\Contact\Control\Alert::initialize()
     */
    protected function initialize(Application $app, $options=null)
    {
        parent::initialize($app);

        $this->setOptions(array(
            'template' => array(
                'namespace' => isset($options['template']['namespace']) ? $options['template']['namespace'] : '@phpManufaktur/Contact/Template',
                'contact' => isset($options['template']['contact']) ? $options['template']['contact'] : 'pattern/admin/simple/edit.contact.twig'
            ),
            'route' => array(
                'action' => isset($options['route']['action']) ? $options['route']['action'] : '/admin/contact/person',
                'category' => isset($options['route']['category']) ? $options['route']['category'] : '/admin/contact/category/list',
                'title' => isset($options['route']['title']) ? $options['route']['title'] : '/admin/contact/title/list',
                'tag' => isset($options['route']['tag']) ? $options['route']['tag'] : '/admin/contact/tag/list',
                'list' => isset($options['route']['list']) ? $options['route']['list'] : '/admin/contact/list'
            )
        ));
        $this->ContactControl = new ContactControl($this->app);

        $Configuration = new Configuration($this->app);
        self::$config = $Configuration->getConfiguration();
    }

    /**
     * Set the contact ID
     *
     * @param integer $contact_id
     */
    public function setContactID($contact_id)
    {
        self::$contact_id = $contact_id;
    }

    /**
     * Build the form with the form.factory
     *
     * @param array $contact record
     * @return $form form fields
     */
    public function getFormFields($contact, &$extra_info=array())
    {
        // we need the Tag's as a simple array!
        $tags = array();
        foreach ($contact['tag'] as $tag) {
            $tags[] = $tag['tag_name'];
        }

        // create array for the birthday years
        $years = array();
        for ($i = date('Y'); $i > (date('Y')-100); $i--) {
            $years[] = $i;
        }
        $birthday_array = array(
            'label' => 'Birthday',
            'format' => 'ddMMyyyy',
            'years' => $years,
            'empty_value' => '',
            'required' => false,
            'data' => (!empty($contact['person'][0]['person_birthday']) && ($contact['person'][0]['person_birthday'] != '0000-00-00')) ? new \DateTime($contact['person'][0]['person_birthday']) : null
        );

        // get the communication types and values
        $email = $this->ContactControl->getDefaultCommunicationRecord();
        $phone = $this->ContactControl->getDefaultCommunicationRecord();
        $fax = $this->ContactControl->getDefaultCommunicationRecord();
        $cell = $this->ContactControl->getDefaultCommunicationRecord();

        foreach ($contact['communication'] as $communication) {
            switch ($communication['communication_type']) {
                case 'EMAIL' :
                    $email = $communication;
                    break;
                case 'PHONE' :
                    $phone = $communication;
                    break;
                case 'FAX':
                    $fax = $communication;
                    break;
                case 'CELL':
                    $cell = $communication;
                    break;
            }
        }

        // private (default) address
        $address_private = $this->ContactControl->getDefaultAddressRecord();

        foreach ($contact['address'] as $address) {
            switch ($address['address_type']) {
                case 'PRIVATE': // no longer in use!
                case 'PRIMARY':
                    $address_private = $address;
                    break;
            }
        }

        $access_type = $this->app['contact']->getAccessType($contact['contact']['contact_id']);
        $access_type = $this->app['translator']->trans($access_type);

        $form = $this->app['form.factory']->createBuilder('form')
        // contact - hidden fields
        ->add("contact_type", 'hidden', array(
            'data' => $contact['contact']['contact_type']
        ))
        ->add('contact_id', 'hidden', array(
            'data' => $contact['contact']['contact_id']
        ))
        ->add('contact_name', 'text', array(
            'required' => false,
            'data' => $contact['contact']['contact_name']
        ))
        ->add('contact_login', 'text', array(
            'required' => !self::$config['email']['required'],
            'data' => $contact['contact']['contact_login']
        ))
        // contact visible form fields
        ->add('contact_status', 'choice', array(
            'choices' => array(
                'ACTIVE' => $this->app['translator']->trans('Active'),
                'LOCKED' => $this->app['translator']->trans('Locked'),
                'PENDING' => $this->app['translator']->trans('Pending'),
                'DELETED' => $this->app['translator']->trans('Deleted')),
            'empty_value' => false,
            'expanded' => false,
            'multiple' => false,
            'required' => false,
            'data' => $contact['contact']['contact_status']
        ))

        // category - visible form fields
        ->add('category', 'choice', array(
            'choices' => $this->ContactControl->getCategoryArrayForTwig(),
            'empty_value' => '- please select -',
            'expanded' => false,
            'multiple' => false,
            'required' => false,
            'data' => isset($contact['category'][0]['category_type_name']) ? $contact['category'][0]['category_type_name'] : ''
        ))

        ->add('category_access', 'text', array(
            'data' => $access_type,
            'required' => false,
            'read_only' => true
        ))

        ->add('tag', 'choice', array(
            'choices' => $this->ContactControl->getTagArrayForTwig(),
            'expanded' => true,
            'multiple' => true,
            'required' => false,
            'label' => 'Tags',
            'data' => $tags
        ))

        // person - hidden fields
        ->add('person_id', 'hidden', array(
            'data' => $contact['person'][0]['person_id']
        ))

        // person - visible form fields
        ->add('person_gender', 'choice', array(
            'required' => false,
            'choices' => array('MALE' => 'Male', 'FEMALE' => 'Female'),
            'expanded' => true,
            'data' => $contact['person'][0]['person_gender']
        ))
        ->add('person_title', 'choice', array(
            'choices' => $this->ContactControl->getTitleArrayForTwig(),
            'empty_value' => '- please select -',
            'expanded' => false,
            'multiple' => false,
            'required' => false,
            'data' => $contact['person'][0]['person_title']
        ))
        ->add('person_first_name', 'text', array(
            'required' => false,
            'data' => $contact['person'][0]['person_first_name']
        ))
        ->add('person_last_name', 'text', array(
            'required' => false,
            'data' => $contact['person'][0]['person_last_name']
        ))
        //->add('person_birthday', 'date', $birthday_array)
        ->add('person_birthday', 'text', array(
            'required' => false,
            'data' => (!empty($contact['person'][0]['person_birthday']) && ($contact['person'][0]['person_birthday'] != '0000-00-00')) ? date($this->app['translator']->trans('DATE_FORMAT'), strtotime($contact['person'][0]['person_birthday'])) : '',
        ))

        // communication
        ->add('email_id', 'hidden', array(
            'data' => $email['communication_id']
        ))
        ->add('email', 'email', array(
            'required' => self::$config['email']['required'],
            'data' => $email['communication_value']
        ))
        ->add('phone_id', 'hidden', array(
            'data' => $phone['communication_id']
        ))
        ->add('phone', 'text', array(
            'required' => false,
            'data' => $phone['communication_value']
        ))
        ->add('cell_id', 'hidden', array(
            'data' => $cell['communication_id']
        ))
        ->add('cell', 'text', array(
            'required' => false,
            'data' => $cell['communication_value']
        ))
        ->add('fax_id', 'hidden', array(
            'data' => $fax['communication_id']
        ))
        ->add('fax', 'text', array(
            'required' => false,
            'data' => $fax['communication_value']
        ))

        // private address
        ->add('address_id', 'hidden', array(
            'data' => $address_private['address_id']
        ))
        ->add('address_street', 'text', array(
            'required' => false,
            'data' => $address_private['address_street']
        ))
        ->add('address_zip', 'text', array(
            'required' => false,
            'data' => $address_private['address_zip']
        ))
        ->add('address_city', 'text', array(
            'required' => false,
            'data' => $address_private['address_city']
        ))
        ->add('address_area', 'text', array(
            'required' => false,
            'data' => $address_private['address_area']
        ))
        ->add('address_state', 'text', array(
            'required' => false,
            'data' => $address_private['address_state']
        ))
        ->add('address_country', 'choice', array(
            'choices' => $this->ContactControl->getCountryArrayForTwig(),
            'empty_value' => '- please select -',
            'expanded' => false,
            'multiple' => false,
            'required' => false,
            'data' => $address_private['address_country_code'],
            'preferred_choices' => self::$config['countries']['preferred']
        ))

        ->add('note_id', 'hidden', array(
            'data' => isset($contact['note'][0]['note_id']) ? $contact['note'][0]['note_id'] : -1
        ))
        ->add('note', 'textarea', array(
            'required' => false,
            'data' => isset($contact['note'][0]['note_content']) ? $contact['note'][0]['note_content'] : ''
        ));

        // adding the extra fields
        if (isset($contact['extra_fields'])) {
            foreach ($contact['extra_fields'] as $field) {
                $name= 'extra_'.strtolower($field['extra_type_name']);
                switch ($field['extra_type_type']) {
                    // determine the form type for the extra field
                    case 'TEXT':
                        $form_type = 'textarea';
                        break;
                    case 'HTML':
                        $form_type = 'textarea';
                        break;
                    default:
                        $form_type = 'text';
                        break;
                }

                // add the form field for the extra field
                $form->add($name, $form_type, array(
                    'attr' => array('class' => $name),
                    'data' => $field['extra_value'],
                    'label' => $this->app['utils']->humanize($field['extra_type_name']),
                    'required' => false
                ));

                // extra info for the Twig handling
                $extra_info[] = array(
                    'name' => $name,
                    'field' => $field
                );
            }
        }
        return $form;
    }

    /**
     * Get the data from the person contact form
     *
     * @param array $data
     * @param array $extra_info
     * @return array
     */
    public function getFormData($data, $extra_info=array())
    {
        $tags = array();
        if (isset($data['tag'])) {
            foreach ($data['tag'] as $tag) {
                $tags[] = array(
                    'contact_id' => $data['contact_id'],
                    'tag_name' => $tag
                );
            }
        }

        $extra_fields = array();
        foreach ($extra_info as $field) {
            $dummy = $field['field'];
            $dummy['extra_value'] = isset($data[$field['name']]) ? $data[$field['name']] : '';
            $extra_fields[] = $dummy;
        }


        if (isset($data['person_birthday']) && !empty($data['person_birthday'])) {
            $dt = Carbon::createFromFormat($this->app['translator']->trans('DATE_FORMAT'), $data['person_birthday']);
            $birthday = $dt->toDateTimeString();
        }
        else {
            $birthday = '0000-00-00';
        }


        return array(
            'contact' => array(
                'contact_id' => $data['contact_id'],
                'contact_type' => 'PERSON',
                'contact_status' => isset($data['contact_status']) ? $data['contact_status'] : 'ACTIVE',
                'contact_name' => isset($data['contact_name']) ? $data['contact_name'] : null,
                'contact_login' => isset($data['contact_login']) ? $data['contact_login'] : null
            ),
            'category' => array(
                array(
                    'contact_id' => $data['contact_id'],
                    'category_type_name' => isset($data['category']) ? $data['category'] : ''
                )
            ),
            'tag' => $tags,
            'person' => array(
                array(
                    'person_id' => $data['person_id'],
                    'contact_id' => $data['contact_id'],
                    'person_gender' => $data['person_gender'],
                    'person_title' => isset($data['person_title']) && !empty($data['person_title']) ? $data['person_title'] : 'NO_TITLE',
                    'person_first_name' => $data['person_first_name'],
                    'person_last_name' => $data['person_last_name'],
                    'person_birthday' => $birthday
                )
            ),
            'communication' => array(
                array(
                    // email
                    'communication_id' => $data['email_id'],
                    'contact_id' => $data['contact_id'],
                    'communication_type' => 'EMAIL',
                    'communication_usage' => 'PRIMARY',
                    'communication_value' => $data['email']
                ),
                array(
                    // phone
                    'communication_id' => $data['phone_id'],
                    'contact_id' => $data['contact_id'],
                    'communication_type' => 'PHONE',
                    'communication_usage' => 'PRIMARY',
                    'communication_value' => $data['phone']
                ),
                array(
                    // cell
                    'communication_id' => $data['cell_id'],
                    'contact_id' => $data['contact_id'],
                    'communication_type' => 'CELL',
                    'communication_usage' => 'PRIMARY', //'PRIVATE',
                    'communication_value' => $data['cell']
                ),
                array(
                    // fax
                    'communication_id' => $data['fax_id'],
                    'contact_id' => $data['contact_id'],
                    'communication_type' => 'FAX',
                    'communication_usage' => 'PRIMARY', //'PRIVATE',
                    'communication_value' => $data['fax']
                ),
            ),
            'address' => array(
                array(
                    'address_id' => $data['address_id'],
                    'contact_id' => $data['contact_id'],
                    'address_type' => 'PRIMARY', //'PRIVATE',
                    'address_street' => $data['address_street'],
                    'address_zip' => $data['address_zip'],
                    'address_city' => $data['address_city'],
                    'address_area' => $data['address_area'],
                    'address_state' => $data['address_state'],
                    'address_country_code' => $data['address_country']
                )
            ),
            'note' => array(
                array(
                    'note_id' => $data['note_id'],
                    'contact_id' => $data['contact_id'],
                    'note_title' => 'Remarks',
                    'note_type' => 'TEXT',
                    'note_content' => $data['note']
                )
            ),
            'extra_fields' => $extra_fields
        );
    }

    /**
     * Default controller for the contact dialog
     *
     * @param Application $app
     * @return string
     */
    public function controller(Application $app, $contact_id=null)
    {
        $this->app = $app;
        $this->initialize();
        if (!is_null($contact_id)) {
            $this->setContactID($contact_id);
        }
        return $this->exec();
    }

    /**
     * Return the complete contact dialog and handle requests
     *
     * @return string contact dialog
     */
    public function exec($extra=null)
    {
        // check if a contact ID isset
        $form_request = $this->app['request']->request->get('form', array());
        if (isset($form_request['contact_id'])) {
            self::$contact_id = $form_request['contact_id'];
        }

        // get the contact array
        $contact = $this->ContactControl->select(self::$contact_id, 'PERSON');

        if ($this->ContactControl->isAlert()) {
            $this->setAlertUnformatted($this->ContactControl->getAlert());
        }

        // get the form fields
        $extra_info = array();
        $contact_form = $this->getFormFields($contact, $extra_info);
        // get the form
        $form = $contact_form->getForm();

        if ('POST' == $this->app['request']->getMethod()) {
            // the form was submitted, bind the request
            $form->bind($this->app['request']);
            if ($form->isValid()) {
                // get the form data
                $data = $form->getData();
                $contact = $this->getFormData($data, $extra_info);

                if (self::$contact_id < 1) {
                    // insert a new record
                    $this->ContactControl->insert($contact, self::$contact_id);
                }
                else {
                    // update the record
                    $has_changed = false; // indicate changes
                    $this->ContactControl->update($contact, self::$contact_id, $has_changed);
                }

                // get the values of the new or updated record
                $contact = $this->ContactControl->select(self::$contact_id);
                // get the form fields
                $contact_form = $this->getFormFields($contact, $extra_info);
                // get the form
                $form = $contact_form->getForm();
            }
            else {
                // general error (timeout, CSFR ...)
                $this->setAlert('The form is not valid, please check your input and try again!', array(),
                    self::ALERT_TYPE_DANGER, true, array('form_errors' => $form->getErrorsAsString(),
                        'method' => __METHOD__, 'line' => __LINE__));
            }
        }
        return $this->app['twig']->render($this->app['utils']->getTemplateFile(
            self::$options['template']['namespace'], self::$options['template']['contact']),
            array(
                'alert' => $this->getAlert(),
                'form' => $form->createView(),
                'route' => self::$options['route'],
                'extra' => $extra,
                'extra_info' => $extra_info,
                'usage' => isset($extra['usage']) ? $extra['usage'] : $this->app['request']->get('usage', 'framework')
            ));
    }
}
