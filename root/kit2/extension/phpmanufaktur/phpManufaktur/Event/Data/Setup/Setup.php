<?php

/**
 * Event
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de/FacebookGallery
 * @copyright 2013 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\Event\Data\Setup;

use Silex\Application;
use phpManufaktur\Event\Data\Event\Event;
use phpManufaktur\Event\Data\Event\Group;
use phpManufaktur\Event\Data\Event\Description;
use phpManufaktur\Event\Data\Event\ExtraType;
use phpManufaktur\Event\Data\Event\Extra;
use phpManufaktur\Event\Data\Event\ExtraGroup;
use phpManufaktur\Event\Data\Event\OrganizerTag;
use phpManufaktur\Event\Data\Event\LocationTag;
use phpManufaktur\Event\Data\Event\ParticipantTag;
use phpManufaktur\Basic\Control\CMS\InstallAdminTool;
use phpManufaktur\Event\Data\Event\Images;
use phpManufaktur\Event\Data\Event\Subscription;
use phpManufaktur\Event\Data\Event\Propose;
use phpManufaktur\Event\Control\Configuration;

class Setup
{


    /**
     * Check if the config file exists and create it with default values
     *
     * @param Application $app
     */
    protected function createConfigFile(Application $app)
    {
        // nothing else to do ...
        $Config = new Configuration($app);
    }

    /**
     * Execute all steps needed to setup the Event application
     *
     * @param Application $app
     * @throws \Exception
     * @return string with result
     */
    public function exec(Application $app)
    {
        try {
            // check if the config file exists and create default settings
            $this->createConfigFile($app);

            $Group = new Group($app);
            $Group->createTable();

            $Event = new Event($app);
            $Event->createTable();

            $Description = new Description($app);
            $Description->createTable();

            $ExtraType = new ExtraType($app);
            $ExtraType->createTable();

            $ExtraGroup = new ExtraGroup($app);
            $ExtraGroup->createTable();

            $Extra = new Extra($app);
            $Extra->createTable();

            $OrganizerTag = new OrganizerTag($app);
            $OrganizerTag->createTable();

            $LocationTag = new LocationTag($app);
            $LocationTag->createTable();

            $ParticipantTag = new ParticipantTag($app);
            $ParticipantTag->createTable();

            $Images = new Images($app);
            $Images->createTable();

            $Subscription = new Subscription($app);
            $Subscription->createTable();

            $Propose = new Propose($app);
            $Propose->createTable();

            // setup kit_framework_event as Add-on in the CMS
            $admin_tool = new InstallAdminTool($app);
            $admin_tool->exec(MANUFAKTUR_PATH.'/Event/extension.json', '/event/cms');

            return $app['translator']->trans('Successfull installed the extension %extension%.',
                array('%extension%' => 'Event'));

        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }
}
