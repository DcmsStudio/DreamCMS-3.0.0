// jQuery Alert Dialogs Plugin
//
// Version 1.1
//
// Cory S.N. LaViska
// A Beautiful Site (http://abeautifulsite.net/)
// 14 May 2009
//
// Visit http://abeautifulsite.net/notebook/87 for more information
//
// Usage:
//        jAlert( message, [title, callback] )
//        jConfirm( message, [title, callback] )
//        jPrompt( message, [value, title, callback] )
// 
// History:
//
//        1.00 - Released (29 December 2008)
//
//        1.01 - Fixed bug where unbinding would destroy all resize events
//
// License:
// 
// This plugin is dual-licensed under the GNU General Public License and the MIT License and
// is copyright 2008 A Beautiful Site, LLC. 
//
(function ( jQuery )
{

	$.alerts = {
		// These properties can be read/written by accessing $.alerts.propertyName from your scripts at any time

		verticalOffset: 0, // vertical offset of the dialog from center screen, in pixels
		horizontalOffset: 0, // horizontal offset of the dialog from center screen, in pixels/
		repositionOnResize: true, // re-centers the dialog on window resize
		overlayOpacity: .3, // transparency level of overlay
		overlayColor: '#000', // base color of overlay
		draggable: true, // make the dialogs draggable (requires UI Draggables plugin)
		okButton: cmslang.confirm_okbtn, // text for the OK button
		cancelButton: cmslang.confirm_cancelbtn, // text for the Cancel button
		dialogClass: null, // if specified, this class will be applied to all dialogs

		// Public methods

		callbackProps: null,

		alert: function ( message, title, callback, aftershowcallback )
		{
			if ( title == null )
				title = 'Alert';
			$.alerts._show( title, message, null, 'alert', function ( result )
			{
				if ( callback )
					callback( result );
			}, aftershowcallback );
		},
		dialog: function ( message, title, callback, aftershowcallback, options )
		{
			if ( title == null )
				title = 'Info';

			$.alerts.draggable = true;

			$.alerts._show( title, message, null, 'dialog', function ( result )
			{

				if ( callback )
					callback( result );
			}, aftershowcallback, options );
		},
		confirm: function ( message, title, callback, aftershowcallback )
		{
			if ( title == null )
				title = 'Confirm';
			$.alerts._show( title, message, null, 'confirm', function ( result )
			{
				if ( callback )
					callback( result );
			}, aftershowcallback );
		},
		prompt: function ( message, value, title, callback, aftershowcallback )
		{
			if ( title == null )
				title = 'Prompt';
			$.alerts._show( title, message, value, 'prompt', function ( result )
			{
				if ( callback )
					callback( result );
			}, aftershowcallback );
		},
		error: function ( message, title, callback, aftershowcallback )
		{
			if ( title == null )
				title = 'Error';
			$.alerts._show( title, message, null, 'error', function ( result )
			{
				if ( callback )
					callback( result );
			}, aftershowcallback );
		},
		// Private methods

		_show: function ( title, msg, value, type, callback, aftershowcallback, options )
		{

			if ( $.alerts.callbackProps !== null ) {
				setTimeout( function ()
				{
					$.alerts._show( title, msg, value, type, callback, aftershowcallback, options );
				}, 3 );
			}
			else {

				$.alerts._hide();
				$( "#popup_overlay_msg" ).remove(); // alex added this, to make sure the 'loader' isn't displayed any more

				$.alerts._overlay( 'show', type );
				var popup, dialog;

				if ( typeof $.fn.BootstrapVersion == 'undefined' ) {
					popup = $( '<div id="popup_container">' +
						'<div class="header"><div id="popup_title"></div></div>' +
						'<div id="popup_content">' +
						'<div id="popup_message"></div>' +
						'</div>' +
						'</div>' );
					dialog = popup;
				}
				else {
					popup = $( '<div class="modal fade"><div class="modal-dialog"><div id="popup_container">' +
						'<div class="header"><div id="popup_title"></div></div>' +
						'<div id="popup_content">' +
						'<div id="popup_message"></div>' +
						'</div>' +
						'</div></div></div>' );
					dialog = popup;
				}

				if ( options && (options.maximize || options.close) ) {
					var container = $( '<div class="win-buttons">' );
					if ( options.close ) {
						var closeBtn = $( '<div class="winbtn win-close-btn">' );
						closeBtn.click( function ( e )
						{
							$.alerts._hide( e );
							$.alerts.draggable = false;
						} );

						container.append( closeBtn );
					}

					if ( options.maximize ) {
						var maxBtn = $( '<div class="winbtn win-max-btn">' );
						maxBtn.click( function ( e )
						{

						} );
						container.append( maxBtn );
					}

					popup.find( '.header' ).prepend( container );

				}

				if ( $( '#fullscreenContainer' ).length == 1 ) {
					$( '#fullscreenContainer' ).append( popup );
				}
				else {
					$( 'body' ).append( popup );
				}

				if ( $.alerts.dialogClass )
					popup.addClass( $.alerts.dialogClass );

				if ( typeof $.fn.BootstrapVersion == 'undefined' ) {

					// IE6 Fix
					var pos = ($.browser.msie && parseInt( $.browser.version ) <= 6) ? 'absolute' : 'fixed';
					popup.css( {
						position: pos,
						zIndex: 999999,
						padding: 0,
						margin: 0
					} );
				}

				if ( type == 'prompt' && typeof title == 'undefined' || title == '' ) {
					title = 'Eingabe...';
				}

				$( "#popup_title", popup ).text( title );
				$( "#popup_content", popup ).addClass( type );

				if ( typeof msg === 'string' ) {
					$( "#popup_message", popup ).html( msg );
				}
				else if ( typeof msg === 'object' ) {
					$( "#popup_message", popup ).append( msg );
				}

				//$("#popup_message").html($("#popup_message").text().replace(/\n/g, '<br />'));

				dialog.css('display', 'inline-block');
				popup.data( 'easein', Config.get('popupEaseIn', 'bounceInDown') );
				popup.data('easeout', Config.get('popupEaseOut', 'bounceOutUp') );


				switch ( type ) {
					case 'alert':

						popup.data( 'easein', 'shake' );
						popup.data('easeout', 'bounceOutUp');

						$( "#popup_message", popup ).after( '<div id="popup_panel"><input class="action-button" type="button" value="' + $.alerts.okButton + '" id="popup_ok" /></div>' );
						$( "#popup_ok", popup ).click( function ( e )
						{
							$.alerts._hide( e, callback, true );
							$.alerts.draggable = false;

						} );
						$( "#popup_ok", popup ).focus();
						$( "#popup_ok", popup ).keypress( function ( e )
						{
							$.alerts.draggable = false;
							if ( e.keyCode == 13 || e.keyCode == 27 )
								$( "#popup_ok", popup ).trigger( 'click' );
						} );
						break;

					case 'dialog':

						var ok = $.alerts.okButton;
						if ( options && options.okButton && options.okButton != '' ) {
							ok = options.okButton;
						}

						$( "#popup_message", popup ).after( '<div id="popup_panel"><input class="action-button" type="button" value="' + $.alerts.okButton + '" id="popup_ok" /></div>' );
						$( "#popup_ok", popup ).click( function ( e )
						{
							$.alerts._hide( e, callback, true );
							$.alerts.draggable = false;
						} );
						$( "#popup_ok", popup ).focus();
						$( "#popup_ok", popup ).keypress( function ( e )
						{
							$.alerts.draggable = false;
							if ( e.keyCode == 13 || e.keyCode == 27 )
								$( "#popup_ok", popup ).trigger( 'click' );
						} );
						break;

					case 'confirm':
						$( "#popup_message", popup ).after( '<div id="popup_panel"><input class="action-button" type="button" value="' + $.alerts.okButton + '" id="popup_ok" /> <input class="action-button" type="button" value="' + $.alerts.cancelButton + '" id="popup_cancel" /></div>' );
						$( "#popup_ok", popup ).click( function ( e )
						{
							$.alerts._hide( e, callback, true );
						} );
						$( "#popup_cancel", popup ).click( function ( e )
						{
							$.alerts._hide( e, callback, false );

						} );
						$( "#popup_ok", popup ).focus();
						$( "#popup_ok", popup ).keypress( function ( e )
						{
							if ( e.keyCode == 13 )
								$( "#popup_ok", popup ).trigger( 'click' );
							if ( e.keyCode == 27 )
								$( "#popup_cancel", popup ).trigger( 'click' );
						} );
						$( "#popup_cancel", popup ).keypress( function ( e )
						{
							if ( e.keyCode == 13 )
								$( "#popup_cancel", popup ).trigger( 'click' );
							if ( e.keyCode == 27 )
								$( "#popup_cancel", popup ).trigger( 'click' );
						} );
						break;
					case 'prompt':
						$( "#popup_message", popup ).append( '<br /><input type="text" size="30" id="popup_prompt" />' ).after( '<div id="popup_panel"><input class="action-button" type="button" value="' + $.alerts.okButton + '" id="popup_ok" /> <input class="action-button" type="button" value="' + $.alerts.cancelButton + '" id="popup_cancel" /></div>' );
						$( "#popup_prompt", popup ).width( $( "#popup_message", popup ).width() - 50 );
						$( "#popup_ok", popup ).click( function ( e )
						{
							var val = $( "#popup_prompt", popup ).val();
							$.alerts._hide( e, callback, val );
						} );
						$( "#popup_cancel", popup ).click( function ( e )
						{
							$.alerts._hide( e, callback, null );
						} );
						$( "#popup_prompt", popup ).keypress( function ( e )
						{
							if ( e.keyCode == 13 )
								$( "#popup_ok", popup ).trigger( 'click' );
							if ( e.keyCode == 27 )
								$( "#popup_cancel", popup ).trigger( 'click' );
						} );
						$( "#popup_ok", popup ).keypress( function ( e )
						{
							if ( e.keyCode == 13 )
								$( "#popup_ok", popup ).trigger( 'click' );
							if ( e.keyCode == 27 )
								$( "#popup_cancel", popup ).trigger( 'click' );
						} );
						$( "#popup_cancel", popup ).keypress( function ( e )
						{
							if ( e.keyCode == 13 )
								$( "#popup_cancel", popup ).trigger( 'click' );
							if ( e.keyCode == 27 )
								$( "#popup_cancel", popup ).trigger( 'click' );
						} );
						if ( value )
							$( "#popup_prompt", popup ).val( value );
						// $("#popup_prompt").focus().select();
						break;
					case 'error':
						popup.data( 'easein', 'shake' );
						popup.data('easeout', 'bounceOutUp');

						$( "#popup_message", popup ).after( '<div id="popup_panel"><input class="action-button" type="button" value="' + $.alerts.okButton + '" id="popup_ok" /> <input class="action-button" type="button" value="' + $.alerts.cancelButton + '" id="popup_cancel" /></div>' );
						$( "#popup_ok", popup ).click( function ( e )
						{
							$.alerts._hide( e, callback, true );
						} );
						$( "#popup_cancel", popup ).click( function ( e )
						{
							$.alerts._hide( e, callback, false );
						} );
						$( "#popup_ok", popup ).focus();
						$( "#popup_ok", popup ).keypress( function ( e )
						{
							if ( e.keyCode == 13 )
								$( "#popup_ok", popup ).trigger( 'click' );
							if ( e.keyCode == 27 )
								$( "#popup_cancel", popup ).trigger( 'click' );
						} );
						$( "#popup_cancel", popup ).keypress( function ( e )
						{
							if ( e.keyCode == 13 )
								$( "#popup_cancel", popup ).trigger( 'click' );
							if ( e.keyCode == 27 )
								$( "#popup_cancel", popup ).trigger( 'click' );
						} );
						break;
				}

				//if ( typeof $.fn.BootstrapVersion == 'undefined' ) {
				$.alerts._reposition( dialog );
				$.alerts._maintainPosition( true );
				//}






				// Make draggable
				if ( $.alerts.draggable && typeof $.fn.BootstrapVersion == 'undefined' ) {
					try {
						popup.draggable( {handle: '#popup_title', containment: 'document'} );
						$( "#popup_title", popup ).css( {cursor: 'move'} );
						$( "#popup_content", popup ).css( {cursor: 'default'} );
					} catch ( e ) { /* requires jQuery UI draggables */
					}
				}

				if ( typeof $.fn.BootstrapVersion != 'undefined' ) {

					popup.css({
						width: popup.find('#popup_container' ).outerWidth(true),
						height: popup.find('#popup_container' ).outerHeight(true)
					});

					$.alerts._reposition( popup );

					popup.modal( {keyboard: false, show: true, backdrop: (type == 'dialog' ? false : true) } );
					popup.on( 'hidden.bs.modal', function ( e )
					{
console.log('Trigger event');
						if ( $.alerts.callbackProps !== null )
						{
							var p = $.alerts.callbackProps;
							$.alerts.callbackProps = null;

							if ( typeof p.call == 'function' ) {
								p.call( p.val );
							}
						}
						$( this ).remove();



					} );
				}

				if ( typeof aftershowcallback === 'function' ) {
					aftershowcallback( popup );
				}
			}
		},
		_hide: function ( e, callback, value )
		{
			if ( e ) {
				if ( typeof $.fn.BootstrapVersion == 'undefined' ) {
					$( e.target ).parents( '#popup_container:first' ).remove();
					$.alerts._overlay( 'hide' );
					$.alerts._maintainPosition( false );

					if ( callback ) {
						callback( value );
					}
				}
				else {
					$.alerts.callbackProps = {
						call: callback,
						val: value
					};

					$( e.target ).parents( 'div.modal:first' ).data('modalcallback', {
						call: callback,
						val: value
					}).modal( 'hide' );
				}
			}
		},
		_overlay: function ( status, type )
		{

			if ( type === 'dialog' ) {
				$.alerts._overlay( 'hide' );
				return;
			}

			if ( typeof $.fn.BootstrapVersion == 'undefined' ) {
				switch ( status ) {
					case 'show':
						$.alerts._overlay( 'hide' );
                        if (!$('#fullscreenContainer' ).length) {
						    $( "body" ).append( '<div id="popup_overlay"></div>' );
                        }
                        else {
                            $('#fullscreenContainer' ).append( '<div id="popup_overlay"></div>' );
                        }
						$( "#popup_overlay" ).css( {
							position: 'fixed',
							zIndex: 99998,
							top: '0px',
							left: '0px',
							width: '100%',
							height: '100%',
							background: $.alerts.overlayColor,
							opacity: $.alerts.overlayOpacity
						} );
						break;
					case 'hide':
						// remove all overlays from ajax request (faild requests)
						$( "#content" ).unmask();
						$( "#maincontent" ).unmask();

						// remove alerts overlay
						$( "#popup_overlay" ).remove();
						break;
				}
			}
		},
		_reposition: function ( popup )
		{

			if (typeof popup != 'object' || !popup.length ) {
				return;
			}
			var top = (($( window ).height() / 2) - (popup.outerHeight(true) / 2)) + $.alerts.verticalOffset;
			var left = (($('#fullscreenContainer').width() / 2) - (popup.outerWidth(true) / 2)) + $.alerts.horizontalOffset;

			if (!$('#fullscreenContainer' ).length) {
				left = (($(window).width() / 2) - (popup.outerWidth(true) / 2)) + $.alerts.horizontalOffset;
			}

			if ( top < 0 )
				top = 0;
			if ( left < 0 )
				left = 0;

			// IE6 fix
			if ( $.browser.msie && parseInt( $.browser.version ) <= 6 )
				top = top + $( window ).scrollTop();

			popup.css( {

				top: top + 'px',
				left: left + 'px'
			} );
			$( "#popup_overlay" ).height( $( document ).height() );
		},
		_maintainPosition: function ( status )
		{
			if ( $.alerts.repositionOnResize ) {
				switch ( status ) {
					case true:
						$( window ).bind( 'resize', $.alerts._reposition );
						break;
					case false:
						$( window ).unbind( 'resize', $.alerts._reposition );
						break;
				}
			}
		}

	};

	// Shortuct functions
	jDialog = function ( message, title, callback, aftershowcallback, options )
	{
		$.alerts.dialog( message, title, callback, aftershowcallback, options );
	};

	jAlert = function ( message, title, callback, aftershowcallback )
	{
		$.alerts.alert( message, title, callback, aftershowcallback );
	};

	jConfirm = function ( message, title, callback, aftershowcallback )
	{
		$.alerts.confirm( message, title, callback, aftershowcallback );
	};

	jPrompt = function ( message, value, title, callback, aftershowcallback )
	{
		$.alerts.prompt( message, value, title, callback, aftershowcallback );
	};

	jError = function ( message, title, callback, aftershowcallback )
	{
		$.alerts.error( message, title, callback, aftershowcallback );
	};

})( jQuery, window );
