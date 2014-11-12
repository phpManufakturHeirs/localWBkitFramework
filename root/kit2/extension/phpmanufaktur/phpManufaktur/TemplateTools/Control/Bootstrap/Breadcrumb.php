<?php

/**
 * TemplateTools
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de/TemplateTools
 * @copyright 2014 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\TemplateTools\Control\Bootstrap;

use Silex\Application;
use phpManufaktur\flexContent\Data\Content\Content as flexContentData;
use phpManufaktur\flexContent\Control\Command\Tools as flexContentTools;

class Breadcrumb
{
    protected $app = null;
    protected static $options = array(
        'link_home' => true,
        'menu_level' => 0,
        'li_before' => null,
        'li_after' => null,
        'template_directory' => '@pattern/bootstrap/function/breadcrumb/'
    );

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
     * Check the $options and set self::$options
     *
     * @param array $options
     */
    protected function checkOptions($options)
    {
        if (isset($options['link_home']) && is_bool($options['link_home'])) {
            self::$options['link_home'] = $options['link_home'];
        }
        if (isset($options['menu_level']) && is_numeric($options['menu_level']) && ($options['menu_level'] > 0)) {
            self::$options['menu_level'] = $options['menu_level'];
        }
        if (isset($options['template_directory']) && !empty($options['template_directory'])) {
            self::$options['template_directory'] = rtrim($options['template_directory'], '/').'/';
        }
        if (isset($options['li_before']) && !empty($options['li_before'])) {
            self::$options['li_before'] = $options['li_before'];
        }
        if (isset($options['li_after']) && !empty($options['li_after'])) {
            self::$options['li_after'] = $options['li_after'];
        }
    }

    /**
     * Get information about the given PAGE_ID
     *
     * @param integer $page_id
     */
    protected function getPageInformation($page_id)
    {
        $SQL = "SELECT `page_id`, `link`, `menu_title`, `page_title`, `description` FROM `".
            CMS_TABLE_PREFIX."pages` WHERE `page_id`=".$page_id;
        $result = $this->app['db']->fetchAssoc($SQL);
        if ((CMS_TYPE === 'LEPTON') && version_compare(CMS_VERSION, '1.3.1', '==')) {
            $result['menu_title'] = utf8_encode($result['menu_title']);
            $result['page_title'] = utf8_encode($result['page_title']);
        }
        return $result;
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
        if (PAGE_ID < 1) {
            // don't show the breadcrump i.e. at search result pages ...
            return '';
        }

        // first check the $options
        $this->checkOptions($options);

        $SQL = "SELECT `page_trail` FROM `".CMS_TABLE_PREFIX."pages` WHERE `page_id`=".PAGE_ID;
        $page_trails = $this->app['db']->fetchColumn($SQL);

        $breadcrumbs = '';
        $result = '';

        if (!is_null($page_trails) && (strlen($page_trails) > 0)) {
            // create the breadcrumb navigation
            if (strpos($page_trails, ',')) {
                $trails = explode(',', $page_trails);
            }
            else {
                $trails = array(intval($page_trails));
            }
            $level = -1;
            foreach ($trails as $trail) {
                // walk through the trails of the current page
                $level++;
                if ($level < self::$options['menu_level']) {
                    // skip until reach the wanted level
                    continue;
                }
                $page = $this->getPageInformation($trail);
                $active = ($trail == PAGE_ID);
                if ($active && (defined('EXTRA_FLEXCONTENT_ID') && (EXTRA_FLEXCONTENT_ID > 0))) {
                    $active = false;
                }
                $breadcrumbs .= $this->app['twig']->render(
                    self::$options['template_directory'].'li.twig',
                    array(
                        'active' => $active,
                        'menu_title' => $page['menu_title'],
                        'page_url' => $this->app['cms']->page_url($trail, true, false),
                        'page_title' => $page['page_title'],
                        'page_description' => $page['description']
                    )
                );
            }

            // check for EXTRA_FLEXCONTENT_ID
            if (defined('EXTRA_FLEXCONTENT_ID') && (EXTRA_FLEXCONTENT_ID > 0)) {
                $flexContentData = new flexContentData($this->app);
                if (false !== ($content = $flexContentData->select(EXTRA_FLEXCONTENT_ID))) {
                    $flexContentTools = new flexContentTools($this->app);
                    $base_url = $flexContentTools->getPermalinkBaseURL($content['language']);
                    $url = $base_url.'/'.$content['permalink'];
                    $breadcrumbs .= $this->app['twig']->render(
                        self::$options['template_directory'].'li.twig',
                        array(
                            'active' => true,
                            'menu_title' => $content['title'],
                            'page_url' => $url,
                            'page_title' => $content['title'],
                            'page_description' => $content['description']
                        )
                    );
                }
            }

            $result = $this->app['twig']->render(
                self::$options['template_directory'].'ol.twig',
                array(
                    'link_home' => self::$options['link_home'],
                    'breadcrumbs' => $breadcrumbs,
                    'active' => false,
                    'li_before' => self::$options['li_before'],
                    'li_after' => self::$options['li_after']
                )
            );
        }
        elseif (self::$options['link_home']) {
            $result = $this->app['twig']->render(
                self::$options['template_directory'].'ol.twig',
                array(
                    'link_home' => self::$options['link_home'],
                    'breadcrumbs' => $breadcrumbs,
                    'active' => true,
                    'li_before' => self::$options['li_before'],
                    'li_after' => self::$options['li_after']
                )
            );
        }

        if ($prompt) {
            echo $result;
        }
        else {
            return $result;
        }
    }
}
