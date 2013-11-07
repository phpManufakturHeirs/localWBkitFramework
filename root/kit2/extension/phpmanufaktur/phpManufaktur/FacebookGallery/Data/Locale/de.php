<?php

/**
 * FacebookGallery
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
    'Available galleries'
        => 'Verfügbare Galerien',
    'Copy to clipboard'
        => 'In die Zwischenablage kopieren',
    'Copy to clipboard: <CTRL>+C, Enter'
        => 'Kopieren mit: <STRG>+C, Eingabetaste',
    'Created at'
        => 'Erstellt am',
    'Facebook Gallery: Error message'
        => 'Facebook Gallery: Fehlermeldung',
    'Gallery'
        => 'Galerie',
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
    'Please help to improve this free software and report the problem to the <a href="%link%" target="_blank">phpManufaktur Support Group</a>.'
        => 'Bitte helfen Sie mit diese freie Software zu verbessern und melden Sie das aufgetretene Problem der <a href="%link%" target="_blank">phpManufaktur Support Group</a>',
    'The facebook ID <b>%facebook_id%</b> provide the following galleries'
        => 'Die Facebook ID <b>%facebook_id%</b> stellt die folgenden Galerien zur Verfügung',
    'This photo is not available in size <b>%size%</b>!'
        => 'Dieses Foto ist nicht in der Größe <b>%size%</b> verfügbar!',
    'View Thumbs'
        => 'Vorschaubilder zeigen',
);