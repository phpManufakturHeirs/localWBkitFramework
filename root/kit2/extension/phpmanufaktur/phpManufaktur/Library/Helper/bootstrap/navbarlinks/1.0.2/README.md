# NavbarLinks

Create a [Bootstrap Navbar](http://getbootstrap.com/components/#navbar) in Templates for [WebsiteBaker](http://websitebaker.org), [LEPTON CMS](http://lepton-cms.org) or [BlackCat CMS](http://blackcat-cms.org).

NavbarLinks is a replacement for the default CMS function [`show_menu2()`](http://www.websitebakers.de/sm2/) to generate menu links and part of the [kitFramework](https://kit2.phpmanufaktur.de).

## Requirements

* [WebsiteBaker](http://websitebaker.org), [LEPTON CMS](http://lepton-cms.org) or [BlackCat CMS](http://blackcat-cms.org)
* [kitFramework Library](https://kit2.phpmanufaktur.de/de/erweiterungen/library.php)

## Usage

Create your Bootstrap 3.x CMS Template. In `<head>` add:

    <?php
      if (!defined('NAVBARLINKS_PATH')) {
        // define NAVBARLINKS_PATH for an access to the navbarlinks() function - including the RELEASE number!
        define('NAVBARLINKS_PATH', 
          WB_PATH.'/kit2/extension/phpmanufaktur/phpManufaktur/Library/Helper/bootstrap/navbarlinks/1.0.2');
      }
      if (!defined('NAVBARLINKS_URL')) {
        // define NAVBARLINKS_URL for an access to the NavbarLinks jQuery support - including the RELEASE number!
        define('NAVBARLINKS_URL', 
          WB_URL.'/kit2/extension/phpmanufaktur/phpManufaktur/Library/Helper/bootstrap/navbarlinks/1.0.2');
      }      
    ?>
    
    <!-- load the CSS file for the NavbarLinks -->
    <link href="<?php echo NAVBARLINKS_URL; ?>/css/navbarlinks.min.css" rel="stylesheet">
    
    <!-- NavbarLinks jQuery settings -->
    <script src="<?php echo NAVBARLINKS_URL; ?>/js/navbarlinks.min.js"></script>
        
In the `<body>` at the place where you want to insert a [Bootstrap Navbar](http://getbootstrap.com/components/#navbar) , add:

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
        <a class="navbar-brand" href="<?php echo WB_URL; ?>" title="Startseite">
          <span class="glyphicon glyphicon-home"></span></a>
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
            // execute navbarlinks()
            navbarlinks(); 
          }
        ?>
      </div>
    </nav>

`navbarlinks()` will return a unsorted list with the pagetree of your CMS, i.e.:

    <ul class="nav navbar-nav">
      <li class="menu-item active menu-first">
        <a href="http://domain.tld/sample.php" title="Title 1" class="active menu-first">Sample 1</a>
      </li>
      <li class="menu-item">
        <a href="http://domain.tld/sample-2.php" title="Title 2">Sample 2</a>
      </li>
      <li class="menu-item">
        <a href="http://domain.tld/sample-3.php" title="Title 3" class="">
          <i class="glyphicon glyphicon-lock"></i> Sample 3</a>
      </li>
      <li class="menu-item dropdown menu-last">
        <a href="http://domain.tld/sample-4.php" class="dropdown-toggle" data-toggle="dropdown" title="Title 4">
          Sample 4 <b class="caret"></b>
        </a>
        <ul class="dropdown-menu">
          <li class="menu-item menu-first">
            <a href="http://domain.tld/sample-5.php" title="Title 5" class=" menu-first menu-last">
              <i class="fa fa-bookmark-o"></i> Sample 5</a>
          </li>
        </ul>
      </li>
    </ul>

The associated classes indicate the status of the items:

* `active` - active (selected) menu item
* `dropdown` - this menu item has a dropdown menu
* `menu-first` - the first menu item of this level
* `menu-last` - the last menu item of this level
* `open` - a dropdown menu is opened

The menu items can also contain [Bootstrap Glyphicons](http://getbootstrap.com/components/#glyphicons) (`sample-3.php`), [Font Awesome Icons](http://fontawesome.io/icons/) (`sample-5.php`) or images - see Parameter `icons` for more information.

In [`/examples/websitebaker/templates/navbarlinks`](https://github.com/phpManufaktur/NavbarLinks/tree/master/examples/websitebaker/templates/navbarlinks) you will find an installable example for a WebsiteBaker Template in `ZIP` format.

## Parameter

`navbarlinks()` can be used with optional parameters:

    navbarlinks(
        0,        // integer - the menu number, same usage as in show_menu()
        0,        // integer - the menu level, same usage as in show_menu()
        false,    // boolean - add an additional dropdown link
        true,     // boolean - add a dropdown divider (horizontal line)
        null,     // array - add icons or images to the menu items
        'public', // string - the visibility of the pages: public, hidden, private or none
		true      // boolean - prompt (echo) the result
    ); 
    
The usage of `menu number` and `menu level` are identical to the usage in [`show_menu2()`](http://www.websitebakers.de/sm2/), use them to select a specific menu (0 is default) or to show menu items of a specific menu level (0 is default).

**Important:** The option `add an additional dropdown link` is switched off by default (`false`). Because the Navbar is responsive and open the dropdown menu links at click on the top menu item, it will **not** open the associated content. Therefore all top menu items of a dropdown menu should be created as [Menu Link](http://www.websitebaker.org/en/help/user-guide/working-with-wb/pages-administration/modify-pages/menu-link.php?lang=EN) to make NavbarLinks working as expected. In all cases you have already created top menu items as [WYSIWYG](http://www.websitebaker.org/en/help/user-guide/working-with-wb/pages-administration/modify-pages/wysiwyg.php) or any other type, you can set `add an additional dropdown link` to `true`. In this case `navbarlinks()` will place a clickable menu item to the parent content below the top menu.

If you are using `add an additional dropdown link` you can `add a dropdown divider` (horizontal line) for this link to separate this links from the other menu items of the dropdown. The divider is switched on (`true`) by default.

You can add `icons` or images to the menu links, just insert an array as parameter and specify the `PAGE_ID` and the icon or image to use:

    navbarlinks(0, 0, false, true, array(
        'page_id' => array(
		    118 => 'glyphicon-lock',
	        120 => 'fa-bookmark-o',
		    79 => '/media/example.jpg',
			115 => '/public/exsample.png'
		),
        'width' => 20		
    ));
    
This will associate:

* the Glyphicon [`glyphicon-lock`](http://getbootstrap.com/components/#glyphicons-glyphs) to the menu link for the `PAGE_ID 118` and insert the additional HTML code `<i class="glyphicon glyphicon-lock"></i>`. You can use any `glyphicon-` you want.
* the Font Awesome icon [`fa-bookmark-o`](http://fontawesome.io/icon/bookmark-o/) to the menu link for the `PAGE_ID 120` and insert the additional HTML code `<i class="fa fa-bookmark-o"></i>`. You can use any `fa-` icon you want, but you must enable Font Awesome in your template, i.e. with: `<link href="<?php echo LIBRARY_URL; ?>/font-awesome/4.0.3/css/font-awesome.min.css" rel="stylesheet" />`
* the image `/media/example.jpg` from the `/media` directory of your CMS to the menu link for the `PAGE_ID 79` and insert the additional HTML code `<img src="http://yourdomain.tld/media/example.jpg" width="20" alt="Page title" />`. You can also use subdirectories. By default `navbarlinks()` will use a `width` of 15 pixel, add `width` to the `icons` parameter to specify another width (see example above)
* the image `/public/example.png` from the public media directory of the kitFramework in `/kit2/media/public` to the menu linkof the `PAGE_ID 115` and insert the additional HTML code `<img src="http://yourdomain.tld/kit2/media/public/example.jpg" width="15" alt="Page title" />`. You can also use subdirectories. By default `navbarlinks()` will use a `width` of 15 pixel, add `width` to the `icons` parameter to specify another width (see example above).

By default the Navbar will show all menu items with the visibility `public`, you can also specify another visibility like `hidden`, `private` or `none`.

`navbarlinks()` will prompt the result directly to the browser, if you want to retrieve the Navbar Menu Items as variable set the last parameter to `false`.

## Support

Please visit the [phpManufaktur Support Group](https://support.phpmanufaktur.de)

[![githalytics.com alpha](https://cruel-carlota.pagodabox.com/7bd2ab96fbe9127648ae19ff0003f636 "githalytics.com")](http://githalytics.com/phpManufaktur/NavbarLinks)