<?php

/**
 * miniShop
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de/miniShop
 * @copyright 2014 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

use phpManufaktur\Basic\Control\CMS\EmbeddedAdministration;

// grant the ROLE hierarchy for the miniShop ROLES
$roles = $app['security.role_hierarchy'];
if (!in_array('ROLE_MINISHOP_ADMIN', $roles)) {
    $roles['ROLE_ADMIN'][] = 'ROLE_MINISHOP_ADMIN';
    $roles['ROLE_MINISHOP_ADMIN'][] = 'ROLE_MEDIABROWSER_ADMIN';
    $app['security.role_hierarchy'] = $roles;
}

// add a access point for miniShop
$entry_points = $app['security.role_entry_points'];
$entry_points['ROLE_ADMIN'][] = array(
    'route' => '/admin/minishop',
    'name' => 'miniShop',
    'info' => $app['translator']->trans('miniShop for the kitFramework'),
    'icon' => array(
        'path' => '/extension/phpmanufaktur/phpManufaktur/miniShop/extension.jpg',
        'url' => MANUFAKTUR_URL.'/miniShop/extension.jpg'
    )
);
$app['security.role_entry_points'] = $entry_points;

// add all ROLES provided and used by the miniShop
$roles = array(
    'ROLE_MINISHOP_ADMIN'
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

/**
 * Use the EmbeddedAdministration feature to connect the extension with the CMS
 *
 * @link https://github.com/phpManufaktur/kitFramework/wiki/Extensions-%23-Embedded-Administration
 */
$app->get('/minishop/cms/{cms_information}', function ($cms_information) use ($app) {
    $administration = new EmbeddedAdministration($app);
    return $administration->route('/admin/minishop', $cms_information);
});

/**
 * The PermanentLink for miniShop uses configurable routes.
 * Setup will create the needed directories in the CMS root and place a
 * .htaccess file which redirect to the routes.
 */
if (file_exists(MANUFAKTUR_PATH.'/miniShop/bootstrap.include.inc')) {
    // the PermanentLink routes must exists and will be created by the setup routine!
    include_once MANUFAKTUR_PATH.'/miniShop/bootstrap.include.inc';
}
else {
    $app['monolog']->addError('Missing the permanent link routes in /miniShop/bootstrap.include.inc!',
        array(__FILE__, __LINE__));
}

/**
 * ADMIN routes
 */

$admin->get('/minishop/setup',
    'phpManufaktur\miniShop\Data\Setup\Setup::Controller');
$admin->get('/minishop/update',
    'phpManufaktur\miniShop\Data\Setup\Update::Controller');
$admin->get('/minishop/uninstall',
    'phpManufaktur\miniShop\Data\Setup\Uninstall::Controller');


$app->get('/admin/minishop',
    'phpManufaktur\miniShop\Control\Admin\Admin::ControllerSelectDefaultTab');
$app->get('/admin/minishop/about',
    'phpManufaktur\miniShop\Control\Admin\About::Controller');

$app->get('/admin/minishop/base/list',
    'phpManufaktur\miniShop\Control\Admin\Base::Controller');
$app->get('/admin/minishop/base/edit/id/{base_id}',
    'phpManufaktur\miniShop\Control\Admin\Base::ControllerEdit')
    ->assert('base_id', '\d+')
    ->value('base_id', -1);
$app->post('/admin/minishop/base/edit/check',
    'phpManufaktur\miniShop\Control\Admin\Base::ControllerEditCheck');

$app->get('/admin/minishop/payment/advance_payment/config',
    'phpManufaktur\miniShop\Control\Payment\AdvancePayment::ControllerConfig');
$app->post('/admin/minishop/payment/advance_payment/config/check',
    'phpManufaktur\miniShop\Control\Payment\AdvancePayment::ControllerConfigCheck');

$app->get('/admin/minishop/payment/paypal/config',
    'phpManufaktur\miniShop\Control\Payment\PayPal::ControllerConfig');
$app->post('/admin/minishop/payment/paypal/config/check',
    'phpManufaktur\miniShop\Control\Payment\PayPal::ControllerConfigCheck');

$app->get('/admin/minishop/group/list',
    'phpManufaktur\miniShop\Control\Admin\Group::Controller');
$app->get('/admin/minishop/group/edit/id/{group_id}',
    'phpManufaktur\miniShop\Control\Admin\Group::ControllerEdit')
    ->assert('group_id', '\d+')
    ->value('group_id', -1);
$app->post('/admin/minishop/group/edit/check',
    'phpManufaktur\miniShop\Control\Admin\Group::ControllerEditCheck');

$app->get('/admin/minishop/article/list',
    'phpManufaktur\miniShop\Control\Admin\Article::Controller');
$app->get('/admin/minishop/article/edit/id/{article_id}',
    'phpManufaktur\miniShop\Control\Admin\Article::ControllerEdit')
    ->assert('article_id', '\d+')
    ->value('article_id', -1);
$app->post('/admin/minishop/article/edit/check',
    'phpManufaktur\miniShop\Control\Admin\Article::ControllerEditCheck');

$app->post('/admin/minishop/article/edit/permalink/create',
    'phpManufaktur\miniShop\Control\Admin\PermanentLinkResponse::ControllerPermanentLink');

$app->post('/admin/minishop/article/image/select',
    'phpManufaktur\miniShop\Control\Admin\Article::ControllerImageSelect');
$app->get('/admin/minishop/article/image/check/id/{article_id}',
    'phpManufaktur\miniShop\Control\Admin\Article::ControllerImageCheck')
    ->assert('article_id', '\d+');
$app->post('/admin/minishop/article/image/remove/id/{article_id}',
    'phpManufaktur\miniShop\Control\Admin\Article::ControllerImageRemove')
    ->assert('article_id', '\d+');

$app->get('/admin/minishop/order/list',
    'phpManufaktur\miniShop\Control\Admin\Order::Controller');
$app->get('/admin/minishop/order/view/id/{order_id}',
    'phpManufaktur\miniShop\Control\Admin\Order::ControllerView')
    ->assert('order_id', '\d+');

// handling the contacts in miniShop ...
$app->match('/admin/minishop/contact/list',
    'phpManufaktur\miniShop\Control\Admin\ContactList::Controller');
$app->match('/admin/minishop/contact/list/page/{page}',
    'phpManufaktur\miniShop\Control\Admin\ContactList::Controller')
    ->assert('page', '\d+');
$app->match('/admin/minishop/contact/search',
    'phpManufaktur\miniShop\Control\Admin\ContactSearch::Controller');
$app->match('/admin/minishop/contact/select',
    'phpManufaktur\miniShop\Control\Admin\ContactSelect::Controller');
$app->match('/admin/minishop/contact/edit/id/{contact_id}',
    'phpManufaktur\miniShop\Control\Admin\ContactSelect::Controller')
    ->assert('contact_id', '\d+');
$app->match('/admin/minishop/contact/person/edit',
    'phpManufaktur\miniShop\Control\Admin\ContactPerson::Controller');
$app->match('/admin/minishop/contact/person/edit/id/{contact_id}',
    'phpManufaktur\miniShop\Control\Admin\ContactPerson::Controller')
    ->assert('contact_id', '\d+');
$app->match('/admin/minishop/contact/company/edit',
    'phpManufaktur\miniShop\Control\Admin\ContactCompany::Controller');
$app->match('/admin/minishop/contact/company/edit/id/{contact_id}',
    'phpManufaktur\miniShop\Control\Admin\ContactCompany::Controller')
    ->assert('contact_id', '\d+');
$app->match('/admin/minishop/contact/category/list',
    'phpManufaktur\miniShop\Control\Admin\Contact\CategoryList::Controller');
$app->match('/admin/minishop/contact/category/edit',
    'phpManufaktur\miniShop\Control\Admin\Contact\CategoryEdit::Controller');
$app->match('/admin/minishop/contact/category/edit/id/{category_id}',
    'phpManufaktur\miniShop\Control\Admin\Contact\CategoryEdit::Controller')
    ->assert('category_id', '\d+');
$app->match('/admin/minishop/contact/extra/list',
    'phpManufaktur\miniShop\Control\Admin\Contact\ExtraList::Controller');
$app->match('/admin/minishop/contact/extra/edit',
    'phpManufaktur\miniShop\Control\Admin\Contact\ExtraEdit::Controller');
$app->match('/admin/minishop/contact/extra/edit/id/{type_id}',
    'phpManufaktur\miniShop\Control\Admin\Contact\ExtraEdit::Controller')
    ->assert('type_id', '\d+');
$app->match('/admin/minishop/contact/title/list',
    'phpManufaktur\miniShop\Control\Admin\Contact\TitleList::Controller');
$app->match('/admin/minishop/contact/title/edit',
    'phpManufaktur\miniShop\Control\Admin\Contact\TitleEdit::Controller');
$app->match('/admin/minishop/contact/title/edit/id/{title_id}',
    'phpManufaktur\miniShop\Control\Admin\Contact\TitleEdit::Controller')
    ->assert('title_id', '\d+');
$app->match('/admin/minishop/contact/tag/list',
    'phpManufaktur\miniShop\Control\Admin\Contact\TagList::Controller');
$app->match('/admin/minishop/contact/tag/edit',
    'phpManufaktur\miniShop\Control\Admin\Contact\TagEdit::Controller');
$app->match('/admin/minishop/contact/tag/edit/id/{tag_id}',
    'phpManufaktur\miniShop\Control\Admin\Contact\TagEdit::Controller')
    ->assert('tag_id', '\d+');

/**
 * kitCommand routes
 */

$app->post('/command/minishop',
    // create the iFrame for the kitCommands and execute the route /content/action
    'phpManufaktur\miniShop\Control\Command\ContentFrame::Controller')
    ->setOption('info', MANUFAKTUR_PATH.'/miniShop/command.minishop.json');

$app->post('/command/minishop/getheader/id/{article_id}',
    // return header information to set title, description and keywords
    // will be accessed by \Basic\Control\kitCommand\Parser::setHeader
    'phpManufaktur\miniShop\Control\Command\getHeader::Controller')
    ->assert('article_id', '\d+');
$app->post('/command/minishop/canonical/id/{article_id}',
    // return the permanent link URL of the given article ID to create a canonical link
    // will be accessed by \Basic\Control\kitCommand\Parser::setCanonicalLink
    'phpManufaktur\miniShop\Control\Command\getCanonicalLink::Controller')
    ->assert('article_id', '\d+');

$app->get('/minishop/action',
    'phpManufaktur\miniShop\Control\Command\Action::Controller');

$app->post('/command/minishop_basket',
    'phpManufaktur\miniShop\Control\Command\ContentFrame::ControllerBasket');

$app->get('/minishop/basket',
    'phpManufaktur\miniShop\Control\Command\Action::ControllerBasket');
$app->post('/minishop/basket/add',
    'phpManufaktur\miniShop\Control\Command\ActionBasket::ControllerBasketControlAdd');
$app->get('/minishop/order',
    'phpManufaktur\miniShop\Control\Command\Order::ControllerContactType');
$app->post('/minishop/order/check/type',
    'phpManufaktur\miniShop\Control\Command\Order::ControllerContactTypeCheck');
$app->post('/minishop/order/check/contact',
    'phpManufaktur\miniShop\Control\Command\Order::ControllerContactCheck');

$app->post('/minishop/paypal/ipn/{order_id}',
    'phpManufaktur\miniShop\Control\Payment\PayPal::ControllerIPN')
    ->assert('order_id', '\d+');
