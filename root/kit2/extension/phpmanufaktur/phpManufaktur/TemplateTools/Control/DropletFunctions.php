<?php

/**
 * TemplateTools
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de/TemplateTools
 * @copyright 2014 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\TemplateTools\Control;

use Silex\Application;
use phpManufaktur\TemplateTools\Data\CMS\Droplets as DropletData;

class DropletFunctions
{
    protected $app = null;
    protected $DropletData = null;

    /**
     * Constructor
     *
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
        $this->DropletData = new DropletData($app);
    }

    /**
     * Execute the Droplet PHP code
     *
     * @param string $code
     * @param array $parameter
     * @return string
     */
    protected function doEval($code, $parameter)
    {
        extract($parameter, EXTR_SKIP);
        if (false === ($result = eval($code))) {
            return $this->app['translator']->trans('A error occured while executing the Droplet, please check the PHP code.');
        }
        return $result;
    }

    /**
     * Execute a Droplet
     *
     * @param string $droplet
     * @param array $parameter
     * @param boolean $prompt
     * @return string
     */
    public function execute($droplet, $parameter=array(), $prompt=true)
    {
        if (false !== ($code = $this->DropletData->selectCode($droplet))) {
            // execute the droplet code
            $result = $this->doEval($code, $parameter);
        }
        else {
            // the Droplet does not exists
            $result = $this->app['translator']->trans('The Droplet %droplet% does not exists!',
                array('%droplet%' => $droplet));
        }

        if ($prompt) {
            echo $result;
        }
        else {
            return $result;
        }
    }
}
