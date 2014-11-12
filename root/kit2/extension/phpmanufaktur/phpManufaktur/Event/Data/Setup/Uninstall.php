<?php

/**
 * Event
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de/Event
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
use phpManufaktur\Basic\Control\CMS\UninstallAdminTool;
use phpManufaktur\Event\Data\Event\Images;
use phpManufaktur\Event\Data\Event\Subscription;
use phpManufaktur\Event\Data\Event\Propose;
use phpManufaktur\Event\Data\Event\RecurringEvent;

class Uninstall
{

    protected $app = null;

    public function exec(Application $app)
    {
        try {
            $Group = new Group($app);
            $Group->dropTable();

            $Event = new Event($app);
            $Event->dropTable();

            $Description = new Description($app);
            $Description->dropTable();

            $ExtraType = new ExtraType($app);
            $ExtraType->dropTable();

            $ExtraGroup = new ExtraGroup($app);
            $ExtraGroup->dropTable();

            $Extra = new Extra($app);
            $Extra->dropTable();

            $OrganizerTag = new OrganizerTag($app);
            $OrganizerTag->dropTable();

            $LocationTag = new LocationTag($app);
            $LocationTag->dropTable();

            $ParticipantTag = new ParticipantTag($app);
            $ParticipantTag->dropTable();

            $Images = new Images($app);
            $Images->dropTable();

            $Subscription = new Subscription($app);
            $Subscription->dropTable();

            $Propose = new Propose($app);
            $Propose->dropTable();

            $RecurringEvent = new RecurringEvent($app);
            $RecurringEvent->dropTable();

            // uninstall kit_framework_event from the CMS
            $admin_tool = new UninstallAdminTool($app);
            $admin_tool->exec(MANUFAKTUR_PATH.'/Event/extension.json');

            $app['monolog']->addInfo('[Event Uninstall] Dropped all tables successfull');
            return $app['translator']->trans('Successfull uninstalled the extension %extension%.',
                array('%extension%' => 'Event'));
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }
}
