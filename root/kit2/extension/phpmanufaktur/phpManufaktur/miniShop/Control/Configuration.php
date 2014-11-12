<?php

/**
 * miniShop
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de/miniShop
 * @copyright 2014 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\miniShop\Control;

use Silex\Application;
use phpManufaktur\Basic\Data\CMS\Settings;

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
        self::$config_path = MANUFAKTUR_PATH.'/miniShop/config.minishop.json';
        $this->readConfiguration();
    }


    /**
     * Return the default configuration array for the miniShop
     *
     * @return array
     */
    public function getDefaultConfigArray()
    {
        $cmsSettings = new Settings($this->app);
        $default_language = $cmsSettings->getSetting('default_language');

        return array(
            'nav_tabs' => array(
                'order' => array(
                    'orders',
                    'contact_list',
                    'contact_edit',
                    'article',
                    'group',
                    'base',
                    'about'
                ),
                'default' => 'about'
            ),
            'locale' => array(
                'EN',
                'DE'
            ),
            'currency' => array(
                'EUR' => array(
                    'name' => 'Euro',
                    'iso' => 'EUR',
                    'symbol' => '€'
                ),
                'USD' => array(
                    'name' => 'US-Dollar',
                    'iso' => 'USD',
                    'symbol' => '$'
                ),
                'CHF' => array(
                    'name' => 'Schweizer Franken',
                    'iso' => 'CHF',
                    'symbol' => 'SFr.'
                ),
                'GBP' => array(
                    'name' => 'Pound Sterling',
                    'iso' => 'GBP',
                    'symbol' => '£'
                )
            ),
            'images' => array(
                'directory' => array(
                    'start' => '/media/public',
                    'select' => '/media/public/shop'
                 ),
                'extension' => array(
                    '*.jpg',
                    '*.jpeg',
                    '*.png',
                    '*.JPG',
                    '*.JPEG',
                    '*.PNG'
                )
            ),
            'permanentlink' => array(
                'directory' => '/shop'
            ),
            'libraries' => array(
                'enabled' => true,
                'jquery' => array(
                    'jquery/jquery/latest/jquery.min.js',
                    'bootstrap/latest/js/bootstrap.min.js',
                    'jquery/lightbox/latest/js/lightbox.min.js'
                ),
                'css' => array(
                    'bootstrap/latest/css/bootstrap.min.css',
                    'font-awesome/latest/css/font-awesome.min.css',
                    'jquery/lightbox/latest/css/lightbox.css'
                )
            ),
            'banking_account' => array(
                'owner' => '',
                'bank_name' => '',
                'iban' => '',
                'bic' => '',
                'reason' => 'miniShop Order #%order_id%/%date%'
            ),
            'paypal' => array(
                'sandbox' => false,
                'email' => '',
                'token' => '',
                'logo_url' => ''
            ),
            'basket' => array(
                'lifetime_hours' => 6
            ),
            'order' => array(
                'admin' => array(
                    'list' => array(
                        'max_days' => 30
                    )
                )
            ),
            'contact' => array(
                'field' => array(
                    'predefined' => array(
                        'contact_type'
                    ),
                    'visible' => array(
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
                        'extra_fields',
                        'special_fields'
                    ),
                    'required' => array(
                        'person_gender',
                        'person_last_name',
                        'company_name',
                        'address_street',
                        'address_zip',
                        'address_city',
                        'address_country_code',
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
                    ),
                    'default_value' => array(
                        'contact_type' => 'PERSON',
                        'person_gender' => 'MALE',
                        'address_country_code' => 'DE'
                    )
                ),
                'admin' => array(
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
                                'person' => '/admin/minishop/contact/person/edit',
                                'tag' => '/admin/minishop/contact/tag/list',
                                'category' => '/admin/minishop/contact/category/list',
                                'title' => '/admin/minishop/contact/title/list',
                                'list' => '/admin/minishop/contact/list'
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
                                'company' => '/admin/minishop/contact/company/edit',
                                'tag' => '/admin/minishop/contact/tag/list',
                                'category' => '/admin/minishop/contact/category/list',
                                'list' => '/admin/minishop/contact/list'
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
        if (!file_exists(self::$config_path)) {
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
