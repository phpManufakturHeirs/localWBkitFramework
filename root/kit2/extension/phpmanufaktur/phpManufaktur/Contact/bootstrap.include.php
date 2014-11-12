<?php

/**
 * Contact
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de/Contact
 * @copyright 2013 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

use phpManufaktur\Basic\Control\CMS\EmbeddedAdministration;
use phpManufaktur\Contact\Control\Contact;

// grant the ROLE hierarchy for the CONTACT ROLES
$roles = $app['security.role_hierarchy'];
if (!in_array('ROLE_CONTACT_ADMIN', $roles)) {
    $roles['ROLE_ADMIN'][] = 'ROLE_CONTACT_ADMIN';
    $roles['ROLE_CONTACT_ADMIN'] = array(
        'ROLE_CONTACT_EDIT',
        'ROLE_CONTACT_EDIT_OWN'
    );
    $app['security.role_hierarchy'] = $roles;
}
if (!in_array('ROLE_CONTACT_EDIT', $roles)) {
    $roles['ROLE_CONTACT_EDIT'] = array(
        'ROLE_CONTACT_EDIT_OWN'
    );
    $app['security.role_hierarchy'] = $roles;
}

$rules = $app['security.access_rules'];
$rule_exists = false;
foreach ($rules as $rule) {
    if ($rule[0] === '^/contact/owner/edit') {
        $rule_exists = true;
    }
}
if (!$rule_exists) {
    $rules[] = array('^/contact/owner/edit', 'ROLE_CONTACT_EDIT_OWN');
    $rules[] = array('^/contact/edit', 'ROLE_CONTACT_EDIT');
    $app['security.access_rules'] = $rules;
}


// add a entry point for CONTACT
$entry_points = $app['security.role_entry_points'];
$entry_points['ROLE_ADMIN'][] = array(
    'route' => '/admin/contact/list',
    'name' => $app['translator']->trans('Contact'),
    'info' => $app['translator']->trans('Customer relationship management for the kitFramework'),
    'icon' => array(
        'path' => '/extension/phpmanufaktur/phpManufaktur/Contact/extension.jpg',
        'url' => MANUFAKTUR_URL.'/Contact/extension.jpg'
    )
);
$entry_points['ROLE_ADMIN'][] = array(
    'route' => '/admin/contact/export',
    'name' => $app['translator']->trans('Contact Export'),
    'info' => $app['translator']->trans('Export Contact records in CSV or Excel file format'),
    'icon' => array(
        'path' => '/extension/phpmanufaktur/phpManufaktur/Contact/extension.jpg',
        'url' => MANUFAKTUR_URL.'/Contact/extension.jpg'
    )
);
$entry_points['ROLE_ADMIN'][] = array(
    'route' => '/admin/contact/import',
    'name' => $app['translator']->trans('Contact Import'),
    'info' => $app['translator']->trans('Import address and contact records from KeepInTouch or as CSV, Excel or Open Data file format.'),
    'icon' => array(
        'path' => '/extension/phpmanufaktur/phpManufaktur/Contact/extension.jpg',
        'url' => MANUFAKTUR_URL.'/Contact/extension.jpg'
    )
);

$app['security.role_entry_points'] = $entry_points;

// add all ROLES provided and used by CONTACT
$roles = array(
    'ROLE_CONTACT_ADMIN',
    'ROLE_CONTACT_EDIT',
    'ROLE_CONTACT_EDIT_OWN'
);
$roles_provided = $app['security.roles_provided'];
if (!in_array($roles, $roles_provided)) {
    foreach ($roles as $role) {
        if (!in_array($role, $roles_provided)) {
            $roles_provided[] = $role;
        }
    }
    $app['security.roles_provided'] = $roles_provided;
}


// share the CONTACT CONTROL
$app['contact'] = $app->share(function($app) {
    return new Contact($app);
});

$app->get('/admin/contact/setup',
    // setup
    'phpManufaktur\Contact\Data\Setup\Setup::exec');
$app->get('/admin/contact/update',
    // update
    'phpManufaktur\Contact\Data\Setup\Update::exec');
$app->get('/admin/contact/uninstall',
    // uninstall
    'phpManufaktur\Contact\Data\Setup\Uninstall::exec');

// Export, selection dialog
$app->get('/admin/contact/export',
    'phpManufaktur\Contact\Control\Export\Controller::ControllerStart');
$app->post('/admin/contact/export/execute',
    'phpManufaktur\Contact\Control\Export\Controller::ControllerExecute');
// Export contact records
$app->post('/admin/contact/export/type/{type}',
    'phpManufaktur\Contact\Control\Export\Excel::ControllerExportType');
// Remove exported file
$app->get('/admin/contact/export/remove/{file}',
    'phpManufaktur\Contact\Control\Export\Excel::ControllerRemoveFile');

// Import, selection dialog
$app->get('/admin/contact/import',
    'phpManufaktur\Contact\Control\Import\Controller::Controller');
$app->post('/admin/contact/import/select',
    'phpManufaktur\Contact\Control\Import\Controller::ControllerSelect');
$app->post('/admin/contact/import/type/{type}',
    'phpManufaktur\Contact\Control\Import\Excel::ControllerType');
$app->post('/admin/contact/import/file/{type}',
    'phpManufaktur\Contact\Control\Import\Excel::ControllerFile');
$app->post('/admin/contact/import/execute',
    'phpManufaktur\Contact\Control\Import\Excel::ControllerExecute');

// Import from KeepInTouch
$app->match('/admin/contact/import/keepintouch',
    'phpManufaktur\Contact\Control\Import\KeepInTouch\KeepInTouch::start');
$app->match('/admin/contact/import/keepintouch/start',
    'phpManufaktur\Contact\Control\Import\KeepInTouch\KeepInTouch::start');
$app->match('/admin/contact/import/keepintouch/execute',
    'phpManufaktur\Contact\Control\Import\KeepInTouch\KeepInTouch::execute');

/**
 * Use the EmbeddedAdministration feature to connect the extension with the CMS
 *
 * @link https://github.com/phpManufaktur/kitFramework/wiki/Extensions-%23-Embedded-Administration
 */
$app->get('/contact/cms/{cms_information}', function ($cms_information) use ($app) {
    $administration = new EmbeddedAdministration($app);
    return $administration->route('/admin/contact/about', $cms_information);
});

$app->get('/admin/contact/about',
    'phpManufaktur\Contact\Control\Backend\About::exec');

$app->match('/admin/contact/list',
    'phpManufaktur\Contact\Control\Backend\ContactList::controller');
$app->match('/admin/contact/list/page/{page}',
    'phpManufaktur\Contact\Control\Backend\ContactList::controller');

$app->match('/admin/contact/company/edit',
    'phpManufaktur\Contact\Control\Backend\ContactCompany::Controller');
$app->match('/admin/contact/company/edit/id/{contact_id}',
    'phpManufaktur\Contact\Control\Backend\ContactCompany::Controller');
$app->match('/admin/contact/person/edit',
    'phpManufaktur\Contact\Control\Backend\ContactPerson::Controller');
$app->match('/admin/contact/person/edit/id/{contact_id}',
    'phpManufaktur\Contact\Control\Backend\ContactPerson::Controller');

$app->match('/admin/contact/search',
    'phpManufaktur\Contact\Control\Backend\ContactSearch::controller');

$app->match('/admin/contact/select',
    'phpManufaktur\Contact\Control\Backend\ContactSelect::controller');
$app->match('/admin/contact/select/id/{contact_id}',
    'phpManufaktur\Contact\Control\Backend\ContactSelect::controller');

$app->match('/admin/contact/category/list',
    'phpManufaktur\Contact\Control\Backend\CategoryList::controller');
$app->match('/admin/contact/category/create',
    'phpManufaktur\Contact\Control\Backend\CategoryEdit::controller');
$app->match('/admin/contact/category/edit/id/{category_id}',
    'phpManufaktur\Contact\Control\Backend\CategoryEdit::controller');

$app->match('/admin/contact/extra/list',
    'phpManufaktur\Contact\Control\Backend\ExtraList::controller');
$app->match('/admin/contact/extra/create',
    'phpManufaktur\Contact\Control\Backend\ExtraEdit::controller');
$app->match('/admin/contact/extra/edit/id/{type_id}',
    'phpManufaktur\Contact\Control\Backend\ExtraEdit::controller');

$app->match('/admin/contact/tag/list',
    'phpManufaktur\Contact\Control\Backend\TagList::controller');
$app->match('/admin/contact/tag/edit',
    'phpManufaktur\Contact\Control\Backend\TagEdit::controller');
$app->match('/admin/contact/tag/edit/id/{tag_id}',
    'phpManufaktur\Contact\Control\Backend\TagEdit::controller');

$app->match('/admin/contact/title/list',
    'phpManufaktur\Contact\Control\Backend\TitleList::controller');
$app->match('/admin/contact/title/edit',
    'phpManufaktur\Contact\Control\Backend\TitleEdit::controller');
$app->match('/admin/contact/title/edit/id/{title_id}',
    'phpManufaktur\Contact\Control\Backend\TitleEdit::controller');

/**
 * kitCommands
 */

$command->post('/contact',
    // the general action controller for all 'contact' kitCommands
    'phpManufaktur\Contact\Control\Command\Action::ControllerAction')
    ->setOption('info', MANUFAKTUR_PATH.'/Contact/command.contact.json');

// custom forms
$app->get('/contact/form',
    'phpManufaktur\Contact\Control\Command\Form::ControllerFormAction');
$app->post('/contact/form/check',
    'phpManufaktur\Contact\Control\Command\Form::ControllerFormAction');

// public contact list
$app->get('/contact/list',
    'phpManufaktur\Contact\Control\Command\ContactList::ControllerList');

// search for public contacts
$app->match('/contact/search',
    'phpManufaktur\Contact\Control\Command\ContactSearch::ControllerSearch');

// view a specific public contact
$app->get('/contact/view',
    'phpManufaktur\Contact\Control\Command\ContactView::ControllerView');

// register a public contact
$app->match('/contact/register',
    'phpManufaktur\Contact\Control\Command\ContactRegister::ControllerType');
$app->post('/contact/register/category/check',
    'phpManufaktur\Contact\Control\Command\ContactRegister::ControllerCategoryCheck');
$app->match('/contact/register/contact',
    'phpManufaktur\Contact\Control\Command\ContactRegister::ControllerContact');
$app->match('/contact/register/contact/check',
    'phpManufaktur\Contact\Control\Command\ContactRegister::ControllerContactCheck');
$app->get('/contact/register/activate/user/{guid}',
    'phpManufaktur\Contact\Control\Command\ContactRegister::ControllerRegisterActivation');
$app->get('contact/register/activate/admin/{guid}',
    'phpManufaktur\Contact\Control\Command\ContactRegister::ControllerRegisterActivationAdmin');
$app->get('contact/register/reject/admin/{guid}',
    'phpManufaktur\Contact\Control\Command\ContactRegister::ControllerRegisterRejectAdmin');

// protected area /contact/owner/edit - need ROLE_CONTACT_EDIT_OWN
$app->get('/contact/owner/login',
    'phpManufaktur\Contact\Control\Command\ContactEdit::ControllerLogin');
$app->post('/contact/owner/login/check',
    'phpManufaktur\Contact\Control\Command\ContactEdit::ControllerLoginCheck');

$app->get('/contact/owner/edit/id/{contact_id}',
    'phpManufaktur\Contact\Control\Command\ContactEdit::ControllerEdit');
$app->post('/contact/owner/edit/check',
    'phpManufaktur\Contact\Control\Command\ContactEdit::ControllerEditCheck');

// permanent link for public contact ID's
$app->get('/contact/public/view/id/{contact_id}',
    'phpManufaktur\Contact\Control\PermanentLink::ControllerPublicViewID');
