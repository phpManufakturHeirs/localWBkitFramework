<?php

/**
 * TemplateTools
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de/TemplateTools
 * @copyright 2014 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

// execute the setup
$admin->get('/templatetools/setup',
    'phpManufaktur\TemplateTools\Data\Setup\Setup::exec');
$admin->get('/templatetools/update',
    'phpManufaktur\TemplateTools\Data\Setup\Update::exec');
$admin->get('/templatetools/uninstall',
    'phpManufaktur\TemplateTools\Data\Setup\Uninstall::exec');

$command->post('/wysiwyg_content',
    'phpManufaktur\TemplateTools\Control\kitCommands\wysiwygContent::Controller')
    ->setOption('info', MANUFAKTUR_PATH.'/TemplateTools/command.wysiwyg_content.json');

$command->post('/page_modified_when',
    'phpManufaktur\TemplateTools\Control\kitCommands\PageModifiedWhen::Controller')
    ->setOption('info', MANUFAKTUR_PATH.'/TemplateTools/command.page_modified_when.json');

$command->post('/page_modified_by',
    'phpManufaktur\TemplateTools\Control\kitCommands\PageModifiedBy::Controller')
    ->setOption('info', MANUFAKTUR_PATH.'/TemplateTools/command.page_modified_by.json');

$command->post('/cms_modified_when',
    'phpManufaktur\TemplateTools\Control\kitCommands\cmsModifiedWhen::Controller')
    ->setOption('info', MANUFAKTUR_PATH.'/TemplateTools/command.cms_modified_when.json');

$command->post('/cms_modified_by',
    'phpManufaktur\TemplateTools\Control\kitCommands\cmsModifiedBy::Controller')
    ->setOption('info', MANUFAKTUR_PATH.'/TemplateTools/command.cms_modified_by.json');

$command->post('/google_map',
    'phpManufaktur\TemplateTools\Control\kitCommands\GoogleMap::Controller')
    ->setOption('info', MANUFAKTUR_PATH.'/TemplateTools/command.google_map.json');
