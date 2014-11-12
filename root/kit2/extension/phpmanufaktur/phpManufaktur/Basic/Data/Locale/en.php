<?php

/**
 * Basic
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de
 * @copyright 2013 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

if ('á' != "\xc3\xa1") {
    // the language files must be saved as UTF-8 (without BOM)
    throw new \Exception('The language file ' . __FILE__ . ' is damaged, it must be saved UTF-8 encoded!');
}

return array(
    'captcha-timeout'
        => 'The solution was received after the CAPTCHA timed out.',

    'help_accounts_list_json'
        => '<p>This file enable you to change the visible columns and the order of the fields shown in the <a href="%FRAMEWORK_URL%/admin/accounts/list" target="_blank">Account Overview List</a>.</p><p>Available fields for the usage in <var>columns</var> and <var>list > order > by</var> are: <var>id, username, email, password, displayname, last_login, roles, guid, guid_timestamp, guid_status, status</var> and <var>timestamp</var>. With <var>list > rows_per_page</var> you determine how many accounts will be shown per page.</p>',
    'help_cms_json'
        => '<p>This configuration file contains information about the parent Content Management System (CMS). If you have moved your website to another URL or have changed the hosting directory you should adapt the settings in this file.</p><p>The <a href="https://github.com/phpManufaktur/kitFramework/wiki/kitFramework-%23-Configuration#cmsjson" target="_blank">kitFramework WIKI</a> describe the settings for the <var>cms.json</var>.<p>',
    'help_config_jsoneditor_json'
        => '<p>This is the configuration file for the configuration editor you are using just in this moment.</p><p>Normally you should not change anything in this configuration. The file contains the help information for the different configuration files and the list of the available configuration files to avoid scanning the system each time.</p><p>If you are missing a configuration file, i.e. for a just installed extension, please use the <key>Rescan</key> button above to force a new scan.</p>',
    'help_doctrine_cms_json'
        => '<p>This configuration file contains the settings for the database connect. If you have changed the database settings for the parent Content Management System (CMS) you must also adapt the database settings in this file, otherwise the kitFramework will no longer work.</p><p>The <a href="https://github.com/phpManufaktur/kitFramework/wiki/kitFramework-%23-Configuration#doctrinecmsjson" target="_blank">kitFramework WIKI</a> describe the settings for the <var>doctrine.cms.json</var>.</p>',
    'help_framework_json'
        => '<p>This is the main configuration file for the kitFramework. Here you can switch on and off the <var>DEBUG</var> and <var>CACHE</var> mode and advice the kitFramework to load your user defined templates before the <var>default</var> templates.</p><p>The <a href="https://github.com/phpManufaktur/kitFramework/wiki/kitFramework-%23-Configuration" target="_blank">kitFramework WIKI</a> describe the settings for the <var>framework.json</var>.</p>',
    'help_proxy_json'
        => '<p>If you are using a proxy server you will need this configuration file. Please ask your system administrator for the needed settings.</p>',
    'help_swift_cms_json'
        => '<p>This file is needed to configure the email settings for the kitFramework. Please ask your email provider for the needed SMTP server, port, username and password.</p><p>You can check the email settings and <a href="%FRAMEWORK_URL%/admin/test/mail" target="_blank">send a testmail.</p><p>The <a href="https://github.com/phpManufaktur/kitFramework/wiki/kitFramework-%23-Configuration#swiftcmsjson" target="_blank">kitFramework WIKI</a> describe the settings for the <var>swift.cms.json</var>.</p>',

    'incorrect-captcha-sol'
        => 'The CAPTCHA solution was incorrect.',
    'invalid-request-cookie'
        => 'The challenge parameter of the ReCaptcha verify script was incorrect.',
    'invalid-site-private-key'
        => 'The private key for the ReCaptcha is invalid, please check the settings!',

);
