<?php

/**
 * kitFramework::kfBasic
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de
 * @copyright 2013 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

require_once realpath(BOOTSTRAP_PATH.'/framework/autoload.php');

use Symfony\Component\HttpKernel\Debug\ErrorHandler;
use Symfony\Component\HttpKernel\Debug\ExceptionHandler;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Translation\Loader\ArrayLoader;
use phpManufaktur\Basic\Control\UserProvider;
use phpManufaktur\Basic\Control\manufakturPasswordEncoder;
use phpManufaktur\Basic\Control\twigExtension;
use phpManufaktur\Basic\Control\Utils;
use phpManufaktur\Basic\Data\Setup\Setup;
use Nicl\Silex\MarkdownServiceProvider;
use Symfony\Component\HttpFoundation\Response;
use Monolog\Handler\SwiftMailerHandler;
use phpManufaktur\Basic\Control\ReCaptcha\ReCaptcha;
use phpManufaktur\Basic\Control\Account\Account;
use Symfony\Bridge\Monolog\Logger;
use phpManufaktur\Basic\Control\Account\CustomLogoutSuccessHandler;
use phpManufaktur\Basic\Control\Account\CustomAuthenticationSuccessHandler;
use phpManufaktur\Basic\Data\dbUtils;

// set the error handling
ini_set('display_errors', 1);
error_reporting(-1);
ErrorHandler::register();
if ('cli' !== php_sapi_name()) {
    ExceptionHandler::register();
}

// init application
$app = new Silex\Application();

// register the Framework Utils
$app['utils'] = $app->share(function($app) {
    return new Utils($app);
});

try {
    // check for the framework configuration file
    $framework_config = $app['utils']->readConfiguration(realpath(BOOTSTRAP_PATH . '/config/framework.json'));
    // framework constants
    define('FRAMEWORK_URL', $framework_config['FRAMEWORK_URL']);
    // FRAMEWORK_PATH == BOOTSTRAP_PATH !
    define('FRAMEWORK_PATH', $app['utils']->sanitizePath(BOOTSTRAP_PATH));
    define('FRAMEWORK_TEMP_PATH', isset($framework_config['FRAMEWORK_TEMP_PATH']) ?
        $framework_config['FRAMEWORK_TEMP_PATH'] : FRAMEWORK_PATH . '/temp');
    define('FRAMEWORK_TEMP_URL', isset($framwework_config['FRAMEWORK_TEMP_URL']) ?
        $framework_config['FRAMEWORK_TEMP_URL'] : FRAMEWORK_URL . '/temp');
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
    define('FRAMEWORK_DEBUG', (isset($framework_config['DEBUG'])) ?
        $framework_config['DEBUG'] : false);
    define('FRAMEWORK_CACHE', (isset($framework_config['CACHE'])) ?
        $framework_config['CACHE'] : true);
    define('CATALOG_ACCEPT_EXTENSION', isset($framework_config['CATALOG_ACCEPT_EXTENSION']) ?
        implode(',', $framework_config['CATALOG_ACCEPT_EXTENSION']) :
        implode(',', array('beta','pre-release','release')));

    if (!isset($framework_config['CATALOG_ACCEPT_EXTENSION'])) {
        // add the accepted extensions to the framework configuration array
        $framework_config['CATALOG_ACCEPT_EXTENSION'] = explode(',', CATALOG_ACCEPT_EXTENSION);
    }
} catch (\Exception $e) {
    throw new \Exception('Problem setting the framework constants!', 0, $e);
}

// debug mode
$app['debug'] = FRAMEWORK_DEBUG;

// get the filesystem into the application
$app['filesystem'] = function()
{
    return new Filesystem();
};

$directories = array(
    FRAMEWORK_PATH . '/logfile',
    FRAMEWORK_PATH . '/temp/cache',
    FRAMEWORK_PATH . '/temp/session'
);

// check the needed temporary directories and create them if needed
if (! $app['filesystem']->exists($directories))
    $app['filesystem']->mkdir($directories);

if (!isset($framework_config['LOGFILE_MAX_SIZE'])) {
    // set the default value for the logfile size
    $framework_config['LOGFILE_MAX_SIZE'] = 2 * 1024 * 1024; // 2 MB;
}

$log_file = FRAMEWORK_PATH . '/logfile/kit2.log';
if ($app['filesystem']->exists($log_file) && (filesize($log_file) > $framework_config['LOGFILE_MAX_SIZE'])) {
    $app['filesystem']->remove(FRAMEWORK_PATH . '/logfile/kit2.log.bak');
    $app['filesystem']->rename($log_file, FRAMEWORK_PATH . '/logfile/kit2.log.bak');
}

date_default_timezone_set('Europe/Berlin');
// register monolog
$app->register(new Silex\Provider\MonologServiceProvider(), array(
    'monolog.logfile' => $log_file,
    'monolog.level' => FRAMEWORK_DEBUG ? Logger::DEBUG : Logger::WARNING,
    'monolog.name' => 'kitFramework'
));
$app['monolog']->addDebug('MonologServiceProvider registered.');

try {
    // read the CMS configuration
    $cms_config = $app['utils']->readConfiguration(FRAMEWORK_PATH . '/config/cms.json');
    // set the CMS_PATH from FRAMEWORK_PATH because it can change at mobile installations
    define('CMS_PATH', $app['utils']->sanitizePath(FRAMEWORK_PATH.'/../'));
    define('CMS_URL', $cms_config['CMS_URL']);
    define('CMS_MEDIA_PATH', $app['utils']->sanitizePath($cms_config['CMS_MEDIA_PATH']));
    define('CMS_MEDIA_URL', $cms_config['CMS_MEDIA_URL']);
    define('CMS_TEMP_PATH', $app['utils']->sanitizePath($cms_config['CMS_TEMP_PATH']));
    define('CMS_TEMP_URL', $cms_config['CMS_TEMP_URL']);
    define('CMS_ADMIN_PATH', $app['utils']->sanitizePath($cms_config['CMS_ADMIN_PATH']));
    define('CMS_ADMIN_URL', $cms_config['CMS_ADMIN_URL']);
    define('CMS_TYPE', $cms_config['CMS_TYPE']);
    define('CMS_VERSION', $cms_config['CMS_VERSION']);
} catch (\Exception $e) {
    throw new \Exception('Problem setting the CMS constants!', 0, $e);
}
$app['monolog']->addDebug('CMS constants defined.');

try {
    // read the doctrine configuration
    $doctrine_config = $app['utils']->readConfiguration(FRAMEWORK_PATH . '/config/doctrine.cms.json');
    define('CMS_TABLE_PREFIX', $doctrine_config['TABLE_PREFIX']);
    define('FRAMEWORK_TABLE_PREFIX', $doctrine_config['TABLE_PREFIX'] . 'kit2_');
    $app->register(new Silex\Provider\DoctrineServiceProvider(), array(
        'db.options' => array(
            'driver' => 'pdo_mysql',
            'dbname' => $doctrine_config['DB_NAME'],
            'user' => $doctrine_config['DB_USERNAME'],
            'password' => $doctrine_config['DB_PASSWORD'],
            'host' => $doctrine_config['DB_HOST'],
            'port' => $doctrine_config['DB_PORT']
        )
    ));
} catch (\Exception $e) {
    throw new \Exception('Problem initializing Doctrine!', 0, $e);
}
// $app['db']->query("SET NAMES 'utf8'");
$app['monolog']->addDebug('DoctrineServiceProvider registered');

// share the database utils
$app['db.utils'] = $app->share(function($app) {
    return new dbUtils($app);
});
$app['monolog']->addDebug('dbUtils registered');

if (!$app['db.utils']->isInnoDBsupported()) {
    // big problem: missing InnoDB support!
    throw new \Exception('Missing the MySQL InnoDB support, please check your server configuration!');
}

// register the session handler
$app->register(new Silex\Provider\SessionServiceProvider(), array(
    'session.storage.save_path' => FRAMEWORK_PATH . '/temp/session',
    'session.storage.options' => array(
        'cookie_lifetime' => 0
    )
));
$app['monolog']->addDebug('SessionServiceProvider registered.');

$app->before(function ($request) {
    $request->getSession()->start();
});

$app->register(new Silex\Provider\UrlGeneratorServiceProvider());
$app['monolog']->addDebug('UrlGeneratorServiceProvider registered.');

// default language
$locale = 'en';
// quick and dirty ... try to detect the favorised language - to be improved!
if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
    $langs = array();
    // break up string into pieces (languages and q factors)
    preg_match_all('/([a-z]{1,8}(-[a-z]{1,8})?)\s*(;\s*q\s*=\s*(1|0\.[0-9]+))?/i', $_SERVER['HTTP_ACCEPT_LANGUAGE'], $lang_parse);
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
}

// register the Translator
$app->register(new Silex\Provider\TranslationServiceProvider(), array(
    'locale_fallback' => 'en'
));

$app['translator'] = $app->share($app->extend('translator', function  ($translator, $app)
{
    $translator->addLoader('array', new ArrayLoader());
    return $translator;
}));

$app['translator']->setLocale($locale);

$app['monolog']->addDebug('Translator Service registered. Added ArrayLoader to the Translator');

// load the language files
$app['utils']->addLanguageFiles(MANUFAKTUR_PATH.'/Basic/Data/Locale');

// load the /Custom language files
$app['utils']->addLanguageFiles(MANUFAKTUR_PATH.'/Basic/Data/Locale/Custom');

// load the /Metric language files
$app['utils']->addLanguageFiles(MANUFAKTUR_PATH.'/Basic/Data/Locale/Metric');

// register the ReCaptcha service
$app['recaptcha'] = $app->share(function($app) {
    return new ReCaptcha($app);
});

// register Twig
$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.options' => array(
        //'cache' => $app['debug'] ? false : FRAMEWORK_PATH . '/temp/cache/',
        'cache' => FRAMEWORK_CACHE ? FRAMEWORK_PATH . '/temp/cache/' : false,
        'strict_variables' => $app['debug'] ? true : false,
        'debug' => FRAMEWORK_DEBUG ? true : false,
        'autoescape' => false
    )
));

// set namespaces for phpManufaktur, thirdParty, framework and CMS template
$app['twig.loader.filesystem']->addPath(MANUFAKTUR_PATH, 'phpManufaktur');
$app['twig.loader.filesystem']->addPath(THIRDPARTY_PATH, 'thirdParty');
// IMPORTANT: define these namespaces also in phpManufaktur\Basic\Control\Utils\templateFile()

$app['twig'] = $app->share($app->extend('twig', function  ($twig, $app)
{
    // add global variables, functions etc. for the templates
    $twig->addExtension(new twigExtension($app));
    if ($app['debug']) {
        $twig->addExtension(new Twig_Extension_Debug());
    }
    $twig->addExtension(new Twig_Extension_StringLoader());
    return $twig;
}));

$app['monolog']->addDebug('TwigServiceProvider registered.');

// register the Markdown service provider
$app->register(new MarkdownServiceProvider());

// register Validator Service
$app->register(new Silex\Provider\ValidatorServiceProvider());
$app['monolog']->addDebug('Validator Service Provider registered.');

// register the FormServiceProvider
$app->register(new Silex\Provider\FormServiceProvider());
$app['monolog']->addDebug('Form Service registered.');

// register the HTTP Cache Service
$app->register(new Silex\Provider\HttpCacheServiceProvider(), array(
    'http_cache.cache_dir' => FRAMEWORK_PATH . '/temp/cache/'
));
$app['monolog']->addDebug('HTTP Cache Service registered.');

try {
    // register the SwiftMailer
    $swift_config = $app['utils']->readConfiguration(FRAMEWORK_PATH . '/config/swift.cms.json');
    $app->register(new Silex\Provider\SwiftmailerServiceProvider());
    $app['swiftmailer.options'] = array(
        'host' => isset($swift_config['SMTP_HOST']) ? $swift_config['SMTP_HOST'] : 'localhost',
        'port' => isset($swift_config['SMTP_PORT']) ? $swift_config['SMTP_PORT'] : '25',
        'username' => $swift_config['SMTP_USERNAME'],
        'password' => $swift_config['SMTP_PASSWORD'],
        'encryption' => isset($swift_config['SMTP_ENCRYPTION']) ? $swift_config['SMTP_ENCRYPTION'] : null,
        'auth_mode' => isset($swift_config['SMTP_AUTH_MODE']) ? $swift_config['SMTP_AUTH_MODE'] : null
    );
    define('SERVER_EMAIL_ADDRESS', $swift_config['SERVER_EMAIL']);
    define('SERVER_EMAIL_NAME', $swift_config['SERVER_NAME']);
    $app['monolog']->addDebug('SwiftMailer Service registered');

    // check the auto mailing
    if (!isset($framework_config['LOGFILE_EMAIL_ACTIVE'])) {
        $framework_config['LOGFILE_EMAIL_ACTIVE'] = true;
        $framework_config['LOGFILE_EMAIL_LEVEL'] = 400; // 400 = ERROR
        $framework_config['LOGFILE_EMAIL_SUBJECT'] = 'kitFramework error at: '.FRAMEWORK_URL;
        $framework_config['LOGFILE_EMAIL_TO'] = SERVER_EMAIL_ADDRESS;
    }
    define('LOGFILE_EMAIL_ACTIVE', $framework_config['LOGFILE_EMAIL_ACTIVE']);
    define('LOGFILE_EMAIL_LEVEL', $framework_config['LOGFILE_EMAIL_LEVEL']);

    if (LOGFILE_EMAIL_ACTIVE) {
        // push handler for SwiftMail to Monolog to prompt errors
        $message = \Swift_Message::newInstance($framework_config['LOGFILE_EMAIL_SUBJECT'])
        ->setFrom(SERVER_EMAIL_ADDRESS, SERVER_EMAIL_NAME)
        ->setTo($framework_config['LOGFILE_EMAIL_TO'])
        ->setBody('kitFramework errror report');
        $app['monolog']->pushHandler(new SwiftMailerHandler($app['mailer'], $message, LOGFILE_EMAIL_LEVEL));
        $app['monolog']->addDebug('Monolog handler for SwiftMailer initialized');
    }
} catch (\Exception $e) {
    throw new \Exception('Problem initializing the SwiftMailer!');
}


if (FRAMEWORK_SETUP) {
    // execute the setup routine for kitFramework::Basic
    $Setup = new Setup();
    $Setup->exec($app);
}

// init the firewall
$app->register(new Silex\Provider\SecurityServiceProvider(), array(
    'security.firewalls' => array(
        'general' => array(
            'pattern' => '^/',
            'anonymous' => true,
            'form' => array(
                'login_path' => '/login',
                'check_path' => '/admin/login_check'
            ),
            'users' => $app->share(function  () use( $app)
            {
                return new UserProvider($app);
            }),
            'logout' => array(
                'logout_path' => '/admin/logout',
                'target_url' => '/goodbye'
            )
        )
    ),
    'security.encoder.digest' => $app->share(function ($app)
    {
        return new manufakturPasswordEncoder($app);
    }),
    'security.access_rules' => array(
        array('^/admin', 'ROLE_ADMIN')
    ),
    'security.role_hierarchy' => array(
        'ROLE_ADMIN' => array('ROLE_USER', 'ROLE_ALLOWED_TO_SWITCH')
    )
));

$app['security.authentication.logout_handler.general'] = $app->share(function () use ($app) {
    return new CustomLogoutSuccessHandler(
        $app['security.http_utils'], '/goodbye');
});
$app['security.authentication.success_handler.general'] = $app->share(function () use ($app) {
    return new CustomAuthenticationSuccessHandler($app['security.http_utils'], array(), $app);
});

// register the ACCOUNT class
$app['account'] = $app->share(function($app) {
    return new Account($app);
});

if (FRAMEWORK_SETUP) {
    // the setup flag was set to TRUE, now we assume that we can set it to FALSE
    $framework_config['FRAMEWORK_SETUP'] = false;
    if (!file_put_contents(FRAMEWORK_PATH. '/config/framework.json', $app['utils']->JSONFormat($framework_config)))
        throw new \Exception('Can\'t write the configuration file for the framework!');
    $app['monolog']->addDebug('Finished kitFramework setup.');
}


// ADMIN
$admin = $app['controllers_factory'];
// kitCOMMAND
$command = $app['controllers_factory'];
// kitFilter
$filter = $app['controllers_factory'];

// loop through /phpManufaktur and /thirdParty to include bootstrap extensions
$scan_paths = array(MANUFAKTUR_PATH, THIRDPARTY_PATH);
foreach ($scan_paths as $scan_path) {
    $entries = scandir($scan_path);
    foreach ($entries as $entry) {
        if (is_dir($scan_path . '/' . $entry)) {
            if (file_exists($scan_path . '/' . $entry . '/bootstrap.include.php')) {
                // don't load the Basic bootstrap again
                if ($entry == 'Basic') continue;
                // include the bootstrap extension
                include_once $scan_path . '/' . $entry . '/bootstrap.include.php';
            }
        }
    }
}

// GENERAL ROUTES for the kitFramework

$app->get('/', function() use($app) {
    // redirect to the protected welcome dialog
    return $app->redirect(FRAMEWORK_URL.'/admin/welcome');
});
$app->get('/login',
    // the general login dialog
    'phpManufaktur\Basic\Control\Login::exec');
$app->get('/password/forgotten',
    // password forgotten?
    'phpManufaktur\Basic\Control\forgottenPassword::dialogForgottenPassword');
$app->post('password/reset',
    // create a new password
    'phpManufaktur\Basic\Control\forgottenPassword::dialogResetPassword');
$app->post('/password/retype',
    // confirm the new password
    'phpManufaktur\Basic\Control\forgottenPassword::dialogRetypePassword');
$app->get('/password/create/{guid}',
    // confirm the link the create a new password
    'phpManufaktur\Basic\Control\forgottenPassword::dialogCreatePassword');
$app->post('/login/first/cms',
    // first login into the framework from the CMS backend
    'phpManufaktur\Basic\Control\Account\FirstLogin::controllerCMSLogin');
$app->post('/login/first/cms/check',
    // first login into the kitFramework
    'phpManufaktur\Basic\Control\Account\FirstLogin::controllerCheckCMSLogin');
$app->match('/goodbye',
    // show the default logout and bye bye message
    'phpManufaktur\Basic\Control\GoodBye::controllerGoodBye');
$app->get('/logout',
    // set paremeters and redirect to /admin/logout
    'phpManufaktur\Basic\Control\GoodBye::controllerLogout');

// ADMIN ROUTES
$admin->get('/basic/setup',
    // setup for the BASIC extension tables (normally not needed!)
    'phpManufaktur\Basic\Data\Setup\Setup::exec');
$admin->get('/basic/update',
    // update the BASIC extension tables
    'phpManufaktur\Basic\Data\Setup\Update::exec');
$admin->get('/basic/uninstall',
    // uninstall the BASIC extension tables (be carefull!)
    'phpManufaktur\Basic\Data\Setup\Uninstall::exec');


$app->get('/admin',
    // redirect to the welcome dialog
    'phpManufaktur\Basic\Control\Welcome::controllerFramework');
$admin->get('/',
    // redirect to the welcome dialog
    'phpManufaktur\Basic\Control\Welcome::controllerFramework');
$admin->get('/account',
    // user account dialog
    'phpManufaktur\Basic\Control\Account::exec');
$admin->get('/welcome',
    // the general welcome dialog
    'phpManufaktur\Basic\Control\Welcome::controllerFramework');
$admin->match('/scan/extensions',
    // scan for installed extensions
    'phpManufaktur\Basic\Control\ScanExtensions::exec');
$admin->get('/scan/catalog',
    // scan for available extensions from Github
    'phpManufaktur\Basic\Control\ScanCatalog::exec');

$admin->get('updater/install/{catalog_id}',
    // install a extension
    'phpManufaktur\Updater\Updater::controllerInstallExtension');
$admin->get('updater/update/{extension_id}',
    // update a extension
    'phpManufaktur\Updater\Updater::controllerUpdateExtension');

$app->get('/welcome/cms/{cms}',
    // the welcome dialog is called by the CMS backend
    'phpManufaktur\Basic\Control\Welcome::controllerCMS');
$app->post('/welcome/login/check',
    // first login check
    'phpManufaktur\Basic\Control\Welcome::checkFirstLogin');

// kitFILTER
$app->post('/kit_filter/{filter}',
    'phpManufaktur\Basic\Control\kitFilter\kitFilter::exec');
$app->post('/kit_filter/{filter}/{params}',
    'phpManufaktur\Basic\Control\kitFilter\kitFilter::exec');

$filter->post('/mailhide',
    'phpManufaktur\Basic\Control\kitFilter\MailHide::exec')
    ->setOption('info', MANUFAKTUR_PATH.'/Basic/filter.mailhide.json');

// kitCOMMAND
$app->post('/kit_command/{command}',
    'phpManufaktur\Basic\Control\kitCommand\kitCommand::exec');
$app->post('/kit_command/{command}/{params}',
    'phpManufaktur\Basic\Control\kitCommand\kitCommand::exec');
$command->post('/help',
    'phpManufaktur\Basic\Control\kitCommand\Help::createHelpFrame')
    ->setOption('info', MANUFAKTUR_PATH.'/Basic/command.help.json');
$command->post('/list',
    'phpManufaktur\Basic\Control\kitCommand\ListCommands::createListFrame')
    ->setOption('info', MANUFAKTUR_PATH.'/Basic/command.list.json');

// BASIC responses to kitCommands
$app->get('/basic/help/{command}',
    // show the help for the requested kitCommand
    'phpManufaktur\Basic\Control\kitCommand\Help::getHelpPage');
$app->get('/basic/help/{command}/{help_file}',
    'phpManufaktur\Basic\Control\kitCommand\Help::getHelpPage');
$app->get('/basic/list',
    // return a list with all available kitCommands and additional information
    'phpManufaktur\Basic\Control\kitCommand\ListCommands::getList');

// kitSEARCH
$app->post('/kit_search/command/{command}',
    // catch all searches within kitCommands
    'phpManufaktur\Basic\Control\kitSearch\Search::exec');

// mount the controller factories
$app->mount('/admin', $admin);
$app->mount('/command', $command);
$app->mount('/filter', $filter);

$app->error(function (\Exception $e, $code) use ($app) {
    if ($app['debug'] && ($code != 403)) {
        // on debugging mode use the regular exception handler!
        return;
    }
    switch ($code) {
        case 404:
            // the requested page could not be found
            $message = $app['twig']->render($app['utils']->getTemplateFile(
                '@phpManufaktur/Basic/Template', 'framework/error.404.twig'));
            break;
        case 403:
            // access denied
            $message = $app['twig']->render($app['utils']->getTemplateFile(
                '@phpManufaktur/Basic/Template', 'framework/error.403.twig'));
            break;
        default:
            // general error message
            $message = $app['twig']->render($app['utils']->getTemplateFile(
                '@phpManufaktur/Basic/Template', 'framework/error.twig'),
                array(
                    'code' => $code,
                    'message' => array(
                        'full' => $e->getMessage(),
                        'short' => substr($e->getMessage(), 0, stripos($e->getMessage(), 'Stack trace:'))
                    ),
                    'file' => substr($app['utils']->sanitizePath($e->getFile()), strlen(FRAMEWORK_PATH)),
                    'line' => $e->getLine()
                ));
            break;
    }
    return new Response($message);
});

if (FRAMEWORK_DEBUG || !FRAMEWORK_CACHE) {
    // don't use cache
    $app->run();
}
else {
    // run in cache mode
    $app['http_cache']->run();
}
