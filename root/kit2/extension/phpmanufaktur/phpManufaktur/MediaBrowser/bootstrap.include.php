<?php

/**
 * MediaBrowser
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de/MediaBrowser
 * @copyright 2013 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

// grant the ROLE hierarchy for the flexContent ROLES
$roles = $app['security.role_hierarchy'];
if (!in_array('ROLE_MEDIABROWSER_ADMIN', $roles)) {
    $roles['ROLE_ADMIN'][] = 'ROLE_MEDIABROWSER_ADMIN';
    $roles['ROLE_MEDIABROWSER_ADMIN'][] = 'ROLE_MEDIABROWSER_USER';
    $app['security.role_hierarchy'] = $roles;
}

// add a protected area and access rules
$access_rules = $app['security.access_rules'];
if (!in_array('^/mediabrowser', $access_rules)) {
    $access_rules[] = array('^/mediabrowser', 'ROLE_MEDIABROWSER_USER');
    $app['security.access_rules'] = $access_rules;
}

// add a access point for flexContent
$entry_points = $app['security.role_entry_points'];
if (!in_array('ROLE_MEDIABROWSER_USER', $entry_points)) {
    $entry_points['ROLE_MEDIABROWSER_USER'] = array(
        array(
            'route' => '/mediabrowser/entrypoints',
            'name' => 'MediaBrowser',
            'info' => 'Acces the public and protected Media resources',
            'icon' => array(
                'path' => '/extension/phpmanufaktur/phpManufaktur/MediaBrowser/extension.jpg',
                'url' => MANUFAKTUR_URL.'/MediaBrowser/extension.jpg'
            )
        )
    );
    $app['security.role_entry_points'] = $entry_points;
}

// add all ROLES provided and used by MediaBrowser
$roles = array(
    'ROLE_MEDIABROWSER_ADMIN',
    'ROLE_MEDIABROWSER_USER'
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


// main dialog - expect the parameters given as GET Request
$app->get('/mediabrowser',
    'phpManufaktur\MediaBrowser\Control\Browser::ControllerMediaBrowser');
// main dialog - special route for the Entry points
$app->get('/mediabrowser/entrypoints',
    'phpManufaktur\MediaBrowser\Control\Browser::ControllerEntryPoints');

// main dialog - get all parameters as encoded string
$app->get('/mediabrowser/init/{params}',
    'phpManufaktur\MediaBrowser\Control\Browser::ControllerMediaBrowserInit');

$app->get('/mediabrowser/delete/{delete}',
    'phpManufaktur\MediaBrowser\Control\Browser::ControllerMediaBrowserDelete');

$app->get('/mediabrowser/directory/{change}',
    'phpManufaktur\MediaBrowser\Control\Browser::ControllerMediaBrowserChangeDirectory');

$app->post('/mediabrowser/directory/create',
    'phpManufaktur\MediaBrowser\Control\Browser::ControllerMediaBrowserCreateDirectory');

$app->post('/mediabrowser/upload',
    'phpManufaktur\MediaBrowser\Control\Browser::ControllerMediaBrowserUpload');

$app->get('/mediabrowser/select/{select}',
    'phpManufaktur\MediaBrowser\Control\Browser::ControllerMediaBrowserSelect');

$app->get('/mediabrowser/exit/{usage}',
    'phpManufaktur\MediaBrowser\Control\Browser::ControllerMediaBrowserExit');

$app->get('/mediabrowser/cke',
    'phpManufaktur\MediaBrowser\Control\Browser::ControllerMediaBrowserCKE');
