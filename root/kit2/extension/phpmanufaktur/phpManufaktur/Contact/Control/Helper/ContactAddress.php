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
use phpManufaktur\Contact\Control\Helper\ContactParent;
use phpManufaktur\Contact\Data\Contact\Address;
use phpManufaktur\Contact\Data\Contact\Country;
use phpManufaktur\Contact\Data\Contact\Contact as ContactData;
use phpManufaktur\Contact\Data\Contact\Person;

class ContactAddress extends ContactParent
{
    protected $Address = null;
    protected $Country = null;
    protected $Contact = null;
    protected $Person = null;

    /**
     * Constructor
     *
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        parent::__construct($app);
        $this->Address = new Address($this->app);
        $this->Country = new Country($this->app);
        $this->Contact = new ContactData($this->app);
        $this->Person = new Person($this->app);
    }

    /**
     * Get a default (empty) address record
     *
     * @return array
     */
    public function getDefaultRecord()
    {
        return $this->Address->getDefaultRecord();
    }

    /**
     * Validate the given ADDRESS
     *
     * @param reference array $address_data
     * @param array $contact_data
     * @param array $option
     * @return boolean
     */
    public function validate(&$address_data, $contact_data=array(), $option=array())
    {
        if (!isset($address_data['address_id']) || !is_numeric($address_data['address_id'])) {
            $this->setMessage("Missing the %identifier%! The ID should be set to -1 if you insert a new record.",
                array('%identifier%' => 'address_id'));
            return false;
        }

        // check if any value is NULL
        foreach ($address_data as $key => $value) {
            if (is_null($value)) {
                switch ($key) {
                    case 'contact_id':
                        $address_data[$key] = -1;
                        break;
                    case 'address_type':
                        $address_data[$key] = 'OTHER';
                        break;
                    case 'address_identifier':
                    case 'address_description':
                    case 'address_street':
                    case 'address_appendix_1':
                    case 'address_appendix_2':
                    case 'address_zip':
                    case 'address_city':
                    case 'address_area':
                    case 'address_state':
                    case 'address_country_code':
                        $address_data[$key] = '';
                        break;
                    case 'address_status':
                        $address_data[$key] = 'ACTIVE';
                        break;
                    default:
                        throw new ContactException("The key $key is not defined!");
                        break;
                }
            }
        }

        if ((isset($address_data['address_street']) && (!empty($address_data['address_street']))) ||
            (isset($address_data['address_city']) && !empty($address_data['address_city'])) ||
            (isset($address_data['address_zip']) && !empty($address_data['address_zip']))) {
            // passed the minimum requirements, go ahead with all other checks

            if (isset($address_data['address_country_code']) && !empty($address_data['address_country_code'])) {
                $address_data['address_country_code'] = strtoupper(trim($address_data['address_country_code']));
                if (!$this->Country->existsCountryCode($address_data['address_country_code'])) {
                    $this->setMessage('The country code %country_code% does not exists!',
                        array('%country_code%' => $address_data['address_country_code']));
                    return false;
                }
            }

            if (isset($address_data['address_country_code']) && ($address_data['address_country_code'] == 'DE') &&
                isset($address_data['address_zip']) && !empty($address_data['address_zip'])) {
                // check the german ZIP code
                if (!preg_match('/^(?!01000|99999)(0[1-9]\d{3}|[1-9]\d{4})$/', $address_data['address_zip'])) {
                    $this->setMessage('The zip %zip% is not valid!', array('%zip%' => $address_data['address_zip']));
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * Insert a ADDRESS record
     *
     * @param array $data
     * @param integer $contact_id
     * @param reference integer $address_id
     * @param reference boolean $has_inserted
     * @return boolean
     */
    public function insert($data, $contact_id, &$address_id=null, &$has_inserted=null)
    {
        // enshure that the contact_id isset
        $data['contact_id'] = $contact_id;
        $has_inserted = false;

        if (!$this->validate($data)) {
           return false;
        }
        if ((isset($data['address_street']) && (!empty($data['address_street']))) ||
            (isset($data['address_city']) && !empty($data['address_city'])) ||
            (isset($data['address_zip']) && !empty($data['address_zip']))) {

            // insert only, if street, city or zip isset
            $this->Address->insert($data, $address_id);
            $has_inserted = true;
            $this->app['monolog']->addDebug("Insert address ID $address_id for contact ID $contact_id");

            // check if a primary address isset for the contact
            if ($this->Contact->getPrimaryAddressID($contact_id) < 1) {
                // set the primary address
                $this->Contact->setPrimaryAddressID($contact_id, $address_id);
                $this->app['monolog']->addDebug("Set address ID $address_id as primary address for the contact ID $contact_id");
            }
        }
        else {
            // nothing to do
            $this->app['monolog']->addDebug("Skipped ADDRESS insert because no street, zip or city isset.");
        }
        return true;
    }

    /**
     * Process the update for the given address record
     *
     * @param array $new_data the changed address
     * @param array $old_data the existing address from database
     * @param integer $address_id
     * @param reference boolean $has_changed set to true if record has changed
     * @return boolean
     */
    public function update($new_data, $old_data, $address_id, &$has_changed=false)
    {
        $has_changed = false;

        if ((!isset($new_data['address_street']) || empty($new_data['address_street'])) &&
            (!isset($new_data['address_zip']) || empty($new_data['address_zip'])) &&
            (!isset($new_data['address_city']) || empty($new_data['address_city']))) {
            // check if this address can be deleted

            if ($this->Contact->getPrimaryAddressID($old_data['contact_id']) == $address_id) {
                $this->setMessage("Can't delete the Adress with the ID %address_id% because it is used as primary address.",
                    array('%address_id%' => $address_id));
                return false;
            }
            else {
                // delete the address
                $this->Address->delete($address_id);
                $this->setMessage("The Address with the ID %address_id% was successfull deleted.",
                    array('%address_id%' => $address_id));
                $has_changed = true;
                return true;
            }
        }

        // now we can validate the address
        if (!$this->validate($new_data)) {
            return false;
        }

        // process the new data
        $changed = array();
        foreach ($new_data as $key => $value) {
            if (($key == 'address_id') || ($key == 'address_timestamp')) continue;
            if (isset($old_data[$key]) && ($old_data[$key] != $value)) {
                $changed[$key] = $value;
            }
        }

        if (!empty($changed)) {
            // update the communication record
            $this->Address->update($changed, $address_id);
            $has_changed = true;
        }
        return true;
    }

}
