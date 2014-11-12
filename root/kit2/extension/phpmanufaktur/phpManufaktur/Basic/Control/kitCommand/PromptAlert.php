<?php

/**
 * Basic
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de
 * @copyright 2013 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\Basic\Control\kitCommand;

use Silex\Application;
use phpManufaktur\Basic\Control\Pattern\Alert;

class PromptAlert extends Alert
{
    public function ControllerPromptAlert(Application $app, $alert)
    {
        $this->initialize($app);
        $this->setAlertUnformatted(base64_decode($alert));

        return $this->promptAlert();
    }
}
