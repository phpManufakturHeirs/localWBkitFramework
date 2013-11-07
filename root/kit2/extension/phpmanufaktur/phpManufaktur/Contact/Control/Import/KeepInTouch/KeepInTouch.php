<?php

/**
 * Contact
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de/contact
 * @copyright 2013 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\Contact\Control\Import\KeepInTouch;

use Silex\Application;
use phpManufaktur\Contact\Data\Import\KeepInTouch\KeepInTouch as KeepInTouchData;
use phpManufaktur\Contact\Control\Contact;
use phpManufaktur\Contact\Control\Import\Dialog;

class KeepInTouch extends Dialog {

    protected static $kit_release = null;
    protected static $import_is_possible = false;
    protected $KeepInTouch = null;
    protected $Contact = null;
    protected static $script_start = null;
    protected static $max_execution_time = 60; // 60 seconds

    /**
     * Initialize the class
     *
     * @see \phpManufaktur\Contact\Control\Import\Dialog::initialize()
     */
    protected function initialize(Application $app)
    {
        self::$script_start = microtime(true);
        parent::initialize($app);
        $this->KeepInTouch = new KeepInTouchData($app);
        if ($this->KeepInTouch->existsKIT()) {
            // KIT exists, check the version
            self::$kit_release = $this->KeepInTouch->getKITrelease();
            if (!is_null(self::$kit_release)) {
                self::$import_is_possible = version_compare(self::$kit_release, '0.72', '>=');
            }
        }
        $this->Contact = new Contact($app);
        // increase the execution time to 60 seconds
        ini_set('max_execution_time', self::$max_execution_time);
    }

    /**
     * First step to import data from a KIT1 installation
     *
     * @param Application $app
     * @return string rendered dialog
     */
    public function start(Application $app)
    {
        // initialize the class
        $this->initialize($app);

        $records = 0;
        if (self::$import_is_possible) {
            $records = $this->KeepInTouch->countKITrecords();
            $this->setMessage('Detected a KeepInTouch installation (Release: %release%) with %count% active or locked contacts.',
                array('%release%' => self::$kit_release, '%count%' => $records));
        }
        else {
            $this->setMessage('There exists no KeepInTouch installation at the parent CMS!');
        }

        $this->app['session']->set('KIT_IMPORT_CONTACTS_DETECTED', $records);
        $this->app['session']->set('KIT_IMPORT_CONTACTS_IMPORTED', 0);

        return $this->app['twig']->render($this->app['utils']->getTemplateFile('@phpManufaktur/Contact/Template', 'backend/import/start.keepintouch.twig'),
            array(
                'message' => $this->getMessage(),
                'records' => $records,
                'import_is_possible' => self::$import_is_possible,
                'kit_release' => self::$kit_release
            ));
    }

    protected function addContact($kit, $contact_type='PERSON', &$contact_id)
    {
        $tags = array();
        if (isset($kit['tags'])) {
            foreach ($kit['tags'] as $tag) {
                $tags[] = array(
                    'contact_id' => -1,
                    'tag_name' => $tag
                );
            }
        }
        $communication = array();
        if (isset($kit['communication'])) {
            foreach ($kit['communication'] as $comm) {
                $communication[] = array(
                    'communication_id' => -1,
                    'contact_id' => -1,
                    'communication_type' => $comm['type'],
                    'communication_usage' => $comm['usage'],
                    'communication_value' => $comm['address']
                );
            }
        }

        $addresses = array();
        if (isset($kit['addresses'])) {
            foreach ($kit['addresses'] as $address) {
                $addresses[] = array(
                    'address_id' => -1,
                    'contact_id' => -1,
                    'address_type' => ($contact_type == 'PERSON') ? 'PRIVATE' : 'BUSINESS',
                    'address_street' => $address['address_street'],
                    'address_zip' => $address['address_zip'],
                    'address_city' => $address['address_city'],
                    'address_country_code' => ($address['address_country'] != '-1') ? strtoupper($address['address_country']) : '',
                    'address_appendix_1' => $address['address_extra'],
                    'address_state' => $address['address_region']
                );
            }
        }

        $data = array(
            'contact' => array(
                'contact_id' => -1,
                'contact_type' => $contact_type,
                'contact_status' => ($kit['origin']['contact_status'] == 'statusActive') ? 'ACTIVE' : 'LOCKED',
                'contact_name' => $kit['origin']['contact_identifier'],
                'contact_login' => $kit['login'],
                'contact_since' => $kit['origin']['contact_since']
                ),
            'category' => array(
                array(
                    'contact_id' => -1,
                    'category_type_name' => (isset($kit['categories'][0])) ? $kit['categories'][0] : ''
                    )
                ),
            'tag' => $tags,
            'communication' => $communication,
            'address' => $addresses
        );

        if ($contact_type == 'PERSON') {
            $data['person'] = array(
                array(
                    'person_id' => -1,
                    'contact_id' => -1,
                    'person_gender' => $kit['person_gender'],
                    'person_title' => $kit['person_title'],
                    'person_first_name' => $kit['origin']['contact_person_first_name'],
                    'person_last_name' => $kit['origin']['contact_person_last_name'],
                    'person_birthday' => (!empty($kit['origin']['contact_birthday'])) ? $kit['origin']['contact_birthday'] : '0000-00-00',
                    'person_primary_company_id' => isset($kit['primary_company_id']) ? $kit['primary_company_id'] : -1
                )
            );
        }
        else {
            $data['company'] = array(
                array(
                    'company_id' => -1,
                    'contact_id' => -1,
                    'company_name' => $kit['origin']['contact_company_name'],
                    'company_department' => $kit['origin']['contact_company_dept'],
                    'company_additional' => $kit['origin']['contact_company_add'],
                    'company_primary_person_id' => isset($kit['primary_person_id']) ? $kit['primary_person_id'] : -1
                    )
            );
        }

        // get the KIT note for the contact
        $data['note'] = array();
        if (false !== ($note = $this->KeepInTouch->getNote($kit['origin']['contact_id']))) {
            $data['note'][] = array(
                'contact_id' => -1,
                'note_id' => -1,
                'note_title' => 'Imported from KeepInTouch',
                'note_type' => 'TEXT',
                'note_content' => strip_tags($note['memo_memo']),
                'note_originator' => $note['memo_update_by'],
                'note_date' => $note['memo_update_when']
            );
        }

        // insert the contact data
        if (!$this->Contact->insert($data, $contact_id)) {
            self::$message = $this->Contact->getMessage();
            return false;
        }

        // get the KIT protocol
        $protocols = $this->KeepInTouch->getProtocol($kit['origin']['contact_id']);
        foreach ($protocols as $protocol) {
            $text = strip_tags($protocol['protocol_memo']);
            $date = $protocol['protocol_date'];
            $originator = $protocol['protocol_update_by'];
            $this->Contact->addProtocolInfo($contact_id, $text, $date, $originator);
        }

        $this->Contact->addProtocolInfo($contact_id, 'Import from KeepInTouch successfull.');

        return $contact_id;
    }

    public function execute(Application $app)
    {
        // initialize the class
        $this->initialize($app);

        if (is_null($this->app['session']->get('KIT_IMPORT_CONTACTS_DETECTED', null))) {
            // no session set - show the start dialog
            return $this->start($app);
        }

        $this->app['monolog']->addInfo('Start Import from KeepInTouch');
        $counter = 0;
        $prompt_success = true;
        if (self::$import_is_possible) {
            // get all KIT IDs
            $kit_ids = $this->KeepInTouch->getAllKITids();

            // check for additional fields
            // - not implemented yet -

            foreach ($kit_ids as $kit_id) {
                $kit = $this->KeepInTouch->getKITrecord($kit_id['contact_id']);
                // first check the login
                if ($this->Contact->existsLogin($kit['login'])) {
                    // this contact already exists!
                    continue;
                }

                // determine the contact types
                $add_person = false;
                $add_company = false;
                if (!empty($kit['origin']['contact_company_name'])) {
                    $add_company = true;
                }
                if (!empty($kit['origin']['contact_person_last_name'])) {
                    $add_person = true;
                }
                if (!$add_person && !$add_company) {
                    if ($kit['origin']['contact_type'] == 'typePerson') {
                        $add_person = true;
                    }
                    else {
                        $add_company = true;
                    }
                }

                $person_contact_id = -1;
                if ($add_person) {
                    if (!$this->addContact($kit, 'PERSON', $person_contact_id)) {
                        // something went wrong!
                        break;
                    }
                }

                $company_contact_id = -1;
                if ($add_company) {
                    if ($person_contact_id > -1) {
                        // change the login for the company
                        $kit['login'] = $kit['login'].'_2';
                        if ($this->Contact->existsLogin($kit['login'])) {
                            // this contact already exists!
                            continue;
                        }
                        $kit['primary_person_id'] = $person_contact_id;
                    }
                    if (!$this->addContact($kit, 'COMPANY', $company_contact_id)) {
                        // something went wrong!
                        break;
                    }
                    if ($person_contact_id > -1) {
                        $data = array(
                            'contact' => array(
                                    'contact_id' => $person_contact_id
                                ),
                            'person' => array(
                                array(
                                    'person_id' => $this->Contact->getPersonIDbyContactID($person_contact_id),
                                    'contact_id' => $person_contact_id,
                                    'person_primary_company_id' => $company_contact_id
                                )
                            )
                        );
                        // update the person contact data
                        if (!$this->Contact->update($data, $person_contact_id)) {
                            // something went wrong!
                            self::$message = $this->Contact->getMessage();
                            break;
                        }
                    }
                }

                // increase counter
                $counter++;
                $total = $this->app['session']->get('KIT_IMPORT_CONTACTS_IMPORTED', 0) + $counter;
                $this->app['session']->set('KIT_IMPORT_CONTACTS_IMPORTED', $total);

                if (((microtime(true) - self::$script_start) + 5) > self::$max_execution_time) {
                    // abort import to prevent timeout
                    $this->setMessage('To prevent a timeout of the script the import was aborted after import of %counter% records. Please reload this page to continue the import process.',
                            array('%counter%' => $counter));
                    $this->app['monolog']->addInfo(sprintf('[Import KeepInTouch] Script aborted after %.3f seconds and %d records to prevent a timeout', microtime(true) - self::$script_start, $counter));
                    $prompt_success = false;
                    break;
                }
            }

            $contacts_detected = $this->app['session']->get('KIT_IMPORT_CONTACTS_DETECTED', 0);
            $contacts_imported = $this->app['session']->get('KIT_IMPORT_CONTACTS_IMPORTED', 0);

            if ($prompt_success) {
                $this->setMessage('The import from KeepInTouch was successfull finished.');
                $this->app['monolog']->addInfo('The import from KeepInTouch was successfull finished.');

                $this->app['session']->remove('KIT_IMPORT_CONTACTS_DETECTED');
                $this->app['session']->remove('KIT_IMPORT_CONTACTS_IMPORTED');
            }
        }
        else {
            $this->setMessage('There exists no KeepInTouch installation at the parent CMS!');
        }

        return $this->app['twig']->render($this->app['utils']->getTemplateFile('@phpManufaktur/Contact/Template', 'backend/import/execute.keepintouch.twig'),
            array(
                'message' => $this->getMessage(),
                'contacts' => array(
                    'detected' => $contacts_detected,
                    'imported' => $contacts_imported
                )
            ));
    }
}
