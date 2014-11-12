<?php

/**
 * TemplateTools
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de/TemplateTools
 * @copyright 2014 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\TemplateTools\Control;

use Silex\Application;
use phpManufaktur\TemplateTools\Control\Bootstrap\Nav;
use phpManufaktur\TemplateTools\Control\Bootstrap\Breadcrumb;
use phpManufaktur\TemplateTools\Control\Bootstrap\Pager;
use phpManufaktur\TemplateTools\Control\Bootstrap\Alert;
use phpManufaktur\TemplateTools\Control\Classic\SocialSharingButtons;

class Bootstrap
{
    protected $app = null;

    /**
     * Constructor
     *
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * Create a unsorted list for the Bootstrap nav components
     *
     * @param string $class
     * @param array $options
     * @param boolean $prompt
     * @return string
     */
    public function nav($class='nav nav-tabs', $options=array(), $prompt=true)
    {
        $Nav = new Nav($this->app);
        return $Nav->nav($class, $options, $prompt);
    }

    /**
     * Create a breadcrumb navigation
     *
     * @param array $options
     * @param boolean $prompt
     * @return string breadcrumb
     */
    public function breadcrumb($options=array(), $prompt=true)
    {
        $Breadcrumb = new Breadcrumb($this->app);
        return $Breadcrumb->breadcrumb($options, $prompt);
    }

    /**
     * Create a Bootstrap Pager to step through the site
     *
     * @param array $options
     * @param boolean $prompt
     * @return string
     */
    public function pager($options=array(), $prompt=true)
    {
        $Pager = new Pager($this->app);
        return $Pager->pager($options, $prompt);
    }

    /**
     * Use the Bootstrap Alert Component to alert a message
     *
     * @param string $message
     * @param array $options
     * @param boolean $prompt
     * @return string rendered alert
     */
    public function alert($message='', $options=array(), $prompt=true)
    {
        $Alert = new Alert($this->app);
        return $Alert->alert($message, $options, $prompt);
    }

    /**
     * Create responsive social sharing buttons
     *
     * @param array $buttons
     * @param array $options
     * @param boolean $prompt
     * @return string
     */
    public function social_sharing_buttons($buttons=array(), $options=array(), $prompt=true)
    {
        $SocialSharingButtons = new SocialSharingButtons($this->app);
        return $SocialSharingButtons->social_sharing_buttons($buttons, $options, $prompt);
    }

    /**
     * Return Sitemap Links in Columns for the given $menu
     *
     * @param integer|string $menu
     * @param array $options
     * @return NULL
     */
    public function sitelinks_navigation($menu, $options=array(), $prompt=true)
    {
        // we are using the Classic sitelinks_navigation() function!
        if (!isset($options['template_directory'])) {
            // the only difference are the used templates ...
            $options['template_directory'] = '@pattern/bootstrap/function/sitelinks/';
        }
        return $this->app['classic']->sitelinks_navigation($menu, $options, $prompt);
    }

    /**
     * Return a locale navigation for the current page tree
     *
     * @param array $options
     * @param boolean $prompt
     * @throws \InvalidArgumentException
     * @return string
     */
    public function locale_navigation($options=array(), $prompt=true)
    {
        // we are using the Classic language_navigation() function!
        if (!isset($options['template_directory'])) {
            // the only difference are the used templates ...
            $options['template_directory'] = '@pattern/bootstrap/function/locale/';
        }
        return $this->app['classic']->locale_navigation($options, $prompt);
    }
}
