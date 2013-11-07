<?php

/**
 * kitFramework::Basic
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de
 * @copyright 2013 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\Basic\Data\Setup;

use Silex\Application;
use phpManufaktur\Basic\Data\Security\Users;
use phpManufaktur\Basic\Data\ExtensionCatalog;
use phpManufaktur\Basic\Data\Setting;
use phpManufaktur\Basic\Data\ExtensionRegister;
use phpManufaktur\Basic\Data\kitCommandParameter;
use phpManufaktur\Basic\Control\CMS\UninstallSearch;
use phpManufaktur\Basic\Data\Security\AdminAction;

class Uninstall
{

    /**
     * Uninstall the database tables for the BASIC extension of the kitFramework
     *
     * @param Application $app
     */
    public function exec(Application $app)
    {
        // drop the kitFramework users table
        $users = new Users($app);
        $users->dropTable();

        // drop the Extension Catalog
        $catalog = new ExtensionCatalog($app);
        $catalog->dropTable();

        // drop the setting table
        $setting = new Setting($app);
        $setting->dropTable();

        // drop the table for the extension register
        $register = new ExtensionRegister($app);
        $register->dropTable();

        // drop the table for the kitCommand parameters
        $cmdParameter = new kitCommandParameter($app);
        $cmdParameter->dropTable();

        // drop AdminAction
        $adminAction = new AdminAction($app);
        $adminAction->dropTable();

        // uninstall the search function
        $Search = new UninstallSearch($app);
        $Search->exec();

        return $app['translator']->trans('Successfull uninstalled the extension %extension%.',
            array('%extension%' => 'Basic'));
    }

}
