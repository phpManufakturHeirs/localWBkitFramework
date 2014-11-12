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
use phpManufaktur\miniShop\Control\Configuration;

class Update
{
    protected $app = null;
    protected $Configuration = null;

    /**
     * Release 0.12
     */
    protected function release_012()
    {
        $files = array(
            '/miniShop/Template/default/command/include',
            '/miniShop/Template/default/command/contact.order.twig',
            '/miniShop/Template/default/command/list.article.twig',
            '/miniShop/Template/default/command/order.twig',
            '/miniShop/Template/default/command/view.article.twig',
            '/miniShop/Template/default/command/view.basket.twig'
        );
        foreach ($files as $file) {
            // remove no longer needed directories and files
            if ($this->app['filesystem']->exists(MANUFAKTUR_PATH.$file)) {
                $this->app['filesystem']->remove(MANUFAKTUR_PATH.$file);
                $this->app['monolog']->addInfo(sprintf('[miniShop Update] Removed file or directory %s', $file));
            }
        }
    }

    /**
     * Release 0.13
     */
    protected function release_013()
    {
        $config = $this->Configuration->getConfiguration();
        if (!isset($config['paypal'])) {
            $default = $this->Configuration->getDefaultConfigArray();
            $config['paypal'] = $default['paypal'];
            $this->Configuration->setConfiguration($config);
            $this->Configuration->saveConfiguration();
        }

        if (!$this->app['db.utils']->columnExists(FRAMEWORK_TABLE_PREFIX.'minishop_order', 'transaction_id')) {
            // add column redirect_target
            $SQL = "ALTER TABLE `".FRAMEWORK_TABLE_PREFIX."minishop_order` ADD ".
                "`transaction_id` VARCHAR(256) NOT NULL DEFAULT 'NONE' AFTER `payment_method`";
            $this->app['db']->query($SQL);
            $this->app['monolog']->addInfo('[miniShop Update] Add field `transaction_id` to table `minishop_order`');
        }
    }

    /**
     * Execute the update for the miniShop
     *
     * @param Application $app
     */
    public function Controller(Application $app)
    {
        $this->app = $app;
        $this->Configuration = new Configuration($app);

        // Release 0.12
        $this->release_012();
        // Release 0.13
        $this->release_013();

        return $app['translator']->trans('Successfull updated the extension %extension%.',
            array('%extension%' => 'miniShop'));
    }
}
