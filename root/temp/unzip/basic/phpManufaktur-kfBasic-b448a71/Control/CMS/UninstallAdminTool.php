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

use phpManufaktur\Basic\Control\CMS\WebsiteBaker\UninstallAdminTool as WebsiteBakerUninstallAdminTool;
use phpManufaktur\Basic\Control\CMS\LEPTON\UninstallAdminTool as LeptonUninstallAdminTool;
use phpManufaktur\Basic\Control\CMS\BlackCat\UninstallAdminTool as BlackCatUninstallAdminTool;
use Silex\Application;

class UninstallAdminTool
{
    protected $app = null;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    public function exec($extension_path)
    {
        if (CMS_TYPE == 'WebsiteBaker') {
            $admin_tool = new WebsiteBakerUninstallAdminTool($this->app);
        }
        elseif (CMS_TYPE == 'BlackCat') {
            $admin_tool = new BlackCatUninstallAdminTool($this->app);
        }
        elseif (CMS_TYPE == 'LEPTON') {
            $admin_tool = new LeptonUninstallAdminTool($this->app);
        }
        else {
            throw new \Exception('The CMS_TYPE '.CMS_TYPE.' is not supported!');
        }
        return $admin_tool->exec($extension_path);
    }
}
