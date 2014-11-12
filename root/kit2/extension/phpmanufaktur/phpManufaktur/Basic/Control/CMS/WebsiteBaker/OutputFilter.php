<?php

/**
 * kitFramework::Basic
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de
 * @copyright 2014 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\Basic\Control\CMS\WebsiteBaker;

class OutputFilter
{
    protected static $cms_type = null;
    protected static $cms_version = null;

    /**
     * Check type and version of the parent CMS, set the values
     * to the class properties
     *
     */
    protected function checkCMS()
    {
        if (defined('LEPTON_VERSION')) {
            self::$cms_type = 'LEPTON';
            self::$cms_version = LEPTON_VERSION;
        }
        elseif (defined('CAT_VERSION')) {
            self::$cms_type = 'BlackCat';
            self::$cms_version = CAT_VERSION;
        }
        elseif (defined('WB_VERSION')) {
            self::$cms_type = 'WebsiteBaker';
            self::$cms_version = WB_VERSION;
            // fix for WB 2.8.4
            if ((self::$cms_version == '2.8.3') && file_exists(WB_PATH.'/setup.ini.php')) {
                self::$cms_version = '2.8.4';
            }
        }
        else {
            self::$cms_type = '- unknown -';
            self::$cms_version = '0.0.0';
        }
    }

    /**
     * Get the URL for the current requested URI
     *
     * @param boolean $remove_parameter - if true strip the parameter string
     * @return string
     */
    public static function getCurrentPageURL($remove_parameter=true)
    {
        $pageURL = 'http';
        if (isset($_SERVER["HTTPS"]) && (!empty($_SERVER['HTTPS']))) {
            $pageURL .= "s";
        }

        $pageURL .= "://";
        if ($_SERVER["SERVER_PORT"] != "80") {
            $pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
        }
        else {
            $pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
        }

        if ($remove_parameter && strpos($pageURL, '?')) {
            $pageURL = substr($pageURL, 0, strpos($pageURL, '?'));
        }

        return $pageURL;
    }

    /**
     * Get the URL of the submitted PAGE_ID - check for special pages like
     * TOPICS and/or NEWS and return the URL of the TOPIC/NEW page if active
     *
     * @param integer $page_id
     * @return boolean|string
     */
    public static function getURLbyPageID($page_id)
    {
        global $database;
        global $post_id;

        if (defined('TOPIC_ID') && (TOPIC_ID > 0)) {
            // this is a TOPICS page
            $SQL = "SELECT `link` FROM `".TABLE_PREFIX."mod_topics` WHERE `topic_id`='".TOPIC_ID."'";
            $link = $database->get_one($SQL);
            if ($database->is_error()) {
                trigger_error(sprintf('[%s - %s] %s', __FUNCTION__, __LINE__, $database->get_error()), E_USER_ERROR);
                return false;
            }
            // include TOPICS settings
            global $topics_directory;
            include WB_PATH . '/modules/topics/module_settings.php';
            return WB_URL . $topics_directory . $link . PAGE_EXTENSION;
        }

        if (!is_null($post_id) || (defined('POST_ID') && (POST_ID > 0))) {
            // this is a NEWS page
            $id = (defined('POST_ID')) ? POST_ID : $post_id;
            $SQL = "SELECT `link` FROM `".TABLE_PREFIX."mod_news_posts` WHERE `post_id`='$id'";
            $link = $database->get_one($SQL);
            if ($database->is_error()) {
                trigger_error(sprintf('[%s - %s] %s', __FUNCTION__, __LINE__, $database->get_error()), E_USER_ERROR);
                return false;
            }
            return WB_URL.PAGES_DIRECTORY.$link.PAGE_EXTENSION;
        }

        $SQL = "SELECT `link` FROM `".TABLE_PREFIX."pages` WHERE `page_id`='$page_id'";
        $link = $database->get_one($SQL, MYSQL_ASSOC);
        if ($database->is_error()) {
            trigger_error(sprintf('[%s - %s] %s', __FUNCTION__, __LINE__, $database->get_error()), E_USER_ERROR);
            return false;
        }
        return WB_URL.PAGES_DIRECTORY.$link.PAGE_EXTENSION;
    }

    /**
     * Execute the content filter for the kitFramework.
     * Extract CMS parameters like type, version, path, url, id of the calling
     * page and other, additional routes all parameters of a kitCommand and all
     * $_REQUESTs to the kitCommand routine of the kitFramework.
     *
     * @param string $content
     * @return string $content
     */
    public function parse($content)
    {
        global $post_id;
        global $page_id;

        // check CMS type and version
        $this->checkCMS();

        $id = defined('PAGE_ID') ? PAGE_ID : $page_id;

        // collect the main information about CMS, page and user
        $parse = array(
            'cms' => array(
                'type' => self::$cms_type,
                'version' => self::$cms_version,
                'locale' => strtolower(LANGUAGE),
                'page_id' => ($id > 0) ? $id : -1,
                'page_url' => ($id > 0) ? $this->getURLbyPageID($id) : $this->getCurrentPageURL(),
                'page_visibility' => VISIBILITY,
                'remove_commands' => ($id == 0),
                'user' => array(
                    'id' => (isset($_SESSION['USER_ID'])) ? $_SESSION['USER_ID'] : -1,
                    'name' => (isset($_SESSION['USERNAME'])) ? $_SESSION['USERNAME'] : '',
                    'email' => (isset($_SESSION['EMAIL'])) ? $_SESSION['EMAIL'] : ''
                ),
                'special' => array(
                    'post_id' => (!is_null($post_id) || defined('POST_ID')) ? defined('POST_ID') ? POST_ID : $post_id : null,
                    'topic_id' => defined('TOPIC_ID') ? TOPIC_ID : null
                )
            ),
            'GET' => $_GET,
            'POST' => $_POST,
            'content' => $content,
        );

        $options = array(
            CURLOPT_POST => true,
            CURLOPT_HEADER => false,
            CURLOPT_URL => WB_URL.'/kit2/kit_parser',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_POSTFIELDS => http_build_query(array('parse' => $parse), '', '&'),
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_SSL_VERIFYPEER => false
        );
        // use the connection multiple times by using a cookie to identify
        if (!isset($_SESSION['KIT2_COOKIE_FILE']) || !file_exists($_SESSION['KIT2_COOKIE_FILE'])) {
            $_SESSION['KIT2_COOKIE_FILE'] = WB_PATH.'/kit2/temp/session/'.uniqid('outputfilter_');
            $options[CURLOPT_COOKIEJAR] = $_SESSION['KIT2_COOKIE_FILE'];
        }
        else {
            $options[CURLOPT_COOKIEFILE] = $_SESSION['KIT2_COOKIE_FILE'];
        }

        $ch = curl_init();
        curl_setopt_array($ch, $options);

        if (false === ($content = curl_exec($ch))) {
            trigger_error(curl_error($ch), E_USER_ERROR);
        }
        curl_close($ch);
        return $content;
    }
}
