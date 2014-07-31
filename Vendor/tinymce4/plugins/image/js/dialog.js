/**
 * Created by marcel on 17.04.14.
 */
function getWin()
{
	return(!window.frameElement && window.dialogArguments) || opener || parent || top;
}

var w = getWin(), win;
var editor = parent.tinymce.EditorManager.activeEditor;
var params = editor.windowManager.getParams();
var trans = w.tinymce.util.I18n, jcrop_api;

var img = $( '<img id="image-target" />' ), ratioY = 0, ratioX = 0, width = 0, height = 0;

function updateCoords( c )
{
	var w = parseFloat( c.w /* ratioX */ );
	var h = parseFloat( c.h /* ratioY */ );
	var x = parseFloat( c.x /* ratioX */ );
	var y = parseFloat( c.y /* ratioY */ );

	$( '#x', $( '#crop-form' ) ).val( x );
	$( '#y', $( '#crop-form' ) ).val( y );
	$( '#w', $( '#crop-form' ) ).val( w );
	$( '#h', $( '#crop-form' ) ).val( h );

	$( '#cropsize' ).text( Math.round( w ) + ' x ' + Math.round( h ) );

	if ( c.w ) {
		$( '#width,#height,#constrain' ).attr( 'disabled', 'disabled' );
		$( '#do-crop', $( '#crop-form' ) ).show();
		$( '#cropsize' ).show();
	}
	else {

		$( '#width,#height,#constrain' ).removeAttr( 'disabled' );
		$( '#do-crop', $( '#crop-form' ) ).hide();
		$( '#cropsize' ).hide();

	}
}

function checkCoords()
{
	if ( parseInt( $( '#w', $( '#crop-form' ) ).val() ) ) return true;
	return false;
};

function recalcSize()
{
	var widthCtrl, heightCtrl, newWidth, newHeight;

	widthCtrl = $( '#width' );
	heightCtrl = $( '#height' );

	newWidth = widthCtrl.val();
	newHeight = heightCtrl.val();

	if ( $( '#constrain' ).is( ':checked' ) && width && height && newWidth && newHeight ) {
		if ( width != newWidth ) {
			newHeight = Math.round( (newWidth / width) * newHeight );
			heightCtrl.val( newHeight );
		} else {
			newWidth = Math.round( (newHeight / height) * newWidth );
			widthCtrl.val( newWidth );
		}
	}

	width = newWidth;
	height = newHeight;
}

function getImageData()
{
	var cropFile = $( '#crop-file' ).val();
	return {
		width: $( '#width' ).val(),
		height: $( '#height' ).val(),

		baseWidth: width,
		baseHeight: height,

		cropwidth: img.width(),
		cropheight: img.height(),

		cropFile: $( '#crop-file' ).val().replace( /\?.*/, '' ).replace( editor.settings.baseUrl, '' ),
		baseSrc: $( '#file' ).val().replace( /\?.*/, '' ).replace( editor.settings.baseUrl, '' ),
		alt: $( '#alt' ).val(),
		fancybox: $( '#fancybox' ).is( ':checked' ),
		constrain: $( '#constrain' ).is( ':checked' ),
		hspace: $( '#hspace' ).val(),
		vspace: $( '#vspace' ).val(),
		border: $( '#border' ).val(),
		style: $( '#style' ).val(),
	};
}
function calculateAspectRatioFit( srcWidth, srcHeight, maxWidth, maxHeight )
{
	if (srcWidth <= maxWidth && srcHeight <= maxHeight ) {
		return { width: srcWidth , height: srcHeight  };
	}



	var ratio = Math.min( maxWidth / srcWidth, maxHeight / srcHeight );
	return { width: srcWidth * ratio, height: srcHeight * ratio };
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

	$( '#do-crop,#do-reset', $( '#crop-form' ) ).hide();

	var params = {};
	if ( $( '#file' ).val() ) {
		params.selectfile = $( '#file' ).val();
	}

	win = editor.windowManager.open( {
		title: 'Filemanager',
		file: url,
		width: $( top.window ).width() / 1.3,
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

					$( '#crop-file' ).val( '' );
					$( '#file' ).val( val.path );
					$( '#height' ).val( val.height );
					$( '#width' ).val( val.width );

					if ( jcrop_api ) {
						jcrop_api.destroy();
					}
					img.remove();
					img = $( '<img id="image-target" />' );
					img.appendTo( $( '#preview-container' ) );

					// reset
					img.attr( {
						'baseheight': '',
						'basewidth': ''
					} )
						.removeAttr( 'height' )
						.removeAttr( 'width' )
						.removeAttr( 'style' );

					vh = 0;

					img.off( "load" ).attr( 'src', editor.getParam( 'baseUrl' ) + val.path + '?_' + new Date().getTime() ).on( 'load', function ()
					{
						// read real width and height of the image
						var w = img.get( 0 ).width, h = img.get( 0 ).height;

						width = w, height = h; // for base size

						img.attr( {
							'baseheight': h,
							'basewidth': w
						} ).attr( 'style', '' ).css( { maxWidth: $( '#preview-container' ).width(), maxHeight: $( '#preview-container' ).height() } ).show();

						// srcWidth, srcHeight, maxWidth, maxHeight
						var calced = calculateAspectRatioFit( w, h, $( '#preview-container' ).width(), $( '#preview-container' ).height() );
						img.width( calced.width ).height( calced.height );

						// calculate ratio
						ratioY = h / calced.height, ratioX = w / calced.width;

						setTimeout( function ()
						{

							img.Jcrop( {
								onSelect: updateCoords,
								onChange: updateCoords,
								/*
								 maxSize: [$( '#preview-container' ).width(), $( '#preview-container' ).height()],
								 */
								boxWidth: calced.width,
								boxHeight: calced.height

							}, function ()
							{
								jcrop_api = this;
							} );

						}, 100 );

					} );

					// Close the window
					win.close();
					editor.filebrowser = editor.filebrowserParent = null;
				}
			},
			{
				text: 'Close',
				onclick: function ()
				{
					editor.filebrowser = editor.filebrowserParent = null;
					win.close();
				}
			}
		]
	}, params );

	editor.filebrowserParent = win;
	editor.filebrowser = win;

}

function init()
{
	$( '#crop-form' ).find( '#do-reset,#do-crop' ).hide();
	$( '#preview-container' ).width( $( window ).width() - 20 ).height( $( '#preview-container' ).parents( 'div.row' ).height() );
	// $( '#preview-container' ).width( $( '#preview-container' ).parent().width() ).height( ( $( window ).height() - 40 ) - 10 );

	img.appendTo( $( '#preview-container' ) );

	editor.filebrowser = null;
	$( '#tabs a:first' ).tab( 'show' );

	if ( params.baseSrc ) {
		$( '#file' ).val( params.baseSrc.replace( /\?.*/, '' ).replace( editor.settings.baseUrl, '' ) );
	}

	if ( params.alt ) {
		$( '#alt' ).val( params.alt );
	}

	if ( parseInt( params.width ) > 0 ) {
		$( '#width' ).val( parseInt( params.width ) );
		width = parseInt( params.baseWidth );
	}

	if ( parseInt( params.height ) > 0 ) {
		$( '#height' ).val( parseInt( params.height ) );
		height = parseInt( params.baseHeight );
	}

	if ( params.fancybox ) {
		$( '#fancybox' ).val( 1 ).prop( 'checked', true ).trigger( 'change' );
	}

	if ( parseInt( params.border ) > 0 ) {
		$( '#border' ).val( parseInt( params.border ) );
	}

	if ( params.style ) {
		$( '#style' ).val( params.style );
	}

	if ( parseInt( params.vspace ) > 0 ) {
		$( '#vspace' ).val( parseInt( params.vspace ) );
	}

	if ( parseInt( params.hspace ) > 0 ) {
		$( '#hspace' ).val( parseInt( params.hspace ) );
	}

	// set default constrain
	if ( params.constrain ) {
		$( '#constrain' ).val( 1 ).prop( 'checked', true ).trigger( 'change' );
	}
	else if ( typeof params.constrain == 'undefined' ) {
		$( '#constrain' ).val( 1 ).prop( 'checked', true ).trigger( 'change' );
	}

	// create the image
	if ( params.baseSrc ) {
		var useSrc = params.baseSrc;

		if ( params.cropFile ) {
			$( '#oldcrop' ).val( params.cropFile );
			$( '#crop-file' ).val( params.cropFile );
			useSrc = params.cropFile;
		}

		useSrc = useSrc.replace( /\?.*/, '' ).replace( editor.settings.baseUrl, '' );

		img.off( "load" )
			.attr( 'src', editor.settings.baseUrl + useSrc )
			.removeAttr( 'style' )
			.removeAttr( 'height' )
			.removeAttr( 'width' )
			.show()
			.on( 'load', function ()
			{
				// read real width and height of the image
				var w = img.get( 0 ).width, h = img.get( 0 ).height;

				if ( !$( '#height' ).val() ) {
					$( '#height' ).val( h );
					$( '#constrain' ).val( 1 ).prop( 'checked', true ).trigger( 'change' );
				}
				if ( !$( '#width' ).val() ) {
					$( '#width' ).val( w );
					$( '#constrain' ).val( 1 ).prop( 'checked', true ).trigger( 'change' );
				}

				img.attr( 'style', '' ).attr( {
					'baseheight': h,
					'basewidth': w
				} ).css( { maxWidth: $( '#preview-container' ).width(), maxHeight: $( '#preview-container' ).height() } );

				// srcWidth, srcHeight, maxWidth, maxHeight
				var calced = calculateAspectRatioFit( w, h, $( '#preview-container' ).width(), $( '#preview-container' ).height() );
				img.width( calced.width ).height( calced.height );

				// calculate ratio
				ratioY = h / calced.height, ratioX = w / calced.width;

				// create new crop
				img.Jcrop( {
					onSelect: updateCoords,
					onChange: updateCoords,
					boxWidth: calced.width,
					boxHeight: calced.height
				}, function ()
				{
					jcrop_api = this;
				} );
			}
		);
	}

	$( '#width,#height' )
		.bind( 'change',function ()
		{
			recalcSize();
		} ).bind( 'keyup', function ()
		{
			recalcSize();
		} );

	$( '#crop-form' ).find( 'button#do-reset' ).click( function ()
	{
		if ( jcrop_api ) {
			jcrop_api.destroy();
		}

		var btn = $( this );
		var src = $( this ).parents( 'form:first' ).find( '#oldcrop' ).val();
		src = src.replace( /\?.*/, '' ).replace( editor.settings.baseUrl, '' );

		$.ajax( {
			url: editor.settings.baseUrl + 'admin.php',
			dataType: "json",
			cache: false,
			method: 'POST',
			data: {
				action: 'crop',
				do: 'clear',
				source: src
			},
			success: function ( data )
			{
				if ( data && typeof data === 'object' && typeof data.success !== 'undefined' && data.success == true ) {
					btn.hide();

					$( '#cropsize' ).hide();

					if ( $( '#file' ).val() ) {

						img.remove();

						img = $( '<img id="image-target" />' );
						img.appendTo( $( '#preview-container' ) );
						img
							.removeAttr( 'style' )
							.removeAttr( 'height' )
							.removeAttr( 'width' ).attr( {
								'baseheight': '',
								'basewidth': ''
							} )
							.attr( 'src', editor.settings.baseUrl + $( '#file' ).val() )
							.off( "load" ).on( 'load', function ()
							{

								// read real width and height of the image
								var w = img.get( 0 ).width, h = img.get( 0 ).height;
								width = w, height = h; // for base size

								$( '#crop-file' ).val( '' );

								$( '#height' ).val( w );
								$( '#width' ).val( h );

								img.attr( {
									'baseheight': h,
									'basewidth': w
								} ).css( { maxWidth: $( '#preview-container' ).width(), maxHeight: $( '#preview-container' ).height() } ).width( 'auto' ).height( 'auto' );

								// srcWidth, srcHeight, maxWidth, maxHeight
								var calced = calculateAspectRatioFit( w, h, $( '#preview-container' ).width(), $( '#preview-container' ).height() );
								img.width( calced.width ).height( calced.height );

								// calculate ratio
								ratioY = h / calced.height, ratioX = w / calced.width;

								setTimeout( function ()
								{
									// create new crop
									img.Jcrop( {
										onSelect: updateCoords,
										onChange: updateCoords,
										boxWidth: calced.width,
										boxHeight: calced.height
									}, function ()
									{
										jcrop_api = this;
									} );
								}, 10 );
							}
						);
					}
				}
			}
		} );

	} );

	$( '#crop-form' ).find( 'button#do-crop' ).click( function ()
	{
		if ( checkCoords() ) {

			if ( jcrop_api ) {
				jcrop_api.destroy();
			}

			var src = img.attr( 'src' );
			src = src.replace( /\?.*/, '' ).replace( editor.settings.baseUrl, '' );

			$.ajax( {
				url: editor.settings.baseUrl + 'admin.php',
				dataType: "json",
				cache: false,
				method: 'POST',
				data: {
					action: 'crop',
					width: $( '#w' ).val(),
					height: $( '#h' ).val(),
					x: $( '#x' ).val(),
					y: $( '#y' ).val(),
					source: src
				},
				success: function ( data )
				{
					if ( data && typeof data === 'object' && typeof data.success !== 'undefined' && data.success == true ) {

						img.remove();
						img = $( '<img id="image-target" />' );
						img.appendTo( $( '#preview-container' ) );

						img.off( "load" )
							.removeAttr( 'style' )
							.removeAttr( 'height' )
							.removeAttr( 'width' ).attr( {
								'baseheight': '',
								'basewidth': ''
							} )
							.attr( 'src', editor.settings.baseUrl + data.src )
							.on( 'load', function ()
							{
								// read real width and height of the image
								var w = img.get( 0 ).width, h = img.get( 0 ).height;

								$( '#oldcrop' ).val( data.src );
								$( '#crop-file' ).val( data.src );

								img.attr( {
									'baseheight': h,
									'basewidth': w
								} ).css( { maxWidth: $( '#preview-container' ).width(), maxHeight: $( '#preview-container' ).height() } ).width( 'auto' ).height( 'auto' );

								// srcWidth, srcHeight, maxWidth, maxHeight
								var calced = calculateAspectRatioFit( w, h, $( '#preview-container' ).width(), $( '#preview-container' ).height() );
								img.width( calced.width ).height( calced.height );

								// calculate ratio
								ratioY = h / calced.height, ratioX = w / calced.width;

								// create new crop
								img.Jcrop( {
									onSelect: updateCoords,
									onChange: updateCoords,
									boxWidth: calced.width,
									boxHeight: calced.height
								}, function ()
								{
									jcrop_api = this;
								} );
							} );

						// show the reset button
						$( '#cropsize,button#do-crop' ).hide();
						$( '#crop-form' ).find( 'button#do-reset' ).show();
					}
					else if ( data && typeof data === 'object' && typeof data.success !== 'undefined' && data.success != true ) {
						if ( typeof data.msg !== 'undefined' ) {
							alert( data.msg );
						}
						else {
							alert( 'Crop Error' );
						}
					}
					else {
						alert( 'Crop Error' );
					}
				}
			} );
		}
		else {

		}

	} );

	$( '#browse' ).click( function ()
	{
		if ( editor.filebrowser !== null ) {
			return;
		}
		openmanager();
	} );

}

$( document ).ready( function ()
{
	init();
} );