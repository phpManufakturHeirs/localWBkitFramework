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

// scan the /Locale directory and add all available languages
$app['utils']->addLanguageFiles(MANUFAKTUR_PATH.'/FacebookGallery/Data/Locale');
// scan the /Locale/Custom directory and add all available languages
$app['utils']->addLanguageFiles(MANUFAKTUR_PATH.'/FacebookGallery/Data/Locale/Custom');

$app->post('/command/facebookgallery', function() use ($app) {
    // init basic kitCommand
    $kitCommand = new kitCommandBasic($app);
    return $kitCommand->createIFrame('/facebookgallery/exec');
})
->setOption('info', MANUFAKTUR_PATH.'/FacebookGallery/command.facebookgallery.json');

// execute the general FacebookGallery class
$app->match('/facebookgallery/exec', 'phpManufaktur\FacebookGallery\Control\Gallery::exec');

