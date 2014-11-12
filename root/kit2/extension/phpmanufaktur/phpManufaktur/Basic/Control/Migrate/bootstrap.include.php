<?php

/**
 * kitFramework::Migrate
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de
 * @copyright 2014 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

if (!defined('BOOTSTRAP_PATH')) {
    trigger_error('Missing the BOOTSTRAP_PATH!');
}

require_once realpath(BOOTSTRAP_PATH.'/framework/autoload.php');

use phpManufaktur\Basic\Control\Utils;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Bridge\Monolog\Logger;
use Symfony\Component\Finder\Finder;
use phpManufaktur\Basic\Control\twigExtension;
use Symfony\Component\HttpFoundation\Request;

// set the error handling
ini_set('display_errors', 1);
error_reporting(-1);
Symfony\Component\HttpKernel\Debug\ErrorHandler::register();
if ('cli' !== php_sapi_name()) {
    Symfony\Component\HttpKernel\Debug\ExceptionHandler::register();
}

$migrate = new Silex\Application();

// register the Framework Utils
$migrate['utils'] = $migrate->share(function($migrate) {
    return new Utils($migrate);
});

// get the filesystem into the application
$migrate['filesystem'] = function() {
    return new Filesystem();
};

// FRAMEWORK_URL == BOOTSTRAP_URL !
define('FRAMEWORK_URL', BOOTSTRAP_URL);
// FRAMEWORK_PATH == BOOTSTRAP_PATH !
define('FRAMEWORK_PATH', $migrate['utils']->sanitizePath(BOOTSTRAP_PATH));

// check for the framework configuration file
$framework_config = $migrate['utils']->readConfiguration(realpath(BOOTSTRAP_PATH . '/config/framework.json'));

define('FRAMEWORK_TEMP_PATH', FRAMEWORK_PATH . '/temp');
define('FRAMEWORK_TEMP_URL', FRAMEWORK_URL . '/temp');
define('FRAMEWORK_TEMPLATES', isset($framework_config['FRAMEWORK_TEMPLATES']) ?
    implode(',', $framework_config['FRAMEWORK_TEMPLATES']) : 'default');
$templates = explode(',', FRAMEWORK_TEMPLATES);
define('FRAMEWORK_TEMPLATE_PREFERRED', trim($templates[0]));
define('MANUFAKTUR_PATH', FRAMEWORK_PATH . '/extension/phpmanufaktur/phpManufaktur');
define('MANUFAKTUR_URL', FRAMEWORK_URL . '/extension/phpmanufaktur/phpManufaktur');
define('THIRDPARTY_PATH', FRAMEWORK_PATH . '/extension/thirdparty/thirdParty');
define('THIRDPARTY_URL', FRAMEWORK_URL . '/extension/thirdparty/thirdParty');
define('CONNECT_CMS_USERS', isset($framework_config['CONNECT_CMS_USERS']) ?
    $framework_config['CONNECT_CMS_USERS'] : true);
define('FRAMEWORK_SETUP', isset($framework_config['FRAMEWORK_SETUP']) ?
    $framework_config['FRAMEWORK_SETUP'] : true);
define('FRAMEWORK_MEDIA_PATH', FRAMEWORK_PATH.'/media/public');
define('FRAMEWORK_MEDIA_URL', FRAMEWORK_URL.'/media/public');
define('FRAMEWORK_MEDIA_PROTECTED_PATH', FRAMEWORK_PATH.'/media/protected');
define('FRAMEWORK_MEDIA_PROTECTED_URL', FRAMEWORK_URL.'/media/protected');

// set DEBUG always to TRUE and CACHE always to FALSE
define('FRAMEWORK_DEBUG', true);
define('FRAMEWORK_CACHE', false);

define('LIBRARY_PATH', MANUFAKTUR_PATH.'/Library/Library');
define('LIBRARY_URL', MANUFAKTUR_URL.'/Library/Library');

if ($migrate['filesystem']->exists(MANUFAKTUR_PATH.'/Library/Extension')) {
    define('EXTENSION_PATH', MANUFAKTUR_PATH.'/Library/Extension');
}

define('CATALOG_ACCEPT_EXTENSION', isset($framework_config['CATALOG_ACCEPT_EXTENSION']) ?
    implode(',', $framework_config['CATALOG_ACCEPT_EXTENSION']) :
    implode(',', array('beta','pre-release','release')));

if (!isset($framework_config['CATALOG_ACCEPT_EXTENSION'])) {
    // add the accepted extensions to the framework configuration array
    $framework_config['CATALOG_ACCEPT_EXTENSION'] = explode(',', CATALOG_ACCEPT_EXTENSION);
}
define('FRAMEWORK_UID', isset($framework_config['FRAMEWORK_UID']) ? $framework_config['FRAMEWORK_UID'] : null);

// debug mode
$migrate['debug'] = FRAMEWORK_DEBUG;

$directories = array(
    FRAMEWORK_PATH . '/logfile',
    FRAMEWORK_PATH . '/temp/cache',
    FRAMEWORK_PATH . '/temp/session'
);

// check the needed temporary directories and create them if needed
if (!$migrate['filesystem']->exists($directories)) {
    $migrate['filesystem']->mkdir($directories);
}

// set the default timezone to avoid problems
date_default_timezone_set('Europe/Berlin');

// register monolog
$migrate->register(new Silex\Provider\MonologServiceProvider(), array(
    'monolog.logfile' => FRAMEWORK_PATH.'/logfile/migrate.log',
    'monolog.level' => FRAMEWORK_DEBUG ? Logger::DEBUG : Logger::WARNING,
    'monolog.name' => 'Migrate',
    'monolog.maxfiles' => isset($framework_config['LOGFILE_ROTATE_MAXFILES']) ? $framework_config['LOGFILE_ROTATE_MAXFILES'] : 10
));
$migrate['monolog']->popHandler();
$migrate['monolog']->pushHandler(new Monolog\Handler\RotatingFileHandler(
    $migrate['monolog.logfile'],
    $migrate['monolog.maxfiles'],
    $migrate['monolog.level'],
    false
));

$migrate['monolog']->addDebug('MonologServiceProvider registered.');


// register the session handler
$migrate->register(new Silex\Provider\SessionServiceProvider(), array(
    'session.storage.save_path' => FRAMEWORK_PATH . '/temp/session',
    'session.storage.options' => array(
        'cookie_lifetime' => 0
    )
));
$migrate['monolog']->addDebug('SessionServiceProvider registered.');

$migrate->register(new Silex\Provider\UrlGeneratorServiceProvider());
$migrate['monolog']->addDebug('UrlGeneratorServiceProvider registered.');

// register the Translator
$migrate->register(new Silex\Provider\TranslationServiceProvider());
$migrate['monolog']->addDebug('Translator Service registered. Added ArrayLoader to the Translator');

// load the language files for all extensions
$extensions = new Finder();
$extensions->directories()->in(array(MANUFAKTUR_PATH, THIRDPARTY_PATH));
$extensions->depth('== 0');

$search = array(MANUFAKTUR_PATH.'/Basic/Data/Locale/Metric');

// first add the existing regular locale directories
foreach ($extensions as $extension) {
    if ($migrate['filesystem']->exists($extension->getRealpath().'/Data/Locale')) {
        $search[] = $extension->getRealpath().'/Data/Locale';
    }
}
// at least add the existing custom locale directories
foreach ($extensions as $extension) {
    if ($migrate['filesystem']->exists($extension->getRealpath().'/Data/Locale/Custom')) {
        $search[] = $extension->getRealpath().'/Data/Locale/Custom';
    }
}

$locales = new Finder();
$locales->name('*.php')->in($search);

$locales->depth('== 0');
foreach ($locales as $locale) {
    // add the locale resource file
    $migrate['translator'] = $migrate->share($migrate->extend('translator', function ($translator) use ($locale) {
        $lang_array = include_once $locale->getRealpath();
        $translator->addResource('array', $lang_array, $locale->getBasename('.php'));
        return $translator;
    }));
    $migrate['monolog']->addDebug('Added language file: '.$locale->getRealpath());
}

// register Twig
$migrate->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.options' => array(
        'cache' => FRAMEWORK_CACHE ? FRAMEWORK_PATH . '/temp/cache/' : false,
        'strict_variables' => $migrate['debug'] ? true : false,
        'debug' => FRAMEWORK_DEBUG ? true : false,
        'autoescape' => false
    )
));

// set namespaces for phpManufaktur and thirdParty
$migrate['twig.loader.filesystem']->addPath(MANUFAKTUR_PATH, 'phpManufaktur');
$migrate['twig.loader.filesystem']->addPath(MANUFAKTUR_PATH, 'phpmanufaktur');
$migrate['twig.loader.filesystem']->addPath(THIRDPARTY_PATH, 'thirdParty');
$migrate['twig.loader.filesystem']->addPath(THIRDPARTY_PATH, 'thirdparty');
// IMPORTANT: define these namespaces also in phpManufaktur\Basic\Control\Utils\templateFile()

$migrate['twig'] = $migrate->share($migrate->extend('twig', function ($twig, $migrate)
{
    // add global variables, functions etc. for the templates
    $twig->addExtension(new twigExtension($migrate));
    if ($migrate['debug']) {
        $twig->addExtension(new Twig_Extension_Debug());
    }
    $twig->addExtension(new Twig_Extension_StringLoader());
    return $twig;
}));

$migrate['monolog']->addDebug('TwigServiceProvider registered.');

// register Validator Service
$migrate->register(new Silex\Provider\ValidatorServiceProvider());
$migrate['monolog']->addDebug('Validator Service Provider registered.');

// register the FormServiceProvider
$migrate->register(new Silex\Provider\FormServiceProvider());
$migrate['monolog']->addDebug('Form Service registered.');

// register the HTTP Cache Service
$migrate->register(new Silex\Provider\HttpCacheServiceProvider(), array(
    'http_cache.cache_dir' => FRAMEWORK_PATH . '/temp/cache/'
));
$migrate['monolog']->addDebug('HTTP Cache Service registered.');


$migrate->get('/',
    'phpManufaktur\Basic\Control\Migrate\Migrate::ControllerStart');
$migrate->get('/start/',
    'phpManufaktur\Basic\Control\Migrate\Migrate::ControllerStart');
$migrate->get('/remove',
    'phpManufaktur\Basic\Control\Migrate\Migrate::ControllerSessionRemove');

$migrate->get('/authenticate/',
    'phpManufaktur\Basic\Control\Migrate\Authenticate::ControllerAuthenticate');
$migrate->post('/authenticate/check/',
    'phpManufaktur\Basic\Control\Migrate\Authenticate::ControllerAuthenticateCheck');

$migrate->post('/url/check/',
    'phpManufaktur\Basic\Control\Migrate\Migrate::ControllerUrlCheck');

$migrate->post('/mysql/',
    'phpManufaktur\Basic\Control\Migrate\Migrate::ControllerMySql');
$migrate->post('/mysql/check/',
    'phpManufaktur\Basic\Control\Migrate\Migrate::ControllerMySqlCheck');

$migrate->post('/email/',
    'phpManufaktur\Basic\Control\Migrate\Migrate::ControllerEMail');
$migrate->post('/email/check/',
    'phpManufaktur\Basic\Control\Migrate\Migrate::ControllerEMailCheck');


$migrate->before(function(Request $request) use ($migrate)
{
    // quick and dirty ... try to detect the favorised language - to be improved!
    if (!is_null($request->server->get('HTTP_ACCEPT_LANGUAGE'))) {
        // default language
        $locale = 'en';

        $langs = array();
        // break up string into pieces (languages and q factors)
        preg_match_all('/([a-z]{1,8}(-[a-z]{1,8})?)\s*(;\s*q\s*=\s*(1|0\.[0-9]+))?/i', $request->server->get('HTTP_ACCEPT_LANGUAGE'), $lang_parse);
        if (count($lang_parse[1]) > 0) {
            foreach ($lang_parse[1] as $lang) {
                if (false === (strpos($lang, '-'))) {
                    // only the country sign like 'de'
                    $locale = strtolower($lang);
                } else {
                    // perhaps something like 'de-DE'
                    $locale = strtolower(substr($lang, 0, strpos($lang, '-')));
                }
                break;
            }
        }
        // set the locale
        $migrate['translator']->setLocale($locale);
        $migrate['session']->set('LOCALE', $locale);
        $migrate['monolog']->addDebug('Set locale to '.$locale);
    }
});


if (FRAMEWORK_DEBUG || !FRAMEWORK_CACHE) {
    // don't use cache
    $migrate->run();
}
else {
    // run in cache mode
    $migrate['http_cache']->run();
}
