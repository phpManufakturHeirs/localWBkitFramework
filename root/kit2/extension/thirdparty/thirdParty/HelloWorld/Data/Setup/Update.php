<?php

/**
 * HelloWorld
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de/HelloWorld
 * @copyright 2014 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\ContactForm\Data\Setup;

use Silex\Application;

class Update
{
    protected $app = null;

    /**
     * Execute the update for the HelloWorld
     *
     * @param Application $app
     */
    public function Controller(Application $app)
    {
        $this->app = $app;

        if (self::$usage != 'framework') {
            // set the locale from the CMS locale
            $app['translator']->setLocale($app['session']->get('CMS_LOCALE', 'de'));
        }

        return $app['translator']->trans('Successfull updated the extension %extension%.',
            array('%extension%' => 'HelloWorld'));
    }
}
