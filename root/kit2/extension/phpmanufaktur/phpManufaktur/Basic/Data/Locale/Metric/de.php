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
    'April'
        => 'April',
    'August'
        => 'August',

    'CURRENCY_NAME_ISO'
        => 'EUR',
    'CURRENCY_NAME'
        => 'Euro',
    'CURRENCY_SYMBOL'
        => '€',

    'DATE_FORMAT'
        => 'd.m.Y',
    'DATETIME_FORMAT'
        => 'd.m.Y H:i',
    'December'
        => 'Dezember',
    'DECIMAL_SEPARATOR'
        => ',',

    'February'
        => 'Februar',
    'Friday'
        => 'Freitag',

    'January'
        => 'Januar',
    'July'
        => 'Juli',
    'June'
        => 'Juni',

    'March'
        => 'März',
    'May'
        => 'Mai',
    'Monday'
        => 'Montag',

    'November'
        => 'November',

    "o'clock"
        => 'Uhr',
    'October'
        => 'Oktober',

    'Saturday'
        => 'Samstag',
    'September'
        => 'September',
    'Sunday'
        => 'Sonntag',

    'TIME_FORMAT'
        => 'H:i',
    'THOUSAND_SEPARATOR'
        => '.',
    'Thursday'
        => 'Donnerstag',
    'Tuesday'
        => 'Dienstag',

    'Wednesday'
        => 'Mittwoch',
    'Weekday'
        => 'Wochentag',
    'Weekdays'
        => 'Wochentage',

);
