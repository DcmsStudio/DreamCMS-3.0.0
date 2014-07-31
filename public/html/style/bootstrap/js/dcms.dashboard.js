/**
 * Created by marcel on 18.02.14.
 */


var Dashboard = function ()
{

	return {
		inited: false,
		visible: true,
		opts: {
			widgets: false
		},
		init: function ( opts )
		{
			var self = this;
			this.opts = $.extend( {}, this.opts, opts );

			Tools.scrollBar( $( '#dashboard #dropbox' ) );
			this.initWidgetInstall();

			if ( !$( '#menu-extras' ).find( '#toggle-dashboard' ).length ) {
				$( '#content-container,#main-content-statusbar' ).hide();
				$( '<span id="toggle-dashboard" class="Taskbar-Item active"><i class="fa fa-dashboard"></i></span>' ).insertAfter( $( '#console', $( '#menu-extras' ) ) );
				$( '#toggle-dashboard' ).click( function ()
				{
					if ( !$( '#main-tabs li' ).length ) {
						return;
					}

					if ( !$( this ).hasClass( 'active' ) ) {
						$( this ).addClass( 'active' );
						$( '#main-tabs li.active' ).attr( 'reopen', 1 ).removeClass( 'active' );
						$( '#main-content-buttons>div:visible,#main-content-tabs .content-tabs' ).attr( 'reopen', 1 ).hide();
						$( '#content-container,#main-content-statusbar' ).hide();

						Core.updateViewPort();

						$( '#dashboard' ).show();
					}
					else {
						$( this ).removeClass( 'active' );
						$( '#dashboard' ).hide();

						$( '#main-tabs li[reopen]' ).removeAttr( 'reopen' ).addClass( 'active' );
						$( '#main-content-buttons>div[reopen],#main-content-tabs .content-tabs[reopen]' ).removeAttr( 'reopen' ).show();
						$( '#content-container,#main-content-statusbar' ).show();

						$( window ).trigger( 'resize' );
					}
				} );

				$( window ).trigger( 'resize' );

			}


            var found_cols = $( "div.widget-column", $( '#dashboard' ) );
            var widgetcolumns = found_cols.length;
            var i = 1;
            if ( widgetcolumns == 3 ) {
                var i = 2;
            }


			for ( ; i <= widgetcolumns; i++ ) {
				$( "#widget-column-" + i ).sortable( {
					connectWith: $( 'div.widget-column', $( '#dashboard' ) ),
					//containment: '#dashboard',
					dropOnEmpty: true,
					forceHelperSize: true,
					forcePlaceholderSize: true,
					//placeholder: 'widget-placeholder',
					distance: 1,
					revert: 200,
					tolerance: 'pointer',
					handle: 'h2 .widget-title',
					scroll: false,
					zIndex: 99999,
					appendTo: 'body',
					helper: 'clone',
					update: function ()
					{
						setTimeout( function ()
						{
							self.updatePosition();
						}, 300 );
					},
					appendTo: 'body'
				} ).disableSelection();
			}


			this.widgetcolumns = widgetcolumns;

			if ( !this.opts.widgets ) {

				$.ajax( {
					url: 'admin.php?adm=widgets',
					cache: false,
					timeout: 600000,
					async: true
				} ).done( function ( data )
					{
						if ( Tools.responseIsOk( data ) ) {
							self.opts.widgets = data.widgets;
							self.initWidgets();
						}
					} );
			}
			else {
				this.initWidgets();
			}

			this.inited = true;

		},

		show: function ()
		{
			$( '#toggle-dashboard' ).addClass( 'active' );
			$( '#main-tabs li.active' ).attr( 'reopen', 1 ).removeClass( 'active' );
			$( '#main-content-buttons>div:visible,#main-content-tabs .content-tabs' ).attr( 'reopen', 1 ).hide();
			$( '#content-container,#main-content-statusbar' ).hide();
			$( '#dashboard' ).show();
			$( window ).trigger( 'resize' );
		},

		hide: function ()
		{
			$( '#toggle-dashboard' ).removeClass( 'active' );
			$( '#dashboard' ).hide();
			$( '#main-tabs li[reopen]' ).removeAttr( 'reopen' ).addClass( 'active' );
			$( '#main-content-buttons>div[reopen],#main-content-tabs .content-tabs[reopen]' ).removeAttr( 'reopen' ).show();
			$( '#content-container,#main-content-statusbar' ).show();
			$( window ).trigger( 'resize' );
		},

		initWidgetInstall: function ()
		{

			$( document ).unbind( 'mousemove.installwidget' ).bind( 'mousemove.installwidget', function ( e )
			{
				if ( e.pageX > $( window ).width() - 150 ) {

					if ( !$( '#add-widget' ).hasClass( 'anim' ) && $( '#add-widget' ).css( 'right' ) != 0 ) {
						$( '#add-widget' ).stop().addClass( 'anim' ).animate( {right: 0}, {duration: 300, complete: function ()
						{
							$( this ).removeClass( 'anim' );
						}} );
					}

				}
				else {
					if ( !$( '#add-widget' ).hasClass( 'anim' ) && parseInt( $( '#add-widget' ).css( 'right' ) ) >= 0 ) {
						$( '#add-widget' ).stop().addClass( 'anim' ).animate( {right: 0 - $( '#add-widget' ).width()}, {duration: 300, complete: function ()
						{
							$( this ).removeClass( 'anim' );
						}} );
					}
				}
			} );

			$( '#add-widget' ).unbind().click( function (e)
			{
                e.preventDefault();

				$( '#widgets' ).mask( 'bitte warten...' );
				$.get( 'admin.php?adm=widgets&action=list', function ( data )
				{

					$( '#widgets' ).unmask();

					if ( Tools.responseIsOk( data ) ) {
						$( '#widgets' ).hide();
						$( '#install-widgets' ).html( data.template ).show();

						Tools.scrollBar( $( '#dashboard #dropbox' ) );

					}
				} );

			} );
		},

		initWidgets: function ()
		{

			var totalwidgets = this.opts.widgets.length;

			// create first
			for ( var i = 0; i < totalwidgets; i++ ) {
				var widget = this.opts.widgets[i];

				// ignore widgets in other col as found widgetcolumns
				if ( (this.widgetcolumns == 3 && widget.col < 2) ) continue;
				if ( widget.col > this.widgetcolumns || (this.widgetcolumns == 1 && widget.col > 1) ) continue;

				el = this.createWidget( widget );
				var addToCol = widget.col < this.widgetcolumns ? widget.col : this.widgetcolumns;
				$( '#widget-column-' + addToCol ).append( el );
			}

			// load widget contens
			// better load for no errors

			for ( var i = 0; i < totalwidgets; i++ ) {
				var widget = this.opts.widgets[i];

				if ( typeof widget.content_html != 'undefined' ) {
					$( '#widget-' + widget.id + ' .panel-inner' ).unmask();

					if ( addToCol == 1 && $( widget.content_html ).find( 'img' ).attr( 'width' ) >= '16' ) {
						$( widget.content_html ).find( 'img' ).attr( {
							width: 16,
							height: 16
						} );
					}

					if ( widget.name ) { $( '#widget-' + widget.id ).find( '.widget-title' ).text( widget.name ); }
					$( '#widget-' + widget.id ).find( '.panel-inner' ).append( widget.content_html );

					if ( $( widget.content_html ).filter( 'script' ).length ) {
						Tools.eval( widget.content_html );
					}

					$( '#widget-' + widget.id ).show().delay( 300 ).animate( {
						opacity: 1
					}, {
						duration: 500,
						queue: false
					} );

					if ( i + 1 >= totalwidgets ) {
                        $(window).trigger('resizescrollbar');
					}
				}
				else {
					$.ajax( {
						url: 'admin.php?adm=widgets&get=' + widget.key + '&id=' + widget.id,
						data: {
							token: Config.get('token')
						},
						cache: false,
						timeout: 600000,
						async: true,
						global: false,
						success: function ( data )
						{
							$( '#widget-' + widget.id + ' .panel-inner' ).unmask();
							if ( Tools.responseIsOk( data ) ) {
								var dat = data.output;
								if ( addToCol == 1 && $( dat ).find( 'img' ).attr( 'width' ) >= '16' ) {
									$( dat ).find( 'img' ).attr( {
										width: 16,
										height: 16
									} );
								}

								if ( data.title ) { $( '#widget-' + data.id ).find( '.widget-title' ).text( data.title ); }
								$( '#widget-' + data.id ).find( '.panel-inner' ).append( dat );

								if ( $( data.output ).filter( 'script' ).length ) {
									Tools.eval( data.output );
								}

								$( '#widget-' + data.id ).show().delay( 300 ).animate( {
									opacity: 1
								}, {
									duration: 500,
									queue: false
								} );

								if ( i + 1 >= totalwidgets ) {
                                    $(window).trigger('resizescrollbar');
								}

							} else {
								// jAlert(data.error + id);
							}
						}
					} );
				}
			}

		},
		updatePosition: function ()
		{
			var params = {};
			for ( i = 1; i <= this.widgetcolumns; i++ ) {
				if ( $( '#widget-column-' + i ).length ) {
					params['col' + i] = $( '#widget-column-' + i ).sortable( 'toArray' ).join( ',' ).replace( /widget-/g, '' );
				}
			}
			params['action'] = 'order';
			params['adm'] = 'widgets';
			params['token'] = Config.get('token');

			$.post( 'admin.php', params, function ( data )
			{
				if ( !Tools.responseIsOk( data ) ) {
				}
			}, 'json' );
		},
		createWidget: function ( widget )
		{
			var self = this;

			el = $( '<div>' ).addClass( 'panel widget' ).attr( {
				id: 'widget-' + widget.id,
				rel: widget.id
			} ).css( {opacity: '0'} ).hide();
			titlebar = $( '<h2>' );
			titlebar.append( $( '<div class="widget-title">' ).addClass( 'fl' ).append( widget.name ) );
			confbar = $( '<div>' ).addClass( 'fr' );
			titlebar.append( confbar );
			titlebar.append( $( '<br>' ).addClass( 'clearer' ) );

			// add collapse/expand button
			//if(widget.collapsible==1) {
			// imgsrc = Cookie.get('widget_' + widget.id + '_collapsed')==1 ? 'down' : 'up' ;
			if ( widget.collapsible == 1 ) {
				imgsrc = 'up';
			}
			else {
				imgsrc = 'down';
				el.addClass( 'closed' );
			}

			coll_button = $('<span>')
            .addClass( 'collapsible fa fa-caret-' + imgsrc)
            .attr('title', cmslang.widgets_collapsible_title)
            .attr('rel', widget.id);
			//.bind( 'click', function (e) { e.preventDefault(); self.collapseWidget( $( this ).attr( 'rel' ) ); } );

			confbar.append( coll_button );
			titlebar.find( 'div.widget-title' ).bind( 'click', function (e) {
                e.preventDefault();
                self.collapseWidget( $( this ).parents( 'div.widget:first' ).attr( 'rel' ) );
            } );

			// add refresh button
			refresh_button = $('<span>')
                .addClass( 'fa fa-refresh')
                .attr('title', cmslang.widgets_refresh_title)
                .attr('rel', widget.id)
                .bind( 'click', function (e) {
                    e.preventDefault();

                    $( this).addClass('fa-spin');
                    self.refreshWidget( $( this ).attr( 'rel' ), $( this ) );

                } );

			confbar.append( refresh_button );

			// add config button?
			if ( widget.configurable == 1 ) {
				conf_button = $('<span>')
                    .attr('title', cmslang.widgets_config_title)
                    .attr('rel', widget.id)
                    .addClass( 'fa fa-gears')
                    .bind( 'click', function (e)
					{
                        e.preventDefault();
						self.configureWidget( $( this ).attr( 'rel' ), $( this ) )
					} );
				confbar.append( conf_button );
			}

			// add delete button
			delete_button = $( '<span>' )
                .attr('title', cmslang.widgets_remove_title)
                .attr('rel', widget.id)
                .addClass( 'fa fa-times')
                .bind( 'click', function (e) {e.preventDefault(); self.deleteWidget( $( this ).attr( 'rel' ), $( this ) ); } );

			confbar.append( delete_button );

			el.append( titlebar );

			_inner = $( '<div>' ).addClass( 'panel-inner' ).css( {
				padding: 0
			} );

			/*
			 _inner.append( getLoadingImage() );
			 _inner.append( $( '<span>' ).append( cmslang.mask_pleasewait ).css( {
			 color: '#999',
			 fontStyle: 'italic'
			 } ) );
			 */
			el.append( _inner );

			if ( widget.collapsible == 0 ) {
				_inner.css( {
					display: 'none'
				} );
			}

			return el;
		},
		collapseWidget: function ( id )
		{
			var container = $( '#widget-' + id );
			var img = container.find( 'span.collapsible:first' );
			var inner = container.find( 'div.panel-inner' ).text();

			if ( container.find( 'div.panel-inner' ).is( ':visible' ) ) {
				collapse = 0;
				container.addClass( 'closed' );

				$( '#widget-' + id + ' .panel-inner' ).slideUp( 'fast', function ()
				{
					img.removeClass('fa-caret-up').addClass('fa-caret-down');

					$( window ).trigger( 'resize' );

					$.get( 'admin.php?adm=widgets&action=setcollapse&id=' + id + '&collapse=' + collapse, {}, function ( data )
					{
						if ( Tools.responseIsOk( data ) ) {
						}
						else {
							//alert( 'Error ' + data.msg );
						}
					}, 'json' );

				} );

			} else {
				collapse = 1;
				container.removeClass( 'closed' );

				$( '#widget-' + id + ' .panel-inner' ).slideDown( 'fast', function ()
				{
                    img.removeClass('fa-caret-down').addClass('fa-caret-up');

					$( window ).trigger( 'resize' );

					$.get( 'admin.php?adm=widgets&action=setcollapse&id=' + id + '&collapse=' + collapse, {}, function ( data )
					{
						if ( Tools.responseIsOk( data ) ) {
						}
						else {
							//alert( 'Error ' + data.msg );
						}
					}, 'json' );

				} );

			}

		},
		refreshWidget: function ( id )
		{
			if ( !$( '#widget-' + id ).find( '.panel-inner:first' ).is( ':visible' ) ) {
				return;
			}

			$( '#widget-' + id ).find( '.panel-inner:first' ).mask( 'arbeite...' );

            var panelConfig = $( '#widget-' + id + ' .panel-inner div.panel-configuration' );

			$.ajax( {
				url: 'admin.php?adm=widgets&action=refresh&id=' + id,
				cache: false,
				timeout: 30000,
				async: true,
				global: false
			} ).done( function ( data )
				{

					$( '#widget-' + id ).find( '.panel-inner:first' ).unmask();

					if ( Tools.responseIsOk( data ) )
                    {

                        if (panelConfig.length) {
                            $( '#widget-' + data.id ).find( '.panel-inner div.panel-view' ).empty().append( data.output );
                        }
                        else {
                            $( '#widget-' + data.id ).find( '.panel-inner' ).empty().append( data.output );
                        }

						if ( $( data.output ).filter( 'script' ).length ) {
							Tools.eval( data.output );
						}

                        $( '#widget-' + id).find('.fa-spin:first').removeClass('fa-spin');

                        $(window).trigger('resizescrollbar');

					} else {
						jAlert( data.error + id );
					}
				} );

		},
		deleteWidget: function ( id, el )
		{
			var name = $( '#widget-' + id ).find( 'h2 .widget-title' ).text();
			jConfirm( cmslang.widgets_remove_confirm.replace( '%s', name ), cmslang.alert, function ( res )
			{
				if ( res == true ) {
					$.get( 'admin.php?adm=widgets&action=delete&id=' + id, {}, function ( data )
					{
						if ( Tools.responseIsOk( data ) ) {
							$( '#widget-' + id ).animate( { height: 0, opacity: '0'}, {
								duration: 350,
								complete: function ()
								{
									$( this ).remove();
								}
							} );

						} else {
							alert( 'Error ' + data );
						}
						$( '#content' ).unmask();
					}, 'json' );
				}
			} );
		},
		configureWidget: function ( _id, el )
		{
			var self = this;

			if ( _id < 1 ) {
				alert( "ID of this Widget not found!" );
				return;
			}

            var panelConfig = $( '#widget-' + _id + ' .panel-inner div.panel-configuration' );

            if ( panelConfig.length )
            {
                if ( panelConfig.is(':visible')) {
                    panelConfig.hide();
                    $( '#widget-' + _id + ' .panel-inner div.panel-view').show();
                }
                else {
                    $( '#widget-' + _id + ' .panel-inner div.panel-view').hide();
                    panelConfig.show();
                }
                return;
            }


            Win.windowID = 'widgets';


			$( '#widget-' + _id + ' .panel-inner' ).mask( 'arbeite...' );

			$.get( 'admin.php?adm=widgets&action=config&id=' + _id, {}, function ( data )
			{
				$( '#widget-' + _id + ' .panel-inner' ).unmask();

				if ( Tools.responseIsOk( data ) ) {



					var f1 = $( '<input>' ).attr( 'type', 'hidden' );
					f1.attr( 'name', 'adm' );
					f1.attr( 'value', 'widgets' );

					var f2 = $( '<input>' ).attr( 'type', 'hidden' );
					f2.attr( 'name', 'action' );
					f2.attr( 'value', 'save' );

					var f3 = $( '<input>' ).attr( 'type', 'hidden' );
					f3.attr( 'name', 'id' );
					f3.attr( 'value', _id );

                    var configContainer = $('<div class="panel-configuration"></div>').append(data.output);

					$( 'form', configContainer ).unbind().bind( 'submit', function ( e )
					{
						e.preventDefault();
						return false;
					} );

					var label = $( '<label>Titel</label>' );
                    var title = $( '#widget-' + _id ).find( 'h2:first').clone(false);
                    title.find('span').remove();

					var labelInput = $( '<input type="text" name="wgtlabel" value=""/>' ).val( title.text().trim() );

					$( 'form', configContainer ).prepend( labelInput ).prepend( label );
					$( 'form', configContainer ).append( f1 ).append( f2 ).append( f3 );
                    $( '.widget-save-button', configContainer ).unbind().bind( 'click', function ( e )
                    {
                        e.preventDefault();
                        self.saveWidgetConfig( _id );
                        el.trigger('click');
                        $(window).trigger('resizescrollbar');
                    } );

                    $(window).trigger('resizescrollbar');

                    var wrap = $('<div class="panel-view"></div>');
                    $( '#widget-' + data.id + ' .panel-inner').children().appendTo(wrap);
                    $( '#widget-' + data.id + ' .panel-inner').append(wrap.hide()).append(configContainer);

                    Win.prepareWindowFormUi();
                    Bootstraper.init(configContainer);

					//GUI_ELEMENTS.prepareElements( $( '#widget-' + _id + ' .panel-inner' ) );
				}
				else {
					alert( 'Error ' + data.msg );
				}
			}, 'json' );
		},
		saveWidgetConfig: function ( id )
		{

			if ( id < 1 ) {
				alert( "ID of this Widget not found!" );
				return;
			}

			$( '#widget-' + id + ' .panel-inner' ).mask( 'speichern...' );
			var _post = $( '#widget-' + id ).find( 'form' ).serialize();
			_post += '&adm=widgets';
			_post += '&token='+ Config.get('token');



			$.post( 'admin.php', _post, function ( data )
			{
				if ( Tools.responseIsOk( data ) ) {

					$.ajax( 'admin.php?adm=widgets&action=refresh&id=' + id, {
						cache: false,
						timeout: 600000,
                        async: true,
                        global: false
					} ).done( function ( data2 )
						{
							$( '#widget-' + id + ' .panel-inner' ).unmask();

							if ( Tools.responseIsOk( data2 ) )
                            {
								$( '#widget-' + data2.id ).find( 'h2 .widget-title' ).text( $( '#widget-' + id ).find( 'form' ).find( 'input[name=wgtlabel]' ).val() );
                                $( '#widget-' + data2.id + ' .panel-inner div.panel-view').empty().append( data2.output );

                                if ( $( data2.output ).filter( 'script' ).length ) {
                                    Tools.eval( data2.output );
                                }

							} else {
								jAlert( data2.error + id );
							}
						} );

				} else {
					alert( 'Error ' + data.msg );
				}
			}, 'json' );

		}

	};
};