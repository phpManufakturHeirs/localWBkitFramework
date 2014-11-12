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
use phpManufaktur\Basic\Control\Account\UserProvider;
use phpManufaktur\Basic\Control\Account\manufakturPasswordEncoder;
use phpManufaktur\Basic\Control\twigExtension;
use phpManufaktur\Basic\Control\Utils;
use phpManufaktur\Basic\Data\Setup\Setup;
use Symfony\Component\HttpFoundation\Response;
use Monolog\Handler\SwiftMailerHandler;
use phpManufaktur\Basic\Control\ReCaptcha\ReCaptcha;
use phpManufaktur\Basic\Control\Account\Account;
use Symfony\Bridge\Monolog\Logger;
use phpManufaktur\Basic\Control\Account\CustomLogoutSuccessHandler;
use phpManufaktur\Basic\Control\Account\CustomAuthenticationSuccessHandler;
use phpManufaktur\Basic\Data\dbUtils;
use phpManufaktur\Basic\Control\Image;
use phpManufaktur\Basic\Control\MarkdownFunctions;
use Symfony\Component\Finder\Finder;
use phpManufaktur\Basic\Control\CMS\EmbeddedAdministration;
use Symfony\Component\HttpFoundation\Request;

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

// get the filesystem into the application
$app['filesystem'] = function() {
    return new Filesystem();
};

try {
    // check for the framework configuration file
    $framework_config = $app['utils']->readConfiguration(realpath(BOOTSTRAP_PATH . '/config/framework.json'));
    // framework constants
    define('FRAMEWORK_URL', $framework_config['FRAMEWORK_URL']);
    // FRAMEWORK_PATH == BOOTSTRAP_PATH !
    define('FRAMEWORK_PATH', $app['utils']->sanitizePath(BOOTSTRAP_PATH));
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
    define('FRAMEWORK_DEBUG', (isset($framework_config['DEBUG'])) ?
        $framework_config['DEBUG'] : false);
    define('FRAMEWORK_CACHE', (isset($framework_config['CACHE'])) ?
        $framework_config['CACHE'] : true);

    define('LIBRARY_PATH', MANUFAKTUR_PATH.'/Library/Library');
    define('LIBRARY_URL', MANUFAKTUR_URL.'/Library/Library');

    if ($app['filesystem']->exists(MANUFAKTUR_PATH.'/Library/Extension')) {
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
} catch (\Exception $e) {
    throw new \Exception('Problem setting the framework constants!', 0, $e);
}
// debug mode
$app['debug'] = FRAMEWORK_DEBUG;

$directories = array(
    FRAMEWORK_PATH . '/logfile',
    FRAMEWORK_PATH . '/temp/cache',
    FRAMEWORK_PATH . '/temp/session'
);

// check the needed temporary directories and create them if needed
if (!$app['filesystem']->exists($directories)) {
    $app['filesystem']->mkdir($directories);
}

// set the default timezone to avoid problems
date_default_timezone_set('Europe/Berlin');

// register monolog
$app->register(new Silex\Provider\MonologServiceProvider(), array(
    'monolog.logfile' => FRAMEWORK_PATH.'/logfile/framework.log',
    'monolog.level' => FRAMEWORK_DEBUG ? Logger::DEBUG : Logger::WARNING,
    'monolog.name' => 'kitFramework',
    'monolog.maxfiles' => isset($framework_config['LOGFILE_ROTATE_MAXFILES']) ? $framework_config['LOGFILE_ROTATE_MAXFILES'] : 10
));
$app['monolog']->popHandler();
$app['monolog']->pushHandler(new Monolog\Handler\RotatingFileHandler(
    $app['monolog.logfile'],
    $app['monolog.maxfiles'],
    $app['monolog.level'],
    false
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
    define('CMS_TEMP_PATH', CMS_PATH.'/temp');
    define('CMS_TEMP_URL', CMS_URL.'/temp');
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

if (!version_compare($app['db.utils']->getMySQLversion(), '5.0.3', '>=')) {
    // MySQL version must be at minimum 5.0.3
    throw new \Exception('Need at minimum MySQL version 5.0.3, please check your server configuration!');
}

// register the session handler
$app->register(new Silex\Provider\SessionServiceProvider(), array(
    'session.storage.save_path' => FRAMEWORK_PATH . '/temp/session',
    'session.storage.options' => array(
        'cookie_lifetime' => 0
    )
));
$app['monolog']->addDebug('SessionServiceProvider registered.');

$app->register(new Silex\Provider\UrlGeneratorServiceProvider());
$app['monolog']->addDebug('UrlGeneratorServiceProvider registered.');

// register the Translator
$app->register(new Silex\Provider\TranslationServiceProvider());
$app['monolog']->addDebug('Translator Service registered. Added ArrayLoader to the Translator');

// load the language files for all extensions
$extensions = new Finder();
$extensions->directories()->in(array(MANUFAKTUR_PATH, THIRDPARTY_PATH));
$extensions->depth('== 0');

$search = array(MANUFAKTUR_PATH.'/Basic/Data/Locale/Metric');

// first add the existing regular locale directories
foreach ($extensions as $extension) {
    if ($app['filesystem']->exists($extension->getRealpath().'/Data/Locale')) {
        $search[] = $extension->getRealpath().'/Data/Locale';
    }
}
// at least add the existing custom locale directories
foreach ($extensions as $extension) {
    if ($app['filesystem']->exists($extension->getRealpath().'/Data/Locale/Custom')) {
        $search[] = $extension->getRealpath().'/Data/Locale/Custom';
    }
}

$locales = new Finder();
$locales->name('*.php')->in($search);

$locales->depth('== 0');
foreach ($locales as $locale) {
    // add the locale resource file
    $app['translator'] = $app->share($app->extend('translator', function ($translator) use ($locale) {
        $lang_array = include_once $locale->getRealpath();
        $translator->addResource('array', $lang_array, $locale->getBasename('.php'));
        return $translator;
    }));
    $app['monolog']->addDebug('Added language file: '.$locale->getRealpath());
}

// share the ReCaptcha service
$app['recaptcha'] = $app->share(function($app) {
    return new ReCaptcha($app);
});
$app['monolog']->addDebug('Share the reCaptcha Service');

// share the Image Tools
$app['image'] = $app->share(function($app) {
    return new Image($app);
});
$app['monolog']->addDebug('Share the Image Tools');

// Markdown Parser
$app['markdown'] = $app->share(function($app) {
    return new MarkdownFunctions($app);
});
$app['monolog']->addDebug('Share the Markdown Functions');

try {
    // register the SwiftMailer
    $swift_config = $app['utils']->readConfiguration(FRAMEWORK_PATH . '/config/swift.cms.json');
    $app->register(new Silex\Provider\SwiftmailerServiceProvider());
    $app['swiftmailer.options'] = array(
        'host' => isset($swift_config['SMTP_HOST']) ? $swift_config['SMTP_HOST'] : 'localhost',
        'port' => isset($swift_config['SMTP_PORT']) ? $swift_config['SMTP_PORT'] : '25',
        'username' => $swift_config['SMTP_USERNAME'],
        'password' => $swift_config['SMTP_PASSWORD'],
        // possible values are ssl, tls or null
        'encryption' => isset($swift_config['SMTP_ENCRYPTION']) ? $swift_config['SMTP_ENCRYPTION'] : null,
        // possible values are plain, login, cram-md5, or null
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

// register Twig
$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.options' => array(
        'cache' => FRAMEWORK_CACHE ? FRAMEWORK_PATH . '/temp/cache/' : false,
        'strict_variables' => $app['debug'] ? true : false,
        'debug' => FRAMEWORK_DEBUG ? true : false,
        'autoescape' => false
    )
));

// set namespaces for phpManufaktur and thirdParty
$app['twig.loader.filesystem']->addPath(MANUFAKTUR_PATH, 'phpManufaktur');
$app['twig.loader.filesystem']->addPath(MANUFAKTUR_PATH, 'phpmanufaktur');
$app['twig.loader.filesystem']->addPath(THIRDPARTY_PATH, 'thirdParty');
$app['twig.loader.filesystem']->addPath(THIRDPARTY_PATH, 'thirdparty');
if ($app['filesystem']->exists(MANUFAKTUR_PATH.'/TemplateTools/Pattern')) {
    // register the @pattern and @templates Namespaces to enable usage of the TemplateTools
    $app['twig.loader.filesystem']->addPath(MANUFAKTUR_PATH.'/TemplateTools/Pattern', 'Pattern');
    $app['twig.loader.filesystem']->addPath(MANUFAKTUR_PATH.'/TemplateTools/Pattern', 'pattern');
    $app['twig.loader.filesystem']->addPath(CMS_PATH.'/templates', 'Templates');
    $app['twig.loader.filesystem']->addPath(CMS_PATH.'/templates', 'templates');
}
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
            ),
            'switch_user' => array(
                'parameter' => '_switch_user',
                'role' => 'ROLE_ALLOWED_TO_SWITCH'
            )
        )
    ),
    'security.encoder.digest' => $app->share(function ($app)
    {
        return new manufakturPasswordEncoder($app);
    }),
    'security.access_rules' => array(
        array('^/admin', 'ROLE_ADMIN'),
        array('^/user', 'ROLE_USER')
    ),
    'security.role_hierarchy' => array(
        'ROLE_ADMIN' => array(
            'ROLE_USER',
            'ROLE_ALLOWED_TO_SWITCH'
        )
    ),
    'security.role_entry_points' => array(
        'ROLE_ADMIN' => array(
            array(
                'route' => '/admin/welcome/extensions',
                'name' => $app['translator']->trans('Extensions'),
                'info' => $app['translator']->trans('Install, update or remove kitFramework Extensions'),
                'icon' => array(
                    'path' => '/extension/phpmanufaktur/phpManufaktur/Basic/framework.jpg',
                    'url' => MANUFAKTUR_URL.'/Basic/framework.jpg'
                )
            ),
            array(
                'route' => '/admin/accounts/list',
                'name' => $app['translator']->trans('Accounts'),
                'info' => $app['translator']->trans('Access to kitFramework User Accounts'),
                'icon' => array(
                    'path' => '/extension/phpmanufaktur/phpManufaktur/Basic/Template/default/framework/image/user-accounts.jpg',
                    'url' => MANUFAKTUR_URL.'/Basic/Template/default/framework/image/user-accounts.jpg'
                )
            ),
            array(
                'route' => '/admin/json/editor',
                'name' => $app['translator']->trans('Configuration'),
                'info' => $app['translator']->trans('View and edit the kitFramework configuration files'),
                'icon' => array(
                    'path' => '/extension/phpmanufaktur/phpManufaktur/Basic/framework.jpg',
                    'url' => MANUFAKTUR_URL.'/Basic/framework.jpg'
                )
            ),
            array(
                'route' => '/admin/test/mail',
                'name' => $app['translator']->trans('Test email'),
                'info' => $app['translator']->trans('Check the email settings and send a email to the webmaster for testing purpose'),
                'icon' => array(
                    'path' => '/extension/phpmanufaktur/phpManufaktur/Basic/framework.jpg',
                    'url' => MANUFAKTUR_URL.'/Basic/framework.jpg'
                )
            ),
            array(
                'route' => '/admin/i18n/editor',
                'name' => $app['translator']->trans('i18n Editor'),
                'info' => $app['translator']->trans('Parse the kitFramework for locale strings, add custom translations and administrate the internationalization'),
                'icon' => array(
                    'path' => '/extension/phpmanufaktur/phpManufaktur/Basic/extension.i18n.editor.jpg',
                    'url' => MANUFAKTUR_URL.'/Basic/extension.i18n.editor.jpg'
                )
            ),
            array(
                'route' => '/admin/i18n/editor/table/truncate',
                'name' => $app['translator']->trans('i18n truncate'),
                'info' => $app['translator']->trans('Truncate all i18n analyze tables and start a fresh translation session, no translations will be lost.'),
                'icon' => array(
                    'path' => '/extension/phpmanufaktur/phpManufaktur/Basic/extension.i18n.editor.truncate.jpg',
                    'url' => MANUFAKTUR_URL.'/Basic/extension.i18n.editor.truncate.jpg'
                )
            )
        )
    ),
    'security.roles_provided' => array(
        'ROLE_USER',
        'ROLE_ADMIN'
    )
 ));

$app['security.authentication.logout_handler.general'] = $app->share(function () use ($app)
{
    return new CustomLogoutSuccessHandler(
        $app['security.http_utils'], '/goodbye');
});
$app['security.authentication.success_handler.general'] = $app->share(function () use ($app)
{
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
// USER
$user = $app['controllers_factory'];
// kitCOMMAND
$command = $app['controllers_factory'];
// kitFilter
$filter = $app['controllers_factory'];

// loop through /phpManufaktur and /thirdParty to include all bootstrap.include.php files
$bootstraps = new Finder();
$bootstraps->name('bootstrap.include.php')->in(array(MANUFAKTUR_PATH, THIRDPARTY_PATH))->exclude('Basic');
$bootstraps->depth('< 2');
foreach ($bootstraps as $bootstrap) {
    include_once $bootstrap->getRealpath();
}


// GENERAL ROUTES for the kitFramework

$app->get('/',
    'phpManufaktur\Basic\Control\Account\RoleEntryPoints::ControllerRoleEntryPoints');

$app->get('/login',
    // the general login dialog
    'phpManufaktur\Basic\Control\Account\Login::exec');
$app->get('/password/forgotten',
    // password forgotten?
    'phpManufaktur\Basic\Control\Account\forgottenPassword::dialogForgottenPassword');
$app->post('password/reset',
    // create a new password
    'phpManufaktur\Basic\Control\Account\forgottenPassword::dialogResetPassword');
$app->post('/password/retype',
    // confirm the new password
    'phpManufaktur\Basic\Control\Account\forgottenPassword::dialogRetypePassword');
$app->get('/password/create/{guid}',
    // confirm the link the create a new password
    'phpManufaktur\Basic\Control\Account\forgottenPassword::dialogCreatePassword');
$app->post('/login/first/cms',
    // first login into the framework from the CMS backend
    'phpManufaktur\Basic\Control\Account\FirstLogin::controllerCMSLogin');
$app->post('/login/first/cms/check',
    // first login into the kitFramework
    'phpManufaktur\Basic\Control\Account\FirstLogin::controllerCheckCMSLogin');
$app->match('/goodbye',
    // show the default logout and bye bye message
    'phpManufaktur\Basic\Control\Account\GoodBye::controllerGoodBye');
$app->get('/logout',
    // set parameters and redirect to /admin/logout
    'phpManufaktur\Basic\Control\Account\GoodBye::controllerLogout');

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
    'phpManufaktur\Basic\Control\Account\RoleEntryPoints::ControllerRoleEntryPoints');
$admin->get('/',
    // redirect to the role entry points
    'phpManufaktur\Basic\Control\Account\RoleEntryPoints::ControllerRoleEntryPoints');
$admin->get('/welcome',
    // the general welcome dialog
    'phpManufaktur\Basic\Control\cmsTool::ControllerFramework');
$admin->match('/scan/extensions',
    // scan for installed extensions
    'phpManufaktur\Basic\Control\ScanExtensions::exec');
$admin->get('/scan/catalog',
    // scan for available extensions from Github
    'phpManufaktur\Basic\Control\ScanCatalog::Controller');
$admin->get('/welcome/about',
    'phpManufaktur\Basic\Control\cmsTool::ControllerAbout');
$admin->get('/welcome/extensions',
    'phpManufaktur\Basic\Control\cmsTool::ControllerExtensions');
$admin->get('/welcome/extensions/installed',
    'phpManufaktur\Basic\Control\cmsTool::ControllerExtensionsInstalled');
$admin->get('/welcome/extensions/catalog',
    'phpManufaktur\Basic\Control\cmsTool::ControllerExtensionsCatalog');

$app->get('/welcome/cms/{cms}',
    // the welcome dialog is called by the CMS backend
    'phpManufaktur\Basic\Control\cmsTool::ControllerCMS');
$app->post('/welcome/login/check',
    // first login check
    'phpManufaktur\Basic\Control\cmsTool::checkFirstLogin');

$admin->get('/updater/install/{catalog_id}',
    // install a extension
    'phpManufaktur\Updater\Updater::controllerInstallExtension');
$admin->get('/updater/update/{extension_id}',
    // update a extension
    'phpManufaktur\Updater\Updater::controllerUpdateExtension');
$admin->get('/updater/remove/{extension_id}',
    'phpManufaktur\Updater\Updater::controllerRemoveExtension');

$admin->get('/updater/download/framework',
    'phpManufaktur\Updater\Updater::ControllerDownloadFramework');
$admin->get('/updater/remove/framework/restore',
    'phpManufaktur\Updater\Updater::ControllerRemoveFrameworkRestore');

$admin->get('/accounts/list/{page}',
    'phpManufaktur\Basic\Control\Account\Dialog\AccountAdminList::ControllerAccountList')
    ->value('page', 1);
$admin->get('/accounts/edit/{id}',
    'phpManufaktur\Basic\Control\Account\Dialog\AccountAdminEdit::ControllerAccountEdit')
    ->value('id', -1);
$admin->post('/accounts/edit/check',
    'phpManufaktur\Basic\Control\Account\Dialog\AccountAdminEdit::ControllerAccountEditCheck');

/**
 * Use the EmbeddedAdministration feature to connect the jsonEditor with the CMS
 *
 * @link https://github.com/phpManufaktur/kitFramework/wiki/Extensions-%23-Embedded-Administration
 */
$app->get('/basic/cms/jsoneditor/{cms_information}', function ($cms_information) use ($app) {
    $administration = new EmbeddedAdministration($app);
    return $administration->route('/admin/json/editor', $cms_information, 'ROLE_ADMIN');
});

// JSON editor
$admin->get('/json/editor',
    'phpManufaktur\Basic\Control\jsonEditor\jsonEditor::Controller');
$admin->get('/json/editor/scan',
    'phpManufaktur\Basic\Control\jsonEditor\jsonEditor::ControllerScanFramework');
$admin->post('/json/editor/load',
    'phpManufaktur\Basic\Control\jsonEditor\jsonEditor::ControllerLoadFile');
$admin->get('/json/editor/open/file/{filename}',
    'phpManufaktur\Basic\Control\jsonEditor\jsonEditor::ControllerOpenFile');
$app->post('/json/editor/save',
    'phpManufaktur\Basic\Control\jsonEditor\jsonEditor::ControllerSaveFile');

/**
 * Use the EmbeddedAdministration feature to connect the i18nEditor with the CMS
 *
 * @link https://github.com/phpManufaktur/kitFramework/wiki/Extensions-%23-Embedded-Administration
 */
$app->get('/basic/cms/i18n/editor/{cms_information}', function ($cms_information) use ($app)
{
    $administration = new EmbeddedAdministration($app);
    return $administration->route('/admin/i18n/editor', $cms_information, 'ROLE_ADMIN');
});

// i18nEditor
$admin->get('/i18n/editor',
    'phpManufaktur\Basic\Control\i18nEditor\i18nEditor::ControllerOverview');
$admin->get('/i18n/editor/overview',
    'phpManufaktur\Basic\Control\i18nEditor\i18nEditor::ControllerOverview');
$admin->get('/i18n/editor/about',
    'phpManufaktur\Basic\Control\i18nEditor\i18nEditor::ControllerAbout');
$admin->get('/i18n/editor/scan',
    'phpManufaktur\Basic\Control\i18nEditor\i18nEditor::ControllerScan');
$admin->get('/i18n/editor/locale/{locale}/pending',
    'phpManufaktur\Basic\Control\i18nEditor\i18nEditor::ControllerLocalePending');
$admin->get('/i18n/editor/translation/edit/id/{translation_id}',
    'phpManufaktur\Basic\Control\i18nEditor\i18nEditor::ControllerTranslationEdit')
    ->assert('translation_id', '\d+')
    ->value('translation_id', -1);
$admin->post('/i18n/editor/translation/edit/check',
    'phpManufaktur\Basic\Control\i18nEditor\i18nEditor::ControllerTranslationEditCheck');
$admin->get('/i18n/editor/locale/{locale}/files/{file_id}',
    'phpManufaktur\Basic\Control\i18nEditor\i18nEditor::ControllerLocaleFiles')
    ->assert('file_id', '\d+')
    ->value('file_id', -2);
$admin->post('/i18n/editor/locale/{locale}/file/select',
    'phpManufaktur\Basic\Control\i18nEditor\i18nEditor::ControllerLocaleFileSelect');
$admin->get('/i18n/editor/locale/{locale}/custom',
    'phpManufaktur\Basic\Control\i18nEditor\i18nEditor::ControllerLocaleCustom');
$admin->get('/i18n/editor/locale/{locale}/custom/new',
    'phpManufaktur\Basic\Control\i18nEditor\i18nEditor::ControllerLocaleCustomNew');
$admin->post('/i18n/editor/locale/custom/new/check',
    'phpManufaktur\Basic\Control\i18nEditor\i18nEditor::ControllerLocaleCustomNewCheck');
$admin->get('/i18n/editor/sources/{tab}',
    'phpManufaktur\Basic\Control\i18nEditor\i18nEditor::ControllerSources')
    ->value('tab', 'a-c');
$admin->get('/i18n/editor/sources/detail/{locale_id}',
    'phpManufaktur\Basic\Control\i18nEditor\i18nEditor::ControllerSourcesDetail')
    ->assert('locale_id', '\d+');
$admin->post('/i18n/editor/sources/detail/check',
    'phpManufaktur\Basic\Control\i18nEditor\i18nEditor::ControllerSourcesDetailCheck');
$admin->get('/i18n/editor/problems',
    'phpManufaktur\Basic\Control\i18nEditor\i18nEditor::ControllerProblemsConflicts');
$admin->get('/i18n/editor/problems/conflicts',
    'phpManufaktur\Basic\Control\i18nEditor\i18nEditor::ControllerProblemsConflicts');
$admin->get('/i18n/editor/problems/unassigned',
    'phpManufaktur\Basic\Control\i18nEditor\i18nEditor::ControllerProblemsUnassigned');
$admin->get('/i18n/editor/problems/duplicates',
    'phpManufaktur\Basic\Control\i18nEditor\i18nEditor::ControllerProblemsDuplicates');

$admin->get('/i18n/editor/table/create',
    'phpManufaktur\Basic\Control\i18nEditor\i18nParser::ControllerCreateTable');
$admin->get('/i18n/editor/table/drop',
    'phpManufaktur\Basic\Control\i18nEditor\i18nParser::ControllerDropTable');
$admin->get('/i18n/editor/table/truncate',
    'phpManufaktur\Basic\Control\i18nEditor\i18nParser::ControllerTruncateTable');

// send a testmail
$admin->get('/test/mail',
    'phpManufaktur\Basic\Control\Test\Mail::Controller');

// Switch user to show the roles assigned to the user
$app->get('/switched/user/roles/id/{id}',
    'phpManufaktur\Basic\Control\Account\Dialog\SwitchedUserRoles::ControllerSwitchedUserRoles')
    ->value('id', -1);
$app->get('/switched/user/roles/id/{id}/exit',
    'phpManufaktur\Basic\Control\Account\Dialog\SwitchedUserRoles::ControllerSwitchedUserRolesExit')
    ->value('id', -1);

// USER routes
$user->get('/account',
    // user account dialog
    'phpManufaktur\Basic\Control\Account\Dialog\Account::ControllerAccountEdit');
$user->post('/account/edit/check',
    'phpManufaktur\Basic\Control\Account\Dialog\Account::ControllerAccountEditCheck');


// kitCommand Parser
$app->post('/kit_parser',
    'phpManufaktur\Basic\Control\kitCommand\Parser::ControllerParser');

// kitFILTER
$app->post('/kit_filter/{filter}',
    'phpManufaktur\Basic\Control\kitFilter\kitFilter::exec');
$app->post('/kit_filter/{filter}/{params}',
    'phpManufaktur\Basic\Control\kitFilter\kitFilter::exec');
$app->post('/filter/exists/{filter}',
    'phpManufaktur\Basic\Control\kitFilter\ExistsFilter::ControllerExistsFilter');

$filter->post('/mailhide',
    'phpManufaktur\Basic\Control\kitFilter\MailHide::exec')
    ->setOption('info', MANUFAKTUR_PATH.'/Basic/filter.mailhide.json');

// kitCOMMAND
$command->post('/exists/{command}',
    'phpManufaktur\Basic\Control\kitCommand\ExistsCommand::ControllerExistsCommand');
$command->post('/help',
    'phpManufaktur\Basic\Control\kitCommand\Help::createHelpFrame')
    ->setOption('info', MANUFAKTUR_PATH.'/Basic/command.help.json');
$command->post('/list',
    'phpManufaktur\Basic\Control\kitCommand\ListCommands::createListFrame')
    ->setOption('info', MANUFAKTUR_PATH.'/Basic/command.list.json');
$command->post('/catalog',
    'phpManufaktur\Basic\Control\kitCommand\Catalog::controllerCreateIFrame')
    ->setOption('info', MANUFAKTUR_PATH.'/Basic/command.catalog.json');
$command->post('/guid',
    'phpManufaktur\Basic\Control\kitCommand\GUID::ControllerCreateIFrame')
    ->setOption('info', MANUFAKTUR_PATH.'/Basic/command.guid.json');
$command->post('/simulate',
    'phpManufaktur\Basic\Control\kitCommand\Simulate::ControllerCreateIFrame');

// BASIC responses to kitCommands
$app->get('/basic/alert/{alert}',
    'phpManufaktur\Basic\Control\kitCommand\PromptAlert::ControllerPromptAlert');
$app->get('/basic/help/{command}',
    // show the help for the requested kitCommand
    'phpManufaktur\Basic\Control\kitCommand\Help::getHelpPage');
$app->get('/basic/help/{command}/{help_file}',
    'phpManufaktur\Basic\Control\kitCommand\Help::getHelpPage');
$app->get('/basic/list',
    // return a list with all available kitCommands and additional information
    'phpManufaktur\Basic\Control\kitCommand\ListCommands::getList');
$app->get('/basic/catalog',
    'phpManufaktur\Basic\Control\kitCommand\Catalog::controllerCatalog');
$app->get('/basic/guid',
    'phpManufaktur\Basic\Control\kitCommand\GUID::ControllerGUID');
$app->get('/basic/simulate/copy',
    'phpManufaktur\Basic\Control\kitCommand\Simulate::ControllerSimulateCopy');

// kitSEARCH
$app->post('/kit_search/command/{command}',
    // catch all searches within kitCommands
    'phpManufaktur\Basic\Control\kitSearch\Search::exec');
$app->get('/kit_search_enabled',
    'phpManufaktur\Basic\Control\kitSearch\Search::SearchEnabled');

// mount the controller factories
$app->mount('/admin', $admin);
$app->mount('/user', $user);
$app->mount('/command', $command);
$app->mount('/filter', $filter);


$app->error(function (\Exception $e, $code) use ($app)
{
    if ($app['debug'] && ($code != 403)) {
        // on debugging mode use the regular exception handler!
        return;
    }
    switch ($code) {
        case 403:
        case 404:
        case 405:
        case 410:
        case 423:
            // prompt special template for this error
            $message = $app['twig']->render($app['utils']->getTemplateFile(
                '@phpManufaktur/Basic/Template', 'framework/error.'.$code.'.twig'),
                array('usage' => $app['request']->get('usage', 'framework')));
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
                    'line' => $e->getLine(),
                    'usage' => $app['request']->get('usage', 'framework')
                ));
            break;
    }
    return new Response($message);
});

$app->before(function(Request $request) use ($app)
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
        $app['translator']->setLocale($locale);
        $app['monolog']->addDebug('Set locale to '.$locale);
    }
});


if (FRAMEWORK_DEBUG || !FRAMEWORK_CACHE) {
    // don't use cache
    $app->run();
}
else {
    // run in cache mode
    $app['http_cache']->run();
}
