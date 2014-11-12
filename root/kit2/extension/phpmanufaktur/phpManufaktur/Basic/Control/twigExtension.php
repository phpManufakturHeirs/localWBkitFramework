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
        if (defined('CMS_PATH')) {
            return array(
                'CMS_MEDIA_URL' => CMS_MEDIA_URL,
                'CMS_TYPE' => CMS_TYPE,
                'CMS_PATH' => CMS_PATH,
                'CMS_URL' => CMS_URL,
                'CMS_VERSION' => CMS_VERSION,
                'FRAMEWORK_MEDIA_URL' => FRAMEWORK_MEDIA_URL,
                'FRAMEWORK_MEDIA_PROTECTED_URL' => FRAMEWORK_MEDIA_PROTECTED_URL,
                'FRAMEWORK_PATH' => FRAMEWORK_PATH,
                'FRAMEWORK_URL' => FRAMEWORK_URL,
                'FRAMEWORK_TEMPLATES' => explode(',', FRAMEWORK_TEMPLATES),
                'LIBRARY_URL' => LIBRARY_URL,
                'MANUFAKTUR_PATH' => MANUFAKTUR_PATH,
                'MANUFAKTUR_URL' => MANUFAKTUR_URL,
                'SERVER_EMAIL_ADDRESS' => SERVER_EMAIL_ADDRESS,
                'SERVER_EMAIL_NAME' => SERVER_EMAIL_NAME,
                'THIRDPARTY_PATH' => THIRDPARTY_PATH,
                'THIRDPARTY_URL' => THIRDPARTY_URL
            );
        }
        else {
            return array(
                'FRAMEWORK_MEDIA_URL' => FRAMEWORK_MEDIA_URL,
                'FRAMEWORK_MEDIA_PROTECTED_URL' => FRAMEWORK_MEDIA_PROTECTED_URL,
                'FRAMEWORK_PATH' => FRAMEWORK_PATH,
                'FRAMEWORK_URL' => FRAMEWORK_URL,
                'FRAMEWORK_TEMPLATES' => explode(',', FRAMEWORK_TEMPLATES),
                'LIBRARY_URL' => LIBRARY_URL,
                'MANUFAKTUR_PATH' => MANUFAKTUR_PATH,
                'MANUFAKTUR_URL' => MANUFAKTUR_URL,
                'THIRDPARTY_PATH' => THIRDPARTY_PATH,
                'THIRDPARTY_URL' => THIRDPARTY_URL
            );
        }
    }


    /**
     *
     * @see Twig_Extension::getFunctions()
     */
    public function getFunctions ()
    {
        return array(
            'isAuthenticated' => new \Twig_Function_Method($this, 'isAuthenticated'),
            'getUserDisplayName' => new \Twig_Function_Method($this, 'getUserDisplayName'),
            'getUserRolesEntryPoints' => new \Twig_Function_Method($this, 'getUserRolesEntryPoints'),
            'template_file' => new \Twig_Function_Method($this, 'template_file'),
            'getTemplateFile' => new \Twig_Function_Method($this, 'getTemplateFile'),
            'kitCommandParser' => new \Twig_Function_Method($this, 'kitCommandParser'),
            'kitCommand' => new \Twig_Function_Method($this, 'kitCommand'),
            'reCaptcha' => new \Twig_Function_Method($this, 'reCaptcha'),
            'reCaptchaIsActive' => new \Twig_Function_Method($this, 'reCaptchaIsActive'),
            'mailHide' => new \Twig_Function_Method($this, 'mailHide'),
            'mailHideIsActive' => new \Twig_Function_Method($this, 'mailHideIsActive'),
            'fileExists' => new \Twig_Function_Method($this, 'fileExists'),
            'image' => new \Twig_Function_Method($this, 'image'),
            'markdown' => new \Twig_Function_Method($this, 'functionMarkdown')
        );
    }

    /**
     * (non-PHPdoc)
     * @see Twig_Extension::getFilters()
     */
    public function getFilters()
    {
        return array(
            'ellipsis' => new \Twig_Filter_Method($this, 'filterEllipsis'),
            'markdown' => new \Twig_Filter_Method($this, 'filterMarkdown')
        );
    }

    /**
     * Check if the user is authenticated
     *
     * @return boolean
     */
    function isAuthenticated ()
    {
        if (isset($this->app['account'])) {
            return $this->app['account']->isAuthenticated();
        }
        else {
            // $app['account'] seems not initialized - i.e. using "migrate.php"
            return false;
        }
    }

    /**
     * Get the display name of the authenticated user
     *
     * @throws Twig_Error
     * @return string Ambigous string, mixed>
     */
    function getUserDisplayName()
    {
        if (isset($this->app['account'])) {
            return $this->app['account']->getDisplayName();
        }
        else {
            return 'ANONYMOUS';
        }
    }

    /**
     * Get the User Roles Entry Points as array
     *
     * @return array
     */
    function getUserRolesEntryPoints()
    {
        if (isset($this->app['account'])) {
            return $this->app['account']->getUserRolesEntryPoints();
        }
        else {
            return array();
        }
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
        if (isset($this->app['recaptcha'])) {
            return $this->app['recaptcha']->getHTML($theme, $widget);
        }
        else {
            return null;
        }
    }

    /**
     * Check if the ReCaptcha Service is active or not
     *
     * @param Application $app
     * @return boolean
     */
    function reCaptchaIsActive()
    {
        if (isset($this->app['recaptcha'])) {
            return $this->app['recaptcha']->isActive();
        }
        else {
            return false;
        }
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
        if (isset($this->app['recaptcha'])) {
            return $this->app['recaptcha']->MailHideGetHTML($email, $title, $class, $mailto);
        }
        else {
            return null;
        }
    }

    /**
     * Check if the MailHide Service is active or not
     *
     */
    public function MailHideIsActive()
    {
        if (isset($this->app['recaptcha'])) {
            return $this->app['recaptcha']->MailHideIsActive();
        }
        else {
            return false;
        }
    }

    /**
     * Check if the given file exists
     *
     * @param string $file absolute path
     * @return boolean
     */
    public function fileExists($file)
    {
        return $this->app['filesystem']->exists($file);
    }

    /**
     * Return a array with the URL source, width and height of the given image.
     * If $max_width or $max_height ar not NULL a new image will be resampled.
     *
     * @param string $relative_image_path relative path to $parent_path
     * @param integer $max_width of the image in pixel
     * @param integer $max_height of the image in pixel
     * @param string $parent_path FRAMEWORK_PATH by default
     * @param string $parent_url FRAMEWORK_URL by default
     * @param boolean $cache by default cache the file
     * @return array with src, width, height and path
     */
    public function image($relative_image_path, $max_width=null, $max_height=null, $parent_path=FRAMEWORK_PATH, $parent_url=FRAMEWORK_URL, $cache=true)
    {
        $relative_image_path = $this->app['utils']->sanitizePath($relative_image_path);
        if ($relative_image_path[0] != '/') {
            $relative_image_path = '/'.$relative_image_path;
        }

        $parent_path = $this->app['utils']->sanitizePath($parent_path);

        if ($parent_url[strlen($parent_url)-1] == '/') {
            $parent_url = substr($parent_url, 0, -1);
        }

        if (!$this->app['filesystem']->exists($parent_path.$relative_image_path)) {
            $this->app['monolog']->addDebug("The image $parent_path.$relative_image_path does not exists!",
                array(__METHOD__, __LINE__));
            return array(
                'src' => $parent_url.$relative_image_path,
                'width' => '100%',
                'height' => '100%'
            );
        }

        $image_info = $this->app['image']->getImageInfo($parent_path.$relative_image_path);

        if ((!is_null($max_width) && ($image_info['width'] > $max_width)) ||
            (!is_null($max_height) && ($image_info['height'] > $max_height))) {

            // optimize the image
            $new_size = $this->app['image']->reCalculateImage($image_info['width'], $image_info['height'], $max_width, $max_height);

            // create a new filename
            $pathinfo = pathinfo($relative_image_path);

            $new_relative_image_path = sprintf('%s/%s_%dx%d.%s', $pathinfo['dirname'],
                $pathinfo['filename'], $new_size['width'], $new_size['height'], $pathinfo['extension']);

            $tweak_path = FRAMEWORK_PATH.'/media/twig';
            $tweak_url = FRAMEWORK_URL.'/media/twig';

            if (!$cache || !$this->app['filesystem']->exists($tweak_path.$new_relative_image_path) ||
                (filemtime($tweak_path.$new_relative_image_path) != $image_info['last_modified'])) {
                // create a resampled image
                $this->app['image']->resampleImage($parent_path.$relative_image_path, $image_info['image_type'],
                    $image_info['width'], $image_info['height'], $tweak_path.$new_relative_image_path,
                    $new_size['width'], $new_size['height']);
            }

            return array(
                'path' => $tweak_path.$new_relative_image_path,
                'src' => $tweak_url.$new_relative_image_path,
                'width' => $new_size['width'],
                'height' => $new_size['height']
            );
        }
        else {
            // nothing to do ...
            return array(
                'path' => $parent_path.$relative_image_path,
                'src' => $parent_url.$relative_image_path,
                'width' => $image_info['width'],
                'height' => $image_info['height']
            );
        }
    }

    /**
     * Ellipsis function - shorten the given $text to $length at the nearest
     * space and add three dots at the end ...
     *
     * @param string $text
     * @param number $length
     * @param boolean $striptags remove HTML tags by default
     * @param boolean $htmlpurifier use HTML Purifier (false by default, ignored if striptags=true)
     * @return string
     */
    public function filterEllipsis($text, $length=100, $striptags=true, $htmlpurifier=false) {
        return $this->app['utils']->Ellipsis($text, $length, $striptags, $htmlpurifier);
    }

    /**
     * Return the given markdown $text as HTML
     *
     * @param string $text
     * @param boolean $extra
     */
    public function filterMarkdown($text, $extra=true)
    {
        return $this->app['markdown']->html($text, $extra, false);
    }

    /**
     * Return the given markdown $text as HTML
     *
     * @param string $text
     * @param boolean $extra
     */
    public function functionMarkdown($text, $extra)
    {
        return $this->app['markdown']->html($text, $extra, false);
    }
}

