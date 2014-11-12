/**
 * kitFramework::CKEditor
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de
 * @copyright 2014 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

CKEDITOR.plugins.add( 'hashtaglink', {
    lang : ['en','de'],
    icons: 'hashtaglink',
    init: function( editor ) {
        editor.addCommand( 'hashtaglinkDlg', new CKEDITOR.dialogCommand( 'hashtaglinkDlg' ) );
        editor.ui.addButton( 'hashtaglink', {
            label: editor.lang.hashtaglink.btnlabel,
            command: 'hashtaglinkDlg',
            toolbar: 'links'
        });
        CKEDITOR.dialog.add( 'hashtaglinkDlg', this.path + 'dialogs/hashtaglink.js' );
    }
});

CKEDITOR.plugins.setLang( 'hashtaglink', 'en', {
    btnlabel     : 'Insert/Edit a flexContent #hashtag',
    title        : 'Insert/Edit a flexContent #hashtag',
    name         : 'Insert/Edit a flexContent #hashtag',
    page         : '#Hashtag',
    target         : 'Target',
    cssclass     : 'CSS Class',
    notset       : 'Not set'
});

CKEDITOR.plugins.setLang( 'hashtaglink', 'de', {
    btnlabel     : 'Einen flexContent #hashtag einfügen/ändern',
    title        : 'Einen flexContent #hashtag einfügen/ändern',
    name         : 'Einen flexContent #hashtag einfügen/ändern',
    page         : '#Hashtag',
    target         : 'Ziel',
    cssclass     : 'CSS Klasse',
    notset       : 'Nicht gesetzt'
});
