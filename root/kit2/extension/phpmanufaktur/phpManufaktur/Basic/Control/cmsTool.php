<?php

/**
 * kitFramework::Basic
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de
 * @copyright 2013 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\Basic\Control;

use Silex\Application;
use phpManufaktur\Basic\Control\ExtensionRegister;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use phpManufaktur\Basic\Data\CMS\SearchSection;
use phpManufaktur\Basic\Control\CMS\InstallSearch;
use phpManufaktur\Basic\Control\Pattern\Alert;
use phpManufaktur\Basic\Data\Setting;

/**
 * Display a welcome to the kitFramework dialog
 *
 * @author Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 *
 */
class cmsTool extends Alert
{

    protected static $usage = null;

    /**
     * (non-PHPdoc)
     * @see \phpManufaktur\Basic\Control\Pattern\Alert::initialize()
     */
    protected function initialize(Application $app)
    {
        parent::initialize($app);

        self::$usage = $this->app['request']->get('usage', 'framework');

        if (self::$usage != 'framework') {
            // set the locale from the CMS locale
            $app['translator']->setLocale($app['session']->get('CMS_LOCALE', 'de'));
        }
    }

    /**
     * Get the toolbar for the Welcome dialog
     *
     * @param string $active tab
     * @return array
     */
    protected function getToolbar($active)
    {
        $toolbar = array();
        $tabs = array('entrypoints', 'extensions', 'update', 'about');

        foreach ($tabs as $tab) {
            switch ($tab) {
                case 'about':
                    $toolbar[$tab] = array(
                        'name' => 'about',
                        'text' => $this->app['translator']->trans('About'),
                        'hint' => $this->app['translator']->trans('Information about the kitFramework'),
                        'link' => FRAMEWORK_URL.'/admin/welcome/about',
                        'active' => ($active === $tab)
                    );
                    break;
                case 'entrypoints':
                    $toolbar[$tab] = array(
                        'name' => 'entrypoints',
                        'text' => $this->app['translator']->trans('Entry points'),
                        'hint' => $this->app['translator']->trans('Use the entry points for an easy access'),
                        'link' => FRAMEWORK_URL.'/admin/welcome',
                        'active' => ($active === $tab)
                    );
                    break;
                case 'extensions':
                    $toolbar[$tab] = array(
                        'name' => 'extensions',
                        'text' => $this->app['translator']->trans('Extensions'),
                        'hint' => $this->app['translator']->trans('Install, update or remove kitFramework extensions'),
                        'link' => FRAMEWORK_URL.'/admin/welcome/extensions',
                        'active' => ($active === $tab)
                    );
                    break;
            }
        }
        return $toolbar;
    }

    /**
     * Get the second toolbar for the Extensions
     *
     * @param string $active
     * @return array
     */
    protected function getToolbarExtensions($active)
    {
        $toolbar = array();
        $tabs = array('update', 'catalog', 'installed');

        foreach ($tabs as $tab) {
            switch ($tab) {
                case 'update':
                    $toolbar[$tab] = array(
                        'name' => 'update',
                        'text' => $this->app['translator']->trans('Update'),
                        'hint' => $this->app['translator']->trans('Available updates for your extensions'),
                        'link' => FRAMEWORK_URL.'/admin/welcome/extensions',
                        'active' => ($active === $tab)
                    );
                    break;
                case 'installed':
                    $toolbar[$tab] = array(
                        'name' => 'installed',
                        'text' => $this->app['translator']->trans('Installed'),
                        'hint' => $this->app['translator']->trans('Currently installed extensions'),
                        'link' => FRAMEWORK_URL.'/admin/welcome/extensions/installed',
                        'active' => ($active === $tab)
                    );
                    break;
                case 'catalog':
                    $toolbar[$tab] = array(
                        'name' => 'catalog',
                        'text' => $this->app['translator']->trans('Catalog'),
                        'hint' => $this->app['translator']->trans('Explore the catalog for kitFramework extensions'),
                        'link' => FRAMEWORK_URL.'/admin/welcome/extensions/catalog',
                        'active' => ($active === $tab)
                    );
                    break;

            }
        }
        return $toolbar;
    }

    /**
     * Get the dialog for the entry points
     *
     * return string
     */
    protected function getEntryPointsDialog()
    {
        // get all entry points for this user
        $entry_points = $this->app['account']->getUserRolesEntryPoints();

        foreach ($entry_points['ROLE_ADMIN'] as $key => $entry) {
            // we dont want an access to the extensions here, so we remove it!
            if ($entry['route'] === '/admin/welcome') {
                unset($entry_points['ROLE_ADMIN'][$key]);
            }
        }

        $count = count($entry_points, COUNT_RECURSIVE);
        if ($count < 1) {
            $this->setAlert('Sorry, but you are not allowed to access any entry point!', array(), self::ALERT_TYPE_WARNING);
            $entry_points = null;
        }

        return $this->app['twig']->render($this->app['utils']->getTemplateFile(
            '@phpManufaktur/Basic/Template',
            'framework/tool/entry.points.twig'),
            array(
                'usage' => self::$usage,
                'alert' => $this->getAlert(),
                'toolbar' => $this->getToolbar('entrypoints'),
                'entry_points' => $entry_points
        ));
    }



    /**
     * Initialize the Welcome dialog
     *
     * @param Application $app
     */
    protected function initWelcome(Application $app)
    {
        $this->initialize($app);

        // grant that the updater is installed in the separated directory and is actual
        if (!file_exists(MANUFAKTUR_PATH.'/Updater')) {
            $app['filesystem']->mkdir(MANUFAKTUR_PATH.'/Updater');
        }
        $app['filesystem']->copy(MANUFAKTUR_PATH.'/Basic/Control/Updater/Updater.php', MANUFAKTUR_PATH.'/Updater/Updater.php', true);


        // check if the search addon is installed
        if (!file_exists(CMS_PATH.'/modules/kit_framework_search')) {
            $InstallSearch = new InstallSearch($app);
            $InstallSearch->exec();
        }

        // check if the search section in the CMS exists
        if (file_exists(CMS_PATH.'/modules/kit_framework_search')) {
            $SearchSection = new SearchSection();
            $SearchSection->addSearchSection($app);
        }

        // set the locale from the CMS locale
        $app['translator']->setLocale($app['session']->get('CMS_LOCALE', 'en'));
    }

    /**
     * Execute the welcome dialog. This is the main procedure, this dialog will
     * be also executed from inside the CMS after automatic authentication with
     * the controllerCMS()
     *
     */
    public function ControllerFramework(Application $app)
    {
        $this->initWelcome($app);

        if (null !== ($install = $app['session']->get('FINISH_INSTALLATION', null))) {
            // get the messages from the installation
            $this->setAlertUnformatted($install['message']);
            foreach ($install['execute_route'] as $route) {
                try {
                    // execute the install & update routes
                    $subRequest = Request::create($route, 'GET', array('usage' => self::$usage));
                    // important: we dont want that app->handle() catch errors, so set the third parameter to false!
                    $response = $app->handle($subRequest, HttpKernelInterface::SUB_REQUEST, false);
                    $this->setAlert($response->getContent(), array(), self::ALERT_TYPE_INFO);
                } catch (\Exception $e) {
                    // it is possible that a route really not exists just in this moment, so we log but dont prompt the error
                    $this->setAlert($e->getMessage(), array(), self::ALERT_TYPE_DANGER);
                    //$app['monolog']->addDebug($e->getMessage(), array($e));
                }
            }
            // remove the session
            $app['session']->remove('FINISH_INSTALLATION');
            // now scan for the installed extensions
            $register = new ExtensionRegister($app);
            $register->scanDirectories(ExtensionRegister::GROUP_PHPMANUFAKTUR);
            $register->scanDirectories(ExtensionRegister::GROUP_THIRDPARTY);
        }

        $catalog = new ExtensionCatalog($app);
        $catalog_release = null;
        $available_release = null;
        if ($catalog->isCatalogUpdateAvailable($catalog_release, $available_release)) {
            $this->setAlert('There are new catalog information available, <strong><a href="%route%">please update the catalog</a></strong>.',
                array('%route%' => FRAMEWORK_URL.'/admin/scan/catalog?usage='.self::$usage), self::ALERT_TYPE_INFO);
        }

        $register = new ExtensionRegister($this->app);
        $updates = $register->getAvailableUpdates();
        if (!empty($updates)) {
            $this->setAlert('There are updates available, <strong><a href="%route%">please check out your installed extensions</a></strong>!',
                array('%route%' => FRAMEWORK_URL.'/admin/welcome/extensions?usage='.self::$usage), self::ALERT_TYPE_INFO);
        }

        return $this->getEntryPointsDialog();
    }

    /**
     * Prepare the execution of the welcome dialog
     *
     * @param Application $app
     * @param string $cms
     */
    public function ControllerCMS(Application $app, $cms)
    {
        // get the CMS info parameters
        $cms_string = $cms;
        $cms = json_decode(base64_decode($cms), true);

        $app['request']->request->set('usage', ($cms['target'] == 'cms') ? $cms['type'] : 'framework');
        $this->initWelcome($app);

        if (!$app['account']->checkUserIsCMSAdministrator($cms['username'])) {
            // the user is no CMS Administrator, deny access!
            $this->setAlert('Sorry, but only Administrators are allowed to access this kitFramework extension.',
                array(), self::ALERT_TYPE_WARNING);
            return $this->app['twig']->render($this->app['utils']->getTemplateFile(
                '@phpManufaktur/Basic/Template', 'framework/alert.twig'),
                array(
                    'usage' => self::$usage,
                    'title' => 'Access denied',
                    'alert' => $this->getAlert()
                ));
        }

        // save them partial into session
        $app['session']->set('CMS_TYPE', $cms['type']);
        $app['session']->set('CMS_VERSION', $cms['version']);
        $app['session']->set('CMS_LOCALE', $cms['locale']);
        $app['session']->set('CMS_USER_NAME', $cms['username']);

        if (!$app['account']->checkUserHasFrameworkAccount($cms['username'])) {
            // this user does not exists in the kitFramework User database
            $subRequest = Request::create('/login/first/cms', 'POST', array(
                'usage' => self::$usage,
                'username' => $cms['username'],
                'roles' => array('ROLE_ADMIN'),
                'auto_login' => true,
                'secured_area' => 'general',
                'redirect' => "/welcome/cms/$cms_string"
            ));
            return $app->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
        }

        // auto login the CMS user into the secured area with admin privileges
        $app['account']->loginUserToSecureArea($cms['username'], array('ROLE_ADMIN'));

        // sub request to the welcome dialog
        $subRequest = Request::create('/admin/welcome', 'GET', array('usage' => self::$usage));
        return $app->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
    }

    /**
     * Controller to show the about dialog
     *
     * @param Application $app
     */
    public function ControllerAbout(Application $app)
    {
        $this->initialize($app);

        $libraries = array(
            'Silex' => null,
            'Symfony' => null,
            'Monolog' => null,
            'Doctrine' => null,
            'Twig' => null,
            'SwiftMailer' => null
        );

        // get the /kit2/framework/composer/installed.json
        $installed = $app['utils']->readJSON(FRAMEWORK_PATH.'/framework/composer/installed.json');
        foreach ($installed as $item) {
            switch ($item['name']) {
                case 'symfony/debug':
                    $libraries['Symfony'] = trim($item['version'], 'v');
                    break;
                case 'silex/silex':
                    $libraries['Silex'] = trim($item['version'], 'v');
                    break;
                case 'monolog/monolog':
                    $libraries['Monolog'] = trim($item['version'], 'v');
                    break;
                case 'doctrine/dbal':
                    $libraries['Doctrine'] = trim($item['version'], 'v');
                    break;
                case 'twig/twig':
                    $libraries['Twig'] = trim($item['version'], 'v');
                    break;
                case 'swiftmailer/swiftmailer':
                    $libraries['SwiftMailer'] = trim($item['version'], 'v');
                    break;
            }
        }

        // get the kitFramework info
        $kitframework = $app['utils']->readJSON(FRAMEWORK_PATH.'/framework.json');
        $framework_config = $app['utils']->readJSON(FRAMEWORK_PATH.'/config/framework.json');

        return $this->app['twig']->render($this->app['utils']->getTemplateFile(
            '@phpManufaktur/Basic/Template',
            'framework/tool/about.twig'),
            array(
                'usage' => self::$usage,
                'alert' => $this->getAlert(),
                'toolbar' => $this->getToolbar('about'),
                'libraries' => $libraries,
                'kitframework' => $kitframework,
                'framework_config' => $framework_config
            ));
    }

    /**
     * Controller to show the extension dialog
     *
     * @param Application $app
     */
    public function ControllerExtensions(Application $app)
    {
        $this->initialize($app);

        $catalog = new ExtensionCatalog($app);
        $catalog_release = null;
        $available_release = null;
        $catalog_update = false;
        if ($catalog->isCatalogUpdateAvailable($catalog_release, $available_release)) {
            $this->setAlert('There are new catalog information available, <strong><a href="%route%">please update the catalog</a></strong>.',
                array('%route%' => FRAMEWORK_URL.'/admin/scan/catalog?usage='.self::$usage), self::ALERT_TYPE_INFO);
            $catalog_update = true;
        }

        $register = new ExtensionRegister($this->app);
        $updates = $register->getAvailableUpdates();
        if (!empty($updates)) {
            $this->setAlert('Please execute the available updates.', array(), self::ALERT_TYPE_INFO);
        }

        $Setting = new Setting($app);
        if (!$catalog_update && empty($updates) && $Setting->exists('framework_update')) {
            if (null !== $app['request']->get('update')) {
                // the framework seems to be installed
                if (null !== ($alert = $app['request']->get('alert'))) {
                    $this->setAlert(base64_decode($alert), array(), $app['request']->get('type', 'alert-info'));
                }
                else {
                    $this->setAlert('The kitFramework has successfull updated. Because this update has changed elementary functions and methods of the kitFramework core you should check the behaviour of all kitFramework applications in backend and frontend of your website within the next days. There exists a copy of your previous kitFramework core files, so it is possible to roll back if needed.',
                        array(), self::ALERT_TYPE_INFO);
                }
                // delete all framework settings
                $Setting->deleteByName('framework_update');
                $Setting->deleteByName('framework_ready');
            }
            elseif ($Setting->exists('framework_ready')) {
                // execute the copy process ...
                $this->setAlert('The kitFramework update is prepared, now you can <a href="%url%">remove the existing one and install the new kitFramework release</a>.',
                    array('%url%' => CMS_URL.'/modules/kit_framework/Update/kitFramework/Update.php?'.http_build_query(array(
                        'usage' => self::$usage,
                        'locale' => $app['translator']->getLocale(),
                        'cms_url' => CMS_URL,
                        'cms_path' => CMS_PATH
                    ))),
                    self::ALERT_TYPE_INFO);
            }
            else {
                $this->setAlert('Download and prepare the <a href="%url%">kitFramework update</a>',
                    array('%url%' => FRAMEWORK_URL.'/admin/updater/download/framework?usage='.self::$usage), self::ALERT_TYPE_INFO);
            }
        }
        elseif ($app['filesystem']->exists(FRAMEWORK_PATH.'/framework.bak')) {
            $this->setAlert('There exists a kitFramework restore directory, if your system is working fine you can <a href="%route%">remove this directory</a>.',
                array('%route%' => FRAMEWORK_URL.'/admin/updater/remove/framework/restore?usage='.self::$usage));
        }

        return $this->app['twig']->render($this->app['utils']->getTemplateFile(
            '@phpManufaktur/Basic/Template',
            'framework/tool/extensions.twig'),
            array(
                'usage' => self::$usage,
                'alert' => $this->getAlert(),
                'toolbar' => $this->getToolbar('extensions'),
                'toolbar_extensions' => $this->getToolbarExtensions('update'),
                'update_items' => $register->getAvailableUpdates()
        ));
    }

    /**
     * Controller to show the installed extensions
     *
     * @param Application $app
     */
    public function ControllerExtensionsInstalled(Application $app)
    {
        $this->initialize($app);

        $catalog = new ExtensionCatalog($app);
        $catalog_release = null;
        $available_release = null;
        if ($catalog->isCatalogUpdateAvailable($catalog_release, $available_release)) {
            $this->setAlert('There are new catalog information available, <strong><a href="%route%">please update the catalog</a></strong>.',
                array('%route%' => FRAMEWORK_URL.'/admin/scan/catalog?usage='.self::$usage), self::ALERT_TYPE_INFO);
        }

        $register = new ExtensionRegister($this->app);
        $updates = $register->getAvailableUpdates();
        if (!empty($updates)) {
            $this->setAlert('Please execute the available updates.', array(), self::ALERT_TYPE_INFO);
        }

        $register_items = $register->getInstalledExtensions();

        if (empty($register_items)) {
            // seems that we should scan first for installed extensions
            $register->scanDirectories(ExtensionRegister::GROUP_PHPMANUFAKTUR);
            $register->scanDirectories(ExtensionRegister::GROUP_THIRDPARTY);
            $register_items = $register->getInstalledExtensions();
        }

        return $this->app['twig']->render($this->app['utils']->getTemplateFile(
            '@phpManufaktur/Basic/Template',
            'framework/tool/installed.extensions.twig'),
            array(
                'usage' => self::$usage,
                'alert' => $this->getAlert(),
                'toolbar' => $this->getToolbar('extensions'),
                'toolbar_extensions' => $this->getToolbarExtensions('installed'),
                'register_items' => $register_items
        ));
    }

    /**
     * Controller to show the extension catalog
     *
     * @param Application $app
     */
    public function ControllerExtensionsCatalog(Application $app)
    {
        $this->initialize($app);

        $catalog = new ExtensionCatalog($app);
        $accepted_items = explode(',', CATALOG_ACCEPT_EXTENSION);
        $cat_items = $catalog->getAvailableExtensions($app['translator']->getLocale());

        $catalog_items = array();
        foreach ($cat_items as $item) {
            // show only catalog items which have the accepted release status
            if (isset($item['release_status']) && in_array($item['release_status'], $accepted_items)) {
                $catalog_items[] = $item;
            }
        }

        $register = new ExtensionRegister($this->app);
        $updates = $register->getAvailableUpdates();
        if (!empty($updates)) {
            $this->setAlert('There are updates available, <strong><a href="%route%">please check out your installed extensions</a></strong>!',
                array('%route%' => FRAMEWORK_URL.'/admin/welcome/extensions?usage='.self::$usage), self::ALERT_TYPE_INFO);
        }


        return $this->app['twig']->render($this->app['utils']->getTemplateFile(
            '@phpManufaktur/Basic/Template',
            'framework/tool/catalog.extensions.twig'),
            array(
                'usage' => self::$usage,
                'alert' => $this->getAlert(),
                'toolbar' => $this->getToolbar('extensions'),
                'toolbar_extensions' => $this->getToolbarExtensions('catalog'),
                'catalog_items' => $catalog_items
        ));
    }
}
