<?php

/**
 * Basic
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de
 * @copyright 2014 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\Basic\Data\CMS\WebsiteBaker;

use Silex\Application;

class Settings
{
    protected $app = null;
    protected static $table_name = null;

    /**
     * Constructor
     *
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
        self::$table_name = CMS_TABLE_PREFIX.'settings';
    }

    /**
     * Get a setting from the CMS
     *
     * @param string $name of the setting
     * @throws \Exception
     * @return string value of the setting
     */
    public function getSetting($name)
    {
        try {
            $SQL = "SELECT `value` FROM `".self::$table_name."` WHERE `name`='$name'";
            return $this->app['db']->fetchColumn($SQL);
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }
}
