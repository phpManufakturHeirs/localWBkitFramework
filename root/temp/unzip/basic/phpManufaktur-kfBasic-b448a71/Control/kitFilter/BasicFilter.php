<?php

/**
 * kitFramework::kfBasic
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de
 * @copyright 2013 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\Basic\Control\kitFilter;

use Silex\Application;

class BasicFilter
{
    protected $app = null;
    protected static $parameter = null;
    protected static $cms = null;
    protected static $content = null;
    protected static $filter_expression = null;

    public function initClass(Application $app)
    {
        $this->app = $app;

        if (null === (self::$parameter = $app['request']->request->get('parameter', null))) {
            self::$parameter = array();
        }

        if (null === (self::$cms = $app['request']->request->get('cms', null))) {
            throw new \Exception('Missing the CMS information bag!');
        }

        if (null === (self::$content = $app['request']->request->get('content', null))) {
            throw new \Exception('Missing the content for the filter execution.');
        }

        if (null === (self::$filter_expression = $app['request']->request->get('filter_expression', null))) {
            throw new \Exception('Missing the filter expression.');
        }

        if (isset(self::$cms['locale'])) {
            // set the locale from the CMS locale
            $this->app['translator']->setLocale(self::$cms['locale']);
        }
    }

    /**
     * Return the content
     *
     * @return string content
     */
    public function getContent()
    {
        return self::$content;
    }

    /**
     * Set the content
     *
     * @param string $content
     */
    public function setContent($content)
    {
        self::$content = $content;
    }

    /**
     * Remove the filter expression from the content
     *
     */
    protected function removeExpression()
    {
        self::$content = str_replace(self::$filter_expression, '', self::$content);
    }

    /**
     * Get the complete expression
     *
     * @return string expression
     */
    public function getExpression()
    {
        return self::$filter_expression;
    }

    /**
     * Replace $search with $replace in the content.
     *
     * @param string $search
     * @param string $replace
     */
    protected function replace($search, $replace)
    {
        self::$content = str_replace($search, $replace, self::$content);
    }
}
