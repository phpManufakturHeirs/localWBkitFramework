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

use phpManufaktur\Basic\Control\CMS\WebsiteBaker\OutputFilter as WebsiteBakerOutputFilter;

// no autoloading at this point !!!
require_once WB_PATH.'/kit2/extension/phpmanufaktur/phpManufaktur/Basic/Control/CMS/WebsiteBaker/OutputFilter.php';

/**
 * SearchFilter for the Content Management System WebsiteBaker
 *
 * @author Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 *
 */
class SearchFilter
{
    protected static $result = false;
    protected static $search = null;
    protected static $search_words = null;

    /**
     * Execute the search function of the given $command.
     * Use the print_excerpt2() function of the CMS to push search result
     *
     * @param string $command
     * @param string $param_str Base64 and JSON encoded parameters
     */
    protected function execCurl($command, $param_str)
    {
        $options = array(
            CURLOPT_POST => true,
            CURLOPT_HEADER => false,
            CURLOPT_URL => WB_URL."/kit2/kit_search/command/$command",
            CURLOPT_FRESH_CONNECT => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FORBID_REUSE => true,
            CURLOPT_TIMEOUT => 4,
            CURLOPT_POSTFIELDS => http_build_query(array('cms_parameter' => $param_str)),
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_SSL_VERIFYPEER => false
        );
        $ch = curl_init();
        curl_setopt_array($ch, $options);

        if (false === ($response = curl_exec($ch))) {
            trigger_error(curl_error($ch));
        }
        curl_close($ch);

        $response = json_decode(base64_decode($response), true);

        if (isset($response['search']) && $response['search']['success']) {
            // continue only with search results
            $item = array(
                'page_link' => isset($response['search']['page']['url']) ? $response['search']['page']['url'] : self::$search['page_link'],
                'page_title' => isset($response['search']['page']['title']) ? $response['search']['page']['title'] : self::$search['page_title'],
                'page_description' => isset($response['search']['page']['description']) ? $response['search']['page']['description'] : self::$search['page_description'],
                'page_modified_when' => isset($response['search']['page']['modified_when']) ? $response['search']['page']['modified_when'] : self::$search['page_modified_when'],
                'page_modified_by' => isset($response['search']['page']['modified_by']) ? $response['search']['page']['modified_by'] : self::$search['page_modified_by'],
                'text' => isset($response['search']['text']) ? $response['search']['text'] : '',
                'pic_link' => isset($response['search']['image_link']) ? $response['search']['image_link'] : '',
                'max_excerpt_num' => isset($response['max_excerpt']) ? $response['max_excerpt'] : self::$search['default_max_excerpt']
            );
            if (print_excerpt2($item, self::$search)) {
                self::$result = true;
            }
        }
    }

    /**
     * Create the parameter array which will be submitted to the kitFramework
     * search function of the kitCommand
     *
     * @param array $parameter
     * @return array
     */
    protected function createParameterArray($parameter)
    {
        return array(
            'cms' => array(
                'type' => defined('LEPTON_VERSION') ? 'LEPTON' : 'WebsiteBaker',
                'version' => defined('LEPTON_VERSION') ? LEPTON_VERSION : WB_VERSION,
                'locale' => strtolower(LANGUAGE),
                'url' => WB_URL,
                'path' => WB_PATH,
                // PAGE_ID where the kitframework_search is placed!
                'page_id' => PAGE_ID,
                // PAGE URL where the kitframework_search is placed!
                'page_url' => WebsiteBakerOutputFilter::getURLbyPageID(PAGE_ID)
            ),
            'search' => array(
                'success' => false,
                'page' => array(
                    'id' => $parameter['page_id'],
                    'section_id' => $parameter['section_id'],
                    'title' => $parameter['page_title'],
                    'description' => $parameter['description'],
                    'keywords' => $parameter['keywords'],
                    'url' => $parameter['url'],
                    'modified_when' => $parameter['modified_when'],
                    'modified_by' => $parameter['modified_by']
                ),
                'words' => self::$search_words,
                'match' => self::$search['search_match'],
                'max_excerpt' => self::$search['default_max_excerpt'],
                'image_link' => '',
                'text' => ''
            )
        );
    }

    /**
     * Parse the given content for kitCommands and execute the search function
     *
     * @param string $content
     * @param string $parameter_string
     */
    protected function parseContent($content, $parameter)
    {
        preg_match_all('/(~~)( |&nbsp;)(.){3,256}( |&nbsp;)(~~)/', $content, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
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
            }
            if ((isset($params['search']) && ((strtolower($params['search']) == 'false') || ($params['search'] == 0))) ||
                (isset($params['help']))) {
                // the search function is disabled or user try to search in the help function, continue ...
                continue;
            }
            $parameter['parameter'] = $params;
            $parameter_string = base64_encode(json_encode($parameter));
            // execute the search
            $this->execCurl($command, $parameter_string);
        }
    }

    /**
     * Check if the given $table exists
     *
     * @param string $table
     * @throws \Exception
     * @return boolean
     */
    protected function tableExists($table)
    {
        global $database;

        if (null == ($query = $database->query("SHOW TABLES LIKE '$table'"))) {
            throw new \Exception($database->get_error());
        }
        return (false !== ($row = $query->fetchRow(MYSQL_ASSOC)));
    }


    /**
     * The search connector between WebsiteBaker, LEPTON, BlackCat and the kitCommand
     *
     * @param array $search
     * @throws \Exception
     * @return boolean true on success
     */
    public function search($search)
    {
        global $database;

        self::$result = false;
        self::$search = $search;
        self::$search_words = array();

        foreach (self::$search['search_url_array'] as $word) {
            self::$search_words[] = strip_tags($word);
        }

        // first step: search for kitCommands in WYSIWYG sections
        $SQL = "SELECT `section_id`, `page_id`, `content` FROM `".TABLE_PREFIX."mod_wysiwyg` WHERE `content` LIKE '%~~ % ~~%'";
        if (null == ($query = $database->query($SQL))) {
            throw new \Exception($database->get_error());
        }

        while (false !== ($wysiwyg = $query->fetchRow(MYSQL_ASSOC))) {

            $SQL = "SELECT `page_title`, `description`, `keywords`, `modified_when`, `modified_by` FROM `".TABLE_PREFIX."pages` WHERE `page_id`='".$wysiwyg['page_id']."'";
            if (null == ($page_query = $database->query($SQL)))
                throw new \Exception($database->get_error());
            $page = $page_query->fetchRow(MYSQL_ASSOC);

            $parameter = $this->createParameterArray(array(
                'page_id' => $wysiwyg['page_id'],
                'section_id' => $wysiwyg['section_id'],
                'page_title' => $page['page_title'],
                'description' => $page['description'],
                'keywords' => $page['keywords'],
                'url' => WebsiteBakerOutputFilter::getURLbyPageID($wysiwyg['page_id']),
                'modified_when' => $page['modified_when'],
                'modified_by' => $page['modified_by']
            ));
            // parse the content
            $this->parseContent($wysiwyg['content'], $parameter);
        }

        // second step: search for kitCommands in NEWS articles
        if ($this->tableExists(TABLE_PREFIX.'mod_news_posts')) {
            $SQL = "SELECT * FROM `".TABLE_PREFIX."mod_news_posts` WHERE `content_long` LIKE '%~~ % ~~%' AND `active`='1'";
            if (null == ($query = $database->query($SQL))) {
                throw new \Exception($database->get_error());
            }
            while (false !== ($news = $query->fetchRow(MYSQL_ASSOC))) {
                $parameter = $this->createParameterArray(array(
                    'page_id' => $news['page_id'],
                    'section_id' => $news['section_id'],
                    'page_title' => $news['title'],
                    'description' => strip_tags($news['content_short']),
                    'keywords' => '',
                    'url' => WB_URL.PAGES_DIRECTORY.$news['link'].PAGE_EXTENSION,
                    'modified_when' => $news['posted_when'],
                    'modified_by' => $news['posted_by']
                ));
                // parse the content
                $this->parseContent($news['content_long'], $parameter);
            }
        }

        // third step: search for kitCommands in TOPICS articles
        if ($this->tableExists(TABLE_PREFIX.'mod_topics')) {
            $SQL = "SELECT * FROM `".TABLE_PREFIX."mod_topics` WHERE `content_long` LIKE '%~~ % ~~%' AND `active`>'1'";
            if (null == ($query = $database->query($SQL))) {
                throw new \Exception($database->get_error());
            }

            global $topics_directory;
            include WB_PATH . '/modules/topics/module_settings.php';

            while (false !== ($topics = $query->fetchRow(MYSQL_ASSOC))) {
                $parameter = $this->createParameterArray(array(
                    'page_id' => $topics['page_id'],
                    'section_id' => $topics['section_id'],
                    'page_title' => $topics['title'],
                    'description' => $topics['description'],
                    'keywords' => $topics['keywords'],
                    'url' => WB_URL . $topics_directory . $topics['link'] . PAGE_EXTENSION,
                    'modified_when' => $topics['published_when'],
                    'modified_by' => $topics['posted_by']
                ));
                // parse the content
                $this->parseContent($topics['content_long'], $parameter);
            }
        }

        return self::$result;
    }
}
