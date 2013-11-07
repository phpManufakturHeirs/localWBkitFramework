<?php

/**
 * kitFramework
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de
 * @copyright 2013 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 *
 * This language file contains only metrics
 */

if ('á' != "\xc3\xa1") {
    // the language files must be saved as UTF-8 (without BOM)
    throw new \Exception('The language file ' . __FILE__ . ' is damaged, it must be saved UTF-8 encoded!');
}

return array(
    'CURRENCY_SYMBOL'
        => '€',
    'DATE_FORMAT'
        => 'd.m.Y',
    'DATETIME_FORMAT'
        => 'd.m.Y H:i',
    'DECIMAL_SEPARATOR'
        => ',',
    'TIME_FORMAT'
        => 'H:i',
    'THOUSAND_SEPARATOR'
        => '.',
);
