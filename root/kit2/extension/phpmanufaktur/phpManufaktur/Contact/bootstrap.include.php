<?php

/**
 * Contact
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de/contact
 * @copyright 2013 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

// not really needed but make syntax control more easy ...
global $app;

use phpManufaktur\Basic\Control\CMS\EmbeddedAdministration;
use phpManufaktur\Contact\Control\Contact;

// share the CONTACT CONTROL
$app['contact'] = $app->share(function($app) {
    return new Contact($app);
});

// scan the /Locale directory and add all available languages
$app['utils']->addLanguageFiles(MANUFAKTUR_PATH.'/Contact/Data/Locale');
// scan the /Locale/Custom directory and add all available languages
$app['utils']->addLanguageFiles(MANUFAKTUR_PATH.'/Contact/Data/Locale/Custom');

$app->get('/admin/contact/setup',
    // setup
    'phpManufaktur\Contact\Data\Setup\Setup::exec');
$app->get('/admin/contact/update',
    // update
    'phpManufaktur\Contact\Data\Setup\Update::exec');
$app->get('/admin/contact/uninstall',
    // uninstall
    'phpManufaktur\Contact\Data\Setup\Uninstall::exec');

$app->match('/admin/contact/simple/contact',
    // Simple Dialog: Select Contact
    'phpManufaktur\Contact\Control\Dialog\Simple\ContactSelect::controller');
$app->match('/admin/contact/simple/contact/id/{contact_id}',
    // Simple Dialog: Select specific Contact
    'phpManufaktur\Contact\Control\Dialog\Simple\ContactSelect::controller');

// Contact Person
$app->match('/admin/contact/simple/contact/person',
    'phpManufaktur\Contact\Control\Dialog\Simple\ContactPerson::controller');
$app->match('/admin/contact/simple/contact/person/id/{contact_id}',
    'phpManufaktur\Contact\Control\Dialog\Simple\ContactPerson::controller');

// Contact Company
$app->match('/admin/contact/simple/contact/company',
    'phpManufaktur\Contact\Control\Dialog\Simple\ContactCompany::controller');
$app->match('/admin/contact/simple/contact/company/id/{contact_id}',
    'phpManufaktur\Contact\Control\Dialog\Simple\ContactCompany::controller');

// Contact List
$app->match('/admin/contact/simple/contact/list',
    'phpManufaktur\Contact\Control\Dialog\Simple\ContactList::controller');
$app->match('/admin/contact/simple/contact/list/page/{page}',
    'phpManufaktur\Contact\Control\Dialog\Simple\ContactList::controller');

// Search
$app->match('/admin/contact/simple/search',
    'phpManufaktur\Contact\Control\Dialog\Simple\Search::controller');

// Edit Tags
$app->match('/admin/contact/simple/tag/edit',
    'phpManufaktur\Contact\Control\Dialog\Simple\TagEdit::controller');
$app->match('/admin/contact/simple/tag/edit/id/{tag_id}',
    'phpManufaktur\Contact\Control\Dialog\Simple\TagEdit::controller');

// Tag List
$app->match('/admin/contact/simple/tag/list',
    'phpManufaktur\Contact\Control\Dialog\Simple\TagList::controller');

// Category List
$app->match('/admin/contact/simple/category/list',
    'phpManufaktur\Contact\Control\Dialog\Simple\CategoryList::controller');

// Category Edit
$app->match('/admin/contact/simple/category/edit',
    'phpManufaktur\Contact\Control\Dialog\Simple\CategoryEdit::controller');
$app->match('/admin/contact/simple/category/edit/id/{category_type_id}',
    'phpManufaktur\Contact\Control\Dialog\Simple\CategoryEdit::controller');

// Title List
$app->match('/admin/contact/simple/title/list',
    'phpManufaktur\Contact\Control\Dialog\Simple\TitleList::controller');

// Title Edit
$app->match('/admin/contact/simple/title/edit',
    'phpManufaktur\Contact\Control\Dialog\Simple\TitleEdit:controller');
$app->match('/admin/contact/simple/title/edit/id/{title_id}',
    'phpManufaktur\Contact\Control\Dialog\Simple\TitleEdit:controller');

// Extra fields List
$app->match('/admin/contact/simple/extra/list',
    'phpManufaktur\Contact\Control\Dialog\Simple\ExtraFieldList::controller');

// Extra fields Edit
$app->match('/admin/contact/simple/extra/edit',
    'phpManufaktur\Contact\Control\Dialog\Simple\ExtraFieldEdit::controller');
$app->match('/admin/contact/simple/extra/edit/id/{type_id}',
    'phpManufaktur\Contact\Control\Dialog\Simple\ExtraFieldEdit::controller');

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
    return $administration->route('/admin/contact/backend/about', $cms_information);
});

$app->get('/admin/contact/backend/about',
    // about dialog
    'phpManufaktur\Contact\Control\Backend\About::exec');
$app->match('/admin/contact/backend/list',
    // contact list
    'phpManufaktur\Contact\Control\Backend\ContactList::controller');
$app->match('/admin/contact/backend/list/page/{page}',
    // contact list - select page
    'phpManufaktur\Contact\Control\Backend\ContactList::controller');
$app->match('/admin/contact/backend/company/edit',
    // create a company
    'phpManufaktur\Contact\Control\Backend\ContactCompany::controller');
$app->match('/admin/contact/backend/company/edit/id/{contact_id}',
    // edit a company
    'phpManufaktur\Contact\Control\Backend\ContactCompany::controller');
$app->match('/admin/contact/backend/person/edit',
    // create a person
    'phpManufaktur\Contact\Control\Backend\ContactPerson::controller');
$app->match('/admin/contact/backend/person/edit/id/{contact_id}',
    // edit a person
    'phpManufaktur\Contact\Control\Backend\ContactPerson::controller');
$app->match('/admin/contact/backend/search',
    // search
    'phpManufaktur\Contact\Control\Backend\ContactSearch::controller');
$app->match('/admin/contact/backend/select',
    // select a new contact
    'phpManufaktur\Contact\Control\Backend\ContactSelect::controller');
$app->match('/admin/contact/contact/backend/select/id/{contact_id}',
    // select a person or company contact
    'phpManufaktur\Contact\Control\Backend\ContactSelect::controller');
$app->match('/admin/contact/backend/category/list',
    // category list
    'phpManufaktur\Contact\Control\Backend\CategoryList::controller');
$app->match('/admin/contact/backend/category/create',
    // create a category
    'phpManufaktur\Contact\Control\Backend\CategoryEdit::controller');
$app->match('/admin/contact/backend/category/edit/id/{category_id}',
    // edit a category
    'phpManufaktur\Contact\Control\Backend\CategoryEdit::controller');
$app->match('/admin/contact/backend/extra/list',
    // extra fields List
    'phpManufaktur\Contact\Control\Backend\ExtraList::controller');
$app->match('/admin/contact/backend/extra/create',
    // create a extra field
    'phpManufaktur\Contact\Control\Backend\ExtraEdit::controller');
$app->match('/admin/contact/backend/extra/edit/id/{type_id}',
    // edit a extra field
    'phpManufaktur\Contact\Control\Backend\ExtraEdit::controller');
$app->match('/admin/contact/backend/tag/list',
    // tag list
    'phpManufaktur\Contact\Control\Backend\TagList::controller');
$app->match('/admin/contact/backend/tag/edit',
    // create a new tag
    'phpManufaktur\Contact\Control\Backend\TagEdit::controller');
$app->match('/admin/contact/backend/tag/edit/id/{tag_id}',
    // edit a tag
    'phpManufaktur\Contact\Control\Backend\TagEdit::controller');
$app->match('/admin/contact/backend/title/list',
    // title list
    'phpManufaktur\Contact\Control\Backend\TitleList::controller');
$app->match('/admin/contact/backend/title/create',
    // create a new title
    'phpManufaktur\Contact\Control\Backend\TitleEdit::controller');
$app->match('/admin/contact/backend/title/edit/id/{title_id}',
    'phpManufaktur\Contact\Control\Backend\TitleEdit::controller');
