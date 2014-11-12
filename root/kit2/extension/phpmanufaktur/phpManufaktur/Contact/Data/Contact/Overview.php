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

class Overview
{

    protected $app = null;
    protected static $table_name = null;
    protected $Contact = null;
    protected $Address = null;
    protected $Company = null;
    protected $Person = null;
    protected $Communication = null;


    /**
     * Constructor
     *
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
        self::$table_name = FRAMEWORK_TABLE_PREFIX.'contact_overview';
        $this->Contact = new Contact($app);
        $this->Address = new Address($app);
        $this->Company = new Company($app);
        $this->Person = new Person($app);
        $this->Communication = new Communication($app);
    }

    /**
     * Create the OVERVIEW table
     *
     * @throws \Exception
     */
    public function createTable()
    {
        $table = self::$table_name;
        $table_contact = FRAMEWORK_TABLE_PREFIX.'contact_contact';
        $foreign_key_1 = self::$table_name.'_ibfk_1';
        $foreign_key_2 = self::$table_name.'_ibfk_2';
        $foreign_key_3 = self::$table_name.'_ibfk_3';

        $SQL = <<<EOD
    CREATE TABLE IF NOT EXISTS `$table` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `contact_id` INT(11) NOT NULL DEFAULT '-1',
        `contact_login` VARCHAR(64) NOT NULL DEFAULT '',
        `contact_name` VARCHAR(128) NOT NULL DEFAULT '',
        `contact_type` ENUM('PERSON','COMPANY') NOT NULL DEFAULT 'PERSON',
        `contact_status` ENUM('ACTIVE','LOCKED','PENDING','DELETED'),
        `person_id` INT(11) NOT NULL DEFAULT '-1',
        `person_gender` ENUM('MALE','FEMALE') NOT NULL DEFAULT 'MALE',
        `person_title` VARCHAR(32) NOT NULL DEFAULT '',
        `person_first_name` VARCHAR(128) NOT NULL DEFAULT '',
        `person_last_name` VARCHAR(128) NOT NULL DEFAULT '',
        `person_nick_name` VARCHAR(128) NOT NULL DEFAULT '',
        `person_birthday` DATE NOT NULL DEFAULT '0000-00-00',
        `company_id` INT(11) NOT NULL DEFAULT '-1',
        `company_name` VARCHAR(128) NOT NULL DEFAULT '',
        `company_department` VARCHAR(128) NOT NULL DEFAULT '',
        `communication_phone` VARCHAR(255) NOT NULL DEFAULT '',
        `communication_email` VARCHAR(255) NOT NULL DEFAULT '',
        `address_id` INT(11) NOT NULL DEFAULT '-1',
        `address_street` VARCHAR(128) NOT NULL DEFAULT '',
        `address_zip` VARCHAR(32) NOT NULL DEFAULT '',
        `address_city` VARCHAR(128) NOT NULL DEFAULT '',
        `address_area` VARCHAR(128) NOT NULL DEFAULT '',
        `address_state` VARCHAR(128) NOT NULL DEFAULT '',
        `address_country_code` VARCHAR(8) NOT NULL DEFAULT '',
        `category_id` INT(11) NOT NULL DEFAULT -1,
        `category_name` VARCHAR(64) NOT NULL DEFAULT '',
        `category_access` ENUM('ADMIN','PUBLIC') NOT NULL DEFAULT 'ADMIN',
        `tags` VARCHAR(512) NOT NULL DEFAULT '',
        `order_name` VARCHAR(256) NOT NULL DEFAULT '',
        `timestamp` TIMESTAMP,
        PRIMARY KEY (`id`),
        INDEX `contact_id_idx` (`contact_id` ASC) ,
        INDEX `contact_name_idx` (`contact_name` ASC) ,
        INDEX `contact_status_idx` (`contact_status` ASC) ,
        CONSTRAINT `$foreign_key_1`
          FOREIGN KEY (`contact_id` )
          REFERENCES `$table_contact` (`contact_id` )
          ON DELETE CASCADE,
        CONSTRAINT `$foreign_key_2`
          FOREIGN KEY (`contact_name` )
          REFERENCES `$table_contact` (`contact_name` )
          ON DELETE CASCADE
          ON UPDATE CASCADE
        )
    COMMENT='Summary/Overview over all contacts'
    ENGINE=InnoDB
    AUTO_INCREMENT=1
    DEFAULT CHARSET=utf8
    COLLATE='utf8_general_ci'
EOD;
        try {
            $this->app['db']->query($SQL);
            $this->app['monolog']->addInfo("Created table 'contact_overview'", array(__METHOD__, __LINE__));
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
     * Refresh insert and update contact records into the overview table.
     * This function should be called each time a contact is inserted or updated.
     *
     * @param integer $contact_id
     * @throws \Exception
     */
    public function refresh($contact_id)
    {
        try {
            // get the contact block
            $contact = $this->Contact->select($contact_id);

            if ($contact['contact_type'] == 'COMPANY') {
                // get the company
                $company = $this->Company->selectByContactID($contact_id);
                $company = $company[0];

                if ($company['company_primary_address_id'] > 0) {
                    $address = $this->Address->select($company['company_primary_address_id']);
                }

                if ($company['company_primary_email_id'] > 0) {
                    $email = $this->Communication->selectValue($company['company_primary_email_id']);
                }

                if ($company['company_primary_phone_id'] > 0) {
                    $phone = $this->Communication->selectValue($company['company_primary_phone_id']);
                }
            }
            else {
                // get the person
                $person = $this->Person->selectByContactID($contact_id);
                $person = $person[0];

                if ($person['person_primary_address_id'] > 0) {
                    $address = $this->Address->select($person['person_primary_address_id']);
                }

                if ($person['person_primary_email_id'] > 0) {
                    $email = $this->Communication->selectValue($person['person_primary_email_id']);
                }

                if ($person['person_primary_phone_id'] > 0) {
                    $phone = $this->Communication->selectValue($person['person_primary_phone_id']);
                }
            }

            // select the TAGS
            $SQL = "SELECT `tag_name` FROM `".FRAMEWORK_TABLE_PREFIX."contact_tag` WHERE `contact_id`='$contact_id'";
            $tags = array();
            $tags_result = $this->app['db']->fetchAll($SQL);
            foreach ($tags_result as $tag) {
                $tags[] = $tag['tag_name'];
            }

            // build a field for easy ordering the contacts, independent from type
            if ($contact['contact_type'] == 'PERSON') {
                if (!empty($person['person_last_name'])) {
                    $order_name = $person['person_last_name'];
                    if (!empty($person['person_first_name'])) {
                        $order_name .= ', '.$person['person_first_name'];
                    }
                }
                else {
                    $order_name = $contact['contact_name'];
                }
            }
            else {
                if (!empty($company['company_name'])) {
                    $order_name = $company['company_name'];
                    if (!empty($company['company_department'])) {
                        $order_name .= ', '.$company['company_department'];
                    }
                }
                else {
                    $order_name = $contact['contact_name'];
                }
            }

            // get the category information
            $SQL = "SELECT `category_type_id` FROM `".FRAMEWORK_TABLE_PREFIX."contact_category` WHERE `contact_id`=$contact_id";
            $result = $this->app['db']->fetchColumn($SQL);
            $category_type_id = (is_numeric($result) && ($result > 0)) ? $result : -1;
            $category_type_name = '';
            $category_type_access = 'ADMIN';

            if ($category_type_id > 0) {
                $SQL = "SELECT * FROM `".FRAMEWORK_TABLE_PREFIX."contact_category_type` WHERE `category_type_id`=$category_type_id";
                $result = $this->app['db']->fetchAssoc($SQL);
                if (is_array($result)) {
                    $category_type_name = $result['category_type_name'];
                    $category_type_access = $result['category_type_access'];
                }
            }

            $record = array(
                'contact_id' => $contact_id,
                'contact_login' => $contact['contact_login'],
                'contact_name' => $contact['contact_name'],
                'contact_type' => $contact['contact_type'],
                'contact_status' => $contact['contact_status'],
                'person_id' => isset($person['person_id']) ? $person['person_id'] : -1,
                'person_first_name' => isset($person['person_first_name']) ? $person['person_first_name'] : '',
                'person_last_name' => isset($person['person_last_name']) ? $person['person_last_name'] : '',
                'person_birthday' => isset($person['person_birthday']) ? $person['person_birthday'] : '0000-00-00',
                'person_title' => isset($person['person_title']) ? $person['person_title'] : '',
                'person_gender' => isset($person['person_gender']) ? $person['person_gender'] : 'MALE',
                'company_id' => isset($company['company_id']) ? $company['company_id'] : -1,
                'company_name' => isset($company['company_name']) ? $company['company_name'] : '',
                'company_department' => isset($company['company_department']) ? $company['company_department'] : '',
                'communication_phone' => isset($phone) ? $phone : '',
                'communication_email' => isset($email) ? $email : '',
                'address_id' => isset($address['address_id']) ? $address['address_id'] : -1,
                'address_street' => isset($address['address_street']) ? $address['address_street'] : '',
                'address_city' => isset($address['address_city']) ? $address['address_city'] : '',
                'address_zip' => isset($address['address_zip']) ? $address['address_zip'] : '',
                'address_area' => isset($address['address_area']) ? $address['address_area'] : '',
                'address_state' => isset($address['address_state']) ? $address['address_state'] : '',
                'address_country_code' => isset($address['address_country_code']) ? $address['address_country_code'] : '',
                'order_name' => $order_name,
                'tags' => implode(',', $tags),
                'category_id' => $category_type_id,
                'category_name' => $category_type_name,
                'category_access' => $category_type_access
            );

            // prepare the data record
            $data = array();
            foreach ($record as $key => $value) {
                $data[$this->app['db']->quoteIdentifier($key)] = is_string($value) ? $this->app['utils']->sanitizeText($value) : $value;
            }

            $SQL = "SELECT `contact_id` FROM `".self::$table_name."` WHERE `contact_id`='$contact_id'";
            if (($check = $this->app['db']->fetchColumn($SQL)) == $contact_id) {
                // update the overview
                $this->app['db']->update(self::$table_name, $data, array('contact_id' => $contact_id));
            }
            else {
                // insert a new record
                $this->app['db']->insert(self::$table_name, $data);
            }
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Select all items of the overview sorted by contact_id in ascending order
     *
     * @return array overview list with all items
     * @throws \Exception
     */
    public function selectAll()
    {
        try {
            $results = $this->app['db']->fetchAll("SELECT * FROM `".self::$table_name."` ORDER BY `contact_id` ASC");
            $overviews = array();
            foreach ($results as $result) {
                $overview = array();
                foreach ($result as $key => $value) {
                    $overview[$key] = is_string($value) ? $this->app['utils']->unsanitizeText($value) : $value;
                }
                $overviews[] = $overview;
            }
            return (!empty($overviews)) ? $overviews : false;
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Select the overview record for the given $contact_id
     *
     * @param integer $contact_id
     * @throws \Exception
     * @return Ambigous <boolean,array> false or overview record
     */
    public function select($contact_id)
    {
        try {
            if (filter_var($contact_id, FILTER_VALIDATE_INT)) {
                $SQL = "SELECT * FROM `".self::$table_name."` WHERE `contact_id` = '$contact_id'";
            }
            else {
                $SQL = "SELECT * FROM `".self::$table_name."` WHERE `contact_login` = '$contact_id'";
            }
            $result = $this->app['db']->fetchAssoc($SQL);
            $overview = array();
            foreach ($result as $key => $value) {
                $overview[$key] = is_string($value) ? $this->app['utils']->unsanitizeText($value) : $value;
            }
            return (!empty($overview)) ? $overview : false;
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Select the overview record for the given contact login
     *
     * @param string $login
     * @throws \Exception
     * @return Ambigous <boolean, array> false or overview record
     */
    public function selectLogin($login)
    {
        try {
            $SQL = "SELECT * FROM `".self::$table_name."` WHERE `contact_login` = '$login'";
            $result = $this->app['db']->fetchAssoc($SQL);
            $overview = array();
            foreach ($result as $key => $value) {
                $overview[$key] = is_string($value) ? $this->app['utils']->unsanitizeText($value) : $value;
            }
            return (!empty($overview)) ? $overview : false;
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }
    /**
     * Rebuild the complete overview table
     *
     * @throws \Exception
     */
    public function rebuildOverview()
    {
        try {
            $contact = new Contact($this->app);
            $contacts = $contact->selectAll();
            foreach ($contacts as $contact) {
                $this->refresh($contact['contact_id']);
            }
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Return the column names of the overview table
     *
     * @throws \Exception
     * @return multitype:unknown
     */
    public function getColumns()
    {
        try {
            $result = $this->app['db']->fetchAll("SHOW COLUMNS FROM `".self::$table_name."`");
            $columns = array();
            foreach ($result as $column) {
                $columns[] = $column['Field'];
            }
            return $columns;
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Count the records in the table Overview
     *
     * @param array $status flags, i.e. array('ACTIVE','LOCKED')
     * @param array $type flags, i.e. array('PERSON')
     * @throws \Exception
     * @return integer number of records
     */
    public function count($status=null, $type=null)
    {
        try {
            $SQL = "SELECT COUNT(*) FROM `".self::$table_name."`";
            if ((is_array($status) && !empty($status)) || (is_array($type) && !empty($type))) {
                $SQL .= " WHERE (";
                $use_status = false;
                if (is_array($status) && !empty($status)) {
                    $use_status = true;
                    $SQL .= '(';
                    $start = true;
                    foreach ($status as $stat) {
                        if (!$start) {
                            $SQL .= " OR ";
                        }
                        else {
                            $start = false;
                        }
                        $SQL .= "`contact_status`='$stat'";
                    }
                    $SQL .= ')';
                }
                if (is_array($type) && !empty($type)) {
                    if ($use_status) {
                        $SQL .= ' AND ';
                    }
                    $SQL .= '(';
                    $start = true;
                    foreach ($type as $typ) {
                        if (!$start) {
                            $SQL .= " OR ";
                        }
                        else {
                            $start = false;
                        }
                        $SQL .= "`contact_type`='$typ'";
                    }
                    $SQL .= ')';
                }
                $SQL .= ")";
            }
            return $this->app['db']->fetchColumn($SQL);
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Select a list from table Overview in paging view
     *
     * @param integer $limit_from start selection at position
     * @param integer $rows_per_page select max. rows per page
     * @param array $select_status tags, i.e. array('ACTIVE','LOCKED')
     * @param array $order_by fields to order by
     * @param string $order_direction 'ASC' (default) or 'DESC'
     * @param array $select_type tags. i.e. array('PERSON') to limit to PERSON
     * @throws \Exception
     * @return array selected records
     */
    public function selectList($limit_from, $rows_per_page, $select_status=null, $order_by=null, $order_direction='ASC', $select_type=null)
    {
        try {
            $SQL = "SELECT * FROM `".self::$table_name."`";
            if ((is_array($select_status) && !empty($select_status)) || (is_array($select_type) && !empty($select_type))) {
                $SQL .= " WHERE (";
                $use_status = false;
                if (is_array($select_status) && !empty($select_status)) {
                    $use_status = true;
                    $SQL .= '(';
                    $start = true;
                    foreach ($select_status as $stat) {
                        if (!$start) {
                            $SQL .= " OR ";
                        }
                        else {
                            $start = false;
                        }
                        $SQL .= "`contact_status`='$stat'";
                    }
                    $SQL .= ')';
                }
                if (is_array($select_type) && !empty($select_type)) {
                    if ($use_status) {
                        $SQL .= ' AND ';
                    }
                    $SQL .= '(';
                    $start = true;
                    foreach ($select_type as $typ) {
                        if (!$start) {
                            $SQL .= " OR ";
                        }
                        else {
                            $start = false;
                        }
                        $SQL .= "`contact_type`='$typ'";
                    }
                    $SQL .= ')';
                }
                $SQL .= ")";
            }
            if (is_array($order_by) && !empty($order_by)) {
                $SQL .= " ORDER BY ";
                $start = true;
                foreach ($order_by as $by) {
                    if (!$start) {
                        $SQL .= ", ";
                    }
                    else {
                        $start = false;
                    }
                    $SQL .= "`$by`";
                }
                $SQL .= " $order_direction";
            }
            $SQL .= " LIMIT $limit_from, $rows_per_page";
            $results = $this->app['db']->fetchAll($SQL);
            $overviews = array();
            foreach ($results as $result) {
                $overview = array();
                foreach ($result as $key => $value) {
                    $overview[$key] = is_string($value) ? $this->app['utils']->unsanitizeText($value) : $value;
                }
                $overviews[] = $overview;
            }
            return (!empty($overviews)) ? $overviews : false;
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Select contacts for the given tags and return a array for the usage in Twig
     *
     * @param array $tag_names to select for
     * @param string $status default is 'ACTIVE'
     * @param string $status_operator default = '='
     * @throws \Exception
     * @return array contacts
     */
    public function getContactsByTagsForTwig($tag_names, $status='ACTIVE', $status_operator='=')
    {
        try {
            $tag_string = '';
            $start = true;
            foreach ($tag_names as $tag_name) {
                $start ? $start = false : $tag_string .= ' OR ';
                $tag_string .= "`tag_name`='$tag_name'";
            }
            $SQL = "SELECT contact.contact_id, contact.order_name  FROM `".FRAMEWORK_TABLE_PREFIX."contact_tag` as tag, ".
                "`".self::$table_name."` as contact WHERE contact.contact_id = tag.contact_id AND ".
                "contact.contact_status$status_operator'$status' AND ($tag_string) GROUP BY contact.contact_id ORDER BY contact.order_name ASC";
            $results = $this->app['db']->fetchAll($SQL);
            $contacts = array();
            foreach ($results as $contact) {
                $contacts[$contact['contact_id']] = $contact['order_name'];
            }
            return $contacts;
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Search for contacts with the given search term. Multiple terms separated
     * by a space will be concat as OR condition, but you can also use AND as as
     * a separator to concat the terms with AND.
     *
     * @param string $search_term
     * @param array $tags default null - restrict search to the given tags
     * @param string $status by default 'DELETED'
     * @param string $status_operator by default '!='
     * @param string $order_by by default 'order_name'
     * @param string $order_direction by default 'ASC'
     * @throws \Exception
     * @return Ambigous <boolean, array > return false if no hit, overview records otherwise
     */
    public function searchContact($search_term, $tags=null, $status='DELETED', $status_operator='!=', $order_by='order_name', $order_direction='ASC')
    {
        try {
            $SQL = "SELECT * FROM `".self::$table_name."` WHERE (";
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
                $SQL .= "`contact_name` LIKE '%$search%' OR "
                    ."`contact_type`= '$search' OR `person_id` = '$search' OR `person_gender` = '$search'  OR "
                    ."`person_first_name` LIKE '%$search%' OR "
                    ."`person_last_name` LIKE '%$search%' OR "
                    ."`person_nick_name` LIKE '%$search%' OR "
                    ."`person_birthday` LIKE '%$search%' OR "
                    ."`company_id` = '$search' OR "
                    ."`company_name` LIKE '%$search%' OR "
                    ."`company_department` LIKE '%$search%' OR "
                    ."`communication_phone` LIKE '%$search%' OR "
                    ."`communication_email` LIKE '%$search%' OR "
                    ."`address_id` = '$search' OR "
                    ."`address_street` LIKE '%$search%' OR "
                    ."`address_zip` LIKE '%$search%' OR "
                    ."`address_city` LIKE '%$search%' OR "
                    ."`address_area` LIKE '%$search%' OR "
                    ."`address_state` LIKE '%$search%' OR "
                    ."`address_country_code` = '$search'";
            }
            $SQL .= ")) AND ";

            if (is_array($tags)) {
                $SQL .= "(";
                $start = true;
                foreach ($tags as $tag) {
                    if ($start) {
                        $start = false;
                    }
                    else {
                        $SQL .= " OR ";
                    }
                    $SQL .= "((`tags` = '$tag') OR (`tags` LIKE '$tag,%') OR (`tags` LIKE '%,$tag,%') OR (`tags` LIKE '%,$tag'))";
                }
                $SQL .= ") AND ";
            }
            $SQL .= "`contact_status` $status_operator '$status' ORDER BY $order_by $order_direction";

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
