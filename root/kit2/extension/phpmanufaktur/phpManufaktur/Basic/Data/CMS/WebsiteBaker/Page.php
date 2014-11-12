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
    protected static $pages_directory = null;
    protected static $page_extension = null;

    /**
     * Constructor
     *
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
        self::$page_extension = $this->getPageExtension();
        self::$pages_directory = $this->getPageDirectory();
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
     * Get the TOPICS directory if this addon is installed
     *
     * @return string|boolean
     */
    public function getTopicsDirectory()
    {
        if ($this->app['filesystem']->exists(CMS_PATH . '/modules/topics/module_settings.php')) {
            if (!$this->app['filesystem']->exists(FRAMEWORK_PATH.'/config/cms/module/topics.json')) {
                $config = array(
                    'topics' => array(
                        'directory' => '/topics/'
                    )
                );
                $this->app['filesystem']->mkdir(FRAMEWORK_PATH.'/config/cms/module');
                if (!file_put_contents(FRAMEWORK_PATH.'/config/cms/module/topics.json', $this->app['utils']->JSONFormat($config))) {
                    // can't create the config file
                    $error = error_get_last();
                    throw new \Exception($error);
                }
            }
            $topics = $this->app['utils']->readJSON(FRAMEWORK_PATH.'/config/cms/module/topics.json');
            return $this->getPageDirectory().$topics['topics']['directory'];
        }
        return false;
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
                if (false === ($topics_directory = $this->getTopicsDirectory())) {
                    throw new \Exception('A TOPIC_ID was submitted, but the TOPICS addon is not installed at the parent CMS!');
                }
                // indicate a TOPICS page
                $SQL = "SELECT `link` FROM `".CMS_TABLE_PREFIX."mod_topics` WHERE `topic_id`='".$arguments['topic_id']."'";
                $topic_link = $this->app['db']->fetchColumn($SQL);
                return CMS_URL . $topics_directory . $topic_link . $this->getPageExtension();
            }

            if (isset($arguments['post_id']) && !is_null($arguments['post_id'])) {
                // indicate a NEWS page
                if (!file_exists(CMS_PATH. '/modules/news/info.php')) {
                    throw new \Exception('A POST_ID was submitted, but the NEWS addon is not installed at the parent CMS!');
                }
                $SQL = "SELECT `link` FROM `".CMS_TABLE_PREFIX."mod_news_posts` WHERE `post_id`='".$arguments['post_id']."'";
                $post_link = $this->app['db']->fetchColumn($SQL);
                return CMS_URL . self::$pages_directory . $post_link . self::$page_extension;
            }

            // regular CMS page
            $SQL = "SELECT `link` FROM `".CMS_TABLE_PREFIX."pages` WHERE `page_id`='$page_id'";
            $page_link = $this->app['db']->fetchColumn($SQL);
            return CMS_URL. self::$pages_directory. $page_link . self::$page_extension;
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
            if (isset($arguments['topic_id']) && !is_null($arguments['topic_id']) && ($arguments['topic_id'] > 0)) {
                // indicate a TOPICS page
                if (!file_exists(CMS_PATH . '/modules/topics/module_settings.php')) {
                    throw new \Exception('A TOPIC_ID was submitted, but the TOPICS addon is not installed at the parent CMS!');
                }
                // get the title
                $SQL = "SELECT `title` FROM `".CMS_TABLE_PREFIX."mod_topics` WHERE `topic_id`='".$arguments['topic_id']."'";
                return $this->app['db']->fetchColumn($SQL);
            }

            if (isset($arguments['post_id']) && !is_null($arguments['post_id']) && ($arguments['post_id'] > 0)) {
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

    /**
     * Get the CMS page link list in alphabetical order for the given field and direction
     *
     * @param string $order_by the field to order by
     * @param string $order_direction the order direction ASC or DESC
     * @throws \Exception
     * @return <array|boolean>
     */
    public function getPageLinkList($order_by='link', $order_direction='ASC')
    {
        try {
            if (!in_array($order_by, array('link','menu_title','page_title'))) {
                $order_by = 'link';
            }
            $order_direction = ($order_direction == 'DESC') ? 'DESC' : 'ASC';
            $SQL = "SELECT `page_id`, `link`, `level`, `menu_title`, `page_title`, `visibility` FROM `".CMS_TABLE_PREFIX.
                "pages` WHERE `visibility`!='deleted' ORDER BY `$order_by` $order_direction";
            $results = $this->app['db']->fetchAll($SQL);
            $links = array();
            foreach ($results as $result) {
                $link = array();
                foreach ($result as $key => $value) {
                    $link[$key] = is_string($value) ? $this->app['utils']->unsanitizeText($value) : $value;
                }
                $link['complete_link'] = self::$pages_directory . $link['link'] . self::$page_extension;
                $links[] = $link;
            }
            return (!empty($links)) ? $links : false;
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Get the page ID by the given page link
     *
     * @param string $link
     * @throws \Exception
     * @return Ambigous <boolean, integer>
     */
    public function getPageIDbyPageLink($link)
    {
        try {
            $SQL = "SELECT `page_id` FROM `".CMS_TABLE_PREFIX."pages` WHERE `link`='$link'";
            $result = $this->app['db']->fetchColumn($SQL);
            return ($result > 0) ? $result : false;
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Check if the given kitCommand exists at the page ID
     *
     * @param string $command
     * @param integer $page_id
     * @throws \Exception
     * @return boolean
     */
    public function existsCommandAtPageID($command, $page_id)
    {
        try {
            $SQL = "SELECT `section_id` FROM `".CMS_TABLE_PREFIX."mod_wysiwyg` WHERE `page_id`='$page_id' AND `content` LIKE '%~~ $command %'";
            $result = $this->app['db']->fetchColumn($SQL);
            return ($result > 0);
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Return the language code for the given page ID
     *
     * @param integer $page_id
     * @throws \Exception
     * @return Ambigous <boolean, string>
     */
    public function getPageLanguage($page_id)
    {
        try {
            $SQL = "SELECT `language` FROM `".CMS_TABLE_PREFIX."pages` WHERE `page_id`='$page_id'";
            $result = $this->app['db']->fetchColumn($SQL);
            return (strlen($result) == 2) ? $result : false;
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Return the page link for the given page ID
     *
     * @param integer $page_id
     * @throws \Exception
     * @return Ambigous <boolean, string>
     */
    public function getPageLinkByPageID($page_id)
    {
        try {
            $SQL = "SELECT `link` FROM `".CMS_TABLE_PREFIX."pages` WHERE `page_id`='$page_id'";
            $result = $this->app['db']->fetchColumn($SQL);
            return (strlen($result) > 0) ? $result : false;
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Get the visibility of the given CMS page ID
     *
     * @param integer $page_id
     * @throws \Exception
     * @return Ambigous <boolean, string> FALSE if page not exists, otherwise 'public','hidden','registered','private' or 'none'
     */
    public function getPageVisibilityByPageID($page_id)
    {
        try {
            $SQL = "SELECT `visibility` FROM `".CMS_TABLE_PREFIX."pages` WHERE `page_id`=$page_id";
            $result = $this->app['db']->fetchColumn($SQL);
            return (strlen($result) > 0) ? strtolower($result) : false;
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

}
