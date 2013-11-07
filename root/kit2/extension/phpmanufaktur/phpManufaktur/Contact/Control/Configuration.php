<?php

/**
 * Contact
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de/FacebookGallery
 * @copyright 2013 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\Contact\Control;

use Silex\Application;

class Configuration
{
    protected $app = null;
    protected static $config = null;

    /**
     * Constructor
     *
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
        $this->readConfiguration();
    }

    /**
     * Return the default configuration array for Event
     *
     * @return array
     */
    public static function getDefaultConfigArray()
    {
        return array(
            'email' => array(
                'required' => true
            ),
            'countries' => array(
                'preferred' => array(
                    'DE',
                    'CH',
                    'AT'
                )
            )
        );
    }

    /**
     * Read the configuration file
     */
    protected function readConfiguration()
    {
        if (!file_exists(MANUFAKTUR_PATH.'/Contact/config.contact.json')) {
            self::$config = $this->getDefaultConfigArray();
            $this->saveConfiguration();
        }
        self::$config = $this->app['utils']->readConfiguration(MANUFAKTUR_PATH.'/Contact/config.contact.json');
    }

    /**
     * Save the configuration file
     */
    public function saveConfiguration()
    {
        // write the formatted config file to the path
        file_put_contents(MANUFAKTUR_PATH.'/Contact/config.contact.json', $this->app['utils']->JSONFormat(self::$config));
        $this->app['monolog']->addDebug('Save configuration /Contact/config.contact.json');
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
