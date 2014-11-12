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

require_once EXTENSION_PATH.'/browser/1.9.0/lib/Browser.php';

class BrowserDetect
{
    protected $app = null;
    protected $Browser = null;

    /**
     * Constructor
     *
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
        $this->Browser = new \Browser();
    }

    /**
     * The name of the browser.  All return types are from the class contants
     *
     * @return string Name of the browser
     */
    public function name($prompt=true)
    {
        $name = $this->Browser->getBrowser();
        if ($prompt) {
            echo $name;
        }
        return $name;
    }

    /**
     * The version of the browser.
     *
     * @return string Version of the browser (will only contain alpha-numeric characters and a period)
     */
    public function version($main_version_only=false, $prompt=true)
    {
        $version = $this->Browser->getVersion();
        if ($main_version_only && strpos($version, '.')) {
            $version_array = explode('.', $version);
            if (isset($version_array[0])) {
                $version = $version_array[0];
            }
        }
        if ($prompt) {
            echo $version;
        }
        return $version;
    }

    /**
     * The name of the platform.  All return types are from the class contants
     *
     * @return string Name of the browser
     */
    public function platform($prompt=true)
    {
        $platform = $this->Browser->getPlatform();
        if ($prompt) {
            echo $platform;
        }
        return $platform;
    }

    /**
     * Is the browser from a mobile device?
     *
     * @return boolean True if the browser is from a mobile device otherwise false
     */
    public function is_mobile()
    {
        return $this->Browser->isMobile();
    }

    /**
     * Is the browser from a tablet device?
     *
     * @return boolean True if the browser is from a tablet device otherwise false
     */
    public function is_tablet()
    {
        return $this->Browser->isTablet();
    }

    /**
     * Check if browser is not mobile and not tablet (simplified check!)
     *
     * @return boolean
     */
    public function is_desktop()
    {
        return (!$this->Browser->isMobile() && !$this->Browser->isTablet());
    }

    /**
     * Return the IP address from which the Browser is executed
     *
     * @param boolean $prompt
     * @return string
     */
    public function ip($prompt=true)
    {
        $ip = $_SERVER['REMOTE_ADDR'];
        if ($prompt) {
            echo $ip;
        }
        return $ip;
    }
}
