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
use phpManufaktur\TemplateTools\Control\Bootstrap\Breadcrumb;
use phpManufaktur\TemplateTools\Control\Bootstrap\Pager;
use phpManufaktur\TemplateTools\Control\Classic\SocialSharingButtons;
use phpManufaktur\TemplateTools\Control\Classic\SitelinksNavigation;
use phpManufaktur\TemplateTools\Control\Classic\LocaleNavigation;

class Classic
{
    protected $app = null;
    protected $BootstrapBreadcrumb = null;
    protected $BootstrapPager = null;

    /**
     * Constructor
     *
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
        $this->BootstrapBreadcrumb = new Breadcrumb($app);
        $this->BootstrapPager = new Pager($app);
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
        // we are using the Bootstrap Breadcrumb function!
        if (!isset($options['template_directory'])) {
            // the only difference are the used templates ...
            $options['template_directory'] = '@pattern/classic/function/breadcrumb/';
        }
        return $this->BootstrapBreadcrumb->breadcrumb($options, $prompt);
    }

    /**
     * Create a Pager to step through the site
     *
     * @param array $options
     * @param boolean $prompt
     * @return string
     */
    public function pager($options=array(), $prompt=true)
    {
        // we are using the Bootstrap Breadcrumb function!
        if (!isset($options['template_directory'])) {
            // the only difference are the used templates ...
            $options['template_directory'] = '@pattern/classic/function/pager/';
        }
        return $this->BootstrapPager->pager($options, $prompt);
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
        $SitelinksNavigation = new SitelinksNavigation($this->app);
        $result = $SitelinksNavigation->sitelinks_navigation($menu, $options);
        if ($prompt) {
            echo $result;
        }
        return $result;
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
        $LocaleNavigation = new LocaleNavigation($this->app);
        return $LocaleNavigation->locale_navigation($options, $prompt);
    }

}
