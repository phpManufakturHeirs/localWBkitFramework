<?php

/**
 * kitFramework::Basic
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de
 * @copyright 2013 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\Basic\Control\CMS\WebsiteBaker;


class OutputFilter
{
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

        if (defined('TOPIC_ID')) {
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

        if (!is_null($post_id) || defined('POST_ID')) {
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
     * Try to load a CSS file from the specified directory
     *
     * @param string reference $content
     * @param string $directory
     * @param string $css_file
     * @param string $template
     * @return boolean true on success
     */
    protected function load_css_file(&$content, $directory, $css_file, $template)
    {
        // remove leading and trailing slashes and backslashes
        $directory = trim($directory, '/\\');
        $css_file = trim($css_file, '/\\');
        $template = trim($template, '/\\');
        // we will scan the extension path for phpManufaktur and thirdParty
        $scan_paths = array(
            WB_PATH.'/kit2/extension/phpmanufaktur/phpManufaktur',
            WB_PATH.'/kit2/extension/thirdparty/thirdParty'
        );
        foreach ($scan_paths as $path) {
            if (false === ($scan_files = scandir($path))) {
                return false;
            }
            foreach ($scan_files as $scan_file) {
                if (is_dir($path.'/'.$scan_file) && (strtolower($scan_file) == $directory)) {
                    $css_path = "$path/$scan_file/Template/$template/$css_file";
                    if (file_exists($css_path)) {
                        // ok - the CSS file exist, now we load it
                        $css_url = WB_URL.substr($css_path, strlen(WB_PATH));
                        if (false !== (stripos($content, '<!-- kitFramework:CSS -->'))) {
                            $replace = '<!-- kitFramework:CSS -->'."\n".'<link rel="stylesheet" type="text/css" href="'.$css_url.'" media="all" />';
                            $content = str_ireplace('<!-- kitFramework:CSS -->', $replace, $content);
                        }
                        else {
                            $replace = '<!-- kitFramework:CSS -->'."\n".'<link rel="stylesheet" type="text/css" href="'.$css_url.'" media="all" />'."\n".'</head>';
                            $content = str_ireplace('</head>', $replace, $content);
                        }
                        return true;
                    }
                }
            }
        }
        // no CSS file loaded
        return false;
    }

    /**
     * Try to load a JavaScript or jQuery file from the specified directory
     *
     * @param string reference $content
     * @param string $directory
     * @param string $js_file
     * @param string $template
     * @return boolean true on success
     */
    protected function load_js_file(&$content, $directory, $js_file, $template)
    {
        // remove leading and trailing slashes and backslashes
        $directory = trim($directory, '/\\');
        $js_file = trim($js_file, '/\\');
        $template = trim($template, '/\\');
        // we will scan the extension path for phpManufaktur and thirdParty
        $scan_paths = array(
            WB_PATH.'/kit2/extension/phpmanufaktur/phpManufaktur',
            WB_PATH.'/kit2/extension/thirdparty/thirdParty'
        );
        foreach ($scan_paths as $path) {
            if (false === ($scan_files = scandir($path))) {
                return false;
            }
            foreach ($scan_files as $scan_file) {
                if (is_dir($path.'/'.$scan_file) && (strtolower($scan_file) == $directory)) {
                    $js_path = "$path/$scan_file/Template/$template/$js_file";
                    if (file_exists($js_path)) {
                        // ok - the JS file exist, now we load it
                        $css_url = WB_URL.substr($js_path, strlen(WB_PATH));
                        if (false !== (stripos($content, '<!-- kitFramework:JS -->'))) {
                            $replace = '<!-- kitFramework:JS -->'."\n".'<script src="'.$css_url.'" type="text/javascript"></script>';
                            $content = str_ireplace('<!-- kitFramework:JS -->', $replace, $content);
                        }
                        else {
                            $replace = '<!-- kitFramework:JS -->'."\n".'<script src="'.$css_url.'" type="text/javascript"></script>'."\n".'</head>';
                            $content = str_ireplace('</head>', $replace, $content);
                        }
                        return true;
                    }
                }
            }
        }
        // no JS file loaded
        return false;
    }

    /**
     * Check if a CSS or JS file is to load, check the params, set defaults and
     * call the subroutines to load the files
     *
     * @param string reference $content
     * @param string $command
     * @param string $type i.e. 'css' or 'js'
     * @param string $value
     */
    protected function checkLoadFile(&$content, $command, $type, $value) {
        if ($type == 'css') {
            // we have to load an additional CSS file
            $count = substr_count($value, ',');
            if ($count == 0) {
                if (empty($value)) {
                    // assume that the directory is equal to the command
                    return $this->load_css_file($content, $command, 'screen.css', 'default');
                }
                else {
                    // directory is given, all other values are default
                    return $this->load_css_file($content, strtolower(trim($value)), 'screen.css', 'default');
                }
            }
            elseif ($count == 1) {
                list($directory, $css_file) = explode(',', strtolower($value));
                return $this->load_css_file($content, trim($directory), trim($css_file), 'default');
            }
            elseif ($count == 2) {
                // three parameters
                list($directory, $css_file, $template) = explode(',', strtolower($value));
                return $this->load_css_file($content, trim($directory), trim($css_file), trim($template));
            }
        }
        elseif ($type == 'js') {
            $count = substr_count($value, ',');
            if ($count == 1) {
                // two parameters, split into directory and JS file
                list($directory, $js_file) = explode(',', strtolower($value));
                return $this->load_js_file($content, trim($directory), trim($js_file), 'default');
            }
            elseif ($count == 2) {
                // three parameters, split into directory, JS file and template
                list($directory, $js_file, $template) = explode(',', strtolower($value));
                return $this->load_js_file($content, trim($directory), trim($js_file), trim($template));
            }
        }
    }

    /**
     * Execute the content filter for the kitFramework.
     * Extract CMS parameters like type, version, path, url, id of the calling
     * page and other, additional routes all parameters of a kitCommand and all
     * $_REQUESTs to the kitCommand routine of the kitFramework.
     *
     * @param string $content
     * @return mixed
     */
    public function parse($content, $parseCMS=true, &$kit_command=array())
    {
        global $post_id;

        if (defined('LEPTON_VERSION')) {
            $cms_type = 'LEPTON';
            $cms_version = LEPTON_VERSION;
        }
        elseif (defined('CAT_VERSION')) {
            $cms_type = 'BlackCat';
            $cms_version = CAT_VERSION;
        }
        elseif (defined('WB_VERSION')) {
            $cms_type = 'WebsiteBaker';
            $cms_version = WB_VERSION;
            // fix for WB 2.8.4
            if (($cms_version == '2.8.3') && file_exists(WB_PATH.'/setup.ini.php')) {
                $cms_version = '2.8.4';
            }
        }
        else {
            $cms_type = '- unknown -';
            $cms_version = '0.0.0';
        }

        $use_alternate_parameter = false;
        $config_path = realpath(__DIR__.'/../../../../../../config/cms.json');
        if (file_exists($config_path)) {
            $config = json_decode(file_get_contents($config_path), true);
            if (isset($config['OUTPUT_FILTER']['METHOD']) && ($config['OUTPUT_FILTER']['METHOD'] == 'ALTERNATE')) {
                $use_alternate_parameter = true;
            }
        }

        $kit_command = array();
        $load_css = array();
        //preg_match_all('/(~~ ).*( ~~)/', $content, $matches, PREG_SET_ORDER);
        preg_match_all('/(~~)( |&nbsp;)(.){3,256}( |&nbsp;)(~~)/', $content, $matches, PREG_SET_ORDER);
        foreach ($matches as $match) {
            if (PAGE_ID < 1) {
                // no regular page, probably the search function where we don't
                // want to execute any kitCommand, so we remove it and continue
                $content = str_replace($match[0], '', $content);
                continue;
            }

            $command_expression = str_ireplace("&nbsp;", ' ', $match[0]);
            // get the expression without leading and trailing ~~
            $command_string = trim(str_replace('~~', '', $command_expression));

            if (empty($command_string)) continue;


            // explode the string into an array by spaces
            $command_array = explode(' ', $command_string);
            // the first match is the command!
            $command = strtolower(trim($command_array[0]));
            // delete the command from array
            unset($command_array[0]);
            // get the parameter string
            $parameter_string = implode(' ', $command_array);
            $params = array();
            $css_loaded = false;
            // now we search for the parameters
            preg_match_all('/([a-z,A-Z,0-9,_]{2,32}([ ]){0,2}\[)(.*?)(])/', $parameter_string, $parameter_matches, PREG_SET_ORDER);
            // loop through the parameters
            foreach ($parameter_matches as $parameter_match) {
                // the bracket [ separate key and value
                $parameter_pair = explode('[', $parameter_match[0]);
                // no pair? continue!
                if (count($parameter_pair) != 2) continue;
                // separate the key
                $key = strtolower(trim(strip_tags($parameter_pair[0])));
                // separate the value
                $value = trim(strip_tags(substr($parameter_pair[1], 0, strrpos($parameter_pair[1], ']'))));
                // add to the params array
                $params[$key] = $value;
                if ($parseCMS) {
                    // only css and js within the CMS!
                    if (($key == 'css') || ($key == 'js')) {
                        // we have to load an additional CSS file
                        if ($this->checkLoadFile($content, $command, $key, $value) && ($key == 'css')) {
                            $css_loaded = true;
                        }
                    }
                }
            }
            if ($parseCMS) {
                // parse() is executed for the CMS content!
                if (!$css_loaded) {
                    // load the kitCommand default CSS file
                    $this->load_css_file($content, 'basic', '/kitcommand/css/kitcommand.css', 'default');
                }
                $cmd_array = array(
                    'cms' => array(
                        'type' => $cms_type,
                        'version' => $cms_version,
                        'locale' => strtolower(LANGUAGE),
                        'page_id' => PAGE_ID,
                        'page_url' => $this->getURLbyPageID(PAGE_ID),
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
                    'parameter' => $params,
                );
                $kit_filter = false;
                if ($use_alternate_parameter) {
                    $command_url = WB_URL.'/kit2/kit_command/'.$command.'/'.base64_encode(json_encode($cmd_array));
                }
                else {
                    $command_url = WB_URL.'/kit2/kit_command/'.$command;
                }
                if ((false !== ($pos = strpos($command, 'filter:'))) && ($pos == 0)) {
                    $kit_filter = true;
                    $command = trim(substr($command, strlen('filter:')));
                    $cmd_array['content'] = $content;
                    $cmd_array['filter_expression'] = $command_expression;
                    $command_url = WB_URL.'/kit2/kit_filter/'.$command;
                    if ($use_alternate_parameter) {
                        $command_url = WB_URL.'/kit2/kit_filter/'.$command.'/'.base64_encode(json_encode($cmd_array));
                    }
                }
                $options = array(
                    CURLOPT_POST => true,
                    CURLOPT_HEADER => false,
                    CURLOPT_URL => $command_url,
                    CURLOPT_FRESH_CONNECT => true,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_FORBID_REUSE => true,
                    CURLOPT_TIMEOUT => 4,
                    CURLOPT_POSTFIELDS => http_build_query(array('cms_parameter' => base64_encode(json_encode($cmd_array)))),
                    CURLOPT_SSL_VERIFYHOST => false,
                    CURLOPT_SSL_VERIFYPEER => false
                );
                $ch = curl_init();
                curl_setopt_array($ch, $options);

                if (false === ($response = curl_exec($ch))) {
                    trigger_error(curl_error($ch));
                }
                curl_close($ch);
                if ($kit_filter && !key_exists('help', $params)) {
                    $content = $response;
                }
                else {
                    $content = str_replace($command_expression, $response, $content);
                }
            }
            else {
                // parse() is executed within the Framework !!!
                $kit_command[] = array(
                    'cms' => array(
                        'locale' => 'en',
                        'page_id' => '-1',
                        'page_url' => '',
                        'user' => array(
                            'id' => -1,
                            'name' => '',
                            'email' => ''
                        ),
                        'special' => array(
                            'post_id' => null,
                            'topic_id' => null
                        )
                    ),
                    'GET' => array(),
                    'POST' => array(),
                    'command' => $command,
                    'parameter' => $params,
                    'expression' => $command_expression
                );
            }
        }
        return $content;
    }
}
