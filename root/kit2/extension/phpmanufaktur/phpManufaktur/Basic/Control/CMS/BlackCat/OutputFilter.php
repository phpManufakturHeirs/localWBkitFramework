<?php

/**
 * kitFramework::Basic
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de
 * @copyright 2013 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\Basic\Control\CMS\BlackCat;

use phpManufaktur\Basic\Control\CMS\WebsiteBaker\OutputFilter as WebsiteBakerOutputFilter;

if (!defined('CMS_PATH') || defined('SYNCDATA_PATH')) {
    // missing CMS_PATH indicate that the output filter is called directly by the CMS, we have no autoloading at this point !!!
    require_once CAT_PATH.'/kit2/extension/phpmanufaktur/phpManufaktur/Basic/Control/CMS/WebsiteBaker/OutputFilter.php';
}

class OutputFilter extends WebsiteBakerOutputFilter
{
    // nothing to extend or to change
}
