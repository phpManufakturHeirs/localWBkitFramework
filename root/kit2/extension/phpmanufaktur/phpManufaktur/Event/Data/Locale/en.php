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

/**
 * The language file for the ENGLISH language is NOT COMPLETE, you will miss many entries!
 *
 * Reason: The english messages, labels, titles a.s.o. are placed directly in the source code
 * and not available as separate file. Please use the de.php as reference for all translations!
 */

return array(

    'About'
        => '?',

    // event data columns
    'event_costs'
        => 'Costs',
    'event_date_from'
        => 'Date from',
    'event_date_to'
        => 'Date to',
    'event_deadline'
        => 'Deadline',
    'event_id'
        => 'ID',
    'event_participants_confirmed'
        => 'Part. conf.',
    'event_participants_max'
        => 'max. Part.',
    'event_participants_total'
        => 'Total',
    'event_publish_from'
        => 'Publish from',
    'event_publish_to'
        => 'Publish to',
    'event_status'
        => 'Status',
    'event_timestamp'
        => 'Timestamp',

);
