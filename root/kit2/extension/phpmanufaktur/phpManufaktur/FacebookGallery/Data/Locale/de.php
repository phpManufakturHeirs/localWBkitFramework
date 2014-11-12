<?php

/**
 * kitFramework::FacebookGallery
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de/FacebookGallery
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
  'Allowed values for the parameter <em>limit</em> are 2,3,4,6,8,9 and 12 but not %limit%!'
    => 'Erlaubte Werte für den Parameter <em>limit</em> sind 2,3,4,6,8,9 und 12 jedoch nicht %limit%!',
  'Available galleries'
    => 'Verfügbare Galerien',
  'Copy to clipboard'
    => 'In die Zwischenablage kopieren',
  'Copy to clipboard: <CTRL>+C, Enter'
    => 'Kopieren mit: <STRG>+C, Eingabetaste',
  'Created at'
    => 'Erstellt am',
  'FacebookGallery - Full Page Image Gallery Example'
    => 'FacebookGallery - Beispiel für eine Bildergalerie',
  'GALLERY_TYPE_MOBILE'
    => 'Mobile Uploads',
  'GALLERY_TYPE_NORMAL'
    => 'Allgemein',
  'GALLERY_TYPE_PROFILE'
    => 'Profilbilder',
  'GALLERY_TYPE_WALL'
    => 'Chronik',
  'Hint: This error occur if you try to access protected Facebook galleries, which belong to private account. The kitFramework FacebookGallery does only support galleries which belong to fanpages and public pages.'
    => 'Hinweis: Dieser Fehler tritt auf, wenn Sie versuchen auf geschützte Facebook Galerien zuzugreifen, die zu privaten Zugängen gehören. Die kitFramework FacebookGallery unterstützt ausschließlich Galerien, die zu Fanpages bzw. öffentlichen Seiten gehören.',
  'kitFramework FacebookGallery is using the Full Page Image Gallery'
    => 'kitFramework FacebookGallery verwendet die Full Page Image Gallery',
  'The facebook ID <b>%facebook_id%</b> provide the following galleries'
    => 'Die Facebook ID <b>%facebook_id%</b> stellt die folgenden Galerien zur Verfügung',
  'This image is not available in size %size%, try another size value!'
    => 'Diese Abbildung ist in der Größe %size% nicht verfügbar, versuchen Sie einen anderen Größenwert!',
  'View Thumbs'
    => 'Vorschaubilder zeigen',

);
