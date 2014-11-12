/**
 * kitFramework::CKEditor
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de
 * @copyright 2014 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

CKEDITOR.plugins.add( 'flexcontentlink', {
    lang : ['en','de'],
    icons: 'flexcontentlink',
    init: function( editor ) {
        editor.addCommand( 'flexcontentlinkDlg', new CKEDITOR.dialogCommand( 'flexcontentlinkDlg' ) );
        editor.ui.addButton( 'flexcontentlink', {
            label: editor.lang.flexcontentlink.btnlabel,
            command: 'flexcontentlinkDlg',
            toolbar: 'links'
        });
        CKEDITOR.dialog.add( 'flexcontentlinkDlg', this.path + 'dialogs/flexcontentlink.js' );
    }
});

CKEDITOR.plugins.setLang( 'flexcontentlink', 'en', {
    btnlabel     : 'Insert/Edit a flexContent article link',
    title        : 'Insert/Edit a flexContent article link',
    name         : 'Insert/Edit a flexContent article link',
    page         : 'Article',
    target         : 'Target',
    cssclass     : 'CSS Class',
    notset       : 'Not set'
});

CKEDITOR.plugins.setLang( 'flexcontentlink', 'de', {
    btnlabel     : 'Link auf einen flexContent Artikel einfügen/ändern',
    title        : 'Link auf einen flexContent Artikel einfügen/ändern',
    name         : 'Link auf einen flexContent Artikel einfügen/ändern',
    page         : 'Artikel',
    target         : 'Ziel',
    cssclass     : 'CSS Klasse',
    notset       : 'Nicht gesetzt'
});
