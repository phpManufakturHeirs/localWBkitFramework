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
use phpManufaktur\Basic\Data\ExtensionRegister as Register;
use phpManufaktur\Basic\Data\ExtensionCatalog as Catalog;

/**
 * Check for installed extensions and read the information from extension.json
 * and read the data into the register table
 *
 * @author Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 *
 */
class ExtensionRegister
{
    protected $app = null;
    protected static $table_name = null;
    protected static $message = '';

    const GROUP_PHPMANUFAKTUR = 'phpManufaktur';
    const GROUP_THIRDPARTY = 'thirdParty';

    public function __construct(Application $app)
    {
        $this->app = $app;
        self::$table_name = FRAMEWORK_TABLE_PREFIX.'basic_extension_register';
    }

    /**
     * @return the $message
     */
    public function getMessage ()
    {
        return self::$message;
    }

    /**
     * @param string $message
     */
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

    protected function checkDirectory($path, $group, &$extension='')
    {
        $extension = '';
        if (file_exists($path.'/extension.json')) {
            try {
                $target = $this->app['utils']->readConfiguration($path.'/extension.json');
            } catch (\Exception $e) {
                $this->setMessage('Can not read the extension.json in %directory%!<br />Error message: %error%',
                    array('%directory%' => substr($path.'/extension.json', strlen(FRAMEWORK_PATH)), '%error%' => $e->getMessage()));
                return false;
            }
            if (!isset($target['guid']) || !isset($target['release']['number'])) {
                $this->setMessage('The extension.json of <b>%name%</b> does not contain all definitions, check GUID, Group and Release!',
                    array('%name%' => $group));
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
                $this->setMessage('Add the extension <b>%name%</b> to the register.',
                    array('%name%' => $data['name']));
            }
            else {
                // update the existing record
                $register->update($id, $data);
                $this->setMessage('Updated the register data for <b>%name%</b>.',
                    array('%name%' => $data['name']));
            }
            $extension = $data['name'];
            return true;
        }
        return false;
    }

    public function scanDirectories($group=self::GROUP_PHPMANUFAKTUR)
    {
        $checkedExtensions = array();
        $path = ($group == self::GROUP_PHPMANUFAKTUR) ? MANUFAKTUR_PATH : THIRDPARTY_PATH;
        $handle = opendir($path);
        // we loop through the directory to get the first subdirectory ...
        while (false !== ($directory = readdir($handle))) {
            if ('.' == $directory || '..' == $directory)
                continue;
            if (is_dir($path .'/'. $directory)) {
                $extension = '';
                $this->checkDirectory($path.'/'.$directory, $group, $extension);
                if (!empty($extension)) {
                    $checkedExtensions[] = $extension;
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
        }
    }

    public function getInstalledExtensions()
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
           $result[] = $data;
        }
        return $result;
    }
}
