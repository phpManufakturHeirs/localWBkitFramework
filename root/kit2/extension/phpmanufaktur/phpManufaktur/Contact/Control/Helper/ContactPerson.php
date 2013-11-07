<?php

/**
 * Contact
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de/contact
 * @copyright 2013 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\Contact\Control\Helper;

use Silex\Application;
use phpManufaktur\Contact\Data\Contact\Communication;
use phpManufaktur\Contact\Data\Contact\Person as PersonData;
use phpManufaktur\Contact\Control\Helper\ContactException;

class ContactPerson extends ContactParent
{
    protected $CommunicationData = null;
    protected $PersonData = null;
    protected $ContactCommunication = null;

    /**
     * Constructor
     *
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        parent::__construct($app);
        $this->CommunicationData = new Communication($this->app);
        $this->PersonData = new PersonData($this->app);
        $this->ContactCommunication = new ContactCommunication($this->app);
    }

    /**
     * Return a default (empty) PERSON contact record.
     *
     * @return array
     */
    public function getDefaultRecord()
    {
        return $this->PersonData->getDefaultRecord();
    }

    /**
     * Validate the given PERSON data record
     *
     * @param reference array $person_data
     * @param array $contact_data
     * @param array $option
     * @return boolean
     */
    public function validate(&$person_data, $contact_data=array(), $option=array())
    {
        // the person_id must be always set!
        if (!isset($person_data['person_id'])) {
            $this->setMessage("Missing the %identifier%! The ID should be set to -1 if you insert a new record.",
                array('%identifier%' => 'person_id'));
            return false;
        }
        // check if any items are NULL
        foreach ($person_data as $key => $value) {
            if (is_null($value)) {
                switch ($key) {
                    case 'person_gender':
                        $person_data[$key] = 'MALE';
                        break;
                    case 'person_title':
                    case 'person_first_name':
                    case 'person_last_name':
                    case 'person_nick_name':
                        $person_data[$key] = '';
                        break;
                    case 'person_birthday':
                        $person_data[$key] = '0000-00-00 00:00:00';
                        break;
                    case 'person_status':
                        $person_data[$key] = 'ACTIVE';
                        break;
                    case 'contact_id':
                    case 'person_primary_address_id':
                    case 'person_primary_phone_id':
                    case 'person_primary_email_id':
                    case 'person_primary_company_id':
                    case 'person_primary_note_id':
                        // all integer fields ...
                        $person_data[$key] = -1;
                        break;
                    default:
                        throw new ContactException("The key $key is not defined!");
                }
            }
        }

        return true;
    }

    /**
     * Insert a new PERSON record. Check first for values which belong to depending
     * contact tables
     *
     * @param array $data
     * @param integer $contact_id
     * @param reference integer $person_id
     * @throws ContactException
     * @return boolean
     */
    public function insert($data, $contact_id, &$person_id=-1)
    {
        // enshure that the contact_id isset
        $data['contact_id'] = $contact_id;

        if (!$this->validate($data)) {
            return false;
        }
        $person_id = -1;
        $this->PersonData->insert($data, $person_id);
        $this->app['monolog']->addInfo("Inserted person record for the contactID {$contact_id}", array(__METHOD__, __LINE__));
        return true;
    }

    /**
     * Update the given person contact block
     *
     * @param array $new_data
     * @param array $old_data
     * @param integer $person_id
     * @param reference boolean $has_changed
     * @return boolean
     */
    public function update($new_data, $old_data, $person_id, &$has_changed=false)
    {
        $has_changed = false;
        $changed = array();

        foreach ($new_data as $key => $value) {
            if ($key === 'person_id') continue;
            if ((is_null($old_data[$key]) && !is_null($value)) || (isset($old_data[$key]) && ($old_data[$key] != $value))) {
                $changed[$key] = $value;
            }
        }
        if (!empty($changed)) {
            $this->PersonData->update($changed, $person_id);
            $has_changed = true;
        }
        return true;
    }
}

