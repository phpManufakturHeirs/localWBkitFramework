<?php

/**
 * kitFramework::MediaBrowser
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de/MediaBrowser
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
  'Can\'t change the access rights for the file <b>%file%</b>!'
    => 'Konnte die Zugriffsrechte für die Datei <b>%file%</b> nicht ändern!',
  'Can\'t change the last modification time for the file <b>%file%</b>!'
    => 'Konnte die Zeit der letzten Änderung für die Datei <b>%file%</b> nicht setzen!',
  'Can\'t create the directory <b>%directory%</b>, message: <em>%message%</em>'
    => 'Konnte das Verzeichnis <b>%directory%</b> nicht anlegen, Meldung: <em>%message%</em>',
  'Create directory'
    => 'Verzeichnis anlegen',
  'Delete directory: %directory%'
    => 'Verzeichnis löschen: %directory%',
  'Delete file: %file%'
    => 'Datei löschen: %file%',
  'Directory'
    => 'Verzeichnis',
  'Exit MediaBrowser'
    => 'MediaBrowser schließen',
  'kitFramework MediaBrowser'
    => 'kitFramework MediaBrowser',
  'Media file'
    => 'Mediendatei',
  'Mode'
    => 'Modus',
  'No file selected!'
    => 'Es wurde keine Datei ausgewählt!',
  'one level up'
    => 'eine Verzeichnisebene höher',
  'Ooops, can\'t validate the upload form, something went wrong ...'
    => 'Oh, das ist etwas schiefgelaufen, kann den Upload Dialog nicht überprüfen ...',
  'Select file: %file%'
    => 'Datei auswählen: %file%',
  'Start'
    => 'Start',
  'Submit file'
    => 'Datei übermitteln',
  'The directory %directory% was successfull created.'
    => 'Das Verzeichnis %directory% wurde erfolgreich angelegt.',
  'The directory %directory% was successfull deleted.'
    => 'Das Verzeichnis %directory% wurde erfolgreich gelöscht.',
  'The file %file% was successfull deleted.'
    => 'Die Datei %file% wurde erfolgreich gelöscht.',
  'The file %file% was successfull uploaded.'
    => 'Die Datei %file% wurde erfolgreich übertragen.',
  'The file extension %extension% is not supported!'
    => 'Die Dateiendung %extension% wird nicht unterstützt!',
  'This directory does not contain any media files.'
    => 'Dieses Verzeichnis enthält keine Medien Dateien.',
  'Upload file'
    => 'Datei hochladen',
  
);
