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
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpFoundation\Request;

class Action extends Basic
{
    protected $Message = null;

    protected function initParameters(Application $app, $parameter_id=-1)
    {
        parent::initParameters($app, $parameter_id);
        $this->Message = new Message($app);
    }

    /**
     * Action handler for the kitCommand ~~ event ~~
     *
     * @param Application $app
     * @throws \Exception
     * @return string dialog or result
     */
    public function exec(Application $app)
    {
        try {
            $this->initParameters($app);
            // get the kitCommand parameters
            $parameters = $this->getCommandParameters();

            // check the CMS GET parameters
            $GET = $this->getCMSgetParameters();
            if (isset($GET['command']) && ($GET['command'] == 'event')) {
                foreach ($GET as $key => $value) {
                    if ($key == 'command') continue;
                    $parameters[$key] = $value;
                }
                $this->setCommandParameters($parameters);
            }
            if (!isset($parameters['action'])) {
                // there is no 'mode' parameter set, so we show the "Welcome" page
                $subRequest = Request::create('/basic/help/event/welcome', 'GET');
                return $app->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
            }

            // get the config file
            $config = $app['utils']->readConfiguration(MANUFAKTUR_PATH.'/Event/config.event.json');

            if (!isset($config['permalink']['cms']['url']) || empty($config['permalink']['cms']['url'])) {
                // missing the URL for permanent links and responses
                $message = 'Please define a permanent link in config.event.json. Without this link Event can not create permanent links or respond to user requests.';
                $this->setMessage($message);
                $app['monolog']->addError("kfEvent: $message");
            }

            switch ($parameters['action']) {
                case 'event':
                    $Event = new Event();
                    return $Event->exec($app);
                case 'list':
                    $List = new EventList();
                    return $List->exec($app);
                case 'propose':
                    $Propose = new Propose();
                    return $Propose->exec($app);
                case 'search':
                    $Search = new EventSearch();
                    return $Search->controllerDialog($app);
                case 'config':
                    $EventConfig = new EventConfig();
                    return $EventConfig->exec($app);
                default:
                    return $this->Message->render('The action <b>%action%</b> is unknown, please check the parameters for the kitCommand!',
                        array('%action%' => $parameters['action']));
            }
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }

}
