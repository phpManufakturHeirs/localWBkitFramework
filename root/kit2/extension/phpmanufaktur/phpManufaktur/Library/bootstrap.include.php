<?php

/**
 * Library
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de/Library
 * @copyright 2014 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

$app->get('/admin/library/setup',
    'phpManufaktur\Library\Setup\Setup::ControllerSetup');
$app->get('/admin/library/update',
    'phpManufaktur\Library\Setup\Update::ControllerUpdate');

$app->post('/command/libraryinfo',
    'phpManufaktur\Library\Control\Command\LibraryInfo::ControllerLibraryFrame')
    ->setOption('info', MANUFAKTUR_PATH.'/Library/command.libraryinfo.json');

$app->get('/library/info',
    'phpManufaktur\Library\Control\Command\LibraryInfo::ControllerLibraryInfo');
