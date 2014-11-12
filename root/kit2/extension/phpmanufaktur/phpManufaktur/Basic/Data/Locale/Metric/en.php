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
    'CURRENCY_NAME_ISO'
        => 'USD',
    'CURRENCY_NAME'
        => 'US-Dollar',
    'CURRENCY_SYMBOL'
        => '$',
    'DATE_FORMAT'
        => 'm/d/Y',
    'DATETIME_FORMAT'
        => 'm/d/Y - h:i a',
    'DECIMAL_SEPARATOR'
        => '.',
    'THOUSAND_SEPARATOR'
        => ',',
    'TIME_FORMAT'
        => 'h:i a',
);
