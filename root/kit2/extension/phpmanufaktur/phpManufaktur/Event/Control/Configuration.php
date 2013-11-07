<?php

/**
 * Event
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de/FacebookGallery
 * @copyright 2013 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\Event\Control;

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
            'general' => array(
                'max_execution_time' => 60
            ),
            'event' => array(
                'microdata' => array(
                    'offer_count_unlimited' => 20
                ),
                'subscription' => array(
                    'confirm' => array(
                        'double_opt_in' => false,
                        'mail_to' => array(
                            'contact',
                            'provider',
                            'organizer'
                        )
                    )
                ),
                'description' => array(
                    'title' => array(
                        'required' => true,
                        'min_length' => 5
                    ),
                    'short' => array(
                        'required' => true,
                        'min_length' => 30
                    ),
                    'long' => array(
                        'required' => true,
                        'min_length' => 50
                    )
                ),
                'date' => array(
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
                ),
                'edit' => array(
                    'frontend' => true
                ),
                'propose' => array(
                    'confirm' => array(
                        'mail_to' => array(
                            'provider'
                        )
                    )
                )
            ),
            'account' => array(
                    'confirm' => array(
                        'mail_to' => array(
                            'provider'
                        )
                    )
                ),
            'contact' => array(
                'confirm' => array(
                    'double_opt_in' => true,
                    'mail_to' => array(
                        'contact',
                        'provider'
                    )
                )
            ),
            'permalink' => array(
                'cms' => array(
                    'url' => ''
                )
            ),
            'ical' => array(
                'active' => true,
                'framework' => array(
                    'path' => '/media/protected/event/ical'
                )
            ),
            'qrcode' => array(
                "active" => false,
                "framework" => array(
                    "path" => array(
                        "link" => '/media/protected/event/qrcode/link',
                        "ical" => '/media/protected/event/qrcode/ical'
                    )
                ),
                "settings" => array(
                    "content" => "link",
                    "size" => 3,
                    "error_correction" => 1,
                    "margin" => 2
                )
            ),
            'rating' => array(
                'active' => true,
                'type' => 'small',
                'length' => 5,
                'step' => true,
                'rate_max' => 5,
                'show_rate_info' => false
            )
        );
    }

    /**
     * Read the configuration file
     */
    protected function readConfiguration()
    {
        if (!file_exists(MANUFAKTUR_PATH.'/Event/config.event.json')) {
            self::$config = $this->getDefaultConfigArray();
            $this->saveConfiguration();
        }
        self::$config = $this->app['utils']->readConfiguration(MANUFAKTUR_PATH.'/Event/config.event.json');
    }

    /**
     * Save the configuration file
     */
    public function saveConfiguration()
    {
        // write the formatted config file to the path
        file_put_contents(MANUFAKTUR_PATH.'/Event/config.event.json', $this->app['utils']->JSONFormat(self::$config));
        $this->app['monolog']->addDebug('Save configuration /Event/config.event.json');
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
