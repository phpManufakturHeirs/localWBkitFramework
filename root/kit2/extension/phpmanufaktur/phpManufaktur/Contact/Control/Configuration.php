<?php

/**
 * Contact
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de/Contact
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
                'required' => true,
                'parse' => array(
                    'enabled' => true,
                    'validate' => true,
                    'format' => true
                )
            ),
            'countries' => array(
                'preferred' => array(
                    'DE',
                    'CH',
                    'AT'
                )
            ),
            'phonenumber' => array(
                'parse' => array(
                    'enabled' => true,
                    'default_country' => 'DE',
                    'validate' => true,
                    'format' => true,
                    'default_format' => 'INTERNATIONAL',
                    'maximum_length' => 25
                )
            ),
            'zip' => array(
                'parse' => array(
                    'enabled' => true
                )
            ),
            'url' => array(
                'parse' => array(
                    'enabled' => true,
                    'format' => true,
                    'validate' => true,
                    'strip_query' => false,
                    'strip_fragment' => false,
                    'lowercase_host' => true
                )
            ),
            'command' => array(
                'register' => array(
                    'field' => array(
                        'predefined' => array(
                            'contact_type',
                            'category_id'
                        ),
                        'visible' => array(
                            'tags',
                            'person_gender',
                            'person_first_name',
                            'person_last_name',
                            'company_name',
                            'company_department',
                            'communication_email',
                            'communication_phone',
                            'address_street',
                            'address_zip',
                            'address_city',
                            'address_country_code',
                            'note',
                            'extra_fields'
                        ),
                        'required' => array(
                            'person_gender',
                            'person_last_name',
                            'company_name'
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
                            'contact_status',
                            'category_name'
                        ),
                        'tags' => array(
                        )
                    ),
                    'publish' => array(
                        'activation' => 'admin'
                    )
                )
            ),
            'pattern' => array(
                'form' => array(
                    'contact' => array(
                        'field' => array(
                            'predefined' => array(
                                'contact_type',
                                'category_id'
                            ),
                            'visible' => array(
                                'tags',
                                'person_gender',
                                'person_first_name',
                                'person_last_name',
                                'company_name',
                                'company_department',
                                'communication_email',
                                'communication_phone',
                                'address_street',
                                'address_zip',
                                'address_city',
                                'address_country_code',
                                'note',
                                'extra_fields'
                            ),
                            'required' => array(
                                'person_gender',
                                'person_last_name',
                                'company_name'
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
                                'contact_status',
                                'category_name'
                            ),
                            'tags' => array(
                            )
                        )
                    )
                )
            ),
            'dialog' => array(
                'contact' => array(
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
                                'person' => '/admin/contact/person/edit',
                                'tag' => '/admin/contact/tag/list',
                                'category' => '/admin/contact/category/list',
                                'title' => '/admin/contact/title/list',
                                'list' => '/admin/contact/list'
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
                                'person' => '/admin/contact/person/edit',
                                'company' => '/admin/contact/company/edit',
                                'tag' => '/admin/contact/tag/list',
                                'category' => '/admin/contact/category/list',
                                'list' => '/admin/contact/list'
                            )
                        )
                    )
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
