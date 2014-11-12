<?php

/**
 * Event
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de/Event
 * @copyright 2013 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\Event\Data\Setup;

use Silex\Application;
use phpManufaktur\Event\Data\Event\Propose;
use phpManufaktur\Event\Control\Configuration;
use phpManufaktur\Basic\Control\CMS\InstallAdminTool;
use phpManufaktur\Event\Data\Event\RecurringEvent;

class Update
{
    protected $app = null;

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
     * Release 2.0.18
     */
    protected function release_2018()
    {
        $Configuration = new Configuration($this->app);
        $config = $Configuration->getConfiguration();
        if (!isset($config['event']['description']['title']['required'])) {
            // enable/disable title, description short & long
            $config['event']['description']['title']['required'] = true;
            $config['event']['description']['short']['required'] = true;
            $config['event']['description']['long']['required'] = true;
            // enable frontend edit
            $config['event']['edit']['frontend'] = true;
            // enable additional administrative email addresses for proposes
            $config['event']['propose']['confirm']['mail_to'] = array('provider');
            // enable additional administrative email addresses for accounts
            $config['account']['confirm']['mail_to'] = array('provider');
            $Configuration->setConfiguration($config);
            $Configuration->saveConfiguration();
        }
    }

    /**
     * Release 2.0.16
     */
    protected function release_2016()
    {
        if (!$this->columnExists(FRAMEWORK_TABLE_PREFIX.'event_event', 'event_url')) {
            // add field event_url in table event_event
            $SQL = "ALTER TABLE `".FRAMEWORK_TABLE_PREFIX."event_event` ADD `event_url` TEXT NOT NULL AFTER `event_deadline`";
            $this->app['db']->query($SQL);
            $this->app['monolog']->addInfo('[Event Update] Add field `event_url` to table `event_event`');
        }

        if (!$this->tableExists(FRAMEWORK_TABLE_PREFIX.'event_propose')) {
            // create propose table
            $Propose = new Propose($this->app);
            $Propose->createTable();
        }

        $Configuration = new Configuration($this->app);
        $config = $Configuration->getConfiguration();
        if (!isset($config['event']['description'])) {
            $config['event']['description'] = array(
                'title' => array(
                    'min_length' => 5
                ),
                'short' => array(
                    'min_length' => 30
                ),
                'long' => array(
                    'min_length' => 50
                )
            );
            $config['event']['date'] = array(
                'event_date_from' => array(
                    'allow_date_in_past' => false
                ),
                'event_date_to' => array(

                ),
                'event_publish_from' => array(
                    'subtract_days' => 21
                ),
                'event_publish_to' => array(
                    'add_days' => 7
                )
            );
            $Configuration->setConfiguration($config);
            $Configuration->saveConfiguration();
        }
    }

    /**
     * Release 2.0.14
     */
    protected function release_2014()
    {
        if (file_exists(MANUFAKTUR_PATH.'/Event/config.event.json')) {
            $config = $this->app['utils']->readConfiguration(MANUFAKTUR_PATH.'/Event/config.event.json');
            if (!isset($config['rating']['active'])) {
                $config['rating'] = array(
                    'active' => true,
                    'type' => 'small',
                    'length' => 5,
                    'step' => true,
                    'rate_max' => 5,
                    'show_rate_info' => false
                    );
                // write the formatted config file to the path
                file_put_contents(MANUFAKTUR_PATH.'/Event/config.event.json', $this->app['utils']->JSONFormat($config));
                $this->app['monolog']->addDebug('Added rating -> active to /Event/config.event.json');
            }
        }
    }

    /**
     * Release 2.0.25
     */
    protected function release_2025()
    {
        if (file_exists(MANUFAKTUR_PATH.'/Event/config.event.json')) {
            $config = $this->app['utils']->readConfiguration(MANUFAKTUR_PATH.'/Event/config.event.json');
            if (!isset($config['event']['location']['unknown'])) {
                $config['event']['location'] = array(
                    'unknown' => array(
                        'enabled' => false,
                        'identifier' => 'unknown.location@event.dummy.tld'
                    ),
                    'required' => array(
                        'name' => false,
                        'zip' => true,
                        'city' => true,
                        'communication' => false
                    )
                );
                $config['event']['organizer']['unknown'] = array(
                    'enabled' => true,
                    'identifier' => 'unknown.organizer@event.dummy.tld'
                );
                // write the formatted config file to the path
                file_put_contents(MANUFAKTUR_PATH.'/Event/config.event.json', $this->app['utils']->JSONFormat($config));
                $this->app['monolog']->addDebug('Added rating -> active to /Event/config.event.json');
            }
            if (!isset($config['contact']['fragmentary'])) {
                $config['contact']['fragmentary'] = array(
                    'login' => array(
                        'suffix' => '@event.dummy.tld'
                    )
                );
                // write the formatted config file to the path
                file_put_contents(MANUFAKTUR_PATH.'/Event/config.event.json', $this->app['utils']->JSONFormat($config));
                $this->app['monolog']->addDebug('Added rating -> active to /Event/config.event.json');
            }
        }
    }

    protected function release_2028()
    {
        $files = array(
            '/Event/Control/Import/Dialog.php',
            '/Event/Template/default/backend',
            '/Event/Template/default/import'
        );
        foreach ($files as $file) {
            // remove no longer needed directories and files
            if ($this->app['filesystem']->exists(MANUFAKTUR_PATH.$file)) {
                $this->app['filesystem']->remove(MANUFAKTUR_PATH.$file);
                $this->app['monolog']->addInfo(sprintf('[Event Update] Removed file or directory %s', $file));
            }
        }
    }

    /**
     * Release 2.0.32
     */
    protected function release_2032()
    {
        // remove no longer needed files and directories
        $items = array(
            MANUFAKTUR_PATH.'/Event/Template/default/admin/bootstrap'
        );
        foreach ($items as $item) {
            $this->app['filesystem']->remove($item);
        }
    }

    /**
     * Release 2.0.33
     */
    protected function release_2033()
    {
        if (!$this->app['db.utils']->tableExists(FRAMEWORK_TABLE_PREFIX.'event_recurring_event')) {
            $RecurringEvent = new RecurringEvent($this->app);
            $RecurringEvent->createTable();
        }

        if (!$this->app['db.utils']->ColumnExists(FRAMEWORK_TABLE_PREFIX.'event_event', 'event_recurring_id')) {
            // add field event_url in table event_event
            $SQL = "ALTER TABLE `".FRAMEWORK_TABLE_PREFIX."event_event` ADD `event_recurring_id` INT(11) NOT NULL DEFAULT -1 AFTER `event_status`";
            $this->app['db']->query($SQL);
            $this->app['monolog']->addInfo('[Event Update] Add field `event_recurring_id` to table `event_event`');
        }
    }

    /**
     * Releas 2.0.35
     */
    protected function release_2035()
    {
        $Configuration = new Configuration($this->app);
        $config = $Configuration->getConfiguration();
        if (!isset($config['fallback'])) {
            $config['fallback']['cms']['url'] = '';
            $Configuration->setConfiguration($config);
            $Configuration->saveConfiguration();
        }
    }

    /**
     * Release 2.0.36
     */
    protected function release_2036()
    {
        if (!$this->app['db.utils']->enumValueExists(FRAMEWORK_TABLE_PREFIX.'event_recurring_event', 'month_pattern_type', 'FIRST_THIRD')) {
            $SQL = "ALTER TABLE `".FRAMEWORK_TABLE_PREFIX."event_recurring_event` CHANGE `month_pattern_type` `month_pattern_type` ENUM('FIRST','SECOND','THIRD','FOURTH','LAST','FIRST_THIRD','SECOND_FOURTH','SECOND_LAST') NOT NULL DEFAULT 'FIRST'";
            $this->app['db']->query($SQL);
            $this->app['monolog']->addInfo('[Event Update] Add enum values FIRST_THIRD, SECOND_FOURTH and SECOND_LAST to recurring events');
        }

        $Configuration = new Configuration($this->app);
        $config = $Configuration->getConfiguration();

        if (!isset($config['event']['subscription']['contact'])) {
            $config['event']['subscription']['contact'] = array(
                'gender' => array(
                    'name' => 'person_gender',
                    'enabled' => true,
                    'required' => true,
                    'default' => 'MALE'
                ),
                'first_name' => array(
                    'name' => 'person_first_name',
                    'enabled' => true,
                    'required' => false
                ),
                'last_name' => array(
                    'name' => 'person_last_name',
                    'enabled' => true,
                    'required' => true
                ),
                'email' => array(
                    'name' => 'email',
                    'enabled' => true,
                    'required' => true
                ),
                'phone' => array(
                    'name' => 'phone',
                    'enabled' => false,
                    'required' => false
                ),
                'cell' => array(
                    'name' => 'cell',
                    'enabled' => false,
                    'required' => false
                ),
                'birthday' => array(
                    'name' => 'birthday',
                    'enabled' => false,
                    'required' => false
                ),
                'street' => array(
                    'name' => 'street',
                    'enabled' => false,
                    'required' => false
                ),
                'zip' => array(
                    'name' => 'zip',
                    'enabled' => false,
                    'required' => false
                ),
                'city' => array(
                    'name' => 'city',
                    'enabled' => false,
                    'required' => false
                ),
                'country' => array(
                    'name' => 'country',
                    'enabled' => false,
                    'required' => false,
                    'default' => 'DE',
                    'preferred' => array('DE','AT','CH')
                )
            );

            $config['event']['subscription']['terms'] = array(
                'name' => 'terms_conditions',
                'enabled' => false,
                'required' => true,
                'label' => 'I accept the <a href="%url%" target="_blank">general terms and conditions</a>',
                'url' => CMS_URL
            );

            $Configuration->setConfiguration($config);
            $Configuration->saveConfiguration();
        }
    }

    /**
     * Release 2.0.39
     */
    protected function release_2039()
    {
        $Configuration = new Configuration($this->app);
        $config = $Configuration->getConfiguration();

        if (!isset($config['nav_tabs'])) {
            $config['nav_tabs'] = array(
                'order' => array(
                    'event_list',
                    'event_edit',
                    'subscription',
                    'propose',
                    'contact_list',
                    'contact_edit',
                    'group',
                    'about'
                ),
                'default' => 'about'
            );
            $Configuration->setConfiguration($config);
            $Configuration->saveConfiguration();
        }
    }

    /**
     * Release 2.0.42
     */
    protected function release_2042()
    {
        $Configuration = new Configuration($this->app);
        $config = $Configuration->getConfiguration();

        if (!isset($config['contact']['person'])) {
            $default = $Configuration->getDefaultConfigArray();
            $config['contact']['person'] = $default['contact']['person'];
            $config['contact']['company'] = $default['contact']['company'];
            $Configuration->setConfiguration($config);
            $Configuration->saveConfiguration();
        }
    }

    /**
     * Execute the update for Event
     *
     * @param Application $app
     */
    public function exec(Application $app)
    {
        $this->app = $app;

        $this->release_2014();
        $this->release_2016();
        $this->release_2018();
        $this->release_2025();
        $this->release_2028();
        $this->release_2032();
        $this->release_2033();
        $this->release_2035();
        $this->release_2036();
        $this->release_2039();
        $this->release_2042();

        // re-install or update the admin-tool
        $AdminTool = new InstallAdminTool($app);
        $AdminTool->exec(MANUFAKTUR_PATH.'/Event/extension.json', '/event/cms');

        return $app['translator']->trans('Successfull updated the extension %extension%.',
            array('%extension%' => 'Event'));
    }
}
