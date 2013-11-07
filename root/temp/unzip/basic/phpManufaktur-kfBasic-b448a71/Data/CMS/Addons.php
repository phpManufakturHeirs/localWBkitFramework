<?php

/**
 * kitFramework::Basic
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de
 * @copyright 2013 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\Basic\Data\CMS;

use Silex\Application;
use phpManufaktur\Basic\Data\CMS\WebsiteBaker\Addons as WebsiteBakerAddons;
use phpManufaktur\Basic\Data\CMS\LEPTON\Addons as LeptonAddons;
use phpManufaktur\Basic\Data\CMS\BlackCat\Addons as BlackCatAddons;

/**
 * Class to access the CMS addons
 *
 * @author Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 *
 */
class Addons
{

    protected $app = null;

    /**
     * Constructor
     *
     * @param Application $app
     */
    public function __construct (Application $app)
    {
        $this->app = $app;
        switch (CMS_TYPE) {
            case 'WebsiteBaker':
                $this->cms = new WebsiteBakerAddons($app); break;
            case 'LEPTON':
                $this->cms = new LeptonAddons($app); break;
            case 'BlackCat':
                $this->cms = new BlackCatAddons($app); break;
            default:
                throw new \Exception(sprintf("The CMS TYPE <b>%s</b> is not supported!", CMS_TYPE));
        }
    }

    /**
     * Check if the addon with the given directory name exists in the table
     *
     * @param string $directory
     * @throws \Exception
     * @return boolean
     */
    public function existsDirectory($directory)
    {
        return $this->cms->existsDirectory($directory);
    }

    /**
     * Insert a new record into the Addons table
     *
     * @param array $data
     * @param reference integer $addon_id
     * @throws \Exception
     */
    public function insert($data, &$addon_id=null)
    {
        return $this->cms->insert($data, $addon_id);
    }

    /**
     * Update the addon record with the given directory name and the data array
     *
     * @param string $directory
     * @param array $data
     * @throws \Exception
     */
    public function update($directory, $data)
    {
        return $this->cms->update($directory, $data);
    }

    /**
     * Delete the record with the given directory name
     *
     * @param string $directory
     * @throws \Exception
     */
    public function delete($directory)
    {
        return $this->cms->delete($directory);
    }

}
