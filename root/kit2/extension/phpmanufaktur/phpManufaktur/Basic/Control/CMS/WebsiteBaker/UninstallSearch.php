<?php

/**
 * kitFramework::Basic
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de
 * @copyright 2013 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\Basic\Control\CMS\WebsiteBaker;

use Silex\Application;
use phpManufaktur\Basic\Data\CMS\WebsiteBaker\Addons;
use phpManufaktur\Basic\Data\CMS\WebsiteBaker\SearchSection;

class UninstallSearch
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
     * Uninstall
     */
    public function exec()
    {
        // delete search from all sections
        $SearchSection = new SearchSection();
        $SearchSection->removeSearchSection($this->app);

        // delete the search from the addons
        $Addons = new Addons($this->app);
        $Addons->delete('kit_framework_search');

        // remove probably existing directory
        $this->app['filesystem']->remove(CMS_PATH.'/modules/kit_search');
    }
}
