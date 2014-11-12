<?php

/**
 * kitFramework::Basic
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de
 * @copyright 2013 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\Basic\Data\Security;

use phpManufaktur\Basic\Control\Account\manufakturPasswordEncoder;
use Silex\Application;

/**
 * User table for the kitFramework.
 * Contains the encrypted password, username, email and roles
 *
 * @author Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 *
 */
class Users
{

    protected $app = null;
    private static $guid_wait_hours_between_resets = 24;
    protected static $table_name = null;

    public function __construct (Application $app)
    {
        $this->app = $app;
        self::$table_name = FRAMEWORK_TABLE_PREFIX.'basic_users';
    } // __construct()

    /**
     *
     * @return the $guid_wait_hours_between_resets
     */
    public static function getGuidWaitHoursBetweenResets ()
    {
        return Users::$guid_wait_hours_between_resets;
    }

    /**
     *
     * @param number $guid_wait_hours_between_resets
     */
    public static function setGuidWaitHoursBetweenResets ($guid_wait_hours_between_resets)
    {
        Users::$guid_wait_hours_between_resets = $guid_wait_hours_between_resets;
    }

    /**
     * Create the table 'users'
     *
     * @throws \Exception
     */
    public function createTable ()
    {
        $table = self::$table_name;
        $SQL = <<<EOD
    CREATE TABLE IF NOT EXISTS `$table` (
      `id` INT(11) NOT NULL AUTO_INCREMENT,
      `username` VARCHAR(64) NOT NULL DEFAULT '',
      `email` VARCHAR(128) NOT NULL DEFAULT '',
      `password` VARCHAR(255) NOT NULL DEFAULT '',
      `displayname` VARCHAR(64) NOT NULL DEFAULT '',
      `last_login` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
      `roles` TEXT NOT NULL,
      `guid` VARCHAR(64) NOT NULL DEFAULT '',
      `guid_timestamp` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
      `guid_status` ENUM('ACTIVE', 'LOCKED') NOT NULL DEFAULT 'ACTIVE',
      `status` ENUM('ACTIVE','LOCKED') NOT NULL DEFAULT 'ACTIVE',
      `timestamp` TIMESTAMP,
      PRIMARY KEY (`id`),
      UNIQUE (`username`, `email`, `guid`)
    )
    COMMENT='The user table for the kitFramework'
    ENGINE=InnoDB
    AUTO_INCREMENT=1
    DEFAULT CHARSET=utf8
    COLLATE='utf8_general_ci'
EOD;
        try {
            $this->app['db']->query($SQL);
            $this->app['monolog']->addDebug("Created table '".self::$table_name."'", array(__METHOD__, __LINE__));
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e->getMessage(), 0, $e);
        }
    } // createTable()

    /**
     * Delete table - switching check for foreign keys off before executing
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
            $this->app['monolog']->addDebug("Drop table 'basic_users'", array(__METHOD__, __LINE__));
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Delete the record with the given ID
     *
     * @param integer $id
     * @throws \Exception
     */
    public function delete($id)
    {
        try {
            $this->app['db']->delete(self::$table_name, array('id' => $id));
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Select a User record from the given $name where $name can be the login name
     * or the email address of the user
     *
     * @param string $name
     * @throws \Exception
     * @return boolean multitype:Ambigous mixed, unknown>
     */
    public function selectUser($name)
    {
        try {
            $login = strtolower(trim($name));
            $SQL = "SELECT * FROM `" .self::$table_name. "` WHERE (`username`='$login' OR `email`='$login')";
            $result = $this->app['db']->fetchAssoc($SQL);
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e->getMessage(), 0, $e);
        }
        if (! isset($result['username'])) {
            // no user found!
            $this->app['monolog']->addDebug(sprintf('User %s not found in table _users', $name));
            return false;
        }
        $user = array();
        foreach ($result as $key => $value)
            $user[$key] = (is_string($value)) ? $this->app['utils']->unsanitizeText($value) : $value;
        return $user;
    } // selectUser()

    /**
     * Select a user record by the given ID
     *
     * @param integer $id
     * @throws \Exception
     * @return boolean|array FALSE if the record not exists
     */
    public function select($id)
    {
        try {
            $SQL = "SELECT * FROM `".self::$table_name."` WHERE `id`=$id";
            $result = $this->app['db']->fetchAssoc($SQL);
            $user = array();
            foreach ($result as $key => $value) {
                $user[$key] = is_string($value) ? $this->app['utils']->unsanitizeText($value) : $value;
            }
            return (isset($user['id'])) ? $user : false;
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e->getMessage(), 0, $e);
        }
    }

    /**
     * Insert a new User into the table.
     * Create a GUID if none exists.
     *
     * @param array $data
     * @param integer $user_id
     * @throws \Exception
     */
    public function insertUser ($data, &$user_id = -1)
    {
        try {
            if (! isset($data['username']) || ! isset($data['email']) || ! isset($data['password']) || ! isset($data['roles']))
                throw new \Exception('The fields username, email, password and roles must be set!');
            if (! isset($data['guid']) || empty($data['guid'])) {
                // create a GUID and set the timestamp
                $data['guid'] = $this->app['utils']->createGUID();
                $data['guid_timestamp'] = date('Y-m-d H:i:s');
            }
            if (isset($data['displayname']))
                $data['displayname'] = $this->app['utils']->sanitizeText($data['displayname']);
            if (is_array($data['roles']))
                $data['roles'] = implode(',', $data['roles']);
            $data['email'] = strtolower($data['email']);
            // insert a new record
            $this->app['db']->insert(self::$table_name, $data);
            $user_id = $this->app['db']->lastInsertId();
            return $user_id;
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e->getMessage(), 0, $e);
        }
    } // insertUser()

    /**
     * Create a new GUID for the user.
     * The GUID is needed to request a new password.
     *
     * @param string $email
     * @param boolean $guid_check
     * @throws \Exception
     * @return boolean
     */
    public function createNewGUID ($email, $guid_check = true)
    {
        try {
            $user = $this->selectUser($email);
            if (! is_array($user) || ! isset($user['id'])) {
                // user does not exists - logfile is written by selectUser()
                return false;
            }
            if ($guid_check) {
                $d = strtotime($user['guid_timestamp']);
                $limit = mktime(date('H', $d) + self::getGuidWaitHoursBetweenResets(),
                    date('i', $d), date('s', $d), date('m', $d), date('d', $d), date('Y', $d));
                if (time() < $limit) {
                    // cannot create a new GUID as long the old GUID is not expired
                    return false;
                    //throw new \Exception(sprintf('Can\'t create a new GUID as long the last GUID is not expired. You must wait %d hours between the creation.'));
                }
            }
            $data = array(
                'guid' => $this->app['utils']->createGUID(),
                'guid_status' => 'ACTIVE',
                'guid_timestamp' => date('Y-m-d H:i:s')
            );
            $where = array(
                'email' => $email
            );
            $this->app['db']->update(self::$table_name, $data, $where);
            $this->app['monolog']->addDebug(sprintf('Created a new GUID for user %s.', $email));
            // return the GUID data array
            return $data;
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e->getMessage(), 0, $e);
        }
    } // createNewGUID()

    /**
     * Select a user by the submitted GUID
     *
     * @param string $guid
     * @throws \Exception
     * @return boolean|multitype:Ambigous <string, mixed, unknown>
     */
    public function selectUserByGUID($guid) {
        try {
            $SQL = "SELECT * FROM `".self::$table_name."` WHERE `guid`='$guid'";
            $result = $this->app['db']->fetchAssoc($SQL);
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e->getMessage());
        }
        if (!is_array($result) || !isset($result['guid'])) {
            return false;
        }
        try {
            $user = array();
            foreach ($result as $key => $value)
                $user[$key] = (is_string($value)) ? $this->app['utils']->unsanitizeText($value) : $value;
            return $user;
        } catch (\Doctrine\DBAL\DBALException $e)  {
            throw new \Exception($e);
        }
    } // selectUserByGUID()

    /**
     * Update the record for $username with the given $data
     *
     * @param string $username
     * @param array $data
     * @throws \Exception
     */
    public function updateUser($username, $data) {
        try {
            $update = array();
            foreach ($data as $key => $value) {
                $update[$this->app['db']->quoteIdentifier($key)] = (is_string($value)) ? $this->app['utils']->sanitizeText($value) : $value;
            }
            $this->app['db']->update(self::$table_name, $update, array('username' => $username));
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Update a user record by the given user ID
     *
     * @param integer $id
     * @param array $data
     * @throws \Exception
     */
    public function updateUserByID($id, $data)
    {
        try {
            $update = array();
            foreach ($data as $key => $value) {
                if (($key == 'id') || ($key == 'timestamp') || ($key == 'last_login')) {
                    continue;
                }
                $update[$key] = (is_string($value)) ? $this->app['utils']->sanitizeText($value) : $value;
            }
            $this->app['db']->update(self::$table_name, $update, array('id' => $id));
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Check if the user with the given username or email exists
     *
     * @param string $username username or email
     * @param integer $ignore_id dont't check the given user ID
     * @throws \Exception
     * @return boolean
     */
    public function existsUser($username, $ignore_id=null) {
        try {
            $SQL = "SELECT `id` FROM `".self::$table_name."` WHERE `username`='$username' OR `email`='$username'";
            if (!is_numeric($ignore_id)) {
                $SQL .= " AND `id`!='$ignore_id'";
            }
            $id = $this->app['db']->fetchColumn($SQL);
            return ($id > 0);
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e->getMessage(), 0, $e);
        }
    }

    /**
     * Check if the given displayname is already in use
     *
     * @param string $displayname
     * @param integer $ignore_id don't check the given user ID
     * @throws \Exception
     * @return boolean
     */
    public function existsDisplayName($displayname, $ignore_id=null) {
        try {
            $SQL = "SELECT `id` FROM `".self::$table_name."` WHERE `displayname`='$displayname'";
            if (!is_numeric($ignore_id)) {
                $SQL .= " AND `id`!='$ignore_id'";
            }
            $id = $this->app['db']->fetchColumn($SQL);
            return ($id > 0);
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e->getMessage(), 0, $e);
        }
    }

    /**
     * Check if the login is valid
     *
     * @param string $username
     * @param string $password
     * @throws \Exception
     * @return boolean
     */
    public function checkLogin($username, $password, &$roles=array())
    {
        try {
            $pass = $this->encodePassword($password);
            $SQL = "SELECT `roles` FROM `".self::$table_name."` WHERE (`username`='$username' OR `email`='$username') AND `password`='$pass' AND `status`='ACTIVE'";
            $result = $this->app['db']->fetchAssoc($SQL);
            if (!is_array($result) || !isset($result['roles'])) {
                return false;
            }
            $roles = (strpos($result['roles'], ',')) ? explode(',', $result['roles']) : array($result['roles']);
            return true;
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e->getMessage(), 0, $e);
        }
    }

    /**
     * Encode the raw password
     *
     * @param string $raw the password
     * @param string $salt (actual not used)
     * @return string
     */
    public function encodePassword($raw, $salt='')
    {
        $passwordEncoder = new manufakturPasswordEncoder($this->app);
        return $passwordEncoder->encodePassword($raw, $salt);
    }

    /**
     * Return the number of records of the user table
     *
     * @return integer
     * @throws \Exception
     */
    public function count()
    {
        try {
            $SQL = "SELECT COUNT(*) FROM `".self::$table_name."`";
            return $this->app['db']->fetchColumn($SQL);
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    public function selectList($limit_from, $rows_per_page, $columns, $order_by=null, $order_direction='ASC')
    {
        try {
            $SQL = "SELECT * FROM `".self::$table_name."`";
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
                    $SQL .= "$by";
                }
                $SQL .= " $order_direction";
            }
            $SQL .= " LIMIT $limit_from, $rows_per_page";
            $results = $this->app['db']->fetchAll($SQL);
            $accounts = array();
            foreach ($results as $result) {
                $account = array();
                foreach ($columns as $column) {
                    $account[$column] = (is_string($result[$column])) ? $this->app['utils']->unsanitizeText($result[$column]) : $result[$column];
                }
                $accounts[] = $account;
            }
            return $accounts;
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

}
