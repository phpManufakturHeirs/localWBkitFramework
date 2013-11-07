<?php

/**
 * kitFramework::kfBasic
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de
 * @copyright 2013 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\Basic\Control\kitSearch;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class Search
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

            $subRequest = Request::create('/search/command/'.$command, 'POST', $cms_parameter);
            // important: we dont want that app->handle() catch errors, so set the third parameter to false!
            $result = $app->handle($subRequest, HttpKernelInterface::SUB_REQUEST, false);
        } catch (\Exception $e) {
            // no search for this kitCommand found or error while executing
            $result = array(
                'search' => array(
                    'success' => false,
                    'text' => $e->getMessage()
                )
            );
            $result = base64_encode(json_encode($result));
        }
        return $result;
    }
}
