<?php

/**
 * Basic
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de
 * @copyright 2014 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\Basic\Data\CMS;

use Silex\Application;
use phpManufaktur\Basic\Data\CMS\WebsiteBaker\Settings as WebsiteBakerSettings;
use phpManufaktur\Basic\Data\CMS\LEPTON\Settings as LeptonSettings;
use phpManufaktur\Basic\Data\CMS\BlackCat\Settings as BlackCatSettings;

class Settings {

    protected $app = null;
    protected $cms = null;

    /**
     * Constructor
     *
     * @param Application $app
     * @throws \Exception
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
        switch (CMS_TYPE) {
            case 'WebsiteBaker':
                $this->cms = new WebsiteBakerSettings($app); break;
            case 'LEPTON':
                $this->cms = new LeptonSettings($app); break;
            case 'BlackCat':
                $this->cms = new BlackCatSettings($app); break;
            default:
                throw new \Exception(sprintf("The CMS TYPE <b>%s</b> is not supported!", CMS_TYPE));
        }
    }

    /**
     * Get a setting from the CMS
     *
     * @param string $name of the setting
     * @throws \Exception
     * @return string value of the setting
     */
    public function getSetting($name)
    {
         return $this->cms->getSetting($name);
    }

}
