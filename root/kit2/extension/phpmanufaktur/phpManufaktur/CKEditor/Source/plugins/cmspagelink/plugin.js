/**
 * kitFramework::CKEditor
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de
 * @copyright 2014 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

CKEDITOR.plugins.add( 'cmspagelink', {
    lang : ['en','de'],
    icons: 'cmspagelink',
    init: function( editor ) {
        editor.addCommand( 'cmspagelinkDlg', new CKEDITOR.dialogCommand( 'cmspagelinkDlg' ) );
        editor.ui.addButton( 'cmspagelink', {
            label: editor.lang.cmspagelink.btnlabel,
            command: 'cmspagelinkDlg',
            toolbar: 'links'
        });
        CKEDITOR.dialog.add( 'cmspagelinkDlg', this.path + 'dialogs/cmspagelink.js' );
    }
});

CKEDITOR.plugins.setLang( 'cmspagelink', 'en', {
    btnlabel     : 'Insert/Edit page link from CMS',
    title        : 'Insert/Edit page link from CMS',
    name         : 'Insert/Edit page link from CMS',
    page         : 'Page',
    target         : 'Target',
    cssclass     : 'CSS Class',
    notset       : 'Not set'
});

CKEDITOR.plugins.setLang( 'cmspagelink', 'de', {
    btnlabel     : 'CMS Seitenlink einfügen/ändern',
    title        : 'CMS Seitenlink einfügen/ändern',
    name         : 'CMS Seitenlink einfügen/ändern',
    page         : 'Seite',
    target         : 'Ziel',
    cssclass     : 'CSS Klasse',
    notset       : 'Nicht gesetzt'
});
