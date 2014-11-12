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

class Setup
{
    protected $app = null;
    protected static $configuration = null;


    /**
     * Execute all steps needed to setup HelloWorld
     *
     * @param Application $app
     * @throws \Exception
     * @return string with result
     */
    public function Controller(Application $app)
    {
        try {
            $this->app = $app;

            if (self::$usage != 'framework') {
                // set the locale from the CMS locale
                $app['translator']->setLocale($app['session']->get('CMS_LOCALE', 'de'));
            }

            return $app['translator']->trans('Successfull installed the extension %extension%.',
                array('%extension%' => 'HelloWorld'));

        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }
}
