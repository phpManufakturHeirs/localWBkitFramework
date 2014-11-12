/**
 * Library
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de/Library
 * @copyright 2014 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

if (typeof 'jQuery' !== 'undefined') {
  $(document).ready(function() {
    $('ul.dropdown-menu [data-toggle=dropdown]').on('click', function(event) {
      // Avoid following the href location when clicking
      event.preventDefault();
      // Avoid having the menu to close when clicking
      event.stopPropagation();
      // If a menu is already open we close it
      //$('ul.dropdown-menu [data-toggle=dropdown]').parent().removeClass('open');
      // opening the one you clicked on
      $(this).parent().addClass('open');

      var menu = $(this).parent().find("ul");
      var menupos = menu.offset();

      if ((menupos.left + menu.width()) + 30 > $(window).width()) {
        var newpos = -menu.width();
      } else {
        var newpos = $(this).parent().width();
      }
      menu.css({left: newpos});

    });
  });
}
