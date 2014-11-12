<?php

/**
 * TemplateTools
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de/TemplateTools
 * @copyright 2014 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\TemplateTools\Control\Classic;

use Silex\Application;

class SitelinksNavigation
{
    protected $app = null;
    protected static $options = array(
        'level' => 0,
        'strict' => true,
        'visibility' => array(
            'public'
        ),
        'template_directory' => '@pattern/classic/function/sitelinks/'
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
        if (isset($options['level']) && is_numeric($options['level']) && ($options['level'] > 0)) {
            self::$options['level'] = $options['level'];
        }
        if (isset($options['strict']) && is_bool($options['strict'])) {
            self::$options['strict'] = $options['strict'];
        }
        if (isset($options['visibility']) && is_array($options['visibility']) && !empty($options['visibility'])) {
            self::$options['visibility'] = $options['visibility'];
        }
        if (isset($options['template_directory']) && !empty($options['template_directory'])) {
            self::$options['template_directory'] = rtrim($options['template_directory'], '/').'/';
        }
    }

    /**
     * Return Sitemap Links in Columns for the given $menu
     *
     * @param integer|string $menu
     * @param array $options
     * @return NULL
     */
    public function sitelinks_navigation($menu, $options=array())
    {
        $this->checkOptions($options);

        $page_menu = $this->app['cms']->internal_page_menu();

        if (!is_numeric($menu) && is_string($menu) && !empty($menu) && is_array($page_menu)) {
            // try to get the Menu ID by the Menu Name
            $search = $menu;
            $menu = 999; // set Menu ID to a non existing one
            foreach ($page_menu as $id => $name) {
                if (strtolower($search) == strtolower($name)) {
                    $menu = $id;
                    break;
                }
            }
        }

        // get the first page_id for the given menu
        $SQL = "SELECT `page_id` FROM `".CMS_TABLE_PREFIX."pages` WHERE `menu`=$menu ORDER BY `level` ASC, `position` ASC LIMIT 1";
        $page_id  = $this->app['db']->fetchColumn($SQL);

        if (!is_numeric($page_id) || ($page_id < 1)) {
            // no hit - return NULL
            return null;
        }

        $visibility = '';
        foreach (self::$options['visibility'] as $visi) {
            if (!empty($visibility)) {
                $visibility .= ' OR ';
            }
            $visibility .= "`visibility`='$visi'";
        }

        // get the columns
        $SQL = "SELECT `page_id`, `link`, `page_title`, `menu_title`, `description`, `keywords` FROM `".CMS_TABLE_PREFIX."pages` WHERE ".
            "`parent`=$page_id AND ($visibility) ";
        if (self::$options['strict']) {
            $SQL .= "AND `menu`=$menu ";
        }
        $SQL .= "ORDER BY `position` ASC";

        $sitelinks = array();

        $columns = $this->app['db']->fetchAll($SQL);
        if (is_array($columns)) {
            foreach ($columns as $column) {
                $sitelinks['columns'][$column['page_id']] = array(
                    'page_id' => $column['page_id'],
                    'url' => CMS_URL. CMS_PAGES_DIRECTORY. $column['link']. CMS_PAGES_EXTENSION,
                    'menu_title' => $this->app['tools']->unsanitizeText($column['menu_title']),
                    'page_title' => $this->app['tools']->unsanitizeText($column['page_title']),
                    'description' => $this->app['tools']->unsanitizeText($column['description']),
                    'active' => false,
                );
                if ((CMS_TYPE === 'LEPTON') && version_compare(CMS_VERSION, '1.3.1', '==')) {
                    $sitelinks['columns'][$column['page_id']]['menu_title'] = 
                        utf8_encode($sitelinks['columns'][$column['page_id']]['menu_title']);
                    $sitelinks['columns'][$column['page_id']]['page_title'] = 
                        utf8_encode($sitelinks['columns'][$column['page_id']]['page_title']);
                }
                // get the items of the column
                $page_id = $column['page_id'];
                $SQL = "SELECT `page_id`, `link`, `page_title`, `menu_title`, `description`, `keywords` FROM `".CMS_TABLE_PREFIX."pages` WHERE ".
                    "`parent`=$page_id AND ($visibility) ";
                if (self::$options['strict']) {
                    $SQL .= "AND `menu`=$menu ";
                }
                $SQL .= "ORDER BY `position` ASC";
                $items = $this->app['db']->fetchAll($SQL);
                if (is_array($items)) {
                    foreach ($items as $item) {
                        $active = ($item['page_id'] == PAGE_ID);
                        if ($active) {
                            // set also the column to active!
                            $sitelinks['columns'][$column['page_id']]['active'] = true;
                        }
                        $sitelinks['columns'][$column['page_id']]['items'][$item['page_id']] = array(
                            'page_id' => $item['page_id'],
                            'url' => CMS_URL. CMS_PAGES_DIRECTORY. $item['link']. CMS_PAGES_EXTENSION,
                            'menu_title' => $this->app['tools']->unsanitizeText($item['menu_title']),
                            'page_title' => $this->app['tools']->unsanitizeText($item['page_title']),
                            'description' => $this->app['tools']->unsanitizeText($item['description']),
                            'active' => $active
                        );
                        if ((CMS_TYPE === 'LEPTON') && version_compare(CMS_VERSION, '1.3.1', '==')) {
                            $sitelinks['columns'][$column['page_id']]['items'][$item['page_id']]['menu_title'] = 
                                utf8_encode($sitelinks['columns'][$column['page_id']]['items'][$item['page_id']]['menu_title']);
                            $sitelinks['columns'][$column['page_id']]['items'][$item['page_id']]['page_title'] = 
                                utf8_encode($sitelinks['columns'][$column['page_id']]['items'][$item['page_id']]['page_title']);
                        }
                    }
                }
            }
        }

        // render the buttons
        return $this->app['twig']->render(
            self::$options['template_directory'].'sitelinks.twig',
            array('sitelinks' => $sitelinks)
        );
    }
}
