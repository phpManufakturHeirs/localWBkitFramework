<?php

/**
 * kitFramework::Basic
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de
 * @copyright 2014 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\Basic\Control\CMS\BlackCat;

use phpManufaktur\Basic\Control\CMS\WebsiteBaker\kitFrameworkInfo as WebsiteBakerKitFrameworkInfo;

if (!defined('CMS_PATH')) {
    // missing CMS_PATH indicate that the class is called directly by the CMS, we have no autoloading at this point !!!
    require_once WB_PATH.'/kit2/extension/phpmanufaktur/phpManufaktur/Basic/Control/CMS/WebsiteBaker/kitFrameworkInfo.php';
}

class kitFrameworkInfo extends WebsiteBakerKitFrameworkInfo
{
    // nothing to extend or to change because the handling for WebsiteBaker,
    // LEPTON and BlackCat is identical - maybe change later...
}
