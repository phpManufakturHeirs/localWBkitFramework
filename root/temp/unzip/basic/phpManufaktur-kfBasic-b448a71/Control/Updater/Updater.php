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
use phpManufaktur\Basic\Control\Welcome;
use phpManufaktur\Basic\Data\ExtensionCatalog;
use phpManufaktur\Basic\Data\ExtensionRegister;


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
class Updater
{
    protected $app = null;
    protected static $message;
    protected $ExtensionCatalog = null;
    protected $ExtensionRegister = null;
    protected $Github = null;
    protected $cURL = null;
    protected $Welcome = null;
    protected $unZIP = null;
    protected static $extension_array = array();
    protected static $usage = null;

    public function __construct(Application $app=null)
    {
        if (!is_null($app)) {
            $this->initUpdater($app);
        }
    }

    /**
     * Initialize the update
     *
     * @param Application $app
     */
    protected function initUpdater(Application $app)
    {
        $this->app = $app;
        $this->ExtensionCatalog = new ExtensionCatalog($app);
        $this->ExtensionRegister = new ExtensionRegister($app);
        $this->Github = new gitHub($app);
        $this->cURL = new cURL($app);
        $this->unZIP = new unZip($app);
        $this->Welcome = new Welcome($app);

        self::$usage = $this->app['request']->get('usage', 'framework');
    }

    /**
     * @return the $message
     */
    public function getMessage()
    {
        return self::$message;
    }

    /**
     * Set a message. Messages are chained and will be translated with the given
     * parameters. If $log_message = true, the message will also logged to the
     * kitFramework logfile.
     *
     * @param string $message
     * @param array $params
     * @param boolean $log_message
     */
    public function setMessage($message, $params=array(), $log_message=false)
    {
        self::$message .= $this->app['twig']->render($this->app['utils']->getTemplateFile(
            '@phpManufaktur/Basic/Template',
            'framework/message.twig'),
            array(
                'message' => $this->app['translator']->trans($message, $params)
            ));
        if ($log_message) {
            // log this message
            $this->app['monolog']->addDebug(strip_tags($this->app['translator']->trans($message, $params, 'messages', 'en')));
        }
    }

    /**
     * Check if a message is active
     *
     * @return boolean
     */
    public function isMessage()
    {
        return !empty(self::$message);
    }

    /**
     * Clear the existing message(s)
     */
    public function clearMessage()
    {
        self::$message = '';
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
                    if (false === ($installed = $this->ExtensionRegister->selectByGroupAndName($group, $extension))) {
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
    public function controllerInstallExtension(Application $app, $catalog_id)
    {
        $this->initUpdater($app);

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
                // Ooops, problem copying the repo into directory
                $this->Welcome->setMessage($this->getMessage());
                return $this->Welcome->controllerFramework($this->app);
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
                $this->setMessage("Successfull $install_mode the extension %extension%.",
                    array('%extension%' => $info['name']));
            }
        }

        $app['session']->set('FINISH_INSTALLATION', array(
            'message' => $this->getMessage(),
            'execute_route' => $execute_route
        ));
        // use redirect to enable a application reload and autoload of the new extensions
        return $app->redirect(FRAMEWORK_URL.'/admin/welcome?usage='.self::$usage);
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
            $this->setMessage("Can't read the the %repository% from %organization% at Github!",
                array('%repository%' => $repository, '%organization%' => $organization), true);
            return false;
        }

        $target_path = FRAMEWORK_TEMP_PATH.'/repository.zip';
        $this->cURL->DownloadRedirectedURL($tag_url, $target_path);

        // repository.zip is in temp directory
        if (!file_exists($target_path)) {
            $this->setMessage("Can't open the file <b>%file%</b>!",
                array('%file%' => substr($target_path, strlen(FRAMEWORK_PATH))), true);
            return false;
        }

        $this->unZIP->setUnZipPath(FRAMEWORK_TEMP_PATH.'/repository');
        $this->unZIP->checkDirectory($this->unZIP->getUnZipPath());
        $this->unZIP->extract($target_path);
        $files = $this->unZIP->getFileList();
        if (null === ($subdirectory = $this->getFirstSubdirectory($this->unZIP->getUnZipPath()))) {
            $this->setMessage('The received repository has an unexpected directory structure!', array(), true);
            return false;
        }
        $source_directory = $this->unZIP->getUnZipPath().'/'.$subdirectory;
        $extension = $this->app['utils']->readConfiguration($source_directory.'/extension.json');
        if (!isset($extension['path'])) {
            $this->setMessage('The received extension.json does not specifiy the path of the extension!', array(), true);
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
        $this->initUpdater($app);

        if (false === ($extension = $this->ExtensionRegister->select($extension_id))) {
            $this->Welcome->setMessage('The extension with the ID %extension_id% does not exists!',
                array('%extension_id%' => $extension_id), true);
            return $this->Welcome->controllerFramework($app);
        }

        if (false === ($catalog_id = $this->ExtensionCatalog->selectIDbyGUID($extension['guid']))) {
            $this->Welcome->setMessage('There exists no catalog entry for the extension %name% with the GUID %guid%.',
                array('%name%' => $extension['name'], '%guid%' => $extension['guid']), true);
            return $this->Welcome->controllerFramework($app);
        }
        // ok - we have the catalog number and can execute the update/installation
        return $this->controllerInstallExtension($app, $catalog_id);
    }
}

