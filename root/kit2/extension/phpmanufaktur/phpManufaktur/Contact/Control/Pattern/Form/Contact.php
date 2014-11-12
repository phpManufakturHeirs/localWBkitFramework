<?php

/**
 * Contact
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de/Contact
 * @copyright 2014 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\Contact\Control\Pattern\Form;

use Silex\Application;
use phpManufaktur\Contact\Control\Configuration;
use phpManufaktur\Basic\Control\Pattern\Alert;
use phpManufaktur\Contact\Data\Contact\TagType;
use phpManufaktur\Contact\Data\Contact\ExtraCategory;
use phpManufaktur\Contact\Data\Contact\ExtraType;
use phpManufaktur\Contact\Data\Contact\CategoryType;
use Carbon\Carbon;

class Contact extends Alert
{
    protected static $config = null;
    protected static $usage = null;
    protected static $usage_param = null;

    protected static $person_fields = array(
        'person_id',
        'person_gender',
        'person_title',
        'person_first_name',
        'person_last_name',
        'person_nick_name',
        'person_birthday'
    );
    protected static $company_fields = array(
        'company_id',
        'company_name',
        'company_department',
        'company_additional',
        'company_additional_2',
        'company_additional_3'
    );
    protected static $must_fields = array(
        'contact_id',
        'contact_type',
        'communication_email',
        'person_id',
        'company_id',
        'address_id'
    );

    /**
     * (non-PHPdoc)
     * @see \phpManufaktur\Basic\Control\Pattern\Alert::initialize()
     */
    protected function initialize(Application $app)
    {
        parent::initialize($app);

        $Config = new Configuration($app);
        self::$config = $Config->getConfiguration();

        self::$usage = $this->app['request']->get('usage', 'framework');
        self::$usage_param = (self::$usage != 'framework') ? '?usage='.self::$usage : '';
    }

    /**
     * Select the contact record for the given contact ID. Prepare and return
     * data array for usage with the contact form
     *
     * @param integer $contact_id
     * @return mixed <boolean|array>
     */
    public function getData($contact_id)
    {
        $data = array();
        $contact = $this->app['contact']->select($contact_id);
        if ((!isset($contact['contact']['contact_id'])) || ($contact['contact']['contact_id'] < 1)) {
            return false;
        }

        // get contact section
        foreach ($contact['contact'] as $key => $value) {
            $data[$key] = $value;
        }

        // get the category
        if (isset($contact['category'][0]['category_type_id'])) {
            $data['category_name'] = $contact['category'][0]['category_type_name'];
            $data['category_type_id'] = $contact['category'][0]['category_type_id'];
        }

        // get the person section
        if (isset($contact['person'][0]) && ($contact['person'][0]['person_id'] > 0)) {
            foreach ($contact['person'][0] as $key => $value) {
                if ($key == 'contact_id') continue;
                $data[$key] = $value;
            }
        }

        // get the company section
        if (isset($contact['company'][0]) && ($contact['company'][0]['company_id'])) {
            foreach ($contact['company'][0] as $key => $value) {
                if ($key == 'contact_id') continue;
                $data[$key] = $value;
            }
        }

        if (isset($contact['communication']) && is_array($contact['communication'])) {
            foreach ($contact['communication'] as $communication) {
                if (($communication['communication_status'] == 'ACTIVE') &&
                    ($communication['communication_id'] > 0)) {
                    switch ($communication['communication_type']) {
                        case 'EMAIL':
                            if ($communication['communication_usage'] == 'PRIMARY') {
                                $data['communication_email_id'] = $communication['communication_id'];
                                $data['communication_email'] = $communication['communication_value'];
                            }
                            elseif (!isset($data['communication_email_secondary'])) {
                                $data['communication_email_secondary_id'] = $communication['communication_id'];
                                $data['communication_email_secondary'] = $communication['communication_value'];
                            }
                            break;
                        case 'PHONE':
                            if ($communication['communication_usage'] == 'PRIMARY') {
                                $data['communication_phone_id'] = $communication['communication_id'];
                                $data['communication_phone'] = $communication['communication_value'];
                            }
                            elseif (!isset($data['communication_phone_secondary'])) {
                                $data['communication_phone_secondary_id'] = $communication['communication_id'];
                                $data['communication_phone_secondary'] = $communication['communication_value'];
                            }
                            break;
                        case 'CELL':
                            if ($communication['communication_usage'] == 'PRIMARY') {
                                $data['communication_cell_id'] = $communication['communication_id'];
                                $data['communication_cell'] = $communication['communication_value'];
                            }
                            elseif (!isset($data['communication_cell_secondary'])) {
                                $data['communication_cell_secondary_id'] = $communication['communication_id'];
                                $data['communication_cell_secondary'] = $communication['communication_value'];
                            }
                            break;
                        case 'FAX':
                            if ($communication['communication_usage'] == 'PRIMARY') {
                                $data['communication_fax_id'] = $communication['communication_id'];
                                $data['communication_fax'] = $communication['communication_value'];
                            }
                            elseif (!isset($data['communication_fax_secondary'])) {
                                $data['communication_fax_secondary_id'] = $communication['communication_id'];
                                $data['communication_fax_secondary'] = $communication['communication_value'];
                            }
                            break;
                        case 'URL':
                            if ($communication['communication_usage'] == 'PRIMARY') {
                                $data['communication_url_id'] = $communication['communication_id'];
                                $data['communication_url'] = $communication['communication_value'];
                            }
                            elseif (!isset($data['communication_url_secondary'])) {
                                $data['communication_url_secondary_id'] = $communication['communication_id'];
                                $data['communication_url_secondary'] = $communication['communication_value'];
                            }
                            break;
                        default:
                            // nothing to do here ...
                            break;
                    }
                }
            }
        }

        if (isset($contact['address']) && is_array($contact['address'])) {
            foreach ($contact['address'] as $address) {
                if (($address['address_status'] == 'ACTIVE') && ($address['address_id'] > 0)) {
                    switch ($address['address_type']) {
                        case 'PRIVATE': // no longer in use!
                        case 'BUSINESS': // no longer in use!
                        case 'PRIMARY':
                            if (!isset($data['address_id'])) {
                                $data['address_id'] = $address['address_id'];
                                $data['address_street'] = $address['address_street'];
                                $data['address_zip'] = $address['address_zip'];
                                $data['address_city'] = $address['address_city'];
                                $data['address_area'] = $address['address_area'];
                                $data['address_state'] = $address['address_state'];
                                $data['address_country_code'] = $address['address_country_code'];
                            }
                            elseif (!isset($data['address_secondary_id'])) {
                                $data['address_secondary_id'] = $address['address_id'];
                                $data['address_secondary_street'] = $address['address_street'];
                                $data['address_secondary_zip'] = $address['address_zip'];
                                $data['address_secondary_city'] = $address['address_city'];
                                $data['address_secondary_area'] = $address['address_area'];
                                $data['address_secondary_state'] = $address['address_state'];
                                $data['address_secondary_country_code'] = $address['address_country_code'];
                            }
                            break;
                        case 'DELIVERY':
                            if (!isset($data['address_delivery_id'])) {
                                $data['address_delivery_id'] = $address['address_id'];
                                $data['address_delivery_street'] = $address['address_street'];
                                $data['address_delivery_zip'] = $address['address_zip'];
                                $data['address_delivery_city'] = $address['address_city'];
                                $data['address_delivery_area'] = $address['address_area'];
                                $data['address_delivery_state'] = $address['address_state'];
                                $data['address_delivery_country_code'] = $address['address_country_code'];
                            }
                            elseif (!isset($data['address_delivery_secondary_id'])) {
                                $data['address_delivery_secondary_id'] = $address['address_id'];
                                $data['address_delivery_secondary_street'] = $address['address_street'];
                                $data['address_delivery_secondary_zip'] = $address['address_zip'];
                                $data['address_delivery_secondary_city'] = $address['address_city'];
                                $data['address_delivery_secondary_area'] = $address['address_area'];
                                $data['address_delivery_secondary_state'] = $address['address_state'];
                                $data['address_delivery_secondary_country_code'] = $address['address_country_code'];
                            }
                            break;
                        case 'BILLING':
                            if (!isset($data['address_billing_id'])) {
                                $data['address_billing_id'] = $address['address_id'];
                                $data['address_billing_street'] = $address['address_street'];
                                $data['address_billing_zip'] = $address['address_zip'];
                                $data['address_billing_city'] = $address['address_city'];
                                $data['address_billing_area'] = $address['address_area'];
                                $data['address_billing_state'] = $address['address_state'];
                                $data['address_billing_country_code'] = $address['address_country_code'];
                            }
                            elseif (!isset($data['address_secondary_id'])) {
                                $data['address_billing_secondary_id'] = $address['address_id'];
                                $data['address_billing_secondary_street'] = $address['address_street'];
                                $data['address_billing_secondary_zip'] = $address['address_zip'];
                                $data['address_billing_secondary_city'] = $address['address_city'];
                                $data['address_billing_secondary_area'] = $address['address_area'];
                                $data['address_billing_secondary_state'] = $address['address_state'];
                                $data['address_billing_secondary_country_code'] = $address['address_country_code'];
                            }
                            break;
                        case 'OTHER':
                            if (!isset($data['address_other_id'])) {
                                $data['address_other_id'] = $address['address_id'];
                                $data['address_other_street'] = $address['address_street'];
                                $data['address_other_zip'] = $address['address_zip'];
                                $data['address_other_city'] = $address['address_city'];
                                $data['address_other_area'] = $address['address_area'];
                                $data['address_other_state'] = $address['address_state'];
                                $data['address_other_country_code'] = $address['address_country_code'];
                            }
                            elseif (!isset($data['address_secondary_id'])) {
                                $data['address_other_secondary_id'] = $address['address_id'];
                                $data['address_other_secondary_street'] = $address['address_street'];
                                $data['address_other_secondary_zip'] = $address['address_zip'];
                                $data['address_other_secondary_city'] = $address['address_city'];
                                $data['address_other_secondary_area'] = $address['address_area'];
                                $data['address_other_secondary_state'] = $address['address_state'];
                                $data['address_other_secondary_country_code'] = $address['address_country_code'];
                            }
                            break;
                    }
                }
            }
        }

        if (isset($contact['note'][0]) && is_array($contact['note'][0])) {
            $data['note_id'] = $contact['note'][0]['note_id'];
            $data['note'] = $contact['note'][0]['note_content'];
        }

        if (isset($contact['extra_fields']) && is_array($contact['extra_fields'])) {
            foreach ($contact['extra_fields'] as $extra) {
                $data['extra_'.strtolower($extra['extra_type_name'])] = $extra['extra_value'];
            }
        }

        if (isset($contact['tag']) && is_array($contact['tag'])) {
            foreach ($contact['tag'] as $tag) {
                $data['tags'][] = $tag['tag_name'];
            }
        }

        return $data;
    }



    /**
     * Check the contact form data record and return a regular contact record
     * for insert or update a contact
     *
     * @param array $data
     * @param array $field settings for the form
     * @param boolean $ignore_status override the check for the contact status
     * @return boolean|array
     */
    public function checkData($data, $field=array(), $ignore_status=false, &$validation_errors=array())
    {
        if (!is_array($field) || empty($field)) {
            // use the default configuration from config.contact.json
            $field = self::$config['pattern']['form']['contact']['field'];
        }

        // perform some checks for the fields
        if (isset($field['tags']) && is_array($field['tags'])) {
            $tags = array();
            foreach ($field['tags'] as $tag) {
                $tags[] = strtoupper($tag);
            }
            $field['tags'] = $tags;
        }

        foreach (self::$must_fields as $must_field) {
            // check the must have fields of the data record
            if (!array_key_exists($must_field, $data)) {
                $this->setAlert('Missing the field <strong>%field%</strong> in data record!',
                    array('%field%' => $must_field), self::ALERT_TYPE_DANGER);
                return false;
            }
        }

        $CategoryType = new CategoryType($this->app);

        if (isset($data['category_name']) && !is_null($data['category_name'])) {
            if (false !== ($category = $CategoryType->selectByName($data['category_name']))) {
                $data['category_type_id'] = $category['category_type_id'];
            }
        }

        if (($data['contact_id'] > 0) ||
            (false !== ($existing_id = $this->app['contact']->existsLogin($data['communication_email'])))) {

            $id = ($data['contact_id'] > 0) ? $data['contact_id'] : $existing_id;
            // select the existing record
            $existing_contact = $this->app['contact']->select($id);

            if ($existing_contact['contact']['contact_id'] != $id) {
                // the query return an empty contact record - this mean that the record is deleted or locked for any reason
                $this->setAlert('Sorry, but we have a problem. Please contact the webmaster and tell him to check the status of the email address %email%.',
                    array('%email%' => $data['communication_email']), self::ALERT_TYPE_DANGER);
                return false;
            }

            if (!$ignore_status && (!$this->app['account']->isAuthenticated() || !$this->app['account']->isGranted('ROLE_CONTACT_ADMIN'))) {
                // only admins can change the contact status and the contact type!
                if (($existing_contact['contact']['contact_status'] !== 'ACTIVE') &&
                    ((!in_array('contact_status', $field['visible']) ||
                     (in_array('contact_status', $field['visible']) && in_array('contact_status', $field['readonly']))))) {
                    // the existing contact is not ACTIVE and the status of the submitted record can not be changed!
                    $this->setAlert('There exists already a contact record for you, but the status of this record is <strong>%status%</strong>. Please contact the webmaster to activate the existing record.',
                        array('%status%' => $this->app['translator']->trans($this->app['utils']->humanize($existing_contact['contact']['contact_status']))),
                        self::ALERT_TYPE_WARNING);
                    return false;
                }

                if ($existing_contact['contact']['contact_type'] !== $data['contact_type']) {
                    // problem: the contact type differ!
                    $contact_type = $this->app['utils']->humanize($existing_contact['contact']['contact_type']);
                    $contact_type = $this->app['translator']->trans($contact_type);
                    $this->setAlert('There exists already a contact record for you, but this record is assigned to a <strong>%type%</strong> and can not be changed. Please use the same type or contact the webmaster.',
                        array('%type%' => $contact_type),
                        self::ALERT_TYPE_WARNING);
                    return false;
                }
            }

            // compare the existing data with the submitted data
            $data['contact_id'] = $existing_contact['contact']['contact_id'];
            $data['contact_status'] = (isset($data['contact_status']) && ($data['contact_status'] != $existing_contact['contact']['contact_status'])) ? $data['contact_status'] : $existing_contact['contact']['contact_status'];
            $data['contact_login'] = (isset($data['contact_login']) && !empty($data['contact_login']) && ($data['contact_login'] != $existing_contact['contact']['contact_login'])) ? $data['contact_login'] : $existing_contact['contact']['contact_login'];
            $data['contact_name'] = (isset($data['contact_name']) && !empty($data['contact_name']) && ($data['contact_name'] != $existing_contact['contact']['contact_name'])) ? $data['contact_name'] : $existing_contact['contact']['contact_name'];
            if (isset($data['contact_since']) && !empty($data['contact_since']) && ($data['contact_since'] != '0000-00-00')) {
                $dt = Carbon::createFromFormat($this->app['translator']->trans('DATE_FORMAT'), $data['contact_since']);
                $contact_since = $dt->toDateTimeString();
            }
            else {
                $contact_since = '0000-00-00';
            }
            $data['contact_since'] = (($contact_since != '0000-00-00') && ($contact_since != $existing_contact['contact']['contact_since'])) ? $contact_since : $existing_contact['contact']['contact_since'];

            // address data
            if (isset($existing_contact['address']) && is_array($existing_contact['address'])) {
                foreach ($existing_contact['address'] as $address) {
                    $prefix = null;
                    switch ($address['address_type']) {
                        case 'PRIMARY':
                            $prefix = 'address_'; break;
                        case 'SECONDARY':
                            $prefix = 'address_secondary_'; break;
                        case 'DELIVERY':
                            $prefix = 'address_delivery_'; break;
                        case 'DELIVERY_SECONDARY':
                            $prefix = 'address_delivery_secondary_'; break;
                        case 'BILLING':
                            $prefix = 'address_billing_'; break;
                        case 'BILLING_SECONDARY':
                            $prefix = 'address_billing_secondary_'; break;
                    }
                    if (!is_null($prefix)) {
                        $data[$prefix.'id'] = $address['address_id'];
                        $data[$prefix.'street'] = (isset($data[$prefix.'street']) && !empty($data[$prefix.'street']) && ($data[$prefix.'street'] != $address['address_street'])) ? $data[$prefix.'street'] : $address['address_street'];
                        $data[$prefix.'city'] = (isset($data[$prefix.'city']) && !empty($data[$prefix.'city']) && ($data[$prefix.'city'] != $address['address_city'])) ? $data[$prefix.'city'] : $address['address_city'];
                        $data[$prefix.'zip'] = (isset($data[$prefix.'zip']) && !empty($data[$prefix.'zip']) && ($data[$prefix.'zip'] != $address['address_zip'])) ? $data[$prefix.'zip'] : $address['address_zip'];
                        $data[$prefix.'area'] = (isset($data[$prefix.'area']) && !empty($data[$prefix.'area']) && ($data[$prefix.'area'] != $address['address_area'])) ? $data[$prefix.'area'] : $address['address_area'];
                        $data[$prefix.'state'] = (isset($data[$prefix.'state']) && !empty($data[$prefix.'state']) && ($data[$prefix.'state'] != $address['address_state'])) ? $data[$prefix.'state'] : $address['address_state'];
                        $data[$prefix.'country_code'] = (isset($data[$prefix.'country_code']) && !empty($data[$prefix.'country_code']) && ($data[$prefix.'country_code'] != $address['address_country_code'])) ? $data[$prefix.'country_code'] : $address['address_country_code'];
                        $data[$prefix.'identifier'] = (isset($data[$prefix.'identifier']) && !empty($data[$prefix.'identifier']) && ($data[$prefix.'identifier'] != $address['address_identifier'])) ? $data[$prefix.'identifier'] : $address['address_identifier'];
                        $data[$prefix.'appendix_1'] = (isset($data[$prefix.'appendix_1']) && !empty($data[$prefix.'appendix_1']) && ($data[$prefix.'appendix_1'] != $address['address_appendix_1'])) ? $data[$prefix.'appendix_1'] : $address['address_appendix_1'];
                        $data[$prefix.'appendix_2'] = (isset($data[$prefix.'appendix_2']) && !empty($data[$prefix.'appendix_2']) && ($data[$prefix.'appendix_2'] != $address['address_appendix_2'])) ? $data[$prefix.'appendix_2'] : $address['address_appendix_2'];
                        $data[$prefix.'description'] = (isset($data[$prefix.'description']) && !empty($data[$prefix.'description']) && ($data[$prefix.'description'] != $address['address_description'])) ? $data[$prefix.'description'] : $address['address_description'];
                    }
                }
            }

            if ($data['contact_type'] == 'PERSON') {
                $data['person_id'] = (($data['person_id'] > 0) && ($data['person_id'] != $existing_contact['person'][0]['person_id'])) ? $data['person_id'] : $existing_contact['person'][0]['person_id'];
                $data['person_gender'] = (isset($data['person_gender']) && !empty($data['person_gender']) && ($data['person_gender'] != $existing_contact['person'][0]['person_gender'])) ? $data['person_gender'] : $existing_contact['person'][0]['person_gender'];
                $data['person_title'] = (isset($data['person_title']) && !empty($data['person_title']) && ($data['person_title'] != $existing_contact['person'][0]['person_title'])) ? $data['person_title'] : $existing_contact['person'][0]['person_title'];
                $data['person_first_name'] = (isset($data['person_first_name']) && !empty($data['person_first_name']) && ($data['person_first_name'] != $existing_contact['person'][0]['person_first_name'])) ? $data['person_first_name'] : $existing_contact['person'][0]['person_first_name'];
                $data['person_last_name'] = (isset($data['person_last_name']) && !empty($data['person_last_name']) && ($data['person_last_name'] != $existing_contact['person'][0]['person_last_name'])) ? $data['person_last_name'] : $existing_contact['person'][0]['person_last_name'];
                $data['person_nick_name'] = (isset($data['person_nick_name']) && !empty($data['person_nick_name']) && ($data['person_nick_name'] != $existing_contact['person'][0]['person_nick_name'])) ? $data['person_nick_name'] : $existing_contact['person'][0]['person_nick_name'];
                if (isset($data['person_birthday']) && !empty($data['person_birthday']) && ($data['person_birthday'] != '0000-00-00')) {
                    $dt = Carbon::createFromFormat($this->app['translator']->trans('DATE_FORMAT'), $data['person_birthday']);
                    $birthday = $dt->toDateTimeString();
                }
                else {
                    $birthday = $existing_contact['person'][0]['person_birthday'];
                }
                $data['person_birthday'] = (($birthday != '0000-00-00') && ($birthday != $existing_contact['person'][0]['person_birthday'])) ?
                $birthday : $existing_contact['person'][0]['person_birthday'];
            }
            else {
                // COMPANY
                $data['company_id'] = (($data['company_id'] > 0) && ($data['company_id'] != $existing_contact['company'][0]['company_id'])) ? $data['company_id'] : $existing_contact['company'][0]['company_id'];
                $data['company_name'] = (isset($data['company_name']) && !empty($data['company_name']) && ($data['company_name'] != $existing_contact['company'][0]['company_name'])) ? $data['company_name'] : $existing_contact['company'][0]['company_name'];
                $data['company_department'] = (isset($data['company_department']) && !empty($data['company_department']) && ($data['company_department'] != $existing_contact['company'][0]['company_department'])) ? $data['company_department'] : $existing_contact['company'][0]['company_department'];
                $data['company_additional'] = (isset($data['company_additional']) && !empty($data['company_additional']) && ($data['company_additional'] != $existing_contact['company'][0]['company_additional'])) ? $data['company_additional'] : $existing_contact['company'][0]['company_additional'];
                $data['company_additional_2'] = (isset($data['company_additional_2']) && !empty($data['company_additional_2']) && ($data['company_additional_2'] != $existing_contact['company'][0]['company_additional_2'])) ? $data['company_additional_2'] : $existing_contact['company'][0]['company_additional_2'];
                $data['company_additional_3'] = (isset($data['company_additional_3']) && !empty($data['company_additional_3']) && ($data['company_additional_3'] != $existing_contact['company'][0]['company_additional_3'])) ? $data['company_additional_3'] : $existing_contact['company'][0]['company_additional_3'];
            }

            if (isset($existing_contact['communication']) && is_array($existing_contact['communication'])) {
                foreach ($existing_contact['communication'] as $communication) {
                    $name_id = null;
                    $name_value = null;
                    switch ($communication['communication_type']) {
                        case 'EMAIL':
                            if ($communication['communication_usage'] == 'PRIMARY') {
                                $name_id = 'communication_email_id';
                                $name_value = 'communicaction_email';
                            }
                            else {
                                $name_id = 'communication_email_secondary_id';
                                $name_value = 'communication_email_secondary';
                            }
                            break;
                        case 'PHONE':
                            if ($communication['communication_usage'] == 'PRIMARY') {
                                $name_id = 'communication_phone_id';
                                $name_value = 'communication_phone';
                            }
                            else {
                                $name_id = 'communication_phone_secondary_id';
                                $name_value = 'communication_phone_secondary';
                            }
                            break;
                        case 'CELL':
                            if ($communication['communication_usage'] == 'PRIMARY') {
                                $name_id = 'communication_cell_id';
                                $name_value = 'communication_cell';
                            }
                            else {
                                $name_id = 'communication_cell_secondary_id';
                                $name_value = 'communication_cell_secondary';
                            }
                            break;
                        case 'FAX':
                            if ($communication['communication_usage'] == 'PRIMARY') {
                                $name_id = 'communication_fax_id';
                                $name_value = 'communication_fax';
                            }
                            else {
                                $name_id = 'communication_fax_secondary_id';
                                $name_value = 'communication_fax_secondary';
                            }
                            break;
                        case 'URL':
                            if ($communication['communication_usage'] == 'PRIMARY') {
                                $name_id = 'communication_url_id';
                                $name_value = 'communication_url';
                            }
                            else {
                                $name_id = 'communication_url_secondary_id';
                                $name_value = 'communication_url_secondary';
                            }
                            break;
                    }
                    if (!is_null($name_id)) {
                        $data[$name_id] = $communication['communication_id'];
                        $data[$name_value] = (isset($data[$name_value]) && !empty($data[$name_value]) && ($data[$name_value] != $communication['communication_value'])) ? $data[$name_value] : $communication['communication_value'];
                    }
                }
            }

            $data['note_id'] = isset($existing_contact['note'][0]['note_id']) ? $existing_contact['note'][0]['note_id'] : -1;
            if ($data['note_id'] > 0) {
                $data['note'] = (isset($data['note']) && !empty($data['note']) && ($data['note'] != $existing_contact['note'][0]['note_content'])) ? $data['note'] : $existing_contact['note'][0]['note_content'];
                $data['note_date'] = $existing_contact['note'][0]['note_date'];
            }

            // check category
            if (isset($existing_contact['category'][0]) && is_array($existing_contact['category'][0])) {
                if (isset($data['category_type_id']) && ($data['category_type_id'] > 0)) {
                    // form has maybe submitted another category
                    $data['category_id'] = (isset($data['category_id']) && ($data['category_id'] > 0) && ($data['category_id'] != $existing_contact['category'][0]['category_id'])) ? $data['category_id'] : $existing_contact['category'][0]['category_id'];
                    $data['category_type_id'] = ($data['category_type_id'] != $existing_contact['category'][0]['category_type_id']) ? $data['category_type_id'] : $existing_contact['category'][0]['category_type_id'];
                }
                else {
                    // use the category information from the existing contact record
                    $data['category_id'] = $existing_contact['category'][0]['category_id'];
                    $data['category_type_id'] = $existing_contact['category'][0]['category_type_id'];
                }
            }

            if (isset($existing_contact['extra_fields']) && is_array($existing_contact['extra_fields'])) {
                foreach ($existing_contact['extra_fields'] as $extra_field) {
                    $name = 'extra_'.strtolower($extra_field['extra_type_name']);
                    $id = 'extra_'.strtolower($extra_field['extra_type_name']).'_id';
                    if ($extra_field['extra_type_type'] == 'DATE') {
                        if (isset($data[$name]) && !empty($data[$name]) && ($data[$name] != '0000-00-00')) {
                            $dt = Carbon::createFromFormat($this->app['translator']->trans('DATE_FORMAT'), $data[$name]);
                            $date = $dt->toDateTimeString();
                        }
                        else {
                            $date = $existing_contact['person'][0]['person_birthday'];
                        }
                        $data[$name] = (($date != '0000-00-00') && ($date != $extra_field['extra_value'])) ? $date : $extra_field['extra_value'];
                    }
                    else {
                        $data[$name] = (isset($data[$name]) && !empty($data[$name]) && ($data[$name] != $extra_field['extra_value'])) ? $data[$name] : $extra_field['extra_value'];
                    }
                    $data[$id] = $extra_field['extra_id'];
                }
            }


            // important: on update grant that existing tags will be not removed in case $field['tags'] is used !!!
            if (isset($data['tags']) && is_array($data['tags']) &&
                isset($existing_contact['tag']) && is_array($existing_contact['tag']) &&
                isset($field['tags']) && is_array($field['tags']) && !empty($field['tags'])) {
                foreach ($existing_contact['tag'] as $tag_field) {
                    if (!in_array($tag_field['tag_name'], $field['tags']) &&
                        !in_array($tag_field['tag_name'], $data['tags'])) {
                            $data['tags'][] = $tag_field['tag_name'];
                        }
                }
            }

        }

        /**
         Create the contact record for INSERT or UPDATE
         */

        if (!isset($contact_since) && isset($data['contact_since']) && !empty($data['contact_since']) && ($data['contact_since'] != '0000-00-00')) {
            $dt = Carbon::createFromFormat($this->app['translator']->trans('DATE_FORMAT'), $data['contact_since']);
            $contact_since = $dt->toDateTimeString();
        }
        elseif ($data['contact_id'] < 1) {
            // for new contacts set the current date/time
            $contact_since = date('Y-m-d H:i:s');
        }
        else {
            $contact_since = '0000-00-00';
        }

        $contact = array(
            'contact' => array(
                'contact_id' => $data['contact_id'],
                'contact_type' => $data['contact_type'],
                'contact_status' => isset($data['contact_status']) ? $data['contact_status'] : 'ACTIVE',
                'contact_login' => isset($data['contact_login']) ? $data['contact_login'] : $data['communication_email'],
                'contact_since' => $contact_since,
                'contact_name' => isset($data['contact_name']) ? $data['contact_name'] : $data['communication_email']
            )
        );

        // the minimum information for an address is the city, so we check for the existance of this field
        $address_type_check = array('address_', 'address_secondary_', 'address_billing_', 'address_billing_secondary_', 'address_delivery_', 'address_delivery_secondary_');
        foreach ($address_type_check as $prefix) {
            if (isset($data[$prefix.'city'])) {
                switch ($prefix) {
                    case 'address_secondary_':
                        $type = 'SECONDARY'; break;
                    case 'address_billing_':
                        $type = 'BILLING'; break;
                    case 'address_billing_secondary_':
                        $type = 'BILLING_SECONDARY'; break;
                    case 'address_delivery_':
                        $type = 'DELIVERY'; break;
                    case 'address_delivery_secondary_':
                        $type = 'DELIVERY_SECONDARY'; break;
                    case 'address_':
                        $type = 'PRIMARY'; break;
                    default:
                        throw new \Exception("Unknown address prefix: $prefix!");
                }

                $country_code = isset($data[$prefix.'country_code']) ? $data[$prefix.'country_code'] : '';
                $check_zip = isset($data[$prefix.'zip']) ? $data[$prefix.'zip'] : '';
                if (!empty($check_zip) && !empty($country_code) && (false === ($zip = $this->app['contact']->parseZIP($check_zip, $country_code)))) {
                    $validation_errors[] = $prefix.'zip';
                    $zip = $check_zip;
                }
                else {
                    $zip = $check_zip;
                }
                $contact['address'][] = array(
                    'address_id' => isset($data[$prefix.'id']) ? $data[$prefix.'id'] : -1,
                    'contact_id' => $data['contact_id'],
                    'address_type' => $type,
                    'address_street' => isset($data[$prefix.'street']) ? $data[$prefix.'street'] : '',
                    'address_zip' => $zip,
                    'address_city' => isset($data[$prefix.'city']) ? $data[$prefix.'city'] : '',
                    'address_area' => isset($data[$prefix.'area']) ? $data[$prefix.'area'] : '',
                    'address_state' => isset($data[$prefix.'state']) ? $data[$prefix.'state'] : '',
                    'address_country_code' => $country_code,
                    'address_identifier' => isset($data[$prefix.'identifier']) ? $data[$prefix.'identifier'] : '',
                    'address_appendix_1' => isset($data[$prefix.'appendix_1']) ? $data[$prefix.'appendix_1'] : '',
                    'address_appendix_2' => isset($data[$prefix.'appendix_2']) ? $data[$prefix.'appendix_2'] : '',
                    'address_description' => isset($data[$prefix.'description']) ? $data[$prefix.'description'] : ''
                );
            }
        }

        if (!isset($birthday)) {
            if (isset($data['person_birthday']) && !empty($data['person_birthday']) && ($data['person_birthday'] != '0000-00-00')) {
                $dt = Carbon::createFromFormat($this->app['translator']->trans('DATE_FORMAT'), $data['person_birthday']);
                $birthday = $dt->toDateTimeString();
            }
            else {
                $birthday = '0000-00-00';
            }
        }
        if ($data['contact_type'] == 'PERSON') {
            $contact['person'] = array(
                array(
                    'person_id' => $data['person_id'],
                    'contact_id' => $data['contact_id'],
                    'person_gender' => isset($data['person_gender']) ? $data['person_gender'] : 'MALE',
                    'person_title' => isset($data['person_title']) ? $data['person_title'] : 'NO_TITLE',
                    'person_first_name' => isset($data['person_first_name']) ? $data['person_first_name'] : '',
                    'person_last_name' => isset($data['person_last_name']) ? $data['person_last_name'] : '',
                    'person_nick_name' => isset($data['person_nick_name']) ? $data['person_nick_name'] : '',
                    'person_birthday' => $birthday
                )
            );
        }
        else {
            $contact['company'] = array(
                array(
                    'company_id' => $data['company_id'],
                    'contact_id' => $data['contact_id'],
                    'company_name' => isset($data['company_name']) ? $data['company_name'] : '',
                    'company_department' => isset($data['company_department']) ? $data['company_department'] : '',
                    'company_additional' => isset($data['company_additional']) ? $data['company_additional'] : '',
                    'company_additional_2' => isset($data['company_additional_2']) ? $data['company_additional_2'] : '',
                    'company_additional_3' => isset($data['company_additional_3']) ? $data['company_additional_3'] : ''
                )
            );
        }

        if (isset($data['communication_email']) && !empty($data['communication_email'])) {
            if (false === ($email = $this->app['contact']->parseEMail($data['communication_email']))) {
                $validation_errors[] = 'communication_email';
                $email = $data['communication_email'];
            }
            $contact['communication'][] = array(
                'communication_id' => $data['communication_email_id'],
                'contact_id' => $data['contact_id'],
                'communication_type' => 'EMAIL',
                'communication_usage' => 'PRIMARY',
                'communication_value' => $email
            );
        }

        if (isset($data['communication_email_secondary']) && !empty($data['communication_email_secondary'])) {
            if (false === ($email = $this->app['contact']->parseEMail($data['communication_email_secondary']))) {
                $validation_errors[] = 'communication_email_secondary';
                $email = $data['communication_email_secondary'];
            }
            $contact['communication'][] = array(
                'communication_id' => $data['communication_email_secondary_id'],
                'contact_id' => $data['contact_id'],
                'communication_type' => 'EMAIL',
                'communication_usage' => 'SECONDARY',
                'communication_value' => $email
            );
        }

        $country_code = (isset($data['address_country_code']) && !empty($data['address_country_code'])) ? $data['address_country_code'] : null;

        if (isset($data['communication_phone']) && !empty($data['communication_phone'])) {
            if (false === ($number = $this->app['contact']->parsePhoneNumber($data['communication_phone'], $country_code))) {
                $validation_errors[] = 'communication_phone';
                $number = $data['communication_phone'];
            }
            $contact['communication'][] = array(
                'communication_id' => $data['communication_phone_id'],
                'contact_id' => $data['contact_id'],
                'communication_type' => 'PHONE',
                'communication_usage' => 'PRIMARY',
                'communication_value' => $number
            );
        }
        if (isset($data['communication_phone_secondary']) && !empty($data['communication_phone_secondary'])) {
            if (false === ($number = $this->app['contact']->parsePhoneNumber($data['communication_phone_secondary'], $country_code))) {
                $validation_errors[] = 'communication_phone_secondary';
                $number = $data['communication_phone_secondary'];
            }
            $contact['communication'][] = array(
                'communication_id' => $data['communication_phone_secondary_id'],
                'contact_id' => $data['contact_id'],
                'communication_type' => 'PHONE',
                'communication_usage' => 'SECONDARY',
                'communication_value' => $number
            );
        }

        if (isset($data['communication_cell']) && !empty($data['communication_cell'])) {
            if (false === ($number = $this->app['contact']->parsePhoneNumber($data['communication_cell'], $country_code))) {
                $validation_errors[] = 'communication_cell';
                $number = $data['communication_cell'];
            }
            $contact['communication'][] = array(
                'communication_id' => $data['communication_cell_id'],
                'contact_id' => $data['contact_id'],
                'communication_type' => 'CELL',
                'communication_usage' => 'PRIMARY',
                'communication_value' => $number
            );
        }
        if (isset($data['communication_cell_secondary']) && !empty($data['communication_cell_secondary'])) {
            if (false === ($number = $this->app['contact']->parsePhoneNumber($data['communication_cell_secondary'], $country_code))) {
                $validation_errors[] = 'communication_cell_secondary';
                $number = $data['communication_cell_secondary'];
            }
            $contact['communication'][] = array(
                'communication_id' => $data['communication_cell_secondary_id'],
                'contact_id' => $data['contact_id'],
                'communication_type' => 'CELL',
                'communication_usage' => 'SECONDARY',
                'communication_value' => $number
            );
        }

        if (isset($data['communication_fax']) && !empty($data['communication_fax'])) {
            if (false === ($number = $this->app['contact']->parsePhoneNumber($data['communication_fax'], $country_code))) {
                $validation_errors[] = 'communication_fax';
                $number = $data['communication_fax'];
            }
            $contact['communication'][] = array(
                'communication_id' => $data['communication_fax_id'],
                'contact_id' => $data['contact_id'],
                'communication_type' => 'FAX',
                'communication_usage' => 'PRIMARY',
                'communication_value' => $number
            );
        }
        if (isset($data['communication_fax_secondary']) && !empty($data['communication_fax_secondary'])) {
            if (false === ($number = $this->app['contact']->parsePhoneNumber($data['communication_fax_secondary'], $country_code))) {
                $validation_errors[] = 'communication_fax_secondary';
                $number = $data['communication_fax_secondary'];
            }
            $contact['communication'][] = array(
                'communication_id' => $data['communication_fax_secondary_id'],
                'contact_id' => $data['contact_id'],
                'communication_type' => 'FAX',
                'communication_usage' => 'SECONDARY',
                'communication_value' => $number
            );
        }

        if (isset($data['communication_url']) && !empty($data['communication_url'])) {
            if (false === ($url = $this->app['contact']->parseURL($data['communication_url']))) {
                $validation_errors[] = 'communication_url';
                $url = $data['communication_url'];
            }
            $contact['communication'][] = array(
                'communication_id' => $data['communication_url_id'],
                'contact_id' => $data['contact_id'],
                'communication_type' => 'URL',
                'communication_usage' => 'PRIMARY',
                'communication_value' => $url
            );
        }

        if (isset($data['communication_url_secondary']) && !empty($data['communication_url_secondary'])) {
            if (false === ($url = $this->app['contact']->parseURL($data['communication_url_secondary']))) {
                $validation_errors[] = 'communication_url_secondary';
                $url = $data['communication_url_secondary'];
            }
            $contact['communication'][] = array(
                'communication_id' => $data['communication_url_secondary_id'],
                'contact_id' => $data['contact_id'],
                'communication_type' => 'URL',
                'communication_usage' => 'SECONDARY',
                'communication_value' => $url
            );
        }

        $contact['note'] = array(
            array(
                'note_id' => isset($data['note_id']) ? $data['note_id'] : -1,
                'contact_id' => $data['contact_id'],
                'note_title' => 'Remarks',
                'note_type' => 'TEXT',
                'note_content' => isset($data['note']) ? $data['note'] : '',
                'note_date' => isset($data['note_date']) ? $data['note_date'] : date('Y-m-d H:i:s')
            )
        );

        $category_type = $CategoryType->select($data['category_type_id']);

        $contact['category'] = array(
            array(
                'category_id' => $data['category_id'],
                'contact_id' => $data['contact_id'],
                'category_type_id' => $data['category_type_id'],
                'category_type_name' => $category_type['category_type_name']
            )
        );

        // EXTRA FIELDS
        $ExtraCategory = new ExtraCategory($this->app);
        $type_ids = $ExtraCategory->selectTypeIDByCategoryTypeID($data['category_type_id']);
        $ExtraType = new ExtraType($this->app);

        $contact['extra_fields'] = array();
        foreach ($type_ids as $type_id) {
            // get the extra field specification
            if (false !== ($extra = $ExtraType->select($type_id))) {
                $name = 'extra_'.strtolower($extra['extra_type_name']);
                $id = 'extra_'.strtolower($extra['extra_type_name']).'_id';
                if (isset($data[$name])) {
                    $contact['extra_fields'][] = array(
                        'extra_id' => isset($data[$id]) ? $data[$id] : -1,
                        'extra_type_id' => $extra['extra_type_id'],
                        'extra_type_name' => $extra['extra_type_name'],
                        'category_id' => $data['category_id'],
                        'category_type_name' => $category_type['category_type_name'],
                        'contact_id' => $data['contact_id'],
                        'extra_type_type' => $extra['extra_type_type'],
                        'extra_value' => !is_null($data[$name]) ? $data[$name] : ''
                    );
                }
            }
        }

        if (isset($data['tags']) && is_array($data['tags'])) {
            foreach ($data['tags'] as $tag) {
                $contact['tag'][] = array(
                    'tag_name' => $tag,
                    'contact_id' => $data['contact_id']
                );
            }
        }

        return $contact;
    }

    /**
     * Get a Form to select the contact type
     *
     * @return FormFactory
     */
    public function getFormContactType($hidden_fields=null)
    {
        $form = $this->app['form.factory']->createBuilder('form')
            ->add('contact_type', 'choice', array(
                'choices' => array('PERSON' => 'Person', 'COMPANY' => 'Organization'),
                'empty_value' => false,
                'expanded' => true,
                'data' => 'PERSON'
            ));

        if (is_array($hidden_fields)) {
            foreach ($hidden_fields as $key => $value) {
                $form->add($key, 'hidden', array(
                    'data' => $value
                ));
            }
        }

        return $form->getForm();
    }

    /**
     * Return a form to select the contact category
     *
     * @return FormFactory
     */
    public function getFormContactCategory($categories=null, $hidden_fields=null)
    {
        if (is_array($categories) && !empty($categories)) {
            reset($categories);
        }
        else {
            $categories = $this->app['contact']->getCategoryArrayForTwig();
            reset($categories);
        }

        $form = $this->app['form.factory']->createBuilder('form')
            ->add('category_type_id', 'choice', array(
                'choices' => $categories,
                'empty_value' => false,
                'multiple' => false,
                'expanded' => true,
                // set the first entry as default value
                'data' => key($categories)
            ));

        if (is_array($hidden_fields)) {
            foreach ($hidden_fields as $key => $value) {
                $form->add($key, 'hidden', array(
                    'data' => $value
                ));
            }
        }

        return $form->getForm();
    }

    /**
     * Get the Contact form, depending on the settings in the field array.
     *
     * @see /Contact/config.contact.json - [pattern][form][contact][field]
     * @param array $data - prepared contact record
     * @param array $field
     * @return mixed <boolean|FormFactory>
     */
    public function getFormContact($data=array(), $field=array())
    {
        if (!is_array($field) || empty($field)) {
            // use the default configuration from config.contact.json
            $field = self::$config['pattern']['form']['contact']['field'];
        }

        if (!isset($field['predefined']) || !isset($field['visible']) || !isset($field['hidden']) ||
            !isset($field['required']) || !isset($field['readonly'])) {
            $this->setAlert('Missing one or more keys in the field definition array! At least are needed: predefined, visible, hidden, required, readonly',
                array(), self::ALERT_TYPE_DANGER);
            return false;
        }

        $data['contact_id'] = (isset($data['contact_id'])) ? intval($data['contact_id']) : -1;
        $data['contact_type'] = (isset($data['contact_type'])) ? $data['contact_type'] : 'PERSON';

        // create the form
        $csrf_protection = (isset($field['csrf_protection'])) ? $field['csrf_protection'] : true;
        $form = $this->app['form.factory']->createBuilder('form', null, array('csrf_protection' => $csrf_protection));

        // loop through the hidden fields
        if (isset($field['hidden']) && is_array($field['hidden'])) {
            foreach ($field['hidden'] as $hidden) {
                $default_value = null;
                if (in_array($hidden, array('contact_id','category_id','company_id','person_id','address_id'))) {
                    $default_value = -1;
                }
                $form->add($hidden, 'hidden', array(
                    'data' => isset($data[$hidden]) ? $data[$hidden] : $default_value
                ));
            }
        }

        $TagType = new TagType($this->app);
        $ExtraType = new ExtraType($this->app);
        $ExtraCategory = new ExtraCategory($this->app);
        $CategoryType = new CategoryType($this->app);

        reset($field);

        // loop through the visible fields
        foreach ($field['visible'] as $visible) {
            switch ($visible) {
                case 'contact_id':
                case 'contact_type':
                    $form->add($visible, 'text', array(
                        'read_only' => true,
                        'required' => false,
                        'data' => $this->app['translator']->trans($this->app['utils']->humanize($data[$visible]))
                    ));
                    break;
                case 'contact_since':
                case 'contact_timestamp':
                    $form->add($visible, 'text', array(
                        'read_only' => true,
                        'required' => false,
                        'data' => isset($data[$visible]) ? date($this->app['translator']->trans('DATETIME_FORMAT'), strtotime($data[$visible])) : null
                    ));
                    break;
                case 'contact_status':
                    $form->add($visible, 'choice', array(
                        'choices' => array(
                            'ACTIVE' => $this->app['translator']->trans('Active'),
                            'LOCKED' => $this->app['translator']->trans('Locked'),
                            'PENDING' => $this->app['translator']->trans('Pending'),
                            'DELETED' => $this->app['translator']->trans('Deleted')),
                        'empty_value' => false,
                        'required' => in_array($visible, $field['required']),
                        'read_only' => in_array($visible, $field['readonly']),
                        'data' => isset($data[$visible]) ? $data[$visible] : 'ACTIVE'
                    ));
                    break;
                case 'category_name':
                    if (isset($field['categories']) && is_array($field['categories']) && !empty($field['categories'])) {
                        $categories = array();
                        foreach ($field['categories'] as $category) {
                            if ((false !== ($id = filter_var($category, FILTER_VALIDATE_INT))) &&
                                (false !== ($type = $CategoryType->select($id))) &&
                                !array_key_exists($category['category_type_name'], $categories)) {
                                $categories[$type['category_type_name']] = $this->app['utils']->humanize($type['category_type_name']);
                            }
                            elseif ((false !== ($type = $CategoryType->selectByName(trim($category)))) &&
                                    !array_key_exists($type['category_type_name'], $categories)) {
                                $categories[$type['category_type_name']] = $this->app['utils']->humanize($type['category_type_name']);
                            }
                        }
                    }
                    else {
                        $categories = $this->app['contact']->getCategoryArrayForTwig();
                    }
                    $attr = array();
                    if (isset($field['route']['category'])) {
                        $attr = array(
                            'route' => FRAMEWORK_URL.$field['route']['category'].self::$usage_param
                        );
                    }
                    $form->add($visible, 'choice', array(
                        'choices' => $categories,
                        'empty_value' => '- please select -',
                        'required' => in_array($visible, $field['required']),
                        'read_only' => in_array($visible, $field['readonly']),
                        'data' => isset($data[$visible]) ? $data[$visible] : null,
                        'attr' => $attr
                    ));
                    break;
                case 'category_access':
                    $access_type = $this->app['contact']->getAccessType($data['contact_id']);
                    $access_type = $this->app['utils']->humanize($access_type);
                    $access_type = $this->app['translator']->trans($access_type);
                    $form->add($visible, 'text', array(
                        'data' => $access_type,
                        'required' => false,
                        'read_only' => true
                    ));
                    break;
                case 'tags':
                    // contact tags
                    if (isset($field['tags']) && is_array($field['tags']) && !empty($field['tags'])) {
                        $tags = array();
                        foreach ($field['tags'] as $tag) {
                            if ((false !== ($id = filter_var($tag, FILTER_VALIDATE_INT))) &&
                                (false !== ($type = $TagType->select($id))) &&
                                !array_key_exists($type['tag_type_id'], $tags)) {
                                $tags[$type['tag_name']] = $this->app['utils']->humanize($type['tag_name']);
                            }
                            elseif ((false !== ($type = $TagType->selectByName(trim($tag)))) &&
                                    !array_key_exists($type['tag_type_id'], $tags)) {
                                $tags[$type['tag_name']] = $this->app['utils']->humanize($type['tag_name']);
                            }
                        }
                    }
                    else {
                        $tags = $this->app['contact']->getTagArrayForTwig();
                    }
                    $attr = array();
                    if (isset($field['route']['tag'])) {
                        $attr = array(
                            'route' => FRAMEWORK_URL.$field['route']['tag'].self::$usage_param
                        );
                    }
                    $form->add($visible, 'choice', array(
                        'choices' => $tags,
                        'multiple' => true,
                        'expanded' => true,
                        'read_only' => in_array($visible, $field['readonly']),
                        'required' => in_array($visible, $field['required']),
                        'data' => (isset($data[$visible]) && is_array($data[$visible])) ? $data[$visible] : null,
                        'attr' => $attr
                    ));
                    break;
                case 'person_gender':
                    if ($data['contact_type'] == 'COMPANY') {
                        break;
                    }
                    $form->add($visible, 'choice', array(
                        'required' => in_array($visible, $field['required']),
                        'choices' => array('MALE' => 'Male', 'FEMALE' => 'Female'),
                        'expanded' => true,
                        'read_only' => in_array($visible, $field['readonly']),
                        'required' => in_array($visible, $field['required']),
                        'data' => isset($data[$visible]) ? $data[$visible] : 'MALE'
                    ));
                    break;
                case 'person_title':
                    if ($data['contact_type'] == 'COMPANY') {
                        break;
                    }
                    $attr = array();
                    if (isset($field['route']['title'])) {
                        $attr = array(
                            'route' => FRAMEWORK_URL.$field['route']['title'].self::$usage_param
                        );
                    }
                    $form->add($visible, 'choice', array(
                        'choices' => $this->app['contact']->getTitleArrayForTwig(),
                        'empty_value' => '- please select -',
                        'required' => in_array($visible, $field['required']),
                        'read_only' => in_array($visible, $field['readonly']),
                        'data' => isset($data[$visible]) ? $data[$visible] : null,
                        'attr' => $attr
                    ));
                    break;
                case 'person_birthday':
                    if ($data['contact_type'] == 'COMPANY') {
                        break;
                    }
                    $birthday = (isset($data[$visible]) && ($data[$visible] != '0000-00-00')) ? date($this->app['translator']->trans('DATE_FORMAT'), strtotime($data[$visible])) : '';
                    $form->add($visible, 'text', array(
                        'required' => in_array($visible, $field['required']),
                        'read_only' => in_array($visible, $field['readonly']),
                        'data' => $birthday,
                        'attr' => array('class' => 'datepicker')
                    ));
                    break;
                case 'communication_email':
                case 'communication_email_secondary':
                case 'communication_phone':
                case 'communication_phone_secondary':
                case 'communication_cell':
                case 'communication_cell_secondary':
                case 'communication_fax':
                case 'communication_fax_secondary':
                case 'communication_url':
                case 'communication_url_secondary':
                    $form->add($visible.'_id', 'hidden', array(
                        'data' => isset($data[$visible.'_id']) ? $data[$visible.'_id'] : -1
                    ));
                    $form->add($visible, 'text', array(
                        'required' => (in_array($visible, $field['required']) || ($visible === 'communication_email')),
                        'read_only' => in_array($visible, $field['readonly']),
                        'data' => isset($data[$visible]) ? $data[$visible] : ''
                    ));
                    break;
                case 'address_country_code':
                case 'address_secondary_country_code':
                case 'address_delivery_country_code':
                case 'address_delivery_secondary_country_code':
                case 'address_billing_country_code':
                case 'address_billing_secondary_country_code':
                    $form->add($visible, 'choice', array(
                        'required' => in_array($visible, $field['required']),
                        'choices' => $this->app['contact']->getCountryArrayForTwig(),
                        'empty_value' => '- please select -',
                        'read_only' => in_array($visible, $field['readonly']),
                        'data' => isset($data[$visible]) ? $data[$visible] : '',
                        'preferred_choices' => self::$config['countries']['preferred']
                    ));
                    break;
                case 'address_description':
                case 'note':
                    $form->add('note_id', 'hidden', array(
                        'data' => isset($data['note_id']) ? $data['note_id'] : -1
                    ));
                    $form->add($visible, 'textarea', array(
                        'required' => in_array($visible, $field['required']),
                        'read_only' => in_array($visible, $field['readonly']),
                        'data' => isset($data[$visible]) ? $data[$visible] : ''
                    ));
                    break;
                case 'extra_fields':
                    // 'extra_fields' mean to show all 'extra_xxx' fields !!!
                    $type_ids = $ExtraCategory->selectTypeIDByCategoryTypeID(
                        isset($data['category_type_id']) ? $data['category_type_id'] : -1);
                    foreach ($type_ids as $type_id) {
                        // get the extra field specification
                        if (false !== ($extra = $ExtraType->select($type_id))) {
                            $name = 'extra_'.strtolower($extra['extra_type_name']);
                            $extra_type = null;
                            $value = isset($data[$name]) ? $data[$name] : null;
                            switch ($extra['extra_type_type']) {
                                // determine the form type
                                case 'TEXT':
                                    $form_type = 'textarea';
                                    $class = $name;
                                    break;
                                case 'HTML':
                                    $form_type = 'textarea';
                                    $extra_type = 'html';
                                    $class = $name;
                                    break;
                                case 'DATE':
                                    $form_type = 'text';
                                    $class = "datepicker $name";
                                    $value = (isset($data[$name]) && ($data[$name] != '0000-00-00')) ? date($this->app['translator']->trans('DATE_FORMAT'), strtotime($data[$name])) : '';
                                    break;
                                default:
                                    $form_type = 'text';
                                    $class = $name;
                                    break;
                            }
                            $form->add($name, $form_type, array(
                                'attr' => array('class' => $class, 'type' => $extra_type),
                                'required' => in_array($name, $field['required']),
                                'read_only' => in_array($name, $field['readonly']),
                                'label' => $this->app['utils']->humanize($extra['extra_type_name']),
                                'data' => $value
                            ));
                        }
                    }
                    break;
                case 'contact_name':
                case 'contact_login':
                case 'person_first_name':
                case 'person_last_name':
                case 'person_nick_name':
                case 'company_name':
                case 'company_department':
                case 'address_street':
                case 'address_zip':
                case 'address_city':
                case 'address_area':
                case 'address_state':
                case 'address_identifier':
                case 'address_appendix_1':
                case 'address_appendix_2':
                case 'address_secondary_street':
                case 'address_secondary_zip':
                case 'address_secondary_city':
                case 'address_secondary_area':
                case 'address_secondary_state':
                case 'address_secondary_identifier':
                case 'address_secondary_appendix_1':
                case 'address_secondary_appendix_2':
                case 'address_billing_street':
                case 'address_billing_zip':
                case 'address_billing_city':
                case 'address_billing_area':
                case 'address_billing_state':
                case 'address_billing_identifier':
                case 'address_billing_appendix_1':
                case 'address_billing_appendix_2':
                case 'address_billing_secondary_street':
                case 'address_billing_secondary_zip':
                case 'address_billing_secondary_city':
                case 'address_billing_secondary_area':
                case 'address_billing_secondary_state':
                case 'address_billing_secondary_identifier':
                case 'address_billing_secondary_appendix_1':
                case 'address_billing_secondary_appendix_2':
                case 'address_delivery_street':
                case 'address_delivery_zip':
                case 'address_delivery_city':
                case 'address_delivery_area':
                case 'address_delivery_state':
                case 'address_delivery_identifier':
                case 'address_delivery_appendix_1':
                case 'address_delivery_appendix_2':
                case 'address_delivery_secondary_street':
                case 'address_delivery_secondary_zip':
                case 'address_delivery_secondary_city':
                case 'address_delivery_secondary_area':
                case 'address_delivery_secondary_state':
                case 'address_delivery_secondary_identifier':
                case 'address_delivery_secondary_appendix_1':
                case 'address_delivery_secondary_appendix_2':
                    // default text fields
                    if (($data['contact_type'] == 'COMPANY' && in_array($visible, self::$person_fields)) ||
                        ($data['contact_type'] == 'PERSON') && in_array($visible, self::$company_fields)) {
                        break;
                    }
                    $form->add($visible, 'text', array(
                        'required' => in_array($visible, $field['required']),
                        'read_only' => in_array($visible, $field['readonly']),
                        'data' => isset($data[$visible]) ? $data[$visible] : ''
                    ));
                    break;
                case "special_fields":
                    if (isset($field['special']) && is_array($field['special'])) {
                        foreach ($field['special'] as $special) {
                            if (isset($special['enabled']) && $special['enabled']) {
                                switch ($special['type']) {
                                    case 'hidden':
                                        $form->add($special['name'], $special['type'], array(
                                            'data' => isset($special['data']) ? $special['data'] : null
                                        ));
                                        break;
                                    case 'choice':
                                        // set the default properties
                                        $label = $this->app['utils']->humanize($special['name']);
                                        $property = array(
                                            'choices' => array(),
                                            'empty_value' => '- please select -',
                                            'expanded' => false,
                                            'multiple' => false,
                                            'preferred_choices' => array(),
                                            'label' => $this->app['translator']->trans($label),
                                            'required' => true,
                                            'read_only' => false,
                                            'attr' => array()
                                        );
                                        foreach ($special as $key => $value) {
                                            switch ($key) {
                                                case 'name':
                                                case 'type':
                                                case 'enabled':
                                                    // this is no property
                                                    break;
                                                default:
                                                    // add property
                                                    $property[$key] = $value;
                                                    break;
                                            }
                                        }
                                        $form->add($special['name'], $special['type'], $property);
                                        break;
                                    default:
                                        $property = array();
                                        foreach ($special as $key => $value) {
                                            switch ($key) {
                                                case 'name':
                                                case 'type':
                                                case 'enabled':
                                                    // this is no property
                                                    break;
                                                default:
                                                    // add property
                                                    $property[$key] = $value;
                                                    break;
                                            }
                                        }
                                        $form->add($special['name'], $special['type'], $property);
                                        break;
                                }
                            }
                        }
                    }
                    break;
                default:
                    if ((false !== ($pos = stripos($visible, 'extra_'))) && ($pos == 0)) {
                        // possibly an extra field!
                        $name = strtolower(substr($visible, strlen('extra_')));
                        if (false !== ($type = $ExtraCategory->selectTypebyNameAndCategory($name,
                            isset($data['category_type_id']) ? $data['category_type_id'] : -1))) {
                            $extra_type = null;
                            $value = isset($data[$name]) ? $data[$name] : null;
                            switch ($type['extra_type_type']) {
                                // determine the form type
                                case 'TEXT':
                                    $form_type = 'textarea';
                                    $class = $name;
                                    break;
                                case 'HTML':
                                    $form_type = 'textarea';
                                    $extra_type = 'html';
                                    $class = $name;
                                    break;
                                case 'DATE':
                                    $form_type = 'text';
                                    $class = "datepicker $name";
                                    $value = (isset($data[$name]) && ($data[$name] != '0000-00-00')) ? date($this->app['translator']->trans('DATE_FORMAT'), strtotime($data[$name])) : '';
                                    break;
                                default:
                                    $form_type = 'text';
                                    $class = $name;
                                    break;
                            }
                            $form->add($name, $form_type, array(
                                'attr' => array('class' => $class, 'type' => $extra_type),
                                'required' => in_array($name, $field['required']),
                                'read_only' => in_array($name, $field['readonly']),
                                'label' => $this->app['utils']->humanize($type['extra_type_name']),
                                'data' => $value
                            ));
                        }
                        else {
                            $this->setAlert('The field %field% is unknown, please check the configuration!',
                                array('%field%' => $visible), self::ALERT_TYPE_DANGER);
                            return false;
                        }
                    }
                    else {
                        // unknown field
                        $this->setAlert('The field %field% is unknown, please check the configuration!',
                            array('%field%' => $visible), self::ALERT_TYPE_DANGER);
                        return false;
                    }
                    break;
            }
        }

        // return the form
        return $form->getForm();
    }
}
