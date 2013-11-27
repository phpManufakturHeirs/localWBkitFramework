<?php

/**
 * Contact
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de/contact
 * @copyright 2013 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\Contact\Control;

use Silex\Application;
use phpManufaktur\Contact\Control\Helper\ContactParent;
use phpManufaktur\Contact\Control\Helper\ContactException;
use phpManufaktur\Contact\Data\Contact\Contact as ContactData;
use Symfony\Component\Validator\Constraints as Assert;
use phpManufaktur\Contact\Data\Contact\Title;
use phpManufaktur\Contact\Data\Contact\Country;
use phpManufaktur\Contact\Data\Contact\Overview;
use phpManufaktur\Contact\Control\Helper\ContactAddress;
use phpManufaktur\Contact\Control\Helper\ContactCommunication;
use phpManufaktur\Contact\Control\Helper\ContactCompany;
use phpManufaktur\Contact\Control\Helper\ContactNote;
use phpManufaktur\Contact\Control\Helper\ContactPerson;
use phpManufaktur\Contact\Data\Contact\CategoryType;
use phpManufaktur\Contact\Control\Helper\ContactCategory;
use phpManufaktur\Contact\Control\Helper\ContactTag;
use phpManufaktur\Contact\Data\Contact\TagType;
use phpManufaktur\Contact\Data\Contact\Person;
use phpManufaktur\Contact\Data\Contact\Protocol;
use phpManufaktur\Contact\Data\Contact\Extra;
use phpManufaktur\Contact\Data\Contact\ExtraCategory;
use phpManufaktur\Contact\Data\Contact\ExtraType;

class Contact extends ContactParent
{

    protected static $contact_id = -1;

    protected $ContactData = null;
    protected $ContactPerson = null;
    protected $ContactCompany = null;
    protected $ContactCommunication = null;
    protected $ContactAddress = null;
    protected $ContactNote = null;
    protected $Overview = null;
    protected $ContactCategory = null;
    protected $ContactTag = null;
    protected $PersonData = null;
    protected $ProtocolData = null;
    protected $ExtraCategory = null;
    protected $ExtraType = null;
    protected $Extra = null;

    protected static $ContactBlocks = array(
        'contact' => array(
            'login' => array(
                'use_email_address' => true
            ),
            'name' => array(
                'use_login' => true
            )
        ),
        'person',
        'company',
        'communication' => array(
                'usage' => array(
                    'default' => 'PRIVATE'
                ),
                'value' => array(
                    'ignore_if_empty' => true
                )
            ),
        'address',
        'note',
        'category',
        'tag'
    );

    /**
     * Constructor
     *
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        parent::__construct($app);

        $this->ContactAddress = new ContactAddress($this->app);
        $this->ContactCommunication = new ContactCommunication($this->app);
        $this->ContactCompany = new ContactCompany($this->app);
        $this->ContactData = new ContactData($this->app);
        $this->ContactNote = new ContactNote($this->app);
        $this->ContactPerson = new ContactPerson($this->app);
        $this->Overview = new Overview($this->app);
        $this->ContactCategory = new ContactCategory($this->app);
        $this->ContactTag = new ContactTag($this->app);
        $this->PersonData = new Person($this->app);
        $this->ProtocolData = new Protocol($this->app);
        // extra fields
        $this->Extra = new Extra($this->app);
        $this->ExtraCategory = new ExtraCategory($this->app);
        $this->ExtraType = new ExtraType($this->app);
    }

    /**
     * Get the ContactBlocks used by the validate() functions
     *
     * @return array
     */
    public function getContactBlocks()
    {
        return self::$ContactBlocks;
    }

    /**
     * Replace the default ContentBlocks with the given $blocks
     *
     * @param array $blocks
     * @throws \Exception
     */
    public function setContactBlocks($blocks)
    {
        if (!is_array($blocks)) {
            throw new \Exception("ContactBlocks must be submitted as array!");
        }
        self::$ContactBlocks = array();
        foreach ($blocks as $block) {
            self::$ContactBlocks[] = strtolower($block);
        }
    }

    public function getTitleArrayForTwig()
    {
        $title = new Title($this->app);
        return $title->getArrayForTwig();
    }

    public function getCountryArrayForTwig()
    {
        $country = new Country($this->app);
        return $country->getArrayForTwig();
    }

    public function getCategoryArrayForTwig()
    {
        $categoryType = new CategoryType($this->app);
        return $categoryType->getArrayForTwig();
    }

    public function getTagArrayForTwig()
    {
        $tagType = new TagType($this->app);
        return $tagType->getArrayForTwig();
    }

    public function getContactsByTagsForTwig($tag_names, $status='ACTIVE', $status_operator='=')
    {
        return $this->Overview->getContactsByTagsForTwig($tag_names, $status, $status_operator);
    }

    /**
     * Get the contact record for this contact_id
     */
    public function getDefaultRecord($contact_type='PERSON')
    {
        $data = array(
            'contact' => array(
                'contact_id' => -1,
                'contact_name' => '',
                'contact_login' => '',
                'contact_type' => $contact_type,
                'contact_status' => 'ACTIVE',
                'contact_timestamp' => '0000-00-00 00:00:00',
            )
        );

        if ($data['contact']['contact_type'] === 'PERSON') {
            $data['person'] = array($this->ContactPerson->getDefaultRecord());
        }
        else {
            $data['company'] = array($this->ContactCompany->getDefaultRecord());
        }

        // default communication entry
        $data['communication'] = array(
            $this->ContactCommunication->getDefaultRecord()
        );

        // default address entry
        $data['address'] = array(
            $this->ContactAddress->getDefaultRecord()
        );

        // default note entry
        $data['note'] = array(
            $this->ContactNote->getDefaultRecord()
        );

        // default category entry
        $data['category'] = array(
            $this->ContactCategory->getDefaultRecord()
        );

        // default tag entry
        $data['tag'] = array(
            $this->ContactTag->getDefaultRecord()
        );

        return $data;
    }

    public function getDefaultAddressRecord()
    {
        return $this->ContactAddress->getDefaultRecord();
    }

    public function getDefaultCommunicationRecord()
    {
        return $this->ContactCommunication->getDefaultRecord();
    }

    public function getContactType($contact_id)
    {
        return $this->ContactData->getContactType($contact_id);
    }

    /**
     * Return the primary EMail address for the given contact ID
     *
     * @param integer $contact_id
     */
    public function getPrimaryEMailAddress($contact_id)
    {
        return $this->ContactData->getPrimaryEmailAddress($contact_id);
    }

    /**
     * General select function for contact records.
     * The identifier can be the contact_id or the login name.
     * Return a PERSON or a COMPANY record. If the identifier not exists return
     * a default contact array.
     *
     * @param mixed $identifier
     */
    public function select($identifier, $contact_type='PERSON')
    {
        if (!is_numeric($identifier)) {
            // try to get the contact ID by the login name
            if (!$identifier = $this->existsLogin($identifier)) {
                return $this->getDefaultRecord($contact_type);
            }
        }
        if (is_numeric($identifier)) {

            self::$contact_id = $identifier;
            if (self::$contact_id < 1) {
                return $this->getDefaultRecord($contact_type);
            }
            else {
                if (false === ($contact = $this->ContactData->select(self::$contact_id))) {
                    self::$contact_id = -1;
                    $this->setMessage("The contact with the ID %contact_id% does not exists!", array('%contact_id%' => $identifier));
                    return $this->getDefaultRecord($contact_type);
                }
                if (false === ($contact = $this->ContactData->selectContact(self::$contact_id))) {
                    $this->setMessage("Can't read the contact with the ID %contact_id% - it is possibly deleted.",
                        array('%contact_id%' => $identifier));
                    return $this->getDefaultRecord($contact_type);
                }
                return $contact;
            }
        }
        else {
            throw new ContactException("The identifier for SELECT a CONTACT must be an integer!");
        }
    }

    /**
     * Select the overview record for the given contact ID
     *
     * @param integer $contact_id
     * @return Ambigous <boolean, array> FALSE or overview record
     */
    public function selectOverview($contact_id)
    {
        return $this->Overview->select($contact_id);
    }

    /**
     * Validate the CONTACT block and perform some actions.
     * Can set the 'contact_login' and the 'contact_name' if specified in the
     * $option array
     *
     * @param reference array $data
     * @param array $contact_data
     * @param array $option
     * @return boolean
     */
    protected function validateContact(&$data, $contact_data=null, $option=null)
    {
        // the contact_id must be always set
        if (!isset($data['contact_id']) || !is_numeric($data['contact_id'])) {
            $this->setMessage("Missing the %identifier%! The ID should be set to -1 if you insert a new record.",
                array('%identifier%' => 'contact_id'));
            return false;
        }

        // the contact type must be always set
        $contact_types = $this->ContactData->getContactTypes();
        if (!isset($data['contact_type']) || !in_array($data['contact_type'], $contact_types)) {
            $this->setMessage("The contact_type must be always set (%contact_types%).",
                array('%contact_types%' => implode(', ', $contact_types)));
            return false;
        }

        if (!isset($option['mode']['insert'])) {
            // check only if not insert a new record
            if (!isset($data['contact_login']) || empty($data['contact_login'])) {
                // missing the login
                if (isset($option['login']['use_email_address']) && $option['login']['use_email_address']) {
                    // try to use the email as login

                    if (isset($contact_data['communication'])) {
                        $use_email = false;
                        foreach ($contact_data['communication'] as $communication) {
                            if (isset($communication['communication_type']) && $communication['communication_type'] == 'EMAIL') {
                                if (isset($communication['communication_value'])) {
                                    $errors = $this->app['validator']->validateValue($communication['communication_value'], new Assert\Email());
                                    if (count($errors) > 0) {
                                        $this->setMessage("The contact login must be set!");
                                        return false;
                                    }
                                    else {
                                        $data['contact_login'] = strtolower($communication['communication_value']);
                                        $use_email = true;
                                        break;
                                    }
                                }

                            }
                        }
                        if (!$use_email) {
                            $this->setMessage("The contact login must be set!");
                            return false;
                        }
                    }
                    else {
                        $this->setMessage("The contact login must be set!");
                        return false;
                    }
                }
                else {
                    $this->setMessage("The contact login must be set!");
                    return false;
                }
            }

            if (!isset($data['contact_name']) || empty($data['contact_name'])) {
                if (isset($option['name']['use_login']) && $option['name']['use_login']) {
                    // use the LOGIN also for the NAME
                    $data['contact_name'] = $data['contact_login'];
                }
                else {
                    $this->setMessage("The contact name must be set!");
                    return false;
                }
            }
        }

        // if this is new record check it the login name is available
        if (($data['contact_id'] < 1) &&
            (false !== ($check = $this->ContactData->selectLogin($data['contact_login'])))) {
            $this->setMessage('The login <b>%login%</b> is already in use, please choose another one!',
                array('%login%' => $data['contact_login']));
            return false;
        }

        return true;
    }

    /**
     * Validate the given $data record for all contact types.
     * With $options you can define the ContentBlocks which will be validated.
     * You can also use SetContactBlocks() to set the ContentBlocks global for
     * all operations and not only for validate()
     *
     * @param array $data
     * @param array $options if empty the global ContactBlocks will be used
     * @return boolean
     */
    public function validate(&$contact_data, $options=array())
    {
        if (!is_array($options) || empty($options)) {
            $options = self::$ContactBlocks;
        }

        $check = true;
        $this->clearMessage();

        foreach ($options as $key => $value) {
            if (is_array($value)) {
                $block = strtolower($key);
                $validate_options = $value;
            }
            else {
                $block = strtolower($value);
                $validate_options = array();
            }
            switch ($block) {
                case 'contact':
                    // check the contact block
                    if (isset($contact_data[$block]) && is_array($contact_data[$block])) {
                        if (!$this->validateContact($contact_data[$block], $contact_data, $validate_options)) {
                            $check = false;
                        }
                    }
                    break;
                case 'person':
                    // check the person block
                    if (isset($contact_data[$block]) && is_array($contact_data[$block])) {
                        $level = 0;
                        foreach ($contact_data[$block] as $person_data) {
                            if (!$this->ContactPerson->validate($person_data, $contact_data, $validate_options)) {
                                $check = false;
                            }
                            $contact_data[$block][$level] = $person_data;
                            $level++;
                        }
                    }
                    break;
                case 'company':
                    // check the company block
                    if (isset($contact_data[$block]) && is_array($contact_data[$block])) {
                        $level = 0;
                        foreach ($contact_data[$block] as $company_data) {
                            if (!$this->ContactCompany->validate($company_data, $contact_data, $validate_options)) {
                                $check = false;
                            }
                            $contact_data[$block][$level] = $company_data;
                            $level++;
                        }
                    }
                    break;
                case 'communication':
                    // check the communication block
                    if (isset($contact_data[$block]) && is_array($contact_data[$block])) {
                        $level = 0;
                        foreach ($contact_data[$block] as $communication_data) {
                            if (!$this->ContactCommunication->validate($communication_data, $contact_data, $validate_options)) {
                                $check = false;
                            }
                            $contact_data[$block][$level] = $communication_data;
                            $level++;
                        }
                    }
                    break;
                case 'address':
                    // check the address block
                    if (isset($contact_data[$block]) && is_array($contact_data[$block])) {
                        $level = 0;
                        foreach ($contact_data[$block] as $address_data) {
                            if (!$this->ContactAddress->validate($address_data, $contact_data, $validate_options)) {
                                $check = false;
                            }
                            $contact_data[$block][$level] = $address_data;
                            $level++;
                        }
                    }
                    break;
                case 'note':
                    if (isset($contact_data[$block]) && is_array($contact_data[$block])) {
                        $level = 0;
                        foreach ($contact_data[$block] as $note_data) {
                            if (!$this->ContactNote->validate($note_data, $contact_data, $validate_options)) {
                                $check = false;
                            }
                            $contact_data[$block][$level] = $note_data;
                            $level++;
                        }
                    }
                    break;
                case 'category':
                    if (isset($contact_data[$block]) && is_array($contact_data[$block])) {
                        $level = 0;
                        foreach ($contact_data[$block] as $category_data) {
                            if (!$this->ContactCategory->validate($category_data, $contact_data, $validate_options)) {
                                $check = false;
                            }
                            $contact_data[$block][$level] = $category_data;
                            $level++;
                        }
                    }
                case 'tag':
                    if (isset($contact_data[$block]) && is_array($contact_data[$block])) {
                        $level = 0;
                        foreach ($contact_data[$block] as $tag_data) {
                            if (!$this->ContactTag->validate($tag_data, $contact_data, $validate_options)) {
                                $check = false;
                            }
                            $contact_data[$block][$level] = $tag_data;
                            $level++;
                        }
                    }
                default:
                    // ContactBlock does not exists
                    throw new \Exception("The ContactBlock $block does not exists!");
            }
        }

        // return the result of the check
        return $check;
    }

    /**
     * Insert the contact block of the new contact record into the database
     *
     * @param array $contact_data
     * @param array $complete_data
     * @param reference integer $contact_id
     * @return boolean
     */
    protected function insertContact($contact_data, $complete_data=null, &$contact_id=null)
    {
        $contact_data['contact_id'] = -1;

        $contact_blocks = $this->getContactBlocks();
        $option = isset($contact_blocks['content']) ? $contact_blocks['content'] : array();
        $option['mode'] = 'insert';

        // if no contact_login isset, try to set the email address as login
        if (!isset($contact_data['contact_login']) || empty($contact_data['contact_login'])) {
            // try to get an email address
            $check = false;
            if (isset($complete_data['communication'])) {
                foreach ($complete_data['communication'] as $communication) {
                    if (isset($communication['communication_type']) && ($communication['communication_type'] == 'EMAIL') &&
                        !empty($communication['communication_value'])) {
                        $contact_data['contact_login'] = $communication['communication_value'];
                        $check = true;
                        break;
                    }
                }
            }

            if (!$check) {
                $this->setMessage("The login_name or a email address must be always set, can't insert the record!");
                return false;
            }
        }

        if (!isset($contact_data['contact_name']) || empty($contact_data['contact_name'])) {
            // set the contact_login also as contact_name
            $contact_data['contact_name'] = $contact_data['contact_login'];
        }

        if (!$this->validateContact($contact_data, $complete_data, $option)) {
            // contact validation fail
            return false;
        }

        // insert the new record
        $this->ContactData->insert($contact_data, $contact_id);

        return true;
    }


    /**
     * Insert the given $data record into the contact database. Process all needed
     * steps, uses transaction and roll back if necessary.
     *
     * @param array $data
     * @param reference integer $contact_id
     * @throws ContactException
     * @return boolean
     */
    public function insert($data, &$contact_id=null)
    {

        try {
            // BEGIN TRANSACTION
            $this->app['db']->beginTransaction();

            $this->clearMessage();

            // get the contact blocks with the options
            $contact_blocks = $this->getContactBlocks();

            // first step: insert a contact record
            if (!isset($data['contact'])) {
                $this->setMessage("Missing the contact block! Can't insert the new record!");
                $this->app['db']->rollback();
                return false;
            }

            if (!$this->insertContact($data['contact'], $data, self::$contact_id)) {
                $this->app['db']->rollback();
                return false;
            }
            // set the contact ID
            $data['contact']['contact_id'] = self::$contact_id;
            $contact_id = self::$contact_id;

            // as next we need the person record
            if (isset($data['person'])) {
                foreach ($data['person'] as $person) {
                    if (!is_array($person)) continue;
                    if (!$this->ContactPerson->insert($person, self::$contact_id)) {
                        // something went wrong, rollback and return with message
                        $this->app['db']->rollback();
                        return false;
                    }
                }
            }

            // COMPANY
            if (isset($data['company'])) {
                foreach ($data['company'] as $company) {
                    if (!is_array($company)) continue;
                    if (!$this->ContactCompany->insert($company, self::$contact_id)) {
                        // something went wrong, rollback and return with message
                        $this->app['db']->rollback();
                        return false;
                    }
                }
            }

            // check the communication
            if (isset($data['communication'])) {
                foreach ($data['communication'] as $communication) {
                    if (!is_array($communication)) continue;
                    if (!$this->ContactCommunication->insert($communication, self::$contact_id)) {
                        // rollback and return to the dialog
                        $this->app['db']->rollback();
                        return false;
                    }
                }
            }

            if (isset($data['address'])) {
                foreach ($data['address'] as $address) {
                    // loop through the addresses
                    if (!is_array($address)) continue;
                    if (!$this->ContactAddress->insert($address, self::$contact_id)) {
                        // rollback and return to the dialog
                        $this->app['db']->rollback();
                        return false;
                    }
                }
            }

            if (isset($data['note'])) {
                foreach ($data['note'] as $note) {
                    if (!is_array($note)) continue;
                    if (!$this->ContactNote->insert($note, self::$contact_id)) {
                        // something went wrong, rollback
                        $this->app['db']->rollback();
                        return false;
                    }
                }
            }

            if (isset($data['category'])) {
                foreach ($data['category'] as $category) {
                    if (!is_array($category)) continue;
                    $category_id = -1;
                    if (!$this->ContactCategory->insert($category, self::$contact_id, $category_id)) {
                        // something went wrong, rollback
                        $this->app['db']->rollback();
                        return false;
                    }
                    if ($category_id > 0) {
                        // check if extra fields are associated to the category
                        $category_type_id = $this->ContactCategory->selectCategoryTypeID($category_id);
                        $field_ids = $this->ExtraCategory->selectTypeIDByCategoryTypeID($category_type_id);
                        foreach ($field_ids as $field_id) {
                            $this->Extra->insert($contact_id, $category_id, $category['category_type_name'], $field_id);
                        }
                        // check if any data are submitted
                        if (isset($data['extra_fields'])) {
                            foreach ($data['extra_fields'] as $field) {
                                if (false === ($type = $this->ExtraType->selectName($field['extra_type_name']))) {
                                    $this->setMessage('Missing the field `extra_type_name`');
                                    $this->app['db']->rollback();
                                    return false;
                                }
                                $this->Extra->insert($contact_id, $category_id, $category['category_type_name'], $type['extra_type_id'], $field['extra_value']);
                            }
                        }
                    }
                }

            }

            if (isset($data['tag'])) {
                foreach ($data['tag'] as $tag) {
                    if (!is_array($tag)) continue;
                    if (!$this->ContactTag->insert($tag, self::$contact_id)) {
                        // something went wrong, rollback
                        $this->app['db']->rollback();
                        return false;
                    }
                }
            }



            // all complete - now we refresh the OVERVIEW
            $this->Overview->refresh($contact_id);

            // contact protocol
            $this->ProtocolData->addInfo(self::$contact_id, 'Contact successfull inserted.');

            // COMMIT TRANSACTION
            $this->app['db']->commit();

            if (!$this->isMessage()) {
                $this->setMessage("Inserted the new contact with the ID %contact_id%.", array('%contact_id%' => self::$contact_id));
            }

            return true;
        } catch (\Exception $e) {
            // ROLLBACK TRANSACTION
            $this->app['db']->rollback();
            throw new ContactException($e);
        }
    }

    /**
     * Update a contact block record with the given new and old data for the
     * specified contact ID
     *
     * @param array $new_data the data to update
     * @param array $old_data the existing data from database
     * @param integer $contact_id
     * @param reference boolean $has_changed will be set to true if data has changed
     * @return boolean
     */
    protected function updateContact($new_data, $old_data, $contact_id, &$has_changed=false)
    {
        $has_changed = false;
        $changed = array();

        foreach ($new_data as $key => $value) {
            if ($key == 'contact_key') continue;
            if ($old_data[$key] != $value) {
                $changed[$key] = $value;
            }
        }

        if (!empty($changed)) {
            foreach ($changed as $key => $value) {
                switch ($key) {
                    case 'contact_login':
                        if (is_null($value) || empty($value)) {
                            // contact_login must be always set!
                            $this->setMessage("The field %field% can not be empty!", array('%field%' => 'contact_login'));
                            return false;
                        }
                        // check if the login already exists
                        if ($this->ContactData->existsLogin($value, $contact_id)) {
                            $this->setMessage('The login <b>%login%</b> is already in use, please choose another one!',
                                array('%login%' => $value));
                            return false;
                        }
                        break;
                    case 'contact_name':
                        if (is_null($value) || empty($value)) {
                            // contact_name must be always set!
                            $this->setMessage("The field %field% can not be empty!", array('%field%' => 'contact_name'));
                            return false;
                        }
                        if ($this->ContactData->existsName($value, $contact_id)) {
                            // the contact_name already exists - tell it the user but update the record!
                            $this->setMessage("The contact name %name% already exists! The update has still executed, please check if you really want this duplicate name.",
                                array('%name%' => $value));
                            // don't return false!!!
                        }
                }
            }
            $this->ContactData->update($changed, $contact_id);
            $has_changed = true;
        }

        return true;
    }

    /**
     * Update the complete contact with all blocks
     *
     * @param array $data regular contact array
     * @param integer $contact_id
     * @param reference boolean $data_changed will be set to true if data has changed
     * @throws ContactException
     * @throws \Exception
     * @return boolean
     */
    public function update($data, $contact_id, &$data_changed=false)
    {
        // first get the existings record
        if (false === ($old = $this->ContactData->selectContact($contact_id))) {
            $this->setMessage("The contact with the ID %contact_id% does not exists!",
                array('%contact_id%' => $contact_id));
            return false;
        }
        self::$contact_id = $contact_id;

        try {
            // start transaction
            $this->app['db']->beginTransaction();

            $this->clearMessage();

            $data_changed = false;

            // contact block
            if (isset($data['contact'])) {
                $has_changed = false;
                if (!$this->updateContact($data['contact'], $old['contact'], $contact_id, $has_changed)) {
                    // rollback
                    $this->app['db']->rollback();
                    return false;
                }
                if ($has_changed) {
                    $data_changed = true;
                }
            }
            else {
                $this->setMessage("The contact block must be set always!");
                // rollback
                $this->app['db']->rollback();
                return false;
            }

            if ($old['contact']['contact_type'] == 'COMPANY') {
                // Contact TYPE: COMPANY
                if (isset($data['company'])) {
                    foreach ($data['company'] as $new_company) {
                        $has_changed = false;
                        foreach ($old['company'] as $old_company) {
                            if ($old_company['company_id'] == $new_company['company_id']) {
                                // update the company
                                if (!$this->ContactCompany->update($new_company, $old_company, $new_company['company_id'], $has_changed)) {
                                    // rollback
                                    $this->app['db']->rollback();
                                    return false;
                                }
                                if ($has_changed) {
                                    $data_changed = true;
                                }
                                break;
                            }
                        }
                    }
                }

            }
            else {
                // Contact TYPE: PERSON
                if (isset($data['person'])) {
                    foreach ($data['person'] as $new_person) {
                        $has_changed = false;
                        if (count($old['person']) < 1) {
                            // no handling of multiple persons
                            throw new ContactException("The handling of multiple persons within one account of type PERSON is not supported yet.");
                        }
                        foreach ($old['person'] as $old_person) {
                            if ($old_person['person_id'] == $new_person['person_id']) {
                                // update the person
                                if (!$this->ContactPerson->update($new_person, $old_person, $new_person['person_id'], $has_changed)) {
                                    // rollback
                                    $this->app['db']->rollback();
                                    return false;
                                }
                                if ($has_changed) {
                                    $data_changed = true;
                                }
                                break;
                            }
                        }
                    }
                }
            }

            if (isset($data['communication'])) {
                foreach ($data['communication'] as $new_communication) {
                    if (!is_array($new_communication)) continue;
                    if (!isset($new_communication['communication_id'])) {
                        throw new ContactException("Update check fail because the 'communication_id' is missing in the 'communication' block!");
                    }
                    if ($new_communication['communication_id'] < 1) {
                        // insert a new communication record
                        $communication_id = -1;
                        $has_inserted = false;
                        if (!$this->ContactCommunication->insert($new_communication, $contact_id, $communication_id, $has_inserted)) {
                            // rollback
                            $this->app['db']->rollback();
                            return false;
                        }
                        if ($has_inserted) {
                            $data_changed = true;
                        }
                        continue;
                    }
                    $processed = false;
                    foreach ($old['communication'] as $old_communication) {
                        if ($old_communication['communication_id'] == $new_communication['communication_id']) {
                            $has_changed = false;
                            if (!$this->ContactCommunication->update($new_communication, $old_communication, $new_communication['communication_id'], $has_changed)) {
                                // rollback
                                $this->app['db']->rollback();
                                return false;
                            }
                            if ($has_changed) {
                                $data_changed = true;
                            }
                            $processed = true;
                            break;
                        }
                    }
                    if (!$processed) {
                        // the communication entry was not processed!
                        $this->setMessage("The %entry% entry with the ID %id% was not processed, there exists no fitting record for comparison!",
                            array(
                                '%id%' => $new_communication['communication_id'],
                                '%entry%' => 'communication'
                            ));
                        $this->addError("The communication ID {$new_communication['communication_id']} was not updated because it was not found in the table!",
                            array(__METHOD__, __LINE__));
                    }
                }
            }

            if (isset($data['address'])) {
                foreach ($data['address'] as $new_address) {
                    if (!is_array($new_address)) continue;
                    if (!isset($new_address['address_id'])) {
                        throw new ContactException("Update check fail because the 'address_id' is missing in the 'address' block!");
                    }
                    if ($new_address['address_id'] < 1) {
                        // insert a new address
                        $address_id = -1;
                        $has_inserted = false;
                        $this->ContactAddress->insert($new_address, $contact_id, $address_id, $has_inserted);
                        if ($has_inserted) {
                            $data_changed = true;
                        }
                        continue;
                    }
                    $processed = false;
                    foreach ($old['address'] as $old_address) {
                        if ($old_address['address_id'] == $new_address['address_id']) {
                            $has_changed = false;
                            if (!$this->ContactAddress->update($new_address, $old_address, $new_address['address_id'], $has_changed)) {
                                // rollback
                                $this->app['db']->rollback();
                                return false;
                            }
                            if ($has_changed) {
                                $data_changed = true;
                            }
                            $processed = true;
                            break;
                        }
                    }
                    if (!$processed) {
                        // the address entry was not processed!
                        $this->setMessage("The %entry% entry with the ID %id% was not processed, there exists no fitting record for comparison!",
                            array(
                                '%id%' => $new_address['address_id'],
                                '%entry%' => 'address'
                            ));
                        $this->addError("The address ID {$new_address['address_id']} was not updated because it was not found in the table!",
                            array(__METHOD__, __LINE__));
                    }
                }
            }

            if (isset($data['note'])) {
                foreach ($data['note'] as $new_note) {
                    if (!is_array($new_note)) continue;
                    if (!isset($new_note['note_id'])) {
                        throw new \Exception("Update check fail because the 'note_id' is missing in the 'note' block!");
                    }
                    if ($new_note['note_id'] < 1) {
                        // insert a new note
                        $note_id = -1;
                        $has_inserted = false;
                        $this->ContactNote->insert($new_note, $contact_id, $note_id, $has_inserted);
                        if ($has_inserted) {
                            $data_changed = true;
                        }
                        continue;
                    }
                    $processed = false;
                    foreach ($old['note'] as $old_note) {
                        if ($old_note['note_id'] == $new_note['note_id']) {
                            $has_changed = false;
                            if (!$this->ContactNote->update($new_note, $old_note, $new_note['note_id'], $has_changed)) {
                                // rollback
                                $this->app['db']->rollback();
                                return false;
                            }
                            if ($has_changed) {
                                $data_changed = true;
                            }
                            $processed = true;
                            break;
                        }
                    }
                    if (!$processed) {
                        // the address entry was not processed!
                        $this->setMessage("The %entry% entry with the ID %id% was not processed, there exists no fitting record for comparison!",
                            array(
                                '%id%' => $new_note['note_id'],
                                '%entry%' => 'note'
                            ));
                        $this->addError("The note ID {$new_note['note_id']} was not updated because it was not found in the table!",
                        array(__METHOD__, __LINE__));
                    }
                }
            }

            if (isset($data['category'])) {
                foreach ($data['category'] as $new_category) {
                    if (!is_array($new_category)) continue;
                    $checked = false;
                    foreach ($old['category'] as $old_category) {
                        if ($old_category['category_type_name'] == $new_category['category_type_name']) {
                            $checked = true;
                            break;
                        }
                    }
                    if (!$checked) {
                        // insert a new category
                        $category_id = -1;
                        $has_inserted = false;
                        $this->ContactCategory->insert($new_category, $contact_id, $category_id, $has_inserted);
                        if ($has_inserted) {
                            $data_changed = true;
                            // check if extra fields are associated to the category
                            $category_type_id = $this->ContactCategory->selectCategoryTypeID($category_id);
                            $field_ids = $this->ExtraCategory->selectTypeIDByCategoryTypeID($category_type_id);
                            foreach ($field_ids as $field_id) {
                                $this->Extra->insert($contact_id, $category_id, $new_category['category_type_name'], $field_id);
                            }
                        }
                        continue;
                    }
                }
                foreach ($old['category'] as $old_category) {
                    $checked = false;
                    foreach ($data['category'] as $new_category) {
                        if (!is_array($new_category)) continue;
                        if ($new_category['category_type_name'] == $old_category['category_type_name']) {
                            $checked = true;
                            break;
                        }
                    }
                    if (!$checked) {
                        // delete the category
                        $this->ContactCategory->delete($old_category['category_id']);
                        $data_changed = true;
                        // check if extra fields are asscociated to the category
                        $this->Extra->delete($contact_id, $old_category['category_id']);
                    }
                }
            }

            if (isset($data['extra_fields'])) {
                foreach ($data['extra_fields'] as $field) {
                    $this->Extra->update($field['extra_id'], $field);
                }
            }

            if (isset($data['tag'])) {
                // insert new tags if needed
                foreach ($data['tag'] as $new_tag) {
                    if (!is_array($new_tag)) continue;
                    $tag_id = -1;
                    $has_inserted = false;
                    $this->ContactTag->insert($new_tag, $contact_id, $tag_id, $has_inserted);
                    if ($has_inserted) {
                        $data_changed = true;
                    }
                }
                // delete no longer needed tags
                foreach ($old['tag'] as $old_tag) {
                    $checked = false;
                    foreach ($data['tag'] as $new_tag) {
                        if (!is_array($new_tag)) continue;
                        if ($new_tag['tag_name'] == $old_tag['tag_name']) {
                            $checked = true;
                            break;
                        }
                    }
                    if (!$checked) {
                        // delete the tag
                        $this->ContactTag->delete($old_tag['tag_name']);
                        $data_changed = true;
                    }
                }
            }

            if ($data_changed) {
                // all complete - now we refresh the OVERVIEW
                $this->Overview->refresh($contact_id);
            }

            // contact protocol
            $this->ProtocolData->addInfo($contact_id, 'Contact successfull updated.');

            // commit transaction
            $this->app['db']->commit();

            if ($data_changed) {
                if (!$this->isMessage()) {
                    $this->setMessage("The contact with the ID %contact_id% was successfull updated.",
                        array('%contact_id%' => self::$contact_id));
                }
            }
            else {
                if (!$this->isMessage()) {
                    $this->setMessage("The contact record was not changed!");
                }
            }

            return true;
        } catch (ContactException $e) {
            // rollback transaction
            $this->app['db']->rollback();
            throw new ContactException($e);
        }
    }

    /**
     * Check if the desired contact login already existst. Optionally exclude the
     * given contact id from the check
     *
     * @param integer $contact_login
     * @param integer $exclude_contact_id
     * @throws \Exception
     * @return integer|boolean
     */
    public function existsLogin($contact_login, $exclude_contact_id=null)
    {
        return $this->ContactData->existsLogin($contact_login, $exclude_contact_id);
    }

    /**
     * Check if the given category name exists
     *
     * @param string $category_name
     * @return boolean
     */
    public function existsCategoryName($category_name)
    {
        return $this->ContactCategory->existsCategory($category_name);
    }

    /**
     * Create a new category
     *
     * @param array $data
     * @param integer reference $category_type_id
     */
    public function createCategory($data, &$category_type_id)
    {
        $this->ContactCategory->createCategory($data, $category_type_id);
    }

    /**
     * Check if a extra type name already exists
     *
     * @param string $extra_type_name
     * @return boolean
     */
    public function existsExtraTypeName($extra_type_name)
    {
        return $this->ExtraType->existsTypeName($extra_type_name);
    }

    /**
     * Create a new extra type
     *
     * @param array $data
     * @param integer $extra_type_id
     */
    public function createExtraType($data, &$extra_type_id)
    {
        $this->ExtraType->insert($data, $extra_type_id);
    }

    /**
     * Bind a extra type to the given category
     *
     * @param integer $extra_type_id
     * @param integer $category_type_id
     * @param integer reference $id
     */
    public function bindExtraTypeToCategory($extra_type_id, $category_type_id, &$id=null)
    {
        $this->ExtraCategory->insert($extra_type_id, $category_type_id, $id);
    }

    /**
     * Check if the given $tag_name already exists
     *
     * @param string $tag_name
     * @param integer $exclude_tag_id
     * @return boolean
     */
    public function existsTagName($tag_name, $exclude_tag_id=null)
    {
        return $this->ContactTag->existsTag($tag_name, $exclude_tag_id);
    }

    /**
     * Create a new Tag Type with the given name and description
     *
     * @param string $tag_name
     * @param string $tag_description
     * @param integer reference $tag_type_id
     * @return integer new tag type ID
     */
    public function createTagName($tag_name, $tag_description='', &$tag_type_id=-1)
    {
        return $this->ContactTag->createTag($tag_name, $tag_description, $tag_type_id);
    }

    /**
     * Get the Person ID for the given Contact ID
     *
     * @param integer $contact_id
     * @throws \Exception
     * @return integer Person ID
     */
    public function getPersonIDbyContactID($contact_id)
    {
        return $this->PersonData->getPersonIDbyContactID($contact_id);
    }

    /**
     * Insert a protocol entry. Set actual date and actual user if possible.
     *
     * @param integer $contact_id
     * @param string $protocol_text
     * @param string $protocol_date
     * @param string $protocol_originator
     * @param integer reference $protocol_id
     */
    public function addProtocolInfo($contact_id, $protocol_text, $protocol_date='0000-00-00 00:00:00', $protocol_originator='SYSTEM', &$protocol_id=-1)
    {
        $this->ProtocolData->addInfo($contact_id, $protocol_text, $protocol_date, $protocol_originator, $protocol_id);
    }

    /**
     * Set the specified tag for the contact with the given ID
     *
     * @param string $tag_name
     * @param integer $contact_id
     */
    public function setContactTag($tag_name, $contact_id)
    {
        $this->ContactTag->insert(array('tag_name' => $tag_name), $contact_id);
    }

    /**
     * Check if the tag name is already set for the contact ID
     *
     * @param string $tag_name
     * @param integer $contact_id
     */
    public function issetContactTag($tag_name, $contact_id)
    {
        return $this->ContactTag->issetContactTag($tag_name, $contact_id);
    }

    /**
     * Get the status for the given $login, where $login can be the
     * contact_login or the contact_id
     *
     * @param <string|integer> $login
     * @throws \Exception
     * @return Ambigous <boolean, string> FALSE or contact_status
     */
    public function getStatus($login)
    {
        return $this->ContactData->getStatus($login);
    }

}

