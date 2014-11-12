<?php

/**
 * kitFramework:Basic
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de
 * @copyright 2013 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\Basic\Control;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use phpManufaktur\Basic\Control\jsonEditor\jsonFormat;
use phpManufaktur\Basic\Control\kitCommand\Parser;

// EXTENSION_PATH is not initialized yet use the full path!
if (file_exists(BOOTSTRAP_PATH.'/extension/phpmanufaktur/phpManufaktur/Library/Extension/htmlpurifier/4.6.0/library/HTMLPurifier.auto.php')) {
    require_once BOOTSTRAP_PATH.'/extension/phpmanufaktur/phpManufaktur/Library/Extension/htmlpurifier/4.6.0/library/HTMLPurifier.auto.php';
}

/**
 * Class with usefull utils for the general usage within the kitFramework
 *
 * @author Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 *
 */
class Utils
{

    protected $app = null;
    protected static $proxy = null;
    protected static $proxy_auth = 'NONE';
    protected static $proxy_port = null;
    protected static $proxy_usrpwd = null;

    /**
     * Constructor for the Utils
     */
    public function __construct (Application $app)
    {
        $this->app = $app;

        // FRAMEWORK_PATH is not set at this point!
        $proxy_file = BOOTSTRAP_PATH.'/config/proxy.json';

        if (file_exists($proxy_file)) {
            // set the proxy options
            $proxy = $this->readJSON($proxy_file);
            if (isset($proxy['PROXYAUTH']) && ($proxy['PROXYAUTH'] != 'NONE')) {
                if (strtoupper($proxy['PROXYAUTH']) == 'NTLM') {
                    self::$proxy_auth = CURLAUTH_NTLM;
                }
                else {
                    self::$proxy_auth = CURLAUTH_BASIC;
                }
                self::$proxy_usrpwd = $proxy['PROXYUSERPWD'];
            }
            self::$proxy = $proxy['PROXY'];
            self::$proxy_port = $proxy['PROXYPORT'];
        }
    }

    /**
     * Return a array with the PROXY settings
     *
     * @return multitype:string NULL
     */
    public function getProxyInfo()
    {
        return array(
            'proxy' => self::$proxy,
            'proxy_auth' => self::$proxy_auth,
            'proxy_usrpwd' => self::$proxy_usrpwd,
            'proxy_port' => self::$proxy_port
        );
    }

    /**
     * Sanitize variables and prepare them for saving in a MySQL record
     *
     * @param mixed $item
     * @return mixed
     */
    public static function sanitizeVariable ($item)
    {
        if (! is_array($item)) {
            // undoing 'magic_quotes_gpc = On' directive
            if (get_magic_quotes_gpc())
                $item = stripcslashes($item);
            $item = self::sanitizeText($item);
        }
        return $item;
    }

    /**
     * Sanitize a text variable and prepare it for saving in a MySQL record
     *
     * @param string $text
     * @return string
     */
    public static function sanitizeText ($text)
    {
        $search = array("<",">","\"","'","\\","\x00","\n","\r","'",'"',"\x1a");
        $replace = array("&lt;","&gt;","&quot;","&#039;","\\\\","\\0","\\n","\\r","\'",'\"',"\\Z");
        return str_replace($search, $replace, $text);
    }

    /**
     * Unsanitize a text variable and prepare it for output
     *
     * @param string $text
     * @return string
     */
    public static function unsanitizeText($text)
    {
        $text = stripcslashes($text);
        $text = str_replace(array("&lt;","&gt;","&quot;","&#039;"), array("<",">","\"","'"), $text);
        return $text;
    }

    /**
     * Generate a globally unique identifier (GUID)
     * Uses COM extension under Windows otherwise
     * create a random GUID in the same style
     *
     * @return string $guid
     */
    public static function createGUID ()
    {
        if (function_exists('com_create_guid')) {
            $guid = com_create_guid();
            $guid = strtolower($guid);
            if (strpos($guid, '{') == 0) {
                $guid = substr($guid, 1);
            }
            if (strpos($guid, '}') == strlen($guid) - 1) {
                $guid = substr($guid, 0, strlen($guid) - 2);
            }
            return $guid;
        } else {
            return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x', mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0x0fff) | 0x4000, mt_rand(0, 0x3fff) | 0x8000, mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff));
        }
    }

    /**
     * Check a password for length, chars, special chars and return a strength
     * value between 1 to 10.
     *
     * @param string $password
     * @return number
     * @link http://www.phpro.org/examples/Password-Strength-Tester.html
     */
    function passwordStrength ($password)
    {
        if (strlen($password) == 0) {
            return 1;
        }
        if (strpos($password, ' ') !== false) {
            return 1;
        }

        $strength = 0;

        // get the length of the password
        $length = strlen($password);

        // check if password is not all lower case
        if (strtolower($password) != $password) {
            $strength += 1;
        }

        // check if password is not all upper case
        if (strtoupper($password) == $password) {
            $strength += 1;
        }

        // check string length is 8 -15 chars
        if ($length >= 8 && $length <= 15) {
            $strength += 1;
        }

        // check if lenth is 16 - 35 chars
        if ($length >= 16 && $length <= 35) {
            $strength += 2;
        }

        // check if length greater than 35 chars
        if ($length > 35) {
            $strength += 3;
        }

        // get the numbers in the password
        preg_match_all('/[0-9]/', $password, $numbers);
        $strength += count($numbers[0]);

        // check for special chars
        preg_match_all('/[|!@#$%&*\/=?,;.:\-_+~^\\\]/', $password, $specialchars);
        $strength += sizeof($specialchars[0]);

        // get the number of unique chars
        $chars = str_split($password);
        $num_unique_chars = sizeof(array_unique($chars));
        $strength += $num_unique_chars * 2;

        // strength is a number 1-10
        $strength = $strength > 99 ? 99 : $strength;
        $strength = floor($strength / 10 + 1);

        return $strength;
    }

    /**
     * Create a password
     *
     * @param number $length default = 12
     * @return string generated password
     */
    public function createPassword($length=12)
    {
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789|!@#$%&*\=?,;.:-_+~/^';
        $password = '';
        $max = strlen($chars) - 1;

        for ($i=0; $i < $length; $i++) {
            $password .= $chars[rand(0, $max)];
        }
        return $password;
    }

    /**
     * Return a valid path to the desired template, depending on the namespace,
     * the preconfigured Framework template names and/or the preferred template
     *
     * @param string $template_namespace the Twig namespace to use
     * @param string $template_file the file to load, you can use leading directories
     * @param string $preferred_template optional specifiy a preferred template
     * @param boolean $return_path return the path instead of the Twig namespace
     * @throws \Exception
     * @return string
     * @deprecated use getTemplateFile() instead!
     */
    public function templateFile($template_namespace, $template_file, $preferred_template='', $return_path=false)
    {
        trigger_error('templateFile() is deprecated, please use getTemplateFile() instead!', E_USER_DEPRECATED);
        return $this->getTemplateFile($template_namespace, $template_file, $preferred_template, $return_path);
    }

    /**
     * Return a valid path to the desired template, depending on the namespace,
     * the preconfigured Framework template names and/or the preferred template
     *
     * @param string $template_namespace the Twig namespace to use
     * @param string $template_file the file to load, you can use leading directories
     * @param string $preferred_template optional specifiy a preferred template
     * @param boolean $return_path return the path instead of the Twig namespace
     * @throws \Exception
     * @return string
     */
    public function getTemplateFile($template_namespace, $template_file, $preferred_template='', $return_path=false)
    {
        $TEMPLATE_NAMESPACES = array(
            'phpManufaktur' => MANUFAKTUR_PATH,
            'phpmanufaktur' => MANUFAKTUR_PATH,
            'thirdParty' => THIRDPARTY_PATH,
            'thirdparty' => THIRDPARTY_PATH
        );
        if ($this->app['filesystem']->exists(MANUFAKTUR_PATH.'/TemplateTools/Pattern')) {
            $TEMPLATE_NAMESPACES['Pattern'] = MANUFAKTUR_PATH.'/TemplateTools/Pattern';
            $TEMPLATE_NAMESPACES['pattern'] = MANUFAKTUR_PATH.'/TemplateTools/Pattern';
            if (defined('CMS_PATH')) {
                $TEMPLATE_NAMESPACES['Templates'] = CMS_PATH.'/templates';
                $TEMPLATE_NAMESPACES['templates'] = CMS_PATH.'/templates';
            }
        }

        if ($template_namespace[0] != '@') {
            throw new \Exception('Namespace expected in variable $template_namespace but path found!');
        }
        // no trailing slash!
        if (strrpos($template_namespace, '/') == strlen($template_namespace) - 1)
            $template_namespace = substr($template_namespace, 0, strlen($template_namespace) - 1);
            // separate the namespace
        if (false === strpos($template_namespace, '/')) {
            // only namespace - no subdirectory!
            $namespace = substr($template_namespace, 1);
            $directory = '';
        } else {
            $namespace = substr($template_namespace, 1, strpos($template_namespace, '/') - 1);
            $directory = substr($template_namespace, strpos($template_namespace, '/'));
        }

        // no leading slash for the template file
        if ($template_file[0] == '/')
            $template_file = substr($template_file, 1);
            // explode the template names
        $template_names = explode(',', FRAMEWORK_TEMPLATES);
        if (!empty($preferred_template)) {
            array_unshift($template_names, $preferred_template);
        }

        // walk through the template names
        foreach ($template_names as $name) {
            $file = $TEMPLATE_NAMESPACES[$namespace] . $directory . '/' . $name . '/' . $template_file;
            if (file_exists($file)) {
                if ($return_path) {
                    // return the PATH
                    return $file;
                }
                else {
                    // success - build the namespace path for Twig
                    return $template_namespace . '/' . $name . '/' . $template_file;
                }
            }
        }
        // Uuups - no template found!
        throw new \Exception(sprintf('Template file %s not found within the namespace %s!', $template_file, $template_namespace));
    }

    /**
     * Formatiert einen BYTE Wert in einen lesbaren Wert und gibt
     * einen Byte, KB, MB oder GB String zurueck
     *
     * @param integer $byte
     * @return string
     */
    public static function bytes2string ($byte)
    {
        if ($byte < 1024) {
            $result = round($byte, 2) . ' Byte';
        }
        elseif ($byte >= 1024 and $byte < pow(1024, 2)) {
            $result = round($byte / 1024, 2) . ' KB';
        }
        elseif ($byte >= pow(1024, 2) and $byte < pow(1024, 3)) {
            $result = round($byte / pow(1024, 2), 2) . ' MB';
        }
        elseif ($byte >= pow(1024, 3) and $byte < pow(1024, 4)) {
            $result = round($byte / pow(1024, 3), 2) . ' GB';
        }
        elseif ($byte >= pow(1024, 4) and $byte < pow(1024, 5)) {
            $result = round($byte / pow(1024, 4), 2) . ' TB';
        }
        elseif ($byte >= pow(1024, 5) and $byte < pow(1024, 6)) {
            $result = round($byte / pow(1024, 5), 2) . ' PB';
        }
        elseif ($byte >= pow(1024, 6) and $byte < pow(1024, 7)) {
            $result = round($byte / pow(1024, 6), 2) . ' EB';
        }
        return $result;
    }

    /**
     * fixes a path by removing //, /../ and other things
     *
     * @access public
     * @param string $path to fix
     * @return string
     *
     */
    public static function sanitizePath ($path)
    {
        // remove / at end of string; this will make sanitizePath fail otherwise!
        $path = preg_replace('~/{1,}$~', '', $path);

        // make all slashes forward
        $path = str_replace('\\', '/', $path);

        // bla/./bloo ==> bla/bloo
        $path = preg_replace('~/\./~', '/', $path);

        // resolve /../
        // loop through all the parts, popping whenever there's a .., pushing otherwise.
        $parts = array();
        foreach (explode('/', preg_replace('~/+~', '/', $path)) as $part) {
            if ($part === ".." || $part == '') {
                array_pop($parts);
            } elseif ($part != "") {
                $parts[] = $part;
            }
        }

        $new_path = implode("/", $parts);

        // windows
        if (! preg_match('/^[a-z]\:/i', $new_path)) {
            $new_path = '/' . $new_path;
        }

        return $new_path;
    }


    /**
     * Transform a string into a float value, using the localized settings for
     * the thousend and decimal separator.
     *
     * @param string $string
     * @return float
     */
    public function str2float($string)
    {
        // remove the localized thousand separator
        $string = str_replace($this->app['translator']->trans('THOUSAND_SEPARATOR'), '', $string);
        // replace the localized decimal separator with a dot
        $string = str_replace($this->app['translator']->trans('DECIMAL_SEPARATOR'), '.', $string);
        return floatval($string);
    }

    /**
     * Transform a string into a integer value, using the localized settings for
     * the thousend and decimal separator.
     *
     * @param string $string
     * @return integer
     */
    public function str2int($string)
    {
        // remove the localized thousand separator
        $string = str_replace($this->app['translator']->trans('THOUSAND_SEPARATOR'), '', $string);
        // replace the localized decimal separator with a dot
        $string = str_replace($this->app['translator']->trans('DECIMAL_SEPARATOR'), '.', $string);
        return intval($string);
    }

    /**
     * Read the specified configuration file in JSON format and return array
     *
     * @param string $file path to JSON file
     * @throws \Exception
     * @return array configuration items
     */
    public function readConfiguration($file)
    {
        if (file_exists($file)) {
            if (null === ($config = json_decode(file_get_contents($file), true))) {
                $code = json_last_error();
                // get JSON error message from last error code
                switch ($code) :
                case JSON_ERROR_NONE:
                    $error = 'No errors';
                break;
                case JSON_ERROR_DEPTH:
                    $error = 'Maximum stack depth exceeded';
                    break;
                case JSON_ERROR_STATE_MISMATCH:
                    $error = 'Underflow or the modes mismatch';
                    break;
                case JSON_ERROR_CTRL_CHAR:
                    $error = 'Unexpected control character found';
                    break;
                case JSON_ERROR_SYNTAX:
                    $error = 'Syntax error, malformed JSON';
                    break;
                case JSON_ERROR_UTF8:
                    $error = 'Malformed UTF-8 characters, possibly incorrectly encoded';
                    break;
                default:
                    $error = 'Unknown error';
                    break;
                endswitch;

                // throw Exception
                throw new \Exception(sprintf('Error decoding JSON file %s, returned error code: %d - %s',
                    substr($file, strlen(FRAMEWORK_PATH)), $code, $error));
            }
        } else {
            throw new \Exception(sprintf('Missing the configuration file: %s!', substr($file, strlen(BOOTSTRAP_PATH))));
        }
        // return the configuration array
        return $config;
    }

    /**
     * Alias for readConfiguration()
     *
     * @see readConfiguration()
     * @param string $file path to JSON file
     * @return Ambigous <multitype:, mixed>
     */
    public function readJSON($file)
    {
        return $this->readConfiguration($file);
    }

    /**
     * Scan the given $locale_path for language files and add them to the global
     * translator resource
     *
     * @param string $locale_path
     * @throws \Exception
     * @deprecated The BASIC extension will automatically load all language files!
     */
    function addLanguageFiles($locale_path)
    {
        $this->app['monolog']->addDebug('The function addLanguageFiles() is deprecrated, all locales will be loaded automatically!',
            array(__METHOD__, __LINE__, 'Locale: '.$locale_path));
        return true;
    }

    /**
     * Copy a file, or recursively copy a folder and its contents
     *
     * @param string $source Source path
     * @param string $dest Destination path
     * @param string $permissions New folder creation permissions
     * @return bool Returns true on success, false on failure
     *
     * @author <http://stackoverflow.com/a/12763962/2243419>
     */
    public static function xcopy($source, $dest, $permissions = 0755)
    {
        // Check for symlinks
        if (is_link($source)) {
            return symlink(readlink($source), $dest);
        }

        // Simple copy for a file
        if (is_file($source)) {
            return copy($source, $dest);
        }

        // Make destination directory
        if (!is_dir($dest)) {
            mkdir($dest, $permissions);
        }

        // Loop through the folder
        $dir = dir($source);
        while (false !== $entry = $dir->read()) {
            // Skip pointers
            if ($entry == '.' || $entry == '..') {
                continue;
            }

            // Deep copy directories
            self::xcopy("$source/$entry", "$dest/$entry");
        }

        // Clean up
        $dir->close();
        return true;
    }

    /**
     * Set the cURL options for the usage of a proxy
     *
     * @param resource $curl_resource
     */
    public function setCURLproxy($curl_resource)
    {
        if (!is_null(self::$proxy) && self::$proxy != "") {
            $this->app['monolog']->addDebug('setting proxy config', array(__METHOD__, __LINE__));
            curl_setopt($curl_resource, CURLOPT_PROXYAUTH, self::$proxy_auth);
            curl_setopt($curl_resource, CURLOPT_PROXY, self::$proxy);
            curl_setopt($curl_resource, CURLOPT_PROXYPORT, self::$proxy_port);
            curl_setopt($curl_resource, CURLOPT_PROXYUSERPWD, self::$proxy_usrpwd);
        }
    }

    /**
     * Parse the given content for kitCommands, execute them and replace the content
     *
     * @param string $content
     * @return string parsed content
     */
    public function parseKITcommand($content)
    {
        $Parser = new Parser();
        $commands = $Parser->getCommandsOnly($this->app, $content);
        foreach ($commands as $command) {
            $parse = array(
                'cms' => array(
                    'locale' => $this->app['translator']->getLocale(),
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
                'command' => $command['command'],
                'parameter' => $command['parameter'],
                'expression' => $command['expression']
            );
            $subRequest = Request::create('/command/'.$command['command'], 'POST', $parse);
            $Response = $this->app->handle($subRequest, HttpKernelInterface::SUB_REQUEST, false);
            $command_response = $Response->getContent();
            if ($this->isJSON($command_response)) {
                // if the kitCommand return a JSON response we take only the 'response' part
                $response = json_decode($command_response, true);
                if (isset($response['response'])) {
                    $command_response = $response['response'];
                }
            }
            $content = str_replace($command['expression'], $command_response, $content);
        }
        return $content;
    }

    /**
     * Execute the given kitCommand and return the content of the result.
     *
     * @param string $command name of the kitCommand
     * @param array $parameter to use by the kitCommand
     */
    public function execKITcommand($command, $parameter=array())
    {
        $params = array(
            'cms' => array(
                'locale' => $this->app['translator']->getLocale(),
                'page_id' => '-1',
                'page_url' => '',
                'user' => array(
                    'id' => -1,
                    'name' => '',
                    'email' => ''
                ),
            ),
            'GET' => array(),
            'POST' => array(),
            'parameter' => $parameter,
        );
        // process each kitCommand
        $subRequest = Request::create('/command/'.strtolower($command), 'POST', $params);
        $Response = $this->app->handle($subRequest, HttpKernelInterface::SUB_REQUEST, false);
        return $Response->getContent();
    }

    /**
     * Like json_encode but format the JSON in a human friendly way
     *
     * @param array $chunk the array to save as JSON
     * @param string $already_json set true if $chunk is already JSON and should be formatted
     * @return string
     */
    public function JSONFormat($chunk, $already_json = false)
    {
        $jsonFormat = new jsonFormat();
        return $jsonFormat->format($chunk, $already_json);
    }

    /**
     * Solution for the Latin1 --> UTF-8 MySQL and PHP problem ...
     *
     * @param unknown $convert
     * @return Ambigous <unknown, string>
     */
    public function utf8_entities($convert)
    {
        require_once __DIR__.'/utf-8/functions-utf8.php';

        return entities_to_umlauts2($convert);
    }

    /**
     * Parse a PHP file for defined constants.
     * If $constant = null return a array with all constants or false if none exists.
     * If $constant is a named return the defined value or false, if the constant does
     * not exists.
     *
     * @param string $php_file
     * @param string $constant
     * @throws \Exception
     * @return boolean|array
     * @link http://stackoverflow.com/a/645914/2243419
     */
    public function parseFileForConstants($php_file, $constant=null)
    {
        function is_constant($token) {
            return $token == T_CONSTANT_ENCAPSED_STRING || $token == T_STRING ||
            $token == T_LNUMBER || $token == T_DNUMBER;
        }

        function strip($value) {
            return preg_replace('!^([\'"])(.*)\1$!', '$2', $value);
        }

        $defines = array();
        $state = 0;
        $key = '';
        $value = '';

        if (false === ($file = file_get_contents($php_file))) {
            throw new \Exception("Can not read the content of the file $php_file!");
        }

        $tokens = token_get_all($file);
        $token = reset($tokens);

        while ($token) {
            if (is_array($token)) {
                if ($token[0] == T_WHITESPACE || $token[0] == T_COMMENT || $token[0] == T_DOC_COMMENT) {
                    // do nothing
                }
                elseif ($token[0] == T_STRING && strtolower($token[1]) == 'define') {
                    $state = 1;
                }
                elseif ($state == 2 && is_constant($token[0])) {
                    $key = $token[1];
                    $state = 3;
                }
                elseif ($state == 4 && is_constant($token[0])) {
                    $value = $token[1];
                    $state = 5;
                }
            } else {
                $symbol = trim($token);
                if ($symbol == '(' && $state == 1) {
                    $state = 2;
                }
                elseif ($symbol == ',' && $state == 3) {
                    $state = 4;
                }
                elseif ($symbol == ')' && $state == 5) {
                    $defines[strip($key)] = strip($value);
                    $state = 0;
                }
            }
            $token = next($tokens);
        }

        if (is_null($constant)) {
            return !empty($defines) ? $defines : false;
        }
        else {
            foreach ($defines as $key => $value) {
                if (strtolower($key) == strtolower($constant)) {
                    return $value;
                }
            }
            return false;
        }
    }

    /**
     * Sanitize a link or filename to a safe and clean one
     *
     * @param string $link
     * @return string
     */
    public function sanitizeLink($link)
    {
        require_once __DIR__.'/utf-8/functions-utf8.php';

        $link = entities_to_7bit($link);
        // Now remove all bad characters
        $bad = array('\'','"','`','!','@','#','$','%','^','&','*','=','+','|','/','\\',';',':',',','?');
        $link = str_replace($bad, '', $link);
        // replace multiple dots in filename to single dot and (multiple) dots at the end of the filename to nothing
        $link = preg_replace(array('/\.+/', '/\.+$/'), array('.', ''), $link);
        // Now replace spaces with page spacer
        $link = trim($link);
        $link = preg_replace('/(\s)+/', '-', $link);
        // Now convert to lower-case
        $link = strtolower($link);
        // If there are any weird language characters, this will protect us against possible problems they could cause
        $link = str_replace(array('%2F', '%'), array('/', ''), urlencode($link));
        $link = str_replace('---', '-', $link);
        // Finally, return the cleaned string
        return $link;
    }

    /**
     * Ellipsis function - shorten the given $text to $length at the nearest
     * space and add three dots at the end ...
     *
     * @param string $text
     * @param number $length
     * @param boolean $striptags remove HTML tags by default
     * @param boolean $htmlpurifier use HTML Purifier (false by default, ignored if striptags=true)
     * @return string
     */
    public function Ellipsis($text, $length=100, $striptags=true, $htmlpurifier=false) {
        if ($striptags) {
            $text = strip_tags($text);
        }

        // remove leading and trailing spaces
        $text = trim($text);

        if (empty($text)) {
            // nothing to do ...
            return '';
        }

        $start_length = strlen($text);
        $text .= ' ';
        $text = substr($text, 0, $length);
        $text = substr($text, 0, strrpos($text, ' '));

        if (!$striptags && $htmlpurifier && class_exists('HTMLPurifier')) {
            $config = \HTMLPurifier_Config::createDefault();
            $purifier = new \HTMLPurifier($config);
            $text = $purifier->purify($text);

            $DOM = new \DOMDocument;

            // enable internal error handling
            libxml_use_internal_errors(true);

            // need a hack to properly handle UTF-8 encoding
            if (!$DOM->loadHTML(mb_convert_encoding($text, 'HTML-ENTITIES', "UTF-8"))) {
                // on error still return the $text
                return $text;
            }
            libxml_clear_errors();

            $xpath = new \DOMXPath($DOM);
            $textNodes = $xpath->query('//text()');
            $lastTextNode = $textNodes->item($textNodes->length - 1);
            $lastTextNode->nodeValue .= ' ...';

            $XPath = new \DOMXPath($DOM);
            // get only the body tag with its contents, then trim the body tag itself to get only the original content
            $text = mb_substr($DOM->saveXML($XPath->query('//body')->item(0)), 6, -7, "UTF-8");
        }
        elseif ($start_length > strlen($text)) {
            $text .= ' ...';
        }
        return $text;
    }

    /**
     * Makes a technical name human readable.
     *
     * Sequences of underscores are replaced by single spaces. The first letter
     * of the resulting string is capitalized, while all other letters are
     * turned to lowercase.
     *
     * @param string $text The text to humanize.
     * @return string The humanized text.
     */
    public function humanize($text)
    {
        return ucfirst(trim(strtolower(preg_replace('/[_\s]+/', ' ', $text))));
    }

    /**
     * Check if the given string is a valid JSON string
     *
     * @param string $string
     * @return boolean
     */
    public function isJSON($string)
    {
        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }

    /**
     * Check if the given function is disabled in php.ini
     *
     * @param string $function
     * @return boolean
     */
    public function isFunctionDisabled($function)
    {
        $disabled = explode(',', ini_get('disable_functions'));
        return in_array($function, $disabled);
    }

    /**
     * Convert a hexadecimal colorcode into an RGB array, return a 'rgb()' string
     *
     * @param string $hex hexadecimal, can contain a leading sharp
     * @param boolean $formatted if true (default) return RGB string
     * @param string $format_rgb format string for the RGB return
     * @return Ambigous <string, array>
     */
    public function hex2rgb($hex, $formatted=true, $format_rgb='rgb(%d, %d %d)')
    {
        // remove the sharp at start
        $hex =  ltrim($hex, '#');

        if (strlen($hex) === 3) {
            $rgb = array(
                hexdec(substr($hex,0,1).substr($hex,0,1)),
                hexdec(substr($hex,1,1).substr($hex,1,1)),
                hexdec(substr($hex,2,1).substr($hex,2,1))
            );
        }
        else {
            $rgb = array(
                hexdec(substr($hex,0,2)),
                hexdec(substr($hex,2,2)),
                hexdec(substr($hex,4,2))
            );
        }
        // return RGB as formatted string or as array
        return $formatted ? sprintf($format_rgb, $rgb[0], $rgb[1], $rgb[2]) : $rgb;
    }

    /**
     * Convert the given rgb value into a hexadecimal color value with the leading '#'
     *
     * @param mixed $rgb can be an array(r,g,b) or a string in form 'r,g,b'
     * @throws \Exception
     * @return string hexadecimal value
     */
    public function rgb2hex($rgb)
    {
        if (is_string($rgb)) {
            if (!strpos($rgb, ',')) {
                throw new \Exception('Variable $rgb can be an array or a string in form r,g,b');
            }
            $rgb = explode(',', $rgb);
        }

        if (count($rgb) != 3) {
            throw new \Exception('Variable $rgb can be an array or a string in form r,g,b');
        }

        $hex = "#";
        $hex .= str_pad(dechex($rgb[0]), 2, "0", STR_PAD_LEFT);
        $hex .= str_pad(dechex($rgb[1]), 2, "0", STR_PAD_LEFT);
        $hex .= str_pad(dechex($rgb[2]), 2, "0", STR_PAD_LEFT);

        return $hex;
    }

    /**
     * Uses the htmlentities function to change special chars like é or Ö to a
     * simple e or O - use i.e. for sorting functions
     *
     * @param string $string
     * @param boolean $lowercase set to TRUE to return a lowercase string
     * @return string
     */
    public function specialCharsToAsciiChars($string, $lowercase=false)
    {
        if (strpos($string = htmlentities($string, ENT_QUOTES, 'UTF-8'), '&') !== false) {
            $string = html_entity_decode(preg_replace('~&([a-z]{1,2})(?:acute|cedil|circ|grave|lig|orn|ring|slash|tilde|uml);~i', '$1', $string), ENT_QUOTES, 'UTF-8');
        }
        return ($lowercase) ? strtolower($string) : $string;
    }

    /**
     * Get the toolbar for all backend dialogs
     *
     * @param  string $active dialog
     * @param  array  $config toolbar configuration
     * @return array
     */
    public function getToolbar($active,$extension,$config) {
        $toolbar = array();
        foreach ($config['nav_tabs']['order'] as $tab) {
            if(isset($config['nav_tabs']['options']) && is_array($config['nav_tabs']['options']) && isset($config['nav_tabs']['options'][$tab])) {
                $toolbar[$tab] = array(
                    'name'   => $tab,
                    'text'   => ( isset($config['nav_tabs']['options'][$tab]['text'])
                             ?  $this->app['translator']->trans($config['nav_tabs']['options'][$tab]['text'])
                             :  $this->app['translator']->trans($tab) ),
                    'hint'   => ( isset($config['nav_tabs']['options'][$tab]['hint'])
                             ?  $this->app['translator']->trans($config['nav_tabs']['options'][$tab]['hint'])
                             :  $this->app['translator']->trans('No hint available') ),
                    'link'   => ( isset($config['nav_tabs']['options'][$tab]['link'])
                             ?  FRAMEWORK_URL.$config['nav_tabs']['options'][$tab]['link']
                             :  FRAMEWORK_URL.'/admin/'.$extension.'/'.$tab ),
                    'class'  => ( isset($config['nav_tabs']['options'][$tab]['class'])
                             ?  $config['nav_tabs']['options'][$tab]['class']
                             :  false ),
                    'active' => ($active == $tab)
                );
            }
            else {
                $toolbar[$tab] = array(
                    'name'   => $tab,
                    'text'   => $this->app['translator']->trans($tab),
                    'hint'   => $this->app['translator']->trans('No hint available'),
                    'link'   => FRAMEWORK_URL.'/admin/'.$extension.'/'.$tab,
                    'active' => ($active == $tab)
                );
            }
        }
        return $toolbar;
    }

}
