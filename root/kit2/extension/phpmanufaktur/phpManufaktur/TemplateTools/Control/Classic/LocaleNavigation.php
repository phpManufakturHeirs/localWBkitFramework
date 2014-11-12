<?php

/**
 * TemplateTools
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de/TemplateTools
 * @copyright 2014 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\TemplateTools\Control\Classic;

use Silex\Application;
use phpManufaktur\TemplateTools\Data\Locale\Data\Locale;

class LocaleNavigation
{
    protected $app = null;
    protected $LocaleData = null;

    protected static $options = array(
        'locales' => null,
        'menu' => 1,
        'visibility' => array(
            'public'
        ),
        'template_directory' => '@pattern/classic/function/locale/'
    );

    /**
     * Constructor
     *
     * @param Application $app
    */
    public function __construct(Application $app)
    {
        $this->app = $app;
        $this->LocaleData = new Locale($app);
    }

    /**
     * Check the $options and set self::$options
     *
     * @param array $options
     */
    protected function checkOptions($options)
    {
        if (isset($options['locales']) && is_array($options['locales']) && !empty($options['locales'])) {
            self::$options['locales'] = array();
            foreach ($options['locales'] as $locale) {
                self::$options['locales'][] = strtolower(trim($locale));
            }
        }
        if (isset($options['menu']) && is_numeric($options['menu']) && ($options['menu'] > 0)) {
            self::$options['menu'] = intval($options['menu']);
        }
        if (isset($options['visibility']) && is_array($options['visibility']) && !empty($options['visibility'])) {
            self::$options['visibility'] = $options['visibility'];
        }
        if (isset($options['template_directory']) && !empty($options['template_directory'])) {
            self::$options['template_directory'] = rtrim($options['template_directory'], '/').'/';
        }
    }

    /**
     * Check the given locales or get them from the page tree
     *
     * @return boolean
     */
    protected function checkLocales()
    {
        if (is_array(self::$options['locales']) && !empty(self::$options['locales'])) {
            // loop through the given locales
            $locales = array();
            foreach (self::$options['locales'] as $locale) {
                $locale = strtolower(trim($locale));
                if ($this->LocaleData->existsCode($locale)) {
                    // the locale exists in the country table, accept it
                    $locales[] = $locale;
                }
            }
            self::$options['locales'] = $locales;
            return (!empty(self::$options['locales']));
        }

        // no locales given, get them from the pages table
        $visibility = '';
        foreach (self::$options['visibility'] as $visi) {
            if (!empty($visibility)) {
                $visibility .= ' OR ';
            }
            $visibility .= "`visibility`='$visi'";
        }
        $SQL = "SELECT `link` FROM `".CMS_TABLE_PREFIX."pages` WHERE `level`=0 AND ".
            "`menu`=".self::$options['menu']." AND ($visibility) ORDER BY `position` ASC";
        $results = $this->app['db']->fetchAll($SQL);
        $locales = array();
        if (is_array($results)) {
            foreach ($results as $locale) {
                // ignore the first character '/'
                $locale = substr($locale['link'], 1);
                if ($this->LocaleData->existsCode($locale)) {
                    // the locale exists in the country table, accept it
                    $locales[] = $locale;
                }
            }
            self::$options['locales'] = $locales;
            return (!empty(self::$options['locales']));
        }

        // no valid locales!
        return false;
    }

    /**
     * Return a locale navigation for the current page tree
     *
     * @param array $options
     * @param boolean $prompt
     * @throws \InvalidArgumentException
     * @return string
     */
    public function locale_navigation($options=array(), $prompt=true)
    {
        // first check the options
        $this->checkOptions($options);

        if (!$this->checkLocales()) {
            throw new \InvalidArgumentException('There exists no valid locales for the locale_navigation(). Please check the pages settings and the options.');
        }

        $locale_id = $this->app['cms']->page_option('locale_id', false);

        $visibility = '';
        foreach (self::$options['visibility'] as $visi) {
            if (!empty($visibility)) {
                $visibility .= ' OR ';
            }
            $visibility .= "`visibility`='$visi'";
        }

        $locales = array();
        foreach (self::$options['locales'] as $locale) {
            $url = CMS_URL.CMS_PAGES_DIRECTORY.'/'.$locale.CMS_PAGES_EXTENSION;
            if (($locale == PAGE_LOCALE) && (PAGE_ID > 0)) {
                // this is the current page
                $url = PAGE_URL;
            }
            elseif (!is_null($locale_id)) {
                // search for fitting pages
                $SQL = "SELECT `link` FROM `".CMS_TABLE_PREFIX."pages` WHERE `keywords` LIKE '%[%locale_id%:%]%' AND ".
                    "`language`='$locale' AND ($visibility) AND `menu`=".self::$options['menu'];
                $link = $this->app['db']->fetchColumn($SQL);
                if (!empty($link)) {
                    $url = CMS_URL.CMS_PAGES_DIRECTORY.$link.CMS_PAGES_EXTENSION;
                }
            }
            elseif ((CMS_TYPE == 'LEPTON') || (CMS_TYPE == 'BlackCat')) {
                $SQL = "SELECT `link` FROM `".CMS_TABLE_PREFIX."pages` WHERE `page_id`=".PAGE_ID;
                $link = $this->app['db']->fetchColumn($SQL);
                if (!empty($link)) {
                    $link = substr($link, strlen('/en'));
                    $link = "/$locale$link";
                    $SQL = "SELECT `link` FROM `".CMS_TABLE_PREFIX."pages` WHERE `link`='$link' AND ".
                        "($visibility) AND `menu`=".self::$options['menu'];
                    $link = $this->app['db']->fetchColumn($SQL);
                    if (!empty($link)) {
                        $url = CMS_URL.CMS_PAGES_DIRECTORY.$link.CMS_PAGES_EXTENSION;
                    }
                }
            }

            $locales[] = array(
                'locale' => $locale,
                'name' => $this->LocaleData->getName($locale),
                'native_name' => $this->LocaleData->getNativeName($locale),
                'active' => ($locale == PAGE_LOCALE),
                'url' => $url
            );
        }

        $result = $this->app['twig']->render(
            self::$options['template_directory'].'locale.navigation.twig',
            array(
                'locales' => $locales
            )
        );
        if ($prompt) {
            echo $result;
        }
        return $result;
    }
}
