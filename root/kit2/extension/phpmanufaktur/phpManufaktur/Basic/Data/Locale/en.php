<?php

/**
 * Event
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de/FacebookGallery
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
    'incorrect-captcha-sol'
        => 'The CAPTCHA solution was incorrect.',
    'invalid-request-cookie'
        => 'The challenge parameter of the ReCaptcha verify script was incorrect.',
    'invalid-site-private-key'
        => 'The private key for the ReCaptcha is invalid, please check the settings!',

);
