<?php

/**
 * Event
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de/FacebookGallery
 * @copyright 2013 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

use phpManufaktur\Basic\Control\CMS\EmbeddedAdministration;

// not really needed but make error control more easy ...
global $app;

$roles = $app['security.role_hierarchy'];
if (!in_array('ROLE_EVENT_ADMIN', $roles)) {
    $roles['ROLE_ADMIN'][] = 'ROLE_EVENT_ADMIN';
    $roles['ROLE_EVENT_ADMIN'][] = 'ROLE_EVENT_EDIT';
    $app['security.role_hierarchy'] = $roles;
}


// scan the /Locale directory and add all available languages
$app['utils']->addLanguageFiles(MANUFAKTUR_PATH.'/Event/Data/Locale');
// scan the /Locale/Custom directory and add all available languages
$app['utils']->addLanguageFiles(MANUFAKTUR_PATH.'/Event/Data/Locale/Custom');

/**
 * Use the EmbeddedAdministration feature to connect the extension with the CMS
 *
 * @link https://github.com/phpManufaktur/kitFramework/wiki/Extensions-%23-Embedded-Administration
 */
$app->get('/event/cms/{cms_information}', function ($cms_information) use ($app) {
    $administration = new EmbeddedAdministration($app);
    return $administration->route('/admin/event/about', $cms_information);
});

/**
 * ADMIN routes
 */

$admin->get('/event/setup',
    // setup routine for kfEvent
    'phpManufaktur\Event\Data\Setup\Setup::exec');
$admin->get('/event/update',
    // update Event
    'phpManufaktur\Event\Data\Setup\Update::exec');
$admin->get('/event/uninstall',
    // uninstall routine for kfEvent
    'phpManufaktur\Event\Data\Setup\Uninstall::exec');


$app->get('/admin/event/about',
    // About dialog
    'phpManufaktur\Event\Control\Backend\About::exec');

$app->match('/admin/event/contact/list',
    // Contact List
    'phpManufaktur\Event\Control\Backend\ContactList::exec');
$app->match('/admin/event/contact/list/page/{page}',
    'phpManufaktur\Event\Control\Backend\ContactList::exec');

$app->match('/admin/event/contact/search',
    // search contacts
    'phpManufaktur\Event\Control\Backend\ContactSearch::exec');

$app->match('/admin/event/contact/select',
    // Contact create and edit
    'phpManufaktur\Event\Control\Backend\ContactSelect::exec');
$app->match('/admin/event/contact/edit/id/{contact_id}',
    'phpManufaktur\Event\Control\Backend\ContactSelect::exec');

$app->match('/admin/event/contact/person/edit',
    // Create and Edit Person contacts
    'phpManufaktur\Event\Control\Backend\ContactPerson::exec');
$app->match('/admin/event/contact/person/edit/id/{contact_id}',
    'phpManufaktur\Event\Control\Backend\ContactPerson::exec');

$app->match('/admin/event/contact/company/edit',
    // Create and Edit Company contacts
    'phpManufaktur\Event\Control\Backend\ContactCompany::exec');
$app->match('/admin/event/contact/company/edit/id/{contact_id}',
    'phpManufaktur\Event\Control\Backend\ContactCompany::exec');

$app->match('/admin/event/contact/category/list',
    // Category List
    'phpManufaktur\Event\Control\Backend\Contact\CategoryList::exec');

$app->match('/admin/event/contact/category/edit',
    // Category Edit
    'phpManufaktur\Event\Control\Backend\Contact\CategoryEdit::exec');
$app->match('/admin/event/contact/category/edit/id/{category_id}',
    'phpManufaktur\Event\Control\Backend\Contact\CategoryEdit::exec');

$app->match('/admin/event/contact/extra/list',
    // Extra fields List
    'phpManufaktur\Event\Control\Backend\Contact\ExtraList::exec');

$app->match('/admin/event/contact/extra/edit',
    // Create and edit extra fields
    'phpManufaktur\Event\Control\Backend\Contact\ExtraEdit::exec');
$app->match('/admin/event/contact/extra/edit/id/{type_id}',
    'phpManufaktur\Event\Control\Backend\Contact\ExtraEdit::exec');

$app->match('/admin/event/contact/title/list',
    // Title List
    'phpManufaktur\Event\Control\Backend\Contact\TitleList::exec');

$app->match('/admin/event/contact/title/edit',
    // Title Edit
    'phpManufaktur\Event\Control\Backend\Contact\TitleEdit::exec');
$app->match('/admin/event/contact/title/edit/id/{title_id}',
    'phpManufaktur\Event\Control\Backend\Contact\TitleEdit::exec');

$app->match('/admin/event/contact/tag/list',
    // Tag List
    'phpManufaktur\Event\Control\Backend\Contact\TagList::exec');

$app->match('/admin/event/contact/tag/edit',
    // Tag Edit
    'phpManufaktur\Event\Control\Backend\Contact\TagEdit::exec');
$app->match('/admin/event/contact/tag/edit/id/{tag_id}',
    'phpManufaktur\Event\Control\Backend\Contact\TagEdit::exec');

$app->match('/admin/event/group/list',
    // Event Group List
    'phpManufaktur\Event\Control\Backend\GroupList::exec');

$app->match('/admin/event/group/edit',
    // Event Group Edit
    'phpManufaktur\Event\Control\Backend\GroupEdit::exec');
$app->match('/admin/event/group/edit/id/{group_id}',
    'phpManufaktur\Event\Control\Backend\GroupEdit::exec');

$app->match('/admin/event/extra/field/list',
    // Extra Field List
    'phpManufaktur\Event\Control\Backend\ExtraFieldList::exec');

$app->match('/admin/event/extra/field/edit',
    // Extra Field Edit
    'phpManufaktur\Event\Control\Backend\ExtraFieldEdit::exec');
$app->match('/admin/event/extra/field/edit/id/{type_id}',
    'phpManufaktur\Event\Control\Backend\ExtraFieldEdit::exec');

$app->match('/admin/event/edit',
    // Create or Edit Event
    'phpManufaktur\Event\Control\Backend\EventEdit::exec');
$app->match('/admin/event/edit/id/{event_id}',
    'phpManufaktur\Event\Control\Backend\EventEdit::exec');

$app->match('/admin/event/image/add/event/{event_id}',
    // add image to event
    'phpManufaktur\Event\Control\Backend\EventEdit::addImage');
$app->match('/admin/event/image/delete/id/{image_id}/event/{event_id}',
    // delete image from event
    'phpManufaktur\Event\Control\Backend\EventEdit::deleteImage');

$app->match('/admin/event/list',
    // Show the Event List
    'phpManufaktur\Event\Control\Backend\EventList::exec');
$app->match('/admin/event/list/page/{page}',
    'phpManufaktur\Event\Control\Backend\EventList::exec');

$app->match('/admin/event/search',
    'phpManufaktur\Event\Control\Backend\EventSearch::exec');

$app->match('/admin/event/import/kitevent',
    // Import events from kitEvent
    'phpManufaktur\Event\Control\Import\kitEvent\kitEvent::start');
$app->match('/admin/event/import/kitevent/start',
    'phpManufaktur\Event\Control\Import\kitEvent\kitEvent::start');
$app->match('/admin/event/import/kitevent/import',
    'phpManufaktur\Event\Control\Import\kitEvent\kitEvent::import');

$app->get('/admin/event/ical/rebuild',
    // rebuild all iCal files
    'phpManufaktur\Event\Control\Command\EventICal::ControllerRebuildAllICalFiles');
$app->get('/admin/event/qrcode/rebuild',
    // rebuild all QR-Code files
    'phpManufaktur\Event\Control\Command\EventQRCode::ControllerRebuildAllQRCodeFiles');

// handling of subscriptions
$app->get('/admin/event/registration',
    'phpManufaktur\Event\Control\Backend\Subscribe::controllerList');

// handling of proposes
$app->get('/admin/event/propose',
    'phpManufaktur\Event\Control\Backend\Propose::controllerList');

/**
 * kitCOMMAND routes
 */
$command->post('/event',
    // create the iFrame and execute route /event/action
    'phpManufaktur\Event\Control\Command\EventFrame::exec')
    ->setOption('info', MANUFAKTUR_PATH.'/Event/command.event.json');

/**
 * EVENT routes for the kitCommand
 */
$app->get('/event/action',
    // the default action handler for kitCommand: event
    'phpManufaktur\Event\Control\Command\Action::exec');

$app->get('/event/id/{event_id}',
    // select the given event id
    'phpManufaktur\Event\Control\Command\Event::ControllerSelectID');
$app->get('/event/id/{event_id}/view/{view}',
    // select the given event id and determine the view mode
    'phpManufaktur\Event\Control\Command\Event::ControllerSelectID');

$app->get('/event/perma/id/{event_id}',
    // process permanent link for the given event ID
    'phpManufaktur\Event\Control\Command\Event::ControllerSelectPermaLinkID');

$app->get('/event/ical/{event_id}',
    // download of a ical file, also from the protected area
    'phpManufaktur\Event\Control\Command\EventICal::ControllerGetICalFile');

$app->get('/event/qrcode/{event_id}',
    // download a qr-code, also from the protected area
    'phpManufaktur\Event\Control\Command\EventQRCode::ControllerGetQRCodeFile');

$app->get('/event/subscribe/id/{event_id}/redirect/{redirect}',
    // subscribe to a event
    'phpManufaktur\Event\Control\Command\Subscribe::exec');
$app->post('/event/subscribe/check',
    'phpManufaktur\Event\Control\Command\Subscribe::check');

$app->get('/event/subscribe/guid/{guid}',
    // confirm a subscription
    'phpManufaktur\Event\Control\Command\ConfirmSubscription::exec');

$app->post('/event/search',
    // search dialog results
    'phpManufaktur\Event\Control\Command\EventSearch::controllerSearch');

/**
 * Propose a event
 */
$app->match('/event/propose/organizer/search',
    'phpManufaktur\Event\Control\Command\Propose::controllerSearchOrganizer');
$app->post('/event/propose/organizer/select',
    'phpManufaktur\Event\Control\Command\Propose::controllerSelectOrganizer');
$app->get('/event/propose/organizer/create/group/{group_id}',
    'phpManufaktur\Event\Control\Command\Propose::controllerCreateOrganizer');
$app->get('/event/propose/organizer/id/{contact_id}',
    'phpManufaktur\Event\Control\Command\Propose::controllerOrganizerID');
$app->post('/event/propose/contact/check',
    'phpManufaktur\Event\Control\Command\Propose::controllerContactCheck');
$app->match('/event/propose/location/search',
    'phpManufaktur\Event\Control\Command\Propose::controllerSearchLocation');
$app->post('/event/propose/location/select',
    'phpManufaktur\Event\Control\Command\Propose::controllerSelectLocation');
$app->get('/event/propose/location/id/{contact_id}',
    'phpManufaktur\Event\Control\Command\Propose::controllerLocationID');
$app->post('/event/propose/event/check',
    'phpManufaktur\Event\Control\Command\Propose::controllerEventCheck');
$app->post('/event/propose/submitter/confirm',
    'phpManufaktur\Event\Control\Command\Propose::controllerSubmitterConfirm');
$app->get('/event/propose/cancel/{guid}',
    'phpManufaktur\Event\Control\Command\Propose::controllerSubmitterCancelled');
$app->get('/event/propose/confirm/{guid}',
    'phpManufaktur\Event\Control\Command\Propose::controllerSubmitterActivate');
$app->get('/event/propose/publish/{guid}',
    'phpManufaktur\Event\Control\Command\Propose::controllerAdminPublish');
$app->get('/event/propose/reject/{guid}',
    'phpManufaktur\Event\Control\Command\Propose::controllerAdminReject');

/**
 * Edit a event
 */
$app->get('/event/edit/id/{event_id}/redirect/{redirect}',
    'phpManufaktur\Event\Control\Command\Edit::controllerCheck');
$app->post('/event/frontend/edit',
    'phpManufaktur\Event\Control\Command\Edit::controllerEditEvent');
$app->post('/event/frontend/edit/check',
    'phpManufaktur\Event\Control\Command\Edit::controllerEditEventCheck');

$app->match('/event/frontend/login',
    'phpManufaktur\Event\Control\Command\Edit::controllerLogin');
$app->post('/event/frontend/login/check',
    'phpManufaktur\Event\Control\Command\Edit::controllerLoginCheck');

$app->get('/event/frontend/account/select/event/{event_id}/redirect/{redirect}',
    'phpManufaktur\Event\Control\Command\Edit::controllerSelectAccount');
$app->post('/event/frontend/account/select/check',
    'phpManufaktur\Event\Control\Command\Edit::controllerSelectAccountCheck');

$app->get('/event/frontend/edit/account/activate/{guid}',
    'phpManufaktur\Event\Control\Command\Edit::controllerActivateRole');
$app->get('/event/frontend/edit/account/reject/{guid}',
    'phpManufaktur\Event\Control\Command\Edit::controllerRejectRole');
$app->get('/event/frontend/edit/account/password',
    'phpManufaktur\Event\Control\Command\Edit::controllerNewPasswordDialog');
$app->post('/event/frontend/edit/account/password/check',
    'phpManufaktur\Event\Control\Command\Edit::controllerNewPasswordCheck');
