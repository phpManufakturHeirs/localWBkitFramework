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
use phpManufaktur\Basic\Control\ExtensionCatalog;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class ScanCatalog extends Alert
{
    protected static $usage = null;
    protected $Catalog = null;

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

        $this->Catalog = new ExtensionCatalog($app);
    }

    /**
     * Controller to fetch the kitFramework extension catalog
     *
     * @param Application $app
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function Controller(Application $app)
    {
        $this->initialize($app);

        $this->Catalog->getOnlineCatalog();

        // sub request to the extensions dialog
        $subRequest = Request::create('/admin/welcome/extensions/catalog', 'GET', array('usage' => self::$usage));
        return $app->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
    }
}
