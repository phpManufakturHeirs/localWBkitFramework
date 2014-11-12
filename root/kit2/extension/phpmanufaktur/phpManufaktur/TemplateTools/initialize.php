<?php

/**
 * TemplateTools
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de/TemplateTools
 * @copyright 2014 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

if (!defined('WB_PATH')) {
    trigger_error('The TemplateTools expect to be executed within a WebsiteBaker, LEPTON CMS or BlackCat CMS template!', E_USER_ERROR);
}

require_once realpath(WB_PATH.'/kit2/framework/autoload.php');

// set the error handling
ini_set('display_errors', 1);
error_reporting(-1);
Symfony\Component\HttpKernel\Debug\ErrorHandler::register();
if ('cli' !== php_sapi_name()) {
    Symfony\Component\HttpKernel\Debug\ExceptionHandler::register();
}

$template = new Silex\Application();

if (!defined('BOOTSTRAP_PATH')) define('BOOTSTRAP_PATH', WB_PATH.'/kit2');

// register the validator service
$template->register(new Silex\Provider\ValidatorServiceProvider());

// register the Framework Tools
$template['tools'] = $template->share(function($template) {
    return new phpManufaktur\TemplateTools\Control\Tools($template);
});

// INTERNAL - we need also the utils to access orginal kitFramework Classes!
$template['utils'] = $template->share(function($template) {
    return new phpManufaktur\Basic\Control\Utils($template);
});

// define needed constants
if (!defined('CMS_PATH')) define('CMS_PATH', $template['tools']->sanitizePath(WB_PATH));
if (!defined('CMS_URL')) define('CMS_URL', WB_URL);
if (!defined('CMS_MEDIA_PATH')) define('CMS_MEDIA_PATH', CMS_PATH.MEDIA_DIRECTORY);
if (!defined('CMS_MEDIA_URL')) define('CMS_MEDIA_URL', CMS_URL.MEDIA_DIRECTORY);
if (!defined('CMS_LOCALE')) define('CMS_LOCALE', strtolower(DEFAULT_LANGUAGE));
if (!defined('CMS_TEMPLATES_PATH')) define('CMS_TEMPLATES_PATH', CMS_PATH.'/templates');
if (!defined('CMS_TEMPLATES_URL')) define('CMS_TEMPLATES_URL', CMS_URL.'/templates');
if (!defined('CMS_ADDONS_PATH')) define('CMS_ADDONS_PATH', CMS_PATH.'/modules');
if (!defined('CMS_ADDONS_URL')) define('CMS_ADDONS_URL', CMS_URL.'/modules');
if (!defined('CMS_USER_ID')) define('CMS_USER_ID', isset($_SESSION['USER_ID']) ? $_SESSION['USER_ID'] : -1);
if (!defined('CMS_USER_USERNAME')) define('CMS_USER_USERNAME', isset($_SESSION['USERNAME']) ? $_SESSION['USERNAME'] : 'anonymous');
if (!defined('CMS_USER_DISPLAYNAME')) define('CMS_USER_DISPLAYNAME', isset($_SESSION['DISPLAY_NAME']) ? $_SESSION['DISPLAY_NAME'] : 'anonymous');
if (!defined('CMS_USER_EMAIL')) define('CMS_USER_EMAIL', isset($_SESSION['EMAIL']) ? $_SESSION['EMAIL'] : '');
if (!defined('CMS_USER_IS_AUTHENTICATED')) define('CMS_USER_IS_AUTHENTICATED', ((CMS_USER_ID > 0) && (CMS_USER_EMAIL != '')));
if (!defined('CMS_USER_GROUP_NAMES')) define('CMS_USER_GROUP_NAMES', (isset($_SESSION['GROUP_NAME'])) ? implode(',', $_SESSION['GROUP_NAME']) : '');
if (!defined('CMS_USER_GROUP_IDS')) define('CMS_USER_GROUP_IDS', (isset($_SESSION['GROUPS_ID'])) ? $_SESSION['GROUPS_ID'] : '');
if (!defined('CMS_USER_IS_ADMIN')) define('CMS_USER_IS_ADMIN', (isset($_SESSION['GROUP_ID']) && ($_SESSION['GROUP_ID'] == 1)));
if (!defined('CMS_USER_ACCOUNT_URL')) define('CMS_USER_ACCOUNT_URL', defined('PREFERENCES_URL') ? PREFERENCES_URL : CMS_URL);
if (!defined('CMS_LOGIN_ENABLED')) define('CMS_LOGIN_ENABLED', FRONTEND_LOGIN);
if (!defined('CMS_LOGIN_URL')) define('CMS_LOGIN_URL', defined('LOGIN_URL') ? LOGIN_URL : CMS_URL);
if (!defined('CMS_LOGIN_FORGOTTEN_URL')) define('CMS_LOGIN_FORGOTTEN_URL', defined('FORGOT_URL') ? FORGOT_URL : CMS_URL);
if (!defined('CMS_PAGES_DIRECTORY')) define('CMS_PAGES_DIRECTORY', PAGES_DIRECTORY);
if (!defined('CMS_PAGES_EXTENSION')) define('CMS_PAGES_EXTENSION', PAGE_EXTENSION);
if (!defined('CMS_TITLE')) define('CMS_TITLE', WEBSITE_TITLE);
if (!defined('CMS_DESCRIPTION')) define('CMS_DESCRIPTION', WEBSITE_DESCRIPTION);
if (!defined('CMS_KEYWORDS')) define('CMS_KEYWORDS', WEBSITE_KEYWORDS);

// get the redirect URL for the login
$redirect_url = ((isset($_SESSION['HTTP_REFERER']) && $_SESSION['HTTP_REFERER'] != '') ? $_SESSION['HTTP_REFERER'] : CMS_URL);
$redirect_url = (isset($_REQUEST['redirect']) && !empty($_REQUEST['redirect'])) ? $_REQUEST['redirect'] : $redirect_url;
if (!defined('CMS_LOGIN_REDIRECT_URL')) define('CMS_LOGIN_REDIRECT_URL', $redirect_url);
if (!defined('CMS_LOGIN_SIGNUP_ENABLED')) define('CMS_LOGIN_SIGNUP_ENABLED', FRONTEND_SIGNUP);
if (!defined('CMS_LOGIN_SIGNUP_URL')) define('CMS_LOGIN_SIGNUP_URL', defined('SIGNUP_URL') ? SIGNUP_URL : CMS_URL);
if (!defined('CMS_LOGOUT_URL')) define('CMS_LOGOUT_URL', defined('LOGOUT_URL') ? LOGOUT_URL : CMS_URL);
if (!defined('CMS_SEARCH_VISIBILITY')) define('CMS_SEARCH_VISIBILITY', SEARCH);

// check for the framework configuration file
$framework_config = $template['tools']->readJSON(realpath(BOOTSTRAP_PATH . '/config/framework.json'));

if (!defined('FRAMEWORK_DEBUG')) define('FRAMEWORK_DEBUG', (isset($framework_config['DEBUG'])) ? $framework_config['DEBUG'] : false);
$template['debug'] = FRAMEWORK_DEBUG;

if (!defined('FRAMEWORK_CACHE')) define('FRAMEWORK_CACHE', (isset($framework_config['CACHE'])) ? $framework_config['CACHE'] : true);
if (!defined('FRAMEWORK_UID')) define('FRAMEWORK_UID', isset($framework_config['FRAMEWORK_UID']) ? $framework_config['FRAMEWORK_UID'] : $template['utils']->createGUID());
if (!defined('FRAMEWORK_UID_AUTHENTICATED')) {
    // check if a kitFramework extension is accessing the website
    if ((isset($_GET['fuid']) && ($_GET['fuid'] == FRAMEWORK_UID)) ||
        (isset($_POST['fuid']) && ($_POST['fuid'] == FRAMEWORK_UID))) {
        define('FRAMEWORK_UID_AUTHENTICATED', true);
    }
    else {
        define('FRAMEWORK_UID_AUTHENTICATED', false);
    }
}
if (!defined('FRAMEWORK_PATH')) define('FRAMEWORK_PATH', CMS_PATH.'/kit2');
if (!defined('FRAMEWORK_URL')) define('FRAMEWORK_URL', CMS_URL.'/kit2');
if (!defined('FRAMEWORK_MEDIA_PATH')) define('FRAMEWORK_MEDIA_PATH', FRAMEWORK_PATH.'/media/public');
if (!defined('FRAMEWORK_MEDIA_URL')) define('FRAMEWORK_MEDIA_URL', FRAMEWORK_URL.'/media/public');

if (!defined('MANUFAKTUR_PATH')) define('MANUFAKTUR_PATH', FRAMEWORK_PATH . '/extension/phpmanufaktur/phpManufaktur');
if (!defined('MANUFAKTUR_URL')) define('MANUFAKTUR_URL', FRAMEWORK_URL . '/extension/phpmanufaktur/phpManufaktur');

if (!defined('THIRDPARTY_PATH')) define('THIRDPARTY_PATH', FRAMEWORK_PATH . '/extension/thirdparty/thirdParty');
if (!defined('THIRDPARTY_URL')) define('THIRDPARTY_URL', FRAMEWORK_URL . '/extension/thirdparty/thirdParty');

if (!defined('LIBRARY_PATH')) define('LIBRARY_PATH', MANUFAKTUR_PATH.'/Library/Library');
if (!defined('LIBRARY_URL')) define('LIBRARY_URL', MANUFAKTUR_URL.'/Library/Library');

if (!defined('EXTENSION_PATH')) define('EXTENSION_PATH', MANUFAKTUR_PATH.'/Library/Extension');
if (!defined('EXTENSION_URL')) define('EXTENSION_URL', MANUFAKTUR_URL.'/Library/Extension');

if (!defined('HELPER_PATH')) define('HELPER_PATH', MANUFAKTUR_PATH.'/Library/Helper');
if (!defined('HELPER_URL')) define('HELPER_URL', MANUFAKTUR_URL.'/Library/Helper');

// get the filesystem into the application
$template['filesystem'] = function() {
    return new Symfony\Component\Filesystem\Filesystem();
};

// register monolog
$template->register(new Silex\Provider\MonologServiceProvider(), array(
    'monolog.logfile' => FRAMEWORK_PATH.'/logfile/templatetools.log',
    'monolog.level' => FRAMEWORK_DEBUG ? Symfony\Bridge\Monolog\Logger::DEBUG : Symfony\Bridge\Monolog\Logger::WARNING,
    'monolog.name' => 'TemplateTools',
    'monolog.maxfiles' => isset($framework_config['LOGFILE_ROTATE_MAXFILES']) ? $framework_config['LOGFILE_ROTATE_MAXFILES'] : 10
));
$template['monolog']->popHandler();
$template['monolog']->pushHandler(new Monolog\Handler\RotatingFileHandler(
    $template['monolog.logfile'],
    $template['monolog.maxfiles'],
    $template['monolog.level'],
    false
));
$template['monolog']->addDebug('Monolog initialized.');

// read the CMS configuration
$cms_config = $template['tools']->readJSON(FRAMEWORK_PATH . '/config/cms.json');

if (!defined('CMS_TYPE')) define('CMS_TYPE', $cms_config['CMS_TYPE']);
if (!defined('CMS_VERSION')) define('CMS_VERSION', $cms_config['CMS_VERSION']);

$template['cms'] = $template->share(function($template) {
    return new phpManufaktur\TemplateTools\Control\cmsFunctions($template);
});

// read the doctrine configuration
$doctrine_config = $template['tools']->readJSON(FRAMEWORK_PATH.'/config/doctrine.cms.json');

if (!defined('CMS_TABLE_PREFIX')) define('CMS_TABLE_PREFIX', $doctrine_config['TABLE_PREFIX']);
if (!defined('FRAMEWORK_TABLE_PREFIX')) define('FRAMEWORK_TABLE_PREFIX', $doctrine_config['TABLE_PREFIX'] . 'kit2_');

$template->register(new Silex\Provider\DoctrineServiceProvider(), array(
    'db.options' => array(
        'driver' => 'pdo_mysql',
        'dbname' => $doctrine_config['DB_NAME'],
        'user' => $doctrine_config['DB_USERNAME'],
        'password' => $doctrine_config['DB_PASSWORD'],
        'host' => $doctrine_config['DB_HOST'],
        'port' => $doctrine_config['DB_PORT']
    )
));

// create the page sequence
$template['cms']->page_sequence();

if (!defined('PAGE_LOCALE')) define('PAGE_LOCALE', strtolower(LANGUAGE));
if (!defined('PAGE_FOOTER')) define('PAGE_FOOTER', $template['cms']->page_footer('Y', false));
if (!defined('PAGE_HEADER')) define('PAGE_HEADER', $template['cms']->page_header(false));
if (!defined('PAGE_KEYWORDS')) define('PAGE_KEYWORDS', $template['cms']->page_keywords(false));
if (!defined('PAGE_MENU_LEVEL')) {
    if (PAGE_ID > 0) {
        // get the MENU LEVEL of the current page
        $SQL = "SELECT `level` FROM `".CMS_TABLE_PREFIX."pages` WHERE `page_id`=".PAGE_ID;
        $page_level = $template['db']->fetchColumn($SQL);
        define('PAGE_MENU_LEVEL', $page_level);
    }
    else {
        define('PAGE_MENU_LEVEL', 0);
    }
}
if (!defined('PAGE_MENU_TITLE')) define('PAGE_MENU_TITLE', MENU_TITLE);
if (!defined('PAGE_PARENT_ID')) {
    if (PAGE_ID > 0) {
        // get the parent PAGE_ID
        $SQL = "SELECT `parent` FROM `".CMS_TABLE_PREFIX."pages` WHERE `page_id`=".PAGE_ID;
        $parent_id = $template['db']->fetchColumn($SQL);
        define('PAGE_PARENT_ID', $parent_id);
    }
    else {
        define('PAGE_PARENT_ID', 0);
    }
}
if (!defined('PAGE_HAS_CHILD')) define('PAGE_HAS_CHILD', $template['cms']->page_has_child());
if (!defined('PAGE_TITLE')) define('PAGE_TITLE', $template['cms']->page_title(' - ', '[PAGE_TITLE]', false));
if (!defined('PAGE_URL')) define('PAGE_URL', $template['cms']->page_url(PAGE_ID, false, false));
if (!defined('PAGE_LINK')) define('PAGE_LINK', $template['cms']->page_link(PAGE_ID));
if (!defined('PAGE_VISIBILITY')) define('PAGE_VISIBILITY', VISIBILITY);
if (!defined('PAGE_ID_HOME')) define('PAGE_ID_HOME', $template['cms']->page_id_home());

// normally set by CMS but not at SEARCH pages!
if (!defined('PAGE_DESCRIPTION')) define('PAGE_DESCRIPTION', '');

if (!defined('TEMPLATE_DEFAULT_NAME')) define('TEMPLATE_DEFAULT_NAME', DEFAULT_TEMPLATE);
if (!defined('TEMPLATE_PATH')) define('TEMPLATE_PATH', CMS_PATH.substr(TEMPLATE_DIR, strlen(CMS_URL)));
if (!defined('TEMPLATE_URL')) define('TEMPLATE_URL', TEMPLATE_DIR);
if (!defined('TEMPLATE_DIRECTORY')) define('TEMPLATE_DIRECTORY', substr(TEMPLATE_DIR, strlen(CMS_TEMPLATES_URL)+1));
if (!defined('TEMPLATE_NAME')) define('TEMPLATE_NAME', TEMPLATE);

// register the Translator
$template->register(new Silex\Provider\TranslationServiceProvider(), array(
    'locale_fallback' => 'en'
));
$template['translator'] = $template->share($template->extend('translator', function($translator, $template)
{
    $translator->addLoader('array', new Symfony\Component\Translation\Loader\ArrayLoader());
    return $translator;
}));

// set the locale from the current page
$template['translator']->setLocale(PAGE_LOCALE);

// load the metric language file from BASIC
$template['tools']->addLanguageFiles(MANUFAKTUR_PATH.'/Basic/Data/Locale/Metric');

// load the language files for the TemplateTools
$template['tools']->addLanguageFiles(MANUFAKTUR_PATH.'/TemplateTools/Data/Locale');
$template['tools']->addLanguageFiles(MANUFAKTUR_PATH.'/TemplateTools/Data/Locale/Custom');

if ($template['filesystem']->exists(TEMPLATE_PATH.'/locale')) {
    // if the template has a /locale directory load these language files also
    $template['tools']->addLanguageFiles(TEMPLATE_PATH.'/locale');
}

// get PAGE_MODIFIED_WHEN and PAGE_MODIFIED_BY
if (PAGE_ID > 0) {
    $SQL = "SELECT `modified_when`, `display_name` FROM `".CMS_TABLE_PREFIX."pages`, `".CMS_TABLE_PREFIX."users` ".
        "WHERE `user_id`=`modified_by` AND `page_id`=".PAGE_ID;
    $result = $template['db']->fetchAssoc($SQL);
    if (!isset($result['modified_when'])) {
        throw new \Exception("Can't read the page information for ID ".PAGE_ID." from the database!");
    }
    if (!defined('PAGE_MODIFIED_WHEN')) define('PAGE_MODIFIED_WHEN', date('Y-m-d H:i:s', $result['modified_when']));
    if (!defined('PAGE_MODIFIED_BY')) define('PAGE_MODIFIED_BY', $template['tools']->unsanitizeText($result['display_name']));
}
else {
    // no valid PAGE_ID, i.e. at search pages
    if (!defined('PAGE_MODIFIED_WHEN')) define('PAGE_MODIFIED_WHEN', date('Y-m-d H:i:s'));
    if (!defined('PAGE_MODIFIED_BY')) define('PAGE_MODIFIED_BY', $template['translator']->trans('- unknown -'));
}

global $post_id;
if (!defined('EXTRA_POST_ID')) define('EXTRA_POST_ID', isset($post_id) ? $post_id : -1);
if (!defined('EXTRA_TOPIC_ID')) define('EXTRA_TOPIC_ID', defined('TOPIC_ID') ? TOPIC_ID : -1);
if (!defined('EXTRA_FLEXCONTENT_ID')) {
    if (isset($_GET['command']) && ($_GET['command'] == 'flexcontent') &&
        isset($_GET['action']) && ($_GET['action'] == 'view') &&
        isset($_GET['content_id']) && is_numeric($_GET['content_id'])) {
        define('EXTRA_FLEXCONTENT_ID', $_GET['content_id']);
    }
    else {
        define('EXTRA_FLEXCONTENT_ID', -1);
    }
}

// add missing constants which need a full configured access to $template['cms']
if (!defined('CMS_MODIFIED_BY')) define('CMS_MODIFIED_BY', $template['cms']->cms_modified_by(null, false));
if (!defined('CMS_MODIFIED_WHEN')) define('CMS_MODIFIED_WHEN', $template['cms']->cms_modified_when('Y-m-d H:i:s', null, false));

if (!defined('CMS_MAINTENANCE_MODE')) define('CMS_MAINTENANCE_MODE', defined('MAINTENANCE_MODE') ? MAINTENANCE_MODE : $template['cms']->internal_maintenance());

// Markdown Parser
$template['markdown'] = $template->share(function($template) {
    return new phpManufaktur\Basic\Control\MarkdownFunctions($template);
});


// register Twig
$template->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => array(
        TEMPLATE_PATH
        ),
    'twig.options' => array(
        'cache' => FRAMEWORK_CACHE ? FRAMEWORK_PATH . '/temp/cache/' : false,
        'strict_variables' => FRAMEWORK_DEBUG,
        'debug' => FRAMEWORK_DEBUG,
        'autoescape' => false
    )
));

// add namespaces for easy template access
$template['twig.loader.filesystem']->addPath(MANUFAKTUR_PATH, 'phpManufaktur');
$template['twig.loader.filesystem']->addPath(MANUFAKTUR_PATH, 'phpmanufaktur');
$template['twig.loader.filesystem']->addPath(THIRDPARTY_PATH, 'thirdParty');
$template['twig.loader.filesystem']->addPath(THIRDPARTY_PATH, 'thirdparty');
$template['twig.loader.filesystem']->addPath(CMS_TEMPLATES_PATH, 'templates');
$template['twig.loader.filesystem']->addPath(CMS_TEMPLATES_PATH, 'Templates');
$template['twig.loader.filesystem']->addPath(MANUFAKTUR_PATH.'/TemplateTools/Pattern', 'pattern');
$template['twig.loader.filesystem']->addPath(MANUFAKTUR_PATH.'/TemplateTools/Pattern', 'Pattern');

$template['twig'] = $template->share($template->extend('twig', function($twig, $template)
{
    // add global variables, functions etc. for the templates
    $twig->addExtension(new phpManufaktur\TemplateTools\Control\TwigExtension($template));
    if ($template['debug']) {
        $twig->addExtension(new Twig_Extension_Debug());
    }
    $twig->addExtension(new Twig_Extension_StringLoader());
    return $twig;
}));

$template['monolog']->addDebug('TwigServiceProvider registered.');


// execute droplets
$template['droplet'] = $template->share(function($template) {
    return new phpManufaktur\TemplateTools\Control\DropletFunctions($template);
});

// execute kitCommands
$template['command'] = $template->share(function($template) {
    return new phpManufaktur\TemplateTools\Control\kitCommandFunctions($template);
});

// image tools
$template['image'] = $template->share(function($template) {
    return new phpManufaktur\Basic\Control\Image($template);
});

// Bootstrap tools
$template['bootstrap'] = $template->share(function($template) {
    return new phpManufaktur\TemplateTools\Control\Bootstrap($template);
});

// Classic tools
$template['classic'] = $template->share(function($template) {
    return new phpManufaktur\TemplateTools\Control\Classic($template);
});

// Browser Detection
$template['browser'] = $template->share(function($template) {
   return new phpManufaktur\TemplateTools\Control\BrowserDetect($template);
});

if (!defined('BROWSER_AMAYA')) define('BROWSER_AMAYA', 'Amaya');
if (!defined('BROWSER_ANDROID')) define('BROWSER_ANDROID', 'Android');
if (!defined('BROWSER_BINGBOT')) define('BROWSER_BINGBOT', 'Bing Bot');
if (!defined('BROWSER_BLACKBERRY')) define('BROWSER_BLACKBERRY', 'BlackBerry');
if (!defined('BROWSER_CHROME')) define('BROWSER_CHROME', 'Chrome');
if (!defined('BROWSER_FIREBIRD')) define('BROWSER_FIREBIRD', 'Firebird');
if (!defined('BROWSER_FIREFOX')) define('BROWSER_FIREFOX', 'Firefox');
if (!defined('BROWSER_GALEON')) define('BROWSER_GALEON', 'Galeon');
if (!defined('BROWSER_GOOGLEBOT')) define('BROWSER_GOOGLEBOT', 'GoogleBot');
if (!defined('BROWSER_ICAB')) define('BROWSER_ICAB', 'iCab');
if (!defined('BROWSER_ICECAT')) define('BROWSER_ICECAT', 'IceCat');
if (!defined('BROWSER_ICEWEASEL')) define('BROWSER_ICEWEASEL', 'Iceweasel');
if (!defined('BROWSER_IE')) define('BROWSER_IE', 'Internet Explorer');
if (!defined('BROWSER_IPAD')) define('BROWSER_IPAD', 'iPad');
if (!defined('BROWSER_IPHONE')) define('BROWSER_IPHONE', 'iPhone');
if (!defined('BROWSER_IPOD')) define('BROWSER_IPOD', 'iPod');
if (!defined('BROWSER_KONQUEROR')) define('BROWSER_KONQUEROR', 'Konqueror');
if (!defined('BROWSER_LYNX')) define('BROWSER_LYNX', 'Lynx');
if (!defined('BROWSER_MOZILLA')) define('BROWSER_MOZILLA', 'Mozilla');
if (!defined('BROWSER_MSN')) define('BROWSER_MSN', 'MSN Browser');
if (!defined('BROWSER_MSNBOT')) define('BROWSER_MSNBOT', 'MSN Bot');
if (!defined('BROWSER_NETPOSITIVE')) define('BROWSER_NETPOSITIVE', 'NetPositive');
if (!defined('BROWSER_NETSCAPE_NAVIGATOR')) define('BROWSER_NETSCAPE_NAVIGATOR', 'Netscape Navigator');
if (!defined('BROWSER_NOKIA')) define('BROWSER_NOKIA', 'Nokia Browser');
if (!defined('BROWSER_NOKIA_S60')) define('BROWSER_NOKIA_S60', 'Nokia S60 OSS Browser');
if (!defined('BROWSER_OMNIWEB')) define('BROWSER_OMNIWEB', 'OmniWeb');
if (!defined('BROWSER_OPERA')) define('BROWSER_OPERA', 'Opera');
if (!defined('BROWSER_OPERA_MINI')) define('BROWSER_OPERA_MINI', 'Opera Mini');
if (!defined('BROWSER_PHOENIX')) define('BROWSER_PHOENIX', 'Phoenix');
if (!defined('BROWSER_POCKET_IE')) define('BROWSER_POCKET_IE', 'Pocket Internet Explorer');
if (!defined('BROWSER_SAFARI')) define('BROWSER_SAFARI', 'Safari');
if (!defined('BROWSER_SHIRETOKO')) define('BROWSER_SHIRETOKO', 'Shiretoko');
if (!defined('BROWSER_SLURP')) define('BROWSER_SLURP', 'Yahoo! Slurp');
if (!defined('BROWSER_UNKNOWN')) define('BROWSER_UNKNOWN', 'unknown');
if (!defined('BROWSER_W3CVALIDATOR')) define('BROWSER_W3CVALIDATOR', 'W3C Validator');
if (!defined('BROWSER_WEBTV')) define('BROWSER_WEBTV', 'WebTV');

if (!defined('PLATFORM_ANDROID')) define('PLATFORM_ANDROID', 'Android');
if (!defined('PLATFORM_APPLE')) define('PLATFORM_APPLE', 'Apple');
if (!defined('PLATFORM_BEOS')) define('PLATFORM_BEOS', 'BeOS');
if (!defined('PLATFORM_BLACKBERRY')) define('PLATFORM_BLACKBERRY', 'BlackBerry');
if (!defined('PLATFORM_FREEBSD')) define('PLATFORM_FREEBSD', 'FreeBSD');
if (!defined('PLATFORM_IPAD')) define('PLATFORM_IPAD', 'iPad');
if (!defined('PLATFORM_IPHONE')) define('PLATFORM_IPHONE', 'iPhone');
if (!defined('PLATFORM_IPOD')) define('PLATFORM_IPOD', 'iPod');
if (!defined('PLATFORM_LINUX')) define('PLATFORM_LINUX', 'Linux');
if (!defined('PLATFORM_NETBSD')) define('PLATFORM_NETBSD', 'NetBSD');
if (!defined('PLATFORM_NOKIA')) define('PLATFORM_NOKIA', 'Nokia');
if (!defined('PLATFORM_OS2')) define('PLATFORM_OS2', 'OS/2');
if (!defined('PLATFORM_OPENBSD')) define('PLATFORM_OPENBSD', 'OpenBSD');
if (!defined('PLATFORM_OPENSOLARIS')) define('PLATFORM_OPENSOLARIS', 'OpenSolaris');
if (!defined('PLATFORM_SUNOS')) define('PLATFORM_SUNOS', 'SunOS');
if (!defined('PLATFORM_UNKNOWN')) define('PLATFORM_UNKNOWN', 'unknown');
if (!defined('PLATFORM_WINDOWS')) define('PLATFORM_WINDOWS', 'Windows');
if (!defined('PLATFORM_WINDOWS_CE')) define('PLATFORM_WINDOWS_CE', 'Windows CE');

if (FRAMEWORK_CACHE) {
    // register the HTTP Cache Service
    $template->register(new Silex\Provider\HttpCacheServiceProvider(), array(
        'http_cache.cache_dir' => FRAMEWORK_PATH . '/temp/cache/'
    ));
}
