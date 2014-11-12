<?php

/**
 * miniShop - phpManufaktur
 *
 * This file was generated by the kitFramework
 */

try {
    // set the error handling
    ini_set('display_errors', 1);
    error_reporting(-1);

    if (!defined('WB_PATH') || !isset($_SESSION['USERNAME']))
        throw new Exception('Access is not authorized, please authenticate first!');

    global $database;

    if (null === ($pwd = $database->get_one("SELECT `password` FROM `".TABLE_PREFIX."users` WHERE `username`='".$_SESSION['USERNAME']."'", MYSQL_ASSOC)))
        throw new Exception($database->get_error());

    $cms_info = array(
        'locale' => strtolower(LANGUAGE),
        'username' => $_SESSION['USERNAME'],
        'authentication' => $pwd,
        'target' => 'cms'
    );
    $CMS_PARAMETER = base64_encode(json_encode($cms_info));
    $cms_info['target'] = 'framework';
    $FRAMEWORK_PARAMETER = base64_encode(json_encode($cms_info));
    $CMS_URL = WB_URL;
    $TOGGLE_PAGETREE = '';
    if (defined('CAT_VERSION')) {
      $TOGGLE_PAGETREE = '<script type="text/javascript">$(document).ready(function() { togglePageTree(); });</script>';
    }
    
    echo <<<EOD
    <div style="width:100%;margin:0;padding:5px;color:#000;background-color:#fff;">
      <div style="width:100%;height:15px;margin:5px 0;padding:0;text-align:right;">
        <a href="{$CMS_URL}/kit2/minishop/cms/{$FRAMEWORK_PARAMETER}" target="_blank">
          <img src="{$CMS_URL}/kit2/extension/phpmanufaktur/phpManufaktur/Basic/Template/default/framework/image/kitframework_15x14.png" width="15" height="14" alt="Open in kitFramework" title="Open in kitFramework" />
        </a>
      </div>
      <iframe id="kitframework_iframe" width="100%" height="700" src="{$CMS_URL}/kit2/minishop/cms/{$CMS_PARAMETER}" name="minishop" frameborder="0" style="border:none" scrolling="auto" marginheight="0px" marginwidth="0px">
        <p>Sorry, but your browser does not support embedded frames!</p>
      </iframe>
      <div style="font-size:10px;text-align:right;margin:2px 0 0 0;padding:0;">
        <a href="https://kit2.phpmanufaktur.de" target="_blank">kitFramework by phpManufaktur</a>
      </div>
    </div>
    {$TOGGLE_PAGETREE}
EOD;
}
catch (Exception $e) {
    trigger_error($e->getMessage(), E_USER_ERROR);
}
