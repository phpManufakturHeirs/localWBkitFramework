<?php

/**
 * miniShop
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de/miniShop
 * @copyright 2014 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\miniShop\Data\Setup;

use Silex\Application;
use phpManufaktur\miniShop\Data\Shop\Base;
use phpManufaktur\miniShop\Data\Shop\Group;
use phpManufaktur\miniShop\Data\Shop\Article;
use phpManufaktur\miniShop\Data\Shop\Basket;
use phpManufaktur\miniShop\Data\Shop\Order;

class Uninstall
{
    protected $app = null;

    /**
     * Execute the update for the miniShop
     *
     * @param Application $app
     */
    public function Controller(Application $app)
    {
        $this->app = $app;

        $baseTable = new Base($app);
        $baseTable->dropTable();

        $groupTable = new Group($app);
        $groupTable->dropTable();

        $articleTable = new Article($app);
        $articleTable->dropTable();

        $basketTable = new Basket($app);
        $basketTable->dropTable();

        $orderTable = new Order($app);
        $orderTable->dropTable();

        return $app['translator']->trans('Successfull uninstalled the extension %extension%.',
            array('%extension%' => 'miniShop'));
    }
}
