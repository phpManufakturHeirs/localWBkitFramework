<?php

/**
 * Event
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de/Event
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
            'nav_tabs' => array(
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
            ),
            'general' => array(
                'max_execution_time' => 60
            ),
            'event' => array(
                'microdata' => array(
                    'offer_count_unlimited' => 20
                ),
                'location' => array(
                    'unknown' => array(
                        'enabled' => false,
                        'identifier' => 'unkown.location@event.dummy.tld'
                    ),
                    'required' => array(
                        'name' => false,
                        'zip' => true,
                        'city' => true,
                        'communication' => false,
                    )
                ),
                'organizer' => array(
                    'unknown' => array(
                        'enabled' => true,
                        'identifier' => 'unknown.organizer@event.dummy.tld'
                    ),
                ),
                'subscription' => array(
                    'confirm' => array(
                        'double_opt_in' => false,
                        'mail_to' => array(
                            'contact',
                            'provider',
                            'organizer'
                        )
                    ),
                    'contact' => array(
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
                    ),
                    'terms' => array(
                        'name' => 'terms_conditions',
                        'enabled' => false,
                        'required' => true,
                        'label' => 'I accept the <a href="%url%" target="_blank">general terms and conditions</a>',
                        'url' => CMS_URL
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
                ),
                'fragmentary' => array(
                    'login' => array(
                        'suffix' => '@event.dummy.tld'
                    )
                ),
                'person' => array(
                    'field' => array(
                        'predefined' => array(),
                        'visible' => array(
                            'contact_status',
                            'category_name',
                            'category_access',
                            'tags',
                            'person_gender',
                            'person_title',
                            'person_first_name',
                            'person_last_name',
                            'person_birthday',
                            'communication_email',
                            'communication_phone',
                            'communication_cell',
                            'communication_url',
                            'address_street',
                            'address_zip',
                            'address_city',
                            'address_country_code',
                            'note',
                            'extra_fields',
                            'special_fields'
                        ),
                        'required' => array(
                            'communication_email'
                        ),
                        'hidden' => array(
                            'contact_id',
                            'contact_type',
                            'category_id',
                            'category_type_id',
                            'person_id',
                            'company_id',
                            'address_id'
                        ),
                        'readonly' => array(
                            'category_access'
                        ),
                        'tags' => array(),
                        'route' => array(
                            'person' => '/admin/event/contact/person/edit',
                            'tag' => '/admin/event/contact/tag/list',
                            'category' => '/admin/event/contact/category/list',
                            'title' => '/admin/event/contact/title/list',
                            'list' => '/admin/event/contact/list'
                        )
                    )
                ),
                'company' => array(
                    'field' => array(
                        'predefined' => array(),
                        'visible' => array(
                            'contact_status',
                            'category_name',
                            'category_access',
                            'tags',
                            'company_name',
                            'company_department',
                            'communication_email',
                            'communication_phone',
                            'communication_fax',
                            'communication_url',
                            'address_street',
                            'address_zip',
                            'address_city',
                            'address_country_code',
                            'address_delivery_street',
                            'address_delivery_zip',
                            'address_delivery_city',
                            'address_delivery_country_code',
                            'address_billing_street',
                            'address_billing_zip',
                            'address_billing_city',
                            'address_billing_country_code',
                            'note',
                            'extra_fields',
                            'special_fields'
                        ),
                        'required' => array(
                            'communication_email'
                        ),
                        'hidden' => array(
                            'contact_id',
                            'contact_type',
                            'category_id',
                            'category_type_id',
                            'company_id',
                            'person_id',
                            'address_id'
                        ),
                        'readonly' => array(
                            'category_access'
                        ),
                        'tags' => array(),
                        'route' => array(
                            'company' => '/admin/event/contact/company/edit',
                            'tag' => '/admin/event/contact/tag/list',
                            'category' => '/admin/event/contact/category/list',
                            'list' => '/admin/event/contact/list'
                        )
                    )
                )
            ),
            'permalink' => array(
                'cms' => array(
                    'url' => ''
                )
            ),
            'fallback' => array(
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
