<?php

/**
 * kitFramework
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de/FacebookGallery
 * @copyright 2013 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

?>
<!DOCTYPE html>
<html>
  <head>
    <title><?php page_title(); ?></title>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
    <meta name="robots" content="noindex,nofollow" />
    <meta name="description" content="<?php page_description(); ?>" />
    <meta name="keywords" content="<?php page_keywords(); ?>" />
    <meta name=”content-language” content=”de” />
    <link rel="stylesheet" type="text/css" href="<?php echo TEMPLATE_DIR; ?>/css/screen.css" media="screen, projection" />
    <link rel="stylesheet" type="text/css" href="<?php echo TEMPLATE_DIR; ?>/font-awesome/css/font-awesome.min.css" media="screen, projection" />
    <link rel="shortcut icon" type="image/x-icon" href="<?php echo TEMPLATE_DIR; ?>/image/favicon.ico" />
    <?php
      if (function_exists('register_frontend_modfiles')) {
        register_frontend_modfiles('css');
        register_frontend_modfiles('js');
      } 
    ?>
    <script type="text/javascript">
      function copyToClipboard (text) {
        window.prompt("In die Zwischenablage kopieren: <STRG>+C, Eingabe", text);
      }
    </script>
  </head>
  <body>
    <div id="framework_body">
      <div id="logo"></div>
      <div id="navigation">
        <?php show_menu2(0, SM2_ROOT, SM2_CURR, SM2_TRIM|SM2_NUMCLASS, '<div class="menu-item [class]">[a][menu_title]</a>', '</div>', '', ''); ?>
      </div>
      <div id="content">
        <?php page_content(); ?>
      </div>
      <div id="footer">
        <p><a href="http://websitebaker.org" target="_blank">WebsiteBaker</a> is released under the <a href="http://www.gnu.org/licenses/gpl.html" target="_blank">GNU General Public License</a></p>
        <p>The <a href="https://kit2.phpmanufaktur.de" target="_blank">kitFramework</a> is created by <a href="https://phpmanufaktur.de" target="_blank">phpManufaktur</a> and released under the <a href="http://opensource.org/licenses/MIT" target="_blank">MIT license</a></p>
      </div>
    </div>
    
  </body>
</html>

