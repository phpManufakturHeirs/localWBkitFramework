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

namespace phpManufaktur\Basic\Control\kitFilter;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class kitFilter
{
    public function exec(Application $app, $filter, $params=null)
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
            if (isset($cms_parameter['parameter']['help'])) {
                // get the help function for this kitCommand
                $subRequest = Request::create("/command/help?command=filter:$filter", 'POST', $cms_parameter);
                // important: we dont want that app->handle() catch errors, so set the third parameter to false!
                return $app->handle($subRequest, HttpKernelInterface::SUB_REQUEST, false);
            }
            else {
                $subRequest = Request::create('/filter/'.$filter, 'POST', $cms_parameter);
                // important: we dont want that app->handle() catch errors, so set the third parameter to false!
                return $app->handle($subRequest, HttpKernelInterface::SUB_REQUEST, false);
            }

        } catch (\Exception $e) {

            // always report problems!
            $app['monolog']->addError($e, array($e->getFile(), $e->getLine()));

            if (isset($cms_parameter['cms']['locale'])) {
                // set the locale given by the CMS
                $app['locale'] = $cms_parameter['cms']['locale'];
            }
            if (isset($cms_parameter['parameter']['debug']) && ((strtolower($cms_parameter['parameter']['debug']) == 'true') ||
                ($cms_parameter['parameter']['debug'] == 1) || ($cms_parameter['parameter']['debug'] == '')) &&
                isset($cms_parameter['filter_expression']) && isset($cms_parameter['content'])) {
                // the debug parameter isset, so return the extended error information
                $debug = array(
                    'filter' => $filter,
                    'file' => substr($e->getFile(), strlen(FRAMEWORK_PATH)),
                    'line' => $e->getLine(),
                    'message' => $e->getMessage()
                );
                $result = $app['twig']->render($app['utils']->getTemplateFile(
                    '@phpManufaktur/Basic/Template',
                    'kitfilter/debug.twig'),
                    array('debug' => $debug));
                return str_replace($cms_parameter['filter_expression'], $result, $cms_parameter['content']);
            }
            elseif (isset($cms_parameter['filter_expression']) && isset($cms_parameter['content'])) {
                $result = $app['twig']->render($app['utils']->getTemplateFile(
                    '@phpManufaktur/Basic/Template',
                    'kitfilter/error.twig'),
                    array('filter' => $filter));
                return str_replace($cms_parameter['filter_expression'], $result, $cms_parameter['content']);
            }
            else {
                return $app['twig']->render($app['utils']->getTemplateFile(
                    '@phpManufaktur/Basic/Template',
                    'kitfilter/error.twig'),
                    array('filter' => $filter));
            }
        }
    }
}
