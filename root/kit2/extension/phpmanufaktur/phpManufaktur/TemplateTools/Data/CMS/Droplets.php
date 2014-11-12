<?php

/**
 * TemplateTools
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de/TemplateTools
 * @copyright 2014 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\TemplateTools\Data\CMS;

use Silex\Application;

class Droplets
{
    protected $app = null;

    /**
     * Constructor
     *
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * Select the CODE for the given Droplet
     *
     * @param string $droplet
     * @throws \Exception
     * @return Ambigous <boolean, string>
     */
    public function selectCode($droplet)
    {
        try {
            $SQL = "SELECT `code` FROM `".CMS_TABLE_PREFIX."mod_droplets` WHERE `name`='$droplet'";
            $code = $this->app['db']->fetchColumn($SQL);
            return (!empty($code)) ? $code : false;
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }
}
