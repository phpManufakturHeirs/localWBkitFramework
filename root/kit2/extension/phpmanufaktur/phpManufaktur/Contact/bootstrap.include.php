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

// scan the /Locale directory and add all available languages
$app['utils']->addLanguageFiles(MANUFAKTUR_PATH.'/Contact/Data/Locale');
// scan the /Locale/Custom directory and add all available languages
$app['utils']->addLanguageFiles(MANUFAKTUR_PATH.'/Contact/Data/Locale/Custom');

// Setup, Update and Uninstall
$app->get('/admin/contact/setup',
    'phpManufaktur\Contact\Data\Setup\Setup::exec');
$app->get('/admin/contact/update',
    'phpManufaktur\Contact\Data\Setup\Update::exec');
$app->get('/admin/contact/uninstall',
    'phpManufaktur\Contact\Data\Setup\Uninstall::exec');

// Select Contact
$app->match('/admin/contact/simple/contact',
    'phpManufaktur\Contact\Control\Dialog\Simple\ContactSelect::controller');
$app->match('/admin/contact/simple/contact/id/{contact_id}',
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
