<?php

/**
 * Event
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de/FacebookGallery
 * @copyright 2013 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\Event\Data\Setup;

use Silex\Application;
use phpManufaktur\Event\Data\Event\Propose;
use phpManufaktur\Event\Control\Configuration;

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
     * Execute the update for Event
     *
     * @param Application $app
     */
    public function exec(Application $app)
    {
        $this->app = $app;

        // Release 2.0.14
        $this->release_2014();

        // Release 2.0.16
        $this->release_2016();

        // Release 2.0.18
        $this->release_2018();

        return $app['translator']->trans('Successfull updated the extension %extension%.',
            array('%extension%' => 'Event'));
    }
}
