<?php

/**
 * kitFramework::Event
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de/Event
 * @copyright 2014 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 *
 * This file was created by the kitFramework i18nEditor
 */

if ('á' != "\xc3\xa1") {
    // the language files must be saved as UTF-8 (without BOM)
    throw new \Exception('The language file ' . __FILE__ . ' is damaged, it must be saved UTF-8 encoded!');
}

return array(
  'About'
    => '?',
  'but not at %dates%.'
    => 'but not at %dates%.',
  'Day sequence'
    => 'Repeat each x-days',
  'Event costs'
    => 'Costs',
  'Event date from'
    => 'Date from',
  'Event date to'
    => 'Date to',
  'Event deadline'
    => 'Deadline',
  'Event id'
    => 'ID',
  'Event participants confirmed'
    => 'Participants confirmed',
  'Event participants max'
    => 'Participants maximum',
  'Event publish from'
    => 'Publish from',
  'Event publish to'
    => 'Publish to',
  'Event status'
    => 'Status',
  'Exclude dates'
    => 'Exclude dates',
  'Month pattern day view'
    => 'Select day',
  'Month pattern sequence'
    => 'Repeat each x-month',
  'Month pattern type'
    => 'Select pattern',
  'Month sequence day view'
    => 'At day x of month',
  'Month sequence month'
    => 'Repeat each x-month',
  'Month type'
    => 'Select type',
  'Recurring type'
    => 'Select type',
  'Week day view'
    => 'Weekday',
  'Week sequence'
    => 'Repeat each x-weeks',
  'Year pattern day view'
    => 'Select day',
  'Year pattern month view'
    => 'At month',
  'Year pattern type'
    => 'Select pattern',
  'Year repeat'
    => 'Repeat each x-year',
  'Year sequence day view'
    => 'At day x of month',
  'Year sequence month view'
    => 'At month',
  'Year type'
    => 'Select type',
  
);
