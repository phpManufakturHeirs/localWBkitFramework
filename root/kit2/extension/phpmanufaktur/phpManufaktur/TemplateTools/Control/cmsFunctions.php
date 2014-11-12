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
use phpManufaktur\Basic\Data\CMS\Page;
use phpManufaktur\flexContent\Data\Content\Content as flexContentData;
use phpManufaktur\flexContent\Control\Command\Tools as flexContentTools;
use phpManufaktur\TemplateTools\Control\cmsFunctions\PageImage;
use phpManufaktur\imageTweak\Control\imageTweak;

class cmsFunctions
{
    protected $app = null;
    protected $PageData = null;
    private static $page_sequence = null;
    private static $page_block = null;
    private static $page_menu = null;
    private static $page_option = null;

    /**
     * Constructor
     *
     * @param Application $app
     */
    public function __construct(Application $app, $initialize=true)
    {
        $this->app = $app;
        $this->PageData = new Page($app);

        // be aware - some constants may undefined!
        $cms_path = defined('CMS_PATH') ? CMS_PATH : WB_PATH;
        $cms_url = defined('CMS_URL') ? CMS_URL : WB_URL;
        $template_dir = defined('TEMPLATE_DIR') ? TEMPLATE_DIR : $cms_url.'/templates';

        $info_path = $cms_path.substr($template_dir, strlen($cms_url)).'/info.php';
        if ($app['filesystem']->exists($info_path)) {
            global $block;
            global $menu;
            require_once $info_path;
            if (is_array($block)) {
                self::$page_block = $block;
            }
            if (is_array($menu)) {
                self::$page_menu = $menu;
            }
        }
    }

    /**
     * Return self::$page_menu for internal use in other classes
     *
     * @return null|array
     */
    public function internal_page_menu()
    {
        return self::$page_menu;
    }

    /**
     * Return the page description for the actual PAGE_ID.
     * The function detect TOPICS, NEWS and flexContent article and return specific descriptions
     *
     * @param boolean $prompt
     * @return string
     */
    public function page_description($prompt=true)
    {
        if (defined('EXTRA_TOPIC_ID') && (EXTRA_TOPIC_ID > 0)) {
            if (!file_exists(CMS_ADDONS_PATH . '/topics/module_settings.php')) {
                throw new \Exception('A EXTRA_TOPIC_ID was submitted, but TOPICS is not installed!');
            }
            // get the title
            $SQL = "SELECT `description` FROM `".CMS_TABLE_PREFIX."mod_topics` WHERE `topic_id`=".EXTRA_TOPIC_ID;
            $description = $this->app['utils']->unsanitizeText($this->app['db']->fetchColumn($SQL));
            if ($prompt) {
                echo $description;
            }
            return $description;
        }
        elseif (defined('EXTRA_POST_ID') && (EXTRA_POST_ID > 0)) {
            // indicate a NEWS page
            if (!file_exists(CMS_PATH. '/modules/news/info.php')) {
                throw new \Exception('A EXTRA_POST_ID was submitted, but the NEWS addon is not installed at the parent CMS!');
            }
            // there is no description available, so we return the CMS_DESCRIPTION
            if ($prompt) {
                echo CMS_DESCRIPTION;
            }
            return CMS_DESCRIPTION;
        }
        elseif (defined('EXTRA_FLEXCONTENT_ID') && (EXTRA_FLEXCONTENT_ID > 0)) {
            // this is a flexContent article
            $flexContentData = new flexContentData($this->app);
            if (false !== ($content = $flexContentData->select(EXTRA_FLEXCONTENT_ID))) {
                if ($prompt) {
                    echo $content['description'];
                }
                return $content['description'];
            }
            return null;
        }
        elseif (function_exists('page_description')) {
            if ($prompt) {
                \page_description();
            }
            else {
                ob_start();
                \page_description();
                return ob_get_clean();
            }
        }
        else {
            return null;
        }
    }

    /**
     * Create a page title for the given Template
     *
     * @param string $title
     * @return string
     */
    protected function replaceTitlePlaceholders($title, $spacer=' - ', $template='[PAGE_TITLE]')
    {
        $placeholders = array('[WEBSITE_TITLE]', '[PAGE_TITLE]', '[MENU_TITLE]', '[SPACER]');
        $values = array(WEBSITE_TITLE, $title, MENU_TITLE, $spacer);

        return str_replace($placeholders, $values, $template);
    }

    /**
     * Return the page title for the actual PAGE_ID
     * This function detect NEWS, TOPICS and flexContent articles and return the correct titles
     *
     * @param string $spacer
     * @param string $template
     * @param boolean $prompt
     * @return string
     */
    public function page_title($spacer= ' - ', $template='[PAGE_TITLE]', $prompt=true)
    {
        if (defined('EXTRA_TOPIC_ID') && (EXTRA_TOPIC_ID  > 0)) {
            // this is a TOPIC article
            if (!file_exists(CMS_ADDONS_PATH . '/topics/module_settings.php')) {
                throw new \Exception('A EXTRA_TOPIC_ID was submitted, but TOPICS is not installed!');
            }
            // get the title
            $SQL = "SELECT `title` FROM `".CMS_TABLE_PREFIX."mod_topics` WHERE `topic_id`=".EXTRA_TOPIC_ID;
            $title = $this->app['db']->fetchColumn($SQL);

            $title = $this->replaceTitlePlaceholders($title, $spacer, $template);
            if ($prompt) {
                echo $title;
            }
            return $title;
        }
        elseif (defined('EXTRA_POST_ID') && (EXTRA_POST_ID > 0)) {
            // this is a NEWS article
            if (!file_exists(CMS_PATH. '/modules/news/info.php')) {
                throw new \Exception('A EXTRA_POST_ID was submitted, but the NEWS addon is not installed at the parent CMS!');
            }
            $SQL = "SELECT `title` FROM `".CMS_TABLE_PREFIX."mod_news_posts` WHERE `post_id`=".EXTRA_POST_ID;
            $title = $this->app['db']->fetchColumn($SQL);

            $title = $this->replaceTitlePlaceholders($title, $spacer, $template);
            if ($prompt) {
                echo $title;
            }
            return $title;
        }
        elseif (defined('EXTRA_FLEXCONTENT_ID') && (EXTRA_FLEXCONTENT_ID > 0)) {
            // this is a flexContent article
            $flexContentData = new flexContentData($this->app);
            if (false !== ($content = $flexContentData->select(EXTRA_FLEXCONTENT_ID))) {
                $title = $this->replaceTitlePlaceholders($content['title'], $spacer, $template);
                if ($prompt) {
                    echo $title;
                }
                return $title;
            }
            return null;
        }
        elseif (function_exists('page_title')) {
            if ($prompt) {
                \page_title($spacer, $template);
            }
            else {
                ob_start();
                \page_title($spacer, $template);
                return ob_get_clean();
            }
        }
        else {
            return null;
        }
    }

    /**
     * Extract the page options from the page keywords and cleanup the keywords
     * string. This function will set the self::$page_option array
     *
     * @param string $keywords
     * @return string
     */
    protected function extractOptionsFromKeywords($keywords)
    {
        $keywords = trim($keywords);
        self::$page_option = null;

        if (empty($keywords)) {
            return $keywords;
        }

        if (strpos($keywords, ',')) {
            $keyword_array = explode(',', $keywords);
        }
        else {
            $keyword_array = array($keywords);
        }

        if (isset($keyword_array[0]) && (false !== strpos($keyword_array[0], '[')) && (false !== strpos($keyword_array[0], ']'))) {
            // remove the square brackets from the options string
            $options_string = trim(substr($keyword_array[0], strpos($keyword_array[0], '[')+1, strpos($keyword_array[0], ']')-1));

            // explode to $options
            $options = (strpos($options_string, '|')) ? explode('|', $options_string) : array($options_string);
            self::$page_option = array();

            // walk through the options
            foreach ($options as $option) {
                if (strpos($option, ':')) {
                    list($key, $value) = explode(':', $option);
                    // decode enties &#58; &#124; and &#44;
                    $value = html_entity_decode($value);
                    if (strtolower(trim($value)) == 'true') {
                        $value = true;
                    }
                    elseif (strtolower(trim($value)) == 'false') {
                        $value = false;
                    }
                    self::$page_option[strtolower(trim($key))] = trim($value);
                }
            }

            // build the new keyword string
            $new_keywords = array();
            unset($keyword_array[0]);
            foreach ($keyword_array as $keyword) {
                $new_keywords[] = trim($keyword);
            }
            $keywords = implode(', ', $new_keywords);
        }

        return $keywords;
    }

    /**
     * Get the value for the given page option
     *
     * @param string $option
     * @param string $prompt
     * @return Ambigous <NULL, multitype:, string>
     */
    public function page_option($option, $prompt=true)
    {
        if (is_null(self::$page_option)) {
            // possibly self::$page_option are never set, so we call page_keywords() to initialize
            $this->page_keywords(false);
        }

        $option = strtolower(trim($option));
        $page_option = (isset(self::$page_option[$option])) ? self::$page_option[$option] : null;

        if ($prompt) {
            echo $page_option;
        }
        return $page_option;
    }

    /**
     * Return the page keywords for the actual PAGE_ID
     * The function detect TOPICS, NEWS and flexContent articles and return specific keywords
     *
     * @param boolean $prompt
     * @return string
     */
    public function page_keywords($prompt=true)
    {
        $keywords = null;

        if (defined('EXTRA_TOPIC_ID') && (EXTRA_TOPIC_ID > 0)) {
            if (!file_exists(CMS_ADDONS_PATH . '/topics/module_settings.php')) {
                throw new \Exception('A EXTRA_TOPIC_ID was submitted, but TOPICS is not installed!');
            }
            // get the keywords
            $SQL = "SELECT `keywords` FROM `".CMS_TABLE_PREFIX."mod_topics` WHERE `topic_id`=".EXTRA_TOPIC_ID;
            $keywords = $this->app['db']->fetchColumn($SQL);
        }
        elseif (defined('EXTRA_POST_ID') && (EXTRA_POST_ID > 0)) {
            // indicate a NEWS page
            if (!file_exists(CMS_PATH. '/modules/news/info.php')) {
                throw new \Exception('A EXTRA_POST_ID was submitted, but the NEWS addon is not installed at the parent CMS!');
            }
            // there are no keywords available, so we return the CMS_KEYWORDS
            $keywords = CMS_KEYWORDS;
        }
        elseif (defined('EXTRA_FLEXCONTENT_ID') && (EXTRA_FLEXCONTENT_ID > 1)) {
            // this is a flexContent article
            $flexContentData = new flexContentData($this->app);
            if (false !== ($content = $flexContentData->select(EXTRA_FLEXCONTENT_ID))) {
                $keywords = $content['keywords'];
            }
        }
        elseif (function_exists('page_keywords')) {
            ob_start();
            \page_keywords();
            $keywords = ob_get_clean();
        }

        $keywords = $this->extractOptionsFromKeywords($keywords);

        if ($prompt) {
            echo $keywords;
        }
        return $keywords;
    }

    /**
     * Check the given $block. If the $block is a string return the ID of the
     * block if exists, otherwise NULL
     *
     * @param mixed $block
     * @return integer|NULL
     */
    protected function check_block($block)
    {
        if (!is_numeric($block) && is_string($block) && is_array(self::$page_block)) {
            // try to get the Block ID by the Block Name
            $search = $block;
            $block = null;
            foreach (self::$page_block as $id => $name) {
                if (strtolower($search) == strtolower($name)) {
                    // hit - return the ID
                    return $id;
                }
            }
            if (is_null($block)) {
                // return null if block does not exists
                return null;
            }
        }
        elseif (is_numeric($block) && ($block > 0)) {
            return $block;
        }
        else {
            return null;
        }
    }

    /**
     * Return the page content by the given block for the actual PAGE_ID
     *
     * @param number|string $block
     * @param boolean $prompt
     * @param array $options
     * @return string
     */
    public function page_content($block=1, $prompt=true, $options=array())
    {
        if (null == ($block = $this->check_block($block))) {
            return null;
        }

        if (function_exists('page_content')) {
            // get the content for the given block
            ob_start();
            \page_content($block);
            $content = ob_get_clean();

            $use_image_tweak = (isset($options['image_tweak']) && is_bool($options['image_tweak'])) ? $options['image_tweak'] : true;

            if (!empty($content) && $use_image_tweak &&
                class_exists('phpManufaktur\imageTweak\Control\imageTweak')) {
                // process imageTweak
                $imageTweak = new imageTweak();
                if (method_exists($imageTweak, 'controllerTemplateTools')) {
                    $content = $imageTweak->controllerTemplateTools($this->app, $content);
                }
            }

            if ($prompt) {
                echo $content;
            }
            return $content;
        }
        else {
            // can nothing do ...
            return null;
        }
    }

    /**
     * Mapping the show_menu2()
     *
     * @param number|string $aMenu
     * @param string $aStart
     * @param unknown $aMaxLevel
     * @param string $aOptions
     * @param string $aItemOpen
     * @param string $aItemClose
     * @param string $aMenuOpen
     * @param string $aMenuClose
     * @param string $aTopItemOpen
     * @param string $aTopMenuOpen
     * @param boolean $prompt
     * @return Ambigous <boolean, string, unknown>
     */
    public function show_menu2(
        $aMenu          = 0,
        $aStart         = SM2_ROOT,
        $aMaxLevel      = -1999, // SM2_CURR+1
        $aOptions       = SM2_TRIM,
        $aItemOpen      = false,
        $aItemClose     = false,
        $aMenuOpen      = false,
        $aMenuClose     = false,
        $aTopItemOpen   = false,
        $aTopMenuOpen   = false,
        $prompt         = true
        )
    {
        if (!is_numeric($aMenu) && is_string($aMenu) && !empty($aMenu) && is_array(self::$page_menu)) {
            // try to get the Menu ID by the Menu Name
            $search = $aMenu;
            $aMenu = 999; // set Menu ID to a non existing one
            foreach (self::$page_menu as $id => $name) {
                if (strtolower($search) == strtolower($name)) {
                    $aMenu = $id;
                    break;
                }
            }
        }

        if (function_exists('show_menu2')) {
            if ($prompt) {
                \show_menu2($aMenu,$aStart,$aMaxLevel,$aOptions,$aItemOpen,
                    $aItemClose,$aMenuOpen,$aMenuClose,$aTopItemOpen,$aTopMenuOpen);
            }
            else {
                ob_start();
                \show_menu2($aMenu,$aStart,$aMaxLevel,$aOptions,$aItemOpen,
                    $aItemClose,$aMenuOpen,$aMenuClose,$aTopItemOpen,$aTopMenuOpen);
                return ob_get_clean();
            }
        }
        else {
            return null;
        }
    }

    /**
     * Get the page link for the given page ID
     *
     * @param string $page_id
     * @return string|NULL
     */
    public function page_link($page_id=PAGE_ID)
    {
        if (!is_numeric($page_id) || ($page_id < 1)) {
            return null;
        }
        $SQL = "SELECT `link` FROM `".CMS_TABLE_PREFIX."pages` WHERE `page_id`=$page_id";
        return $this->app['db']->fetchColumn($SQL);
    }

    /**
     * Get the URL of the given page ID. If arguments 'topic_id' or 'post_id'
     * the function will return the URL for the given TOPICS or NEWS article
     *
     * @param null|array $arguments
     * @param boolean $prompt
     * @throws \Exception
     * @return string URL of the page
     */
    public function page_url($page_id=PAGE_ID, $ignore_extra_ids=false, $prompt=true)
    {
        if ($ignore_extra_ids) {
            // dont process any EXTRA_ ids!
            if ($page_id > 0) {
                $url = CMS_URL. CMS_PAGES_DIRECTORY. $this->page_link($page_id). CMS_PAGES_EXTENSION;
            }
            else {
                $url = $_SERVER['REQUEST_URI'];
            }
        }
        else {
            // get the URL and look also for the EXTRA_ ids
            if (defined('EXTRA_FLEXCONTENT_ID') && (EXTRA_FLEXCONTENT_ID > 1)) {
                // this is a flexContent Article...
                $flexContentData = new flexContentData($this->app);
                if (false !== ($content = $flexContentData->selectPermaLinkByContentID(EXTRA_FLEXCONTENT_ID))) {
                    $flexContentTools = new flexContentTools($this->app);
                    $base_url = $flexContentTools->getPermalinkBaseURL($content['language']);
                    $url = $base_url.'/'.$content['permalink'];
                }
                else {
                    $url = CMS_URL. CMS_PAGES_DIRECTORY. $this->page_link($page_id). CMS_PAGES_EXTENSION;
                }
            }
            elseif (is_numeric($page_id) && ($page_id > 0)) {
                // this is a regular CMS page
                $url = $this->PageData->getURL($page_id, array(
                    'topic_id' => (defined('EXTRA_TOPIC_ID') && (EXTRA_TOPIC_ID > 0)) ? EXTRA_TOPIC_ID : null,
                    'post_id' => (defined('EXTRA_POST_ID') && (EXTRA_POST_ID > 0)) ? EXTRA_POST_ID : null
                ));
            }
            else {
                $url = $_SERVER['REQUEST_URI'];
            }
        }

        if ($prompt) {
            echo $url;
        }
        return $url;
    }

    /**
     * Return the page footer from the CMS options.
     * You can use the placeholders [YEAR] and [PROCESS_TIME]
     *
     * @param string $date_format for the [YEAR] placeholder
     * @param boolean $prompt
     * @return string formatted
     */
    public function page_footer($date_format='Y', $prompt=true)
    {
        if (function_exists('page_footer')) {
            if ($prompt) {
                \page_footer($date_format);
            }
            else {
                ob_start();
                \page_footer($date_format);
                return ob_get_clean();
            }
        }
        else {
            return null;
        }
    }

    /**
     * Return the page header from the CMS options.
     *
     * @param boolean $prompt
     */
    public function page_header($prompt=true)
    {
        if (function_exists('page_header')) {
            if ($prompt) {
                \page_header();
            }
            else {
                ob_start();
                \page_header();
                return ob_get_clean();
            }
        }
        else {
            return null;
        }
    }

    /**
     * Function to add optional module Javascript or CSS stylesheets into the
     * <head> section of the frontend
     *
     * @param string $file_type
     * @param boolean $prompt
     * @return string
     */
    public function register_frontend_modfiles($file_type='css', $prompt=true)
    {
        if (function_exists('register_frontend_modfiles')) {
            if ($prompt) {
                \register_frontend_modfiles($file_type);
            }
            else {
                ob_start();
                \register_frontend_modfiles($file_type);
                return ob_get_clean();
            }
        }
        else {
            return null;
        }
    }

    /**
     * Function to add optional frontend_body.js files before the </body> tag.
     * This function override the original and does NOT include jQuery files
     * into the <head> section.
     *
     * @param boolean $prompt
     * @return string
     */
    public function register_frontend_modfiles_body($prompt=true)
    {
        global $include_body_links;

        if (!defined('MOD_FRONTEND_BODY_JAVASCRIPT_REGISTERED')) {
            define('MOD_FRONTEND_BODY_JAVASCRIPT_REGISTERED', true);
        }

        $body_links = '';
        $base_link = '<script src="'.CMS_URL.'/modules/{MODULE_DIRECTORY}/frontend_body.js" type="text/javascript"></script>';
        $base_file = "frontend_body.js";

        // ensure that frontend_body.js is only added once per module type
        if (!empty($include_body_links)) {
            if (strpos($body_links, $include_body_links) === false) {
                $body_links .= $include_body_links;
            }
            $include_body_links = '';
        }

        // gather information for all models embedded on actual page
        $SQL = "SELECT `module` FROM `".CMS_TABLE_PREFIX."sections` WHERE `page_id`=".PAGE_ID." AND `module`<>'wysiwyg'";
        $modules = $this->app['db']->fetchAll($SQL);
        if (is_array($modules)) {
            foreach ($modules as $module) {
                if ($this->app['filesystem']->exists(CMS_PATH.'/modules/'.$module['module'].'/'.$base_file)) {
                    // create link with frontend_body.js source for the current module
                    $tmp_link = str_replace("{MODULE_DIRECTORY}", $module['module'], $base_link);
                    // define constant indicating that the register_frontent_files_body was invoked
                    if (!defined('MOD_FRONTEND_BODY_JAVASCRIPT_REGISTERED')) {
                        define('MOD_FRONTEND_BODY_JAVASCRIPT_REGISTERED', true);
                    }
                    // ensure that frontend_body.js is only added once per module type
                    if (strpos($body_links, $tmp_link) === false) {
                        $body_links .= $tmp_link;
                    }
                }
            }
        }

        if ($prompt) {
            echo $body_links;
        }
        else {
            return $body_links;
        }
    }

    /**
     * Get the previous page ID for the given page ID
     *
     * @param integer $page_id
     * @param array $page_visibility
     * @param boolean $prompt
     * @return integer
     */
    public function page_previous_id($page_id=PAGE_ID, $page_visibility=array('public'), $prompt=true)
    {
        if (!is_array($page_visibility) || empty($page_visibility)) {
            $page_visibility = array('public');
        }

        if ($page_id == PAGE_ID_HOME) {
            return -1;
        }

        $SQL = "SELECT `menu` FROM `".CMS_TABLE_PREFIX."pages` WHERE `page_id`=$page_id";
        if (null == ($menu = $this->app['db']->fetchColumn($SQL))) {
            // no result - possible at search pages etc.
            return -1;
        }

        // get the page sequence
        $sequence = $this->page_sequence($menu, 0, $page_visibility);

        $result = -1;

        if (false !== ($key = array_search($page_id, $sequence))) {
            if (isset($sequence[$key-1])) {
                $result = $sequence[$key-1];
            }
        }

        if ($prompt) {
            echo $result;
        }
        return $result;
    }


    /**
     * Get the next page ID for the given page ID
     *
     * @param integer $page_id
     * @param array $page_visibility
     * @param boolean $prompt
     * @return integer
     */
    public function page_next_id($page_id=PAGE_ID, $page_visibility=array('public'), $prompt=true)
    {
        if (!is_array($page_visibility) || empty($page_visibility)) {
            $page_visibility = array('public');
        }

        $SQL = "SELECT `menu` FROM `".CMS_TABLE_PREFIX."pages` WHERE `page_id`=$page_id";
        if (null == ($menu = $this->app['db']->fetchColumn($SQL))) {
            // no result - possible at search pages etc.
            return -1;
        }

        // get the page sequence
        $sequence = $this->page_sequence($menu, 0, $page_visibility);

        $result = -1;

        if (false !== ($key = array_search($page_id, $sequence))) {
            if (isset($sequence[$key+1])) {
                $result = $sequence[$key+1];
            }
        }

        if ($prompt) {
            echo $result;
        }
        return $result;
    }

    /**
     * Callback function to add a page ID to the page sequence array
     *
     * @param integer $id
     * @see page_sequence()
     */
    protected static function add_page_sequence_id($id)
    {
        self::$page_sequence[] = $id;
    }

    /**
     * Create a array with the complete page sequence for the given menu, level
     * and visibility. Can be used to create a sitemap or step through the site.
     *
     * @param number $menu
     * @param number $start_level
     * @param array $page_visibility
     */
    public function page_sequence($menu=1, $start_level=0, $page_visibility=array('public'))
    {
        if (!is_array($page_visibility) || empty($page_visibility)) {
            $page_visibility = array('public');
        }
        $visibility = '';
        foreach ($page_visibility as $visi) {
            if (!empty($visibility)) {
                $visibility .= ' OR ';
            }
            $visibility .= "`visibility`='$visi'";
        }

        $sequence = array();

        $SQL = "SELECT `page_id` FROM `".CMS_TABLE_PREFIX."pages` WHERE `menu`=$menu AND ".
            "`level`=$start_level AND ($visibility) ORDER BY `position` ASC";
        $pages = $this->app['db']->fetchAll($SQL);

        foreach ($pages as $page) {
            $sequence[$page['page_id']][] = $page['page_id'];
        }

        $SQL = "SELECT MAX(`level`) FROM `".CMS_TABLE_PREFIX."pages` WHERE `menu`=$menu";
        $max_level = $this->app['db']->fetchColumn($SQL);

        for ($level=$start_level+1; $level < $max_level+1; $level++) {
            $SQL = "SELECT `page_id`, `page_trail` FROM `".CMS_TABLE_PREFIX."pages` WHERE ".
                "`menu`=$menu AND `level`=$level AND ($visibility) ORDER BY `position` ASC";
            $pages = $this->app['db']->fetchAll($SQL);
            foreach ($pages as $page) {
                $t = explode(',', $page['page_trail']);
                switch ($level) {
                    case 1:
                        $sequence[$t[0]][$t[1]][] = $page['page_id'];
                        break;
                    case 2:
                        $sequence[$t[0]][$t[1]][$t[2]][] = $page['page_id'];
                        break;
                    case 3:
                        $sequence[$t[0]][$t[1]][$t[2]][$t[3]][] = $page['page_id'];
                        break;
                    case 4:
                        $sequence[$t[0]][$t[1]][$t[2]][$t[3]][$t[4]][] = $page['page_id'];
                        break;
                    case 5:
                        $sequence[$t[0]][$t[1]][$t[2]][$t[3]][$t[4]][$t[5]][] = $page['page_id'];
                        break;
                    case 6:
                        $sequence[$t[0]][$t[1]][$t[2]][$t[3]][$t[4]][$t[5]][$t[6]][] = $page['page_id'];
                        break;
                    case 7:
                        $sequence[$t[0]][$t[1]][$t[2]][$t[3]][$t[4]][$t[5]][$t[6]][$t[7]][] = $page['page_id'];
                        break;
                    case 8:
                        $sequence[$t[0]][$t[1]][$t[2]][$t[3]][$t[4]][$t[5]][$t[6]][$t[7]][$t[8]][] = $page['page_id'];
                        break;
                    case 9:
                        $sequence[$t[0]][$t[1]][$t[2]][$t[3]][$t[4]][$t[5]][$t[6]][$t[7]][$t[8]][$t[9]][] = $page['page_id'];
                        break;
                }
            }
        }

        // reset sequence array
        self::$page_sequence = array();
        // create the page sequence
        array_walk_recursive($sequence, array($this, 'add_page_sequence_id'));

        return self::$page_sequence;
    }

    /**
     * Check if the given PAGE ID has a child
     *
     * @param integer $page_id
     * @throws \Exception
     * @return boolean
     */
    public function page_has_child($page_id=PAGE_ID)
    {
        if ($page_id > 0) {
            $SQL = "SELECT `page_id` FROM `".CMS_TABLE_PREFIX."pages` WHERE `parent` = $page_id LIMIT 1";
            $page_id = $this->app['db']->fetchColumn($SQL);
            return ($page_id > 0);
        }
        else {
            return false;
        }
    }

    /**
     * Return the WYSIWYG content of the given section ID
     *
     * @param integer $section_id
     * @param boolean $prompt
     * @throws \InvalidArgumentException
     * @return string
     */
    public function wysiwyg_content($section_id, $prompt=true)
    {
        if (false === ($section_id = filter_var($section_id, FILTER_VALIDATE_INT))) {
            throw new \InvalidArgumentException('The $section_id must be of type integer!');
        }
        $SQL = "SELECT `content` FROM `".CMS_TABLE_PREFIX."mod_wysiwyg` WHERE `section_id`=$section_id";
        $content = $this->app['db']->fetchColumn($SQL);
        // force UTF-8 encoding!
        $content = mb_convert_encoding($content, 'HTML-ENTITIES', "UTF-8");
        $content = str_replace(array('{SYSVAR:MEDIA_REL}','%5B','%5D'), array(CMS_MEDIA_URL,'[',']'), $content );
        $result = (!empty($content)) ? $this->app['utils']->unsanitizeText($content) : '';
        if ($prompt) {
            echo $result;
        }
        return $result;
    }

    /**
     * Get the first content image from any WYSIWYG, NEWS, TOPICS or flexContent article.
     * Try alternate to get a teaser image (TOPICS, flexContent)
     *
     * @param integer $page_id
     * @param array $options
     * @return string return the URL of the image or an empty string
     */
    public function page_image($page_id=PAGE_ID, $options=array())
    {
        $PageImage = new PageImage($this->app);
        return $PageImage->page_image($page_id, $options);
    }

    /**
     * Get the PAGE_ID for the first, the HOME page. Check also for for
     * menu_links and return the corrected PAGE_ID.
     * Return -1 if the HOME ID can not evaluated
     *
     * @return integer
     */
    public function page_id_home()
    {
        if (($id = isset(self::$page_sequence[0]) ? self::$page_sequence[0] : -1) == -1) {
            // no page sequence available
            return $id;
        }

        $SQL = "SELECT `module` FROM `".CMS_TABLE_PREFIX."sections` WHERE `page_id`=$id AND `position`=1";
        $module = $this->app['db']->fetchColumn($SQL);

        if ($module == 'menu_link') {
            // this is a menulink, so get the target instead
            $SQL = "SELECT `target_page_id` FROM `".CMS_TABLE_PREFIX."mod_menu_link` WHERE `page_id`=$id";
            $target_id = $this->app['db']->fetchColumn($SQL);
            if (is_numeric($target_id) && ($target_id > 0)) {
                // use the target PAGE_ID
                return $target_id;
            }
            else {
                // this is an external link, we can't follow it
                return -1;
            }
        }
        return $id;
    }

    /**
     * Date/Time of the last modification for the given page
     *
     * @param integer $page_id
     * @param string $format
     * @param string $locale
     * @param boolean $prompt
     * @return string
     */
    public function page_modified_when($page_id=PAGE_ID, $format='DATETIME_FORMAT', $locale=null, $prompt=true)
    {
        if (is_null($locale)) {
            $locale = defined('PAGE_LOCALE') ? PAGE_LOCALE : 'en';
        }

        $SQL = "SELECT `modified_when` FROM `".CMS_TABLE_PREFIX."pages` WHERE `page_id`=$page_id";
        $result = $this->app['db']->fetchAssoc($SQL);

        if (!isset($result['modified_when'])) {
            // no valid page, return the current date/time in given format
            $datetime = date($this->app['translator']->trans($format, array(), 'messages', $locale));
        }
        else {
            $datetime = date($this->app['translator']->trans($format, array(), 'messages', $locale), $result['modified_when']);
        }

        if ($prompt) {
            echo $datetime;
        }
        return $datetime;
    }

    /**
     * Display name of the user who has at last modified the given page
     *
     * @param integer $page_id
     * @param string $locale
     * @param boolean $prompt
     * @return string
     */
    public function page_modified_by($page_id=PAGE_ID, $locale=null, $prompt=true)
    {
        $SQL = "SELECT `display_name` FROM `".CMS_TABLE_PREFIX."pages`, `".CMS_TABLE_PREFIX."users` ".
        "WHERE `user_id`=`modified_by` AND `page_id`=$page_id";

        $result = $this->app['db']->fetchAssoc($SQL);

        if (is_null($locale)) {
            $locale = defined('PAGE_LOCALE') ? PAGE_LOCALE : 'en';
        }

        $name = isset($result['display_name']) ? $this->app['utils']->unsanitizeText($result['display_name']) :
            $this->app['translator']->trans('- unknown -', array(), 'messages', $locale);

        if ($prompt) {
            echo $name;
        }
        return $name;
    }

    /**
     * Date/Time of the last modification of the CMS
     *
     * @param string $format
     * @param string $locale
     * @param boolean $prompt
     * @return string
     */
    public function cms_modified_when($format='DATETIME_FORMAT', $locale=null, $prompt=true)
    {
        if (is_null($locale)) {
            $locale = defined('PAGE_LOCALE') ? PAGE_LOCALE : 'en';
        }

        $SQL = "SELECT MAX(`modified_when`) AS `cms_modified` FROM `".CMS_TABLE_PREFIX."pages`";
        $result = $this->app['db']->fetchAssoc($SQL);

        if (!isset($result['cms_modified'])) {
            // no valid page, return the current date/time in given format
            $datetime = date($this->app['translator']->trans($format, array(), 'messages', $locale));
        }
        else {
            $datetime = date($this->app['translator']->trans($format, array(), 'messages', $locale), $result['cms_modified']);
        }

        if ($prompt) {
            echo $datetime;
        }
        return $datetime;
    }

    /**
     * Displayname of the user who has last changed a page of the CMS
     *
     * @param string $locale
     * @param boolean $prompt
     * @return string
     */
    public function cms_modified_by($locale=null, $prompt=true)
    {
        $SQL = "SELECT `display_name` FROM `".CMS_TABLE_PREFIX."pages`, `".CMS_TABLE_PREFIX."users` ".
            "WHERE `modified_when`=(SELECT MAX(`modified_when`) FROM `".CMS_TABLE_PREFIX."pages`) AND `user_id`=`modified_by`";
        $result = $this->app['db']->fetchAssoc($SQL);

        if (is_null($locale)) {
            $locale = defined('PAGE_LOCALE') ? PAGE_LOCALE : 'en';
        }

        $name = isset($result['display_name']) ? $this->app['utils']->unsanitizeText($result['display_name']) :
            $this->app['translator']->trans('- unknown -', array(), 'messages', $locale);

        if ($prompt) {
            echo $name;
        }
        return $name;
    }

    /**
     * Get the SECTION_ID's for the given PAGE_ID and $block identifier (ID or name).
     * Order the result 'ASC', 'DESC' or as RANDOM
     *
     * @param integer $page_id
     * @param integer|string $block
     * @param string $order
     * @return NULL|array
     */
    public function wysiwyg_section_ids($page_id=PAGE_ID, $block=1, $order='ASC')
    {
        $order = strtoupper(trim($order));
        if (!in_array($order, array('ASC', 'DESC', 'RANDOM'))) {
            $order = 'ASC';
        }

        if (null == ($block = $this->check_block($block))) {
            return null;
        }

        if (!is_numeric($page_id) || ($page_id < 1)) {
            return null;
        }

        $SQL = "SELECT `section_id` FROM `".CMS_TABLE_PREFIX."sections` WHERE `page_id`=$page_id AND ".
            "`block`=$block AND `module`='wysiwyg' ORDER BY ";
        $SQL .= ($order == 'RANDOM') ? "RAND()" : "`position` $order";

        $results = $this->app['db']->fetchAll($SQL);
        $section_ids = array();
        if (is_array($results)) {
            foreach ($results as $result) {
                $section_ids[] = $result['section_id'];
            }
        }
        return $section_ids;
    }

    /**
     * Check if the Maintenance Mode is switched on, used to set CMS_MAINTENANCE
     *
     * @return boolean
     */
    public function internal_maintenance()
    {
        $id = defined('PAGE_ID_HOME') ? PAGE_ID_HOME : $this->page_id_home();
        $SQL = "SELECT `keywords` FROM `".CMS_TABLE_PREFIX."pages` WHERE `page_id`=$id";
        $keywords = $this->app['db']->fetchColumn($SQL);

        $keywords = trim($keywords);
        $keyword_array = (strpos($keywords, ',')) ? explode(',', $keywords) : array($keywords);

        if (isset($keyword_array[0]) && (false !== strpos($keyword_array[0], '[')) && (false !== strpos($keyword_array[0], ']'))) {
            // remove the square brackets from the options string
            $options_string = trim(substr($keyword_array[0], strpos($keyword_array[0], '[')+1, strpos($keyword_array[0], ']')-1));

            // explode to $options
            $options = (strpos($options_string, '|')) ? explode('|', $options_string) : array($options_string);

            // walk through the options
            foreach ($options as $option) {
                if (strpos($option, ':')) {
                    list($key, $value) = explode(':', $option);
                    // decode enties &#58; &#124; and &#44;
                    $value = html_entity_decode($value);
                    if (strtolower(trim($value)) == 'true') {
                        $value = true;
                    }
                    elseif (strtolower(trim($value)) == 'false') {
                        $value = false;
                    }
                    if (strtolower(trim($key)) == 'maintenance') {
                        return (is_bool($value)) ? $value : false;
                    }
                }
            }
        }

        return false;
    }

    /**
     * Check if the CMS_MAINTENANCE_MODE is enabled and active.
     * Possible values for $let_pass are 'ADMIN' (default), 'USER' or 'NONE'
     *
     * @param string $let_pass - allow access for authenticated users
     * @throws \Exception
     * @throws \InvalidArgumentException
     * @return string|boolean
     */
    public function cms_maintenance_active($let_pass='ADMIN')
    {
        $let_pass = strtoupper(trim($let_pass));

        if (!defined('CMS_MAINTENANCE_MODE')) {
            throw new \Exception('Missing the constant CMS_MAINTENANCE_MODE!');
        }

        switch ($let_pass) {
            case 'NONE':
                // let pass no one - return the CMS_MAINTENANCE_MODE
                return CMS_MAINTENANCE_MODE;
            case 'ADMIN':
            case 'USER':
                if (!CMS_MAINTENANCE_MODE) {
                    return false;
                }
                $authenticated = ($let_pass == 'USER') ? CMS_USER_IS_AUTHENTICATED : CMS_USER_IS_ADMIN;
                if ($authenticated || (isset($_GET['fuid']) && ($_GET['fuid'] == FRAMEWORK_UID))) {
                    // the user is authenticated or the template is called by an authenticated kitFramework extension
                    $_POST['fuid'] = FRAMEWORK_UID;
                    return false;
                }
                else {
                    // Maintenance mode is active!
                    return true;
                }
            default:
                // invalid parameter
                throw new \InvalidArgumentException("Invalid parameter for cms_maintenance_active(), allowed values are 'NONE', 'ADMIN' or 'USER'");
        }
    }

    /**
     * Check if the browser (robot) comes from Google, MSN or Yahoo and return
     * a HTTP Status Code 503 in this case
     *
     * @param integer $retry_after seconds
     * @param boolean $prompt
     * @return boolean
     */
    public function cms_maintenance_header_searchbot($retry_after=86400, $prompt=true)
    {
        if (($this->app['browser']->name(false) == BROWSER_GOOGLEBOT) ||
            ($this->app['browser']->name(false) == BROWSER_MSNBOT) ||
            ($this->app['browser']->name(false) == BROWSER_SLURP)) {
            if ($prompt) {
                // send a 503 header to Google, MSN and Yahoo robots
                header('HTTP/1.1 503 Service Temporarily Unavailable');
                header('Status: 503 Service Temporarily Unavailable');
                header("Retry-After: $retry_after"); // seconds
            }
            return true;
        }
        return false;
    }
}
