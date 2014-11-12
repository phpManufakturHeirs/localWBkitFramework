<?php

/**
 * Contact
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de/Contact
 * @copyright 2014 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\Contact\Control\Export;

use phpManufaktur\Basic\Control\Pattern\Alert;
use Silex\Application;
use phpManufaktur\Contact\Data\Contact\Contact as ContactData;
use phpManufaktur\Contact\Data\Contact\ExtraType;

require_once EXTENSION_PATH.'/phpexcel/1.8.0/Classes/PHPExcel/IOFactory.php';

class Excel extends Alert
{
    protected $ContactData = null;

    /**
     * (non-PHPdoc)
     * @see \phpManufaktur\Basic\Control\Pattern\Alert::initialize()
     */
    protected function initialize(Application $app)
    {
        parent::initialize($app);

        $this->ContactData = new ContactData($app);
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

            'address_secondary_street' => '',
            'address_secondary_city' => '',
            'address_secondary_zip' => '',
            'address_secondary_area' => '',
            'address_secondary_state' => '',
            'address_secondary_country_code' => '',

            'address_delivery_street' => '',
            'address_delivery_city' => '',
            'address_delivery_zip' => '',
            'address_delivery_area' => '',
            'address_delivery_state' => '',
            'address_delivery_country_code' => '',

            'address_delivery_secondary_street' => '',
            'address_delivery_secondary_city' => '',
            'address_delivery_secondary_zip' => '',
            'address_delivery_secondary_area' => '',
            'address_delivery_secondary_state' => '',
            'address_delivery_secondary_country_code' => '',

            'address_billing_street' => '',
            'address_billing_city' => '',
            'address_billing_zip' => '',
            'address_billing_area' => '',
            'address_billing_state' => '',
            'address_billing_country_code' => '',

            'address_billing_secondary_street' => '',
            'address_billing_secondary_city' => '',
            'address_billing_secondary_zip' => '',
            'address_billing_secondary_area' => '',
            'address_billing_secondary_state' => '',
            'address_billing_secondary_country_code' => '',

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

    /**
     * Select all available contact records and create an Excel table for export.
     * Save the table as .xlsx or as .csv
     *
     * @param string $save_as can be 'xlsx' or 'csv'
     * @return boolean
     */
    protected function export($save_as='xlsx')
    {
        $objPHPExcel = new \PHPExcel();

        $objPHPExcel->getProperties()->setCreator("kitFramework - Contact")
            ->setLastModifiedBy("kitFramework - Contact")
            ->setTitle("Contact record table")
            ->setSubject("Contact record table")
            ->setDescription("Export of all kitFramework Contact records");

        $objPHPExcel->setActiveSheetIndex(0);

        $contacts = $this->ContactData->selectAll();

        $default_record = $this->getDefaultRecord();
        $extra = $this->getExtraFields();
        $default_record = array_merge($default_record, $extra);

        if (is_array($contacts) && !empty($contacts)) {
            $row = 1;
            // create the column headers
            $columns = array_keys($default_record);
            $i = 0;
            foreach ($columns as $column) {
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($i, $row, $column);
                $i++;
            }
            $row++;
            foreach ($contacts as $item) {
                if (false !== ($record = $this->app['contact']->select($item['contact_id']))) {
                    $contact = $default_record;

                    foreach ($record['contact'] as $key => $value) {
                        if (array_key_exists($key, $contact)) {
                            $contact[$key] = $value;
                        }
                    }

                    if (isset($record['category'][0]['category_type_id'])) {
                        $contact['category_name'] = $record['category'][0]['category_type_name'];
                    }

                    if (isset($record['tag']) && is_array($record['tag'])) {
                        $tags = array();
                        foreach ($record['tag'] as $tag) {
                            $tags[] = $tag['tag_name'];
                        }
                        $contact['tags'] = implode(',', $tags);
                    }

                    if (isset($record['person'][0])) {
                        foreach ($record['person'][0] as $key => $value) {
                            if (array_key_exists($key, $contact)) {
                                if ($key === 'contact_id') continue;
                                if (($key === 'person_title') && ($value == 'NO_TITLE')) {
                                    $value = '';
                                }
                                $contact[$key] = $value;
                            }
                        }
                    }

                    if (isset($record['company'][0])) {
                        foreach ($record['company'][0] as $key => $value) {
                            if (array_key_exists($key, $contact)) {
                                if ($key === 'contact_id') continue;
                                $contact[$key] = $value;
                            }
                        }
                    }

                    if (isset($record['communication']) && is_array($record['communication'])) {
                        foreach ($record['communication'] as $communication) {
                            if (($communication['communication_status'] == 'ACTIVE') &&
                                ($communication['communication_id'] > 0)) {
                                switch ($communication['communication_type']) {
                                    case 'EMAIL':
                                        if ($communication['communication_usage'] == 'PRIMARY') {
                                            $contact['communication_email'] = $communication['communication_value'];
                                        }
                                        elseif (!isset($data['communication_email_secondary'])) {
                                            $contact['communication_email_secondary'] = $communication['communication_value'];
                                        }
                                        break;
                                    case 'PHONE':
                                        if ($communication['communication_usage'] == 'PRIMARY') {
                                            $contact['communication_phone'] = $this->app['contact']->parsePhoneNumber($communication['communication_value']);
                                        }
                                        elseif (!isset($data['communication_phone_secondary'])) {
                                            $contact['communication_phone_secondary'] = $this->app['contact']->parsePhoneNumber($communication['communication_value']);
                                        }
                                        break;
                                    case 'CELL':
                                        if ($communication['communication_usage'] == 'PRIMARY') {
                                            $contact['communication_cell'] = $this->app['contact']->parsePhoneNumber($communication['communication_value']);
                                        }
                                        elseif (!isset($data['communication_cell_secondary'])) {
                                            $contact['communication_cell_secondary'] = $this->app['contact']->parsePhoneNumber($communication['communication_value']);
                                        }
                                        break;
                                    case 'FAX':
                                        if ($communication['communication_usage'] == 'PRIMARY') {
                                            $contact['communication_fax'] = $this->app['contact']->parsePhoneNumber($communication['communication_value']);
                                        }
                                        elseif (!isset($data['communication_fax_secondary'])) {
                                            $contact['communication_fax_secondary'] = $this->app['contact']->parsePhoneNumber($communication['communication_value']);
                                        }
                                        break;
                                    case 'URL':
                                        if ($communication['communication_usage'] == 'PRIMARY') {
                                            $contact['communication_url'] = $this->app['contact']->parseURL($communication['communication_value']);
                                        }
                                        elseif (!isset($data['communication_url_secondary'])) {
                                            $contact['communication_url_secondary'] = $this->app['contact']->parseURL($communication['communication_value']);
                                        }
                                        break;
                                    default:
                                        // nothing to do here ...
                                        break;
                                }
                            }
                        }
                    }

                    if (isset($record['address']) && is_array($record['address'])) {
                        foreach ($record['address'] as $address) {
                            if (($address['address_status'] == 'ACTIVE') && ($address['address_id'] > 0)) {
                                switch ($address['address_type']) {
                                    case 'PRIVATE': // no longer in use!
                                    case 'BUSINESS': // no longer in use!
                                    case 'PRIMARY':
                                        if (!isset($contact['address_id'])) {
                                            $contact['address_street'] = $address['address_street'];
                                            $contact['address_zip'] = $address['address_zip'];
                                            $contact['address_city'] = $address['address_city'];
                                            $contact['address_area'] = $address['address_area'];
                                            $contact['address_state'] = $address['address_state'];
                                            $contact['address_country_code'] = $address['address_country_code'];
                                        }
                                        elseif (!isset($contact['address_secondary_id'])) {
                                            $contact['address_secondary_street'] = $address['address_street'];
                                            $contact['address_secondary_zip'] = $address['address_zip'];
                                            $contact['address_secondary_city'] = $address['address_city'];
                                            $contact['address_secondary_area'] = $address['address_area'];
                                            $contact['address_secondary_state'] = $address['address_state'];
                                            $contact['address_secondary_country_code'] = $address['address_country_code'];
                                        }
                                        break;
                                    case 'DELIVERY':
                                        if (!isset($contact['address_delivery_id'])) {
                                            $contact['address_delivery_street'] = $address['address_street'];
                                            $contact['address_delivery_zip'] = $address['address_zip'];
                                            $contact['address_delivery_city'] = $address['address_city'];
                                            $contact['address_delivery_area'] = $address['address_area'];
                                            $contact['address_delivery_state'] = $address['address_state'];
                                            $contact['address_delivery_country_code'] = $address['address_country_code'];
                                        }
                                        elseif (!isset($contact['address_delivery_secondary_id'])) {
                                            $contact['address_delivery_secondary_street'] = $address['address_street'];
                                            $contact['address_delivery_secondary_zip'] = $address['address_zip'];
                                            $contact['address_delivery_secondary_city'] = $address['address_city'];
                                            $contact['address_delivery_secondary_area'] = $address['address_area'];
                                            $contact['address_delivery_secondary_state'] = $address['address_state'];
                                            $contact['address_delivery_secondary_country_code'] = $address['address_country_code'];
                                        }
                                        break;
                                    case 'BILLING':
                                        if (!isset($contact['address_billing_id'])) {
                                            $contact['address_billing_street'] = $address['address_street'];
                                            $contact['address_billing_zip'] = $address['address_zip'];
                                            $contact['address_billing_city'] = $address['address_city'];
                                            $contact['address_billing_area'] = $address['address_area'];
                                            $contact['address_billing_state'] = $address['address_state'];
                                            $contact['address_billing_country_code'] = $address['address_country_code'];
                                        }
                                        elseif (!isset($contact['address_secondary_id'])) {
                                            $contact['address_billing_secondary_street'] = $address['address_street'];
                                            $contact['address_billing_secondary_zip'] = $address['address_zip'];
                                            $contact['address_billing_secondary_city'] = $address['address_city'];
                                            $contact['address_billing_secondary_area'] = $address['address_area'];
                                            $contact['address_billing_secondary_state'] = $address['address_state'];
                                            $contact['address_billing_secondary_country_code'] = $address['address_country_code'];
                                        }
                                        break;
                                    case 'OTHER':
                                        if (!isset($contact['address_other_id'])) {
                                            $contact['address_other_street'] = $address['address_street'];
                                            $contact['address_other_zip'] = $address['address_zip'];
                                            $contact['address_other_city'] = $address['address_city'];
                                            $contact['address_other_area'] = $address['address_area'];
                                            $contact['address_other_state'] = $address['address_state'];
                                            $contact['address_other_country_code'] = $address['address_country_code'];
                                        }
                                        elseif (!isset($contact['address_secondary_id'])) {
                                            $contact['address_other_secondary_street'] = $address['address_street'];
                                            $contact['address_other_secondary_zip'] = $address['address_zip'];
                                            $contact['address_other_secondary_city'] = $address['address_city'];
                                            $contact['address_other_secondary_area'] = $address['address_area'];
                                            $contact['address_other_secondary_state'] = $address['address_state'];
                                            $contact['address_other_secondary_country_code'] = $address['address_country_code'];
                                        }
                                        break;
                                }
                            }
                        }
                    }


                    if (isset($record['note'][0]) && is_array($record['note'][0])) {
                        $contact['note'] = $record['note'][0]['note_content'];
                    }

                    if (isset($record['extra_fields']) && is_array($record['extra_fields'])) {
                        foreach ($record['extra_fields'] as $extra) {
                            $contact['extra_'.strtolower($extra['extra_type_name'])] = $extra['extra_value'];
                            $contact['extra_'.strtolower($extra['extra_type_name']).'_type'] = $extra['extra_type_type'];
                        }
                    }


                    reset($contact);
                    $i=0;
                    foreach ($contact as $key => $value) {
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($i, $row, $value);
                        $i++;
                    }
                    $row++;
                }
            }
        }
        else {
            $this->setAlert('There a no contacts to export.', array(), self::ALERT_TYPE_INFO);
            return false;
        }

        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $objPHPExcel->setActiveSheetIndex(0);

        if ($save_as === 'xlsx') {
            $file_name = date('ymd-Hi').'-contact-export.xlsx';
            \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007')
                ->save(FRAMEWORK_MEDIA_PATH.'/'.$file_name);
            $this->setAlert('Contact records successfull exported as <a href="%url%">%file_name%</a>. Please <a href="%remove%">remove the file</a> after download.',
                array('%url%' => FRAMEWORK_MEDIA_URL.'/'.$file_name, '%file_name%' => $file_name,
                    '%remove%' => FRAMEWORK_URL.'/admin/contact/export/remove/'.base64_encode($file_name)),
                self::ALERT_TYPE_SUCCESS);
        }
        elseif ($save_as === 'xls') {
            $file_name = date('ymd-Hi').'-contact-export.xls';
            \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5')
            ->save(FRAMEWORK_MEDIA_PATH.'/'.$file_name);
            $this->setAlert('Contact records successfull exported as <a href="%url%">%file_name%</a>. Please <a href="%remove%">remove the file</a> after download.',
                array('%url%' => FRAMEWORK_MEDIA_URL.'/'.$file_name, '%file_name%' => $file_name,
                    '%remove%' => FRAMEWORK_URL.'/admin/contact/export/remove/'.base64_encode($file_name)),
                self::ALERT_TYPE_SUCCESS);
        }
        elseif ($save_as === 'csv') {
            $file_name = date('ymd-Hi').'-contact-export.csv';
            \PHPExcel_IOFactory::createWriter($objPHPExcel, 'CSV')
                ->setUseBOM(true)
                ->setDelimiter(',')
                ->setEnclosure('"')
                ->setLineEnding("\r\n")
                ->setSheetIndex(0)
                ->save(FRAMEWORK_MEDIA_PATH.'/'.date('ymd-Hi').'-contact-export.csv');
            $this->setAlert('Contact records successfull exported as <a href="%url%">%file_name%</a>. Please <a href="%remove%">remove the file</a> after download.',
                array('%url%' => FRAMEWORK_MEDIA_URL.'/'.$file_name, '%file_name%' => $file_name,
                    '%remove%' => FRAMEWORK_URL.'/admin/contact/export/remove/'.base64_encode($file_name)),
                self::ALERT_TYPE_SUCCESS);
        }
        else {
            $this->setAlert('Unknown file format <strong>%format%</strong> to save the contact records.',
                array('%format%' => $save_as), self::ALERT_TYPE_DANGER);
        }
        return true;
    }

    /**
     * Controller to execute the desired export
     *
     * @param Application $app
     */
    public function ControllerExportType(Application $app, $type)
    {
        $this->initialize($app);
        $this->export($type);
        return $this->promptAlertFramework();
    }

    /**
     * Enable to delete the exported .csv or .xlsx file
     *
     * @param Application $app
     * @param string $file the exported file to remove
     */
    public function ControllerRemoveFile(Application $app, $file)
    {
        $this->initialize($app);
        $file = base64_decode($file);
        if ($app['filesystem']->exists(FRAMEWORK_MEDIA_PATH.'/'.$file)) {
            $app['filesystem']->remove(FRAMEWORK_MEDIA_PATH.'/'.$file);
            $this->setAlert('File %file% successfull removed.',
                array('%file%' => $file), self::ALERT_TYPE_SUCCESS);
        }
        else {
            $this->setAlert('Nothing to do ...', array(), self::ALERT_TYPE_INFO);
        }
        return $this->promptAlertFramework();
    }
}

