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
use phpManufaktur\Basic\Control\gitHub\gitHub;
use phpManufaktur\Basic\Control\cURL\cURL;
use phpManufaktur\Basic\Control\unZip\unZip;
use phpManufaktur\Basic\Data\ExtensionCatalog as Catalog;
use phpManufaktur\Basic\Data\Setting;
use phpManufaktur\Basic\Data\ExtensionRegister as Register;

/**
 * Get the catalog with all for the kitFramework available extensions from GitHub
 *
 * @author Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 *
 */
class ExtensionCatalog
{

    protected $app = null;
    protected static $message = '';

    /**
     * Constructor
     *
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * @return the $message
     */
    public function getMessage ()
    {
        return self::$message;
    }

    public function setMessage($message, $params=array())
    {
        self::$message .= $this->app['twig']->render($this->app['utils']->getTemplateFile(
            '@phpManufaktur/Basic/Template',
            'framework/message.twig'),
        array(
            'message' => $this->app['translator']->trans($message, $params)
        ));
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
     * Get the online catalog from Github and read it into the database
     *
     * @throws \Exception
     * @return boolean
     */
    public function getOnlineCatalog()
    {
        // init GitHub
        $github = new gitHub($this->app);
        $release = null;
        if (false === ($catalog_url = $github->getLastRepositoryZipUrl('phpManufaktur', 'kitFramework_Catalog', $release))) {
            throw new \Exception($this->app['translator']->trans("Can't read the the %repository% from %organization% at Github!",
                array('%repository%' => 'kitFramework_Catalog', '%organization%' => 'phpManufaktur')));
        }

        $Setting = new Setting($this->app);
        $last_release = $Setting->select('extension_catalog_release');
        if (\version_compare($release, $last_release, '>')) {
            $this->setMessage("actual: $last_release, online: $release (online is newer, we'll update!)");
        }
        else {
            // nothing to do!
            return true;
        }
        $cURL = new cURL($this->app);
        $info = array();
        $target_path = FRAMEWORK_TEMP_PATH.'/catalog.zip';
        $cURL->DownloadRedirectedURL($catalog_url, $target_path);

        // catalog.zip is in temp directory
        if (!file_exists($target_path)) {
            throw new \Exception($this->app['translator']->trans("Can't open the file <b>%file%</b>!",
                array('%file%' => substr($target_path, strlen(FRAMEWORK_PATH)))));
        }
        // init unZip
        $unZip = new unZip($this->app);
        $unZip->setUnZipPath(FRAMEWORK_TEMP_PATH.'/catalog');
        $unZip->checkDirectory($unZip->getUnZipPath());
        $unZip->extract($target_path);
        $files = $unZip->getFileList();
        if (null === ($subdirectory = $this->getFirstSubdirectory($unZip->getUnZipPath()))) {
            throw new \Exception($this->app['translator']->trans('The received repository has an unexpected directory structure!'));
        }

        // init catalog
        $catalog = new Catalog($this->app);

        foreach ($files as $file) {
            if (false !== strpos($file['file_name'], '/')) {
                $work = explode('/', substr($file['file_name'], strlen($subdirectory)+1));
                if (isset($work[0]) && isset($work[1])) {
                    $category = strtolower($work[0]);
                    switch ($category) {
                        case 'framework':
                            if (strtolower($work[1]) == 'framework.json') {
                                try {
                                    $framework = $this->app['utils']->readConfiguration($file['file_path']);
                                } catch (\Exception $e) {
                                    $this->setMessage('Can not read the information file for the kitFramework!');
                                }
                                if (file_exists(FRAMEWORK_PATH.'/framework.json') && isset($framework['release']['number'])) {
                                    // check if a new kitFramework release is available
                                    $actual_framework = $this->app['utils']->readConfiguration(FRAMEWORK_PATH.'/framework.json');
                                    if (version_compare($framework['release']['number'], $actual_framework['release']['number'], '>')) {
                                        // the framework version has changed!
                                        $this->setMessage('New kitFramework release available!');
                                    }
                                }
                            }
                            break;
                        case 'extension':
                            if (isset($work[2]) && isset($work[3]) && (strtolower($work[3]) == 'extension.json')) {
                                $group = strtolower($work[1]);
                                $name = $work[2];
                                try {
                                    $target = $this->app['utils']->readConfiguration($file['file_path']);
                                } catch (\Exception $e) {
                                    $this->setMessage('Can not read the extension.json for %name%!<br />Error message: %error%',
                                        array('%name%' => $name, '%error%' => $e->getMessage()));
                                    break;
                                }
                                if (!isset($target['guid']) || !isset($target['group']) || !isset($target['release']['number'])) {
                                    $this->setMessage('The extension.json of <b>%name%</b> does not contain all definitions, check GUID, Group and Release!',
                                        array('%name%' => $name));
                                    break;
                                }
                                $path = substr($file['file_path'], 0, strrpos($file['file_path'], '/')+1);
                                $logo = array(
                                    'blob' => '',
                                    'type' => 'png',
                                    'width' => 0,
                                    'height' => 0,
                                    'size' => 0
                                );
                                if (file_exists($path.'extension.jpg') || file_exists($path.'extension.png')) {
                                    if (file_exists($path.'extension.jpg')) {
                                        $image = $path.'extension.jpg';
                                        $logo['type'] = 'jpg';
                                    }
                                    else {
                                        $image =  $path."extension.png";
                                        $logo['type'] = 'png';
                                    }
                                    $logo['size'] = filesize($image);
                                    $handle = fopen($image, "rb");
                                    $img = fread($handle, $logo['size']);
                                    fclose($handle);
                                    $logo['blob'] = base64_encode($img);
                                    list($logo['width'], $logo['height']) = getimagesize($image);
                                }
                                $data = array(
                                    'guid' => $target['guid'],
                                    'name' => $name,
                                    'category' => $target['category'],
                                    'group' => $target['group'],
                                    'release' => $target['release']['number'],
                                    'release_status' => (isset($target['release']['status'])) ? $target['release']['status'] : 'undefined',
                                    'date' => date('Y-m-d', strtotime($target['release']['date'])),
                                    'info' => base64_encode(json_encode($target)),
                                    'logo_blob' => $logo['blob'],
                                    'logo_type' => $logo['type'],
                                    'logo_size' => $logo['size'],
                                    'logo_width' => $logo['width'],
                                    'logo_height' => $logo['height']
                                );
                                if (null === ($id = $catalog->selectIDbyGUID($data['guid']))) {
                                    // insert as new record
                                    $id = $catalog->insert($data);
                                    $this->setMessage('Add the extension <b>%name%</b> to the catalog.',
                                        array('%name%' => $data['name']));
                                }
                                else {
                                    // update the existing record
                                    $catalog->update($id, $data);
                                    $this->setMessage('Updated the catalog data for <b>%name%</b>.',
                                        array('%name%' => $data['name']));
                                }
                            }
                            break;
                    }
                }
            }
        }
        // update settings
        $Setting->update('extension_catalog_release', $release);
        return true;
    }

    public function getAvailableExtensions()
    {
        $catalog = new Catalog($this->app);
        $items = $catalog->selectAll();

        $register = new Register($this->app);

        $result = array();
        foreach ($items as $item) {
            if (null !== ($reg_id = $register->selectIDbyGUID($item['guid']))) {
                // this extension is already installed, skip ...
                continue;
            }
            $data = $item;
            $info = json_decode(base64_decode($item['info']), true);
            $data['info'] = $info;
            if (isset($info['description'][$this->app['locale']])) {
                // description for the actual locale is available
                $description = array(
                    'title' => isset($info['description'][$this->app['locale']]['title']) ? $info['description'][$this->app['locale']]['title'] : '',
                    'short' => isset($info['description'][$this->app['locale']]['short']) ? $info['description'][$this->app['locale']]['short'] : '',
                    'long' => isset($info['description'][$this->app['locale']]['long']) ? $info['description'][$this->app['locale']]['long'] : '',
                    'url' => isset($info['description'][$this->app['locale']]['url']) ? $info['description'][$this->app['locale']]['url'] : ''
                );
            }
            else {
                // use the english default
                $description = array(
                    'title' => isset($info['description']['en']['title']) ? $info['description']['en']['title'] : '',
                    'short' => isset($info['description']['en']['short']) ? $info['description']['en']['short'] : '',
                    'long' => isset($info['description']['en']['long']) ? $info['description']['en']['long'] : '',
                    'url' => isset($info['description']['en']['url']) ? $info['description']['en']['url'] : ''
                );
            }
            $data['description'] = $description;
            $result[] = $data;
        }
        return $result;
    }

}
