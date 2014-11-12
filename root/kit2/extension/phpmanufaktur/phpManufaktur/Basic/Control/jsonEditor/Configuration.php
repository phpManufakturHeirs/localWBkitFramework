<?php

/**
 * kitFramework
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de
 * @copyright 2014 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\Basic\Control\jsonEditor;

use Silex\Application;

class Configuration
{
    protected $app = null;
    protected static $config = null;
    protected static $config_path = null;

    /**
     * Constructor
     *
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
        self::$config_path = MANUFAKTUR_PATH.'/Basic/config.jsoneditor.json';
        $this->readConfiguration();
    }

    /**
     * Return the default configuration array
     *
     * @return array
     */
    public function getDefaultConfigArray()
    {
        return array(
            'last_scan' => null,
            'wait_hours' => 72,
            'exclude' => array(
                'file' => array(
                    'composer.json',
                    'package.json',
                    'bower.json',
                    'extension.*',
                    'command.*',
                    'filter.*',
                    '_*.json',
                    '.*.json',
                    'bak.*.json'
                ),
                'directory' => array(
                    'Data',
                    'Library/Library'
                )
            ),
            'help' => array(
                // extensions should "inject" their hints by themselves!
                'accounts.list.json' => 'help_accounts_list_json',
                'cms.json' => 'help_cms_json',
                'config.jsoneditor.json' => 'help_config_jsoneditor_json',
                'doctrine.cms.json' => 'help_doctrine_cms_json',
                'framework.json' => 'help_framework_json',
                'proxy.json' => 'help_proxy_json',
                'swift.cms.json' => 'help_swift_cms_json',
            ),
            'configuration_files' => array()
        );
    }

    /**
     * Read the configuration file
     */
    protected function readConfiguration()
    {
        if (!$this->app['filesystem']->exists(self::$config_path)) {
            self::$config = $this->getDefaultConfigArray();
            $this->saveConfiguration();
        }
        self::$config = $this->app['utils']->readConfiguration(self::$config_path);
    }

    /**
     * Save the configuration file
     */
    public function saveConfiguration()
    {
        // write the formatted config file to the path
        file_put_contents(self::$config_path, $this->app['utils']->JSONFormat(self::$config));
        $this->app['monolog']->addDebug('Save configuration to '.basename(self::$config_path));
    }

    /**
     * Get the configuration array
     *
     * @return array
     */
    public function getConfiguration()
    {
        return self::$config;
    }

    /**
     * Set the configuration array
     *
     * @param array $config
     */
    public function setConfiguration($config)
    {
        self::$config = $config;
    }

}
