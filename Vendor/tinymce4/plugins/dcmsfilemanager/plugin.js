/**
 * Created by marcel on 16.04.14.
 */

tinymce.PluginManager.add( 'dcmsfilemanager', function ( editor )
{
	var self = this, removed = false, windows = [];


	function getImageSize( url, callback )
	{
		var img = document.createElement( 'img' );

		function done( width, height )
		{
			if ( img.parentNode ) {
				img.parentNode.removeChild( img );
			}

			callback( {width: width, height: height} );
		}

		img.onload = function ()
		{
			done( img.clientWidth, img.clientHeight );
		};

		img.onerror = function ()
		{
			done();
		};

		var style = img.style;
		style.visibility = 'hidden';
		style.position = 'fixed';
		style.bottom = style.left = 0;
		style.width = style.height = 'auto';

		document.body.appendChild( img );
		img.src = url;
	}


	function applyPreview( items )
	{
		tinymce.each( items, function ( item )
		{
			item.textStyle = function ()
			{
				return editor.formatter.getCssText( {inline: 'img', classes: [item.value]} );
			};
		} );

		return items;
	}

	function openmanager() {
		editor.focus(true);
		var title="FileManager";
		// Disabled because of bug
		var type=0;
		if (typeof editor.settings.filemanager_type !== "undefined" && editor.settings.filemanager_type) {
			if ($.isNumeric(editor.settings.filemanager_type) === true && editor.settings.filemanager_type > 0 && editor.settings.filemanager_type <= 3) {
				type=editor.settings.filemanager_type;
			}
			else if (editor.settings.filemanager_type == 'image'){
				type = 1;
			}
			else if (editor.settings.filemanager_type == 'media'){
				type = 3;
			}
			else {
				type = 2;
			}
		}

		win = editor.windowManager.open({
			title: title,
			file: editor.settings.external_filemanager_path+'dialog.php?type=4&descending='+descending+sort_by+fldr+'&lang='+editor.settings.language+'&akey='+akey,
			width: 860,
			height: 570,
			inline: 1,
			resizable: true,
			maximizable: true
		});
	}

	editor.addButton('dcmsfilemanager', {
		icon: 'browse',
		tooltip: 'Insert file',
		shortcut: 'Ctrl+E',
		onclick:openmanager
	});

	editor.addShortcut('Ctrl+E', '', openmanager);

	editor.addMenuItem('dcmsfilemanager', {
		icon: 'browse',
		text: 'Insert file',
		shortcut: 'Ctrl+E',
		onclick: openmanager,
		context: 'insert'
	});
} );