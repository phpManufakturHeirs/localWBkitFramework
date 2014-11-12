<?php

/**
 * kitFramework::Basic
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de
 * @copyright 2014 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\Basic\Control;

use Michelf\Markdown;
use Michelf\MarkdownExtra;
use Silex\Application;

class MarkdownFunctions
{
    protected $app = null;
    protected $Markdown = null;
    protected $MarkdownExtra = null;

    public function __construct(Application $app)
    {
        $this->app = $app;
        $this->Markdown = new Markdown();
        $this->MarkdownExtra = new MarkdownExtra();
    }

    /**
     * Transform the submitted markdown $text to HTML and return it
     *
     * @param unknown $text
     * @param string $extra
     */
    public function html($text, $extra=true, $prompt=true)
    {
        $markdown = $extra ? $this->Markdown->defaultTransform($text) : $this->MarkdownExtra->defaultTransform($text);
        if ($prompt) {
            echo $markdown;
        }
        else {
            return $markdown;
        }
    }

    /**
     * Load the given markdown file from $path and return is as HTML
     *
     * @param string $path
     * @param boolean $extra use Markdown Extra?
     * @param boolean $prompt
     * @throws \Exception
     */
    public function file($path, $extra=true, $prompt=true)
    {
        if ($this->app['filesystem']->exists($path)) {
            if (false === ($text = file_get_contents($path))) {
                $error = error_get_last();
                throw new \Exception($error['message']);
            }
            return $this->html($text, $extra, $prompt);
        }
        else {
            return $this->app['translator']->trans('The file <i>%file%</i> does not exists!',
                array('%file%' => $path));
        }
    }
}
