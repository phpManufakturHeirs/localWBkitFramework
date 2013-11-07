<?php

/**
 * HelloWorld
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de
 * @copyright 2013 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

use thirdParty\HelloWorld\Control\HelloObject;
use thirdParty\HelloWorld\Control\HelloBasic;
use phpManufaktur\Basic\Control\kitCommand\Basic as kitCommandBasic;
use thirdParty\HelloWorld\Data\HelloWorld as HelloWorldData;

// scan the /Locale directory and add all available languages
$app['utils']->addLanguageFiles(THIRDPARTY_PATH.'/HelloWorld/Data/Locale');
// scan the /Locale/Custom directory and add all available languages
$app['utils']->addLanguageFiles(THIRDPARTY_PATH.'/HelloWorld/Data/Locale/Custom');

// setup, update and uninstall of "HelloWorld"
$admin->get('/helloworld/setup', function() use($app) {
    $HelloWorldData = new HelloWorldData($app);
    $HelloWorldData->createTable();
    return $app['translator']->trans('Successfull installed the extension %extension%.',
        array('%extension%' => 'HelloWorld'));
});
$admin->get('/helloworld/update', function() use($app) {
    // nothing to do, just return the message
    return $app['translator']->trans('Successfull updated the extension %extension%.',
        array('%extension%' => 'HelloWorld'));
});
$admin->get('/helloworld/uninstall', function() use($app) {
    $HelloWorldData = new HelloWorldData($app);
    $HelloWorldData->dropTable();
    return $app['translator']->trans('Successfull uninstalled the extension %extension%.',
        array('%extension%' => 'HelloWorld'));
});

/**
 * "Hello World"
 */
$app->post('/command/helloworld', function ()
{
    return 'Hello World!';
})
->setOption('info', THIRDPARTY_PATH.'/HelloWorld/command.helloworld.json');

/**
 * "Hello World" directly from kitFramework
 */
$app->get('/helloworld', function ()
{
    return 'Hello World!';
});

/**
 * Protected "Hello World!"
 */
$app->get('/admin/helloworld', function() {
    return 'Hello World!';
});

/**
 * The kitCommand HelloUser show the usage of parameters
 */
$app->post('/command/hellouser', function() use($app) {
    $parameter = $app['request']->request->get('parameter');
    if (isset($parameter['name']) && !empty($parameter['name'])) {
        return sprintf('Hello %s!', $parameter['name']);
    }
    else {
        return 'Please use the parameter "name[]" in the kitCommand to tell me your name!';
    }
});

/**
 * Sample: CMSinfo
 */
$app->post('/command/helloinfo', function() use($app) {
    // start output buffer
    ob_start();
    // get the CMS parameters
    $cms = $app['request']->request->get('cms');
    echo "<pre>";
    print_r($cms);
    echo "</pre>";
    // return the buffer content and clean the output buffer
    return ob_get_clean();
});


/**
 * Use an object for the handling
 */
$app->post('/command/helloobject', function () {
    $HelloObject = new HelloObject();
    return $HelloObject->SayHello();
})
->setOption('info', THIRDPARTY_PATH.'/HelloWorld/command.helloobject.json');


/**
 * HelloBasic
 *
 * Use Class kitCommand\Basic and the template engine Twig to display some
 * information about the used content management system
 */
$app->post('/command/hellobasic', function() use ($app) {
    $HelloBasic = new HelloBasic($app);
    return $HelloBasic->exec();
});

/**
 * Use the function createIFrame() of class kitCommand\Basic to create a iframe
 * which will contain the response of the kitCommand. The iframe source point to
 * a route of the kitFramework.
 */
$app->post('/command/helloiframe', function () use ($app) {
    $kitCommand = new kitCommandBasic($app);
    return $kitCommand->createIFrame('/helloworld/iframe/start');
});

/**
 * The steps to the different dialogs of the HelloIFrame form
 */
$app->match('/helloworld/iframe/start',       'thirdParty\HelloWorld\Control\HelloIFrame::start');
$app->match('/helloworld/iframe/step02',      'thirdParty\HelloWorld\Control\HelloIFrame::step02');
$app->match('/helloworld/iframe/step03',      'thirdParty\HelloWorld\Control\HelloIFrame::step03');
$app->match('/helloworld/iframe/step04/{id}', 'thirdParty\HelloWorld\Control\HelloIFrame::step04');


$app->match('/command/hellositemodified', 'thirdParty\HelloWorld\Control\HelloSiteModified::exec')
->setOption('info', THIRDPARTY_PATH.'/HelloWorld/command.hellositemodified.json');


