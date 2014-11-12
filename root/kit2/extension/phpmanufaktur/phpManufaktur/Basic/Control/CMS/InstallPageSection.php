<?php

/**
 * BASIC
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de
 * @copyright 2014 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\Basic\Control\CMS;

use phpManufaktur\Basic\Control\CMS\WebsiteBaker\InstallPageSection as WebsiteBakerInstallPageSection;
use phpManufaktur\Basic\Control\CMS\LEPTON\InstallPageSection as LeptonInstallPageSection;
use phpManufaktur\Basic\Control\CMS\BlackCat\InstallPageSection as BlackCatInstallPageSection;
use Silex\Application;

class InstallPageSection
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
     * Install a PAGE SECTION within the CMS to access the kitFramework extension
     *
     * @param string $extension_path
     * @param string $extension_route
     * @throws \Exception
     */
    public function exec($extension_path, $extension_route)
    {
        if (CMS_TYPE == 'WebsiteBaker') {
            $section = new WebsiteBakerInstallPageSection($this->app);
        }
        elseif (CMS_TYPE == 'BlackCat') {
            $section = new BlackCatInstallPageSection($this->app);
        }
        elseif (CMS_TYPE == 'LEPTON') {
            $section = new LeptonInstallPageSection($this->app);
        }
        else {
            throw new \Exception('The CMS_TYPE '.CMS_TYPE.' is not supported!');
        }
        return $section->exec($extension_path, $extension_route);
    }
}
