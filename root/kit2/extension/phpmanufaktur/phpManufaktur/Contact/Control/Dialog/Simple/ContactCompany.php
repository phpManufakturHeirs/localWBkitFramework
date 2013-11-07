<?php

/**
 * Contact
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de/contact
 * @copyright 2013 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\Contact\Control\Dialog\Simple;

use Silex\Application;
use phpManufaktur\Contact\Control\Contact as ContactControl;
use Symfony\Component\Form\FormBuilder;
use phpManufaktur\Contact\Control\Configuration;

class ContactCompany extends Dialog {

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
            $this->initialize($options);
        }
    }

    protected function initialize($options=null)
    {
        // set the form options
        $this->setOptions(array(
            'template' => array(
                'namespace' => isset($options['template']['namespace']) ? $options['template']['namespace'] : '@phpManufaktur/Contact/Template',
                'message' => isset($options['template']['message']) ? $options['template']['message'] : 'backend/message.twig',
                'contact' => isset($options['template']['contact']) ? $options['template']['contact'] : 'backend/simple/edit.company.contact.twig'
            ),
            'route' => array(
                'action' => isset($options['route']['action']) ? $options['route']['action'] : '/admin/contact/simple/contact/company',
                'category' => isset($options['route']['category']) ? $options['route']['category'] : '/admin/contact/simple/category/list',
                'tag' => isset($options['route']['tag']) ? $options['route']['tag'] : '/admin/contact/simple/tag/list'
            )
        ));
        $this->ContactControl = new ContactControl($this->app);

        // get the configuration
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
     * Build the complete form with the form.factory
     *
     * @param array $contact data
     * @return FormBuilder
     */
    public function getFormFields($contact, &$extra_info=array())
    {
        // we need the Tag's as a simple array!
        $tags = array();
        foreach ($contact['tag'] as $tag) {
            $tags[] = $tag['tag_name'];
        }

        // get the communication types and values
        $email = $this->ContactControl->getDefaultCommunicationRecord();
        $phone = $this->ContactControl->getDefaultCommunicationRecord();
        $fax = $this->ContactControl->getDefaultCommunicationRecord();
        $cell = $this->ContactControl->getDefaultCommunicationRecord();
        $url = $this->ContactControl->getDefaultCommunicationRecord();

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
                case 'URL':
                    $url = $communication;
                    break;
            }
        }

        // business (default) address
        $address_business = $this->ContactControl->getDefaultAddressRecord();
        // delivery address
        $address_delivery = $this->ContactControl->getDefaultAddressRecord();

        foreach ($contact['address'] as $address) {
            switch ($address['address_type']) {
                case 'BUSINESS' :
                    $address_business = $address;
                    break;
                case 'DELIVERY':
                    $address_delivery = $address;
                    break;
            }
        }


        $form = $this->app['form.factory']->createBuilder('form')
        ->add('contact_id', 'hidden', array(
            'data' => $contact['contact']['contact_id']
        ))
        ->add('contact_type', 'hidden', array(
            'data' => $contact['contact']['contact_type']
        ))
        ->add('contact_status', 'choice', array(
            'choices' => array('ACTIVE' => 'active', 'LOCKED' => 'locked', 'PENDING' => 'pending', 'DELETED' => 'deleted'),
            'empty_value' => false,
            'expanded' => false,
            'multiple' => false,
            'required' => false,
            'label' => 'Status',
            'data' => $contact['contact']['contact_status']
        ))
        ->add('category', 'choice', array(
            'choices' => $this->ContactControl->getCategoryArrayForTwig(),
            'empty_value' => '- please select -',
            'expanded' => false,
            'multiple' => false,
            'required' => false,
            'label' => 'Category',
            'data' => isset($contact['category'][0]['category_type_name']) ? $contact['category'][0]['category_type_name'] : ''
        ))
        ->add('tag', 'choice', array(
            'choices' => $this->ContactControl->getTagArrayForTwig(),
            'expanded' => true,
            'multiple' => true,
            'required' => false,
            'label' => 'Tags',
            'data' => $tags
        ))
        ->add('contact_name', 'text', array(
            'required' => false,
            'label' => 'Contact name',
            'data' => $contact['contact']['contact_name']
        ))
        ->add('contact_login', 'text', array(
            'required' => !self::$config['email']['required'],
            'label' => 'Contact login',
            'data' => $contact['contact']['contact_login']
        ))

        // company
        ->add('company_id', 'hidden', array(
            'data' => $contact['company'][0]['company_id']
        ))
        ->add('company_name', 'text', array(
            'required' => false,
            'label' => 'Company name',
            'data' => $contact['company'][0]['company_name']
        ))
        ->add('company_department', 'text', array(
            'required' => false,
            'label' => 'Company department',
            'data' => $contact['company'][0]['company_department']
        ))
        ->add('company_additional', 'text', array(
            'required' => false,
            'label' => 'Additional',
            'data' => $contact['company'][0]['company_additional']
        ))
        ->add('company_additional_2', 'text', array(
            'required' => false,
            'label' => 'Additional',
            'data' => $contact['company'][0]['company_additional_2']
        ))
        ->add('company_additional_3', 'text', array(
            'required' => false,
            'label' => 'Additional',
            'data' => $contact['company'][0]['company_additional_3']
        ))

        // communication
        ->add('email_id', 'hidden', array(
            'data' => $email['communication_id']
        ))
        ->add('email_value', 'email', array(
            'required' => self::$config['email']['required'],
            'label' => 'E-Mail',
            'data' => $email['communication_value']
        ))
        ->add('phone_id', 'hidden', array(
            'data' => $phone['communication_id']
        ))
        ->add('phone', 'text', array(
            'required' => false,
            'label' => 'Phone',
            'data' => $phone['communication_value']
        ))
        ->add('cell_id', 'hidden', array(
            'data' => $cell['communication_id']
        ))
        ->add('cell', 'text', array(
            'required' => false,
            'label' => 'Cell',
            'data' => $cell['communication_value']
        ))
        ->add('fax_id', 'hidden', array(
            'data' => $fax['communication_id']
        ))
        ->add('fax', 'text', array(
            'required' => false,
            'label' => 'Fax',
            'data' => $fax['communication_value']
        ))
        ->add('url_id', 'hidden', array(
            'data' => $url['communication_id']
        ))
        ->add('url', 'url', array(
            'required' => false,
            'label' => 'Homepage',
            'data' => $url['communication_value']
        ))

        // business address
        ->add('address_business_id', 'hidden', array(
            'data' => $address_business['address_id']
        ))
        ->add('address_business_street', 'text', array(
            'required' => false,
            'label' => 'Street',
            'data' => $address_business['address_street']
        ))
        ->add('address_business_zip', 'text', array(
            'required' => false,
            'label' => 'Zip',
            'data' => $address_business['address_zip']
        ))
        ->add('address_business_city', 'text', array(
            'required' => false,
            'label' => 'City',
            'data' => $address_business['address_city']
        ))
        ->add('address_business_area', 'text', array(
            'required' => false,
            'label' => 'Area',
            'data' => $address_business['address_area']
        ))
        ->add('address_business_state', 'text', array(
            'required' => false,
            'label' => 'State',
            'data' => $address_business['address_state']
        ))
        ->add('address_business_country', 'choice', array(
            'choices' => $this->ContactControl->getCountryArrayForTwig(),
            'empty_value' => '- please select -',
            'expanded' => false,
            'multiple' => false,
            'required' => false,
            'label' => 'Country',
            'data' => $address_business['address_country_code'],
            'preferred_choices' => self::$config['countries']['preferred']
        ))

        // delivery address
        ->add('address_delivery_id', 'hidden', array(
            'data' => $address_delivery['address_id']
        ))
        ->add('address_delivery_street', 'text', array(
            'required' => false,
            'label' => 'Street',
            'data' => $address_delivery['address_street']
        ))
        ->add('address_delivery_zip', 'text', array(
            'required' => false,
            'label' => 'Zip',
            'data' => $address_delivery['address_zip']
        ))
        ->add('address_delivery_city', 'text', array(
            'required' => false,
            'label' => 'City',
            'data' => $address_delivery['address_city']
        ))
        ->add('address_delivery_country', 'choice', array(
            'choices' => $this->ContactControl->getCountryArrayForTwig(),
            'empty_value' => '- please select -',
            'expanded' => false,
            'multiple' => false,
            'required' => false,
            'label' => 'Country',
            'data' => $address_delivery['address_country_code'],
            'preferred_choices' => self::$config['countries']['preferred']
        ))

        ->add('note_id', 'hidden', array(
            'data' => isset($contact['note'][0]['note_id']) ? $contact['note'][0]['note_id'] : -1
        ))
        ->add('note', 'textarea', array(
            'required' => false,
            'label' => 'Note',
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
                    'label' => ucfirst(str_replace('_', ' ', strtolower($field['extra_type_name']))),
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

        return array(
            'contact' => array(
                'contact_id' => $data['contact_id'],
                'contact_type' => 'COMPANY',
                'contact_name' => isset($data['contact_name']) ? $data['contact_name'] : null,
                'contact_login' => isset($data['contact_login']) ? $data['contact_login'] : null,
                'contact_status' => isset($data['contact_status']) ? $data['contact_status'] : 'ACTIVE'
            ),
            'category' => array(
                array(
                    'contact_id' => $data['contact_id'],
                    'category_type_name' => isset($data['category']) ? $data['category'] : ''
                )
            ),
            'tag' => $tags,
            'company' => array(
                array(
                    'company_id' => $data['company_id'],
                    'contact_id' => $data['contact_id'],
                    'company_name' => $data['company_name'],
                    'company_department' => $data['company_department'],
                    'company_additional' => $data['company_additional'],
                    'company_additional_2' => $data['company_additional_2'],
                    'company_additional_3' => $data['company_additional_3'],
                    'company_primary_address_id' => $data['address_business_id'],
                    'company_primary_phone_id' => $data['phone_id'],
                    'company_primary_email_id' => $data['email_id'],
                    'company_primary_note_id' => $data['note_id']
                )
            ),
            'communication' => array(
                array(
                    // email
                    'communication_id' => $data['email_id'],
                    'contact_id' => $data['contact_id'],
                    'communication_type' => 'EMAIL',
                    'communication_usage' => 'BUSINESS',
                    'communication_value' => $data['email_value']
                ),
                array(
                    // phone
                    'communication_id' => $data['phone_id'],
                    'contact_id' => $data['contact_id'],
                    'communication_type' => 'PHONE',
                    'communication_usage' => 'BUSINESS',
                    'communication_value' => $data['phone']
                ),
                array(
                    // cell
                    'communication_id' => $data['cell_id'],
                    'contact_id' => $data['contact_id'],
                    'communication_type' => 'CELL',
                    'communication_usage' => 'BUSINESS',
                    'communication_value' => $data['cell']
                ),
                array(
                    // fax
                    'communication_id' => $data['fax_id'],
                    'contact_id' => $data['contact_id'],
                    'communication_type' => 'FAX',
                    'communication_usage' => 'BUSINESS',
                    'communication_value' => $data['fax']
                ),
                array(
                    // url
                    'communication_id' => $data['url_id'],
                    'contact_id' => $data['contact_id'],
                    'communication_type' => 'URL',
                    'communication_usage' => 'BUSINESS',
                    'communication_value' => $data['url']
                )
            ),
            'address' => array(
                array(
                    'address_id' => $data['address_business_id'],
                    'contact_id' => $data['contact_id'],
                    'address_type' => 'BUSINESS',
                    'address_street' => $data['address_business_street'],
                    'address_zip' => $data['address_business_zip'],
                    'address_city' => $data['address_business_city'],
                    'address_area' => $data['address_business_area'],
                    'address_state' => $data['address_business_state'],
                    'address_country_code' => $data['address_business_country']
                ),
                array(
                    'address_id' => $data['address_delivery_id'],
                    'contact_id' => $data['contact_id'],
                    'address_type' => 'DELIVERY',
                    'address_street' => $data['address_delivery_street'],
                    'address_zip' => $data['address_delivery_zip'],
                    'address_city' => $data['address_delivery_city'],
                    'address_country_code' => $data['address_delivery_country']
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
     * Default controller for the company dialog
     *
     * @param Application $app
     * @param string $contact_id
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

        $contact = $this->ContactControl->select(self::$contact_id, 'COMPANY');

        if ($this->ContactControl->isMessage()) {
            self::$message = $this->ContactControl->getMessage();
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
                $contact = $this->getFormData($form->getData(), $extra_info);
                self::$contact_id = $contact['contact']['contact_id'];

                if (self::$contact_id < 1) {
                    // insert a new record
                    $this->ContactControl->insert($contact, self::$contact_id);
                }
                else {
                    // update the record
                    $has_changed = false; // indicate changes
                    $this->ContactControl->update($contact, self::$contact_id, $has_changed);
                }

                if (!$this->ContactControl->isMessage()) {
                    $this->setMessage("The contact process has not returned a status message");
                }
                else {
                    // use the return status messages
                    self::$message = $this->ContactControl->getMessage();
                }

                // get the values of the new or updated record
                $contact = $this->ContactControl->select(self::$contact_id, 'COMPANY');
                // get the form fields
                $contact_form = $this->getFormFields($contact, $extra_info);
                // get the form
                $form = $contact_form->getForm();
            }
            else {
                // general error (timeout, CSFR ...)
                $this->setMessage('The form is not valid, please check your input and try again!');
            }
        }

        return $this->app['twig']->render($this->app['utils']->getTemplateFile(self::$options['template']['namespace'], self::$options['template']['contact']),
            array(
                'message' => $this->getMessage(),
                'form' => $form->createView(),
                'route' => self::$options['route'],
                'extra' => $extra,
                'extra_info' => $extra_info
            ));
    }
}
