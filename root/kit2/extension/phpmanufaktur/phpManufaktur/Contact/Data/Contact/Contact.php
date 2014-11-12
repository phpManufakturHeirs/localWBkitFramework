<?php

/**
 * Contact
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de/Contact
 * @copyright 2013 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\Contact\Data\Contact;

use Silex\Application;

class Contact
{

    protected $app = null;
    protected static $table_name = null;
    protected $Person = null;
    protected $Company = null;
    protected $Note = null;
    protected $Communication = null;
    protected $Address = null;
    protected $Category = null;
    protected $Tag = null;
    protected $Extra = null;

    /**
     * Constructor
     *
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
        self::$table_name = FRAMEWORK_TABLE_PREFIX.'contact_contact';
        $this->Person = new Person($this->app);
        $this->Company = new Company($this->app);
        $this->Note = new Note($this->app);
        $this->Communication = new Communication($this->app);
        $this->Address = new Address($this->app);
        $this->Category = new Category($this->app);
        $this->Tag = new Tag($this->app);
        $this->Extra = new Extra($this->app);
    }

    /**
     * Create the CONTACT table
     *
     * @throws \Exception
     */
    public function createTable()
    {
        $table = self::$table_name;
        $SQL = <<<EOD
    CREATE TABLE IF NOT EXISTS `$table` (
        `contact_id` INT(11) NULL AUTO_INCREMENT,
        `contact_name` VARCHAR(128) NOT NULL DEFAULT '',
        `contact_login` VARCHAR(64) NOT NULL DEFAULT '',
        `contact_type` ENUM('PERSON', 'COMPANY') NOT NULL DEFAULT 'PERSON',
        `contact_since` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
        `contact_status` ENUM('ACTIVE', 'LOCKED', 'PENDING', 'DELETED') NOT NULL DEFAULT 'ACTIVE',
        `contact_timestamp` TIMESTAMP,
        PRIMARY KEY (`contact_id`),
        UNIQUE INDEX `contact_login` (`contact_login` ASC) ,
        INDEX `contact_name` (`contact_name` ASC) ,
        INDEX `contact_status` (`contact_status` ASC)
        )
    COMMENT='The main contact table'
    ENGINE=InnoDB
    AUTO_INCREMENT=1
    DEFAULT CHARSET=utf8
    COLLATE='utf8_general_ci'
EOD;
        try {
            $this->app['db']->query($SQL);
            $this->app['monolog']->addInfo("Created table 'contact_contact'", array(__METHOD__, __LINE__));
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Drop table - switching check for foreign keys off before executing
     *
     * @throws \Exception
     */
    public function dropTable()
    {
        try {
            $table = self::$table_name;
            $SQL = <<<EOD
    SET foreign_key_checks = 0;
    DROP TABLE IF EXISTS `$table`;
    SET foreign_key_checks = 1;
EOD;
            $this->app['db']->query($SQL);
            $this->app['monolog']->addInfo("Drop table 'contact_tag'", array(__METHOD__, __LINE__));
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Return the enum values of the field contact_type
     *
     * @throws \Exception
     * @return Ambigous <boolean, multitype:> array on success, false if fail
     */
    public function getContactTypes()
    {
        try {
            $SQL = "SELECT column_type FROM information_schema.columns WHERE table_name='".self::$table_name."' AND column_name='contact_type'";
            $result = $this->app['db']->fetchColumn($SQL);
            $result = explode("','", str_replace(array("enum('", "')", "''"), array('', '', "'"), $result));
            return (is_array($result)) ? $result : false;
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Select a contact record by the given contact_id
     * Return FALSE if the record does not exists
     *
     * @param integer $contact_id
     * @throws \Exception
     * @return multitype:array|boolean
     */
    public function select($contact_id)
    {
        try {
            $SQL = "SELECT * FROM `".self::$table_name."` WHERE `contact_id`='$contact_id'";
            $result = $this->app['db']->fetchAssoc($SQL);
            if (is_array($result) && isset($result['contact_id'])) {
                $contact = array();
                foreach ($result as $key => $value) {
                    $contact[$key] = is_string($value) ? $this->app['utils']->unsanitizeText($value) : $value;
                }
                return $contact;
            }
            else {
                return false;
            }
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Select a contact record by the given login name
     * Return FALSE if the record does not exists
     *
     * @param integer $contact_id
     * @throws \Exception
     * @return multitype:array|boolean
     */
    public function selectLogin($contact_login)
    {
        try {
            $SQL = "SELECT * FROM `".self::$table_name."` WHERE `contact_login`='$contact_login'";
            $result = $this->app['db']->fetchAssoc($SQL);
            if (is_array($result) && isset($result['contact_id'])) {
                $contact = array();
                foreach ($result as $key => $value) {
                    $contact[$key] = is_string($value) ? $this->app['utils']->unsanitizeText($value) : $value;
                }
                return $contact;
            }
            return false;
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Check if the desired contact login already existst. Optionally exclude the
     * given contact id from the check
     *
     * @param integer $contact_login
     * @param integer $exclude_contact_id
     * @throws \Exception
     * @return boolean|integer false if not exists otherwise the contact ID
     */
    public function existsLogin($contact_login, $exclude_contact_id=null)
    {
        try {
            $SQL = "SELECT `contact_id` FROM `".self::$table_name."` WHERE `contact_login`='$contact_login'";
            if (is_numeric($exclude_contact_id)) {
                $SQL .= " AND `contact_id` != '$exclude_contact_id'";
            }
            $result = $this->app['db']->fetchColumn($SQL);
            return (!is_null($result)) ? $result : false;
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Check if the desired contact name already existst. Optionally exclude the
     * given contact id from the check
     *
     * @param integer $contact_login
     * @param integer $exclude_contact_id
     * @throws \Exception
     * @return boolean
     */
    public function existsName($contact_name, $exclude_contact_id=null)
    {
        try {
            $SQL = "SELECT `contact_name` FROM `".self::$table_name."` WHERE `contact_name`='$contact_name'";
            if (is_numeric($exclude_contact_id)) {
                $SQL .= " AND `contact_id` != '$exclude_contact_id'";
            }
            $result = $this->app['db']->fetchColumn($SQL);
            return ($result == $contact_name) ? true : false;
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Insert a new record in the CONTACT table
     *
     * @param array $data
     * @param reference integer $contact_id
     * @throws \Exception
     */
    public function insert($data, &$contact_id=null)
    {
        try {
            $insert = array();
            foreach ($data as $key => $value) {
                if (($key == 'contact_id') || ($key == 'contact_timestamp')) continue;
                $insert[$key] = is_string($value) ? $this->app['utils']->sanitizeText($value) : $value;
            }
            if (!isset($insert['contact_since'])) {
                // add the 'contact_since' field with the actual date/time
                $insert['contact_since'] = date('Y-m-d H:i:s');
            }
            $this->app['db']->insert(self::$table_name, $insert);
            $contact_id = $this->app['db']->lastInsertId();
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Update the contact record with the given contact_id
     *
     * @param array $data
     * @param integer $contact_id
     * @throws \Exception
     */
    public function update($data, $contact_id)
    {
        try {
            $update = array();
            foreach ($data as $key => $value) {
                if (($key == 'contact_id') || ($key == 'contact_timestamp')) continue;
                $update[$this->app['db']->quoteIdentifier($key)] = is_string($value) ? $this->app['utils']->sanitizeText($value) : $value;
            }
            if (!empty($update)) {
                $this->app['db']->update(self::$table_name, $update, array('contact_id' => $contact_id));
            }
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Return a complete structured associative array for the contact with all
     * depending records and informations
     *
     * @param integer $contact_id
     * @param string $status can be ACTIVE, LOCKED or DELETED, default is DELETED
     * @param string $status_operator can be '=' or '!=', default is '!='
     * @param boolean $ignore_status set to true to select also DELETED records
     * @throws \Doctrine\DBAL\DBALException
     * @throws \Exception
     * @return array|boolean FALSE if SELECT return no result
     */
    public function selectContact($contact_id, $status='DELETED', $status_operator='!=', $ignore_status=false)
    {
        try {
            // first get the main contact record ...
            if ($ignore_status) {
                $SQL = "SELECT * FROM `".self::$table_name."` WHERE `contact_id`='{$contact_id}'";
            }
            else {
                $SQL = "SELECT * FROM `".self::$table_name."` WHERE `contact_id`='{$contact_id}' AND `contact_status`{$status_operator}'{$status}'";
            }
            $result = $this->app['db']->fetchAssoc($SQL);
            if (is_array($result) && isset($result['contact_id'])) {
                $contact = array();
                foreach ($result as $key => $value) {
                    $contact['contact'][$key] = is_string($value) ? $this->app['utils']->unsanitizeText($value) : $value;
                }
                // select the PERSON data record
                if ((false === ($contact['person'] = $this->Person->selectByContactID($contact_id, $status, $status_operator))) ||
                    empty($contact['person'])) {
                    $contact['person'] = array($this->Person->getDefaultRecord());
                }

                // select the COMPANY data record
                if ((false === ($contact['company'] = $this->Company->selectByContactID($contact_id, $status, $status_operator))) ||
                    empty($contact['company'])) {
                    $contact['company'] = array($this->Company->getDefaultRecord());
                }
                // add the communication entries
                if ((false === ($contact['communication'] = $this->Communication->selectByContactID($contact_id, $status, $status_operator))) ||
                    empty($contact['communication'])) {
                    $contact['communication'] = array($this->Communication->getDefaultRecord());
                }
                // add the addresses
                if ((false === ($contact['address'] = $this->Address->selectByContactID($contact_id, $status, $status_operator))) ||
                    empty($contact['address'])) {
                    $contact['address'] = array($this->Address->getDefaultRecord());
                }
                // add the NOTES
                if ((false === ($contact['note'] = $this->Note->selectByContactID($contact_id, $status, $status_operator))) ||
                    empty($contact['note'])) {
                    $contact['note'] = array();
                }
                // add the CATEGORIES
                if ((false === ($contact['category'] = $this->Category->selectByContactID($contact_id))) || empty($contact['category'])) {
                    $contact['category'] = array();
                }

                // add the EXTRA FIELDS
                if (isset($contact['category'][0]['category_id'])) {
                    if ((false === ($contact['extra_fields'] = $this->Extra->select($contact_id, $contact['category'][0]['category_id']))) ||
                        empty($contact['extra_fields'])) {
                        $contact['extra_fields'] = array();
                    }
                }
                else {
                    $contact['extra_fields'] = array();
                }

                // add the TAGS
                if ((false === ($contact['tag'] = $this->Tag->selectByContactID($contact_id))) || empty($contact['tag'])) {
                    $contact['tag'] = array();
                }

                // return the formatted contact array
                return $contact;
            }
            else {
                return false;
            }
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Return the contact TYPE for the given contact ID
     *
     * @param integer $contact_id
     * @throws \Exception
     */
    public function getContactType($contact_id)
    {
        try {
            $SQL = "SELECT `contact_type` FROM `".self::$table_name."` WHERE `contact_id`='$contact_id'";
            $result = $this->app['db']->fetchColumn($SQL);
            return (!empty($result)) ? $result : false;
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Get the primary ID for the given contact ID and and the type (PHONE, EMAIL, ADDRESS...)
     *
     * @param integer $contact_id
     * @param string $contact_type
     * @throws \Exception
     */
    protected function getPrimaryIDbyType($contact_id, $contact_type)
    {
        try {
            // first we need the contact type
            $type = $this->getContactType($contact_id);
            if ($type == 'PERSON') {
                return $this->Person->getPersonPrimaryContactTypeID($contact_id, $contact_type);
            }
            else {
                return $this->Company->getCompanyPrimaryContactTypeID($contact_id, $contact_type);
            }
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Set the primary ID for the given contact ID and and the type (PHONE, EMAIL, ADDRESS...)
     *
     * @param integer $contact_id
     * @param string $contact_type
     * @param integer $primary_id
     * @throws \Exception
     */
    protected function setPrimaryIDbyType($contact_id, $contact_type, $primary_id)
    {
        try {
            // first we need the contact type
            $type = $this->getContactType($contact_id);
            if ($type == 'PERSON') {
                $this->Person->setPersonPrimaryContactTypeID($contact_id, $contact_type, $primary_id);
            }
            else {
                $this->Company->setCompanyPrimaryContactTypeID($contact_id, $contact_type, $primary_id);
            }
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Return the primary address ID for the CONTACT ID
     *
     * @param integer $contact_id
     * @throws \Exception
     */
    public function getPrimaryAddressID($contact_id)
    {
        return $this->getPrimaryIDbyType($contact_id, 'ADDRESS');
    }

    /**
     * Set the primary address ID for the CONTACT ID
     *
     * @param integer $contact_id
     * @param integer $address_id
     * @throws \Exception
     */
    public function setPrimaryAddressID($contact_id, $address_id)
    {
        return $this->setPrimaryIDbyType($contact_id, 'ADDRESS', $address_id);
    }

    /**
     * Clear the primary address ID for the CONTACT ID and set it to -1
     *
     * @param integer $contact_id
     */
    public function clearPrimaryAddressID($contact_id)
    {
        return $this->setPrimaryIDbyType($contact_id, 'ADDRESS', -1);
    }

    /**
     * Return the primary NOTE ID for the CONTACT ID
     *
     * @param integer $contact_id
     * @throws \Exception
     */
    public function getPrimaryNoteID($contact_id)
    {
        return $this->getPrimaryIDbyType($contact_id, 'NOTE');
    }

    /**
     * Set the primary NOTE ID for the CONTACT ID
     *
     * @param integer $contact_id
     * @param integer $note_id
     * @throws \Exception
     */
    public function setPrimaryNoteID($contact_id, $note_id)
    {
        return $this->setPrimaryIDbyType($contact_id, 'NOTE', $note_id);
    }

    /**
     * Clear the primary NOTE ID for the CONTACT ID and set it to -1
     *
     * @param integer $contact_id
     */
    public function clearPrimaryNoteID($contact_id)
    {
        return $this->setPrimaryIDbyType($contact_id, 'NOTE', -1);
    }

    /**
     * Return the primary EMAIL ID for the CONTACT ID
     *
     * @param integer $contact_id
     * @throws \Exception
     */
    public function getPrimaryEmailID($contact_id)
    {
        return $this->getPrimaryIDbyType($contact_id, 'EMAIL');
    }

    /**
     * Return the primary EMAIL address for the given contact ID
     *
     * @param integer $contact_id
     */
    public function getPrimaryEmailAddress($contact_id)
    {
        $communication_id = $this->getPrimaryEmailID($contact_id);
        return $this->Communication->selectValue($communication_id);
    }

    /**
     * Set the primary EMAIL ID for the CONTACT ID
     *
     * @param integer $contact_id
     * @param integer $email_id
     * @throws \Exception
     */
    public function setPrimaryEmailID($contact_id, $email_id)
    {
        return $this->setPrimaryIDbyType($contact_id, 'EMAIL', $email_id);
    }

    /**
     * Clear the primary EMAIL ID for the CONTACT ID and set it to -1
     *
     * @param integer $contact_id
     */
    public function clearPrimaryEmailID($contact_id)
    {
        return $this->setPrimaryIDbyType($contact_id, 'EMAIL', -1);
    }

    /**
     * Return the primary PHONE ID for the CONTACT ID
     *
     * @param integer $contact_id
     * @throws \Exception
     */
    public function getPrimaryPhoneID($contact_id)
    {
        return $this->getPrimaryIDbyType($contact_id, 'PHONE');
    }

    /**
     * Set the primary PHONE ID for the CONTACT ID
     *
     * @param integer $contact_id
     * @param integer $phone_id
     * @throws \Exception
     */
    public function setPrimaryPhoneID($contact_id, $phone_id)
    {
        return $this->setPrimaryIDbyType($contact_id, 'PHONE', $phone_id);
    }

    /**
     * Clear the primary PHONE ID for the CONTACT ID and set it to -1
     *
     * @param integer $contact_id
     */
    public function clearPrimaryPhoneID($contact_id)
    {
        return $this->setPrimaryIDbyType($contact_id, 'PHONE', -1);
    }

    public function selectAll()
    {
        try {
            return $this->app['db']->fetchAll("SELECT * FROM `".self::$table_name."` ORDER BY `contact_id` ASC");
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    protected function array_values_recursive($ary){
        $lst = array();
        foreach( array_keys($ary) as $k ){
            $v = $ary[$k];
            if (is_scalar($v)) {
                $lst[] = $v;
            } elseif (is_array($v)) {
                $lst = array_merge($lst, $this->array_values_recursive($v));
            }
        }
        return $lst;
    }

    /**
     * Get the status for the given $login, where $login can be the contact_login
     * or the contact_id
     *
     * @param <string|integer> $login
     * @throws \Exception
     * @return Ambigous <boolean, string> FALSE or contact_status
     */
    public function getStatus($login)
    {
        try {
            if (is_numeric($login)) {
                $SQL = "SELECT `contact_status` FROM `".self::$table_name."` WHERE `contact_id`='$login'";
            }
            else {
                $SQL = "SELECT `contact_status` FROM `".self::$table_name."` WHERE `contact_login`='$login'";
            }
            return (null === ($status = $this->app['db']->fetchColumn($SQL))) ? false : $status;
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Create a list of CONTACT NAMES for use with form.factory / Twig
     *
     * @param array $tags optional, select only contacts with these tags
     * @param array $status select contacts in contact_status
     * @throws \Exception
     * @return Ambigous <boolean, array >
     */
    public function selectContactIdentifiersForSelect($no_contact_at_top=true, $tags=array(), $status=array('ACTIVE'))
    {
        try {
            $contact = self::$table_name;
            $tag_table = FRAMEWORK_TABLE_PREFIX.'contact_tag';

            $in_status = "('".implode("','", $status)."')";

            if (is_array($tags) && !empty($tags)) {
                $in_tags = "('".implode("','", $tags)."')";
                $SQL = "SELECT `$contact`.`contact_id`, `contact_name` FROM `$contact` ".
                    "LEFT JOIN `$tag_table` ON `$tag_table`.`contact_id`=`$contact`.`contact_id` ".
                    "WHERE `$tag_table`.`tag_name` IN $in_tags AND `contact_status` IN $in_status ".
                    "ORDER BY `contact_name` ASC";

            }
            else {
                $SQL = "SELECT `contact_id`,`contact_name` FROM `$contact` WHERE `contact_status` IN $in_status "
                    ."ORDER BY `contact_name` ASC";
            }

            $results = $this->app['db']->fetchAll($SQL);

            $select = array();
            if ($no_contact_at_top) {
                $select[-1] = '- no contact selected -';
            }
            if (is_array($results)) {
                foreach ($results as $result) {
                    $select[$result['contact_id']] = $this->app['utils']->unsanitizeText($result['contact_name']);
                }

            }
            return (!empty($select)) ? $select : false;
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Get the category access type for the given contact ID
     *
     * @param integer $contact_id
     */
    public function getAccessType($contact_id)
    {
        try {
            $category_type = FRAMEWORK_TABLE_PREFIX.'contact_category_type';
            $category = FRAMEWORK_TABLE_PREFIX.'contact_category';
            $SQL = "SELECT `category_type_access` FROM `$category_type` ".
                "LEFT JOIN `$category` ON `$category`.`category_type_id`=`$category_type`.`category_type_id` ".
                "WHERE `contact_id`=$contact_id";
            $result = $this->app['db']->fetchColumn($SQL);
            return (is_string($result)) ? $result : 'ADMIN';
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Check if the given contact ID is a PUBLIC accessible contact record
     *
     * @param integer $contact_id
     */
    public function isPublic($contact_id)
    {
        $access = $this->getAccessType($contact_id);
        return ($access == 'PUBLIC');
    }

    /**
     * Check if the contact record for the given Contact ID is ACTIVE
     *
     * @param integer $contact_id
     * @throws \Exception
     * @return boolean
     */
    public function isActive($contact_id)
    {
        try {
            $SQL = "SELECT `contact_status` FROM `".self::$table_name."` WHERE `contact_id`=$contact_id";
            $status = $this->app['db']->fetchColumn($SQL);
            return ($status == 'ACTIVE');
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Get the contact ID for the given contact login
     *
     * @param string $contact_login
     * @throws \Exception
     * @return Ambigous <boolean, integer>
     */
    public function getContactID($contact_login)
    {
        try {
            $SQL = "SELECT `contact_id` FROM `".self::$table_name."` WHERE `contact_login`='$contact_login'";
            $id = $this->app['db']->fetchColumn($SQL);
            return ($id > 0) ? $id : false;
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Search for public contacts in all relevant data fields.
     *
     * @param string $search_term
     * @param array $tags optional, search only for records with the desired tags
     * @param array $categories optional, search only for records with the desired categories
     * @throws \Exception
     * @return Ambigous <boolean, array>
     */
    public function searchPublicContact($search_term, $tags=array(), $categories=array())
    {
        try {
            $t_overview = FRAMEWORK_TABLE_PREFIX.'contact_overview';
            $t_extra = FRAMEWORK_TABLE_PREFIX.'contact_extra';
            $t_communication = FRAMEWORK_TABLE_PREFIX.'contact_communication';
            $t_address = FRAMEWORK_TABLE_PREFIX.'contact_address';
            $t_company = FRAMEWORK_TABLE_PREFIX.'contact_company';
            $t_person = FRAMEWORK_TABLE_PREFIX.'contact_person';
            $t_note = FRAMEWORK_TABLE_PREFIX.'contact_note';
            $t_tag = FRAMEWORK_TABLE_PREFIX.'contact_tag';
            $t_category = FRAMEWORK_TABLE_PREFIX.'contact_category';
            // base SQL - access only PUBLIC and ACTIVE contacts
            $SQL = "SELECT * FROM `$t_overview` ".
                "LEFT JOIN `$t_extra` ON `$t_extra`.`contact_id`=`$t_overview`.`contact_id` ".
                "LEFT JOIN `$t_communication` ON `$t_communication`.`contact_id`=`$t_overview`.`contact_id` ".
                "LEFT JOIN `$t_address` ON `$t_address`.`contact_id`=`$t_overview`.`contact_id` ".
                "LEFT JOIN `$t_company` ON `$t_company`.`contact_id`=`$t_overview`.`contact_id` ".
                "LEFT JOIN `$t_person` ON `$t_person`.`contact_id`=`$t_overview`.`contact_id` ".
                "LEFT JOIN `$t_note` ON `$t_note`.`contact_id`=`$t_overview`.`contact_id` ".
                "LEFT JOIN `$t_tag` ON `$t_tag`.`contact_id`=`$t_overview`.`contact_id` ".
                "LEFT JOIN `$t_category` ON `$t_category`.`contact_id`=`$t_overview`.`contact_id` ".
                "WHERE `$t_overview`.`category_access`='PUBLIC' AND `$t_overview`.`contact_status`='ACTIVE' AND (";

            $search = trim($search_term);
            $search_array = array();
            if (strpos($search, ' ')) {
                $dummy = explode(' ', $search_term);
                foreach ($dummy as $item) {
                    $search_array[] = trim($item);
                }
            }
            else {
                $search_array[] = trim($search_term);
            }
            $start = true;
            $skipped = false;
            foreach ($search_array as $search) {
                if (!$skipped) {
                    if ($start) {
                        $SQL .= "(";
                        $start = false;
                    }
                    elseif (strtoupper($search) == 'AND') {
                        $SQL .= ") AND (";
                        $skipped = true;
                        continue;
                    }
                    elseif (strtoupper($search) == 'NOT') {
                        $SQL .= ") AND NOT (";
                        $skipped = true;
                        continue;
                    }
                    elseif (strtoupper($search) == 'OR') {
                        $SQL .= ") OR (";
                        $skipped = true;
                        continue;
                    }
                    else {
                        $SQL .= ") OR (";
                    }
                }
                else {
                    $skipped = false;
                }
                $SQL .= "`contact_name` LIKE '%$search%' OR ".
                    "(`$t_person`.`person_gender` LIKE '%$search%' AND `$t_person`.`person_status`='ACTIVE') OR ".
                    "(`$t_person`.`person_title` LIKE '%$search%' AND `$t_person`.`person_status`='ACTIVE') OR ".
                    "(`$t_person`.`person_first_name` LIKE '%$search%' AND `$t_person`.`person_status`='ACTIVE') OR ".
                    "(`$t_person`.`person_last_name` LIKE '%$search%' AND `$t_person`.`person_status`='ACTIVE') OR ".
                    "(`$t_person`.`person_nick_name` LIKE '%$search%' AND `$t_person`.`person_status`='ACTIVE') OR ".
                    "(`$t_person`.`person_primary_note_id`=`$t_note`.`note_id` AND `$t_note`.`note_content` LIKE '%$search%' AND `$t_note`.`note_status`='ACTIVE') OR ".
                    "(`$t_company`.`company_name` LIKE '%$search%' AND `$t_company`.`company_status`='ACTIVE') OR ".
                    "(`$t_company`.`company_department` LIKE '%$search%' AND `$t_company`.`company_status`='ACTIVE') OR ".
                    "(`$t_company`.`company_additional` LIKE '%$search%' AND `$t_company`.`company_status`='ACTIVE') OR ".
                    "(`$t_company`.`company_additional_2` LIKE '%$search%' AND `$t_company`.`company_status`='ACTIVE') OR ".
                    "(`$t_company`.`company_additional_3` LIKE '%$search%' AND `$t_company`.`company_status`='ACTIVE') OR ".
                    "(`$t_company`.`company_primary_note_id`=`$t_note`.`note_id` AND `$t_note`.`note_content` LIKE '%$search%' AND `$t_note`.`note_status`='ACTIVE') OR ".
                    "(`$t_communication`.`communication_value` LIKE '%$search%' AND `$t_communication`.`communication_status`='ACTIVE') OR ".
                    "(`$t_address`.`address_identifier` LIKE '%$search%' AND `$t_address`.`address_status`='ACTIVE') OR ".
                    "(`$t_address`.`address_description` LIKE '%$search%' AND `$t_address`.`address_status`='ACTIVE') OR ".
                    "(`$t_address`.`address_street` LIKE '%$search%' AND `$t_address`.`address_status`='ACTIVE') OR ".
                    "(`$t_address`.`address_appendix_1` LIKE '%$search%' AND `$t_address`.`address_status`='ACTIVE') OR ".
                    "(`$t_address`.`address_appendix_2` LIKE '%$search%' AND `$t_address`.`address_status`='ACTIVE') OR ".
                    "(`$t_address`.`address_city` LIKE '%$search%' AND `$t_address`.`address_status`='ACTIVE') OR ".
                    "(`$t_address`.`address_zip` LIKE '$search%' AND `$t_address`.`address_status`='ACTIVE') OR ".
                    "(`$t_address`.`address_area` LIKE '%$search%' AND `$t_address`.`address_status`='ACTIVE') OR ".
                    "(`$t_address`.`address_state` LIKE '%$search%' AND `$t_address`.`address_status`='ACTIVE') OR ".
                    "(`$t_address`.`address_country_code` LIKE '%$search%' AND `$t_address`.`address_status`='ACTIVE') OR ".
                    "(`$t_extra`.`contact_id`=`$t_overview`.`contact_id` AND `$t_extra`.`extra_text` LIKE '%$search%') OR ".
                    "(`$t_extra`.`contact_id`=`$t_overview`.`contact_id` AND `$t_extra`.`extra_html` LIKE '%$search%') OR ".
                    "(`$t_extra`.`contact_id`=`$t_overview`.`contact_id` AND `$t_extra`.`extra_varchar` LIKE '%$search%') OR ".
                    "(`$t_extra`.`contact_id`=`$t_overview`.`contact_id` AND `$t_extra`.`extra_date` LIKE '%$search%') OR ".
                    "(`$t_extra`.`contact_id`=`$t_overview`.`contact_id` AND `$t_extra`.`extra_datetime` LIKE '%$search%') OR ".
                    "(`$t_extra`.`contact_id`=`$t_overview`.`contact_id` AND `$t_extra`.`extra_time` LIKE '%$search%')";
            }
            $SQL .= ")) ";

            if (is_array($tags) && !empty($tags)) {
                $SQL .= " AND (";
                $start = true;
                foreach ($tags as $tag) {
                    if (!$start) {
                        $SQL .= " OR ";
                    }
                    $SQL .= "(`$t_tag`.`tag_name`='$tag')";
                    $start = false;
                }
                $SQL .= ") ";
            }

            if (is_array($categories) && !empty($categories)) {
                $SQL .= " AND (";
                $start = true;
                foreach ($categories as $category) {
                    if (!$start) {
                        $SQL .= " OR ";
                    }
                    $SQL .= "(`$t_category`.`category_type_name`='$category')";
                    $start = false;
                }
                $SQL .= ") ";
            }

            $SQL .= "GROUP BY `$t_overview`.`contact_id` ORDER BY `$t_overview`.`order_name` ASC";

            $results = $this->app['db']->fetchAll($SQL);

            $contacts = array();
            foreach ($results as $key => $value) {
                $contacts[$key] = is_string($value) ? $this->app['utils']->unsanitizeText($value) : $value;
            }
            return (!empty($contacts)) ? $contacts : false;
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }
}
