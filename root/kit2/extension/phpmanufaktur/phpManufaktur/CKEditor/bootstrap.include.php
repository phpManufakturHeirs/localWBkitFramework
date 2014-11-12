<?php

/**
 * CKEditor extension for the kitFramework
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de
 * @copyright 2013 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */


// add the function CKEditor to Twig
$app['twig'] = $app->share($app->extend('twig', function ($twig, $app) {

    $twig->addFunction(new Twig_SimpleFunction('CKEditor', function(
        $id, $name, $content, $width='100%', $height='200px', $config='default', $directory='/media/public/', $directory_start='/media/public') use ($app) {
        //global $app;

        $framework_url = FRAMEWORK_URL;
        if ($app['account']->isAuthenticated()) {
            $image_url = FRAMEWORK_URL."/mediabrowser/cke?directory=$directory&directory_start=$directory_start";
        }
        else {
            // user is not authenticated, disable access to the mediabrowser!
            $image_url = '';
        }
        $config_url = ($config == 'default') ? MANUFAKTUR_URL.'/CKEditor/ckeditor.config.js' : $config;

        $extra_plugins = array('ajax','xml','cmspagelink');
        if ($app['filesystem']->exists(MANUFAKTUR_PATH.'/flexContent/extension.json')) {
            $extra_plugins[] = 'flexcontentlink';
            $extra_plugins[] = 'hashtaglink';
        }
        $extra_plugins_str = implode(',', $extra_plugins);

        return <<<EOD
        <textarea class="ckeditor" id="$id" name="$name">$content</textarea>
        <script>
          CKEDITOR.replace('$id', {
            width: '$width',
            height: '$height',
            customConfig: '$config_url',
            baseHref: '$framework_url',
            filebrowserImageBrowseUrl: '$image_url',
            filebrowserWindowWidth: 900,
            filebrowserWindowHeight: 600,
            extraPlugins: '$extra_plugins_str'
          });
        </script>
EOD;
        }));
    return $twig;

}));

// Dialog to link pages from the parent CMS
$app->get('/extension/phpmanufaktur/phpManufaktur/CKEditor/Source/plugins/cmspagelink/dialog',
    'phpManufaktur\CKEditor\Control\cmsPageLink::ControllerDialog');

if ($app['filesystem']->exists(MANUFAKTUR_PATH.'/flexContent/extension.json')) {
    // Dialog to link flexContent articles
    $app->get('/extension/phpmanufaktur/phpManufaktur/CKEditor/Source/plugins/flexcontentlink/dialog',
        'phpManufaktur\CKEditor\Control\flexContentLink::ControllerDialog');
    // Dialog to link flexContent #hashtags
    $app->get('/extension/phpmanufaktur/phpManufaktur/CKEditor/Source/plugins/hashtaglink/dialog',
        'phpManufaktur\CKEditor\Control\hashtagLink::ControllerDialog');
}
