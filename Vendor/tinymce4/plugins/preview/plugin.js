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

tinymce.PluginManager.add( 'preview', function ( editor )
{
	var settings = editor.settings;

	editor.addCommand( 'mcePreview', function ()
	{
		var w = parseInt( editor.getParam( "plugin_preview_width", "550" ), 10 ), h = parseInt( editor.getParam( "plugin_preview_height", "400" ), 10 );
		editor.windowManager.open( {
			title: 'Preview',
			width: w,
			height: h,
			html: '<iframe rel="mcepreview" src="javascript:\'\'" frameborder="0"></iframe>',
			buttons: {
				text: 'Close',
				onclick: function ()
				{
					this.parent().parent().close();
				}
			},
			onPostRender: function ()
			{
				var doc = this.getEl( 'body' ).firstChild.contentWindow.document, previewHtml, headHtml = '';

				if ( editor.settings.document_base_url != editor.documentBaseUrl ) {
					headHtml += '<base href="' + editor.documentBaseURI.getURI() + '">';
				}

				tinymce.each( editor.contentCSS, function ( url )
				{
					headHtml += '<link type="text/css" rel="stylesheet" href="' + editor.documentBaseURI.toAbsolute( url ) + '">';
				} );

				if ( editor.previewScripts ) {

					tinymce.each( editor.previewScripts, function ( url )
					{
						headHtml += '<script type="application/javascript" src="'+ url +'"></script> ';
						//headHtml += '<link type="text/css" rel="stylesheet" href="' + editor.documentBaseURI.toAbsolute( url ) + '">';
					} );
				}






				var bodyId = settings.body_id || 'tinymce';
				if ( bodyId.indexOf( '=' ) != -1 ) {
					bodyId = editor.getParam( 'body_id', '', 'hash' );
					bodyId = bodyId[editor.id] || bodyId;
				}

				var bodyClass = settings.body_class || '';
				if ( bodyClass.indexOf( '=' ) != -1 ) {
					bodyClass = editor.getParam( 'body_class', '', 'hash' );
					bodyClass = bodyClass[editor.id] || '';
				}

				var dirAttr = editor.settings.directionality ? ' dir="' + editor.settings.directionality + '"' : '';

				previewHtml = (
					'<!DOCTYPE html>' +
						'<html>' +
						'<head>' +
						headHtml +
						'</head>' +
						'<body id="' + bodyId + '" class="mce-content-body ' + bodyClass + '"' + dirAttr + '>' +
						editor.getContent() +
						'</body>' +
						'</html>'
					);

				doc.open();
				doc.write( previewHtml );
				doc.close();
			}
		} );

		$( 'iframe[rel=mcepreview]' ).parents( 'div.mce-window:last' ).resizable( {
			minHeight: h,
			minWidth: w,
			start: function ()
			{
				$( 'iframe[rel=mcepreview]' ).css( 'pointer-events', 'auto' );
			},
			resize: function ( e, ui )
			{
				var fix = $( this ).find( '.mce-window-head' ).outerHeight( true ) + $( this ).find( '.mce-foot' ).outerHeight( true );
				$( this ).find( 'div.mce-reset > div.mce-container-body,div.mce-reset > div.mce-container-body > div.mce-container,div.mce-reset > div.mce-container-body > div.mce-container > div.mce-container-body' ).width( ui.size.width ).height( ui.size.height - fix )
				var diff = 0, w = $( this ).find( '.mce-foot' ).width();

				$( this ).find( 'div.mce-foot' ).width( ui.size.width ).children( ':first' ).width( ui.size.width );
				if ( w > ui.size.width ) {
					diff = w - ui.size.width;
					$( this ).find( 'div.mce-foot .mce-btn' ).css( {left: '-=' + diff } );
				}
				else {
					diff = ui.size.width - w;
					$( this ).find( 'div.mce-foot .mce-btn' ).css( {left: '+=' + diff } );
				}
			},
			stop: function ( e, ui )
			{
				var fix = $( this ).find( '.mce-window-head' ).outerHeight( true ) + $( this ).find( '.mce-foot' ).outerHeight( true );
				$( this ).find( 'div.mce-reset > div.mce-container-body,div.mce-reset > div.mce-container-body > div.mce-container,div.mce-reset > div.mce-container-body > div.mce-container > div.mce-container-body' ).width( ui.size.width ).height( ui.size.height - fix )
				$( this ).find( '.mce-foot' ).width( ui.size.width ).children( ':first' ).width( ui.size.width );
				$( 'iframe[rel=mcepreview]' ).css( 'pointer-events', 'none' );
			}
		} );

	} );

	editor.addButton( 'preview', {
		title: 'Preview',
		cmd: 'mcePreview'
	} );

	editor.addMenuItem( 'preview', {
		text: 'Preview',
		cmd: 'mcePreview',
		context: 'view'
	} );
} );
