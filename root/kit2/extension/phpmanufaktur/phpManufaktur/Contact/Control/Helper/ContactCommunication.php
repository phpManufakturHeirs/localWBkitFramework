<?php

/**
 * Contact
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de/Contact
 * @copyright 2013 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\Contact\Control\Helper;

use Silex\Application;
use phpManufaktur\Contact\Control\Helper\ContactParent;
use Symfony\Component\Validator\Constraints as Assert;
use phpManufaktur\Contact\Data\Contact\Communication;
use phpManufaktur\Contact\Data\Contact\CommunicationType;
use phpManufaktur\Contact\Data\Contact\CommunicationUsage;
use phpManufaktur\Contact\Data\Contact\Contact as ContactData;

class ContactCommunication extends ContactParent
{
    protected $Communication = null;
    protected $CommunicationType = null;
    protected $CommunicationUsage = null;
    protected $ContactData = null;

    /**
     * Constructor
     *
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        parent::__construct($app);
        $this->Communication = new Communication($this->app);
        $this->CommunicationType = new CommunicationType($this->app);
        $this->CommunicationUsage = new CommunicationUsage($this->app);
        $this->ContactData = new ContactData($this->app);
    }

    /**
     * Return a default (empty) COMMUNICATION record
     *
     * @return array
     */
    public function getDefaultRecord()
    {
        return $this->Communication->getDefaultRecord();
    }

    /**
     * Validate the COMMUNICATION entry
     *
     * @param reference array $communication_data
     * @param array $contact_data
     * @param array $option
     * @return boolean
     */
    public function validate(&$communication_data, $contact_data=array(), $option=array())
    {
        // the communication_id must be always set!
        if (!isset($communication_data['communication_id']) || !is_numeric($communication_data['communication_id'])) {
            $this->setAlert("Missing the %identifier%! The ID should be set to -1 if you insert a new record.",
                array('%identifier%' => 'communication_id'), self::ALERT_TYPE_WARNING);
            return false;
        }

        // check if any value is NULL
        foreach ($communication_data as $key => $value) {
            if (is_null($value)) {
                switch ($key) {
                    case 'contact_id':
                        $communication_data[$key] = -1;
                        break;
                    case 'communication_type':
                        $communication_data[$key] = 'NONE';
                        break;
                    case 'communication_usage':
                        $communication_data[$key] = 'OTHER';
                        break;
                    case 'communication_value':
                        $communication_data[$key] = '';
                        break;
                    case 'communication_status':
                        $communication_data[$key] = 'ACTIVE';
                    default:
                        throw new ContactException("The key $key is not defined!");
                }
            }
        }

        if (!isset($communication_data['communication_type']) || empty($communication_data['communication_type'])) {
            $this->setAlert("The COMMUNICATION TYPE must be set!", array(), self::ALERT_TYPE_WARNING);
            return false;
        }

        if (!$this->CommunicationType->existsType($communication_data['communication_type'])) {
            $this->setAlert("The COMMUNICATION TYPE %type% does not exists!",
                array('%type%' => $communication_data['communication_type']), self::ALERT_TYPE_WARNING);
            return false;
        }

        if (!isset($communication_data['communication_usage']) || empty($communication_data['communication_usage'])) {
            if (isset($option['usage']['default']) && !empty($option['usage']['default'])) {
                $communication_data['communication_usage'] = $option['usage']['default'];
            }
            else {
                $this->setAlert("The COMMUNICATION USAGE must be set!", array(), self::ALERT_TYPE_WARNING);
                return false;
            }
        }

        if (!$this->CommunicationUsage->existsUsage($communication_data['communication_usage'])) {
            $this->setAlert("The COMMUNICATION USAGE %usage% does not exists!",
                array('%usage%' => $communication_data['communication_usage']), self::ALERT_TYPE_WARNING);
            return false;
        }

        if (!isset($communication_data['communication_value']) || empty($communication_data['communication_value'])) {
            if (isset($option['value']['ignore_if_empty']) && (false === $option['value']['ignore_if_empty'])) {
                // dont ignore an empty value
                $this->setAlert("The COMMUNICATION VALUE should not be empty!", array(), self::ALERT_TYPE_WARNING);
                return false;
            }
        }

        if (($communication_data['communication_type'] === 'EMAIL') && !empty($communication_data['communication_value'])) {
            $errors = $this->app['validator']->validateValue($communication_data['communication_value'], new Assert\Email());
            if (count($errors) > 0) {
                $this->setAlert('The email address %email% is not valid, please check your input!',
                    array('%email%' => $communication_data['communication_value']), self::ALERT_TYPE_WARNING);
                return false;
            }
        }

        return true;
    }

    /**
     * Insert a new COMMUNICATION record
     *
     * @param array $data
     * @param integer $contact_id
     * @param reference integer $communication_id
     * @param reference boolean $has_inserted
     * @return boolean
     */
    public function insert($data, $contact_id, &$communication_id=-1, &$has_inserted=null)
    {
        // enshure that the contact_id isset
        $data['contact_id'] = $contact_id;

        $has_inserted = false;

        if (empty($data['communication_value'])) {
            // skip empty value
            $this->app['monolog']->addDebug("Skipped empty communication entry type {$data['communication_type']} for contact ID {$data['contact_id']}.");
            // no error - return TRUE!
            return true;
        }
        // validate the entry
        if (!$this->validate($data)) {
            return false;
        }
        // insert the new communication entry
        $this->Communication->insert($data, $communication_id);

        if ($data['communication_type'] == 'EMAIL') {
            // check primary ID for EMAIL
            if ($this->ContactData->getPrimaryEmailID($contact_id) < 1) {
                $this->ContactData->setPrimaryEmailID($contact_id, $communication_id);
                $this->app['monolog']->addDebug("Set communication ID $communication_id as primary email for contact ID $contact_id.");
            }
        }
        elseif ($data['communication_type'] == 'PHONE') {
            // check primary ID for PHONE
            if ($this->ContactData->getPrimaryPhoneID($contact_id) < 1) {
                $this->ContactData->setPrimaryPhoneID($contact_id, $communication_id);
                $this->app['monolog']->addDebug("Set communication ID $communication_id as primary phone for contact ID $contact_id.");
            }
        }

        $has_inserted = true;
        return true;
    }

    /**
     * Update the given COMMUNICATION record
     *
     * @param array $new_data
     * @param array $old_data
     * @param integer $communication_id
     * @param reference boolean $has_changed
     * @return boolean
     */
    public function update($new_data, $old_data, $communication_id, &$has_changed=false)
    {
        $has_changed = false;

        if (empty($new_data['communication_value'])) {
            // check if this entry can be deleted
            if (($this->ContactData->getPrimaryEmailID($old_data['contact_id']) == $communication_id) ||
                ($this->ContactData->getPrimaryPhoneID($old_data['contact_id']) == $communication_id)) {
                // entry is marked for primary communication and can not deleted!
                $this->setAlert("The %type% entry %value% is marked for primary communication and can not removed!",
                    array('%type%' => $old_data['communication_type'], '%value%' => $old_data['communication_value']),
                    self::ALERT_TYPE_WARNING);
                return false;
            }
            // delete the entry
            $this->Communication->delete($communication_id);
            $this->setAlert("The communication entry %communication% was successfull deleted.",
                array('%communication%' => $old_data['communication_value']), self::ALERT_TYPE_SUCCESS);
            $has_changed = true;
            return true;
        }

        // validate the new data
        if (!$this->validate($new_data)) {
            return false;
        }

        // process the new data
        $changed = array();
        foreach ($new_data as $key => $value) {
            if ($key === 'communication_id') continue;
            if (isset($old_data[$key]) && ($old_data[$key] != $value)) {
                $changed[$key] = $value;
            }
        }

        if (!empty($changed)) {
            // update the communication record
            $this->Communication->update($changed, $communication_id);
            $has_changed = true;
        }
        return true;
    }
}
