<?php

/**
 * kitFramework::Basic
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de
 * @copyright 2014 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\Basic\Control\CMS\WebsiteBaker;

class kitFrameworkInfo
{
    const USERAGENT = 'kitFramework::Basic';

    protected static $proxy = null;
    protected static $proxy_auth = 'NONE';
    protected static $proxy_port = null;
    protected static $proxy_usrpwd = null;

    /**
     * Constructor
     */
    public function __construct()
    {
        // FRAMEWORK_PATH is not set at this point!
        $proxy_file = WB_PATH.'/kit2/config/proxy.json';

        if (file_exists($proxy_file)) {
            // set the proxy options
            if (null === ($proxy = json_decode(file_get_contents($proxy_file), true))) {
                trigger_error("Can't read the proxy file $proxy_file", E_USER_ERROR);
            }
            if (isset($proxy['PROXYAUTH']) && ($proxy['PROXYAUTH'] != 'NONE')) {
                if (strtoupper($proxy['PROXYAUTH']) == 'NTLM') {
                    self::$proxy_auth = CURLAUTH_NTLM;
                }
                else {
                    self::$proxy_auth = CURLAUTH_BASIC;
                }
                self::$proxy_usrpwd = $proxy['PROXYUSERPWD'];
            }
            self::$proxy = $proxy['PROXY'];
            self::$proxy_port = $proxy['PROXYPORT'];
        }
    }

    protected function Query($url, $parameter=array())
    {
        $option = array(
            CURLOPT_URL => $url,
            CURLOPT_HEADER => false,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_USERAGENT => self::USERAGENT,
            CURLOPT_POSTFIELDS => http_build_query($parameter, '', '&'),
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_SSL_VERIFYPEER => false
        );

        if (!is_null(self::$proxy)) {
            // set options for the PROXY
            $option[CURLOPT_PROXYAUTH] = self::$proxy_auth;
            $option[CURLOPT_PROXY] = self::$proxy;
            $option[CURLOPT_PROXYPORT] = self::$proxy_port;
            $option[CURLOPT_PROXYUSERPWD] = self::$proxy_usrpwd;
        }

        $ch = curl_init();
        curl_setopt_array($ch, $option);

        if (false === ($response = curl_exec($ch))) {
            trigger_error(curl_error($ch), E_USER_ERROR);
        }
        $response = curl_exec($ch);
        curl_close($ch);
        return json_decode($response, true);
    }

    public function isFilterAvailable($filter)
    {
        $result = $this->Query(WB_URL.'/kit2/filter/exists/'.$filter);
        return (isset($result['filter_exists'])) ? $result['filter_exists'] : false;
    }

    public function isCommandAvailable($command)
    {
        $result = $this->Query(WB_URL.'/kit2/command/exists/'.$command);
        return (isset($result['command_exists'])) ? $result['command_exists'] : false;
    }

}
