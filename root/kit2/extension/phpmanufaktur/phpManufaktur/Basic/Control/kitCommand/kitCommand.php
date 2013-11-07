<?php

/**
 * kitFramework::Basic
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de
 * @copyright 2013 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 *
 */

namespace phpManufaktur\Basic\Control\kitCommand;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class kitCommand
{
    public function exec(Application $app, $command, $params=null)
    {
        try {
            if (!is_null($params)) {
                $cms_parameter = json_decode(base64_decode($params), true);
            }
            else {
                if (null === ($cms_parameter = $app['request']->get('cms_parameter', null))) {
                    throw new \Exception('Invalid kitCommand execution: missing the POST CMS parameter!');
                }
                if (!is_array($cms_parameter)) {
                    // we assume that the parameter value is base64 encoded
                    $cms_parameter = json_decode(base64_decode($cms_parameter), true);
                }
            }
            if (isset($cms_parameter['parameter']['cache']) && (($cms_parameter['parameter']['cache'] == '0') ||
                (strtolower($cms_parameter['parameter']['cache']) == 'false'))) {
                // clear the Twig cache
                $app['twig']->clearCacheFiles();
            }
            if (isset($cms_parameter['parameter']['help'])) {
                // get the help function for this kitCommand
                $subRequest = Request::create("/command/help?command=$command", 'POST', $cms_parameter);
                // important: we dont want that app->handle() catch errors, so set the third parameter to false!
                return $app->handle($subRequest, HttpKernelInterface::SUB_REQUEST, false);
            }
            else {
                $subRequest = Request::create('/command/'.$command, 'POST', $cms_parameter);
                // important: we dont want that app->handle() catch errors, so set the third parameter to false!
                return $app->handle($subRequest, HttpKernelInterface::SUB_REQUEST, false);
            }
        } catch (\Exception $e) {
            if (isset($cms_parameter['cms']['locale'])) {
                // set the locale given by the CMS
                $app['locale'] = $cms_parameter['cms']['locale'];
            }
            if (isset($cms_parameter['parameter']['debug']) && ((strtolower($cms_parameter['parameter']['debug']) == 'true') ||
                ($cms_parameter['parameter']['debug'] == 1) || ($cms_parameter['parameter']['debug'] == ''))) {
                // the debug parameter isset, so return the extended error information
                $debug = array(
                    'command' => $command,
                    'file' => substr($e->getFile(), strlen(FRAMEWORK_PATH)),
                    'line' => $e->getLine(),
                    'message' => $e->getMessage()
                );
                return $app['twig']->render($app['utils']->getTemplateFile(
                    '@phpManufaktur/Basic/Template',
                    'kitcommand/debug.twig'),
                    array('debug' => $debug));
            }
            else {
                // no debug parameter, we assume that the kitCommand does not exists
                return $app['twig']->render($app['utils']->getTemplateFile(
                    '@phpManufaktur/Basic/Template',
                    'kitcommand/error.twig'),
                    array('command' => $command));
            }
        }
    }
}
