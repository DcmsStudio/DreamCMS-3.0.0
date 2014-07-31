/**
 * Created by marcel on 17.04.14.
 */
tinymce.PluginManager.add( 'image', function ( editor )
{
	"use strict";

	var win, data = {}, dom = editor.dom, imgElm;
	var width, height, query_str = "", url = tinyMCE.baseURL + '/plugins/image/';

	function removePixelSuffix( value )
	{
		if ( value ) {
			value = value.replace( /px$/, '' );
		}

		return value;
	}

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

	function insertImage( opt )
	{




		var html = '', image = '', data = {};
		if ( imgElm && imgElm.nodeName == "IMG" ) {

			if ( $( imgElm ).parent().is( 'a' ) && $( imgElm ).parent().attr( 'rel' ) === 'fancybox' ) {
				// is fancybox image

				image += '<img src="' + (opt.cropFile ? opt.cropFile : opt.baseSrc) + '"';

				if ( opt.fancybox ) {
					image += ' data-fancybox="true"';
				}

				if ( opt.cropFile ) {
					if ( parseInt( opt.cropwidth ) ) {
						data.width = parseInt( opt.cropwidth );
						html += ' width="' + parseInt( opt.cropwidth ) + '"';
					}

					if ( parseInt( opt.cropheight ) ) {
						data.height = parseInt( opt.cropheight );
						html += ' height="' + parseInt( opt.cropheight ) + '"';
					}
				}
				else {
					if ( parseInt( opt.width ) ) {
						data.width = parseInt( opt.width );
						html += ' width="' + parseInt( opt.width ) + '"';
					}

					if ( parseInt( opt.height ) ) {
						data.height = parseInt( opt.height );
						html += ' height="' + parseInt( opt.height ) + '"';
					}
				}

				image += ' data-basefile="' + opt.baseSrc + '"';
				image += ' data-options="' + escape( JSON.stringify( opt ) ) + '"';

				if ( opt.alt ) {
					image += ' alt="' + opt.alt.replace( /"/, '\"' ) + '"';
				}

				if ( opt.style ) {
					image += ' style="' + opt.style.replace( /"/, '\"' );

					if ( parseInt( opt.hspace ) > 0 ) {
						image += ';padding-left:' + parseInt( opt.hspace ) + 'px;padding-right:' + parseInt( opt.hspace ) + 'px';
					}

					if ( parseInt( opt.vspace ) > 0 ) {
						image += ';padding-top:' + parseInt( opt.vspace ) + 'px;padding-bottom:' + parseInt( opt.vspace ) + 'px';
					}

					image += '"';
				}
				else {

					if ( parseInt( opt.hspace ) > 0 || parseInt( opt.vspace ) > 0 ) {
						image += ' style="';

						if ( parseInt( opt.hspace ) > 0 ) {
							image += ';padding-left:' + parseInt( opt.hspace ) + 'px;padding-right:' + parseInt( opt.hspace ) + 'px';
						}

						if ( parseInt( opt.vspace ) > 0 ) {
							image += ';padding-top:' + parseInt( opt.vspace ) + 'px;padding-bottom:' + parseInt( opt.vspace ) + 'px';
						}

						image += '"';

					}
				}

				if ( opt.border > 0 ) {
					image += ' border="' + opt.border + '"';
				}

				image += '/>';

				$( imgElm ).parent().replaceWith( image );

			}
			else {

				image += '<img src="' + (opt.cropFile ? opt.cropFile : opt.baseSrc) + '"';

				if ( opt.fancybox ) {
					image += ' data-fancybox="true"';
				}
				if ( opt.cropFile ) {
					if ( parseInt( opt.cropwidth ) ) {
						data.width = parseInt( opt.cropwidth );
						html += ' width="' + parseInt( opt.cropwidth ) + '"';
					}

					if ( parseInt( opt.cropheight ) ) {
						data.height = parseInt( opt.cropheight );
						html += ' height="' + parseInt( opt.cropheight ) + '"';
					}
				}
				else {
					if ( parseInt( opt.width ) ) {
						data.width = parseInt( opt.width );
						html += ' width="' + parseInt( opt.width ) + '"';
					}

					if ( parseInt( opt.height ) ) {
						data.height = parseInt( opt.height );
						html += ' height="' + parseInt( opt.height ) + '"';
					}
				}

				image += ' data-basefile="' + opt.baseSrc + '"';
				image += ' data-options="' + escape( JSON.stringify( opt ) ) + '"';

				if ( opt.alt ) {
					image += ' alt="' + opt.alt.replace( /"/, '\"' ) + '"';
				}

				if ( opt.style ) {
					image += ' style="' + opt.style.replace( /"/, '\"' ).replace( /;\s*$/, '' );

					if ( parseInt( opt.hspace ) > 0 ) {
						image += ';padding-left:' + parseInt( opt.hspace ) + 'px;padding-right:' + parseInt( opt.hspace ) + 'px';
					}

					if ( parseInt( opt.vspace ) > 0 ) {
						image += ';padding-top:' + parseInt( opt.vspace ) + 'px;padding-bottom:' + parseInt( opt.vspace ) + 'px';
					}

					image += '"';
				}
				else {

					if ( parseInt( opt.hspace ) > 0 || parseInt( opt.vspace ) > 0 ) {
						image += ' style="';

						if ( parseInt( opt.hspace ) > 0 ) {
							image += 'padding-left:' + parseInt( opt.hspace ) + 'px;padding-right:' + parseInt( opt.hspace ) + 'px;';
						}

						if ( parseInt( opt.vspace ) > 0 ) {
							image += 'padding-top:' + parseInt( opt.vspace ) + 'px;padding-bottom:' + parseInt( opt.vspace ) + 'px';
						}

						image += '"';

					}
				}

				if ( opt.border > 0 ) {
					image += ' border="' + opt.border + '"';
				}

				image += '/>';

				$( imgElm ).replaceWith( image );

			}

		}
		else {

			html += '<img src="' + (opt.cropFile ? opt.cropFile : opt.baseSrc) + '"';

			if ( opt.fancybox ) {
				html += ' data-fancybox="true"';
			}

			if ( opt.cropFile ) {
				if ( parseInt( opt.cropwidth ) ) {
					data.width = parseInt( opt.cropwidth );
					html += ' width="' + parseInt( opt.cropwidth ) + '"';
				}

				if ( parseInt( opt.cropheight ) ) {
					data.height = parseInt( opt.cropheight );
					html += ' height="' + parseInt( opt.cropheight ) + '"';
				}
			}
			else {
				if ( parseInt( opt.width ) ) {
					data.width = parseInt( opt.width );
					html += ' width="' + parseInt( opt.width ) + '"';
				}

				if ( parseInt( opt.height ) )
				{
					data.height = parseInt( opt.height );
					html += ' height="' + parseInt( opt.height ) + '"';
				}
			}

			html += ' data-basefile="' + opt.baseSrc + '"';
			html += ' data-options="' + escape( JSON.stringify( opt ) ) + '"';

			if ( opt.alt ) {
				html += ' alt="' + opt.alt.replace( /"/, '\"' ) + '"';
				html += ' title="' + opt.alt.replace( /"/, '\"' ) + '"';
			}

			if ( opt.style ) {
				html += ' style="' + opt.style.replace( /"/, '\"' ).replace( /;\s*$/, '' );

				if ( parseInt( opt.hspace ) > 0 ) {
					html += ';padding-left:' + parseInt( opt.hspace ) + 'px;padding-right:' + parseInt( opt.hspace ) + 'px';
				}

				if ( parseInt( opt.vspace ) > 0 ) {
					html += ';padding-top:' + parseInt( opt.vspace ) + 'px;padding-bottom:' + parseInt( opt.vspace ) + 'px';
				}

				html += '"';
			}
			else {

				if ( parseInt( opt.hspace ) > 0 || parseInt( opt.vspace ) > 0 ) {
					html += ' style="';

					if ( parseInt( opt.hspace ) > 0 ) {
						html += 'padding-left:' + parseInt( opt.hspace ) + 'px;padding-right:' + parseInt( opt.hspace ) + 'px;';
					}

					if ( parseInt( opt.vspace ) > 0 ) {
						html += 'padding-top:' + parseInt( opt.vspace ) + 'px;padding-bottom:' + parseInt( opt.vspace ) + 'px';
					}

					html += '"';
				}
			}

			if ( opt.border > 0 ) {
				html += ' border="' + opt.border + '"';
			}

			html += '/>';

			editor.insertContent( html );
		}
	}

	function GetTheHtml()
	{
		var html = '';
		if ( imgElm && imgElm.nodeName == "IMG" ) {
			html += '<iframe src="' + url + 'dialog.html' + query_str + '&' + new Date().getTime() + '" frameborder="0"></iframe>';
		} else {
			html += '<iframe src="' + url + 'dialog.html' + '?' + new Date().getTime() + '" frameborder="0"></iframe>';
		}

		return html;
	}

	function showDialog()
	{

		editor.focus( true );

		imgElm = (editor.selection ? editor.selection.getNode() : false);
		var params = {};

		if ( imgElm && imgElm.nodeName == "IMG" ) {

			if ( !imgElm.getAttribute( 'data-mce-object' ) && !imgElm.getAttribute( 'data-mce-placeholder' ) && imgElm.getAttribute( 'data-options' ) ) {
				params = JSON.parse( unescape( imgElm.getAttribute( 'data-options' ) ) );

			}
			else {

				data = {
					baseSrc: imgElm.getAttribute( 'src' ),
					src: imgElm.getAttribute( 'src' ),
					alt: imgElm.getAttribute( 'alt' ),
					width: removePixelSuffix(imgElm.getAttribute( 'width' )),
					height: removePixelSuffix(imgElm.getAttribute( 'height' )),
					"class": imgElm.getAttribute( 'class' )
				};

				// Parse styles from img
				data.hspace = removePixelSuffix( imgElm.style.marginLeft || imgElm.style.marginRight );
				data.vspace = removePixelSuffix( imgElm.style.marginTop || imgElm.style.marginBottom );
				data.border = removePixelSuffix( imgElm.style.borderWidth );
				data.style = editor.dom.getAttrib( imgElm, 'style' );
				params = data;
			}



			params.img = imgElm;
		}

		win = editor.windowManager.open( {
			title: "Insert/edit image",
			width: 885,
			height: 500,
			//html: GetTheHtml(),
			file: tinyMCE.baseURL + '/plugins/image/dialog.html',
			buttons: [
				{
					text: 'Insert',
					subtype: 'primary',
					onclick: function ( e )
					{
						var win = editor.windowManager.getWindows()[0];
						var opts = win.getContentWindow().getImageData();
						insertImage( opts );

						win.close();
					}
				},
				{
					text: 'Cancel',
					onclick: function ()
					{
						win.close();
					}
				}
			]
		}, params );
	}

	editor.addButton( 'image', {
		icon: 'image',
		tooltip: 'Insert/edit image',
		onclick: showDialog,
		stateSelector: 'img:not([data-mce-object],[data-mce-placeholder])'
	} );

	editor.addMenuItem( 'image', {
		icon: 'image',
		text: 'Insert image',
		onclick: showDialog,
		context: 'insert',
		prependToContext: true
	} );

} );