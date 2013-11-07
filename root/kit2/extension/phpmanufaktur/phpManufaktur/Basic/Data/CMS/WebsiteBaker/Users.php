<?php

/**
 * kitFramework::Basic
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de
 * @copyright 2013 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\Basic\Data\CMS\WebsiteBaker;

use Silex\Application;

/**
 * Class to access the CMS users
 *
 * @author Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 *
 */
class Users
{

    protected $app = null;

    public function __construct (Application $app)
    {
        $this->app = $app;
    }

    public function selectUser ($name, &$is_admin = false)
    {
        try {
            $login = strtolower($name);
            $SQL = "SELECT * FROM `" . CMS_TABLE_PREFIX . "users` WHERE (`username`='$login' OR `email`='$login') AND `active`='1'";
            $result = $this->app['db']->fetchAssoc($SQL);
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
        if (! isset($result['username']))
            return false;
        $user = array();
        foreach ($result as $key => $value)
            $user[$key] = (is_string($value)) ? $this->app['utils']->unsanitizeText($value) : $value;
        $groups = explode(',', $user['groups_id']);
        $is_admin = (in_array(1, $groups));
        return $user;
    }

}
