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
use phpManufaktur\Basic\Data\CMS\WebsiteBaker\Users as WebsiteBakerUsers;
use phpManufaktur\Basic\Data\CMS\LEPTON\Users as LeptonUsers;
use phpManufaktur\Basic\Data\CMS\BlackCat\Users as BlackCatUsers;

/**
 * Class to access the CMS users
 *
 * @author Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 *
 */
class Users
{

    protected $app = null;

    /**
     * Constructor
     *
     * @param Application $app
     * @throws \Exception
     */
    public function __construct (Application $app)
    {
        $this->app = $app;
        switch (CMS_TYPE) {
            case 'WebsiteBaker':
                $this->cms = new WebsiteBakerUsers($app); break;
            case 'LEPTON':
                $this->cms = new LeptonUsers($app); break;
            case 'BlackCat':
                $this->cms = new BlackCatUsers($app); break;
            default:
                throw new \Exception(sprintf("The CMS TYPE <b>%s</b> is not supported!", CMS_TYPE));
        }
    }

    /**
     * Select the CMS user by the given user name
     *
     * @param string $name
     * @param boolen $is_admin
     * @throws \Exception
     * @return boolean|array
     */
    public function selectUser ($name, &$is_admin = false)
    {
        return $this->cms->selectUser($name, $is_admin);
    }

    /**
     * Select the CMS user by the given ID
     *
     * @param integer $user_id
     * @throws \Exception
     * @return Ambigous <boolean, array>
     */
    public function select($user_id)
    {
        return $this->cms->select($user_id);
    }
}
