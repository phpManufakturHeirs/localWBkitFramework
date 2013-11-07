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
use phpManufaktur\Basic\Control\CMS\InstallSearch;
use phpManufaktur\Basic\Data\Security\AdminAction;

/**
 * Setup all needed database tables and initialize the kitFramework
 *
 * @author Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 *
 */
class Setup
{
    protected $app = null;

    /**
     * Release 0.42
     */
    protected function release_042()
    {
        $Update = new Update();
        $Update->release_042($this->app);
    }

    /**
     * Create the database tables for the BASIC extension of the kitFramework
     *
     * @param Application $app
     */
    public function exec(Application $app)
    {
        $this->app = $app;

        // create the framework user table
        $users = new Users($app);
        $users->createTable();

        // create the Extension Catalog
        $catalog = new ExtensionCatalog($app);
        $catalog->createTable();

        // create the setting table
        $setting = new Setting($app);
        $setting->createTable();
        $setting->insertDefaultValues();

        // create the table for the extension register
        $register = new ExtensionRegister($app);
        $register->createTable();

        // create the table for the kitCommand parameters
        $cmdParameter = new kitCommandParameter($app);
        $cmdParameter->createTable();

        // create the AdminAction table
        $adminAction = new AdminAction($app);
        $adminAction->createTable();

        // maybe BASIC is installed by an older kitFrameworkCMSTool ...
        $this->release_042();

        // install the search function
        $Search = new InstallSearch($app);
        $Search->exec();

        return $app['translator']->trans('Successfull installed the extension %extension%.',
            array('%extension%' => 'Basic'));
    }

}
