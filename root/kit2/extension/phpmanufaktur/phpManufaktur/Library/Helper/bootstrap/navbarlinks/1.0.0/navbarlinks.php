<?php

/**
 * Library
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de/Library
 * @copyright 2014 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */


/**
 * Bootstrap Helper to create Navbar links - show_menu2() replacement
 *
 * @param integer $menu 0 (default) show all menus
 * @param integer $level default is 0
 * @param boolean $add_dropdown_link default FALSE, add extra link to access the submenu
 * @param boolean $add_dropdown_divider default TRUE, separate the extra link with an divider
 * @param string $visibility i.e. 'public' by default
 * @param boolean $echo if true ECHO the links, otherwise RETURN
 * @return string
 */
function navbarlinks($menu=0, $level=0, $add_dropdown_link=false, $add_dropdown_divider=true, $visibility='public', $echo=true)
{
  $Navbar = new bsNavbarLinks();
  $links = $Navbar->getNavbarLinks($menu, $level, $add_dropdown_link, $add_dropdown_divider, $visibility);
  if ($echo) {
    echo $links;
  }
  else {
    return $links;
  }
}

use Symfony\Component\Translation\Translator;
use Symfony\Component\Translation\MessageSelector;
use Symfony\Component\Translation\Loader\ArrayLoader;

if (!defined('NAVBAR_LINKS_PATH')) {
    define('NAVBAR_LINKS_PATH', WB_PATH.'/kit2/extension/phpmanufaktur/phpManufaktur/Library/Helper/bootstrap/navbarlinks/1.0.0');
}

require_once NAVBAR_LINKS_PATH.'/i18n/Loader/Translator.php';

class bsNavbarLinks {

  protected $translator = null;
  protected static $start_level = null;
  protected static $add_dropdown_link = null;
  protected static $add_dropdown_divider = null;

  /**
   * Get the Page ID's for the given menu, level and visibility
   *
   * @param integer $menu
   * @param integer $level
   * @param string $visibility
   * @throws \Exception
   * @return Ambigous <boolean, array>
   */
  protected function getPageIDsForLevel($menu = 0, $level = 0, $page_id=null, $visibility = 'public')
  {
    global $database;

    $menu_sql = ($menu > 0) ? "AND `menu` = $menu " : '';

    $SQL = "SELECT `page_id` FROM `" . TABLE_PREFIX . "pages` WHERE `level`= $level $menu_sql" .
            "AND `visibility`='$visibility'";
    if (!is_null($page_id)) {
        $SQL .= " AND `parent`='$page_id'";
    }
    $SQL .= " ORDER BY `position` ASC";

    $page_ids = array();
    if (null === ($query = $database->query($SQL))) {
      throw new Exception($database->get_error());
    }

    while (false !== ($page = $query->fetchRow(MYSQL_ASSOC))) {
      $page_ids[] = $page['page_id'];
    }

    return (!empty($page_ids)) ? $page_ids : false;
  }

  /**
   * Check if the given PAGE ID has a child
   *
   * @param integer $page_id
   * @throws \Exception
   * @return boolean
   */
  protected function PageHasChild($page_id)
  {
    global $database;

    $SQL = "SELECT `page_id` FROM `" . TABLE_PREFIX . "pages` WHERE `parent` = $page_id LIMIT 1";
    $page_id = $database->get_one($SQL);
    return ($page_id > 0);
  }

  /**
   * Get the URL of the submitted PAGE_ID
   *
   * @param integer $page_id
   * @return boolean|string
   */
  public static function getURLbyPageID($page_id)
  {
    global $database;

    $SQL = "SELECT `link` FROM `" . TABLE_PREFIX . "pages` WHERE `page_id`='$page_id'";
    $link = $database->get_one($SQL, MYSQL_ASSOC);
    if ($database->is_error()) {
      trigger_error(sprintf('[%s - %s] %s', __FUNCTION__, __LINE__, $database->get_error()), E_USER_ERROR);
      return false;
    }
    return WB_URL . PAGES_DIRECTORY . $link . PAGE_EXTENSION;
  }

  /**
   * Get the page title for the given page ID
   *
   * @global object $database
   * @param integer $page_id
   * @return string
   */
  protected function getPageTitle($page_id)
  {
    global $database;

    $SQL = "SELECT `page_title` FROM `" . TABLE_PREFIX . "pages` WHERE `page_id`='$page_id'";
    return $database->get_one($SQL);
  }

  /**
   * Get the menu title for the given Page ID
   *
   * @global object $database
   * @param integer $page_id
   * @return string
   */
  protected function getMenuTitle($page_id)
  {
    global $database;

    $SQL = "SELECT `menu_title` FROM `" . TABLE_PREFIX . "pages` WHERE `page_id`='$page_id'";
    return $database->get_one($SQL);
  }

  /**
   * Build the navar items recursivly
   *
   * @param integer $menu
   * @param integer $level
   * @param string $visibility
   * @param string reference $navbar
   */
  protected function buildNavbar($menu, $level, $visibility, $page_id=null, &$navbar = '')
  {
    $page_ids = $this->getPageIDsForLevel($menu, $level, $page_id, $visibility);
    if (is_array($page_ids)) {
        foreach ($page_ids as $page_id) {
          if ($this->PageHasChild($page_id)) {
            if ($level == self::$start_level) {
              $navbar .= '<li class="menu-item dropdown">';
              $navbar .= sprintf('<a href="%s" class="dropdown-toggle" data-toggle="dropdown" title="%s">%s <b class="caret"></b></a>',
                  $this->getURLbyPageID($page_id),
                  $this->translator->trans('Click to toggle dropdown menu'),
                  $this->getMenuTitle($page_id));
            }
            else {
              $navbar .= '<li class="menu-item dropdown dropdown-submenu">';
              $navbar .= sprintf('<a href="%s" class="dropdown-toggle" data-toggle="dropdown" title="%s">%s</a>',
                  $this->getURLbyPageID($page_id),
                  $this->translator->trans('Click to toggle dropdown menu'),
                  $this->getMenuTitle($page_id));
            }
            $navbar .= '<ul class="dropdown-menu">';
            if (self::$add_dropdown_link) {
                // add an additional dropdown link to enable selection
                $navbar .= sprintf('<li class="%s"><a href="%s" title="%s">%s</a></li>',
                    ($page_id == PAGE_ID) ? 'menu-item active' : 'menu-item',
                    $this->getURLbyPageID($page_id), $this->getPageTitle($page_id),
                    $this->getMenuTitle($page_id));
                if (self::$add_dropdown_divider) {
                    // add a divider to separate the link from the others
                    $navbar .= '<li class="divider"></li>';
                }
            }
            $this->buildNavbar($menu, $level + 1, $visibility, $page_id, $navbar);
            $navbar .= '</ul></li>';
          }
          else {
            $navbar .= sprintf('<li class="%s"><a href="%s" title="%s">%s</a></li>',
                ($page_id == PAGE_ID) ? 'menu-item active' : 'menu-item',
                $this->getURLbyPageID($page_id), $this->getPageTitle($page_id),
                $this->getMenuTitle($page_id));
          }
        }
    }
  }

  /**
   * Add a language file to the Translator service
   *
   * @param string $locale_path
   * @throws \Exception
   */
  protected function addLanguageFiles($locale_path)
  {
      // get the language files
      if (false === ($lang_files = scandir($locale_path)))
          throw new \Exception(sprintf("Can't read the /i18n directory %s!", $locale_path));

      $ignore = array('.', '..', 'index.php', 'README.md');

      // loop through the language files
      foreach ($lang_files as $lang_file) {
          if (!is_file($locale_path.'/'.$lang_file)) {
              continue;
          }
          if (in_array($lang_file, $ignore) || (pathinfo($locale_path.'/'.$lang_file, PATHINFO_EXTENSION) != 'php')) {
              continue;
          }
          $lang_name = pathinfo($locale_path.'/'.$lang_file, PATHINFO_FILENAME);

          // get the array from the desired file
          $lang_array = include_once $locale_path.'/'.$lang_file;

          // add the locale resource file
          $this->translator->addResource('array', $lang_array, $lang_name);
      }
  }

  /**
   * Initialize the Symfony Translator Service
   *
   * @throws \Exception
   */
  protected function initializeTranslator()
  {
      $this->translator = new Translator('en_EN', new MessageSelector());
      $this->translator->setFallbackLocale('en');
      $this->translator->addLoader('array', new ArrayLoader());

      $locale_paths = array('/i18n', '/i18n/Custom');

      foreach ($locale_paths as $locale_path) {
          $this->addLanguageFiles(NAVBAR_LINKS_PATH.$locale_path);
      }

      // set the locale from the CMS
      $this->translator->setLocale(strtolower(LANGUAGE));
  }

  /**
   * Controller to create the Navbar links
   *
   * @param integer $menu
   * @param integer $level
   * @param boolean $add_dropdown_link
   * @param boolean $add_dropdown_divider
   * @param string $visibility
   * @return string
   */
  public function getNavbarLinks($menu=0, $level=0, $add_dropdown_link=false, $add_dropdown_divider=true, $visibility='public')
  {
    self::$start_level = $level;
    self::$add_dropdown_link = $add_dropdown_link;
    self::$add_dropdown_divider = $add_dropdown_divider;

    // initialize the translation service
    $this->initializeTranslator();

    $navbar = '';
    $this->buildNavbar($menu, $level, $visibility, null, $navbar);

    if (!empty($navbar)) {
      $navbar = sprintf('<ul class="nav navbar-nav">%s</ul>', $navbar);
    }

    return $navbar;
  }

}
