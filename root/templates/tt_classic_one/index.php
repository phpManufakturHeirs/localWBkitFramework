<?php
/**
 * TemplateTools
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de/TemplateTools
 * @copyright 2014 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 * 
 * tt_classic_one - Example Template for the TemplateTools
 * 
 * @see 
 */
?>
<!DOCTYPE html>
<html lang="{{ PAGE_LOCALE|lower }}">
  <?php
    // initialize the TemplateTools
    require_once WB_PATH.'/kit2/extension/phpmanufaktur/phpManufaktur/TemplateTools/initialize.php';  

    // use the unchanged pattern to include a simple <head> section
    // the pattern will also load the /css/screen.css !
    $template['twig']->display('@pattern/classic/head.simple.twig');
  ?>
  <body>
    <div class="body-container">
      
      <div class="logo-container">
        <!-- clickable logo and the name and path of the current template -->
        <div class="logo">
          <a href="<?php echo CMS_URL; ?>" title="<?php echo CMS_TITLE; ?>">
            <img src="<?php echo MANUFAKTUR_URL; ?>/TemplateTools/extension.jpg" width="200" height="200" alt="TemplateTools" />
          </a>
        </div>
        <div class="template-name">
          <div class="template-name-header">
            <?php echo TEMPLATE_NAME; ?>
          </div>
          <div class="template-name-path">
            <?php echo TEMPLATE_PATH; ?>
          </div>
        </div>
      </div><!-- /logo-container -->
      
      <div class="content">
        
        <div class="navigation">
          <!-- show a standard navigation with show_menu2() -->
          <?php $template['cms']->show_menu2(); ?>
        </div>
        
        <div class="main">          
          <!-- show a small search dialog -->
          <?php $template['twig']->display('@pattern/classic/search.div.twig'); ?>
          
          <div class="breadcrumb">
            <!-- show a breadcrumb -->
            <?php $template['classic']->breadcrumb(); ?>
          </div>
          
          <!-- show the page content -->
          <?php $template['cms']->page_content(); ?>
                   
        </div><!-- /main -->        
      </div><!-- /content -->
      
      <div class="footer">
        <!-- show a page footer defined in CMS Settings -> Website Footer -->
        <?php echo PAGE_FOOTER; ?>
      </div>
    </div><!-- /body -->
  </body>
</html>
