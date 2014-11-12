<?php

/**
 * FacebookGallery
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de/FacebookGallery
 * @copyright 2013 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

use phpManufaktur\Basic\Control\kitCommand\Basic as kitCommandBasic;

// admin routes
$app->get('admin/facebookgallery/setup',
    'phpManufaktur\FacebookGallery\Data\Setup\Setup::ControllerSetup');
$app->get('admin/facebookgallery/update',
    'phpManufaktur\FacebookGallery\Data\Setup\Update::ControllerUpdate');

// create the kitCommand iFrame for the FacebookGallery
$app->post('/command/facebookgallery',
    'phpManufaktur\FacebookGallery\Control\Gallery::ControllerCreateIFrame')
->setOption('info', MANUFAKTUR_PATH.'/FacebookGallery/command.facebookgallery.json');

// execute the FacebookGallery
$app->match('/facebookgallery',
    'phpManufaktur\FacebookGallery\Control\Gallery::ControllerFacebookGallery');

