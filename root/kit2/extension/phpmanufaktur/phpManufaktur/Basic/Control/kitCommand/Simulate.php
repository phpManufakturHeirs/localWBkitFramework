<?php

namespace phpManufaktur\Basic\Control\kitCommand;

use phpManufaktur\Basic\Control\kitCommand\Basic;
use Silex\Application;

class Simulate extends Basic
{
    protected static $parameter = null;

    /**
     * Prompt the given kitCommand expression
     *
     * @param Application $app
     */
    public function ControllerSimulateCopy(Application $app)
    {
        $this->initParameters($app);

        $this->setFrameAdd(4);

        $parameter = $this->getCommandParameters();
        if (!isset($parameter['expression'])) {
            $this->setAlert('Missing the parameter "expression"!');
        }

        return $app['twig']->render($app['utils']->getTemplateFile(
            '@phpManufaktur/Basic/Template', 'kitcommand/bootstrap/simulate.copy.twig'),
            array(
                'parameter' => $parameter,
                'basic' => $this->getBasicSettings()
        ));
    }

    protected function SimulateSimple()
    {
        return $this->app['twig']->render($this->app['utils']->getTemplateFile(
            '@phpManufaktur/Basic/Template', 'kitcommand/simulate.twig'),
            array(
                'parameter' => self::$parameter,
                'basic' => $this->getBasicSettings()
            ));
    }

    /**
     * Create an iFrame for the kitCommand response
     *
     * @param Application $app
     */
    public function ControllerCreateIFrame(Application $app)
    {
        $this->initParameters($app);
        self::$parameter = $this->getCommandParameters();

        if (isset(self::$parameter['action']) && (strtolower(self::$parameter['action'] == 'copy'))) {
            return $this->createIFrame('/basic/simulate/copy');
        }
        else {
            return $this->SimulateSimple();
        }
    }
}
