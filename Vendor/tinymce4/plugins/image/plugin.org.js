/**
 * plugin.js
 *
 * Copyright, Moxiecode Systems AB
 * Released under LGPL License.
 *
 * License: http://www.tinymce.com/license
 * Contributing: http://www.tinymce.com/contributing
 */

/*global tinymce:true */

tinymce.PluginManager.add( 'image', function ( editor )
{
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

	function createImageList( callback )
	{
		return function ()
		{
			var imageList = editor.settings.image_list;

			if ( typeof(imageList) == "string" ) {
				tinymce.util.XHR.send( {
					url: imageList,
					success: function ( text )
					{
						callback( tinymce.util.JSON.parse( text ) );
					}
				} );
			} else {
				callback( imageList );
			}
		};
	}

	function showDialog( imageList )
	{
		var win, win2, data = {}, dom = editor.dom, imgElm = editor.selection.getNode();
		var width, height, imageListCtrl, classListCtrl;
		var generalFormItems;

		function buildValues( listSettingName, dataItemName, defaultItems )
		{
			var selectedItem, items = [];

			tinymce.each( editor.settings[listSettingName] || defaultItems, function ( target )
			{
				var item = {
					text: target.text || target.title,
					value: target.value
				};

				items.push( item );

				if ( data[dataItemName] === target.value || (!selectedItem && target.selected) ) {
					selectedItem = item;
				}
			} );

			if ( selectedItem && !data[dataItemName] ) {
				data[dataItemName] = selectedItem.value;
				selectedItem.selected = true;
			}

			return items;
		}

		function buildImageList()
		{
			var imageListItems = [
				{text: 'None', value: ''}
			];

			tinymce.each( imageList, function ( image )
			{
				imageListItems.push( {
					text: image.text || image.title,
					value: editor.convertURL( image.value || image.url, 'src' ),
					menu: image.menu
				} );
			} );

			return imageListItems;
		}

		function recalcSize()
		{
			var widthCtrl, heightCtrl, newWidth, newHeight;

			widthCtrl = win.find( '#width' )[0];
			heightCtrl = win.find( '#height' )[0];

			newWidth = widthCtrl.value();
			newHeight = heightCtrl.value();

			if ( win.find( '#constrain' )[0].checked() && width && height && newWidth && newHeight ) {
				if ( width != newWidth ) {
					newHeight = Math.round( (newWidth / width) * newHeight );
					heightCtrl.value( newHeight );
				} else {
					newWidth = Math.round( (newHeight / height) * newWidth );
					widthCtrl.value( newWidth );
				}
			}

			width = newWidth;
			height = newHeight;
		}

		function onSubmitForm()
		{
			function waitLoad( imgElm )
			{
				function selectImage()
				{
					imgElm.onload = imgElm.onerror = null;
					editor.selection.select( imgElm );
					editor.nodeChanged();
				}

				imgElm.onload = function ()
				{
					if ( !data.width && !data.height ) {
						dom.setAttribs( imgElm, {
							width: imgElm.clientWidth,
							height: imgElm.clientHeight
						} );
					}

					selectImage();
				};

				imgElm.onerror = selectImage;
			}

			updateStyle();
			recalcSize();

			data = tinymce.extend( data, win.toJSON() );

			if ( !data.alt ) {
				data.alt = '';
			}

			if ( data.width === '' ) {
				data.width = null;
			}

			if ( data.height === '' ) {
				data.height = null;
			}

			if ( data.style === '' ) {
				data.style = null;
			}

			if ( data.fancybox ) {
				data.fancybox = 'fancybox';
			}
			else {
				data.fancybox = null;
			}

			data = {
				src: data.src,
				alt: data.alt,
				width: data.width,
				height: data.height,
				style: data.style,
				"class": data["class"],
				"data-toggle": data.fancybox
			};

			if ( !data["class"] ) {
				delete data["class"];
			}

			editor.undoManager.transact( function ()
			{
				if ( !data.src ) {
					if ( imgElm ) {
						dom.remove( imgElm );
						editor.focus();
						editor.nodeChanged();
					}

					return;
				}

				if ( !imgElm ) {
					data.id = '__mcenew';
					editor.focus();
					editor.selection.setContent( dom.createHTML( 'img', data ), {no_events: true} );
					imgElm = dom.get( '__mcenew' );
					dom.setAttrib( imgElm, 'id', null );

				} else {
					dom.setAttribs( imgElm, data );
				}

				waitLoad( imgElm );
			} );
		}

		function removePixelSuffix( value )
		{
			if ( value ) {
				value = value.replace( /px$/, '' );
			}

			return value;
		}

		function srcChange()
		{
			if ( imageListCtrl ) {
				imageListCtrl.value( editor.convertURL( this.value(), 'src' ) );
			}

			getImageSize( this.value(), function ( data )
			{
				if ( data.width && data.height ) {
					width = data.width;
					height = data.height;

					win.find( '#width' ).value( width );
					win.find( '#height' ).value( height );
				}
			} );
		}

		function openmanager()
		{
			editor.focus( true );

			var type = 0;

			if ( typeof editor.settings.filemanager_type !== "undefined" && editor.settings.filemanager_type ) {
				if ( $.isNumeric( editor.settings.filemanager_type ) === true && editor.settings.filemanager_type > 0 && editor.settings.filemanager_type <= 2 ) {
					type = editor.settings.filemanager_type;
				}
				else if ( editor.settings.filemanager_type == 'image' ) {
					type = 1;
				}
				else if ( editor.settings.filemanager_type == 'media' ) {
					type = 2;
				}
				else {
					type = 0;
				}
			}

			var url = editor.settings.file_browser_url;

			if ( !url.match( /\?/g ) ) {
				url += '?type=' + type + '&lang=' + editor.settings.language;
			}
			else if ( url.match( /\?/g ) && url.match( /&/g ) ) {
				url += '&type=' + type + '&lang=' + editor.settings.language;
			}
			else {
				url += '&type=' + type + '&lang=' + editor.settings.language;
			}

			if ( imgElm && imgElm.tagName == 'IMG' && !imgElm.getAttribute( 'data-mce-object' ) && !imgElm.getAttribute( 'data-mce-placeholder' ) ) {
				url += '&selected=' + dom.getAttrib( imgElm, 'src' );
			}

			win2 = editor.windowManager.open( {
				title: 'Filemanager',
				file: url,
				width: window.innerWidth / 2 + 100,
				height: 570,
				inline: 1,
				resizable: true,
				maximizable: true,
				buttons: [
					{
						text: 'Insert',
						onclick: function ()
						{
							// Top most window object
							var x = editor.windowManager.getWindows()[1];
							var d = x.getContentWindow();
							var val = d.getSelectedFile();

							win.find( '#src' ).value( val.path );
							win.find( '#height' ).value( val.height );
							win.find( '#width' ).value( val.width );

							$('#mce-image-preview' ).attr('src',  val.path ).width( $('#mce-image-preview' ).parent().outerWidth(true) - 3 );

							// Close the window
							win2.close();

							editor.filebrowser = editor.filebrowserParent = null;
						}
					},

					{text: 'Close', onclick: 'close'}
				]
			} );
			editor.filebrowserParent = win;
			editor.filebrowser = win2;
		}

		width = dom.getAttrib( imgElm, 'width' );
		height = dom.getAttrib( imgElm, 'height' );

		if ( imgElm.nodeName == 'IMG' && !imgElm.getAttribute( 'data-mce-object' ) && !imgElm.getAttribute( 'data-mce-placeholder' ) ) {
			data = {
				src: dom.getAttrib( imgElm, 'src' ),
				alt: dom.getAttrib( imgElm, 'alt' ),
				"class": dom.getAttrib( imgElm, 'class' ),
				width: width,
				height: height
			};

			if ( imgElm.getAttribute( 'data-toggle' ) === 'fancybox' ) {
				data.fancybox = true;
			}

		} else {
			imgElm = null;
		}

		if ( imageList ) {
			imageListCtrl = {
				type: 'listbox',
				label: 'Image list',
				values: buildImageList(),
				value: data.src && editor.convertURL( data.src, 'src' ),
				onselect: function ( e )
				{
					var altCtrl = win.find( '#alt' );

					if ( !altCtrl.value() || (e.lastControl && altCtrl.value() == e.lastControl.text()) ) {
						altCtrl.value( e.control.text() );
					}

					win.find( '#src' ).value( e.control.value() );
				},
				onPostRender: function ()
				{
					imageListCtrl = this;
				}
			};
		}

		if ( editor.settings.image_class_list ) {
			classListCtrl = {
				name: 'class',
				type: 'listbox',
				label: 'Class',
				values: applyPreview( buildValues( 'image_class_list', 'class' ) )
			};
		}

		// General settings shared between simple and advanced dialogs
		if ( typeof editor.settings.file_browser_url === 'string' && editor.settings.file_browser_url != '' ) {
			generalFormItems = [
				{

					type: 'container',
					layout: 'flex',
					classes: 'combobox has-open',
					label: 'Source',
					direction: 'row',
					items: [
						{
							name: 'src',
							type: 'textbox',
							filetype: 'image',
							size: 65,
							classes: 'img_' + editor.id,
							autofocus: true
						},
						{
							name: 'upl_img',
							type: 'button',
							classes: 'btn open',
							icon: 'browse',
							onclick: openmanager,
							tooltip: 'Upload image'
						}
					]
				}
			];
		}
		else {
			generalFormItems = [

				{name: 'src', type: 'filepicker', filetype: 'image', label: 'Source', autofocus: true, onchange: srcChange},
				imageListCtrl
			];
		}

		if ( editor.settings.image_description !== false ) {
			generalFormItems.push( {name: 'alt', type: 'textbox', label: 'Image description'} );
		}

		if ( editor.settings.image_dimensions !== false ) {
			generalFormItems.push( {
				type: 'container',
				label: 'Dimensions',
				layout: 'flex',
				direction: 'row',
				align: 'center',
				spacing: 5,
				items: [
					{name: 'width', type: 'textbox', maxLength: 5, size: 3, onchange: recalcSize, ariaLabel: 'Width'},
					{type: 'label', text: 'x'},
					{name: 'height', type: 'textbox', maxLength: 5, size: 3, onchange: recalcSize, ariaLabel: 'Height'},
					{name: 'constrain', type: 'checkbox', checked: true, text: 'Constrain proportions'}
				]
			} );
		}

		if ( editor.settings.use_fancybox !== false ) {
			generalFormItems.push(
				{label: 'Fancy Box', name: 'fancybox', type: 'checkbox', checked: data.fancybox ? true : false, text: 'Use Fancybox'}
			);
		}

		generalFormItems.push(
			{
				layout: 'flex',
				label: 'Preview',
				// direction: 'row',
				type: 'container',
				html: '<img id="mce-image-preview" src="" width="0" height="0"/>'
			}
		);

		generalFormItems.push( classListCtrl );

		function updateStyle()
		{
			function addPixelSuffix( value )
			{
				if ( value.length > 0 && /^[0-9]+$/.test( value ) ) {
					value += 'px';
				}

				return value;
			}

			if ( !editor.settings.image_advtab ) {
				return;
			}

			var data = win.toJSON();
			var css = dom.parseStyle( data.style );

			delete css.margin;
			css['margin-top'] = css['margin-bottom'] = addPixelSuffix( data.vspace );
			css['margin-left'] = css['margin-right'] = addPixelSuffix( data.hspace );
			css['border-width'] = addPixelSuffix( data.border );

			win.find( '#style' ).value( dom.serializeStyle( dom.parseStyle( dom.serializeStyle( css ) ) ) );
		}

		if ( editor.settings.image_advtab ) {
			// Parse styles from img
			if ( imgElm ) {
				data.hspace = removePixelSuffix( imgElm.style.marginLeft || imgElm.style.marginRight );
				data.vspace = removePixelSuffix( imgElm.style.marginTop || imgElm.style.marginBottom );
				data.border = removePixelSuffix( imgElm.style.borderWidth );
				data.style = editor.dom.serializeStyle( editor.dom.parseStyle( editor.dom.getAttrib( imgElm, 'style' ) ) );
			}

			// Advanced dialog shows general+advanced tabs
			win = editor.windowManager.open( {
				title: 'Insert/edit image',
				data: data,
				bodyType: 'tabpanel',
				body: [
					{
						title: 'General',
						type: 'form',
						items: generalFormItems
					},

					{
						title: 'Advanced',
						type: 'form',
						pack: 'start',
						items: [
							{
								label: 'Style',
								name: 'style',
								type: 'textbox'
							},
							{
								type: 'form',
								layout: 'grid',
								packV: 'start',
								columns: 2,
								padding: 0,
								alignH: ['left', 'right'],
								defaults: {
									type: 'textbox',
									maxWidth: 50,
									onchange: updateStyle
								},
								items: [
									{label: 'Vertical space', name: 'vspace'},
									{label: 'Horizontal space', name: 'hspace'},
									{label: 'Border', name: 'border'}
								]
							}
						]
					}
				],
				onSubmit: onSubmitForm
			} );
		} else {
			// Simple default dialog
			win = editor.windowManager.open( {
				title: 'Insert/edit image',
				data: data,
				body: generalFormItems,
				onSubmit: onSubmitForm
			} );
		}
	}

	editor.addButton( 'image', {
		icon: 'image',
		tooltip: 'Insert/edit image',
		onclick: createImageList( showDialog ),
		stateSelector: 'img:not([data-mce-object],[data-mce-placeholder])'
	} );

	editor.addMenuItem( 'image', {
		icon: 'image',
		text: 'Insert image',
		onclick: createImageList( showDialog ),
		context: 'insert',
		prependToContext: true
	} );
} );
