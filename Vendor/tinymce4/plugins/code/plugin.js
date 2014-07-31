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

tinymce.PluginManager.add('code', function(editor)
{
	var self = this, removed = false, windows = [];


	function getTopMostWindow() {
		windows = editor.windowManager.getWindows();
		if (windows.length) {
			return windows[windows.length - 1];
		}
	}


	function removeFullscreenAce() {

		if ( removed ) {
			return;
		}
		if ( typeof top != 'undefined' ) {
			$( top.window.document.body ).find( 'style[rel=tinymceAce]' ).remove();
		}
		else {
			$(document).find( 'style[rel=tinymceAce]' ).remove();
		}

		removed = true;
	}
	function saveAce(editor, e)
	{
        var htmlObj = $('#htmlSource' );


		if (htmlObj.length && htmlObj.data('ace')) {
			var newValue = htmlObj.data('ace' ).getValue();
			e.data.code = newValue;
		}
		//tinyMCEPopup.editor.setContent(val, {source_view : true});
	}

	function saveContent(editor, e) {
		saveAce(editor, e);
		removeFullscreenAce();
	}


	function showDialog() {
		editor.windowManager.open({
			title: "Source code",
			padding: 0,
			x: 0,
			y: 0,
			body: {
				type: 'textbox',
				name: 'code',
				id: 'htmlSource',
				multiline: true,
				minWidth: editor.getParam("code_dialog_width", 800),
				minHeight: editor.getParam("code_dialog_height", Math.min(tinymce.DOM.getViewPort().h - 200, 500)),
				value: editor.getContent({raw: true, no_events:true}),
				spellcheck: false,
				style: 'direction: ltr; text-align: left',
				padding: 0,
				x: 0,
				y: 0
			},
			onSubmit: function(e) {
				// We get a lovely "Wrong document" error in IE 11 if we
				// don't move the focus to the editor before creating an undo
				// transation since it tries to make a bookmark for the current selection
				editor.focus();
                var bookmark = editor.selection.getBookmark(2,true);

				saveContent(editor, e);
				editor.setContent(e.data.code);

				editor.undoManager.transact(function() {
					editor.setContent(e.data.code);
					//editor.setContent(editor.getContent(/*{raw: true}*/));
				});

				editor.selection.setCursorLocation();
				editor.nodeChanged();

                editor.selection.moveToBookmark(bookmark);
			}
		});

		setTimeout(function() {

            var bm = tinymce.activeEditor.selection.getBookmark(2,true);

			buildAce();

            tinymce.activeEditor.selection.moveToBookmark(bm);

			$('#htmlSource' ).parents('div.mce-window:last' ).resizable({
				minHeight: 400,
				minWidth: 500,
				resize: function(e, ui) {


					var fix = $(this ).find('.mce-window-head').outerHeight(true) + $(this ).find('.mce-foot').outerHeight(true);

					$(this ).find('div.mce-reset > div.mce-container-body,div.mce-reset > div.mce-container-body > div.mce-container,div.mce-reset > div.mce-container-body > div.mce-container > div.mce-container-body' ).width(ui.size.width).height(ui.size.height - fix )



					var diff = 0, w = $(this ).find('.mce-foot').width();

					$(this ).find('div.mce-foot').width(ui.size.width ).children(':first').width(ui.size.width );


					if (w > ui.size.width) {
						diff = w - ui.size.width;
						$(this ).find('div.mce-foot .mce-btn' ).css({left: '-='+ diff });
					}
					else {
						diff = ui.size.width - w;
						$(this ).find('div.mce-foot .mce-btn' ).css({left: '+='+ diff });
					}





					mceResizeAce();
				},
				stop: function(e, ui) {
					var fix = $(this ).find('.mce-window-head').outerHeight(true) + $(this ).find('.mce-foot').outerHeight(true);
					$(this ).find('div.mce-reset > div.mce-container-body,div.mce-reset > div.mce-container-body > div.mce-container,div.mce-reset > div.mce-container-body > div.mce-container > div.mce-container-body' ).width(ui.size.width).height(ui.size.height - fix )
					$(this ).find('.mce-foot').width(ui.size.width).children(':first').width(ui.size.width )
					mceResizeAce();
				}
			});
		}, 100);
	}




	editor.windowManager.baseclose = editor.windowManager.close;


	/**
	 * Closes the top most window.
	 *
	 * @method close
	 */
	editor.windowManager.close = function() {
		if (getTopMostWindow()) {

			editor.windowManager.close = editor.windowManager.baseclose;
			editor.windowManager.baseclose = null;

			saveContent();
			getTopMostWindow().close();

		}
	};






	function buildAce(source) {

		var areas = $('#htmlSource');


		if ( typeof window.ace == 'undefined' ) {
			Loader.require( [
				'../../../../../Vendor/ace/htmlhint',
				'../../../../../Vendor/ace/ace',
				'../../../../../Vendor/ace/ext-chromevox',
				'../../../../../Vendor/ace/ext-elastic_tabstops_lite',
				'../../../../../Vendor/ace/ext-emmet',
				'../../../../../Vendor/ace/emmet',
				'../../../../../Vendor/ace/jshint',
				'../../../../../Vendor/ace/csslint',
				'../../../../../Vendor/ace/ext-keybinding_menu',
				'../../../../../Vendor/ace/ext-language_tools',
				'../../../../../Vendor/ace/ext-modelist',
				'../../../../../Vendor/ace/ext-settings_menu',
				'../../../../../Vendor/ace/ext-static_highlight',
				//   'Vendor/ace/ext-statusbar',
				//   'Vendor/ace/ext-textarea',
				'../../../../../Vendor/ace/ext-themelist',
				'../../../../../Vendor/ace/ext-whitespace',
				'../../../../../Vendor/ace/worker-javascript',
				'../../../../../Vendor/ace/worker-php',
				'../../../../../Vendor/ace/keybinding-emacs',
				'../../../../../Vendor/ace/keybinding-vim',
				'../../../../../Vendor/ace/theme-netbeans',
				'../../../../../Vendor/ace/mode-html',
				'../../../../../public/html/js/backend/dcms.config',
				'../../../../../public/html/js/backend/tpleditor/dcms.ace.token_tooltip',
				'../../../../../public/html/js/backend/tpleditor/dcms.ace.intellisense'
			], function ()
			{
				if ( !areas.data( 'ace' ) ) {

					Config.set('fullscreenContainerId', $('#htmlSource' ).parent().attr('id') );

					var id = areas.attr( 'id' );
					var editorID = 'ace-' + (id ? id : areas.attr( 'name' ).replace( '[', '_' ).replace( ']', '_' )) + 'ace';
					areas.parent().css( {position: 'relative'} );
					var height = areas.parent().height();
					if ( height < 180 ) {
						height = 220;
					}
					$( '<div id="' + editorID + '" />' ).css( {height: height} ).insertBefore( areas );

					$( '#' + editorID ).wrap( $( '<div id="' + editorID + '-wrapper"/>' ).addClass( 'ace-wrapper' ) );


					var editor = new AceEdit;
					editor.init( editorID, areas, $('#htmlSource' ).parent() );
					editor.reindentCode();

					areas.attr( 'aceid', editorID ).data( 'ace', editor );

					var wrapper = $( '#' + editorID + '-wrapper' ).get( 0 );
					var aceContainer = $( '#' + editorID ).get( 0 );
					var aceStatusBar = $('#htmlSource' ).parent().find( 'div.ace-status-bar' );

					areas.data( 'aceElements', {'wrapper': wrapper, 'aceContainer': aceContainer, 'aceStatusBar': aceStatusBar} );
					$('#htmlSource' ).data( 'ace', editor );




					mceResizeAce();
				}
			});
		}
		else {

			if ( !areas.data( 'ace' ) ) {
				var id = areas.attr( 'id' );
				var editorID = 'ace-' + (id ? id : areas.attr( 'name' ).replace( '[', '_' ).replace( ']', '_' )) + 'ace';
				areas.parent().css( {position: 'relative'} );
				var height = areas.parent().height();
				if ( height < 180 ) {
					height = 220;
				}
				$( '<div id="' + editorID + '" />' ).css( {height: height} ).insertBefore( areas );

				$( '#' + editorID ).wrap( $( '<div id="' + editorID + '-wrapper"/>' ).addClass( 'ace-wrapper' ) );


				var editor = new AceEdit;
				editor.init( editorID, areas, $('#htmlSource' ).parent() );
				editor.reindentCode();
				areas.attr( 'aceid', editorID ).data( 'ace', editor );

				var wrapper = $( '#' + editorID + '-wrapper' ).get( 0 );
				var aceContainer = $( '#' + editorID ).get( 0 );
				var aceStatusBar = $('#htmlSource' ).parent().find( 'div.ace-status-bar' );

				areas.data( 'aceElements', {'wrapper': wrapper, 'aceContainer': aceContainer, 'aceStatusBar': aceStatusBar} );
				$('#htmlSource' ).data( 'ace', editor );

				mceResizeAce();
			}
		}
	}



	function mceResizeAce(  )
	{


		var sources = $('#htmlSource' );
		var $windowObj = sources.parent();

		if ( sources.length == 0 ) {
			return;
		}

		var parentBody = window.parent.document.body

		sources.each( function ()
		{
			var aceData = $( this ).data( 'ace' );
			var aceElements = $( this ).data( 'aceElements' );
			if ( aceData && typeof aceData.editor != 'undefined' && aceElements ) {
				var aceE = aceData.editor;
				if ( aceE && !aceData.fullscreen ) {
					var maxWidth = $windowObj.width();
					var maxHeight = $windowObj.height();

					var h = 0, bar = aceData.jqErrorBar;
					if ( bar && bar.is( ':visible' ) ) {
						h = bar.outerHeight( true );
					}
					if ( aceElements.aceStatusBar.length === 1 ) {
						h += parseInt( aceElements.aceStatusBar.outerHeight( true ), 10 );
					}
					aceElements.wrapper.style.width = maxWidth + 'px';
					aceElements.wrapper.style.height = maxHeight + 'px';
					aceElements.aceContainer.style.width = maxWidth + 'px';
					aceElements.aceContainer.style.height = (maxHeight - h) + 'px';

					aceE.focus();

					if ( aceE.renderer ) {
						aceE.renderer.onResize( true );
						aceE.renderer.updateFull();
					}

					aceData.refreshAfterResize();
				}
				else if (aceE && aceData.fullscreen) {

					if (  parentBody  ) {
						var maxWidth = $(window.parent.window).width();
						var maxHeight = $(window.parent.window).height( );
					}
					else {
						var maxWidth = $(window).width();
						var maxHeight = $(window).height(  );

					}

					var h = 0, bar = aceData.jqErrorBar;
					if ( bar && bar.is( ':visible' ) ) {
						h = bar.outerHeight( true );
					}
					if ( aceElements.aceStatusBar.length === 1 ) {
						h += parseInt( aceElements.aceStatusBar.outerHeight( true ), 10 );
					}
					aceElements.wrapper.style.width = maxWidth + 'px';
					aceElements.wrapper.style.height = maxHeight + 'px';
					aceElements.aceContainer.style.width = maxWidth + 'px';
					aceElements.aceContainer.style.height = (maxHeight - h) + 'px';

					aceE.focus();

					if ( aceE.renderer ) {
						aceE.renderer.onResize( true );
						aceE.renderer.updateFull();
					}

					aceData.refreshAfterResize();
				}
			}
		} );
	}


	editor.addCommand("mceCodeEditor", showDialog);

	editor.addButton('code', {
		icon: 'code',
		tooltip: 'Source code',
		onclick: showDialog
	});

	editor.addMenuItem('code', {
		icon: 'code',
		text: 'Source code',
		context: 'tools',
		onclick: showDialog
	});
});