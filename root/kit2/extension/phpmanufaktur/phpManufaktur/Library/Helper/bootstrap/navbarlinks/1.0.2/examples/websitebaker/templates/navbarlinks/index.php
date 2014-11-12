<?php

/**
 * NavbarLinks
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de/Library
 * @copyright 2014 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <!-- declare the content type -->
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <!-- declare the charset -->
    <meta charset="utf-8">
    <!-- IE support -->
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <!-- define the initial viewport -->
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <!-- get the page title from CMS -->
    <title><?php page_title('', '[WEBSITE_TITLE]'); ?></title>
    <!-- get the page description from CMS -->
    <meta name="description" content="<?php page_description(); ?>" />
    <!-- get the page keywords from CMS -->
    <meta name="keywords" content="<?php page_keywords(); ?>" />
    
    <?php
      if (!defined('LIBRARY_URL')) {
        // define LIBRARY_URL for an easy access to jQuery, Bootstrap ...
        define('LIBRARY_URL', WB_URL.'/kit2/extension/phpmanufaktur/phpManufaktur/Library/Library');
      }
      if (!defined('NAVBARLINKS_PATH')) {
        // define NAVBARLINKS_PATH for an access to the navbarlinks() function - including the RELEASE number!
        define('NAVBARLINKS_PATH', WB_PATH.'/kit2/extension/phpmanufaktur/phpManufaktur/Library/Helper/bootstrap/navbarlinks/1.0.2');
      }
      if (!defined('NAVBARLINKS_URL')) {
        // define NAVBARLINKS_URL for an access to the NavbarLinks jQuery support - including the RELEASE number!
        define('NAVBARLINKS_URL', WB_URL.'/kit2/extension/phpmanufaktur/phpManufaktur/Library/Helper/bootstrap/navbarlinks/1.0.2');
      }      
    ?>
    <!-- Bootstrap CSS file -->
    <link href="<?php echo LIBRARY_URL; ?>/bootstrap/3.1.1/css/bootstrap.min.css" rel="stylesheet">
    <!-- we want also use the font-awesome icons ... -->
    <link href="<?php echo LIBRARY_URL; ?>/font-awesome/4.0.3/css/font-awesome.min.css" rel="stylesheet" type="text/css" media="all" />
    <!-- load the CSS file for the NavbarLinks -->
    <link href="<?php echo NAVBARLINKS_URL; ?>/css/navbarlinks.min.css" rel="stylesheet">
    <!-- this is the CSS file for this example template -->
    <link href="<?php echo TEMPLATE_DIR; ?>/screen.css" rel="stylesheet">
    
    <!-- jQuery (necessary for Bootstrap JavaScript extensions) -->
    <script src="<?php echo LIBRARY_URL; ?>/jquery/jquery/1.11.0/jquery.min.js"></script>
    <!-- Bootstrap jQuery extension -->
    <script src="<?php echo LIBRARY_URL; ?>/bootstrap/3.1.1/js/bootstrap.min.js"></script>
    <!-- NavbarLinks jQuery settings -->
    <script src="<?php echo NAVBARLINKS_URL; ?>/js/navbarlinks.min.js"></script>
        
    <!-- Include all compiled plugins (below), or include individual files as needed -->
    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
      <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->   
  </head>
  
  <body>
    <!-- the class container define the viewports LG (large, desktop), MD (medium, tablet), 
      SM (small, smartphone horizontal), XS (very small, smartphone vertical) 
      alternate: use class "container-fluid" to assign the content to the full device width -->
    <div class="container">
      
      <!-- responsive horizontal navigation with the bootstrap navbar -->
      <nav class="navbar navbar-default" role="navigation">
        
        <!-- Brand and toggle get grouped for better mobile display -->
        <div class="navbar-header">
          <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-example-collapse">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <!-- add a home icon and a link to the start page -->
          <a class="navbar-brand" href="<?php echo WB_URL; ?>" title="Startseite"><span class="glyphicon glyphicon-home"></span></a>
        </div>

        <!-- Collect the nav links, forms, and other content for toggling -->
        <div class="collapse navbar-collapse navbar-example-collapse">
          <?php 
            /*
             * show_menu2() can't show a responsive navbar, so we use the function navbarlinks() which is provided 
             * by the kitFramework Library https://github.com/phpManufaktur/kfLibrary/tree/master/Helper/bootstrap
             */
            if (file_exists(NAVBARLINKS_PATH.'/navbarlinks.php')) { 
              require_once NAVBARLINKS_PATH.'/navbarlinks.php';
              // execute navbarlinks(), all parameters here are the DEFAULT values!
              navbarlinks(
                      0,        // integer - the menu number, same usage as in show_menu()
                      0,        // integer - the menu level, same usage as in show_menu()
                      false,    // boolean - add an additional dropdown link
                      true,     // boolean - add a dropdown divider (horizontal line)
                      null,     // array - add icons to the menu items
                      'public', // string - the visibility of the pages: public, hidden, private or none
                      true      // boolean - prompt (echo) the result
                      ); 
            }
          ?>
        </div>
      </nav>
      
      <!-- Stack the columns on mobile by making one full-width and the other half-width -->
      <div class="row">
        <div class="col-xs-12 col-md-4">
          <?php 
            // the content of the actual CMS page
            page_content(); 
          ?>
        </div>
        <div class="col-xs-12 col-md-8 readme">
          <?php 
            if (false !== ($readme = file_get_contents(NAVBARLINKS_PATH.'/README.md'))) {
              // use the Markdown Parser from the kitFramework Library to show the NavbarLinks README.md file ...
              require_once  WB_PATH.'/kit2/extension/phpmanufaktur/phpManufaktur/Library/Extension/php-markdown/1.4.0/Michelf/MarkdownExtra.inc.php';
              echo \Michelf\MarkdownExtra::defaultTransform($readme);
            } 
          ?>
        </div>
      </div>
      
    </div><!-- /container -->
    
    <div id="footer">
      <div class="container">
        <p class="text-muted text-center">
          <a href="https://github.com/phpManufaktur/NavbarLinks">NavbarLinks</a> is part of the <a href="https://kit2.phpmanufaktur.de/de/erweiterungen/library.php">kitFramework Library</a> - &copy; 2014 by <a href="https://phpmanufaktur.de">phpManufaktur</a>
        </p>
      </div>
    </div>    
  </body>
</html>
