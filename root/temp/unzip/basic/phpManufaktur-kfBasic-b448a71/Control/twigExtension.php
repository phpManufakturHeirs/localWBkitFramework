<?php

/**
 * kitFramework
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de
 * @copyright 2013 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\Basic\Control;

use Twig_Extension;
use Twig_SimpleFunction;
use Silex\Application;

/**
 * The Twig extension class for the kitFramework
 *
 * @author Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 *
 */
class twigExtension extends Twig_Extension
{

    protected $app = null;

    /**
     * Constructor
     *
     * @param Application $app
     */
    public function __construct (Application $app)
    {
        $this->app = $app;
    }

    /**
     *
     * @see Twig_ExtensionInterface::getName()
     */
    public function getName ()
    {
        return 'kitFramework';
    } // getName()

    /**
     *
     * @see Twig_Extension::getGlobals()
     */
    public function getGlobals ()
    {
        return array(
            'CMS_ADMIN_URL' => CMS_ADMIN_URL,
            'CMS_MEDIA_URL' => CMS_MEDIA_URL,
            'CMS_TYPE' => CMS_TYPE,
            'CMS_URL' => CMS_URL,
            'CMS_VERSION' => CMS_VERSION,
            'FRAMEWORK_MEDIA_URL' => FRAMEWORK_MEDIA_URL,
            'FRAMEWORK_MEDIA_PROTECTED_URL' => FRAMEWORK_MEDIA_PROTECTED_URL,
            'FRAMEWORK_PATH' => FRAMEWORK_PATH,
            'FRAMEWORK_URL' => FRAMEWORK_URL,
            'MANUFAKTUR_PATH' => MANUFAKTUR_PATH,
            'MANUFAKTUR_URL' => MANUFAKTUR_URL,
            'THIRDPARTY_PATH' => THIRDPARTY_PATH,
            'THIRDPARTY_URL' => THIRDPARTY_URL,

        );
    } // getGlobals()


    /**
     *
     * @see Twig_Extension::getFunctions()
     */
    public function getFunctions ()
    {
        return array(
            'isAuthenticated' => new \Twig_Function_Method($this, 'isAuthenticated'),
            'getUserDisplayName' => new \Twig_Function_Method($this, 'getUserDisplayName'),
            'template_file' => new \Twig_Function_Method($this, 'template_file'),
            'getTemplateFile' => new \Twig_Function_Method($this, 'getTemplateFile'),
            'kitCommandParser' => new \Twig_Function_Method($this, 'kitCommandParser'),
            'kitCommand' => new \Twig_Function_Method($this, 'kitCommand'),
            'reCaptcha' => new \Twig_Function_Method($this, 'reCaptcha'),
            'reCaptchaIsActive' => new \Twig_Function_Method($this, 'reCaptchaIsActive'),
            'mailHide' => new \Twig_Function_Method($this, 'mailHide'),
            'mailHideIsActive' => new \Twig_Function_Method($this, 'mailHideIsActive')
        );
    }


    /**
     * Check if the user is authenticated
     *
     * @return boolean
     */
    function isAuthenticated ()
    {
        return $this->app['account']->isAuthenticated();
    }

    /**
     * Get the display name of the authenticated user
     *
     * @throws Twig_Error
     * @return string Ambigous string, mixed>
     */
    function getUserDisplayName()
    {
        return $this->app['account']->getDisplayName();
    }

    /**
     * Get the template depending on namespace and the framework settings for the template itself
     *
     * @deprecated template_file() is deprecated since kfBasic 0.33, use getTemplateFile() instead
     */
    function template_file($template_namespace, $template_file, $preferred_template='')
    {
        trigger_error('template_file() is deprecated since kfBasic 0.33, use getTemplateFile() instead', E_USER_DEPRECATED);
        return $this->app['utils']->getTemplateFile($template_namespace, $template_file, $preferred_template);
    }

    /**
     * Get the template depending on namespace and the framework settings for the template itself
     *
     * @param string $template_namespace
     * @param string $template_file
     * @param string $preferred_template
     * @return string
     */
    function getTemplateFile($template_namespace, $template_file, $preferred_template='')
    {
        return $this->app['utils']->getTemplateFile($template_namespace, $template_file, $preferred_template);
    }

    /**
     * Parse the content for kitCommands and execute them
     *
     * @param Application $app
     * @param string $content
     * @return string parsed content
     */
    function kitCommandParser($content)
    {
        return $this->app['utils']->parseKITcommand($content);
    }

    /**
     * Execute a kitCommand with the given parameter
     *
     * @param Application $app
     * @param string $command
     * @param array $parameter
     */
    function kitCommand($command, array $parameter=array())
    {
        return $this->app['utils']->execKITcommand($command, $parameter);
    }

    /**
     * Return a ReCaptcha dialog if the ReCaptcha service is active
     *
     * @param Application $app
     * @param string theme to use (override global settings)
     * @param string widget to use for 'custom' theme
     *
     * @link https://developers.google.com/recaptcha/docs/customization
     */
    function reCaptcha($theme=null, $widget=null)
    {
        return $this->app['recaptcha']->getHTML($theme, $widget);
    }

    /**
     * Check if the ReCaptcha Service is active or not
     *
     * @param Application $app
     * @return boolean
     */
    function reCaptchaIsActive()
    {
        return $this->app['recaptcha']->isActive();
    }

    /**
     * Return a MailHide link if the service is active. Otherwise, if $mailto is true,
     * return a complete mailto link. You can set an optional $class for this mailto link
     *
     * @param string $email
     * @param string $title
     * @param boolean $mailto
     * @param string $class
     */
    public function MailHide($email, $title='', $class='', $mailto=true)
    {
        return $this->app['recaptcha']->MailHideGetHTML($email, $title, $class, $mailto);
    }

    /**
     * Check if the MailHide Service is active or not
     *
     */
    public function MailHideIsActive()
    {
        return $this->app['recaptcha']->MailHideIsActive();
    }

} // class twigExtension

