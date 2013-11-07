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

use phpManufaktur\Basic\Control\CMS\WebsiteBaker\UninstallSearch as WebsiteBakerUninstallSearch;
use phpManufaktur\Basic\Control\CMS\LEPTON\UninstallSearch as LeptonUninstallSearch;
use phpManufaktur\Basic\Control\CMS\BlackCat\UninstallSearch as BlackCatUninstallSearch;
use Silex\Application;

class UninstallSearch
{
    protected $app = null;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    public function exec()
    {
        if (CMS_TYPE == 'WebsiteBaker') {
            $Search = new WebsiteBakerUninstallSearch($this->app);
        }
        elseif (CMS_TYPE == 'BlackCat') {
            $Search = new BlackCatUninstallSearch($this->app);
        }
        elseif (CMS_TYPE == 'LEPTON') {
            $Search = new LeptonUninstallSearch($this->app);
        }
        else {
            throw new \Exception('The CMS_TYPE '.CMS_TYPE.' is not supported!');
        }
        return $Search->exec();
    }
}
