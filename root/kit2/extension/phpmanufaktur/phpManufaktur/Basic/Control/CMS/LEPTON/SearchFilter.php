<?php

/**
 * kitFramework::Basic
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de
 * @copyright 2013 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\Basic\Control\CMS\LEPTON;

use phpManufaktur\Basic\Control\CMS\WebsiteBaker\SearchFilter as WebsiteBakerSearchFilter;

// no autoloading at this point !!!
require_once WB_PATH.'/kit2/extension/phpmanufaktur/phpManufaktur/Basic/Control/CMS/WebsiteBaker/SearchFilter.php';

class SearchFilter extends WebsiteBakerSearchFilter
{
    // nothing to extend or to change because the handling for WebsiteBaker,
    // LEPTON and BlackCat is identical - maybe change later...
}
