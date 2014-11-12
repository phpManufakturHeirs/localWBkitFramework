<?php

/**
 * TemplateTools
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de/TemplateTools
 * @copyright 2014 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\TemplateTools\Control;

use Silex\Application;
use phpManufaktur\Basic\Control\Utils;
use Symfony\Component\Finder\Finder;

class Tools
{
    protected $app = null;
    protected $utils = null;

    /**
     * Constructor
     *
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
        $this->utils = new Utils($app);
    }

    /**
     * Sanitize a given path
     *
     * @param string $path
     * @return string
     */
    public function sanitizePath($path)
    {
        return $this->utils->sanitizePath($path);
    }

    /**
     * Sanitize variables and prepare them for saving in a MySQL record
     *
     * @param mixed $item
     * @return mixed
     */
    public function sanitizeVariable ($item)
    {
        return $this->utils->sanitizeVariable($item);
    }

    /**
     * Sanitize a text variable and prepare it for saving in a MySQL record
     *
     * @param string $text
     * @return string
     */
    public function sanitizeText ($text)
    {
        return $this->utils->sanitizeText($text);
    }

    /**
     * Unsanitize a text variable and prepare it for output
     *
     * @param string $text
     * @return string
     */
    public function unsanitizeText($text)
    {
        return $this->utils->unsanitizeText($text);
    }

    /**
     * Scan the given $locale_path for language files and add them to the global
     * translator resource
     *
     * @param string $locale_path
     * @throws \Exception
     */
    function addLanguageFiles($locale_path)
    {
        // load the language files for all extensions
        $locales = new Finder();
        $locales->name('*.php')->in($locale_path);
        $locales->depth('== 0');
        foreach ($locales as $locale) {
            // add the locale resource file
            $this->app['translator'] = $this->app->share($this->app->extend('translator', function ($translator) use ($locale) {
                $lang_array = include_once $locale->getRealpath();
                $translator->addResource('array', $lang_array, $locale->getBasename('.php'));
                return $translator;
            }));
            $this->app['monolog']->addDebug('Added language file: '.$locale->getRealpath());
        }
    }

    /**
     * Ellipsis function - shorten the given $text to $length at the nearest
     * space and add three dots at the end ...
     *
     * @param string $text
     * @param number $length
     * @param boolean $striptags remove HTML tags by default
     * @param boolean $htmlpurifier use HTML Purifier (false by default, ignored if striptags=true)
     * @param boolean $prompt
     * @return string
     * @todo DOM Parser does not work properly, disabled $htmlpurifier !!!
     */
    public function ellipsis($text, $length=100, $striptags=true, $htmlpurifier=false, $prompt=true)
    {
        // at the moment we support only striped text ...
        $result = $this->utils->Ellipsis($text, $length, true, false);
        if ($prompt) {
            echo $result;
        }
        else {
            return $result;
        }
    }

    /**
     * Makes a technical name human readable.
     *
     * Sequences of underscores are replaced by single spaces. The first letter
     * of the resulting string is capitalized, while all other letters are
     * turned to lowercase.
     *
     * @param string $text The text to humanize.
     * @param boolean $prompt
     * @return string The humanized text.
     */
    public function humanize($text, $prompt=true)
    {
        $result = $this->utils->humanize($text);
        if ($prompt) {
            echo $result;
        }
        else {
            return $result;
        }
    }

    /**
     * Like json_encode but format the JSON in a human friendly way
     *
     * @param array $chunk the array to save as JSON
     * @param string $already_json set true if $chunk is already JSON and should be formatted
     * @return string
     */
    public function formatJSON($chunk, $already_json = false)
    {
        return $this->utils->JSONFormat($chunk, $already_json);
    }

    /**
     * Read the specified JSON file and return array
     *
     * @param string $file path to JSON file
     * @throws \Exception
     * @return array JSON items
     */
    public function readJSON($file)
    {
        return $this->utils->readConfiguration($file);
    }

    /**
     * Get the first headline <h1>, <h2> or <h3> from the html content and
     * return the content without the tags
     *
     * @param string $content html text
     * @return NULL|string headline content
     */
    public function get_first_header($content)
    {
        $DOM = new \DOMDocument();
        libxml_use_internal_errors(true);
        if (!$DOM->loadHTML($content)) {
            return null;
        }
        libxml_clear_errors();

        if (null != ($node = $DOM->getElementsByTagName('h1')->item(0))) {
            return $node->nodeValue;
        }
        elseif (null != ($node = $DOM->getElementsByTagName('h2')->item(0))) {
            return $node->nodeValue;
        }
        elseif (null != ($node = $DOM->getElementsByTagName('h3')->item(0))) {
            return $node->nodeValue;
        }
        return null;
    }

    /**
     * Remove the first headline <h1>, <h2> or <h3> from the html content and
     * return the modified html
     *
     * @param string $content
     * @return NULL|string
     */
    public function remove_first_header($content)
    {
        $DOM = new \DOMDocument();
        libxml_use_internal_errors(true);
        if (!$DOM->loadHTML($content)) {
            return null;
        }
        libxml_clear_errors();

        if (null != ($node = $DOM->getElementsByTagName('h1')->item(0))) {
            $node->parentNode->removeChild($node);
        }
        elseif (null != ($node = $DOM->getElementsByTagName('h2')->item(0))) {
            $node->parentNode->removeChild($node);
        }
        elseif (null != ($node = $DOM->getElementsByTagName('h3')->item(0))) {
            $node->parentNode->removeChild($node);
        }
        $content = $DOM->saveHTML();
        return str_replace(array('%5B','%5D'), array('[',']'), $content );
    }
}
