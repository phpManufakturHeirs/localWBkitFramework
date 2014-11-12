<?php

/**
 * kitFramework
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de
 * @copyright 2014 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\Basic\Control\i18nEditor;

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
        self::$config_path = MANUFAKTUR_PATH.'/Basic/config.i18n.editor.json';
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
            'developer' => array(
                'enabled' => false,
                'source' => array(
                    'copy' => true,
                    'backup' => false,
                    'custom' => false,
                    'extension' => array(

                    )
                )
            ),
            'parse' => array(
                'php' => array(
                    'stop_word' => array(
                        "'choice'",
                        "'hidden'",
                        "'text'",
                        "'textarea'",
                        "'utils'"
                    ),
                    'start_word' => array(
                        'add',
                        'setAlert',
                        'trans'
                    ),
                    'property_word' => array(
                        "'empty_value'",
                        "'label'",
                        "'hint'",
                        "'accounts.list.json'",
                        "'cms.json'",
                        "'config.jsoneditor.json'",
                        "'doctrine.cms.json'",
                        "'framework.json'",
                        "'proxy.json'",
                        "'swift.cms.json'"
                    )
                ),
                'twig' => array(
                    'regex' => array(
                        '/{{([^}]+)\|(?:transchoice\((.*?)\)|transchoice|trans|trans\((.*?)\))(?:\|([^}]+)|\s*)}}/i',
                        '/content\s{0,1}:(.*?)\|trans/i'
                    )
                )
            ),
            'finder' => array(
                'php' => array(
                    'exclude' => array(
                        'file' => array(

                        ),
                        'directory' => array(
                            'Control/CMS',
                            'Control/cURL',
                            'Control/gitHub',
                            'Control/kitSearch',
                            'Control/unZip',
                            'Control/utf-8',
                            'Data/Locale',
                            'Template',
                            'Library',
                            'TemplateTools',
                            'TemplateTools/Examples',
                            'TemplateTools/Pattern',
                            'Include'
                        )
                    )
                ),
                'twig' => array(
                    'exclude' => array(
                        'file' => array(

                        ),
                        'directory' => array(
                            'Control',
                            'Data',
                            'Library',
                            'TemplateTools'
                        )
                    ),
                    'template' => array(
                        'name' => array(
                            'exclude' => array(
                                'TemplateTools'
                            )
                        ),
                        'use_subdirectory' => array(
                            'CommandCollection'
                        )
                    )
                ),
                'locale' => array(
                    'exclude' => array(
                        'file' => array(

                        ),
                        'directory' => array(
                            'Control',
                            'Template',
                            'TemplateTools',
                            'Library',
                            'Data/Setup',
                            'Data/CMS',
                            'Data/Security'
                        )
                    )
                )
            ),
            'translation' => array(
                'locale' => array(
                    'DE',
                    'EN'
                ),
                'system' => array(
                    'Address billing',
                    'Address billing city',
                    'Address billing country code',
                    'Address billing street',
                    'Address billing zip',
                    'Address delivery',
                    'Address delivery country code',
                    'Admin',
                    'Advance payment',
                    'April',
                    'Archived',
                    'AT',
                    'August',
                    'Bad credentials',
                    'baron',
                    'captcha-timeout',
                    'CH',
                    'commercial use only',
                    'Communication cell',
                    'Communication email',
                    'Communication fax',
                    'Communication phone',
                    'Communication url',
                    'Configuration',
                    'Contact settings',
                    'Contact since',
                    'Contact timestamp',
                    'CURRENCY_SYMBOL',
                    'Customer',
                    'DATE_FORMAT',
                    'DATETIME_FORMAT',
                    'De',
                    'DE',
                    'December',
                    'DECIMAL_SEPARATOR',
                    'doc',
                    'doctor',
                    'En',
                    'EN',
                    'earl',
                    'February',
                    'Female',
                    'File path choice',
                    'FR',
                    'Friday',
                    'GALLERY_TYPE_MOBILE',
                    'GALLERY_TYPE_NORMAL',
                    'GALLERY_TYPE_PROFILE',
                    'GALLERY_TYPE_WALL',
                    'help_config_imagetweak_json',
                    'help_gallery_json',
                    "I'm a sample header",
                    'I accept that this software is provided under <a href="http://opensource.org/licenses/MIT" target="_blank">MIT License</a>.',
                    'incorrect-captcha-sol',
                    'Insufficient user role',
                    'Intern',
                    'invalid-request-cookie',
                    'invalid-site-private-key',
                    'January',
                    'July',
                    'June',
                    'Male',
                    'March',
                    'May',
                    'Merchant',
                    'Monday',
                    'Nick name',
                    'NL',
                    'Note content',
                    'November',
                    "o'clock",
                    'October',
                    'On account',
                    'Organization',
                    'Paypal',
                    'Person nick name',
                    'prof',
                    'professor',
                    'Public',
                    'ROLE_EVENT_ADMIN',
                    'ROLE_EVENT_LOCATION',
                    'ROLE_EVENT_ORGANIZER',
                    'ROLE_EVENT_SUBMITTER',
                    'Saturday',
                    'second',
                    'second_fourth',
                    'second_last',
                    'September',
                    'Show the actual articles in a overview',
                    'Show the contents of the category',
                    'Show the description of the category and all assigned articles',
                    'Show the description of the hashtag and all assigned articles',
                    'Show this content as single article',
                    'Stay in touch, read our newsletter!',
                    'Sunday',
                    'Tag (#tag)',
                    'TIME_FORMAT',
                    'third',
                    'This is a sample panel text whith some unnecessary content',
                    "This Tag type is created by the kitCommand 'Comments' and will be set for persons who leave a comment.",
                    'This value is not a valid email address.',
                    'THOUSAND_SEPARATOR',
                    'Thursday',
                    'Tuesday',
                    'Unchecked',
                    'Wednesday',
                    'Weekday',
                    'Weekdays'
                ),
                'ignore' => array(
                    '%path% %error%',
                    '%url%',
                    '&nbsp;',
                    '[%s] %s',
                    '[%file%:%line%] Excel Error: %error%',
                    'Ckeditorfuncnum',
                    'Comment parent',
                    'Count cells',
                    'Create by',
                    'Create type',
                    'cURL error: %error%',
                    'data',
                    'Dbglossary',
                    'Email type',
                    'en',
                    'Event participants canceled',
                    'Event participants pending',
                    'event_date_to',
                    'Extra type description',
                    "Extra type id",
                    "Extra type name",
                    'Extra type type'
                ),
                'file' => array(
                    'save' => true,
                    'backup' => true
                )
            ),
            'editor' => array(
                'sources' => array(
                    'list' => array(
                        'order_by' => 'locale_source_plain',
                        'order_direction' => 'ASC'
                    )
                ),
                'translation' => array(
                    'exclude' => array(
                        'extension' => array(
                            'CKEditor',
                            'Library',
                            'TemplateTools',
                            'Updater'
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
