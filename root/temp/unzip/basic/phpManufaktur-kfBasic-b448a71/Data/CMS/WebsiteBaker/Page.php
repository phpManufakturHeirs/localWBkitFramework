<?php

/**
 * kitFramework::Basic
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de
 * @copyright 2013 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\Basic\Data\CMS\WebsiteBaker;

use Silex\Application;

class Page
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
     * Get the page extension with leading dot, by default '.php'
     *
     * @throws \Exception
     * @return string page extension
     */
    public function getPageExtension()
    {
        try {
          $SQL = "SELECT `value` FROM `".CMS_TABLE_PREFIX."settings` WHERE `name`='page_extension'";
          return $this->app['db']->fetchColumn($SQL);
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Get the page directory with leading slash, by default '/pages' or '/page'
     *
     * @throws \Exception
     * @return string page directory
     */
    public function getPageDirectory()
    {
        try {
            $SQL = "SELECT `value` FROM `".CMS_TABLE_PREFIX."settings` WHERE `name`='pages_directory'";
            return $this->app['db']->fetchColumn($SQL);
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Get the URL of the given page ID. If arguments 'topic_id' or 'post_id'
     * the function will return the URL for the given TOPICS or NEWS article
     *
     * @param integer $page_id
     * @param null|array $arguments
     * @throws \Exception
     * @return string URL of the page
     */
    public function getURL($page_id, $arguments=null)
    {
        try {
            if (isset($arguments['topic_id']) && !is_null($arguments['topic_id'])) {
                // indicate a TOPICS page
                if (!file_exists(CMS_PATH . '/modules/topics/module_settings.php')) {
                    throw new \Exception('A TOPIC_ID was submitted, but the TOPICS addon is not installed at the parent CMS!');
                }
                // indicate a TOPICS page
                $SQL = "SELECT `link` FROM `".CMS_TABLE_PREFIX."mod_topics` WHERE `topic_id`='".$arguments['topic_id']."'";
                $topic_link = $this->app['db']->fetchColumn($SQL);
                // include TOPICS settings
                global $topics_directory;
                include_once CMS_PATH . '/modules/topics/module_settings.php';
                return CMS_URL . $topics_directory . $topic_link . $this->getPageExtension();
            }

            if (isset($arguments['post_id']) && !is_null($arguments['post_id'])) {
                // indicate a NEWS page
                if (!file_exists(CMS_PATH. '/modules/news/info.php')) {
                    throw new \Exception('A POST_ID was submitted, but the NEWS addon is not installed at the parent CMS!');
                }
                $SQL = "SELECT `link` FROM `".CMS_TABLE_PREFIX."mod_news_posts` WHERE `post_id`='".$arguments['post_id']."'";
                $post_link = $this->app['db']->fetchColumn($SQL);
                return CMS_URL . $this->getPageDirectory() . $post_link . $this->getPageExtension();
            }

            // regular CMS page
            $SQL = "SELECT `link` FROM `".CMS_TABLE_PREFIX."pages` WHERE `page_id`='$page_id'";
            $page_link = $this->app['db']->fetchColumn($SQL);
            return CMS_URL. $this->getPageDirectory(). $page_link . $this->getPageExtension();
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Get the title of the given page ID. If arguments 'topic_id' or 'post_id'
     * the function will return the title for the given TOPICS or NEWS article
     *
     * @param integer $page_id
     * @param null|array $arguments
     * @throws \Exception
     * @return string title of the page
     */
    public function getTitle($page_id, $arguments=null)
    {
        try {
            if (isset($arguments['topic_id']) && !is_null($arguments['topic_id'])) {
                // indicate a TOPICS page
                if (!file_exists(CMS_PATH . '/modules/topics/module_settings.php')) {
                    throw new \Exception('A TOPIC_ID was submitted, but the TOPICS addon is not installed at the parent CMS!');
                }
                // get the title
                $SQL = "SELECT `title` FROM `".CMS_TABLE_PREFIX."mod_topics` WHERE `topic_id`='".$arguments['topic_id']."'";
                return $this->app['db']->fetchColumn($SQL);
            }

            if (isset($arguments['post_id']) && !is_null($arguments['post_id'])) {
                // indicate a NEWS page
                if (!file_exists(CMS_PATH. '/modules/news/info.php')) {
                    throw new \Exception('A POST_ID was submitted, but the NEWS addon is not installed at the parent CMS!');
                }
                $SQL = "SELECT `title` FROM `".CMS_TABLE_PREFIX."mod_news_posts` WHERE `post_id`='".$arguments['post_id']."'";
                return $this->app['db']->fetchColumn($SQL);
            }

            // regular CMS page
            $SQL = "SELECT `page_title` FROM `".CMS_TABLE_PREFIX."pages` WHERE `page_id`='$page_id'";
            return $this->app['db']->fetchColumn($SQL);
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

}
