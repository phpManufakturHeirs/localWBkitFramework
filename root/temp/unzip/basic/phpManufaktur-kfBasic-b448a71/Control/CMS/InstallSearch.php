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

use phpManufaktur\Basic\Control\CMS\WebsiteBaker\InstallSearch as WebsiteBakerInstallSearch;
use phpManufaktur\Basic\Control\CMS\LEPTON\InstallSearch as LeptonInstallSearch;
use phpManufaktur\Basic\Control\CMS\BlackCat\InstallSearch as BlackCatInstallSearch;
use Silex\Application;

class InstallSearch
{
    protected $app = null;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    public function exec()
    {
        if (CMS_TYPE == 'WebsiteBaker') {
            $Search = new WebsiteBakerInstallSearch($this->app);
        }
        elseif (CMS_TYPE == 'BlackCat') {
            $Search = new BlackCatInstallSearch($this->app);
        }
        elseif (CMS_TYPE == 'LEPTON') {
            $Search = new LeptonInstallSearch($this->app);
        }
        else {
            throw new \Exception('The CMS_TYPE '.CMS_TYPE.' is not supported!');
        }
        return $Search->exec();
    }
}
