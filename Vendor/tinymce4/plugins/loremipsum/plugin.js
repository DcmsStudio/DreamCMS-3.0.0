tinymce.PluginManager.requireLangPack('loremipsum');

tinymce.PluginManager.add( 'loremipsum', function ( editor )
{
	function openmanager() {
		var title="Create Lorem Ipsum";
		win = editor.windowManager.open({
			title: title,
			file: tinyMCE.baseURL + '/plugins/loremipsum/loremipsum.html',
			width: 500,
			height: 240,
			inline: 1,
			buttons: [
				{
					text: 'Insert',
					onclick: function() {
						// Top most window object
                        var bookmark = editor.selection.getBookmark();

						var win = editor.windowManager.getWindows()[0];
						var val = win.getContentWindow().LoremIpsumDialog.insert();
						var sel = editor.selection.getNode();
						editor.selection.select( sel );


						sel.innerHTML = val;



						editor.undoManager.transact(function() {
							editor.setContent(editor.getContent({raw: true}));
						});

                        editor.selection.moveToBookmark(bookmark);

						//editor.execCommand('mceReplaceContent', false, val);


						// Close the window
						win.close();
					}
				},

				{text: 'Close', onclick: 'close'}
			]
		});
	}




	editor.addButton('loremipsum', {
		icon: ' fa fa-magic',
		//image: tinyMCE.baseURL + '/plugins/loremipsum/img/loremipsum.png',
		tooltip: 'Create Lorem Ipsum',
		shortcut: 'Ctrl+LI',
		//stateSelector: ['img[data-mce-placeholder=qrcode]'],
		onclick: openmanager
	});

});
