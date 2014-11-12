<?php

/**
 * kitFramework::kfBasic
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de
 * @copyright 2013 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\Basic\Control\kitFilter;

use Silex\Application;

class ExistsFilter
{
    /**
     * Check if the given filter exists
     *
     * @param Application $app
     * @param string $filter
     */
    public function ControllerExistsFilter(Application $app, $filter)
    {
        $filter_exists = false;
        $patterns = $app['routes']->getIterator();
        // walk through the routing objects
        foreach ($patterns as $pattern) {
            $match = $pattern->getPattern();
            // we are searching for all matching kitFilters, starting with '/filter/'
            if ((strpos($match, '/filter/') !== false) && (strpos($match, '/filter/') == 0)) {
                    $item = substr($match, strlen('/filter/'));
                    if (!isset($item[0]) || ($item[0] == '{')) {
                        // skip subrouting ...
                        continue;
                    }
                    if (strpos($item, '/{')) {
                        // remove additional parameter enclosures
                        $item = substr($item, 0, strpos($item, '/{'));
                    }
                    if ($item == strtolower($filter)) {
                        $filter_exists = true;
                        break;
                    }
            }
        }
        // return a JSON response
        return $app->json(array('filter_exists' => $filter_exists), 200);
    }
}
