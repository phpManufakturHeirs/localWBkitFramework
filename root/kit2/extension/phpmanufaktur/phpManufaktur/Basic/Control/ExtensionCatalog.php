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

use phpManufaktur\Basic\Control\gitHub\gitHub;
use phpManufaktur\Basic\Control\cURL\cURL;
use phpManufaktur\Basic\Control\unZip\unZip;
use phpManufaktur\Basic\Data\ExtensionCatalog as Catalog;
use phpManufaktur\Basic\Data\Setting;
use phpManufaktur\Basic\Data\ExtensionRegister as Register;
use phpManufaktur\Basic\Control\Pattern\Alert;
use Silex\Application;

/**
 * Get the catalog with all for the kitFramework available extensions from GitHub
 *
 * @author Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 *
 */
class ExtensionCatalog extends Alert
{

    protected static $usage = null;
    private static $ignore_locale = false;

    public function __construct(Application $app=null, $ignore_locale=false)
    {
        if (!is_null($app)) {
            self::$ignore_locale = $ignore_locale;
            $this->initialize($app);
        }
    }

    /**
     * (non-PHPdoc)
     * @see \phpManufaktur\Basic\Control\Pattern\Alert::initialize()
     */
    protected function initialize(Application $app)
    {
        parent::initialize($app);

        if (!self::$ignore_locale) {
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
     * Check if a new catalog information is available at Github
     *
     * @param string reference $catalog_release
     * @param string reference $available_release
     * @param string reference $catalog_url
     * @throws \Exception
     * @return boolean
     */
    public function isCatalogUpdateAvailable(&$catalog_release=null,&$available_release=null, &$catalog_url=null)
    {
        // init GitHub
        $github = new gitHub($this->app);
        $available_release = null;
        if (false === ($catalog_url = $github->getLastRepositoryZipUrl('phpManufaktur', 'kitFramework_Catalog', $available_release))) {
            throw new \Exception($this->app['translator']->trans("Can't read the the %repository% from %organization% at Github!",
                array('%repository%' => 'kitFramework_Catalog', '%organization%' => 'phpManufaktur')));
        }

        $Setting = new Setting($this->app);
        $catalog_release = $Setting->select('extension_catalog_release');
        if (version_compare($available_release, $catalog_release, '>')) {
            // update available
            return true;
        }
        // no update available
        return false;
    }

    /**
     * Get the online catalog from Github and read it into the database
     *
     * @throws \Exception
     * @return boolean
     */
    public function getOnlineCatalog()
    {
        $catalog_release = null;
        $available_release = null;
        $catalog_url = null;

        if (!$this->isCatalogUpdateAvailable($catalog_release, $available_release, $catalog_url)) {
            // nothing to do - return ...
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

        $update_extension = array();
        $add_extension = array();

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
                                    $this->setAlert('Can not read the information file for the kitFramework!',
                                        array(), self::ALERT_TYPE_WARNING);
                                }
                                if (file_exists(FRAMEWORK_PATH.'/framework.json') && isset($framework['release']['number'])) {
                                    // check if a new kitFramework release is available
                                    $actual_framework = $this->app['utils']->readConfiguration(FRAMEWORK_PATH.'/framework.json');
                                    if (version_compare($framework['release']['number'], $actual_framework['release']['number'], '>')) {
                                        // the framework version has changed!
                                        $Setting = new Setting($this->app);
                                        if (!$Setting->exists('framework_update')) {
                                            // insert a new record
                                            $Setting->insert('framework_update', $framework['release']['number']);
                                        }
                                        else {
                                            $Setting->update('framework_update', $framework['release']['number']);
                                        }
                                        $this->setAlert('There is a <a href="%route%">new kitFramework release available</a>!',
                                            array('%route%' => FRAMEWORK_URL.'/admin/welcome/extensions?usage='.self::$usage),
                                            self::ALERT_TYPE_INFO);
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
                                    $this->setAlert('Can not read the extension.json for %name%!<br />Error message: %error%',
                                        array('%name%' => $name, '%error%' => $e->getMessage()), self::ALERT_TYPE_WARNING);
                                    break;
                                }
                                if (!isset($target['guid']) || !isset($target['group']) || !isset($target['release']['number'])) {
                                    $this->setAlert('The extension.json of <b>%name%</b> does not contain all definitions, check GUID, Group and Release!',
                                        array('%name%' => $name), self::ALERT_TYPE_WARNING);
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
                                    $add_extension[] = $data['name'];
                                }
                                else {
                                    // update the existing record
                                    $catalog->update($id, $data);
                                    $update_extension[] = $data['name'];
                                }
                            }
                            break;
                    }
                }
            }
        }

        // update settings
        $Setting = new Setting($this->app);
        if (is_null($Setting->select('extension_catalog_release'))) {
            $Setting->insertDefaultValues();
        }
        $Setting->update('extension_catalog_release', $available_release);

        if (!empty($add_extension)) {
            $this->setAlert('Add the extension(s) <strong>%extension%</strong> to the catalog.',
                array('%extension%' => implode(', ', $add_extension)), self::ALERT_TYPE_SUCCESS);
        }
        if (!empty($update_extension)) {
            $this->setAlert('Updated the catalog data for the extension(s) <strong>%extension%</strong>.',
                array('%extension%' => implode(', ', $update_extension)), self::ALERT_TYPE_SUCCESS);
        }

        return true;
    }

    /**
     * Get the available extensions for this kitFramework installation
     *
     * @param string $locale default = 'en'
     * @return array
     */
    public function getAvailableExtensions($locale='en')
    {
        $locale = strtolower($locale);
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
            if (isset($info['description'][$locale])) {
                // description for the actual locale is available
                $description = array(
                    'title' => isset($info['description'][$locale]['title']) ? $info['description'][$locale]['title'] : '',
                    'short' => isset($info['description'][$locale]['short']) ? $info['description'][$locale]['short'] : '',
                    'long' => isset($info['description'][$locale]['long']) ? $info['description'][$locale]['long'] : '',
                    'url' => isset($info['description'][$locale]['url']) ? $info['description'][$locale]['url'] : ''
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
