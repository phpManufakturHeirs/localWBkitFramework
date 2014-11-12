<?php

/**
 * kitFramework::Basic
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de
 * @copyright 2013 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 *
 */

namespace phpManufaktur\Updater;

use Silex\Application;
use phpManufaktur\Basic\Control\gitHub\gitHub;
use phpManufaktur\Basic\Control\cURL\cURL;
use phpManufaktur\Basic\Control\unZip\unZip;
//use phpManufaktur\Basic\Control\cmsTool;
use phpManufaktur\Basic\Data\ExtensionCatalog;
use phpManufaktur\Basic\Data\ExtensionRegister as DataExtensionRegister;
use phpManufaktur\Basic\Control\Pattern\Alert;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use phpManufaktur\Basic\Control\ExtensionRegister;
use phpManufaktur\Basic\Data\Setting;


/**
 * Updater Class for the kitFramework
 *
 * IMPORTANT
 * This class will never executed within phpManufaktur/Basic/Control/Updater,
 * it will ever placed (copied) at phpManufaktur/Updater to prevent conflicts
 * while updating phpManufaktur/Basic itself.
 *
 * @author Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 *
 */
class Updater extends Alert
{
    protected $ExtensionCatalog = null;
    protected $ExtensionRegister = null;
    protected $Github = null;
    protected $cURL = null;
    protected $unZIP = null;
    protected static $extension_array = array();
    protected static $usage = null;

    protected function initialize(Application $app)
    {
        parent::initialize($app);

        $this->ExtensionCatalog = new ExtensionCatalog($app);
        $this->DataExtensionRegister = new DataExtensionRegister($app);
        $this->Github = new gitHub($app);
        $this->cURL = new cURL($app);
        $this->unZIP = new unZip($app);

        if (false === FRAMEWORK_SETUP) {
            self::$usage = $this->app['request']->get('usage', 'framework');

            if (self::$usage != 'framework') {
                // set the locale from the CMS locale
                $app['translator']->setLocale($app['session']->get('CMS_LOCALE', 'de'));
            }
        }
    }

    /**
     * Search for the first subdirectory below the given path
     *
     * @param string $path
     * @return string|NULL subdirectory, null if search fail
     */
    protected function getFirstSubdirectory($path)
    {
        $handle = opendir($path);
        // we loop through the directory to get the first subdirectory ...
        while (false !== ($directory = readdir($handle))) {
            if ('.' == $directory || '..' == $directory)
                continue;
            if (is_dir($path .'/'. $directory)) {
                // ... here we got it!
                return $directory;
            }
        }
        return null;
    }

    /**
     * Check the dependencies for the given catalog ID, add needed extensions to
     * self::$extension_array in the needed order
     *
     * @param integer $catalog_id
     * @throws \Exception
     */
    protected function checkDependencies($catalog_id)
    {
        if (false === ($catalog = $this->ExtensionCatalog->select($catalog_id))) {
            throw new \Exception("The catalog ID $catalog_id does not exists!");
        }
        $info = json_decode(base64_decode($catalog['info']), true);

        if (isset($info['require']['extension'])) {
            foreach ($info['require']['extension'] as $group => $items) {
                foreach ($items as $extension => $check) {
                    $must_install = false;
                    if (false === ($installed = $this->DataExtensionRegister->selectByGroupAndName($group, $extension))) {
                        // this extension is not installed
                        $must_install = true;
                    }
                    elseif (version_compare($installed['release'], $check['release'], '<')) {
                        // extension is installed but a newer release is needed
                        $must_install = true;
                    }
                    if ($must_install) {
                        if (false === ($info = $this->ExtensionCatalog->selectByGroupAndName($group, $extension))) {
                            throw new \Exception("The catalog entry for group $group and name $extension does not exists!");
                        }
                        if (in_array($info['id'], self::$extension_array)) {
                            // this extension is already in the array
                            continue;
                        }
                        // need a recursive check for this extension!
                        $this->checkDependencies($info['id']);
                        // ok nwo we can add the extension to the array
                        self::$extension_array[] = $info['id'];
                        $this->app['monolog']->addDebug("Add the extension $group\\$extension is tagged as needed.");
                    }
                }
            }
        }
    }

    /**
     * Controller to execute the installation or update of a extension
     *
     * @param Application $app
     * @param integer $catalog_id
     * @throws \Exception
     */
    public function controllerInstallExtension(Application $app, $catalog_id, $redirect=true)
    {
        $this->initialize($app);

        // check the dependencies for this extension
        $this->checkDependencies($catalog_id);

        // don't forgot to add the initial extension ...
        self::$extension_array[] = $catalog_id;

        $execute_route = array();

        foreach (self::$extension_array as $cat_id) {
            if (false === ($catalog = $this->ExtensionCatalog->select($cat_id))) {
                throw new \Exception("The catalog ID $cat_id does not exists!");
            }
            $info = json_decode(base64_decode($catalog['info']), true);
            $install_mode = (file_exists(FRAMEWORK_PATH.$info['path'])) ? 'updated' : 'installed';

            $app['monolog']->addDebug("Start installation/update of {$info['download']['github']['organization']}//{$info['download']['github']['repository']}");

            if (!$this->copyLastGithubRepository($info['download']['github']['organization'], $info['download']['github']['repository'])) {
                // Ooops, problem copying the repo into directory - return to the cmsTool
                $subRequest = Request::create('/admin/welcome/extensions', 'GET', array('usage' => self::$usage));
                return $app->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
            }
            $app['monolog']->addDebug("All files are copied to {$info['path']}");

            if (($install_mode == 'installed') && isset($info['setup']['install'])) {
                $app['monolog']->addDebug("Prepare route {$info['setup']['install']} to finish installation.");
                $execute_route[] = $info['setup']['install'];
            }
            elseif (($install_mode == 'updated') && isset($info['setup']['update'])) {
                $app['monolog']->addDebug("Prepare route {$info['setup']['update']} to finish update.");
                $execute_route[] = $info['setup']['update'];
            }
            else {
                $mode = $this->app['translator']->trans($install_mode);
                $this->setAlert('Successfull %mode% the extension %extension%.',
                    array('%extension%' => $info['name'], '%mode%' => $mode), self::ALERT_TYPE_SUCCESS);
            }
        }

        $app['session']->set('FINISH_INSTALLATION', array(
            'message' => $this->getAlert(),
            'execute_route' => $execute_route
        ));

        // at this point we clear the Twig Cache to avoid problems
        $app['twig']->clearCacheFiles();
        $app['monolog']->addDebug('Finish the installation and update process: clear the Twig Cache Files');

        // cleanup Register
        $Extension = new ExtensionRegister($app);
        $Extension->scanDirectories(ExtensionRegister::GROUP_PHPMANUFAKTUR);
        $Extension->scanDirectories(ExtensionRegister::GROUP_THIRDPARTY);

        if ($redirect) {
            // use redirect to enable a application reload and autoload of the new extensions
            return $app->redirect(FRAMEWORK_URL.'/admin/welcome/extensions?usage='.self::$usage);
        }
        else {
            return true;
        }
    }

    public function ControllerDownloadFramework(Application $app)
    {
        $this->initialize($app);

        $release = null;

        if (false === ($tag_url = $this->Github->getLastRepositoryZipUrl('phpManufaktur', 'kitFramework', $release))) {
            $this->setAlert("Can't read the the %repository% from %organization% at Github!",
                array('%repository%' => 'kitFramework', '%organization%' => 'phpManufaktur'), self::ALERT_TYPE_WARNING);
            $subRequest = Request::create('/admin/welcome/extensions', 'GET', array('usage' => self::$usage));
            return $app->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
        }

        $target_path = FRAMEWORK_TEMP_PATH.'/framework.zip';
        $this->cURL->DownloadRedirectedURL($tag_url, $target_path);

        if (!$app['filesystem']->exists($target_path)) {
            $this->setAlert("Can't open the file <b>%file%</b>!",
                array('%file%' => substr($target_path, strlen(FRAMEWORK_PATH))), self::ALERT_TYPE_WARNING);
            $subRequest = Request::create('/admin/welcome/extensions', 'GET', array('usage' => self::$usage));
            return $app->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
        }

        if ($app['filesystem']->exists(FRAMEWORK_TEMP_PATH.'/framework')) {
            $this->app['filesystem']->remove(FRAMEWORK_TEMP_PATH.'/framework');
        }
        $this->unZIP->setUnZipPath(FRAMEWORK_TEMP_PATH.'/framework');
        $this->unZIP->checkDirectory($this->unZIP->getUnZipPath());
        $this->unZIP->extract($target_path);

        $Setting = new Setting($app);
        if ($Setting->exists('framework_ready')) {
            $Setting->update('framework_ready', $release);
        }
        else {
            $Setting->insert('framework_ready', $release);
        }

        $subRequest = Request::create('/admin/welcome/extensions', 'GET', array('usage' => self::$usage));
        return $app->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
    }

    /**
     * Retrieve the the last repository for the extension from Github, unpack
     * and copy it to the target directory. Does not execute the installation!
     * Return false on problems and set messages for information
     *
     * @param string $organization
     * @param string $repository
     * @return boolean
     */
    protected function copyLastGithubRepository($organization, $repository)
    {
        $release = null;
        if (false === ($tag_url = $this->Github->getLastRepositoryZipUrl($organization, $repository, $release))) {
            $this->setAlert("Can't read the the %repository% from %organization% at Github!",
                array('%repository%' => $repository, '%organization%' => $organization), self::ALERT_TYPE_WARNING);
            return false;
        }

        $target_path = FRAMEWORK_TEMP_PATH.'/repository.zip';
        $this->cURL->DownloadRedirectedURL($tag_url, $target_path);

        // repository.zip is in temp directory
        if (!file_exists($target_path)) {
            $this->setAlert("Can't open the file <b>%file%</b>!",
                array('%file%' => substr($target_path, strlen(FRAMEWORK_PATH))), self::ALERT_TYPE_WARNING);
            return false;
        }

        $this->unZIP->setUnZipPath(FRAMEWORK_TEMP_PATH.'/repository');
        $this->unZIP->checkDirectory($this->unZIP->getUnZipPath());
        $this->unZIP->extract($target_path);
        $files = $this->unZIP->getFileList();
        if (null === ($subdirectory = $this->getFirstSubdirectory($this->unZIP->getUnZipPath()))) {
            $this->setAlert('The received repository has an unexpected directory structure!', array(), self::ALERT_TYPE_WARNING);
            return false;
        }
        $source_directory = $this->unZIP->getUnZipPath().'/'.$subdirectory;
        $extension = $this->app['utils']->readConfiguration($source_directory.'/extension.json');
        if (!isset($extension['path'])) {
            $this->setAlert('The received extension.json does not specifiy the path of the extension!', array(), self::ALERT_TYPE_WARNING);
            return false;
        }
        $target_directory = FRAMEWORK_PATH.$extension['path'];

        if (!file_exists($target_directory)) {
            $this->app['filesystem']->mkdir($target_directory);
        }

        // copy the files to the target directory
        $this->app['utils']->xcopy($source_directory, $target_directory);

        // extension is copied to the target directory
        return true;
    }

    /**
     * Controller to prepare the update of an extension
     *
     * @param Application $app
     * @param integer $extension_id
     */
    public function controllerUpdateExtension(Application $app, $extension_id)
    {
        $this->initialize($app);

        if (false === ($extension = $this->DataExtensionRegister->select($extension_id))) {
            $this->setAlert('The extension with the ID %extension_id% does not exists!',
                array('%extension_id%' => $extension_id), self::ALERT_TYPE_WARNING);
            $subRequest = Request::create('/admin/welcome/extensions', 'GET', array('usage' => self::$usage));
            return $app->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
        }

        if (false === ($catalog_id = $this->ExtensionCatalog->selectIDbyGUID($extension['guid']))) {
            $this->setAlert('There exists no catalog entry for the extension %name% with the GUID %guid%.',
                array('%name%' => $extension['name'], '%guid%' => $extension['guid']), self::ALERT_TYPE_WARNING);
            $subRequest = Request::create('/admin/welcome/extensions', 'GET', array('usage' => self::$usage));
            return $app->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
        }
        // ok - we have the catalog number and can execute the update/installation
        return $this->controllerInstallExtension($app, $catalog_id);
    }

    /**
     * Controller to remove an extension
     *
     * @param Application $app
     * @param integer $extension_id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function controllerRemoveExtension(Application $app, $extension_id)
    {
        $this->initialize($app);

        if (false === ($extension = $this->DataExtensionRegister->select($extension_id))) {
            $this->setAlert('The extension with the ID %extension_id% does not exists!',
                array('%extension_id%' => $extension_id), self::ALERT_TYPE_WARNING);
            // sub request to the extensions dialog
            $subRequest = Request::create('/admin/welcome/extensions', 'GET', array('usage' => self::$usage));
            return $app->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
        }

        // extract the extension.json
        $info = json_decode(base64_decode($extension['info']), true);

        if ($app['filesystem']->exists(FRAMEWORK_PATH.$info['path'])) {
            // the extension exists and can be removed
            if (isset($info['setup']['uninstall'])) {
                // execute the uninstall route
                $subRequest = Request::create($info['setup']['uninstall'], 'GET');
                $app->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
            }
            // now remove the extension directory
            $app['filesystem']->remove(FRAMEWORK_PATH.$info['path']);
            // delete from the extension table
            $this->DataExtensionRegister->delete($extension_id);
            // alert success
            $this->setAlert('The extension %extension% was successful removed.',
                array('%extension%' => $info['name']), self::ALERT_TYPE_SUCCESS);
        }
        else {
            // Oooops ...
            $this->setAlert('The extension %extension% does not exists.',
                array('%extension%' => $info['name']), self::ALERT_TYPE_WARNING);
        }

        // cleanup Register
        $Extension = new ExtensionRegister($app);
        $Extension->scanDirectories(ExtensionRegister::GROUP_PHPMANUFAKTUR);
        $Extension->scanDirectories(ExtensionRegister::GROUP_THIRDPARTY);

        // sub request to the extension dialog
        $subRequest = Request::create('/admin/welcome/extensions', 'GET', array('usage' => self::$usage));
        return $app->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
    }

    /**
     * Controller to remove an existing kitFramework restore directory
     *
     * @param Application $app
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function ControllerRemoveFrameworkRestore(Application $app)
    {
        $this->initialize($app);

        if ($app['filesystem']->exists(FRAMEWORK_PATH.'/framework.bak')) {
            $app['filesystem']->remove(FRAMEWORK_PATH.'/framework.bak');
            $this->setAlert('The kitFramework restore directory was successful removed', array(), self::ALERT_TYPE_SUCCESS);
        }
        else {
            $this->setAlert('There exists no kitFramework restore directory!', array(), self::ALERT_TYPE_INFO);
        }

        // sub request to the extension dialog
        $subRequest = Request::create('/admin/welcome/extensions', 'GET', array('usage' => self::$usage));
        return $app->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
    }
}

