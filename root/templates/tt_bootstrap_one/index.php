<?php
/**
 * TemplateTools
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de/TemplateTools
 * @copyright 2014 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 * 
 * tt_bootstrap_one - Example Template for the TemplateTools
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
    // including Bootstrap, the pattern will also load the /css/screen.css !
    $template['twig']->display('@pattern/bootstrap/head.simple.twig');
  ?>
  <body>
    <div class="container">
      
      <div class="logo-container row">
        <!-- clickable logo and the name and path of the current template -->
        <div class="logo col-sm-3 hidden-xs">
          <a href="<?php echo CMS_URL; ?>" title="<?php echo CMS_TITLE; ?>">
            <img src="<?php echo MANUFAKTUR_URL; ?>/TemplateTools/extension.jpg" class="img-responsive" alt="TemplateTools" />
          </a>
        </div>
        <div class="template-name col-sm-9 col-xs-12">
          <div class="template-name-header">
            <?php echo TEMPLATE_NAME; ?>
          </div>
          <div class="template-name-path">
            <?php echo TEMPLATE_PATH; ?>
          </div>
        </div>
      </div><!-- /logo -->
      
      <div class="content row">
        <div class="main col-sm-9 col-sm-push-3">
          <!-- show a search dialog at the top right -->
          <?php $template['twig']->display('@pattern/bootstrap/search.div.twig'); ?>
          
          <!-- show a breadcrumb -->
          <?php $template['bootstrap']->breadcrumb(); ?>

          <!-- show the page content -->
          <?php $template['cms']->page_content(); ?>
        </div>
        
        <div class="navigation col-sm-3 col-sm-pull-9">
          <!-- show a standard navigation with show_menu2() -->
          <?php $template['cms']->show_menu2(); ?>
        </div>
        
      </div><!-- /content -->
      
      <div class="footer row">
        <div class="col-sm-9 col-sm-offset-3">
          <!-- show a page footer defined in CMS Settings -> Website Footer -->
          <?php echo PAGE_FOOTER; ?>
        </div>
      </div>
      
    </div><!-- /container -->
  </body>
</html>
