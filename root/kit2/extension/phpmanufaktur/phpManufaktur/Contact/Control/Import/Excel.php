<?php

/**
 * Contact
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de/Contact
 * @copyright 2014 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\Contact\Control\Import;

use phpManufaktur\Basic\Control\Pattern\Alert;
use Silex\Application;
use phpManufaktur\Contact\Data\Contact\ExtraType;
use Carbon\Carbon;
use phpManufaktur\Contact\Data\Contact\CategoryType;
use phpManufaktur\Contact\Data\Contact\ExtraCategory;
use phpManufaktur\Contact\Data\Contact\Country;

require_once EXTENSION_PATH.'/phpexcel/1.8.0/Classes/PHPExcel/IOFactory.php';

class Excel extends Alert
{
    protected static $usage = null;
    protected static $import_type = null;
    protected static $import_file = null;
    protected static $counter_insert = null;
    protected static $counter_update = null;

    protected function initialize(Application $app)
    {
        parent::initialize($app);
        self::$usage = $app['request']->get('usage', 'framework');
    }

    /**
     * Get the default contact record for the export
     *
     * @return array
     */
    protected function getDefaultRecord()
    {
        return array(
            'contact_id' => -1,
            'contact_name' => '',
            'contact_login' => '',
            'contact_type' => 'PERSON',
            'contact_since' => '0000-00-00 00:00:00',
            'contact_status' => 'ACTIVE',
            'contact_timestamp' => '0000-00-00 00:00:00',

            'category_name' => '',

            'tags' => '',

            'note' => '',

            'person_gender' => 'MALE',
            'person_title' => '',
            'person_first_name' => '',
            'person_last_name' => '',
            'person_nick_name' => '',
            'person_birthday' => '0000-00-00',

            'company_name' => '',
            'company_department' => '',
            'company_additional' => '',
            'company_additional_2' => '',
            'company_additional_3' => '',

            'communication_cell' => '',
            'communication_cell_secondary' => '',
            'communication_email' => '',
            'communication_email_secondary' => '',
            'communication_fax' => '',
            'communication_fax_secondaray' => '',
            'communication_phone' => '',
            'communication_phone_secondary' => '',
            'communication_url' => '',
            'communication_url_secondary' => '',

            'address_street' => '',
            'address_city' => '',
            'address_zip' => '',
            'address_area' => '',
            'address_state' => '',
            'address_country_code' => '',
            'address_description' => '',
            'address_identifier' => '',
            'address_appendix' => '',
            'address_appendix_2' => '',
/*
            'address_secondary_street' => '',
            'address_secondary_city' => '',
            'address_secondary_zip' => '',
            'address_secondary_area' => '',
            'address_secondary_state' => '',
            'address_secondary_country_code' => '',
            'address_secondary_description' => '',
            'address_secondary_identifier' => '',
            'address_secondary_appendix' => '',
            'address_secondary_appendix_2' => '',

            'address_delivery_street' => '',
            'address_delivery_city' => '',
            'address_delivery_zip' => '',
            'address_delivery_area' => '',
            'address_delivery_state' => '',
            'address_delivery_country_code' => '',
            'address_delivery_description' => '',
            'address_delivery_identifier' => '',
            'address_delivery_appendix' => '',
            'address_delivery_appendix_2' => '',

            'address_delivery_secondary_street' => '',
            'address_delivery_secondary_city' => '',
            'address_delivery_secondary_zip' => '',
            'address_delivery_secondary_area' => '',
            'address_delivery_secondary_state' => '',
            'address_delivery_secondary_country_code' => '',
            'address_delivery_secondary_description' => '',
            'address_delivery_secondary_identifier' => '',
            'address_delivery_secondary_appendix' => '',
            'address_delivery_secondary_appendix_2' => '',

            'address_billing_street' => '',
            'address_billing_city' => '',
            'address_billing_zip' => '',
            'address_billing_area' => '',
            'address_billing_state' => '',
            'address_billing_country_code' => '',
            'address_billing_description' => '',
            'address_billing_identifier' => '',
            'address_billing_appendix' => '',
            'address_billing_appendix_2' => '',

            'address_billing_secondary_street' => '',
            'address_billing_secondary_city' => '',
            'address_billing_secondary_zip' => '',
            'address_billing_secondary_area' => '',
            'address_billing_secondary_state' => '',
            'address_billing_secondary_country_code' => '',
            'address_billing_secondary_description' => '',
            'address_billing_secondary_identifier' => '',
            'address_billing_secondary_appendix' => '',
            'address_billing_secondary_appendix_2' => '',
*/
        );
    }

    /**
     * Get the available extra fields to complete the contact record
     *
     * @return array
     */
    protected function getExtraFields()
    {
        $ExtraType = new ExtraType($this->app);
        $extra_types = $ExtraType->selectAll();
        $extras = array();
        foreach ($extra_types as $extra) {
            $extras['extra_'.strtolower($extra['extra_type_name'])] = '';
            $extras['extra_'.strtolower($extra['extra_type_name']).'_type'] = $extra['extra_type_type'];
        }
        return $extras;
    }

    protected function formSelectFile($type='csv')
    {
        $form = $this->app['form.factory']->createBuilder('form')
        ->add('type', 'hidden', array(
            'data' => $type
        ))
        ->add('usage', 'hidden', array(
            'data' => self::$usage
        ));
        if ($type === 'csv') {
            $form->add('encoding', 'choice', array(
                'choices' => array('CP1252' => 'CP1252 (Windows/Excel)','UTF-8' => 'UTF-8 (default)'),
                'empty_value' => false,
                'data' => 'UTF-8'
            ));
            $form->add('delimiter', 'choice', array(
                'choices' => array('comma' => 'separated by comma', 'semicolon' => 'separated by semicolon'),
                'empty_value' => false,
                'data' => 'comma'
            ));
            $form->add('enclosures', 'choice', array(
                'choices' => array('doublequotes' => 'double quotes (")', 'none' => 'none'),
                'empty_value' => false,
                'data' => 'doublequotes'
            ));
        }
        $form->add('import', 'file', array(
            'attr' => array('title' => $this->app['translator']->trans('Click to select the %type% file for import', array('%type%' => strtoupper($type))))
        ));

        return $form->getForm();
    }

    protected function formFileAssociation()
    {
        try {
            $objPHPExcel = \PHPExcel_IOFactory::load(self::$import_file);
            $objWorksheet = $objPHPExcel->getActiveSheet();
            // get the first row and extract the column headers
            $cells = array();
            foreach ($objWorksheet->getRowIterator() as $row) {
                $cellIterator = $row->getCellIterator();
                $cellIterator->setIterateOnlyExistingCells(false); // access all cells!
                $i=0;
                foreach ($cellIterator as $cell) {
                    $cells['cell_'.$i] = $cell->getValue();
                    $i++;
                }
                break;
            }

            $form = $this->app['form.factory']->createBuilder('form')
            ->add('import_type', 'hidden', array(
                'data' => self::$import_type
            ))
            ->add('usage', 'hidden', array(
                'data' => self::$usage
            ))
            ->add('import_file', 'hidden', array(
                'data' => self::$import_file
            ))
            ->add('count_cells', 'hidden', array(
                'data' => count($cells)
            ));

            $default_record = $this->getDefaultRecord();
            $extra_fields = $this->getExtraFields();
            $default_record = array_merge($default_record, $extra_fields);
            $field_keys = array_keys($default_record);
            sort($field_keys);
            $field_types = array_combine($field_keys, $field_keys);

            foreach ($cells as $name => $value) {
                $form->add($name, 'choice', array(
                    'label' => $value,
                    'label_attr' => array('translate' => false),
                    'choices' => $field_types,
                    'empty_value' => '- not assigned -',
                    'required' => false,
                    'attr' => array('translate' => false, 'import_field' => true)
                ));
            }

            $form->add('contact_type', 'choice', array(
                'choices' => array('PERSON' => 'Person', 'COMPANY' => 'Company'),
                'empty_value' => false,
                'data' => 'PERSON'
            ));
            $form->add('category_name', 'choice', array(
                'choices' => $this->app['contact']->getCategoryArrayForTwig(),
                'empty_value' => '- select category (optional) -',
                'required' => false
            ));
            $form->add('tags', 'choice', array(
                'choices' => $this->app['contact']->getTagArrayForTwig(),
                'multiple' => true,
                'expanded' => true,
                'required' => false
            ));
            $form->add('person_gender', 'choice', array(
                'choices' => array('MALE' => 'Male', 'FEMALE' => 'Female'),
                'empty_value' => false,
                'data' => 'MALE'
            ));
            $form->add('address_country_code', 'choice', array(
                'choices' => $this->app['contact']->getCountryArrayForTwig(),
                'empty_value' => '- select country (optional) -',
                'required' => false,
                'data' => 'DE'
            ));

            return $form->getForm();
        } catch (\PHPExcel_Reader_Exception $e) {
            $this->setAlert('[%file%:%line%] Excel Error: %error%',
                array('%error%' => $e->getMessage(),
                    '%file%' => basename($e->getFile()), '%line%' => $e->getLine()), self::ALERT_TYPE_DANGER);
            return false;
        }
    }

    /**
     * Controller select the correct file type form and show it
     *
     * @param Application $app
     * @param string $type
     */
    public function ControllerType(Application $app, $type)
    {
        $this->initialize($app);
        $form = $this->formSelectFile($type);

        return $this->app['twig']->render($this->app['utils']->getTemplateFile(
            '@phpManufaktur/Contact/Template', 'admin/import/file.import.twig'),
            array(
                'type' => $type,
                'alert' => $this->getAlert(),
                'usage' => self::$usage,
                'form' => $form->createView()
            ));
    }

    public function ControllerFile(Application $app, $type)
    {
        $this->initialize($app);
        $form = $this->formSelectFile($type);

        $form->bind($this->app['request']);
        if ($form->isValid()) {
            $data = $form->getData();

            // get the file extension
            $extension = $form['import']->getData()->getClientOriginalExtension();

            self::$import_type = $type;
            self::$import_file = FRAMEWORK_TEMP_PATH.'/'.$this->app['utils']->createGUID().'.'.$extension;

            $form['import']->getData()->move(FRAMEWORK_TEMP_PATH, basename(self::$import_file));
            if (false !== ($form = $this->formFileAssociation())) {
                return $this->app['twig']->render($this->app['utils']->getTemplateFile(
                    '@phpManufaktur/Contact/Template', 'admin/import/assign.fields.twig'),
                    array(
                        'alert' => $this->getAlert(),
                        'usage' => self::$usage,
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
        return $this->promptAlertFramework();
    }

    /**
     * Proceed the import
     *
     * @param array $data
     * @param array $defaults
     * @return boolean
     */
    protected function importContact($data, $defaults)
    {
        if (!isset($data['contact_login']) && !isset($data['communication_email'])) {
            // invalid contact
            return false;
        }
        $login = isset($data['contact_login']) ? $data['contact_login'] : $data['communication_email'];
        if (empty($login)) {
            return false;
        }
        if (false === ($contact_id = $this->app['contact']->existsLogin($login))) {
            $contact_id = -1;
        }

        // set defaults
        $data['contact_login'] = $login;
        $data['contact_type'] = isset($data['contact_type']) ? $data['contact_type'] : $defaults['contact_type'];
        $data['person_id'] = -1;
        $data['company_id'] = -1;
        $data['address_id'] = -1;
        $data['category_id'] = -1;
        $data['category_type_id'] = -1;

        if (isset($data['address_country_code']) && !empty($data['address_country_code'])) {
            $Country = new Country($this->app);
            $check = $data['address_country_code'];
            if ($Country->existsCountryCode($check)) {
                $data['address_country_code'] = strtoupper($check);
            }
            elseif (false === ($data['address_country_code'] = $Country->selectCountryCode($check))) {
                $data['address_country_code'] = $defaults['address_country_code'];
            }
        }
        else {
            $data['address_country_code'] = $defaults['address_country_code'];
        }

        if (isset($data['tags'])) {
            $items = strpos($data['tags'], ',') ? explode(',', $data['tags']) : array($data['tags']);
            $tags = array();
            foreach ($items as $item) {
                $tag = strtoupper(trim($item));
                if ($this->app['contact']->existsTagName($tag)) {
                    $tags[] = $tag;
                }
            }
            $data['tags'] = $tags;
        }
        else {
            $data['tags'] = $defaults['tags'];
        }

        $CategoryType = new CategoryType($this->app);
        if (isset($data['category_name']) && !empty($data['category_name'])) {
            // set the category_type_id
            $category = $CategoryType->selectByName($data['category_name']);
            if (isset($category['category_type_id'])) {
                $data['category_type_id'] = $category['category_type_id'];
            }
        }
        elseif (isset($defaults['category_name']) && !empty($defaults['category_name'])) {
            $data['category_name'] = $defaults['category_name'];
            $category = $CategoryType->selectByName($data['category_name']);
            if (isset($category['category_type_id'])) {
                $data['category_type_id'] = $category['category_type_id'];
            }
        }

        if ($contact_id > 0) {
            // select the existing record
            $existing_contact = $this->app['contact']->select($contact_id);

            if ($existing_contact['contact']['contact_id'] != $contact_id) {
                // the query return an empty contact record - this mean that the record is deleted or locked for any reason
                $this->setAlert('Skipped contact record %login% because it ist deleted or locked for any reason!',
                    array('%login%' => $login), self::ALERT_TYPE_DANGER);
                return false;
            }

            if ($existing_contact['contact']['contact_type'] !== $data['contact_type']) {
                // problem: the contact type differ!
                $this->setAlert('There exists already a contact record for %login%, but this record is assigned to a <strong>%type%</strong> and can not be changed.',
                    array('%login%' => $login, '%type%' => $existing_contact['contact']['contact_type']),
                    self::ALERT_TYPE_WARNING);
                return false;
            }

            // compare the existing data with the submitted data
            $data['contact_id'] = $existing_contact['contact']['contact_id'];
            $data['contact_status'] = $existing_contact['contact']['contact_status'];
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
            $data['address_id'] = $existing_contact['address'][0]['address_id'];
            $data['address_street'] = (isset($data['address_street']) && !empty($data['address_street']) && ($data['address_street'] != $existing_contact['address'][0]['address_street'])) ? $data['address_street'] : $existing_contact['address'][0]['address_street'];
            $data['address_city'] = (isset($data['address_city']) && !empty($data['address_city']) && ($data['address_city'] != $existing_contact['address'][0]['address_city'])) ? $data['address_city'] : $existing_contact['address'][0]['address_city'];
            $data['address_zip'] = (isset($data['address_zip']) && !empty($data['address_zip']) && ($data['address_zip'] != $existing_contact['address'][0]['address_zip'])) ? $data['address_zip'] : $existing_contact['address'][0]['address_zip'];
            $data['address_area'] = (isset($data['address_area']) && !empty($data['address_area']) && ($data['address_area'] != $existing_contact['address'][0]['address_area'])) ? $data['address_area'] : $existing_contact['address'][0]['address_area'];
            $data['address_state'] = (isset($data['address_state']) && !empty($data['address_state']) && ($data['address_state'] != $existing_contact['address'][0]['address_state'])) ? $data['address_state'] : $existing_contact['address'][0]['address_state'];
            $data['address_country_code'] = (isset($data['address_country_code']) && !empty($data['address_country_code']) && ($data['address_country_code'] != $existing_contact['address'][0]['address_country_code'])) ? $data['address_country_code'] : $existing_contact['address'][0]['address_country_code'];

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
            }

            if (isset($existing_contact['communication']) && is_array($existing_contact['communication'])) {
                foreach ($existing_contact['communication'] as $communication) {
                    switch ($communication['communication_type']) {
                        case 'EMAIL':
                            if ($communication['communication_usage'] == 'PRIMARY') {
                                $data['communication_email_id'] = $communication['communication_id'];
                                $data['communication_email'] = (isset($data['communication_email']) && !empty($data['communication_email']) && ($data['communication_email'] != $communication['communication_value'])) ? $data['communication_email'] : $communication['communication_value'];
                            }
                            elseif (!isset($data['communication_email_secondary'])) {
                                $data['communication_email_secondary_id'] = $communication['communication_id'];
                                $data['communication_email_secondary'] = (isset($data['communication_email_secondary']) && !empty($data['communication_email_secondary']) && ($data['communication_email_secondary'] != $communication['communication_value'])) ? $data['communication_email_secondary'] : $communication['communication_value'];
                            }
                            break;
                        case 'PHONE':
                            if ($communication['communication_usage'] == 'PRIMARY') {
                                $data['communication_phone_id'] = $communication['communication_id'];
                                $data['communication_phone'] = (isset($data['communication_phone']) && !empty($data['communication_phone']) && ($data['communication_phone'] != $communication['communication_value'])) ? $data['communication_phone'] : $communication['communication_value'];
                            }
                            elseif (!isset($data['communication_phone_secondary'])) {
                                $data['communication_phone_secondary_id'] = $communication['communication_id'];
                                $data['communication_phone_secondary'] = (isset($data['communication_phone_secondary']) && !empty($data['communication_phone_secondary']) && ($data['communication_phone_secondary'] != $communication['communication_value'])) ? $data['communication_phone_secondary'] : $communication['communication_value'];
                            }
                            break;
                        case 'CELL':
                            if ($communication['communication_usage'] == 'PRIMARY') {
                                $data['communication_cell_id'] = $communication['communication_id'];
                                $data['communication_cell'] = (isset($data['communication_cell']) && !empty($data['communication_cell']) && ($data['communication_cell'] != $communication['communication_value'])) ? $data['communication_cell'] : $communication['communication_value'];
                            }
                            elseif (!isset($data['communication_cell_secondary'])) {
                                $data['communication_cell_secondary_id'] = $communication['communication_id'];
                                $data['communication_cell_secondary'] = (isset($data['communication_cell_secondary']) && !empty($data['communication_cell_secondary']) && ($data['communication_cell_secondary'] != $communication['communication_value'])) ? $data['communication_cell_secondary'] : $communication['communication_value'];
                            }
                            break;
                        case 'FAX':
                            if ($communication['communication_usage'] == 'PRIMARY') {
                                $data['communication_fax_id'] = $communication['communication_id'];
                                $data['communication_fax'] = (isset($data['communication_fax']) && !empty($data['communication_fax']) && ($data['communication_fax'] != $communication['communication_value'])) ? $data['communication_fax'] : $communication['communication_value'];
                            }
                            elseif (!isset($data['communication_fax_secondary'])) {
                                $data['communication_fax_secondary_id'] = $communication['communication_id'];
                                $data['communication_fax_secondary'] = (isset($data['communication_fax_secondary']) && !empty($data['communication_fax_secondary']) && ($data['communication_fax_secondary'] != $communication['communication_value'])) ? $data['communication_fax_secondary'] : $communication['communication_value'];
                            }
                            break;
                        case 'URL':
                            if ($communication['communication_usage'] == 'PRIMARY') {
                                $data['communication_url_id'] = $communication['communication_id'];
                                $data['communication_url'] = (isset($data['communication_url']) && !empty($data['communication_url']) && ($data['communication_url'] != $communication['communication_value'])) ? $data['communication_url'] : $communication['communication_value'];
                            }
                            elseif (!isset($data['communication_url_secondary'])) {
                                $data['communication_url_secondary_id'] = $communication['communication_id'];
                                $data['communication_url_secondary'] = (isset($data['communication_url_secondary']) && !empty($data['communication_url_secondary']) && ($data['communication_url_secondary'] != $communication['communication_value'])) ? $data['communication_url_secondary'] : $communication['communication_value'];
                            }
                            break;
                        default:
                            // nothing to do here ...
                            break;
                    }
                }
            }

            $data['note_id'] = isset($existing_contact['note'][0]['note_id']) ? $existing_contact['note'][0]['note_id'] : -1;
            if ($data['note_id'] > 0) {
                $data['note'] = (isset($data['note']) && !empty($data['note']) && ($data['note'] != $existing_contact['note'][0]['note_content'])) ? $data['note'] : $existing_contact['note'][0]['note_content'];
            }

            if (isset($existing_contact['category'][0]) && is_array($existing_contact['category'][0]) && !empty($existing_contact['category'][0])) {
                $data['category_id'] = (isset($data['category_id']) && ($data['category_id'] > 0) && ($data['category_id'] != $existing_contact['category'][0]['category_id'])) ? $data['category_id'] : $existing_contact['category'][0]['category_id'];
                $data['category_type_id'] = (isset($data['category_type_id']) && ($data['category_type_id'] > 0) && ($data['category_type_id'] != $existing_contact['category'][0]['category_type_id'])) ? $data['category_type_id'] : $existing_contact['category'][0]['category_type_id'];

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
            }


            // important: on update grant that existing tags will be not removed in case $field['tags'] is used !!!
            if (isset($data['tags']) && is_array($data['tags']) &&
                isset($existing_contact['tag']) && is_array($existing_contact['tag']) &&
                isset($defaults['tags']) && is_array($defaults['tags']) && !empty($defaults['tags'])) {

                foreach ($existing_contact['tag'] as $tag_field) {
                    if (!in_array($tag_field['tag_name'], $defaults['tags']) &&
                        !in_array($tag_field['tag_name'], $data['tags'])) {
                            $data['tags'][] = $tag_field['tag_name'];
                    }
                }
            }

        }

        /**
         Create the contact record for INSERT or UPDATE
         */

        if (!isset($data['contact_id'])) {
            $data['contact_id'] = $contact_id;
        }

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
            ),
            'address' => array(
                array(
                    'address_id' => isset($data['address_id']) ? $data['address_id'] : -1,
                    'contact_id' => $data['contact_id'],
                    'address_type' => 'PRIMARY',
                    'address_street' => isset($data['address_street']) ? $data['address_street'] : '',
                    'address_zip' => isset($data['address_zip']) ? $data['address_zip'] : '',
                    'address_city' => isset($data['address_city']) ? $data['address_city'] : '',
                    'address_area' => isset($data['address_area']) ? $data['address_area'] : '',
                    'address_state' => isset($data['address_state']) ? $data['address_state'] : '',
                    'address_country_code' => isset($data['address_country_code']) ? $data['address_country_code'] : '',
                    'address_description' => isset($data['address_description']) ? $data['address_description'] : '',
                    'address_identifier' => isset($data['address_identifier']) ? $data['address_identifier'] : '',
                    'address_appendix_1' => isset($data['address_appendix']) ? $data['address_appendix'] : '',
                    'address_appendix_2' => isset($data['address_appendix_2']) ? $data['address_appendix_2'] : ''
                )
            )
        );

        // @todo: support all other address types!

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
                    'company_id' => isset($data['company_id']) ? $data['company_id'] : -1,
                    'contact_id' => $data['contact_id'],
                    'company_name' => isset($data['company_name']) ? $data['company_name'] : '',
                    'company_department' => isset($data['company_department']) ? $data['company_department'] : ''
                )
            );
        }

        if (isset($data['communication_email']) && !empty($data['communication_email'])) {
            if (false === ($email = $this->app['contact']->parseEMail($data['communication_email']))) {
                $email = $data['communication_email'];
                if ($login == $email) {
                    // fatal - can not use invalid email address for login!
                    $this->setAlert('Fatal: Can not import contact record because the email address %email% is invalid.',
                        array('%email%' => $data['communication_email']), self::ALERT_TYPE_DANGER);
                    return false;
                }
            }
            $contact['communication'][] = array(
                'communication_id' => isset($data['communication_email_id']) ? $data['communication_email_id'] : -1,
                'contact_id' => $data['contact_id'],
                'communication_type' => 'EMAIL',
                'communication_usage' => 'PRIMARY',
                'communication_value' => $email
            );
        }

        if (isset($data['communication_email_secondary']) && !empty($data['communication_email_secondary'])) {
            if (false === ($email = $this->app['contact']->parseEMail($data['communication_email_secondary']))) {
                $email = $data['communication_email_secondary'];
            }
            $contact['communication'][] = array(
                'communication_id' => isset($data['communication_email_secondary_id']) ? $data['communication_email_secondary_id'] : -1,
                'contact_id' => $data['contact_id'],
                'communication_type' => 'EMAIL',
                'communication_usage' => 'SECONDARY',
                'communication_value' => $email
            );
        }

        $country_code = (isset($data['address_country_code']) && !empty($data['address_country_code'])) ? $data['address_country_code'] : null;

        if (isset($data['communication_phone']) && !empty($data['communication_phone'])) {
            if (false === ($number = $this->app['contact']->parsePhoneNumber($data['communication_phone'], $country_code))) {
                $number = $data['communication_phone'];
            }
            $contact['communication'][] = array(
                'communication_id' => isset($data['communication_phone_id']) ? $data['communication_phone_id'] : -1,
                'contact_id' => $data['contact_id'],
                'communication_type' => 'PHONE',
                'communication_usage' => 'PRIMARY',
                'communication_value' => $number
            );
        }
        if (isset($data['communication_phone_secondary']) && !empty($data['communication_phone_secondary'])) {
            if (false === ($number = $this->app['contact']->parsePhoneNumber($data['communication_phone_secondary'], $country_code))) {
                $number = $data['communication_phone_secondary'];
            }
            $contact['communication'][] = array(
                'communication_id' => isset($data['communication_phone_secondary_id']) ? $data['communication_phone_secondary_id'] : -1,
                'contact_id' => $data['contact_id'],
                'communication_type' => 'PHONE',
                'communication_usage' => 'SECONDARY',
                'communication_value' => $number
            );
        }

        if (isset($data['communication_cell']) && !empty($data['communication_cell'])) {
            if (false === ($number = $this->app['contact']->parsePhoneNumber($data['communication_cell'], $country_code))) {
                $number = $data['communication_cell'];
            }
            $contact['communication'][] = array(
                'communication_id' => isset($data['communication_cell_id']) ? $data['communication_cell_id'] : -1,
                'contact_id' => $data['contact_id'],
                'communication_type' => 'CELL',
                'communication_usage' => 'PRIMARY',
                'communication_value' => $number
            );
        }
        if (isset($data['communication_cell_secondary']) && !empty($data['communication_cell_secondary'])) {
            if (false === ($number = $this->app['contact']->parsePhoneNumber($data['communication_cell_secondary'], $country_code))) {
                $number = $data['communication_cell_secondary'];
            }
            $contact['communication'][] = array(
                'communication_id' => isset($data['communication_cell_secondary_id']) ? $data['communication_cell_secondary_id'] : -1,
                'contact_id' => $data['contact_id'],
                'communication_type' => 'CELL',
                'communication_usage' => 'SECONDARY',
                'communication_value' => $number
            );
        }

        if (isset($data['communication_fax']) && !empty($data['communication_fax'])) {
            if (false === ($number = $this->app['contact']->parsePhoneNumber($data['communication_fax'], $country_code))) {
                $number = $data['communication_fax'];
            }
            $contact['communication'][] = array(
                'communication_id' => isset($data['communication_fax_id']) ? $data['communication_fax_id'] : -1,
                'contact_id' => $data['contact_id'],
                'communication_type' => 'FAX',
                'communication_usage' => 'PRIMARY',
                'communication_value' => $number
            );
        }
        if (isset($data['communication_fax_secondary']) && !empty($data['communication_fax_secondary'])) {
            if (false === ($number = $this->app['contact']->parsePhoneNumber($data['communication_fax_secondary'], $country_code))) {
                $number = $data['communication_fax_secondary'];
            }
            $contact['communication'][] = array(
                'communication_id' => isset($data['communication_fax_secondary_id']) ? $data['communication_fax_secondary_id'] : -1,
                'contact_id' => $data['contact_id'],
                'communication_type' => 'FAX',
                'communication_usage' => 'SECONDARY',
                'communication_value' => $number
            );
        }

        if (isset($data['communication_url']) && !empty($data['communication_url'])) {
            if (false === ($url = $this->app['contact']->parseURL($data['communication_url']))) {
                $url = $data['communication_url'];
            }
            $contact['communication'][] = array(
                'communication_id' => isset($data['communication_url_id']) ? $data['communication_url_id'] : -1,
                'contact_id' => $data['contact_id'],
                'communication_type' => 'URL',
                'communication_usage' => 'PRIMARY',
                'communication_value' => $url
            );
        }
        if (isset($data['communication_url_secondary']) && !empty($data['communication_url_secondary'])) {
            if (false === ($url = $this->app['contact']->parseURL($data['communication_url_secondary']))) {
                $url = $data['communication_url_secondary'];
            }
            $contact['communication'][] = array(
                'communication_id' => isset($data['communication_url_secondary_id']) ? $data['communication_url_secondary_id'] : -1,
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
                'note_title' => 'Remark',
                'note_type' => 'TEXT',
                'note_content' => isset($data['note']) ? $data['note'] : ''
            )
        );

        if ($data['category_type_id'] > 0) {
            // a valid category type ID isset

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
        }

        if (isset($data['tags']) && is_array($data['tags'])) {
            foreach ($data['tags'] as $tag) {
                $contact['tag'][] = array(
                    'tag_name' => $tag,
                    'contact_id' => $data['contact_id']
                );
            }
        }

        if ($data['contact_id'] > 0) {
            if ($this->app['contact']->update($contact, $data['contact_id'])) {
                self::$counter_update++;
            }
        }
        elseif ($this->app['contact']->insert($contact)) {
            self::$counter_insert++;
        }
    }

    public function ControllerExecute(Application $app)
    {
        $this->initialize($app);

        $request = $this->app['request']->get('form');
        if (!isset($request['import_type']) || !isset($request['import_file'])) {
            $this->setAlert('The form seems to be manipulated, abort action!', array(), self::ALERT_TYPE_DANGER);
            return $this->promptAlertFramework();
        }
        self::$import_type = $request['import_type'];
        self::$import_file = $request['import_file'];

        if (false === ($form = $this->formFileAssociation())) {
            $this->setAlert('The form seems to be manipulated, abort action!', array(), self::ALERT_TYPE_DANGER);
            return $this->promptAlertFramework();
        }

        $form->bind($this->app['request']);
        if ($form->isValid()) {
            $data = $form->getData();

            $cell_type = array();
            $check = true;

            for ($i=0; $i < $data['count_cells']; $i++) {
                if (!isset($data['cell_'.$i]) || empty($data['cell_'.$i])) {
                    $cell_type[$i] = null;
                    continue;
                }
                if (false !== ($key = array_search($data['cell_'.$i], $cell_type))) {
                    // field is assigned twice!
                    $this->setAlert('You have assigned the field %field% twice! Please check the assignment!',
                        array('%field%' => $data['cell_'.$i]), self::ALERT_TYPE_WARNING);
                    $check = false;
                    break;
                }
                $cell_type[$i] = $data['cell_'.$i];
            }

            // check the minimum assignment: email or contact_login
            if ($check && (!array_search('contact_login', $cell_type) && !array_search('communication_email', $cell_type))) {
                // missing unique identifier
                $this->setAlert('Contact need a unique identifier for each record. By default this is the email address but it can also the contact login. For this reason you must assign the field communication_email or contact_login.',
                    array(), self::ALERT_TYPE_WARNING);
                $check = false;
            }

            if ($check) {
                // execute the import
                try {
                    $defaults = array(
                        'contact_type' => $data['contact_type'],
                        'category_name' => isset($data['category_name']) ? $data['category_name'] : null,
                        'tags' => isset($data['tags']) ? $data['tags'] : array(),
                        'person_gender' => $data['person_gender'],
                        'address_country_code' => isset($data['address_country_code']) ? $data['address_country_code'] : 'DE'
                    );

                    $objPHPExcel = \PHPExcel_IOFactory::load(self::$import_file);

                    $objWorksheet = $objPHPExcel->getActiveSheet();
                    $start = true;
                    self::$counter_insert = 0;
                    self::$counter_update = 0;

                    foreach ($objWorksheet->getRowIterator() as $row) {
                        if ($start) {
                            // don't read the first line == column header!
                            $start = false;
                            continue;
                        }
                        $cellIterator = $row->getCellIterator();
                        $cellIterator->setIterateOnlyExistingCells(false); // access all cells!
                        $contact = array();
                        $i=-1;
                        foreach ($cellIterator as $cell) {
                            // loop through the cells
                            $i++;
                            if (is_null($cell_type[$i])) {
                                continue;
                            }
                            $contact[$cell_type[$i]] = $cell->getValue();
                        }
                        try {
                            $this->importContact($contact, $defaults);
                        } catch (\Exception $e) {
                            $this->setAlert($e->getMessage());
                        }
                    }

                } catch (\PHPExcel_Reader_Exception $e) {
                    $this->setAlert('[%file%:%line%] Excel Error: %error%',
                        array('%error%' => $e->getMessage(),
                            '%file%' => basename($e->getFile()), '%line%' => $e->getLine()), self::ALERT_TYPE_DANGER);
                }
            }
            if (self::$counter_insert > 0) {
                $this->setAlert('Totally inserted %count% contact records',
                    array('%count%' => self::$counter_insert), self::ALERT_TYPE_SUCCESS);
            }
            if (self::$counter_update > 0) {
                $this->setAlert('Totally updated %count% contact records',
                    array('%count%' => self::$counter_update), self::ALERT_TYPE_SUCCESS);
            }
            if ((self::$counter_insert == 0) && (self::$counter_update == 0)) {
                $this->setAlert('There where no contact records inserted or updated.',
                    array(), self::ALERT_TYPE_INFO);
            }
        }
        else {
            // general error (timeout, CSFR ...)
            $this->setAlert('The form is not valid, please check your input and try again!', array(),
                self::ALERT_TYPE_DANGER, true, array('form_errors' => $form->getErrorsAsString(),
                    'method' => __METHOD__, 'line' => __LINE__));
        }

        return $this->app['twig']->render($this->app['utils']->getTemplateFile(
            '@phpManufaktur/Contact/Template', 'admin/import/assign.fields.twig'),
            array(
                'alert' => $this->getAlert(),
                'usage' => self::$usage,
                'form' => $form->createView()
            ));
    }
}
