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

use phpManufaktur\Basic\Data\ExtensionRegister as Register;
use phpManufaktur\Basic\Data\ExtensionCatalog as Catalog;
use phpManufaktur\Basic\Control\Pattern\Alert;
use Silex\Application;

/**
 * Check for installed extensions and read the information from extension.json
 * and read the data into the register table
 *
 * @author Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 *
 */
class ExtensionRegister extends Alert
{
    const GROUP_PHPMANUFAKTUR = 'phpManufaktur';
    const GROUP_THIRDPARTY = 'thirdParty';

    protected static $usage = null;

    /**
     * (non-PHPdoc)
     * @see \phpManufaktur\Basic\Control\Pattern\Alert::initialize()
     */
    protected function initialize(Application $app)
    {
        parent::initialize($app);

        if (false === FRAMEWORK_SETUP) {
            self::$usage = $this->app['request']->get('usage', 'framework');
            if (self::$usage != 'framework') {
                // set the locale from the CMS locale
                $app['translator']->setLocale($app['session']->get('CMS_LOCALE', 'de'));
            }
        }
    }

    /**
     * Check the given $path and $group for a installed extension
     *
     * @param string $path
     * @param string $group
     * @param string reference $extension
     * @param string reference $mode
     * @return boolean
     */
    protected function checkDirectory($path, $group, &$extension='', $mode=null)
    {
        $extension = '';
        if (file_exists($path.'/extension.json')) {
            try {
                $target = $this->app['utils']->readConfiguration($path.'/extension.json');
            } catch (\Exception $e) {
                $this->setAlert('Can not read the extension.json in %directory%!<br />Error message: %error%',
                    array('%directory%' => substr($path.'/extension.json', strlen(FRAMEWORK_PATH)), '%error%' => $e->getMessage()),
                    self::ALERT_TYPE_WARNING);

            }
            if (!isset($target['guid']) || !isset($target['release']['number'])) {
                $this->setAlert('The extension.json of <b>%name%</b> does not contain all definitions, check GUID, Group and Release!',
                    array('%name%' => $group), self::ALERT_TYPE_WARNING);
                return false;
            }
            if (file_exists($path.'/extension.jpg') || file_exists($path.'/extension.png')) {
                $img_path = file_exists($path.'/extension.jpg') ? $path.'/extension.jpg' : $path.'/extension.png';
                $logo_url = FRAMEWORK_URL . substr($img_path, strlen(FRAMEWORK_PATH));
                list($logo_width, $logo_height) = getimagesize($img_path);
            }
            $data = array(
                'guid' => $target['guid'],
                'name' => $target['name'],
                'group' => $target['group'],
                'release' => $target['release']['number'],
                'release_status' => (isset($target['release']['status'])) ? $target['release']['status'] : 'undefined',
                'date' => date('Y-m-d', strtotime($target['release']['date'])),
                'info' => base64_encode(json_encode($target)),
                'logo_url' => isset($logo_url) ? $logo_url : '',
                'logo_width' => isset($logo_width) ? $logo_width : 0,
                'logo_height' => isset($logo_height) ? $logo_height : 0,
                'start_url' => (isset($target['link']['start'])) ? $target['link']['start'] : '',
                'about_url' => (isset($target['link']['about'])) ? $target['link']['about'] : ''
            );
            $register = new Register($this->app);
            if (null === ($id = $register->selectIDbyGUID($data['guid']))) {
                // insert as new record
                $data['date_installed'] = date('Y-m-d');
                $id = $register->insert($data);
                $mode = 'insert';
            }
            else {
                // update the existing record
                $register->update($id, $data);
                $mode = 'update';
            }
            $extension = $data['name'];
            return true;
        }
        return false;
    }

    /**
     * Scan all extension directories for the given $group
     *
     * @param string $group
     */
    public function scanDirectories($group=self::GROUP_PHPMANUFAKTUR)
    {
        $checkedExtensions = array();
        $insert_extension = array();
        $update_extension = array();

        $path = ($group == self::GROUP_PHPMANUFAKTUR) ? MANUFAKTUR_PATH : THIRDPARTY_PATH;
        $handle = opendir($path);

        // we loop through the directory to get the first subdirectory ...
        while (false !== ($directory = readdir($handle))) {
            if ('.' == $directory || '..' == $directory)
                continue;
            if (is_dir($path .'/'. $directory)) {
                $extension = '';
                $mode = null;
                $this->checkDirectory($path.'/'.$directory, $group, $extension, $mode);
                if (!empty($extension)) {
                    $checkedExtensions[] = $extension;
                    if ($mode === 'insert') {
                        $insert_extension[] = $extension;
                    }
                    else {
                        $update_extension[] = $extension;
                    }
                }
            }
        }
        if (!empty($checkedExtensions)) {
            // check for widows in the table
            $Register = new Register($this->app);
            $registered = $Register->selectAllByGroup($group);
            foreach ($registered as $reg) {
                if (!in_array($reg['name'], $checkedExtensions)) {
                    // delete from database
                    $Register->delete($reg['id']);
                    unset($checkedExtensions[$reg['name']]);
                }
            }
            if (!empty($insert_extension)) {
                $this->setAlert('Add the extension(s) <strong>%extension%</strong> to the register.',
                    array('%extension%' => implode(', ', $insert_extension)), self::ALERT_TYPE_SUCCESS);
            }
            if (!empty($update_extension)) {
                $this->setAlert('Updated the register data for the extension(s) <strong>%extension%</strong>.',
                    array('%extension%' => implode(', ', $update_extension)), self::ALERT_TYPE_SUCCESS);
            }
        }
    }

    /**
     * Get an array with the extension data for the given $item
     *
     * @param array $item
     * @return array
     */
    protected function getExtensionData($item)
    {
        $catalog = new Catalog($this->app);
        $cat = array();

        if (null !== ($cat_id = $catalog->selectIDbyGUID($item['guid']))) {
            $cat = $catalog->select($cat_id);
        }

        $data = $item;
        $info = isset($cat['info']) ? json_decode(base64_decode($cat['info']), true) : array();
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
        // check if a changelog is available
        $data['changelog'] = isset($info['release']['changelog']) ? $info['release']['changelog'] : null;
        $data['description'] = $description;
        $data['logo_blob'] = '';
        $data['release_available'] = (isset($cat['release'])) ? $cat['release'] : $item['release'];
        $data['update_available'] = (isset($cat['release']) && (\version_compare($cat['release'], $item['release'], '>'))) ? true : false;
        if (isset($cat['logo_blob'])) {
            $data['logo_blob'] = $cat['logo_blob'];
            $data['logo_type'] = $cat['logo_type'];
            $data['logo_width'] = $cat['logo_width'];
            $data['logo_height'] = $cat['logo_height'];
        }

        return $data;
    }

    /**
     * Check the kitFramework for installed extensions
     *
     * @return array
     */
    public function getInstalledExtensions()
    {
        $register = new Register($this->app);
        $items = $register->selectAll();

        $result = array();
        foreach ($items as $item) {
           $result[] = $this->getExtensionData($item);
        }
        return $result;
    }

    /**
     * Return all availablable Updates as preconfigured array
     *
     * @return array
     */
    public function getAvailableUpdates()
    {
        $register = new Register($this->app);
        $items = $register->selectAll();

        $catalog = new Catalog($this->app);
        $result = array();
        foreach ($items as $item) {
            $cat = array();
            if (null !== ($cat_id = $catalog->selectIDbyGUID($item['guid']))) {
                $cat = $catalog->select($cat_id);
            }
            if ((isset($cat['release']) && isset($item['release'])) && (version_compare($cat['release'], $item['release'], '>'))) {
                $result[] = $this->getExtensionData($item);
            }
        }

        return $result;
    }
}
