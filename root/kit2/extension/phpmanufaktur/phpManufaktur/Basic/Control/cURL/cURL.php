<?php

/**
 * kitFramework::Basic
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de
 * @copyright 2013 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\Basic\Control\cURL;

use Silex\Application;

/**
 * Class to access the cURL library
 *
 * @author Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 *
 */
class cURL {

    protected $app = null;

    const USERAGENT = 'kitFramework::Basic';

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * Download a file with cURL from the given URL
     *
     * @param string $source_url
     * @param string $target_path
     * @throws \Exception
     */
    public function Download ($source_url, $target_path, &$info = array())
    {
        try {
            // time limit for cURL operation
            if (function_exists('set_time_limit') && !$this->app['utils']->isFunctionDisabled('set_time_limit')) {
                set_time_limit(180);
            }
            else {
                $this->app['monolog']->addDebug('Function `set_time_limit()` is disabled!', array(__METHOD__, __LINE__));
            }

            // first try to get the redirected URL
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $source_url);
            curl_setopt($ch, CURLOPT_HEADER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_USERAGENT, self::USERAGENT);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_USERPWD, "fd881e98b9f76fcd9f4d80e8c1cfca68ee9e35b4:x-oauth-basic");

            // set proxy if needed
            $this->app['utils']->setCURLproxy($ch);

            $header = curl_exec($ch);
            curl_close($ch);
            if (preg_match('#Location: (.*)#', $header, $redirect)) {
                // this is the redirected URL
                $source_url = trim($redirect[1]);
            }

            // init cURL
            $ch = curl_init();
            // set the cURL options
            curl_setopt($ch, CURLOPT_USERAGENT, self::USERAGENT);
            curl_setopt($ch, CURLOPT_URL, $source_url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_FAILONERROR, true);
            curl_setopt($ch, CURLOPT_AUTOREFERER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 1000);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_USERPWD, "fd881e98b9f76fcd9f4d80e8c1cfca68ee9e35b4:x-oauth-basic");

            // set proxy if needed
            $this->app['utils']->setCURLproxy($ch);

            // exec cURL and get the file content
            if (false === ($file_content = curl_exec($ch))) {
                throw new \Exception(sprintf('cURL Error: [%d] - %s', curl_errno($ch), curl_error($ch)));
            }
            if (! curl_errno($ch)) {
                $info = curl_getinfo($ch);
            }
            // close the connection
            curl_close($ch);

            if (isset($info['http_code']) && ($info['http_code'] != '200'))
                return false;

            // create the target file
            if (false === ($downloaded_file = fopen($target_path, 'w')))
                throw new \Exception('fopen() fails!');

            // write the content to the target file
            if (false === ($bytes = fwrite($downloaded_file, $file_content)))
                throw new \Exception('fwrite() fails!');

            // close the target file
            fclose($downloaded_file);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    } // curlDownload()

    /**
     * Download a file from the given URL and follow a redirect if needed
     *
     * @param string $source_url
     * @param string $target_path
     * @param array $info
     * @throws \Exception
     */
    public function DownloadRedirectedURL($source_url, $target_path, &$info = array())
    {
        if (!$this->Download($source_url, $target_path, $info)) {
            if (isset($info['http_code']) && ($info['http_code'] == '302') && isset($info['redirect_url']) && ! empty($info['redirect_url'])) {
                // follow the redirect URL!
                $redirect_url = $info['redirect_url'];
                $info = array();
                $this->Download($redirect_url, $target_path, $info);
            } elseif (isset($info['http_code']) && ($info['http_code'] != '200')) {
                throw new \Exception(sprintf('[GitHub Error] HTTP Code: %s - no further informations available!', $info['http_code']));
            }
        }
    }

}
