<?php

/**
 * MediaBrowser
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
    '- please select -'
        => '- bitte auswählen -',
    "Can't change the access rights for the file <b>%file%</b>!"
        => 'Konnte die Zugriffsrechte für die Datei <b>%file%</b> nicht ändern!',
    "Can't create the directory <b>%directory%</b>, message: <em>%message%</em>"
        => 'Konnte das Verzeichnis <b>%directory%</b> nicht anlegen, Meldung: <em>%message%</em>',
    "Can't change the last modification time for the file <b>%file%</b>!"
        => 'Konnte die Zeit der letzten Änderung für die Datei <b>%file%</b> nicht setzen!',
    'Create directory'
        => 'Verzeichnis anlegen',
    'Delete'
        => 'Löschen',
    'No file selected!'
        => 'Es wurde keine Datei ausgewählt!',
    'one level up'
        => 'eine Verzeichnisebene höher',
    'Submit file'
        => 'Datei übermitteln',
    '<p>The directory <b>%directory%</b> was successfull created.</p>'
        => '<p>Das Verzeichnis <b>%directory%</b> wurde erfolgreich angelegt.</p>',
    '<p>The directory <b>%directory%</b> was successfull deleted.</p>'
        => '<p>Das Verzeichnis <b>%directory%</b> wurde erfolgreich gelöscht.</p>',
    'The file extension <b>%extension%</b> is not supported!'
        => 'Die Dateiendung <b>%extension%</b> wird nicht unterstützt!',
    '<p>The file <b>%file%</b> was successfull deleted.</p>'
        => '<p>Die Datei <b>%file%</b> wurde erfolgreich gelöscht.</p>',
    '<p>The file <b>%file%</b> was successfull uploaded.</p>'
        => '<p>Die Datei <b>%file%</b> wurde erfolgreich übermittelt.</p>',
    '<p>This directory does not contain any media files</p>'
        => '<p>Dieses Verzeichnis enthält keine Medien Dateien</p>',
    'Upload file'
        => 'Datei hochladen',

);