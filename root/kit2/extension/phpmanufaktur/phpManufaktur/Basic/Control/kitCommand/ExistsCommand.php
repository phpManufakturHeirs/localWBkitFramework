<?php

/**
 * kitFramework::kfBasic
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de
 * @copyright 2013 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\Basic\Control\kitCommand;

use Silex\Application;

class ExistsCommand
{
    /**
     * Check if the given command exists
     *
     * @param Application $app
     * @param string $command
     */
    public function ControllerExistsCommand(Application $app, $command)
    {
        $command_exists = false;
        $patterns = $app['routes']->getIterator();
        // walk through the routing objects
        foreach ($patterns as $pattern) {
            $match = $pattern->getPattern();
            // we are searching for all matching kitCommands, starting with '/command/'
            if ((strpos($match, '/command/') !== false) && (strpos($match, '/command/') == 0)) {
                $item = substr($match, strlen('/command/'));
                if (!isset($item[0]) || ($item[0] == '{')) {
                    // skip subrouting ...
                    continue;
                }
                if (strpos($item, '/{')) {
                    // remove additional parameter enclosures
                    $item = substr($item, 0, strpos($item, '/{'));
                }
                if ($item == strtolower($command)) {
                    $command_exists = true;
                    break;
                }
            }
        }
        // return a JSON response
        return $app->json(array('command_exists' => $command_exists), 200);
    }
}
