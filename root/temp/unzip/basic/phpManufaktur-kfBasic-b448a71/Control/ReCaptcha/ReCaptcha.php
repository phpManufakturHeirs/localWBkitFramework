<?php

/**
 * kitFramework:Basic
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de
 * @copyright 2013 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\Basic\Control\ReCaptcha;

use Silex\Application;

require_once MANUFAKTUR_PATH.'/Basic/Control/ReCaptcha/recaptchalib.php';

class ReCaptcha
{
    protected $app = null;
    protected static $is_enabled = null;
    protected static $is_active = null;
    protected static $config = null;
    protected static $public_key = null;
    protected static $private_key = null;
    protected static $last_error = null;
    protected static $use_ssl = null;
    protected static $theme = null;
    protected static $custom_theme_widget = null;

    protected static $mailhide_is_enabled = null;
    protected static $mailhide_is_active = null;
    protected static $mailhide_public_key = null;
    protected static $mailhide_private_key = null;

    /**
     * Constructor
     *
     * @param Application $app
     */
    public function __construct(Application $app) {
        $this->app = $app;
        $this->ReadConfigurationFile();
    }

    /**
     * Check if the ReCaptcha Service is enabled
     *
     * @return boolean
     */
    public function isEnabled()
    {
        return (bool) self::$is_enabled;
    }

    /**
     * Activate or deactivate the ReCaptcha Service
     *
     * @param boolean $boolean
     */
    public function Activate($boolean)
    {
        self::$is_active = (bool) $boolean;
    }

    /**
     * Check if ReCaptcha Service is active
     *
     * @return boolean
     */
    public function isActive()
    {
        return (bool) (self::$is_enabled && self::$is_active);
    }

    /**
     * Check if the MailHide Service is enabled
     *
     * @return boolean
     */
    public function MailHideIsEnabled()
    {
        return self::$mailhide_is_enabled;
    }

    /**
     * Activate or deactivate the MailHide Service
     *
     * @param boolean $boolean
     */
    public function MailHideActivate($boolean)
    {
        self::$mailhide_is_active = (bool) $boolean;
    }

    /**
     * Check if the MailHide Service is active
     *
     * @return boolean
     */
    public function MailHideIsActive()
    {
        if (!function_exists ("mcrypt_encrypt")) {
            self::$mailhide_is_active = false;
            $this->app['monolog']->addError("To use reCAPTCHA Mailhide, you need to have the mcrypt php module installed.",
                array(__METHOD__, __LINE__));
        }
        return (bool) (self::$mailhide_is_enabled && self::$mailhide_is_active);
    }

    /**
     * Return the last ReCaptcha error or NULL
     *
     * @return NULL|string
     */
    public function getLastError()
    {
        return self::$last_error;
    }

    /**
     * Get the actual ReCaptcha theme
     *
     * @return string
     */
    public function getTheme()
    {
        return self::$theme;
    }

    /**
     * Set the theme for the ReCaptcha
     *
     * @link https://developers.google.com/recaptcha/docs/customization
     * @param string $theme
     */
    public function setTheme($theme)
    {
        self::$theme = $theme;
    }

    /**
     * Get the actual custom theme widget
     *
     * @return string
     */
    public function getCustomThemeWidget()
    {
        return self::$custom_theme_widget;
    }

    /**
     * Set a custom theme widget. You must set the theme to 'custom' to enable
     * this ReCaptcha feature
     *
     * @link https://developers.google.com/recaptcha/docs/customization
     * @param string $custom_theme_widget
     */
    public function setCustomThemeWidget($custom_theme_widget)
    {
        self::$custom_theme_widget = $custom_theme_widget;
    }

    /**
     * Read the configuration file /config/recaptcha.json.
     * Execute CreateConfigurationFile if the config file not exists
     *
     */
    protected function ReadConfigurationFile()
    {
        if (!file_exists(FRAMEWORK_PATH.'/config/recaptcha.json')) {
            $this->CreateConfigurationFile();
        }
        // read the config file
        self::$config = $this->app['utils']->ReadConfiguration(FRAMEWORK_PATH.'/config/recaptcha.json');
        // set the values
        self::$is_enabled = (isset(self::$config['enabled']['recaptcha'])) ? self::$config['enabled']['recaptcha'] : true;
        self::$mailhide_is_enabled = (isset(self::$config['enabled']['mailhide'])) ? self::$config['enabled']['mailhide'] : true;
        self::$private_key = (isset(self::$config['key']['recaptcha']['private'])) ? self::$config['key']['recaptcha']['private'] : null;
        self::$public_key = (isset(self::$config['key']['recaptcha']['public'])) ? self::$config['key']['recaptcha']['public'] : null;
        self::$mailhide_private_key = (isset(self::$config['key']['mailhide']['private'])) ? self::$config['key']['mailhide']['private'] : null;
        self::$mailhide_public_key = (isset(self::$config['key']['mailhide']['public'])) ? self::$config['key']['mailhide']['public'] : null;
        self::$use_ssl = (isset(self::$config['use_ssl'])) ? self::$config['use_ssl'] : false;
        self::$theme = (isset(self::$config['theme'])) ? self::$config['theme'] : 'red';
        self::$custom_theme_widget = (isset(self::$config['custom_theme_widget'])) ? self::$config['custom_theme_widget'] : '';

        if (is_null(self::$private_key) || is_null(self::$public_key)) {
            self::$is_enabled = false;
        }
        if (!self::$is_enabled) {
            self::$mailhide_is_enabled = false;
        }

        // by default both services are active!
        self::$is_active = true;
        self::$mailhide_is_active = true;
    }

    /**
     * Create a /config/recaptcha.json with default values
     *
     */
    protected function CreateConfigurationFile()
    {
        $config = array(
            'enabled' => array(
                'recaptcha' => true,
                'mailhide' => true
                ),
            'key' => array(
                'recaptcha' => array(
                    // global keys generated for repcaptcha.phpmanufaktur.de
                    'public' => '6LctVdgSAAAAAAf0tjxxC2AGdppPV6l3Hxx54W-5',
                    'private' => '6LctVdgSAAAAAL7Ff3D3k0qhnFLXn9FCShKEPoMh'
                    ),
                'mailhide' => array(
                    'public' => '01Yv4ig9TGZTUkjQAJElwLuA==',
                    'private' => 'c03a995093428096066fb54530417030'
                    )
            ),
            'use_ssl' => false,
            'theme' => 'red',
            'custom_theme_widget' => ''
        );
        file_put_contents(FRAMEWORK_PATH.'/config/recaptcha.json', $this->app['utils']->JSONFormat($config));
    }

    /**
     * If the ReCaptcha Service is active, return the ReCaptcha dialog for
     * the usage with Twig
     *
     * @param string theme to use
     * @return string
     * @link https://developers.google.com/recaptcha/docs/customization
     */
    public function getHTML($theme=null, $widget=null)
    {
        if (!self::$is_enabled || !self::$is_active) {
            return '';
        }
        $use_theme = is_null($theme) ? strtolower(self::$theme) : strtolower($theme);
        $custom_theme_widget = is_null($widget) ? self::$custom_theme_widget : strtolower($widget);
        $captcha = ($use_theme == 'custom') ? '' : recaptcha_get_html(self::$public_key, self::$last_error, self::$use_ssl);
        $response = <<<EOD
        <script type="text/javascript">
            var RecaptchaOptions = {
                theme : '$use_theme',
                custom_theme_widget : '$custom_theme_widget'
            };
        </script>
        $captcha
EOD;
        return $response;
    }

    /**
     * Check if the submitted CAPTCHA is valid.
     * Return always TRUE if the Service is not enabled or inactive
     *
     * @return boolean
     * @todo $_SERVER['REMOTE_ADDR'] is not the best solution?!
     */
    public function isValid()
    {
        if (!self::$is_enabled || !self::$is_active) {
            // ReCaptcha is not in use, return TRUE
            return true;
        }

        if (null !== ($response_field = $this->app['request']->get('recaptcha_response_field', null))) {
            // a ReCaptcha was submitted, so check the answer
            $response = recaptcha_check_answer(
                self::$private_key,
                $_SERVER['REMOTE_ADDR'], // $this->app['request']->getClientIP() deliver always 127.0.0.1
                $this->app['request']->get('recaptcha_challenge_field'),
                $this->app['request']->get('recaptcha_response_field'));
            if ($response->is_valid) {
                self::$last_error = null;
                return true;
            }
            else {
                self::$last_error = $response->error;
                return false;
            }
        }
        // in any other case return TRUE to keep the things running
        return true;
    }

    /**
     * Use the MailHide Service if enabled to hide the real email address.
     * If $title isset show the title instead of a shortended email address in
     * the link. If $mailto is true, return a mailto link, otherwise return only
     * the email address. $class adds an optional class to the link.
     *
     * @param string $email
     * @param string $title
     * @param string $class
     * @param boolean $mailto
     * @return unknown
     */
    public function MailHideGetHTML($email, $title='', $class='', $mailto=true)
    {
        if (!self::$mailhide_is_enabled || !self::$mailhide_is_active) {
            if ($mailto) {
                // create a mailto link
                return $this->app['twig']->render($this->app['utils']->getTemplateFile(
                    '@phpManufaktur/Basic/Template',
                    'framework/mailto.twig'),
                    array(
                        'email' => $email,
                        'class' => $class,
                        'title' => $title
                    ));
            }
            else {
                return $email;
            }
        }
        $mailhide_url = recaptcha_mailhide_url(self::$mailhide_public_key, self::$mailhide_private_key, $email);
        return $this->app['twig']->render($this->app['utils']->getTemplateFile(
            '@phpManufaktur/Basic/Template',
            'framework/mailhide.twig'),
            array(
                'email_parts' => _recaptcha_mailhide_email_parts($email),
                'class' => $class,
                'mailhide_url' => $mailhide_url,
                'title' => $title
            ));
    }

}
