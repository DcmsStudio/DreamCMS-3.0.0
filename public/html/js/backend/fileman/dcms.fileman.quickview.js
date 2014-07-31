(function ( $ )
{
	Fileman.prototype.quickView = function ( fm )
	{
		var self = this,
			support = function ( codec )
			{
				var media = document.createElement( codec.substr( 0, codec.indexOf( '/' ) ) ),
					value = false;

				try {
					value = media.canPlayType && media.canPlayType( codec );
				} catch ( e ) {

				}

				return value && value !== '' && value != 'no';
			};

		/**
		 * Fileman instance
		 *
		 * @type Fileman
		 */
		this.fm = fm;

		this.mode = true;
		this.visible = false;

		// this.html5_video = $('<video>');

		this.support = {
			audio: {
				ogg: support( 'audio/ogg; codecs="vorbis"' ),
				mp3: support( 'audio/mpeg;' ),
				wav: support( 'audio/wav; codecs="1"' ),
				m4a: support( 'audio/x-m4a;' ) || support( 'audio/aac;' )
			},
			video: {
				//         wmv: support('video/x-ms-wmv') || support('video/x-ms-wm'),
				ogg: support( 'video/ogg; codecs="theora"' ),
				webm: support( 'video/webm; codecs="vp8, vorbis"' ),
				mp4: support( 'video/mp4; codecs="avc1.42E01E"' ) || support( 'video/mp4; codecs="avc1.42E01E, mp4a.40.2"' )
			}
		};

		this._hash = '';
		this.title = $( '<strong/>' );
		this.icon = $( '<div class="quicklook-icon cwd-icon"/>' );
		this.info = $( '<label/>' );
		this.media = $( '<div class="quicklook-media"/>' ).hide();
		this.content = $( '<div class="quicklook-content"/>' );
		this.infoWrapper = $( '<div class="quicklook-info-wrapper"/>' );
		this.infoInner = $( '<div class="quicklook-info"/>' );
		this.name = $( '<div class="quicklook-info-data"/>' );
		this.kind = $( '<div class="quicklook-info-data"/>' );
		this.size = $( '<div class="quicklook-info-data"/>' );
		this.date = $( '<div class="quicklook-info-data"/>' );
		this.add = $( '<div class="quicklook-info-data"/>' );

		this.infoInner
			.append( this.name )
			.append( this.kind )
			.append( this.size )
			.append( this.date )
			.append( this.url )
			.append( this.add );

		this.infoWrapper.append( this.icon ).append( this.infoInner );

		this.url = $( '<a href="#"/>' ).hide().click( function ( e )
		{
			e.preventDefault();
			window.open( $( this ).attr( 'href' ) );
			self.hide();
		} );

		/**
		 * Command specific init stuffs
		 *
		 * @return void
		 */
		this.init = function ()
		{

		}

		if ( $( '.fileman-ql' ).length ) {
			$( '.fileman-ql' ).remove();
		}

		this.win = $( '<div class="fileman-ql preview-container"/>' ).hide()
			.append( $( '<div class="fileman-ql-drag-handle"/>' ).append( $( '<span class="ui-icon ui-icon-circle-close"/>' ).click( function ()
				{
					self.hide();
				} ) )
				.append( this.title ) )

			.append( this.media )
			.append( this.content )
			.append( this.infoWrapper )
			// .appendTo(this.fm.view.win)
			.appendTo( 'body' )
			.draggable( {
				handle: '.fileman-ql-drag-handle'
			} )
			.bind( 'updateSize', function ()
			{
				if ( self.media.children().length ) {
					var t = self.media.children( ':first' );
					switch ( t[0].nodeName ) {
						case 'IMG':
							/*
							 var w = t.width(),
							 h = t.height(),
							 _w = self.win.width(),
							 _h = self.win.height() - self.th;

							 var setWidth = Math.round( t.width() * r ), setHeight = Math.round( t.height() * r );

							 t.css( {
							 width: setWidth,
							 height: setHeight
							 } );

							 */

							var windowHeight = self.win.height();
							if ( t.height() > windowHeight - (self.th + 10) ) {
								t.css( 'height', windowHeight - (self.th + 10) );
							}
							else {

								var diff = Math.floor( (windowHeight - (self.th + 10)) / 2 - (t.height()/2) );
								if ( diff > 0 ) {
									t.css( 'margin-top', Math.floor( diff ) );
								}
								else {
									t.css( 'margin-top', 0 );
								}
							}

							break;
						case 'IFRAME':
						case 'EMBED':
						case 'VIDEO':
							var windowHeight = (t.height() + self.th + 10);
							t.css( 'height', windowHeight - self.th - 10 );

							break;
						case 'OBJECT':
							var windowHeight = (t.height() + self.th + 10);
							t.children( 'embed' ).css( 'height', windowHeight - self.th - 10 );
					}
				}

			} )
			.resizable( {
				minWidth: 420,
				minHeight: 120,
				resize: function ( e, ui)
				{
					if ( self.media.children().length ) {
						var t = self.media.children( ':first' );
						switch ( t[0].nodeName ) {
							case 'IMG':
								var windowHeight = ui.size.height;

								var w = t.width(),
									h = t.height(),
									_w = self.win.width() - 10,
									_h = self.win.css( 'height' ) == 'auto' ? 350 : windowHeight - (self.th + 10),
									r = w > _w || h > _h
										? Math.min( Math.min( _w, w ) / w, Math.min( _h, h ) / h )
										: Math.min( Math.max( _w, w ) / w, Math.max( _h, h ) / h );

								var setWidth = Math.round( t.width() * r ), setHeight = Math.round( t.height() * r );

								t.css( {
									width: setWidth,
									height: setHeight
								} );
								var diff = ((_h/2) - (t.height()/2));
								console.log('diff', _h , t.height() );
								if ( diff > 0 ) {
									t.css( 'margin-top', diff);
								}
								else {
									t.css( 'margin-top', 0 );
								}

								break;
							case 'IFRAME':
							case 'EMBED':
							case 'VIDEO':

								t.css( 'height', ui.size.height - (self.th + 10) );
								break;
							case 'OBJECT':

								t.css( 'height', ui.size.height - (self.th + 10) );
								t.children( 'embed' ).css( 'height', ui.size.height - (self.th + 10) );
						}
					}
				} /*,
				stop: function(e, ui){
					if ( self.media.children().length ) {
						var t = self.media.children( ':first' );
						switch ( t[0].nodeName ) {
							case 'IMG':
								var windowHeight = ui.size.height;

								var w = t.width(),
									h = t.height(),
									_w = self.win.width() - 10,
									_h = self.win.css( 'height' ) == 'auto' ? 350 : windowHeight - (self.th + 10),
									r = w > _w || h > _h
										? Math.min( Math.min( _w, w ) / w, Math.min( _h, h ) / h )
										: Math.min( Math.max( _w, w ) / w, Math.max( _h, h ) / h );

								var setWidth = Math.round( t.width() * r ), setHeight = Math.round( t.height() * r );

								t.css( {
									width: setWidth,
									height: setHeight
								} );
								var diff = Math.round( (windowHeight - (self.th + 10)) / 2 - (setHeight/2) );
								if ( diff > 0 ) {
									t.css( 'margin-top', diff );
								}
								else {
									t.css( 'margin-top', 0 );
								}

								break;
							case 'IFRAME':
							case 'EMBED':
							case 'VIDEO':

								t.css( 'height', ui.size.height - (self.th + 10) );
								break;
							case 'OBJECT':

								t.css( 'height', ui.size.height - (self.th + 10) );
								t.children( 'embed' ).css( 'height', ui.size.height - (self.th + 10) );
						}
					}
				} */
			} );

		this.th = parseInt( this.win.children( ':first' ).css( 'height' ) ) || 18;

		/* All browsers do it, but some is shy to says about it. baka da ne! */
		this.mimes = {
			'image/jpeg': 'jpg',
			'image/gif': 'gif',
			'image/png': 'png'
		};

		for ( var i = 0; i < navigator.mimeTypes.length; i++ ) {
			var t = navigator.mimeTypes[i].type;
			if ( t && t != '*' ) {
				this.mimes[t] = navigator.mimeTypes[i].suffixes;
			}
		}

		if ( ($.browser.safari && navigator.platform.indexOf( 'Mac' ) != -1) || $.browser.msie ) {
			/* not booletproof, but better then nothing */
			this.mimes['application/pdf'] = 'pdf';
		}
		else {
			for ( var n = 0; n < navigator.plugins.length; n++ ) {
				for ( var m = 0; m < navigator.plugins[n].length; m++ ) {
					var e = navigator.plugins[n][m].description.toLowerCase();
					if ( e.substring( 0, e.indexOf( " " ) ) == 'pdf' ) {
						this.mimes['application/pdf'] = 'pdf';
						break;
					}
				}
			}
		}

		if ( this.mimes['image/x-bmp'] ) {
			this.mimes['image/x-ms-bmp'] = 'bmp';
		}

		if ( $.browser.msie && !this.mimes['application/x-shockwave-flash'] ) {
			this.mimes['application/x-shockwave-flash'] = 'swf';
		}



		this.hideinfo = function ()
		{
			this.infoWrapper.stop( true ).hide();
		};

		this.showinfo = function ()
		{
			this.infoWrapper.stop( true ).show();
		};

		this.media.bind( 'update', function ()
		{
			this.infoWrapper.show();
		} );

		/**
		 * Open quickLook window
		 **/
		this.show = function ()
		{
			if ( this.win.is( ':hidden' ) && self.fm.selected.length == 1 ) {
				var id = self.fm.selected[0],
					el = self.fm.layout.foldercontentContainer.find( '[hash="' + id + '"]' ),
					o = el.offset();

				update( function ( med )
				{

					if ( !med ) {
						self.win.hide();
						return;
					}

					var mediaWidth = parseInt( med.width(), 10 );
					var mediaHeight = parseInt( med.height(), 10 );

					if ( mediaWidth > $( window ).width() ) {
						mediaWidth = $( window ).width() - 100;
					}

					if ( mediaHeight > $( window ).height() ) {
						mediaHeight = $( window ).height() - 100;
					}

					if ( mediaHeight < 100 ) {
						mediaHeight = 100;
					}

					if ( mediaWidth < 350 ) {
						mediaWidth = 350;
					}

					windowHeight = (mediaHeight + self.th + 10);

					self.win.css( {
						width: 0,
						height: 0,
						left: o.left,
						top: o.top,
						opacity: '0'
					} ).show().animate( {
							width: mediaWidth + 20,
							height: windowHeight,
							opacity: 1,
							top: ($( window ).height() / 2) - (windowHeight / 2),
							left: ($( window ).width() / 2) - (mediaWidth + 30 / 2)
						}, 300, function ()
						{
							self.win.trigger( 'updateSize' );
							self.fm.lockShortcuts();
							self.visible = true;
						} );
				} );

				self.fm.lockShortcuts( true );

				/*
				 setTimeout(function() {

				 var mediaWidth = self.media.find('iframe:first,img:first,video:first').outerWidth(true);
				 var mediaHeight = self.media.find('iframe:first,img:first,video:first').outerHeight(true);

				 if ( mediaWidth > $(window).width() - 50 )
				 {
				 mediaWidth = $(window).width() - 50;
				 }

				 if ( mediaHeight > $(window).height() - 50 )
				 {
				 mediaHeight = $(window).height() - 50;
				 }



				 self.win.css({
				 width: el.width() - 20,
				 height: el.height(),
				 left: o.left,
				 top: o.top,
				 opacity: 0
				 }).show().animate({
				 width: mediaWidth,
				 height: mediaHeight,
				 opacity: 1,
				 top: ($(window).height() / 2) - (mediaHeight / 2),
				 left: ($(window).width() / 2) - (mediaWidth / 2)
				 }, 450, function () {
				 self.win.css({
				 height: 'auto'
				 } ).trigger('resize');

				 self.fm.lockShortcuts();
				 self.visible = true;
				 });
				 }, 550)
				 */

			}
		};

		/**
		 * Close quickLook window
		 **/
		this.hide = function ()
		{
			if ( this.win.is( ':visible' ) ) {
				var o, el = self.fm.layout.foldercontentContainer.find( '[hash="' + this._hash + '"]' );
				if ( el ) {
					o = el.offset();
					this.media.hide( 200 );//.empty()
					this.win.animate( {
						width: 0,
						height: 0,
						left: o.left,
						top: o.top,
						opacity: '0'
					}, 350, function ()
					{
						self.fm.lockShortcuts();
						reset();
						self.media.empty();
						self.win.hide().height( '' ).width( '' );

						self.visible = false;

					} );
				} else {
					this.win.fadeOut( 200, function ()
					{
						"use strict";
						$( this ).width( '' ).height( '' );
					} );
					reset();
					self.fm.lockShortcuts();
					self.media.empty();
					self.visible = false;
				}
			}
		};

		/**
		 * Open/close quickLook window
		 **/
		this.toggle = function ()
		{
			if ( this.win.is( ':visible' ) ) {
				this.hide();
				this.visible = false;
			} else {
				this.show();
				this.visible = true;
			}
		};
		/**
		 * Update quickLook window content if only one file selected,
		 * otherwise close window
		 **/
		this.update = function ()
		{
			if ( this.fm.selected.length != 1 ) {
				var classes = self.icon.get( 0 ).className.replace( /cwd-icon-\w+/gi, '' );
				self.icon.get( 0 ).className = classes;
				if ( !self.media.find( 'img' ).length ) {
					self.media.empty();
				}

				this.hide();
			}
			else if ( this.win.is( ':visible' ) && this.fm.selected[0] != this._hash ) {
				var classes = self.icon.get( 0 ).className.replace( /cwd-icon-\w+/gi, '' );
				self.icon.get( 0 ).className = classes;

				if ( !this.mode ) {

					self.content.hide();
					self.media.hide();

					// stop audio play
					if ( self.media.find( 'audio' ).length ) {
						var audioPlayer = self.media.find( 'audio' )[0];
						audioPlayer.pause();
						audioPlayer.currentTime = 0;
					}

					// stop video play
					if ( self.media.find( 'video' ).length ) {
						var videoPlayer = self.media.find( 'audio' )[0];
						videoPlayer.pause();
						videoPlayer.currentTime = 0;
					}

					self.showinfo();
				}
				else {

					self.media.hide();
					self.content.hide();

					if ( self.media.find( 'img' ).length || self.media.find( 'embed' ).length || self.media.find( 'audio' ).length || self.media.find( 'iframe' ).length ) {
						self.hideinfo();
						self.media.show();

						// stop audio play
						if ( self.media.find( 'audio' ).length ) {
							var audioPlayer = self.media.find( 'audio' )[0];
							if ( audioPlayer.currentTime > 0 )
								audioPlayer.play();
							//audioPlayer.currentTime = 0;
						}

						// stop video play
						if ( self.media.find( 'video' ).length ) {
							var videoPlayer = self.media.find( 'audio' )[0];
							if ( videoPlayer.currentTime > 0 )
								videoPlayer.play();
							//videoPlayer.currentTime = 0;
						}

					}
					else {
						self.showinfo();
					}

				}

				var id = self.fm.selected[0],
					el = self.fm.layout.foldercontentContainer.find( '[hash="' + id + '"]' ),
					o = el.offset();

				update();
			}
			else if ( this.win.is( ':visible' ) ) {
				var classes = self.icon.get( 0 ).className.replace( /cwd-icon-\w+/gi, '' );
				self.icon.get( 0 ).className = classes;
				var f = self.fm.getSelected( 0 );

				if ( !this.mode ) {
					self.content.hide();
					self.media.hide();

					// stop audio play
					if ( self.media.find( 'audio' ).length ) {
						var audioPlayer = self.media.find( 'audio' )[0];
						audioPlayer.pause();
						//audioPlayer.currentTime = 0;
					}

					// stop video play
					if ( self.media.find( 'video' ).length ) {
						var videoPlayer = self.media.find( 'audio' )[0];
						videoPlayer.pause();
						//videoPlayer.currentTime = 0;
					}

					self.showinfo();
				}
				else {

					self.media.hide();
					self.content.hide();

					if ( self.media.find( 'img' ).length ) {
						self.hideinfo();
						self.media.show();
					}
					else if ( self.media.find( 'embed' ).length || self.media.find( 'audio' ).length || self.media.find( 'iframe' ).length ) {
						self.hideinfo();
						self.media.show();

						// stop audio play
						if ( self.media.find( 'audio' ).length ) {
							var audioPlayer = self.media.find( 'audio' )[0];
							if ( audioPlayer.currentTime > 0 )
								audioPlayer.play();
							//audioPlayer.currentTime = 0;
						}

						// stop video play
						if ( self.media.find( 'video' ).length ) {
							var videoPlayer = self.media.find( 'audio' )[0];
							if ( videoPlayer.currentTime > 0 )
								videoPlayer.play();
							//videoPlayer.currentTime = 0;
						}

					}
					else {
						self.showinfo();
					}
				}
			}
		};

		/**
		 * Return height of this.media block
		 * @return Number
		 **/
		this.mediaHeight = function ()
		{
			return this.win.is( ':animated' ) || this.win.css( 'height' ) == 'auto' ? 315 : this.win.height() - this.content.height() - this.th;
		};

		this.setMode = function ( mode )
		{
			this.mode = mode ? true : false;
		};

		/**
		 * Clean quickLook window DOM elements
		 **/
		function reset()
		{

			var classes = self.icon.get( 0 ).className.replace( /cwd-icon-\w+/gi, '' );
			self.icon.get( 0 ).className = classes;

			self.win.unbind( 'mouseleave' );
			self.media.unbind( 'changesize' ).hide();
			self.win.attr( 'class', 'fileman-ql' ).css( 'z-index', self.fm.zIndex );
			self.title.empty();
			self.icon.attr( 'style', '' ).show();
			self.infoWrapper.attr( 'style', '' ).show();
			self.add.hide().empty();

			self._hash = '';
		}
		;

		/**
		 * Update quickLook window content
		 **/
		function update( callback )
		{
			var f = self.fm.getSelected( 0 );
			reset();

			self._hash = f.hash;
			self.title.text( f.name );
			self.win.addClass( self.fm.mime2class( f.mime ) );
			self.name.text( f.name );
			self.kind.text( self.fm.mime2kind( f.link ? 'symlink' : f.mime ) );
			self.size.text( self.fm.formatSize( f.size ) );
			self.date.text( self.fm.i18n( 'Modified' ) + ': ' + self.fm.formatDate( f.date ) );

			//f.url && self.icon.css('background', 'url("'+f.url+'") 50% 50% no-repeat');
			self.icon.addClass( self.fm.mime2class( f.mime ) );

			if ( f.url ) {
				self.url.text( f.url ).attr( 'href', f.url ).hide();

				for ( var i in self.plugins ) {
					if ( self.plugins[i].test && self.plugins[i].test( f.mime, self.mimes, f.name ) ) {
						if ( self.mode ) {
							self.hideinfo();
							self.media.show();
						}
						else {
							self.showinfo();
							self.media.hide();
						}
						var id = self.fm.selected[0],
							el = self.fm.layout.foldercontentContainer.find( '[hash="' + id + '"]' ),
							o = el.offset();

						if ( self.win.is( ':visible' ) ) {
							callback = function ( med )
							{

								if ( !med ) {
									self.win.hide();
									return;
								}

								self.win.trigger( 'updateSize' );
								self.fm.lockShortcuts();
								self.visible = true;
							};
						}

						self.plugins[i].show( self, f, callback );

						return;
					}
				}

				self.media.empty().hide();
				self.url.show();
			}
			else {
				self.media.empty().hide();
				self.url.hide();
			}

		}
		;

	};

	Fileman.prototype.quickView.prototype.plugins = {
		image: new function ()
		{

			this.test = function ( mime, mimes )
			{
				return mime.match( /^image\// );
			};

			this.show = function ( ql, f, callback )
			{
				var url, t, img;

				if ( f.mime.match( /^image\// ) && f.hash == ql._hash ) {

					f.dimensions && ql.add.append( '<span>' + f.dimensions + ' px</span>' ).show();

					if ( ql.mode ) {
						ql.hideinfo();
						ql.media.show();
					}
					else {
						ql.showinfo();
						ql.media.hide();
					}

					var iconUrl = f.url + ($.browser.msie || $.browser.opera ? '?' + Math.random() : '')

					ql.media.children( ':first' ).remove();
					img = $( '<img/>' ).addClass( 'image' )
						.hide()
						.appendTo( ql.media )
						.load( function ()
						{

							ql.icon.css( 'background', 'url("' + iconUrl + '") center center no-repeat' );
							img.show();

							var mw = img.get( 0 ).width, mh = img.get( 0 ).height;

							var t = $( this ).unbind( 'load' ).attr( 'maxwidth', mw ).attr( 'maxheight', mh ).css( {
								maxWidth: mw, maxHeight: mh
							} );
							img.hide();
							setTimeout( function ()
							{

								if ( f.hash == ql._hash ) {
									ql.media.find( '.remove' ).fadeOut( 200, function ()
									{
										$( this ).remove()

									} );

									var prop = (mw / mh).toFixed( 2 );
									ql.media.bind( 'changesize',function ()
									{
										var pw = parseInt( ql.win.width() - 20 ),
											ph = parseInt( ql.win.height() - (ql.th+10) ),
											w, h;

										if ( prop < (pw / ph).toFixed( 2 ) ) {
											h = ph;
											w = Math.floor( h * prop );
										} else {
											w = pw;
											h = Math.floor( w / prop );
										}
										img.width( w ).height( h ); //.css( 'margin-top', h < ph ? Math.floor( (ph - h) / 2 ) : 0 );

										var diff = ((ph/2) - (img.height()/2));
										console.log('diff', ph , img.height() );
										if ( diff > 0 ) {
											t.css( 'margin-top', diff);
										}
										else {
											t.css( 'margin-top', 0 );
										}


									} ).trigger( 'changesize' );

									ql.fm.lockShortcuts( false );

									//show image
									img.fadeIn( 200, function ()
									{
										callback( $( this ).show() );
									} );

								}
							}, 1 );
						} )
						.attr( 'src', iconUrl );

				}

				function preview( img )
				{
					$( img ).width( img.get( 0 ).width ).height( img.get( 0 ).height );
					img.show();

					var prop = (img.width() / img.height()).toFixed( 2 );

					var pw = parseInt( ql.media.innerWidth() ),
						ph = parseInt( ql.media.innerHeight() ),
						w, h;

					if ( prop < (pw / ph).toFixed( 2 ) ) {
						h = ph;
						w = Math.floor( h * prop );
					} else {
						w = pw;
						h = Math.floor( w / prop );
					}

					//	img.css( 'margin-top', h < ph ? Math.floor( (ph - h) / 2 ) : 0 ).hide();

					/*
					 ql.media.bind( 'changesize',function ()
					 {
					 var pw = parseInt( ql.media.innerWidth() ),
					 ph = parseInt( ql.media.innerHeight() ),
					 w, h;

					 if ( prop < (pw / ph).toFixed( 2 ) ) {
					 h = ph;
					 w = Math.floor( h * prop );
					 } else {
					 w = pw;
					 h = Math.floor( w / prop );
					 }

					 img.width( w ).height( h ).css( 'margin-top', h < ph ? Math.floor( (ph - h) / 2 ) : 0 );

					 } ).trigger( 'changesize' );
					 */
					ql.fm.lockShortcuts( false );

					//show image
					img.show().animate( {
						opacity: 1
					}, 450, function ()
					{
						$( this ).css( {
							position: '',
							zIndex: ''
						} );
						//ql.media.trigger('changesize');
						callback( $( this ) );
					} );

				}
			};

		},
		text: new function ()
		{

			this.test = function ( mime, mimes )
			{
				return (mime.indexOf( 'text' ) == 0 && mime.indexOf( 'rtf' ) == -1) || mime.match( /application\/(xml|javascript|json)/ );
			};

			this.show = function ( ql, f, callback )
			{
				if ( f.hash == ql._hash ) {
					ql.media.empty().append( '<iframe src="' + f.url + '" style="height:' + ql.mediaHeight() + 'px" class="quicklook-preview-text" />' );
					callback( ql.media.find( 'iframe' ) );
				}
			};
		},
		swf: new function ()
		{

			this.test = function ( mime, mimes, filename )
			{
				return mime.match( /(shockwave|flash)/ ) || mime == 'application/x-shockwave-flash' || filename.match( /.*\.(flv|swf)$/g );
			};

			this.show = function ( ql, f, callback )
			{
				if ( f.hash == ql._hash ) {
					//  console.log('swf');

					ql.media.empty().append( '<embed pluginspage="http://www.macromedia.com/go/getflashplayer" quality="high" src="' + f.url + '" style="width:100%;height:' + ql.mediaHeight() + 'px" type="application/x-shockwave-flash" class="quicklook-preview-flash" />' );
					callback( ql.media.find( 'embed' ) );
				}
			};
		},
		audio: new function ()
		{
			this.autoplay = false;
			this.mimes = {
				'audio/mpeg': 'mp3',
				'audio/mpeg3': 'mp3',
				'audio/mp3': 'mp3',
				'audio/x-mpeg3': 'mp3',
				'audio/x-mp3': 'mp3',
				'audio/x-wav': 'wav',
				'audio/wav': 'wav',
				'audio/x-m4a': 'm4a',
				'audio/aac': 'm4a',
				'audio/mp4': 'm4a',
				'audio/x-mp4': 'm4a',
				'audio/ogg': 'ogg'
			};

			this.test = function ( mime, mimes )
			{
				return mime.indexOf( 'audio' ) == 0 && mimes[mime];
			};

			this.show = function ( ql, f, callback )
			{

				if ( f.hash == ql._hash ) {

					// var h = ql.win.is(':animated') || ql.win.css('height') == 'auto' ? 100 : ql.win.height()-ql.content.height()-ql.th;
					var type = this.mimes[f.mime];

					if ( ql.support.audio[type] ) {
						var node = $( '<audio class="quicklook-preview-audio" controls preload="auto" autobuffer="true"><source src="' + f.url + '" /></audio>' )
							.appendTo( ql.media.empty() );
						this.autoplay && node[0].play();

						if ( ql.mode == true ) {
							//     ql.hideinfo();
							ql.media.show();
						}
					}
				}

				callback( (node ? node.find( 'video' ) : false) );
			};
		},

		video: new function ()
		{
			this.mimes = {
				'video/mp4': 'mp4',
				'video/x-m4v': 'mp4',
				'video/ogg': 'ogg',
				'application/ogg': 'ogg',
				'video/webm': 'webm'
			};

			this.test = function ( mime, mimes )
			{
				return mime.indexOf( 'video' ) == 0 && this.mimes[mime];
			};

			this.show = function ( ql, f, callback )
			{

				if ( f.hash == ql._hash ) {
					var code = '<video class="video" controls preload="auto" autobuffer="true" style="width:99%;max-height: 95%">' +
						'<source src="' + f.url + '" type="' + f.mime + '"></source>' +
						'</video>';

					if ( ($.browser.mozilla || $.browser.firefox || $.browser.opera) && this.mimes[f.mime] === 'mp4' ) {
						code = '<h1>Sorry your Browser has no ' + this.mimes[f.mime].toUpperCase() + ' support!</h1><iframe src="' + f.url + '" style="width:0;height:0"></iframe>';
					}
					else if ( $.browser.ie && (this.mimes[f.mime] === 'ogg' || this.mimes[f.mime] === 'webm') ) {
						code = '<h1>Sorry your Browser has no ' + this.mimes[f.mime].toUpperCase() + ' support!</h1><iframe src="' + f.url + '" style="width:0;height:0"></iframe>';
					}

					ql.media.empty().append( code );

					callback( code.find( 'video' ) );

					// ql.media.empty()/*.append(wrapper);*/.append('<video class="video" src="'+f.url+'" controls preload="auto" autobuffer="true" style="width:99%;height:99%"><source src="'+ f.url +'" /><embed class="quicklook-preview-video" src="'+f.url+'" style="width:99%;height:99%" /></video>');
				}
			};

		},
		pdf: new function ()
		{

			this.test = function ( mime, mimes )
			{
				return mime == 'application/pdf' && mimes[mime];
			};

			this.show = function ( ql, f, callback )
			{
				if ( f.hash == ql._hash ) {

					var node = $( '<iframe class="quicklook-preview-pdf"/>' )
						.hide()
						.appendTo( ql.media.empty() )
						.load( function ()
						{
							node.show();

							callback( node );

						} )
						.attr( 'src', f.url );
				}
			};
		}

	}
})( jQuery, window );