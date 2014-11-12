<?php

/**
 * TemplateTools
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de/TemplateTools
 * @copyright 2014 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

if ('á' != "\xc3\xa1") {
    // the language files must be saved as UTF-8 (without BOM)
    throw new \Exception('The language file ' . __FILE__ . ' is damaged, it must be saved UTF-8 encoded!');
}

return array(
    'If you add a block of type <em>Sidebar</em> to the page with the <code>PAGE_ID %page_id%</code>, this template will show two columns at <em>large</em> and <em>medium</em> media devices: the <em>Main Content</em> to the left and the <em>Sidebar</em> to the right, try it!'
      => 'Wenn Sie der Seite mit der <code>PAGE_ID %page_id%</code> einen Block des Typ <em>Sidebar</em> hinzufügen, verwendet dieses Template auf Ausgabegeräten der Größe <em>Medium</em> und <em>Large</em> ein zweispaltiges Layout: Der Block <em>Main Content</em> auf der linken und der Block <em>Sidebar</em> auf der rechten Seite. Probieren Sie es aus!',
    'If you add a <em>Footer Navigation</em> to the page tree, the Navigation will be shown instead of this <em>Copyright Notice</em>.'
      => 'Wenn Sie eine <em>Footer Navigation</em> zum Seitenbaum hinzufügen, wird hier die zusätzliche Navigation anstatt dieses <em>Urheber Hinweis</em> angezeigt.',
    'Information about the Browser.'
      => 'Informationen über den verwendeten Browser',
    
    '<em>Ridiculously Responsive Social Sharing Buttons</em>, using about <strong>%percent%%</strong> width of the <em>Panel Container</em>.'
      => '<em>Ridiculously Responsive Social Sharing Buttons</em>, verwenden etwa <strong>%percent%%</strong> der zur Verfügung stehenden Breite des <em>Panel Container</em>.',

    'Mobile Device'
      => 'mobilen Endgerät',
    
    'This is the <em>%block_type%</em> of this page.'
      => 'Dies ist der Inhaltsblock <em>%block_type%</em> dieser Seite.',
    'This <em>Sidebar</em> will be only shown if you have added a block of type <em>Sidebar</em> to the page and if the size of the media device is <em>medium</em> or <em>large</em>.'
      => 'Der Block <em>Sidebar</em> wird nur angezeigt, wenn Sie einen Block des Typs <em>Sidebar</em> zu der Seite hinzugefügt haben und die Bildschirmgröße des Ausgabegerätes <em>Medium</em> oder <em>Large</em> ist.',
    'This template is for demonstration purposes and does not show the content of the block <em>%block_type%</em>.'
      => 'Dieses Template dient Demonstrationszwecken und zeigt nicht den tatsächlichen Inhalt des Blocks <em>%block_type%</em> an.',
    
    'Use <code>{{ page_content(\'%block_type%\') }}</code> in your template to prompt the content of the block <em>%block_type%</em>.'
      => 'Verwenden Sie <code>{{ page_content(\'%block_type%\') }}</code> in Ihrem Template um den Inhalt des Blocks <em>%block_type%</em> auszugeben.',
    
    'Welcome to the start page of %CMS_TITLE%!'
      => 'Herzlich willkommen auf der Startseite von %CMS_TITLE%!',

    'You are using the additional navigation <em>Footer Navigation</em>, you can access it with <code>{{ show_menu2(\'Footer Navigation\') }}</code>. If you remove the <em>Footer Navigation</em> a <em>Copyright Notice</em> will be shown instead.'
      => 'Sie verwenden die zusätzliche Navigation <em>Footer Navigation</em>, Sie können darauf mit <code>{{ show_menu2(\'Footer Navigation\') }}</code> zugreifen. Wenn Sie die <em>Footer Navigation<em> entfernen wird stattdessen ein <em>Urheber Hinweis</em> angezeigt.',
    'You are using the Browser <em>%name%</em> in Version <em>%version%</em> at Platform <em>%platform%</em> and you are viewing this page at a <em>%device%</em>.'
      => 'Sie verwenden den Browser <em>%name%</em> in der Version <em>%version%</em> auf der Plattform <em>%platform%</em> und betrachten diese Seite auf einem <em>%device%</em>.',
);
