<?php

/**
 * kitFramework::Basic
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de
 * @copyright 2014 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\Basic\Control\kitCommand;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use phpManufaktur\Basic\Data\CMS\Page;

class Parser
{
    protected $app = null;
    protected static $content = null;
    protected static $cms = null;
    protected static $GET = null;
    protected static $POST = null;
    protected static $locale = null;
    protected static $command = null;
    protected static $filter = null;

    protected static $header_keys = array(
        // order => order to load in head!
        'generator',
        'robots',
        'set_header',
        'library',
        'js',
        'css',
        'canonical',
        'fuid',
    );

    /**
     * Initialize the Parser
     *
     * @param Application $app
     */
    protected function initialize(Application $app)
    {
        $this->app = $app;

        // get the parse array with all information
        $parse = $app['request']->request->get('parse', null, true);

        if (isset($parse['cms']) && is_array($parse['cms'])) {
            self::$cms = $parse['cms'];
        }
        if (isset($parse['GET']) && is_array($parse['GET'])) {
            self::$GET = $parse['GET'];
        }
        if (isset($parse['POST']) && is_array($parse['POST'])) {
            self::$POST = $parse['POST'];
        }
        if (isset($parse['content'])) {
            self::$content = $parse['content'];
        }

        if (isset($parse['cms']['locale'])) {
            // set the locale from the CMS locale
            self::$locale = $parse['cms']['locale'];
            $app['translator']->setLocale(self::$locale);
        }
    }

    /**
     * Parse the content for kitCommands and kitFilters and collect them
     *
     */
    protected function parseCommands($content)
    {
        self::$command = array();
        self::$filter = array();

        preg_match_all('/(~~)( |&nbsp;)(.){3,512}( |&nbsp;)(~~)/', $content, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            if (self::$cms['remove_commands']) {
                // the CMS forbid to execute kitCommands at this page, so we remove them!
                $content = str_replace($match[0], '', $content);
                continue;
            }

            $command_expression = $match[0];

            // get the expression without leading and trailing ~~
            $command_string = trim(str_ireplace(array('~~', '&nbsp;'), array('', ' '), $command_expression));
            if (empty($command_string)) {
                // nothing to do ...
                continue;
            }

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
                if (count($parameter_pair) != 2) {
                    continue;
                }
                // separate the key and lowercase
                $key = strtolower(trim(strip_tags($parameter_pair[0])));
                // separate the value
                $value = trim(strip_tags(substr($parameter_pair[1], 0, strrpos($parameter_pair[1], ']'))));
                // add to the params array
                $params[$key] = $value;
            }

            // now we have to check for parameters which must be processed before executing the kitCommand

            if (isset($params['cache']) && (($params['cache'] == '0') ||
                (strtolower($params['cache']) == 'false'))) {
                // clear the Twig cache
                $this->app['twig']->clearCacheFiles();
                $this->app['filesystem']->remove(FRAMEWORK_TEMP_PATH.'/cache');
                $this->app['monolog']->addDebug('Clear the Twig Cache Files!');
            }

            if (isset($params['simulate'])) {
                // prevent this command from execution and show the expression in output
                $command = 'simulate';
                $simulate_expression = substr($command_expression, stripos($command_expression, 'simulate['));
                $simulate_expression = substr($simulate_expression, 0, strpos($simulate_expression, ']')+2);
                $new_expression = str_replace($simulate_expression, '', $command_expression);
                $params = array(
                    'expression' => str_replace('~~', '~<span class="command-disturb">~</span>', $new_expression),
                    'command' => $new_expression,
                    'action' => $params['simulate'],
                    'debug' => isset($params['debug']) ? $params['debug'] : 'false'
                );
            }

            // collect the filters and commands
            if ((false !== ($pos = strpos($command, 'filter:'))) && ($pos == 0) && !array_key_exists('help', $params)) {
                // this is a kitFilter
                self::$filter[] = array(
                    'filter' => trim(substr($command, strlen('filter:'))),
                    'parameter' => $params,
                    'expression' => $command_expression
                );
                $this->app['monolog']->addDebug("parseCommands() add kitFilter $command", array(
                    'parameter' => $params,
                    'expression' => $command_expression
                ));
            }
            else {
                self::$command[] = array(
                    'command' => $command,
                    'parameter' => $params,
                    'expression' => $command_expression
                );
                $this->app['monolog']->addDebug("parseCommands() add kitCommand $command", array(
                    'parameter' => $params,
                    'expression' => $command_expression
                ));
            }
        }
        return $content;
    }

    /**
     * Replace the kitCommand expression with the response of the command
     *
     * @param string $expression
     * @param string $replace
     */
    protected function replaceCommand($expression, $replace)
    {
        // replace the kitCommand
        $search = str_replace(array('[','|'), array('\[','\|'), $expression);
        if (preg_match('%<[^>\/]+>\s*'.$search.'\s*<\/[^>]+>%si', self::$content, $hits)) {
            // also remove the tags around the kitCommand expression!
            self::$content = str_replace($hits[0], $replace, self::$content);
        }
        else {
            // only replace the kitCommand
            self::$content = str_replace($expression, $replace, self::$content);
        }
    }

    /**
     * Execute the submitted commands
     *
     */
    protected function executeCommands()
    {
        $processed_commands = array();
        foreach (self::$command as $command) {
            try {
                if (isset($command['parameter']['help'])) {
                    // don't execute the command, show the help file instead!
                    $route = '/command/help?command='.$command['command'];
                }
                else {
                    // regular route for the command
                    $route = '/command/'.$command['command'];
                }
                $subRequest = Request::create($route, 'POST', array(
                    'cms' => self::$cms,
                    'GET' => self::$GET,
                    'POST' => self::$POST,
                    'command' => $command['command'],
                    'parameter' => $command['parameter'],
                    'expression' => $command['expression']
                ));
                // important: we dont want that app->handle() catch errors, so set the third parameter to false!
                $response = $this->app->handle($subRequest, HttpKernelInterface::SUB_REQUEST, false)->getContent();
                if ($this->app['utils']->isJSON($response)) {
                    // the command returned a JSON string
                    $json = json_decode($response, true);
                    if (!isset($json['response'])) {
                        throw new \Exception('The kitCommand '.$command['command'].' returned a JSON string but the field "response" is missing!');
                    }
                    $this->replaceCommand($command['expression'], $json['response']);
                    if (isset($json['parameter']) && is_array($json['parameter']) && !empty($json['parameter'])) {
                        // add the additional parameters to process them by the controller
                        foreach ($json['parameter'] as $key => $value) {
                            if (!isset($command['parameter'][$key])) {
                                // extension does not overwrite existing parameter values!
                                $command['parameter'][$key] = $value;
                            }
                        }
                    }
                }
                else {
                    // the command return a simple string as response
                    $this->replaceCommand($command['expression'], $response);
                }
                $processed_commands[] = $command;
            }
            catch (\Exception $e) {
                // always report problems!
                $this->app['monolog']->addError($e, array($e->getFile(), $e->getLine()));

                if (isset($command['parameter']['debug']) && ((strtolower($command['parameter']['debug']) == 'true') ||
                    ($command['parameter']['debug'] == 1) || ($command['parameter']['debug'] == ''))) {
                        // the debug parameter isset, so return the extended error information
                        $debug = array(
                            'command' => $command['command'],
                            'file' => substr($e->getFile(), strlen(FRAMEWORK_PATH)),
                            'line' => $e->getLine(),
                            'message' => $e->getMessage()
                        );
                        $replace = $this->app['twig']->render($this->app['utils']->getTemplateFile(
                            '@phpManufaktur/Basic/Template', 'kitcommand/debug.twig'),
                            array('debug' => $debug));
                        $this->replaceCommand($command['expression'], $replace);
                }
                else {
                    // no debug parameter, we assume that the kitCommand does not exists
                    $replace = $this->app['twig']->render($this->app['utils']->getTemplateFile(
                        '@phpManufaktur/Basic/Template', 'kitcommand/error.twig'),
                        array('command' => $command['command']));
                    $this->replaceCommand($command['expression'], $replace);
                }
            }
        }
        self::$command = $processed_commands;
    }

    /**
     * Execute the submitted filters
     *
     */
    protected function executeFilters()
    {
        foreach (self::$filter as $filter) {
            try {
                if (isset($filter['parameter']['help'])) {
                    // don't execute the filter, show the help file instead!
                    $route = '/command/help?command=filter:'.$filter['filter'];
                }
                else {
                    // regular route for the filter
                    $route = '/filter/'.$filter['filter'];
                }
                $subRequest = Request::create($route, 'POST', array(
                    'cms' => self::$cms,
                    'GET' => self::$GET,
                    'POST' => self::$POST,
                    'filter' => $filter['filter'],
                    'parameter' => $filter['parameter'],
                    'filter_expression' => $filter['expression'],
                    'content' => self::$content
                ));
                // important: we dont want that app->handle() catch errors, so set the third parameter to false!
                $response = $this->app->handle($subRequest, HttpKernelInterface::SUB_REQUEST, false)->getContent();
                if (isset($command['parameter']['help'])) {
                    $this->replaceCommand($filter['expression'], $response);
                }
                else {
                    self::$content = $response;
                }
            }
            catch (\Exception $e) {
                // always report problems!
                $this->app['monolog']->addError($e, array($e->getFile(), $e->getLine()));

                if (isset($filter['parameter']['debug']) && ((strtolower($filter['parameter']['debug']) == 'true') ||
                    ($filter['parameter']['debug'] == 1) || ($filter['parameter']['debug'] == ''))) {
                        // the debug parameter isset, so return the extended error information
                        $debug = array(
                            'filter' => $filter['filter'],
                            'file' => substr($e->getFile(), strlen(FRAMEWORK_PATH)),
                            'line' => $e->getLine(),
                            'message' => $e->getMessage()
                        );
                        $replace = $this->app['twig']->render($this->app['utils']->getTemplateFile(
                            '@phpManufaktur/Basic/Template', 'kitfilter/debug.twig'),
                            array('debug' => $debug));
                        $this->replaceCommand($filter['expression'], $replace);
                    }
                    else {
                        // no debug parameter, we assume that the kitFilter does not exists
                        $replace = $this->app['twig']->render($this->app['utils']->getTemplateFile(
                            '@phpManufaktur/Basic/Template', 'kitfilter/error.twig'),
                            array('filter' => $filter['filter']));
                        $this->replaceCommand($filter['expression'], $replace);
                    }
            }
        }
    }

    /**
     * Update or create meta tags
     *
     * @param string $meta_name the name of the meta tag
     * @param string $meta_content the content (value) of the meta tag
     * @param string reference $content
     * @return boolean
     */
    protected function setMetaTag($meta_name, $meta_content)
    {
        $DOM = new \DOMDocument;

        // enable internal error handling
        libxml_use_internal_errors(true);
        if (!$DOM->loadHTML(self::$content)) {
            // on error still return false
            return false;
        }
        libxml_clear_errors();

        $changed = false;

        $metas = $DOM->getElementsByTagName('meta');
        foreach ($metas as $meta) {
            if (strtolower($meta->getAttribute('name')) == $meta_name) {
                // update the existing meta tag
                $meta->setAttribute('content', $meta_content);
                $changed = true;
                break;
            }
        }

        if (!$changed) {
            // create a new meta tag
            $meta = $DOM->createElement('meta');
            $meta->setAttribute('name', $meta_name);
            $meta->setAttribute('content', $meta_content);
            $head = $DOM->getElementsByTagName('head')->item(0);
            if (!is_object($head)) {
                // problem initializing - leave here and just return false
                return false;
            }
            $head->appendChild($meta);
        }

        self::$content = $DOM->saveHTML();
        return true;
    }

    /**
     * Load a CSS files with DOM
     *
     * @param array $css_files
     * @return boolean
     */
    protected function loadCSSfiles($css_files)
    {
        // create DOM
        $DOM = new \DOMDocument;
        // enable internal error handling for the DOM
        libxml_use_internal_errors(true);
        if (!$DOM->loadHTML(self::$content)) {
            // on error still return false
            return false;
        }
        libxml_clear_errors();

        $links = $DOM->getElementsByTagName('link');

        foreach ($css_files as $css_file) {
            $load_css = true;
            foreach ($links as $link) {
                if ($link->getAttribute('rel') == 'stylesheet') {
                    if ($css_file == $link->getAttribute('href')) {
                        $load_css = false;
                        break;
                    }
                }
            }
            if ($load_css) {
                // create a new link tag for the CSS file
                $link = $DOM->createElement('link');
                $link->setAttribute('rel', 'stylesheet');
                $link->setAttribute('type', 'text/css');
                $link->setAttribute('media', 'all');
                $link->setAttribute('href', $css_file);
                $head = $DOM->getElementsByTagName('head')->item(0);
                if (!is_object($head)) {
                    // problem initializing - leave here and just return false
                    return false;
                }
                $head->appendChild($link);
            }
        }
        self::$content = $DOM->saveHTML();
        return true;
    }

    /**
     * Load a JS files with DOM
     *
     * @param array $js_files
     * @return boolean
     */
    protected function loadJSfiles($js_files)
    {
        // create DOM
        $DOM = new \DOMDocument;
        // enable internal error handling for the DOM
        libxml_use_internal_errors(true);
        if (!$DOM->loadHTML(self::$content)) {
            // on error still return false
            return false;
        }
        libxml_clear_errors();

        $scripts = $DOM->getElementsByTagName('script');

        foreach ($js_files as $js_file) {
            $load_js = true;
            foreach ($scripts as $script) {
                if ($script->getAttribute('type') == 'text/javascript') {
                    if ($js_file == $script->getAttribute('src')) {
                        $load_js = false;
                        break;
                    }
                }
            }
            if ($load_js) {
                // create a new script tag for the JS file
                $script = $DOM->createElement('script');
                $script->setAttribute('type', 'text/javascript');
                $script->setAttribute('src', $js_file);
                $head = $DOM->getElementsByTagName('head')->item(0);
                if (!is_object($head)) {
                    // problem initializing - leave here and just return false
                    return false;
                }
                $head->appendChild($script);
            }
        }
        self::$content = $DOM->saveHTML();
        return true;
    }

    /**
     * Try to get header information from the command and set them
     *
     * @param string $command
     * @param integer $id
     * @return boolean
     */
    protected function setHeader($command, $id)
    {
        try {
            $route = "/command/$command/getheader/id/$id";
            $subRequest = Request::create($route, 'POST');
            // important: we dont want that app->handle() catch errors, so set the third parameter to false!
            $response = $this->app->handle($subRequest, HttpKernelInterface::SUB_REQUEST, false)->getContent();
        }
        catch (\Exception $e) {
            $this->app['monolog']->addError($e->getMessage(), array($e->getLine(), $e->getFile()));
            return false;
        }
        if ($this->app['utils']->isJSON($response)) {
            $header = json_decode($response, true);

            // create DOM
            $DOM = new \DOMDocument;
            // enable internal error handling for the DOM
            libxml_use_internal_errors(true);
            if (!$DOM->loadHTML(self::$content)) {
                // on error still return false
                return false;
            }
            libxml_clear_errors();
            $changed = false;

            if (isset($header['title']) && !empty($header['title'])) {
                $titles = $DOM->getElementsByTagName('title');
                if (!is_null($titles->item(0))) {
                    $titles->item(0)->nodeValue = $header['title'];
                    $changed = true;
                }
                else {
                    // missing the <title> tag in the CMS template, insert it:
                    $title = $DOM->createElement('title', $header['title']);
                    $head = $DOM->getElementsByTagName('head')->item(0);
                    $head->appendChild($title);
                    $changed = true;
                }
            }

            $metas = $DOM->getElementsByTagName('meta');
            foreach ($metas as $meta) {
                if ((strtolower($meta->getAttribute('name')) == 'description')  &&
                    (isset($header['description']) && !empty($header['description']))) {
                    $meta->setAttribute('content', $header['description']);
                    $changed = true;
                }
                if ((strtolower($meta->getAttribute('name')) == 'keywords') &&
                    (isset($header['keywords']) && !empty($header['keywords']))) {
                    $meta->setAttribute('content', $header['keywords']);
                    $changed = true;
                }
            }
            if ($changed) {
                self::$content = $DOM->saveHTML();
            }
        }
    }

    /**
     * Try to get a canonical URL and set it
     *
     * @param string $command
     * @param mixed $id
     * @return boolean
     */
    protected function setCanonicalLink($command, $id)
    {
        if (filter_var($id, FILTER_VALIDATE_INT)) {
            $route = "/command/$command/canonical/id/$id";
            $subRequest = Request::create($route, 'POST');
            try {
                // important: we dont want that app->handle() catch errors, so set the third parameter to false!
                $response = $this->app->handle($subRequest, HttpKernelInterface::SUB_REQUEST, false)->getContent();
                if (!$this->app['utils']->isJSON($response)) {
                    return false;
                }
                $json = json_decode($response, true);
                $canonical_url = $json['canonical_url'];
            }
            catch (\Exception $e) {
                $this->app['monolog']->addError($e->getMessage(), array($e->getLine(), $e->getFile()));
                return false;
            }
        }
        elseif (filter_var($id, FILTER_VALIDATE_URL)) {
            // the $id is a URL, so we use this
            $canonical_url = (!parse_url($id, PHP_URL_SCHEME)) ? 'http://'.$id : $id;
        }
        else {
            // no valid identifier ...
            return false;
        }

        $DOM = new \DOMDocument;
        // enable internal error handling
        libxml_use_internal_errors(true);
        if (!$DOM->loadHTML(self::$content)) {
            // on error still return false
            return false;
        }
        libxml_clear_errors();
        $changed = false;

        $links = $DOM->getElementsByTagName('link');
        foreach ($links as $link) {
            if (strtolower($link->getAttribute('rel')) == 'canonical') {
                // update the existing link tag
                $link->setAttribute('url', $canonical_url);
                $changed = true;
                break;
            }
        }

        if (!$changed) {
            // create a new link tag
            $link = $DOM->createElement('link');
            $link->setAttribute('rel', 'canonical');
            $link->setAttribute('url', $canonical_url);
            $head = $DOM->getElementsByTagName('head')->item(0);
            if (!is_object($head)) {
                // problem initializing - leave here and just return false
                return false;
            }
            $head->appendChild($link);
        }

        // if a canonical link is set we have to check also an existing OpenGraph URL!
        $metas = $DOM->getElementsByTagName('meta');
        foreach ($metas as $meta) {
            if (strtolower($meta->getAttribute('property')) == 'og:url') {
                // update the existing og:url tag
                $meta->setAttribute('content', $canonical_url);
                break;
            }
        }

        self::$content = $DOM->saveHTML();
    }

    /**
     * Attach a FUID=FRAMEWORK_UID parameter to all WB_URL links which not point
     * to the PAGES directory, assuming that these are kitFramework permanent links.
     *
     * @return boolean
     */
    protected function attachFUIDtoPermalinks()
    {
        $DOM = new \DOMDocument;
        // enable internal error handling
        libxml_use_internal_errors(true);
        // need a hack to properly handle UTF-8 encoding
        if (!$DOM->loadHTML(self::$content)) {
            // on error still return false
            return false;
        }
        libxml_clear_errors();

        $changed = false;

        $Page = new Page($this->app);
        $page_directory = $Page->getPageDirectory();

        $links = $DOM->getElementsByTagName('a');
        foreach ($links as $link) {
            $item = $link->getAttribute('href');
            if ((false !== stripos($item, CMS_URL)) && (false === stripos($item, CMS_URL.$page_directory.'/'))) {
                // URL inside the installation but outside of the pages directory - possibly permanent link
                if (false === strpos($item, '?')) {
                    // item has no query
                    $item = $item.'?fuid='.FRAMEWORK_UID;
                    $link->setAttribute('href', $item);
                    $changed = true;
                }
                else {
                    $query_str = parse_url($item, PHP_URL_QUERY);
                    $query_array = strpos($query_str, '&') ? explode('&', $query_str) : array($query_str);
                    $fuid_exists = false;
                    foreach ($query_array as $query_item) {
                        if (strpos($query_item, '=')) {
                            list($key, $value) = explode('=', $query_item);
                            if ($key == 'fuid') {
                                $fuid_exists = true;
                                break;
                            }
                        }
                    }
                    if (!$fuid_exists) {
                        $item = $item.'?fuid='.FRAMEWORK_UID;
                        $link->setAttribute('href', $item);
                        $changed = true;
                    }
                }
            }
        }
        if ($changed) {
            self::$content = $DOM->saveHTML();
        }
    }

    /**
     * Process the page header, add meta tags, load CSS ...
     *
     * @return boolean
     */
    protected function processPageHeader()
    {
        if (empty(self::$command)) {
            // nothing to do
            return false;
        }

        // set generator meta tag?
        $generator_info = true;
        $css_files = array();
        $js_files = array();

        foreach (self::$header_keys as $header_key) {
            switch ($header_key) {
                case 'css':
                    $load_css = true;
                    foreach (self::$command as $command) {
                        if (isset($command['parameter']['load_css']) &&
                            (($command['parameter']['load_css'] == 0) || (strtolower($command['parameter']['load_css']) == 'false'))) {
                            $load_css = false;
                        }
                    }

                    if ($load_css) {
                        $templates = explode(',', FRAMEWORK_TEMPLATES);
                        $css = MANUFAKTUR_URL.'/Basic/Template/default/kitcommand/css/kitcommand.min.css';
                        foreach ($templates as $template) {
                            if ($this->app['filesystem']->exists(MANUFAKTUR_PATH.'/Basic/Template/'.$template.'/kitcommand/css/kitcommand.min.css')) {
                                $css = MANUFAKTUR_URL.'/Basic/Template/'.$template.'/kitcommand/css/kitcommand.min.css';
                                break;
                            }
                            elseif ($this->app['filesystem']->exists(MANUFAKTUR_PATH.'/Basic/Template/'.$template.'/kitcommand/css/kitcommand.css')) {
                                $css = MANUFAKTUR_URL.'/Basic/Template/'.$template.'/kitcommand/css/kitcommand.css';
                                break;
                            }
                        }
                        $css_files[] = $css;

                        foreach (self::$command as $command) {
                            if (isset($command['parameter']['css'])) {
                                if (strpos($command['parameter']['css'], '|')) {
                                    $items = explode('|', $command['parameter']['css']);
                                }
                                else {
                                    $items = array($command['parameter']['css']);
                                }
                                foreach ($items as $item) {
                                    if (empty($item) && $this->app['filesystem']->exists(MANUFAKTUR_PATH.'/'.$command['command'].'/Template/default/screen.css')) {
                                        $css_files[] = MANUFAKTUR_URL.'/'.$command['command'].'/Template/default/screen.css';
                                        continue;
                                    }
                                    $count = substr_count($item, ',');
                                    if (($count == 0) && ($this->app['filesystem']->exists(MANUFAKTUR_PATH.'/'.$item.'/Template/default/screen.css'))) {
                                        $css_files[] = MANUFAKTUR_URL.'/'.$item.'/Template/default/screen.css';
                                    }
                                    elseif ($count == 1) {
                                        list($extension, $file) = explode(',', $item);
                                        if ($this->app['filesystem']->exists(MANUFAKTUR_PATH.'/'.trim($extension).'/Template/default/'.trim($file))) {
                                            $css_files[] = MANUFAKTUR_URL.'/'.trim($extension).'/Template/default/'.trim($file);
                                        }
                                    }
                                    elseif ($count == 2) {
                                        list($extension, $file, $directory) = explode(',', $item);
                                        if ($this->app['filesystem']->exists(MANUFAKTUR_PATH.'/'.trim($extension).'/Template/'.trim($directory).'/'.trim($file))) {
                                            $css_files[] = MANUFAKTUR_URL.'/'.trim($extension).'/Template/'.trim($directory).'/'.trim($file);
                                        }
                                    }
                                }
                            }
                        }
                    }
                    break;
                case 'js':
                    foreach (self::$command as $command) {
                        if (isset($command['parameter']['js'])) {
                            if (strpos($command['parameter']['js'], '|')) {
                                $items = explode('|', $command['parameter']['js']);
                            }
                            else {
                                $items = array($command['parameter']['js']);
                            }
                            foreach ($items as $item) {
                                $count = substr_count($item, ',');
                                if ($count == 1) {
                                    list($extension, $file) = explode(',', $item);
                                    if ($this->app['filesystem']->exists(MANUFAKTUR_PATH.'/'.trim($extension).'/Template/default/'.trim($file))) {
                                        $js_files[] = MANUFAKTUR_URL.'/'.trim($extension).'/Template/default/'.trim($file);
                                    }
                                }
                                elseif ($count == 2) {
                                    list($extension, $file, $directory) = explode(',', $item);
                                    if ($this->app['filesystem']->exists(MANUFAKTUR_PATH.'/'.trim($extension).'/Template/'.trim($directory).'/'.trim($file))) {
                                        $js_files[] = MANUFAKTUR_URL.'/'.trim($extension).'/Template/'.trim($directory).'/'.trim($file);
                                    }
                                }
                            }
                        }
                    }
                    break;
                case 'library':
                    foreach (self::$command as $command) {
                        if (isset($command['parameter']['library'])) {
                            if (strpos($command['parameter']['library'], ',')) {
                                $libraries = explode(',', $command['parameter']['library']);
                            }
                            else {
                                $libraries = array($command['parameter']['library']);
                            }
                            foreach ($libraries as $library) {
                                $library = trim($library);
                                if ($library[0] != '/') {
                                    $library = '/'.$library;
                                }
                                if ($this->app['filesystem']->exists(LIBRARY_PATH.$library)) {
                                    if (strtolower(pathinfo($library, PATHINFO_EXTENSION)) == 'css') {
                                        $css_files[] = LIBRARY_URL.$library;
                                    }
                                    else {
                                        $js_files[] = LIBRARY_URL.$library;
                                    }
                                }
                            }
                        }
                    }
                    break;
                case 'generator':
                    $generator = true;
                    foreach (self::$command as $command) {
                        if (isset($command['parameter']['generator']) && (($command['parameter']['generator'] == 0) ||
                            (strtolower($command['parameter']['generator'] == 'false')))) {
                            $generator = false;
                            break;
                        }
                    }
                    if ($generator) {
                        $this->setMetaTag('generator', 'kitFramework - extend your CMS');
                    }
                    break;
                case 'robots':
                    $robots = null;
                    if (isset(self::$GET['robots']) && !empty(self::$GET['robots'])) {
                        $robots = self::$GET['robots'];
                    }
                    if (is_null($robots)) {
                        foreach (self::$command as $command) {
                            if (isset($command['parameter']['robots']) && !empty($command['parameter']['robots'])) {
                                $robots = $command['parameter']['robots'];
                                break;
                            }
                        }
                    }
                    if (!is_null($robots)) {
                        $this->setMetaTag('robots', $robots);
                    }
                    break;
                case 'set_header':
                    $set_header = null;
                    $set_command = null;
                    if ((isset(self::$GET['command']) && (strtolower(self::$GET['command'] == $command))) &&
                        (isset(self::$GET['set_header']) && (is_numeric(self::$GET['set_header']) && (self::$GET['set_header'] > 0)))) {
                        $set_header = self::$GET['set_header'];
                        $set_command = self::$GET['command'];
                    }
                    if (is_null($set_header)) {
                        foreach (self::$command as $command) {
                            if (isset($command['parameter']['set_header']) && (is_numeric($command['parameter']['set_header']) &&
                                ($command['parameter']['set_header'] > 0))) {
                                $set_header = $command['parameter']['set_header'];
                                $set_command = $command['command'];
                                break;
                            }
                        }
                    }
                    if (!is_null($set_header)) {
                        $this->setHeader($set_command, $set_header);
                    }
                    break;
                case 'canonical':
                    $set_canonical = null;
                    $set_command = null;
                    if (isset(self::$GET['command']) && isset(self::$GET['canonical'])) {
                        $set_canonical = self::$GET['canonical'];
                        $set_command = strtolower(self::$GET['command']);
                    }
                    if (is_null($set_canonical)) {
                        foreach (self::$command as $command) {
                            if (isset($command['parameter']['canonical']) && !empty($command['parameter']['canonical'])) {
                                $set_canonical = $command['parameter']['canonical'];
                                $set_command = $command['command'];
                                break;
                            }
                        }
                    }
                    if (!is_null($set_canonical)) {
                        $this->setCanonicalLink($set_command, $set_canonical);
                    }
                    break;
            }
        }

        if (!empty($js_files)) {
            $this->loadJSfiles($js_files);
        }

        if (!empty($css_files)) {
            $this->loadCSSfiles($css_files);
        }
    }

    /**
     * Extract only the kitCommands from the content and return as array.
     * This function is used by Utils::parseKITcommand()
     *
     * @param Application $app
     * @param string reference $content
     * @return array
     */
    public function getCommandsOnly(Application $app, $content)
    {
        $this->app = $app;
        $this->app['monolog']->addDebug('execute: '.__METHOD__);
        $old_commands = self::$command;
        $old_filters = self::$filter;

        $this->parseCommands($content);

        $commands = self::$command;
        self::$command = $old_commands;
        self::$filter = $old_filters;

        return $commands;
    }


    public function ControllerParser(Application $app)
    {
        $this->initialize($app);
        $this->app['monolog']->addDebug('execute: '.__METHOD__);

        if (is_null(self::$cms) || is_null(self::$content)) {
            // fatal: missing the main information!
            $prompt = 'kitCommand parser got no CMS information or Content!';
            $app['monolog']->addDebug($prompt);
            return $app['translator']->trans($prompt);
        }

        $this->app['monolog']->addDebug('Parse CMS for kitCommands and kitFilter', self::$cms);

        self::$content = $this->parseCommands(self::$content);

        if (empty(self::$command) && empty(self::$filter)) {
            // nothing else to do ...
            return self::$content;
        }

        // execute the kitCommands
        $this->executeCommands();

        // process the page header
        $this->processPageHeader();

        if ((isset(self::$GET['fuid']) && (self::$GET['fuid'] == FRAMEWORK_UID)) ||
            (isset(self::$POST['fuid']) && (self::$POST['fuid'] == FRAMEWORK_UID))) {
           // attach the FUID parameter to all permanent links!
           $this->attachFUIDtoPermalinks();
        }

        // at least execute the kitFilters
        $this->executeFilters();

        // sometimes the filter destroy the brackets of the [wblink123] so we fix it ...
        return str_replace(array('%5B','%5D'), array('[',']'), self::$content );
    }
}
