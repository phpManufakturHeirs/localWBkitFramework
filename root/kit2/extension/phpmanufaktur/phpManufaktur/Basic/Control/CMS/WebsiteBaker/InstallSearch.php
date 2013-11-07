<?php

/**
 * kitFramework::Basic
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de
 * @copyright 2013 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\Basic\Control\CMS\WebsiteBaker;

use Silex\Application;
use phpManufaktur\Basic\Data\CMS\WebsiteBaker\Addons;
use phpManufaktur\Basic\Data\CMS\WebsiteBaker\SearchSection;

class InstallSearch
{
    protected $app = null;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    public function exec()
    {
        // remove probably existing directory
        $this->app['filesystem']->remove(CMS_PATH.'/modules/kit_framework_search');

        if (!file_exists(CMS_PATH.'/modules/kit_framework_search')) {
            // create the directory
            $this->app['filesystem']->mkdir(CMS_PATH.'/modules/kit_framework_search');
        }

        // copy the search files to the target directory
        $this->app['utils']->xcopy(
            MANUFAKTUR_PATH.'/Basic/Data/Setup/Files/CMS/WebsiteBaker/Search',
            CMS_PATH.'/modules/kit_framework_search');

        // include the info.php to get the actual information
        include_once MANUFAKTUR_PATH.'/Basic/Data/Setup/Files/CMS/WebsiteBaker/Search/info.php';

        // all CMS types
        $data = array(
            'type' => 'module',
            'directory' => $module_directory,
            'name' => $module_name,
            'description' => $module_description,
            'function' => $module_function,
            'version' => $module_version,
            'platform' => $module_platform,
            'author' => $module_author,
            'license' => $module_license
        );

        if (CMS_TYPE == 'LEPTON') {
            $data['guid'] = $module_guid;
        }

        if (CMS_TYPE == 'BlackCat') {
            $data['guid'] = $module_guid;
            $data['installed'] = time();
            $data['bundled'] = 'N';
            $data['removable'] = 'Y';
        }

        $Addons = new Addons($this->app);
        // first delete probably existing entry
        $Addons->delete($module_directory);
        // install the search add-on
        $Addons->insert($data);

        // check for search section at any page
        $SearchSection = new SearchSection();
        $SearchSection->addSearchSection($this->app);
    }
}
