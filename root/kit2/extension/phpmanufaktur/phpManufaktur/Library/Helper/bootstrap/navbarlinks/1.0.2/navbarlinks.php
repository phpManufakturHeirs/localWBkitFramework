<?php

/**
 * NavbarLinks
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
 * @param array|string $icons default NULL, array with icon definition or string to JSON file
 * @param string $visibility i.e. 'public' by default
 * @param boolean $echo if true ECHO the links, otherwise RETURN
 * @return string
 */
function navbarlinks(
    $menu=0,
    $level=0,
    $add_dropdown_link=false,
    $add_dropdown_divider=true,
    $icons=null,
    $visibility='public',
    $echo=true)
{
  $Navbar = new bsNavbarLinks();
  $links = $Navbar->getNavbarLinks($menu, $level, $add_dropdown_link, $add_dropdown_divider, $icons, $visibility);
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

if (!defined('NAVBARLINKS_PATH')) {
    define('NAVBARLINKS_PATH', __DIR__);
}
require_once NAVBARLINKS_PATH.'/i18n/Loader/Translator.php';

if (!defined('FRAMEWORK_PATH')) {
  define('FRAMEWORK_PATH', WB_PATH.'/kit2');
}

require_once FRAMEWORK_PATH.'/framework/twig/twig/lib/Twig/Autoloader.php';
Twig_Autoloader::register();

class bsNavbarLinks {

    protected $translator = null;
    protected $twig = null;
    protected static $start_level = null;
    protected static $add_dropdown_link = null;
    protected static $add_dropdown_divider = null;
    protected static $icons = null;

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

        $SQL = "SELECT `page_id` FROM `" . TABLE_PREFIX . "pages` WHERE `level`= $level $menu_sql" . "AND `visibility`='$visibility'";
        if (! is_null($page_id)) {
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

        return (! empty($page_ids)) ? $page_ids : false;
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
     * Get information about the given menu
     *
     * @param integer $page_id
     * @throws Exception
     * @return array|boolean
     */
    protected function getMenuInformation($page_id)
    {
        global $database;

        $SQL = "SELECT `page_id`, `parent`, `root_parent`, `position`, `level`, `menu_title`, `page_title`, `link` FROM `" .
            TABLE_PREFIX . "pages` WHERE `page_id`=$page_id";
        if (null === ($query = $database->query($SQL))) {
            throw new Exception($database->get_error());
        }

        $result = $query->fetchRow(MYSQL_ASSOC);

        if (isset($result['parent'])) {
            $SQL = "SELECT MAX(`position`) FROM `" . TABLE_PREFIX . "pages` WHERE `parent`=" . $result['parent'];
            $max_position = $database->get_one($SQL);

            $result['active'] = PAGE_ID == $page_id;
            $result['max_position'] = $max_position;
            $result['menu_first'] = ($result['position'] == 1);
            $result['menu_last'] = ($result['position'] == $max_position);
            $result['url'] = WB_URL . PAGES_DIRECTORY . $result['link'] . PAGE_EXTENSION;
            $result['icons'] = self::$icons;
            return $result;
        } else {
            return false;
        }
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
                if (false !== ($info = $this->getMenuInformation($page_id))) {
                    if ($this->PageHasChild($page_id)) {
                        if ($level == self::$start_level) {
                            $navbar .= $this->twig->render('li.dropdown.twig', array('menu' => $info));
                            $info['hint'] = $this->translator->trans('Click to toggle dropdown menu');
                            $info['direction'] = 'down';
                            $navbar .= $this->twig->render('a.dropdown-toggle.twig', array(
                                'menu' => $info
                            ));
                        }
                        else {
                            $navbar .= $this->twig->render('li.dropdown-submenu.twig', array('menu' => $info));
                            $info['hint'] = $this->translator->trans('Click to toggle dropdown menu');
                            $info['direction'] = 'right';
                            $navbar .= $this->twig->render('a.dropdown-toggle.twig', array(
                                'menu' => $info
                            ));
                        }

                        $navbar .= $this->twig->render('ul.dropdown-menu.twig');
                        if (self::$add_dropdown_link) {
                            // add an additional dropdown link to enable selection
                            $navbar .= $this->twig->render('li.menu-item.twig', array(
                                'menu' => $info
                            ));
                            if (self::$add_dropdown_divider) {
                                // add a divider to separate the link from the others
                                $navbar .= $this->twig->render('li.divider.twig');
                            }
                        }
                        $this->buildNavbar($menu, $level + 1, $visibility, $page_id, $navbar);
                        $navbar .= $this->twig->render('ul.li.close.twig');
                    }
                    else {
                        $navbar .= $this->twig->render('li.menu-item.twig', array(
                            'menu' => $info
                        ));
                    }
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
          $this->addLanguageFiles(NAVBARLINKS_PATH.$locale_path);
      }

      // set the locale from the CMS
      $this->translator->setLocale(strtolower(LANGUAGE));
    }

    /**
     * Initialize the Twig template engine
     */
    protected function initializeTwig()
    {
        $loader = new Twig_Loader_Filesystem(array(
          NAVBARLINKS_PATH.'/template/custom',
          NAVBARLINKS_PATH.'/template/default'
        ));

        $this->twig = new Twig_Environment($loader, array(
            'cache' => FRAMEWORK_PATH.'/temp/cache',
            'strict_variables' => true,
            'debug' => true,
            'autoescape' => false
        ));
        $this->twig->addExtension(new Twig_Extension_Debug());
        $this->twig->addGlobal('CMS_URL', WB_URL);
    }

    /**
     * Controller to create the Navbar links
     *
     * @param integer $menu
     * @param integer $level
     * @param boolean $add_dropdown_link
     * @param boolean $add_dropdown_divider
     * @param string $visibility
     * @param array $icons
     * @return string
     */
    public function getNavbarLinks($menu=0, $level=0, $add_dropdown_link=false, $add_dropdown_divider=true, $icons=null, $visibility='public')
    {
        self::$start_level = $level;
        self::$add_dropdown_link = $add_dropdown_link;
        self::$add_dropdown_divider = $add_dropdown_divider;

        // initialize the translation service
        $this->initializeTranslator();

        // initialize Twig
        $this->initializeTwig();

        self::$icons = $icons;

        $navbar = '';
        $this->buildNavbar($menu, $level, $visibility, null, $navbar);

        if (!empty($navbar)) {
            $navbar = $this->twig->render('ul.navbar.twig', array('navbar' => $navbar));
        }

        return $navbar;
    }

}
