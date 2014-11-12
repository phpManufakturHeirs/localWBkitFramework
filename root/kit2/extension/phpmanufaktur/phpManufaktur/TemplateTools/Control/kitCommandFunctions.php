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

class kitCommandFunctions
{
    protected $app = null;

    /**
     * Constructor
     *
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * Execute the given kitCommand
     *
     * @param string $command
     * @param parameter $parameter
     * @param string $prompt
     * @return mixed
     */
    public function execute($command, $parameter=array(), $prompt=true)
    {
        $params = array(
            'cms' => array(
                'type' => CMS_TYPE,
                'version' => CMS_VERSION,
                'locale' => PAGE_LOCALE,
                'page_id' => PAGE_ID,
                'page_url' => PAGE_URL,
                'page_visibility' => PAGE_VISIBILITY,
                'user' => array(
                    'id' => CMS_USER_ID,
                    'name' => CMS_USER_USERNAME,
                    'email' => CMS_USER_EMAIL
                ),
            ),
            'GET' => array(),
            'POST' => array(),
            'parameter' => $parameter,
        );

        $option = array(
            CURLOPT_URL => FRAMEWORK_URL.'/command/'.strtolower($command),
            CURLOPT_HEADER => false,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_USERAGENT => 'kitFramework:TemplateTools',
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query(array('cms_parameter' => $params), '', '&'),
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_SSL_VERIFYPEER => false
        );

        $ch = curl_init();
        curl_setopt_array($ch, $option);

        if (false === ($response = curl_exec($ch))) {
            trigger_error(curl_error($ch), E_USER_ERROR);
        }
        curl_close($ch);
        if ($prompt) {
            echo $response;
        }
        else {
            return $response;
        }
    }
}
