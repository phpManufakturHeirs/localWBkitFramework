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
use phpManufaktur\Basic\Data\CMS\Addons;

class UninstallAdminTool
{
    protected $app = null;

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
     * Uninstall the Admin-Tool for the given extension
     *
     * @param string $extension_path
     * @throws \Exception
     */
    public function exec($extension_path)
    {
        $extension = $this->app['utils']->readJSON($extension_path);

        if (!isset($extension['name'])) {
            throw new \Exception('The extension.json does not contain the extension name!');
        }

        $directory_name = 'kit_framework_'.strtolower(trim($extension['name']));

        $addon = new Addons($this->app);
        if ($addon->existsDirectory($directory_name)) {
            // delete the existing record
            $addon->delete($directory_name);
        }

        if (file_exists(CMS_PATH.'/modules/'.$directory_name)) {
            $this->app['filesystem']->remove(CMS_PATH.'/modules/'.$directory_name);
        }
    }
}
