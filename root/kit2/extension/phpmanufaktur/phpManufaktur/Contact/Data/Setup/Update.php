<?php

/**
 * Contact
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de/contact
 * @copyright 2013 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\Contact\Data\Setup;

use Silex\Application;
use phpManufaktur\Contact\Data\Contact\Protocol;
use phpManufaktur\Contact\Data\Contact\ExtraType;
use phpManufaktur\Contact\Data\Contact\ExtraCategory;
use phpManufaktur\Contact\Data\Contact\Extra;
use phpManufaktur\Contact\Data\Contact\Message;
use phpManufaktur\Contact\Data\Contact\Overview;
use phpManufaktur\Contact\Control\Configuration;
use phpManufaktur\Basic\Control\CMS\InstallAdminTool;

class Update
{
    protected $app = null;
    protected $db_config = null;

    /**
     * Check if the give column exists in the table
     *
     * @param string $table
     * @param string $column_name
     * @return boolean
     */
    protected function columnExists($table, $column_name)
    {
        try {
            $query = $this->app['db']->query("DESCRIBE `$table`");
            while (false !== ($row = $query->fetch())) {
                if ($row['Field'] == $column_name) return true;
            }
            return false;
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Check if the given $table exists
     *
     * @param string $table
     * @throws \Exception
     * @return boolean
     */
    protected function tableExists($table)
    {
        try {
            $query = $this->app['db']->query("SHOW TABLES LIKE '$table'");
            return (false !== ($row = $query->fetch())) ? true : false;
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Check if the $value exists in the ENUM array of the $field
     *
     * @param string $table
     * @param string $field
     * @param string $value
     * @throws \Exception
     * @return boolean|NULL NULL if ENUM not exists in $field or TRUE|FALSE for $value
     */
    protected function enumValueExists($table, $field, $value)
    {
        try {
            $SQL = "SHOW COLUMNS FROM `$table` WHERE FIELD = '$field'";
            $result = $this->app['db']->fetchAssoc($SQL);
            if (isset($result['Type']) && (false !== strpos($result['Type'], "enum('"))) {
                $enum = str_replace(array("enum('", "')", "''"), array('', '', "'"), $result['Type']);
                $check = explode("','", $enum);
                return in_array($value, $check);
            }
            return null;
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Release 2.0.13
     */
    protected function release_2013()
    {
        try {
            if (!$this->tableExists(FRAMEWORK_TABLE_PREFIX.'contact_protocol')) {
                // create protocol table
                $Protocol = new Protocol($this->app);
                $Protocol->createTable();
                $this->app['monolog']->addInfo('[Contact Update] Create table `contact_protocol`');
            }

            if (!$this->columnExists(FRAMEWORK_TABLE_PREFIX.'contact_contact', 'contact_since')) {
                // add field contact_since in contact_contact
                $SQL = "ALTER TABLE `".FRAMEWORK_TABLE_PREFIX."contact_contact` ADD `contact_since` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00' AFTER `contact_type`";
                $this->app['db']->query($SQL);
                $this->app['monolog']->addInfo('[Contact Update] Add field `contact_since` to table `contact_contact`');
            }

            if ($this->columnExists(FRAMEWORK_TABLE_PREFIX.'contact_person', 'person_contact_since')) {
                // move data from `person_contact_since` to `contact_since`
                $SQL = "SELECT `contact_id`, `person_contact_since` FROM `".FRAMEWORK_TABLE_PREFIX."contact_person`";
                $results = $this->app['db']->fetchAll($SQL);
                foreach ($results as $result) {
                    // move all dates to `contact_contact`
                    $this->app['db']->update(
                        FRAMEWORK_TABLE_PREFIX.'contact_contact',
                        array('contact_since' => $result['person_contact_since']),
                        array('contact_id' => $result['contact_id'])
                    );
                }
                $this->app['monolog']->addInfo('[Contact Update] Moved all `person_contact_since` dates to `contact_since`');
                // delete column
                $SQL = "ALTER TABLE `".FRAMEWORK_TABLE_PREFIX."contact_person` DROP `person_contact_since`";
                $this->app['db']->query($SQL);
                $this->app['monolog']->addInfo('[Contact Update] Deleted column `person_contact_since`');
            }

            if (!$this->columnExists(FRAMEWORK_TABLE_PREFIX.'contact_note', 'note_originator')) {
                // add field `note_originator`
                $SQL = "ALTER TABLE `".FRAMEWORK_TABLE_PREFIX."contact_note` ADD `note_originator` VARCHAR(64) NOT NULL DEFAULT 'SYSTEM' AFTER `note_content`";
                $this->app['db']->query($SQL);
                $this->app['monolog']->addInfo('[Contact Update] Add field `note_originator` to table `contact_note`');
            }

            if (!$this->columnExists(FRAMEWORK_TABLE_PREFIX.'contact_note', 'note_date')) {
                // add field `note_date`
                $SQL = "ALTER TABLE `".FRAMEWORK_TABLE_PREFIX."contact_note` ADD `note_date` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00' AFTER `note_originator`";
                $this->app['db']->query($SQL);
                $this->app['monolog']->addInfo('[Contact Update] Add field `note_date` to table `contact_note`');
            }

            if (!$this->tableExists(FRAMEWORK_TABLE_PREFIX.'contact_extra_type')) {
                $ExtraType = new ExtraType($this->app);
                $ExtraType->createTable();
                $this->app['monolog']->addInfo('[Contact Update] Create table `contact_extra_type`');
            }

            if (!$this->tableExists(FRAMEWORK_TABLE_PREFIX.'contact_extra_category')) {
                $ExtraCategory = new ExtraCategory($this->app);
                $ExtraCategory->createTable();
                $this->app['monolog']->addInfo('[Contact Update] Create table `contact_extra_category`');
            }

            if (!$this->tableExists(FRAMEWORK_TABLE_PREFIX.'contact_extra')) {
                $Extra = new Extra($this->app);
                $Extra->createTable();
                $this->app['monolog']->addInfo('[Contact Update] Create table `contact_extra`');
            }

            if (!$this->tableExists(FRAMEWORK_TABLE_PREFIX.'contact_message')) {
                $Message = new Message($this->app);
                $Message->createTable();
                $this->app['monolog']->addInfo('[Contact Update] Create table `contact_message`');
            }


        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Release 2.0.14
     */
    protected function release_2014()
    {
        $has_changed = false;
        if (!$this->columnExists(FRAMEWORK_TABLE_PREFIX.'contact_overview', 'address_area')) {
            // add field `adress_area`
            $SQL = "ALTER TABLE `".FRAMEWORK_TABLE_PREFIX."contact_overview` ADD `address_area` VARCHAR(128) NOT NULL DEFAULT '' AFTER `address_city`";
            $this->app['db']->query($SQL);
            $this->app['monolog']->addInfo('[Contact Update] Add field `address_area` to table `contact_overview`');
            $has_changed = true;
        }
        if (!$this->columnExists(FRAMEWORK_TABLE_PREFIX.'contact_overview', 'address_state')) {
            // add field `adress_area`
            $SQL = "ALTER TABLE `".FRAMEWORK_TABLE_PREFIX."contact_overview` ADD `address_state` VARCHAR(128) NOT NULL DEFAULT '' AFTER `address_area`";
            $this->app['db']->query($SQL);
            $this->app['monolog']->addInfo('[Contact Update] Add field `address_state` to table `contact_overview`');
            $has_changed = true;
        }
        if ($has_changed) {
            // execute a rebuild of all addresses in the overview table
            $this->app['monolog']->addInfo('[Contact Update] Start rebuilding the table `contact_overview`');
            $ContactOverview = new Overview($this->app);
            $ContactOverview->rebuildOverview();
            $this->app['monolog']->addInfo('[Contact Update] Finished rebuilding the table `contact_overview`');
        }
    }

    /**
     * Release 2.0.15
     */
    protected function release_2015()
    {
        if (false === ($this->enumValueExists(FRAMEWORK_TABLE_PREFIX.'contact_contact', 'contact_status', 'PENDING'))) {
            // add PENDING to contact_status
            $SQL = "ALTER TABLE `".FRAMEWORK_TABLE_PREFIX."contact_contact` CHANGE `contact_status` `contact_status` ENUM('ACTIVE', 'LOCKED', 'PENDING', 'DELETED') NOT NULL DEFAULT 'ACTIVE'";
            $this->app['db']->query($SQL);
            $this->app['monolog']->addInfo('[Contact Update] Add ENUM value PENDING to field `contact_status` in table `contact_contact`');
        }

        if (false === ($this->enumValueExists(FRAMEWORK_TABLE_PREFIX.'contact_overview', 'contact_status', 'PENDING'))) {
            // add PENDING to contact_status
            $SQL = "ALTER TABLE `".FRAMEWORK_TABLE_PREFIX."contact_overview` CHANGE `contact_status` `contact_status` ENUM('ACTIVE', 'LOCKED', 'PENDING', 'DELETED') NOT NULL DEFAULT 'ACTIVE'";
            $this->app['db']->query($SQL);
            $this->app['monolog']->addInfo('[Contact Update] Add ENUM value PENDING to field `contact_status` in table `contact_overview`');
        }

    }

    /**
     * Release 2.0.21
     */
    protected function release_2021()
    {
        if (!$this->columnExists(FRAMEWORK_TABLE_PREFIX.'contact_overview', 'contact_login')) {
            // add field
            $SQL = "ALTER TABLE `".FRAMEWORK_TABLE_PREFIX."contact_overview` ADD `contact_login` VARCHAR(64) NOT NULL DEFAULT '' AFTER `contact_id`";
            $this->app['db']->query($SQL);
            $this->app['monolog']->addInfo('[Contact Update] Add field `contact_login` to table `contact_overview`');
            // execute a rebuild of all addresses in the overview table
            $this->app['monolog']->addInfo('[Contact Update] Start rebuilding the table `contact_overview`');
            $ContactOverview = new Overview($this->app);
            $ContactOverview->rebuildOverview();
            $this->app['monolog']->addInfo('[Contact Update] Finished rebuilding the table `contact_overview`');
        }
    }

    /**
     * Execute all available update steps
     *
     * @param Application $app
     * @throws \Exception
     * @return string message
     */
    public function exec(Application $app)
    {
        try {
            $this->app = $app;

            // get Doctrine settings
            $this->db_config = $this->app['utils']->readConfiguration(FRAMEWORK_PATH . '/config/doctrine.cms.json');

            // Release 2.0.13
            $this->app['monolog']->addInfo('[Contact Update] Execute update for release 2.0.13');
            $this->release_2013();

            // Release 2.0.14
            $this->app['monolog']->addInfo('[Contact Update] Execute update for release 2.0.14');
            $this->release_2014();

            // Release 2.0.15
            $this->app['monolog']->addInfo('[Contact Update] Execute update for release 2.0.15');
            $this->release_2015();

            // Release 2.0.21
            $this->app['monolog']->addInfo('[Contact Update] Execute update for release 2.0.21');
            $this->release_2021();

            // Create Configuration if not exists - only constructor needed
            $Configuration = new Configuration($app);

            // setup kit_framework_contact as Add-on in the CMS
            $admin_tool = new InstallAdminTool($app);
            $admin_tool->exec(MANUFAKTUR_PATH.'/Contact/extension.json', '/contact/cms');

            // prompt message and return
            $this->app['monolog']->addInfo('[Contact Update] The update process was successfull.');

            return $app['translator']->trans('Successfull updated the extension %extension%.',
                array('%extension%' => 'Contact'));
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }
}
