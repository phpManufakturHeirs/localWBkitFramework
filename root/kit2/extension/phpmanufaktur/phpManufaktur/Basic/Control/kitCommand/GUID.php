<?php

namespace phpManufaktur\Basic\Control\kitCommand;

use phpManufaktur\Basic\Control\kitCommand\Basic;
use Silex\Application;

class GUID extends Basic
{

    public function ControllerGUID(Application $app)
    {
        $this->initParameters($app);

        $this->setFrameAdd(0);

        return $app['twig']->render($app['utils']->getTemplateFile(
            '@phpManufaktur/Basic/Template', 'kitcommand/bootstrap/guid.twig'),
            array(
                'guid' => $app['utils']->createGUID(),
                'basic' => $this->getBasicSettings()
        ));
    }

    public function ControllerCreateIFrame(Application $app)
    {
        $this->initParameters($app);
        return $this->createIFrame('/basic/guid');
    }
}
