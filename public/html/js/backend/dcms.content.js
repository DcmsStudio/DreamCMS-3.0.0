var contentGallery = function ( container, opt )
{

	var options = {};
	var container = $( container );
	var opts = opt || {};

	var self = this;

	var imageTypes = '*.jpg,*.jpeg,*.gif,*.png';
	var imageTypesLabel = "Alle Bild Dateien";
	var mediaTypes = '*.mpeg,*.mpg,*.mp4,*.avi,*.flv,*.swf,*.mp3,*.mov,*.ogg,*.webm';
	var mediaTypesLabel = "Alle Media Dateien";

	function addUploadThumb( data, file, listItem )
	{
		var img = $( '<img/>' ).attr( 'src', data.fileurl ).attr( 'id', 'preview-' + data.thumbid );
		var li = $( '<li>' ).attr( 'rel', data.thumbid ).attr( 'img', data.filepath ).attr( 'id', 'img-' + data.thumbid );

		if ( data.path ) {
			img.attr( 'src', data.path );
		}

		if ( data.width ) {
			img.attr( 'width', data.width );
		}

		if ( data.height ) {
			img.attr( 'height', data.height );
		}

		li.append( img );

		if ( !container.find( 'ul:first' ).length ) {

		}

		container.find( 'ul:first' ).append( li );
		self.bindBackendEvents();

		listItem.fadeOut( 'fast', function ()
		{
			$( this ).remove();
		} );

	}

	function makeFancyBoxes()
	{
		var images = container.find( 'img' );
		images.each( function ()
		{
			$( this ).css( 'cursor', 'pointer' );
			$( this ).attr( 'title', 'Bild anzeigen' );

			var id = $( this ).attr( 'id' ).replace( /^preview-/, '' );

			$( this ).fancybox( {
				'padding': 0,
				'transitionIn': 'elastic',
				'transitionOut': 'elastic',
				'easingIn': 'swing',
				'easingOut': 'swing',
				'speedIn': 300,
				'speedOut': 300,
				'href': $( this ).attr( 'rel' ),
				'title': 'Bild ID:' + id,
				'titlePosition': 'outside',
				'type': 'image',
				onComplete: function ( currentArray, currentIndex, currentOpts )
				{
					$( "#fancybox-inner" ).unbind( 'hover' ).hover( function ()
					{
						$( "#fancybox-title-over" ).slideUp( 300 );
					}, function ()
					{
						$( "#fancybox-title-over" ).slideDown( 300 );
					} );
				},
				'titleFormat': function ( title, currentArray, currentIndex, currentOpts )
				{
					var descriptionLayout = (title ? '<strong>' + title + '</strong><br/>' : '');
					return '<span id="fancybox-title-over">' + descriptionLayout + 'Bild ' + (currentIndex + 1) + ' von ' + currentArray.length + '</span>';
				}
			} );
		} );
	}

	this.options = opts;

	this.bindBackendEvents = function ()
	{

		container.find( 'li' ).each( function ()
		{
			$( this )
				.unbind( 'hover.img' )
				.bind( 'mouseenter.img', function ()
				{

					$( this ).find( '.img-opts' ).remove();

					var container = $( '<div class="img-opts"><span class="edit"></span><span class="delete"></span></div>' );
					container.appendTo( $( this ) );

					if ( opts.edit ) {
						container.find( 'span.edit' ).click( function ( e )
						{
							var li = $( this ).parents( 'li:first' );
							var id = li.attr( 'rel' );
							opts.edit( e, id, li );
						} );
					}
					else {
						container.find( 'span.edit' ).remove();
					}

					container.find( 'span.delete' ).click( function ( e )
					{
						var li = $( this ).parents( 'li:first' );
						var id = li.attr( 'rel' );

						if ( opts.delete ) {
							opts.delete( e, id, li );
						}
						else {
							li.fadeOut( 'fast', function ()
							{
								$( this ).remove();
							} );
						}
					} );

				} )
				.unbind( 'mouseleave.img' )
				.bind( 'mouseleave.img', function ()
				{
					$( this ).find( '.img-opts' ).remove();
				} );
		} );
	};

	this.initGalleryUpload = function ()
	{

		this.bindBackendEvents();

		if ( typeof opts.postParams != 'object' ) {
			Debug.error( 'The option "postParams" for the upload form is required' );
			return;
		}

		if ( typeof opts.controllName == 'undefined' ) {
			Debug.error( 'The option "controllName" for the upload form is required' );
			return;
		}

		var label = imageTypesLabel;
		var types = imageTypes;

		opts.postParams.token = Config.get('token');

		new Tools.MultiUploadControl( {
			dropHereLabel: '.drop-here',
			control: opts.controllName,
			url: "admin.php",
			postParams: opts.postParams,
			file_type_mask: '*.jpg,*.jpeg,*.gif,*.png',
			file_type_text: "Alle Bild Dateien",
			filePostParamName: 'Filedata',
			onAdd: function ()
			{
				Win.redrawWindowHeight( false, true );
			},
			onSuccess: function ( data, evaldata, file, listItem )
			{

				addUploadThumb( data, file, listItem );

				if ( opts.onAfterUploadDone ) {
					opts.onAfterUploadDone();
				}

				Win.redrawWindowHeight( false, true );

				/*
				 setTimeout(function () {
				 listItem.fadeOut(300, function () {
				 $(this).remove();
				 Win.redrawWindowHeight(false, true);
				 });
				 }, 2000);
				 */
			}
		} );
	};

};

var contentAnalyser = function ( opt )
{

	var contentPreview = $( '#' + Win.windowID ).find( '#optimize-content-preview' );
	var infos = $( '#' + Win.windowID ).find( '#optimize-infos' );
	var extra = $( '#' + Win.windowID ).find( '#optimize-extra' );
	extra.empty();
	contentPreview.find( 'div.panel-body:first' ).empty();


	$('#main-content-tabs').mask();
	$( '#' + Win.windowID ).mask( 'warte...' );

	var params = opt.params;
	params.contentanalyse = 1;
	params.token = Config.get('token');

	jQuery.ajax( {
		type: "POST",
		url: opt.url,
		data: params,
		async: true,
		success: function ( data )
		{
			$( '#' + Win.windowID +',#main-content-tabs' ).unmask();
			if ( Tools.responseIsOk( data ) ) {

				contentPreview.find( 'div.panel-body:first' ).append( data.content );
				contentPreview.find( 'a' ).attr( 'target', '_blank' );

				for ( var k in data.counters ) {
					infos.find( 'td[rel=' + k + ']' ).html( data.counters[k] );
				}

				if ( data.wordlist && data.wordlist.length ) {
					var table = $( '<table class="table table-striped table-hover"></table>' );
					for ( var i = 0; i < data.wordlist.length; ++i ) {
						var item = data.wordlist[i];
						var tr = $( '<tr></tr>' );
						tr.append( '<td width="50%">' + item.word + '</td>' );
						tr.append( '<td width="50%">(' + item.word_in_text + ' mal / ' + item.percent + '% / ' + item.wdf + ' WDF)</td>' );

						table.append( tr );
					}

					table.append('<td colspan="2"><em class="gray" style="margin-top: 10px">WDF = Within Document Frequency</em></td>');
					table.appendTo( extra );
				}
			}
			else {

			}
		}
	} );

};


