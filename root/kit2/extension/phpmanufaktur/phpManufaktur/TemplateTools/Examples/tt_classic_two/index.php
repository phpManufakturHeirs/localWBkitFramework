<?php
/**
 * TemplateTools
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de/TemplateTools
 * @copyright 2014 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 * 
 * tt_classic_two - Example Template for the TemplateTools
 * 
 * @see 
 */

// initialize the TemplateTools
require_once WB_PATH.'/kit2/extension/phpmanufaktur/phpManufaktur/TemplateTools/initialize.php';  

// include a simple <head> .. </head> section
$template['twig']->display('twig/template.twig');
  