<?php

/**
 * kitFramework
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de
 * @copyright 2013 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\Basic\Control;

use Silex\Application;
use phpManufaktur\Basic\Control\Pattern\Alert;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class ScanExtensions extends Alert
{
    protected static $usage = null;

    /**
     * (non-PHPdoc)
     * @see \phpManufaktur\Basic\Control\Pattern\Alert::initialize()
     */
    protected function initialize(Application $app)
    {
        parent::initialize($app);

        self::$usage = $this->app['request']->get('usage', 'framework');
        if (self::$usage != 'framework') {
            // set the locale from the CMS locale
            $app['translator']->setLocale($app['session']->get('CMS_LOCALE', 'en'));
        }
    }

    public function exec(Application $app)
    {
        $this->initialize($app);

        $register = new ExtensionRegister($app);
        $register->scanDirectories(ExtensionRegister::GROUP_PHPMANUFAKTUR);
        $register->scanDirectories(ExtensionRegister::GROUP_THIRDPARTY);

        $this->setAlert('Successfull scanned the kitFramework for installed extensions.',
                array(), self::ALERT_TYPE_SUCCESS);

        $subRequest = Request::create('/admin/welcome/extensions', 'GET', array('usage' => self::$usage));
        return $app->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
    }
}
