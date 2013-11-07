<?php

/**
 * kitFramework::Basic
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de
 * @copyright 2013 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\Basic\Control\CMS;

use phpManufaktur\Basic\Control\CMS\WebsiteBaker\InstallAdminTool as WebsiteBakerInstallAdminTool;
use phpManufaktur\Basic\Control\CMS\LEPTON\InstallAdminTool as LeptonInstallAdminTool;
use phpManufaktur\Basic\Control\CMS\BlackCat\InstallAdminTool as BlackCatInstallAdminTool;
use Silex\Application;

class InstallAdminTool
{
    protected $app = null;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    public function exec($extension_path, $extension_route)
    {
        if (CMS_TYPE == 'WebsiteBaker') {
            $admin_tool = new WebsiteBakerInstallAdminTool($this->app);
        }
        elseif (CMS_TYPE == 'BlackCat') {
            $admin_tool = new BlackCatInstallAdminTool($this->app);
        }
        elseif (CMS_TYPE == 'LEPTON') {
            $admin_tool = new LeptonInstallAdminTool($this->app);
        }
        else {
            throw new \Exception('The CMS_TYPE '.CMS_TYPE.' is not supported!');
        }
        return $admin_tool->exec($extension_path, $extension_route);
    }
}
