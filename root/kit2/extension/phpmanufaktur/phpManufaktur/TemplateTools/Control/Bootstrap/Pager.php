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

class Pager
{
    protected $app = null;
    protected static $options = array(
        'previous' => true,
        'next' => true,
        'center' => true,
        'visibility' => array(
            'public'
        ),
        'template_directory' => '@pattern/bootstrap/function/pager/'
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
        if (isset($options['previous']) && is_bool($options['previous'])) {
            self::$options['previous'] = $options['previous'];
        }
        if (isset($options['next']) && is_bool($options['next'])) {
            self::$options['next'] = $options['next'];
        }
        if (isset($options['center']) && is_bool($options['center'])) {
            self::$options['center'] = $options['center'];
        }
        if (isset($options['template_directory']) && !empty($options['template_directory'])) {
            self::$options['template_directory'] = rtrim($options['template_directory'], '/').'/';
        }
    }

    /**
     * Get information about the given PAGE_ID
     *
     * @param integer $page_id
     */
    protected function getPageInformation($page_id)
    {
        $SQL = "SELECT `page_id`, `menu_title`, `page_title`, `description` FROM `".
            CMS_TABLE_PREFIX."pages` WHERE `page_id`=".$page_id;
        $page = $this->app['db']->fetchAssoc($SQL);
        if (isset($page['page_id'])) {
            $page['page_url'] = $this->app['cms']->page_url($page_id, false, false);
        }
        return $page;
    }

    /**
     * Create a Bootstrap Pager to step through the site
     *
     * @param array $options
     * @param boolean $prompt
     * @return string
     */
    public function Pager($options=array(), $prompt=true)
    {
        // first check the options
        $this->checkOptions($options);

        if (!self::$options['previous'] && !self::$options['next']) {
            // noting todo ...
            $result = '';
        }
        else {
            $previous_id = (self::$options['previous']) ?
                $this->app['cms']->page_previous_id(PAGE_ID, self::$options['visibility'], false) : -1;
            $next_id = (self::$options['next']) ?
                $this->app['cms']->page_next_id(PAGE_ID, self::$options['visibility'], false) : -1;

            $previous = array();
            if ($previous_id > -1) {
                $previous = $this->getPageInformation($previous_id);
            }
            $next = array();
            if ($next_id > -1) {
                $next = $this->getPageInformation($next_id);
            }
            $result = $this->app['twig']->render(
                self::$options['template_directory'].'pager.twig',
                array(
                    'center' => self::$options['center'],
                    'previous' => array(
                        'id' => $previous_id,
                        'enabled' => self::$options['previous'],
                        'page' => $previous
                    ),
                    'next' => array(
                        'id' => $next_id,
                        'enabled' => self::$options['next'],
                        'page' => $next
                    )
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
