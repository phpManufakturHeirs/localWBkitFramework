/**
 * kitFramework::CKEditor
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de
 * @copyright 2014 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

CKEDITOR.dialog.add( 'cmspagelinkDlg', function( editor ) {
    // route '/extension/phpmanufaktur/phpManufaktur/CKEditor/Source/plugins/cmspagelink/dialog'
    // is handled in kfCKEditor bootstrap.include.php !
    var xml = CKEDITOR.ajax.loadXml( CKEDITOR.plugins.getPath( 'cmspagelink' ) + 'dialog' );
    if ( xml === null ) {
        return {
            title: editor.lang.cmspagelink.title,
            minWidth: 380,
            minHeight: 130,
            contents: [{
                id: 'tab1',
                label: 'Tab1',
                title: 'Tab1',
                elements : [{
                    type: 'html',
                    html: '<div class="error">Error loading pages list!</div>'
                }],
            }]
        };
    }
    var itemNodes = xml.selectNodes( 'data/pageslist/item' );
    var items = new Array();    // items array
    var pages = new Array();
    for ( var i = 0 ; i < itemNodes.length ; i++ ) {
        var node = itemNodes[i];
        items[i] = new Array( decodeURIComponent(node.getAttribute("value"), node.getAttribute("id")) );
        pages[node.getAttribute("id")] = node.getAttribute("id"); //decodeURIComponent(node.getAttribute("value"));
    }

    return {
        title: editor.lang.cmspagelink.title,
        minWidth: 380,
        minHeight: 130,
        contents: [{
            id: 'tab1',
            label: 'Tab1',
            elements : [{
                id: 'pageslist',
                type: 'select',
                label: editor.lang.cmspagelink.page,
                items: items
            }, {
                id: 'target',
                type: 'select',
                label: editor.lang.flexcontentlink.target,
                items: [['_self'],['_blank'],['_top'],['_parent']],
                'default': ''
            }, {
                id: 'pagelinkclass',
                type: 'text',
                label: editor.lang.cmspagelink.cssclass,
            }] // end elements
        }], // end contents
        onOk: function() {
            var dialog    = this;
            var selection = editor.getSelection().getSelectedElement();
            var page_id   = dialog.getValueOf( 'tab1', 'pageslist' );


            var title = pages[page_id];
            //alert(title);
            //var separator = title.indexOf('] ')+2;
            //title = title.substr(separator);

            if ( selection === null ) {
                var html  = editor.getSelection().getSelectedText();
                if (html.length === 0) {
                    html = title;
                }
                selection = CKEDITOR.dom.element.createFromHtml(html);
            }
            var css_class = dialog.getValueOf('tab1', 'pagelinkclass');
            var target = dialog.getValueOf('tab1', 'target');

            var insert    = '<a href="'+page_id+'"'+(css_class===''?'':' class="'+css_class+'"')+(target === '' ? '' : ' target="'+target+'"')+'></a>';
            var element   = CKEDITOR.dom.element.createFromHtml(insert);
            selection.appendTo(element);
            editor.insertElement(element);
            return true;
        },
        resizable: 3
    };
});
