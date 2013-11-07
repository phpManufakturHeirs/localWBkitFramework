<?php

/**
 * Event
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://addons.phpmanufaktur.de/event
 * @copyright 2013 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\Event\Control\Command;

use Silex\Application;
use phpManufaktur\Basic\Control\kitCommand\Basic;
use phpManufaktur\Event\Control\Configuration;

class EventConfig extends Basic
{

    public function exec(Application $app)
    {
        $this->initParameters($app);

        $Configuration = new Configuration($app);
        $config = $Configuration->getConfiguration();

        // init Event
        $parameters = $this->getCommandParameters();

        if (isset($parameters['set'])) {
            if (!strpos($parameters['set'], '=')) {
                $this->setMessage('Invalid key => value pair in the set[] parameter!');
            }
            else {
                list($key, $value) = explode('=', $parameters['set']);
                $key = trim($key);
                $value = trim($value);
                switch ($key) {
                    case 'permalink':
                        $config['permalink']['cms']['url'] = $value;
                        $Configuration->setConfiguration($config);
                        $Configuration->saveConfiguration();
                        $this->setMessage('Permalink successfull changed');
                        break;
                }
            }
        }
        else {
            $this->setMessage('Please use the parameter set[] to set a configuration value.');
        }

        return $app['twig']->render($app['utils']->getTemplateFile(
            '@phpManufaktur/Event/Template',
            "command/event.config.twig",
            $this->getPreferredTemplateStyle()),
            array(
                'basic' => $this->getBasicSettings(),
                'config' => $config,
            ));
    }
}
