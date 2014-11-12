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
use Symfony\Component\Validator\Constraints as Assert;
use phpManufaktur\Contact\Data\Contact\Form as FormData;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpFoundation\Response;

class Form extends Basic
{

    private static $form_config = null;
    private static $form_name = null;

    private static $general_form_fields = array(
        'checkbox',
        'email',
        'hidden',
        'radio',
        'select',
        'single_checkbox',
        'text',
        'textarea',
        'url'
        );

    private static $contact_form_fields = array(
        //'contact_type',
        'person_gender',
        //'person_title',
        'person_first_name',
        'person_last_name',
        'person_nick_name',
        //'person_birthday',
        //'company_name',
        //'company_department',
        'communication_email',
        'communication_phone',
        'address_street',
        'address_zip',
        'address_city',
        //'address_area',
        //'address_state',
        //'address_country_code',
        'tags'
    );

    /**
     * Check if the field name is valid and contains only allowed characters
     *
     * @param string $name
     * @return boolean
     */
    protected static function isValidName($name)
    {
        return '' === $name || null === $name || preg_match('/^[a-zA-Z0-9_][a-zA-Z0-9_\-:]*$/D', $name);
    }

    /**
     * Create the form with the form factory
     *
     * @param array $data
     * @return boolean
     */
    protected function getForm($data=array())
    {
        if (!isset(self::$form_config['fields'])) {
            $this->setAlert('Missing the field definitions in `form.json`!', array(), self::ALERT_TYPE_DANGER);
            return false;
        }

        $form = $this->app['form.factory']->createBuilder('form');

        foreach (self::$form_config['fields'] as $key => $field) {

            if (!isset($field['type'])) {
                $this->setAlert('Missing the `type` field in the definition!', array(), self::ALERT_TYPE_DANGER);
                return false;
            }
            if (in_array($field['type'], self::$general_form_fields)) {

                if (!isset($field['name'])) {
                    $this->setAlert('Missing the `name` field in the definition!', array(), self::ALERT_TYPE_DANGER);
                    return false;
                }
                elseif (!self::isValidName($field['name'])) {
                    $this->setAlert('The name "%name%" contains illegal characters. Names should start with a letter, digit or underscore and only contain letters, digits, numbers, underscores ("_"), hyphens ("-") and colons (":").',
                        array('%name%' => $field['name']), self::ALERT_TYPE_DANGER);
                    return false;
                }

                // this is a general form field
                switch ($field['type']) {
                    case 'email':
                    case 'text':
                    case 'textarea':
                    case 'url':
                        // create general form fields
                        $value = isset($field['value']) ? $field['value'] : '';
                        $settings = array(
                            'data' => isset($data[$field['name']]) ? $data[$field['name']] : $value,
                            'label' => isset($field['label']) ? $field['label'] : $this->app['utils']->humanize($field['name']),
                            'required' => isset($field['required']) ? $field['required'] : false
                        );
                        if (isset($field['help']) && !empty($field['help'])) {
                            $settings['attr']['help'] = $field['help'];
                        }
                        $form->add($field['name'], $field['type'], $settings);
                        break;
                    case 'hidden':
                        // create hidden form fields
                        $value = isset($field['value']) ? $field['value'] : null;
                        $form->add($field['name'], 'hidden', array(
                            'data' => isset($data[$field['name']]) ? $data[$field['name']] : $value
                        ));
                        break;
                    case 'single_checkbox':
                        // create only a single checkbox
                        $value = isset($field['value']) ? $field['value'] : 1;
                        $checked = isset($field['checked']) ? (bool) $field['checked'] : false;
                        $settings = array(
                            'value' => $value,
                            'data' => isset($data[$field['name']]) ? ($data[$field['name']] == $value) : $checked,
                            'label' => isset($field['label']) ? $field['label'] : $this->app['utils']->humanize($field['name']),
                            'required' => isset($field['required']) ? $field['required'] : false
                        );
                        if (isset($field['help']) && !empty($field['help'])) {
                            $settings['attr']['help'] = $field['help'];
                        }
                        $form->add($field['name'], 'checkbox', $settings);
                        break;
                    case 'checkbox':
                    case 'radio':
                    case 'select':
                        // create a select dropdown field
                        if (!isset($field['choices']) || !is_array($field['choices'])) {
                            $this->setAlert('Fields of type `select`, `radio` or `checkbox` need one or more values defined as array in `choices`!',
                                array(), self::ALERT_TYPE_DANGER);
                            return false;
                        }
                        $value = null;
                        if (isset($field['value'])) {
                            if ($field['type'] == 'checkbox') {
                                // values must be always given as array!
                                $value = (is_array($field['value'])) ? $field['value'] : array($field['value']);
                            }
                            else {
                                $value = $field['value'];
                            }
                        }
                        $settings = array(
                            'choices' => $field['choices'],
                            'expanded' => ($field['type'] != 'select'),
                            'multiple' => ($field['type'] == 'checkbox'),
                            'empty_value' => isset($field['empty_value']) ? $field['empty_value'] : '- please select -',
                            'data' => isset($data[$field['name']]) ? $data[$field['name']] : $value,
                            'label' => isset($field['label']) ? $field['label'] : $this->app['utils']->humanize($field['name']),
                            'required' => isset($field['required']) ? $field['required'] : false
                        );
                        if (isset($field['help']) && !empty($field['help'])) {
                            $settings['attr']['help'] = $field['help'];
                        }
                        $form->add($field['name'], 'choice', $settings);
                        break;
                    default:
                        $this->setAlert('Missing the handling for the field type `%type%`, please contact the support!',
                            array('%type%' => $field['type']), self::ALERT_TYPE_DANGER);
                        return false;
                }
            }
            elseif (in_array($field['type'], self::$contact_form_fields)) {
                // this is a contact field

                if (isset($field['name'])) {
                    // unset an existing field name!
                    unset(self::$form_config['fields'][$key]['name']);
                }
                // set the field name equal to the field type!
                self::$form_config['fields'][$key]['name'] = $field['type'];

                switch ($field['type']) {
                    case 'person_gender':
                        $value = isset($field['value']) ? $field['value'] : 'MALE';
                        $settings = array(
                            'choices' => array('MALE' => 'MALE', 'FEMALE' => 'FEMALE'),
                            'expanded' => false,
                            'multiple' => false,
                            'empty_value' => isset($field['empty_value']) ? $field['empty_value'] : '- please select -',
                            'data' => isset($data[$field['type']]) ? $data[$field['type']] : $value,
                            'label' => isset($field['label']) ? $field['label'] : $this->app['utils']->humanize($field['type']),
                            'required' => isset($field['required']) ? $field['required'] : true
                        );
                        if (isset($field['help']) && !empty($field['help'])) {
                            $settings['attr']['help'] = $field['help'];
                        }
                        $form->add($field['type'], 'choice', $settings);
                        break;
                    case 'communication_email':
                        $value = isset($field['value']) ? $field['value'] : '';
                        $settings = array(
                            'data' => isset($data[$field['type']]) ? $data[$field['type']] : $value,
                            'label' => isset($field['label']) ? $field['label'] : $this->app['utils']->humanize($field['type']),
                            'required' => true
                        );
                        if (isset($field['help']) && !empty($field['help'])) {
                            $settings['attr']['help'] = $field['help'];
                        }
                        $form->add($field['type'], 'email', $settings);
                        break;
                    case 'tags':
                        // set the contact TAGS
                        if (isset($field['value'])) {
                            $value = is_array($field['value']) ? implode(',', $field['value']) : $field['value'];
                        }
                        else {
                            $value = null;
                        }
                        $form->add($field['type'], 'hidden', array(
                            'data' => $value
                        ));
                        break;
                    case 'person_first_name':
                    case 'person_last_name':
                    case 'person_nick_name':
                    case 'communication_phone':
                    case 'address_street':
                    case 'address_zip':
                    case 'address_city':
                        $value = isset($field['value']) ? $field['value'] : '';
                        $settings = array(
                            'data' => isset($data[$field['type']]) ? $data[$field['type']] : $value,
                            'label' => isset($field['label']) ? $field['label'] : $this->app['utils']->humanize($field['type']),
                            'required' => isset($field['required']) ? $field['required'] : true
                        );
                        if (isset($field['help']) && !empty($field['help'])) {
                            $settings['attr']['help'] = $field['help'];
                        }
                        $form->add($field['type'], 'text', $settings);
                        break;
                }
            }
            else {
                $this->setAlert('There exists no handling for the field type `%type%` neither as form nor as contact field!',
                        array('%type%' => $field['type']), self::ALERT_TYPE_DANGER);
                return false;
            }
        }

        // return the form
        return $form->getForm();
    }

    /**
     * Check the form values and collect the form data and prepare data for
     * saving the form and contact data
     *
     * @param array reference $data
     * @param array reference $form_data
     * @param array reference $contact_data
     * @return boolean
     */
    protected function checkForm(&$data=array(), &$form_data=array(), &$contact_data=array())
    {
        if (false === ($form = $this->getForm())) {
            return false;
        }

        // get the requested data
        $form->bind($this->app['request']);

        if ($form->isValid()) {
            // the form is valid now check the data
            $data = $form->getData();
            $form_data = array();
            $contact_data = array();

            // first step: check the reCaptcha!
            if (!$this->app['recaptcha']->isValid()) {
                $this->setAlert($this->app['recaptcha']->getLastError(), array(), self::ALERT_TYPE_DANGER);
                return false;
            }

            $checked = true;

            foreach (self::$form_config['fields'] as $field) {
                if (in_array($field['type'], self::$general_form_fields)) {
                    switch ($field['type']) {
                        case 'email':
                            // check the email address
                            $form_data[$field['name']] = '';
                            if (isset($data[$field['name']]) && (!empty($data[$field['name']]))) {
                                $errors = $this->app['validator']->validateValue($data[$field['name']], new Assert\Email());
                                if (count($errors) > 0) {
                                    $this->setAlert('The email address %email% is invalid!',
                                        array('%email%' => $data[$field['name']]), self::ALERT_TYPE_WARNING);
                                    $checked = false;
                                }
                                else {
                                    $form_data[$field['name']] = strtolower($data[$field['name']]);
                                }
                            }

                            if (isset($field['required']) && $field['required'] && empty($form_data[$field['name']])) {
                                $this->setAlert('The field %field% is required, please check your input!',
                                    array('%field%' => isset($field['label']) ? $field['label'] : $this->app['utils']->humanize($field['name'])),
                                    self::ALERT_TYPE_WARNING);
                                $checked = false;
                            }
                            break;

                        case 'url':
                            // check the URL
                            $form_data[$field['name']] = '';
                            if (isset($data[$field['name']]) && (!empty($data[$field['name']]))) {
                                $errors = $this->app['validator']->validateValue($data[$field['name']], new Assert\Url());
                                if (count($errors) > 0) {
                                    $this->setAlert('The URL %url% is not valid, accepted is a pattern like http://example.com or https://www.example.com.',
                                    array('%url%' => $data[$field['name']]), self::ALERT_TYPE_WARNING);
                                    $checked = false;
                                }
                                else {
                                    $form_data[$field['name']] = strtolower($data[$field['name']]);
                                }
                            }

                            if (isset($field['required']) && $field['required'] && empty($form_data[$field['name']])) {
                                $this->setAlert('The field %field% is required, please check your input!',
                                    array('%field%' => isset($field['label']) ? $field['label'] : $this->app['utils']->humanize($field['name'])),
                                    self::ALERT_TYPE_WARNING);
                                $checked = false;
                            }
                            break;

                        case 'hidden':
                        case 'text':
                        case 'textarea':
                            // check text input and textarea
                            $form_data[$field['name']] = isset($data[$field['name']]) ? $data[$field['name']] : '';

                            if (isset($field['required']) && $field['required'] && empty($form_data[$field['name']])) {
                                $this->setAlert('The field %field% is required, please check your input!',
                                    array('%field%' => isset($field['label']) ? $field['label'] : $this->app['utils']->humanize($field['name'])),
                                    self::ALERT_TYPE_WARNING);
                                $checked = false;
                            }
                            break;

                        case 'radio':
                        case 'select':
                        case 'single_checkbox':
                            $form_data[$field['name']] = isset($data[$field['name']]) ? $data[$field['name']] : '';

                            if (isset($field['required']) && $field['required'] && empty($form_data[$field['name']])) {
                                $this->setAlert('The field %field% is required, please check your input!',
                                    array('%field%' => isset($field['label']) ? $field['label'] : $this->app['utils']->humanize($field['name'])),
                                    self::ALERT_TYPE_WARNING);
                                $checked = false;
                            }
                            break;

                        case 'checkbox':
                            $form_data[$field['name']] = isset($data[$field['name']]) ? implode(',', $data[$field['name']]) : '';

                            if (isset($field['required']) && $field['required'] && empty($form_data[$field['name']])) {
                                $this->setAlert('The field %field% is required, please check your input!',
                                    array('%field%' => isset($field['label']) ? $field['label'] : $this->app['utils']->humanize($field['name'])),
                                    self::ALERT_TYPE_WARNING);
                                $checked = false;
                            }
                            break;
                    }
                }
                elseif (in_array($field['type'], self::$contact_form_fields)) {
                    switch ($field['type']) {
                        case 'communication_email':
                            // check the email address
                            $contact_data[$field['name']] = '';
                            if (isset($data[$field['name']]) && (!empty($data[$field['name']]))) {
                                $errors = $this->app['validator']->validateValue($data[$field['name']], new Assert\Email());
                                if (count($errors) > 0) {
                                    $this->setAlert('The email address %email% is invalid!',
                                        array('%email%' => $data[$field['name']]), self::ALERT_TYPE_WARNING);
                                    $checked = false;
                                }
                                else {
                                    $contact_data[$field['name']] = strtolower($data[$field['name']]);
                                }
                            }
                            else {
                                $this->setAlert('The field %field% is required, please check your input!',
                                    array('%field%' => isset($field['label']) ? $field['label'] : $this->app['utils']->humanize($field['name'])),
                                    self::ALERT_TYPE_WARNING);
                                $checked = false;
                            }
                            break;
                        case 'person_gender':
                            $contact_data[$field['name']] = isset($data[$field['name']]) ? $data[$field['name']] : 'MALE';
                            break;
                        case 'tags':
                            $contact_data[$field['name']] = isset($data[$field['name']]) ? $data[$field['name']] : '';
                            break;
                        case 'person_first_name':
                        case 'person_last_name':
                        case 'person_nick_name':
                        case 'communication_phone':
                        case 'address_street':
                        case 'address_zip':
                        case 'address_city':
                            // check text input and textarea
                            $contact_data[$field['name']] = isset($data[$field['name']]) ? $data[$field['name']] : '';

                            if (isset($field['required']) && $field['required'] && empty($form_data[$field['name']])) {
                                $this->setAlert('The field %field% is required, please check your input!',
                                    array('%field%' => isset($field['label']) ? $field['label'] : $this->app['utils']->humanize($field['name'])),
                                    self::ALERT_TYPE_WARNING);
                                $checked = false;
                            }
                            break;
                    }
                }
            }

            // return the check result
            return $checked;
        }
        else {
            // general error (timeout, CSFR ...)
            $this->setAlert('The form is not valid, please check your input and try again!', array(),
                self::ALERT_TYPE_DANGER, true, array('form_errors' => $form->getErrorsAsString(),
                    'method' => __METHOD__, 'line' => __LINE__));
        }
        return false;
    }

    /**
     * Save the form and contact data
     *
     * @param array $form_data
     * @param array $contact_data
     */
    protected function saveFormData($form_data, $contact_data, &$form_record=array())
    {
        // first check the contact data
        $contact_id = -1;

        if (!empty($contact_data) && isset($contact_data['communication_email'])) {
            // check the contact information and create a new or update an existing record

            if (false !== ($contact_id = $this->app['contact']->existsLogin($contact_data['communication_email']))) {
                // this contact already exists!
                $contact = $this->app['contact']->select($contact_id);
                $data = array();

                foreach ($contact['person'][0] as $key => $value) {
                    if (isset($contact_data[$key]) && !empty($contact_data[$key]) &&
                        ($contact_data[$key] != $value)) {
                        $data['person'][0]['contact_id'] = $contact_id;
                        $data['person'][0]['person_id'] = $contact['person'][0]['person_id'];
                        $data['person'][0][$key] = $contact_data[$key];
                    }
                }

                foreach ($contact['address'][0] as $key => $value) {
                    if (isset($contact_data[$key]) && !empty($contact_data[$key]) &&
                        ($contact_data[$key] != $value)) {
                        $data['address'][0]['contact_id'] = $contact_id;
                        $data['address'][0]['address_id'] = $contact['address'][0]['address_id'];
                        $data['address'][0][$key] = $contact_data[$key];
                    }
                }

                $phone_checked = false;
                foreach ($contact['communication'] as $communication) {
                    // update existing communication entries
                    if (($communication['communication_type'] == 'EMAIL') &&
                        ($communication['communication_usage'] == 'PRIMARY') &&
                        !empty($contact_data['communication_email']) &&
                        ($communication['communication_value'] != $contact_data['communication_email'])) {
                        $data['communication'][] = array(
                            'contact_id' => $contact_id,
                            'communication_id' => $communication['communication_id'],
                            'communication_value' => $contact_data['communication_email']
                        );
                    }
                    elseif (($communication['communication_type'] == 'PHONE') &&
                        ($communication['communication_usage'] == 'PRIMARY') &&
                        !empty($contact_data['communication_phone']) &&
                        ($communication['communication_value'] != $contact_data['communication_phone'])) {
                        $data['communication'][] = array(
                            'contact_id' => $contact_id,
                            'communication_id' => $communication['communication_id'],
                            'communication_value' => $contact_data['communication_phone']
                        );
                        $phone_checked = true;
                    }
                }


                if (!$phone_checked && !empty($contact_data['communication_phone'])) {
                    // add a communication entry for the phone
                    $data['communication'][] = array(
                        'contact_id' => $contact_id,
                        'communication_id' => -1,
                        'communication_type' => 'PHONE',
                        'communication_value' => $contact_data['communication_phone'],
                        'communication_usage' => 'PRIMARY'
                    );
                }


                if (!empty($data)) {
                    $data['contact']['contact_id'] = $contact_id;
                    if (!$this->app['contact']->update($data, $contact_id)) {
                        return false;
                    }
                }
            }
            else {
                // create a new contact
                $data = array(
                    'contact' => array(
                        'contact_id' => -1,
                        'contact_name' => $contact_data['communication_email'],
                        'contact_login' => $contact_data['communication_email'],
                        'contact_type' => 'PERSON'
                    ),
                    'person' => array(
                        array(
                            'contact_id' => -1,
                            'person_id' => -1,
                            'person_gender' => isset($contact_data['person_gender']) ? $contact_data['person_gender'] : 'MALE',
                            'person_title' => isset($contact_data['person_title']) ? $contact_data['person_title'] : 'NO_TITLE',
                            'person_first_name' => isset($contact_data['person_first_name']) ? $contact_data['person_first_name'] : '',
                            'person_last_name' => isset($contact_data['person_last_name']) ? $contact_data['person_last_name'] : '',
                            'person_nick_name' => isset($contact_data['person_nick_name']) ? $contact_data['person_nick_name'] : '',
                            'person_birthday' => isset($contact_data['person_birthday']) ? $contact_data['person_birthday'] : '0000-00-00'
                        )
                    ),
                    'address' => array(
                        array(
                            'contact_id' => -1,
                            'address_id' => -1,
                            'address_type' => 'PRIMARY', // PRIVATE is no longer in use,
                            'address_street' => isset($contact_data['address_street']) ? $contact_data['address_street'] : '',
                            'address_zip' => isset($contact_data['address_zip']) ? $contact_data['address_zip'] : '',
                            'address_city' => isset($contact_data['address_city']) ? $contact_data['address_city'] : ''
                        )
                    ),
                    'communication' => array(
                        array(
                            'contact_id' => -1,
                            'communication_id' => -1,
                            'communication_type' => 'EMAIL',
                            'communication_value' => $contact_data['communication_email'],
                            'communication_usage' => 'PRIMARY'
                        ),
                        array(
                            'contact_id' => -1,
                            'communication_id' => -1,
                            'communication_type' => 'PHONE',
                            'communication_value' => isset($contact_data['communication_phone']) ? $contact_data['communication_phone'] : '',
                            'communication_usage' => 'PRIMARY'
                        )
                    )
                );
                if (!$this->app['contact']->insert($data, $contact_id)) {
                    // problem inserting the new contact!
                    return false;
                }
            }

            // check if the TAG `FORMS` exists and create it if needed
            if (!$this->app['contact']->existsTagName('FORMS')) {
                $this->app['contact']->createTagName('FORMS',
                    $this->app['translator']->trans('This tag will be assigned to all user-defined `Contact` forms.'));
            }

            $tags = array();
            $tags[] = 'FORMS';

            if (isset($contact_data['tags']) && !empty($contact_data['tags'])) {
                // check user defined TAGS
                if (false !== strpos($contact_data['tags'], ',')) {
                    $user_tags = explode(',', $contact_data['tags']);
                    foreach ($user_tags as $user_tag) {
                        $user_tag = strtoupper(trim($user_tag));
                        if (!empty($user_tag)) {
                            // check the TAG
                            $tag_name = null;
                            if (!$this->app['contact']->validateTagName($user_tag, $tag_name)) {
                                // failed the tag name check!
                                $this->app['monolog']->addDebug('The user defined TAG '.$user_tag.' failed the tag name check!');
                                continue;
                            }
                            if (!$this->app['contact']->existsTagName($tag_name)) {
                                // create the TAG
                                $this->app['contact']->createTagName($tag_name,
                                    sprintf('This tag was automatically generated for the form %s', self::$form_name));
                            }
                            $tags[] = $tag_name;
                        }
                    }
                }
                else {
                    $tags[] = strtoupper(trim($contact_data['tags']));
                }
            }

            if (isset(self::$form_config['tags']) && is_array(self::$form_config['tags'])) {
                foreach (self::$form_config['tags'] as $key => $tag) {
                    if (!isset($tag['name'])) {
                        $this->setAlert('Missing the field `name` in the `form.json` tag definition!',
                            array(), self::ALERT_TYPE_DANGER);
                        return false;
                    }
                    $tag_name = null;
                    if (!$this->app['contact']->validateTagName($tag['name'], $tag_name)) {
                        // failed the tag name check!
                        return false;
                    }
                    if ($this->app['contact']->existsTagName($tag_name)) {
                        // this tag already exists, add the tag to the array
                        $tags[] = $tag_name;
                        continue;
                    }
                    // add a description to the tag
                    $tag_description = isset($tag['description']) ? $tag['description'] :
                    sprintf('This tag was automatically generated for the form %s', self::$form_name);
                    $this->app['contact']->createTagName($tag_name, $tag_description);
                    $tags[] = $tag_name;
                }
            }

            // loop through the tags and check if they are assigned to the contact
            foreach ($tags as $tag) {
                if (!$this->app['contact']->issetContactTag($tag, $contact_id)) {
                    $this->app['contact']->setContactTag($tag, $contact_id);
                }
            }
        }

        $form_record = array(
            'contact_id' => $contact_id,
            'contact_data' => json_encode($contact_data),
            'form_name' => self::$form_name,
            'form_config' => json_encode(self::$form_config),
            'form_data' => json_encode($form_data),
            'form_submitted_when' => date('Y-m-h H:i:s'),
            'form_submitter_ip' => $_SERVER['REMOTE_ADDR']
        );

        $FormData = new FormData($this->app);
        $FormData->insert($form_record);
        return true;
    }

    /**
     * Send mails to the provider and the submitter of the form
     *
     * @param array $form_data
     * @param array $contact_data
     * @param array $form_record
     * @return boolean
     */
    protected function sendMails($form_data, $contact_data, $form_record)
    {
        if (isset(self::$form_config['email']['enabled']) && !self::$form_config['email']['enabled']) {
            // email submission is disabled!
            return true;
        }

        // set the sender name and email
        $from_email = isset(self::$form_config['email']['mail_from']['email']) &&
            !empty(self::$form_config['email']['mail_from']['email']) ?
            self::$form_config['email']['mail_from']['email'] : SERVER_EMAIL_ADDRESS;
        $from_name = isset(self::$form_config['email']['mail_from']['name']) &&
            !empty(self::$form_config['email']['mail_from']['name']) ?
            self::$form_config['email']['mail_from']['name'] : SERVER_EMAIL_NAME;

        // set the mail_to fields
        $mail_to = $from_email;
        if (isset(self::$form_config['email']['mail_to']) && is_array(self::$form_config['email']['mail_to']) &&
            !empty(self::$form_config['email']['mail_to'])) {
            $mail_to = is_array(self::$form_config['email']['mail_to']) ? self::$form_config['email']['mail_to'] :
                array(self::$form_config['email']['mail_to']);
        }

        // set the email for the form submitter
        $submitter_email = null;
        foreach (self::$form_config['fields'] as $field) {
            if (isset($field['type']) && ($field['type'] == 'communication_email')) {
                if (isset($contact_data[$field['name']]) && !empty($contact_data[$field['name']])) {
                    // use the communication_email from the contact data
                    $submitter_email = $contact_data[$field['name']];
                    break;
                }
            }
        }
        if (null === $submitter_email) {
            foreach (self::$form_config['fields'] as $field) {
                if (isset($field['type']) && ($field['type'] == 'email')) {
                    if (isset($form_data[$field['name']]) && !empty($form_data[$field['name']])) {
                        // use the standard email field from the form data
                        $submitter_email = $form_data[$field['name']];
                        break;
                    }
                }
            }
        }

        // set the email header
        $header = isset(self::$form_config['email']['header']) ? self::$form_config['email']['header'] :
            $this->app['translator']->trans('Submission from form %form%', array('%form%' => self::$form_name));

        // send mail to the provider
        $body = $this->app['twig']->render($this->app['utils']->getTemplateFile(
            '@phpManufaktur/Contact/Template',
            '/form/'.self::$form_name.'/provider.mail.twig',
            $this->getPreferredTemplateStyle()),
            array(
                'form_data' => $form_data,
                'contact_data' => $contact_data,
                'form_record' => $form_record
            ));

        // create the message
        $message = \Swift_Message::newInstance()
            ->setSubject($this->app['translator']->trans($header))
            ->setFrom(array($from_email => $from_name))
            ->setTo($mail_to)
            ->setBody($body)
            ->setContentType('text/html');
        if (null !== $submitter_email) {
            $message->setReplyTo($submitter_email);
        }

        // send the message
        $failed = array();
        if (!$this->app['mailer']->send($message, $failed)) {
            $subject = $this->app['translator']->trans($header);
            $this->setAlert('Failed to send a email with the subject <b>%subject%</b> to the addresses: <b>%failed%</b>.',
                array('%subject%' => $subject,
                    '%failed%' => implode(', ', $failed)), self::ALERT_TYPE_DANGER);
            return false;
        }

        if (null !== $submitter_email) {
            // send mail to the submitter of the form
            $body = $this->app['twig']->render($this->app['utils']->getTemplateFile(
                '@phpManufaktur/Contact/Template',
                '/form/'.self::$form_name.'/submitter.mail.twig',
                $this->getPreferredTemplateStyle()),
                array(
                    'form_data' => $form_data,
                    'contact_data' => $contact_data,
                    'form_record' => $form_record
                ));

            // create the message
            $message = \Swift_Message::newInstance()
                ->setSubject($this->app['translator']->trans($header))
                ->setFrom(array($from_email => $from_name))
                ->setTo($submitter_email)
                ->setBody($body)
                ->setContentType('text/html');

            // send the message
            $failed = array();
            if (!$this->app['mailer']->send($message, $failed)) {
                $subject = $this->app['translator']->trans($header);
                $this->setAlert('Failed to send a email with the subject <b>%subject%</b> to the addresses: <b>%failed%</b>.',
                    array('%subject%' => $subject,
                        '%failed%' => implode(', ', $failed)), self::ALERT_TYPE_DANGER);
                return false;
            }
        }
        // all done ...
        return true;
    }

    /**
     * Render the form and prompt it
     *
     * @param unknown $form
     */
    protected function renderForm($form)
    {
        return $this->app['twig']->render($this->app['utils']->getTemplateFile(
            '@phpManufaktur/Contact/Template',
            '/form/'.self::$form_name.'/form.twig',
            $this->getPreferredTemplateStyle()),
            array(
                'basic' => $this->getBasicSettings(),
                'form' => $form->createView(),
                'config' => self::$form_config,
                'route' => array(
                    'action' => FRAMEWORK_URL.'/contact/form/check?pid='.$this->getParameterID()
                )
            ));
    }

    /**
     * Central Controller for all form actions
     *
     * @param Application $app
     * @return string
     */
    public function ControllerFormAction(Application $app)
    {
        $this->initParameters($app);

        // get the kitCommand parameters
        $parameters = $this->getCommandParameters();

        // check the CMS GET parameters
        $GET = $this->getCMSgetParameters();
        if (isset($GET['command']) && ($GET['command'] == 'contact')) {
            foreach ($GET as $key => $value) {
                if ($key == 'command') continue;
                $parameters[$key] = $value;
            }
            $this->setCommandParameters($parameters);
        }

        if (!isset($parameters['form'])) {
            $this->setAlert('Missing the parameter `form`!', array(), self::ALERT_TYPE_DANGER);
            return $this->promptAlert();
        }
        self::$form_name = $parameters['form'];

        try {
            // get the form config file
            $config = $app['utils']->getTemplateFile('@phpManufaktur/Contact/Template',
            '/form/'.self::$form_name.'/form.json', $this->getPreferredTemplateStyle(), true);
            // read the form configuration
            self::$form_config = $app['utils']->readJSON($config);
        } catch (\Exception $e) {
            $this->setAlert($e->getMessage(), array(), self::ALERT_TYPE_DANGER);
            return $this->promptAlert();
        }

        $data = array();

        if ('POST' == $this->app['request']->getMethod()) {
            // the form was submitted!
            $form_data = array();
            $contact_data = array();

            if ($this->checkForm($data, $form_data, $contact_data)) {
                // submission successfull - save the form data
                $form_record = array();
                if ($this->saveFormData($form_data, $contact_data, $form_record)) {
                    // try to send emails
                    if ($this->sendMails($form_data, $contact_data, $form_record)) {
                        if (isset(self::$form_config['confirmation']['redirect']['route']) &&
                            !empty(self::$form_config['confirmation']['redirect']['route'])) {
                            // redirect to a internal route
                            $subRequest = Request::create(
                                self::$form_config['confirmation']['redirect']['route'],
                                'POST', array(
                                    'pid' => $this->getParameterID(),
                                    'ref' => 'form',
                                    'form_data' => $form_data,
                                    'contact_data' => $contact_data,
                                    'form_record' => $form_record
                                ));
                            return $app->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
                        }
                        elseif (isset(self::$form_config['confirmation']['redirect']['url']) &&
                            !empty(self::$form_config['confirmation']['redirect']['url'])) {
                            // redirect to internal or external URL
                            $params = '';
                            if (isset(self::$form_config['confirmation']['redirect']['parameter']) &&
                                self::$form_config['confirmation']['redirect']['parameter']) {
                                // attach the form data as base64 encoded parameters
                                $params = strpos(self::$form_config['confirmation']['redirect']['url'], '?') ? '&' : '?'.
                                    http_build_query(array(
                                        'ref' => 'form',
                                        'c' => base64_encode(json_encode($contact_data)),
                                        'f' => base64_encode(json_encode($form_data))
                                    ), '', '&');
                            }
                            // redirect must use a javascript to show the URL outside the iFrame
                            $redirect = "<script type=\"text/javascript\">".
                                "window.open('".self::$form_config['confirmation']['redirect']['url'].
                                $params."','_parent');</script>";
                            return new Response($redirect);
                        }
                        else {
                            // show the confirmation template
                            return $this->app['twig']->render($this->app['utils']->getTemplateFile(
                                '@phpManufaktur/Contact/Template',
                                '/form/'.self::$form_name.'/confirmation.twig',
                                $this->getPreferredTemplateStyle()),
                                array(
                                    'basic' => $this->getBasicSettings(),
                                    'config' => self::$form_config,
                                    'form_data' => $form_data,
                                    'contact_data' => $contact_data,
                                    'form_record' => $form_record
                                ));
                        }
                    }
                }
                $this->setAlert('Sorry, but there occured a problem while processing the form. We have informed the webmaster.',
                    array(), self::ALERT_TYPE_DANGER);
            }
        }

        if (false === ($form = $this->getForm($data))) {
            // problem building the form
            return $this->promptAlert();
        }

        // return the rendered form
        return $this->renderForm($form);
    }

}

