if ( typeof Forum != 'object' ) {
	var Forum = {
		isMod: false,
		mode: null, // thread, forum, other
		uploadSetting: {maxuploadsize: 0, token: '', uploadextensions: '*.*'},
		forumID: null,
		threadID: null,
		postID: null,
		forumAjaxSending: '<div id="forum-ajax-mask"><div class="load"></div></div>',
		init: function ()
		{
			var self = this, location = document.location.href;
			location = location.replace( /https?:\/\/([^\/]*)\//i, '' );
			location = location.replace( /\/([a-z0-9_\-]+)\.(html?|dcms|xml|txt|php)\??.*$/ig, '' ); // remove the document name

			var segments = location.split( '/' );

			//  segments.shift(); // remove plugin
			var firstSegment = segments.shift(); // remove forum
			var str = segments.join( '/' );

			console.log( 'Segments:' + str );
			if ( segments.length ) {
				if ( segments[0].match( /\d+?/ ) ) {
					this.forumID = parseInt( segments[0] );
					this.mode = 'forum';
				}

				if ( segments[0].match( /^[a-z_]/ ) ) {
					for ( var x = 0; x < segments.length; ++x ) {

						if ( x == 0 ) {
							this.mode = segments[x].toString();
							if ( this.threadID == null && segments.length > 1 && segments[x + 1].match( /^[\d]+$/ ) ) {
								this.threadID = parseInt( segments[x + 1] );
							}
							break;
						}
						else {
							this.mode = segments[x].toString();

							if ( this.threadID == null && segments[x + 1].match( /^[\d]+$/ ) ) {
								this.threadID = parseInt( segments[x + 1] );
							}

							break;
						}

					}
				}
				else {
					this.mode = 'forum';
				}
			}
			else {
				this.mode = 'forum';
			}

			this.mode = this.mode.toLowerCase();

			// Find the forumid by navigation :)
			if ( !this.forumID ) {
				$( '#breadcrumbs' ).find( 'a[href*="/forum/"]:last' ).each( function ()
				{
					if ( !self.forumID ) {
						var href = $( this ).attr( 'href' );
						if ( href.match( /.*\/forum\/[\d]+\/.*/g ) ) {
							self.forumID = parseInt( href.replace( /.*\/forum\/([\d]+)\/.*/g, '$1' ) );
						}
					}
				} );
			}

			console.log( 'Mode: ' + this.mode + ' ThredID: ' + this.threadID + ' ForumID: ' + this.forumID );
			this.setModMessage();
			switch ( this.mode ) {
				case 'forum':
				default:
					this.bindForumEvents();
					this.bindForumModMenu();
					break;

				case 'thread':
					this.bindThreadEvents();
					this.bindThreadModMenu();
					this.bindLightboxes();
					break;

				case 'replythread':
				case 'newthread':
					this.bindPostForm();
					break;

				case 'search':

					break;
			}

			$( 'body' ).append( this.forumAjaxSending );
		},

		initThreadList: function ( opts )
		{
			if ( this.forumID > 0 ) {
				var self = this;

				$( document ).click( function ( e )
				{
					if ( $( '#forum-menu-threadlist' ).is( ':visible' ) ) {
						if ( !$( e.target ).is( '#forum-menu-threadlist' ) && !$( e.target ).parents( '#forum-menu-threadlist' ).length && !$( e.target ).parents( '#threadlist-menu-extra' ).length ) {
							$( '#forum-menu-threadlist' ).fadeOut( 300 );
						}
					}
				} );

				if ( !$( '#forum-menu-threadlist' ).length ) {
					var threadlist_extramenu = '<div class="forum-menu threadlist" id="forum-menu-threadlist"><form action="' + document.location.href.replace( /\?.*$/g, '' ) + '" method="POST">'
						+ '<fieldset><legend>Sortieren nach</legend>'
						+ '<select name="order"><option value="date">letzter Beitrag</option><option value="hits">Anzahl Aufrufe</option><option value="rating">Bewertung</option>'
						+ '<option value="posts">Anzahl Beiträge</option><option value="title">Titel</option><option value="attachments">Anzahl Attachments</option></select>'
						+ '</fieldset>'
						+ '<fieldset><legend>Sortieren nach</legend>'
						+ '<select name="sort"><option value="desc">Absteigend</option><option value="asc">Aufsteigend</option></select>'
						+ '</fieldset>'
						+ '<fieldset><legend>Zeitraum</legend>'
						+ '<select name="timefilter"><option value="all">alle</option><option value="1">von Heute</option>'
						+ '<option value="7">letzten 7 Tage</option>'
						+ '<option value="15">letzten 15 Tage</option>'
						+ '<option value="30">letzten 30 Tage</option>'
						+ '<option value="45">letzten 45 Tage</option>'
						+ '<option value="60">letzten 60 Tage</option>'
						+ '<option value="75">letzten 75 Tage</option>'
						+ '<option value="365">letzten 365 Tage</option>'
						+ '</select></fieldset>'
						+ '<button id="set-forumfilter" type="button">Übernehmen</button>'
						+ '</form></div>';

					$( 'body' ).append( threadlist_extramenu );
				}

				$( '#threadlist-menu-extra' ).unbind().bind( 'click', function ( e )
				{
					e.preventDefault();

					if ( !$( '#forum-menu-threadlist' ).is( ':visible' ) ) {
						$( '#forum-menu-threadlist' ).css( {left: $( this ).offset().left + $( this ).width() - $( '#forum-menu-threadlist' ).outerWidth( true ),
							top: $( this ).parent().offset().top + $( this ).parent().outerHeight( true ) } ).fadeIn( 300 );
					}
					else {

						$( '#forum-menu-threadlist' ).fadeOut( 300 );
					}
				} );

				$( '#set-forumfilter' ).click( function ( e )
				{
					e.stopPropagation();
					$( this ).parents( 'form:first' ).submit();
				} );

				if ( $( '#thread-ajax-paging' ).length == 1 ) {

					var page = parseInt( opts.page ) ? parseInt( opts.page ) : 1;
					var pages = parseInt( opts.pages ) ? parseInt( opts.pages ) : 1;

					$( '#thread-ajax-paging' ).find( 'span:first' ).click( function ()
					{
						if ( pages > page ) {
							$( '#forum-ajax-mask' ).animate( {top: 0}, {
								duration: 300,
								complete: function ()
								{
									setTimeout( function ()
									{
										$.ajax( {
											method: 'POST',
											async: false,
											url: document.location.href.replace( /\?.*$/g, '' ),
											data: {
												forumid: self.forumID,
												ajax: true,
												page: page + 1
											}
										} ).done( function ( data )
											{
												$( '#forum-ajax-mask' ).animate( {top: 0 - $( '#forum-ajax-mask' ).outerHeight( true ) - 10}, {duration: 500} );

												if ( responseIsOk( data ) ) {
													page++;

													if ( data.threadrows ) {
														var rows = $( data.threadrows );
														rows.hide().insertBefore( $( '#thread-ajax-paging' ) );
													}

													if ( data.paging ) {
														$( '#thread-paging' ).html( data.paging );
													}

													if ( page >= pages ) {
														$( '#thread-ajax-paging' ).fadeOut( 250, function ()
														{
															rows.fadeIn( 250 );
														} );
													}
													else {
														rows.fadeIn( 250 );
													}
												}
												else {
													console.log( '--------- Ajax Error ---------' );
													console.log( data );
												}
											} );

									}, 300 );
								}
							} );

						}

					} );
				}
			}
		},

		setFilter: function ( o )
		{
			if ( o.order ) {
				$( '#forum-menu-threadlist select[name=order]' ).find( 'option[value=' + o.order + ']' ).attr( 'selected', 'selected' ).prop( 'selected', true );
			}
			if ( o.sort ) {
				$( '#forum-menu-threadlist select[name=sort]' ).find( 'option[value=' + o.sort + ']' ).attr( 'selected', 'selected' ).prop( 'selected', true );
			}
			if ( o.timefilter ) {
				$( '#forum-menu-threadlist select[name=timefilter]' ).find( 'option[value=' + o.timefilter + ']' ).attr( 'selected', 'selected' ).prop( 'selected', true );
			}
		},

		executeForumModAction: function ( action, postdata, callback )
		{
			if ( postdata && action ) {
				var self = this;

				$( '#forum-ajax-mask' ).animate( {top: 0}, {
					duration: 300,
					complete: function ()
					{
						setTimeout( function ()
						{

							postdata.cp = 'plugin';
							postdata.plugin = 'forum';
							postdata.action = action;
							postdata.forumid = self.forumID;
							if ( self.mode == 'thread' ) {
								postdata.threadid = self.threadID;
							}
							$.ajax( {
								method: 'POST',
								async: false,
								url: 'index.php',
								data: postdata
							} ).done( function ( data )
								{
									$( '#forum-ajax-mask' ).animate( {top: 0 - $( '#forum-ajax-mask' ).outerHeight( true ) - 10}, {duration: 500} );
									if ( typeof callback === 'function' ) {
										callback( data );
									}
								} );

						}, 200 );

					}
				} );
			}
		},

		bindForumModMenu: function ()
		{
			var self = this;
			if ( !$( '#forum-modmenu' ).length ) {
				return;
			}

			$( '#mod-check-all' ).change( function ()
			{
				if ( $( this ).is( ':checked' ) ) {
					$( '#forumposts-list' ).find( 'li .moderate-check input:checkbox' ).attr( 'checked', 'checked' ).prop( 'checked', true ).trigger( 'change' );
				}
				else {
					$( '#forumposts-list' ).find( 'li .moderate-check input:checkbox' ).removeAttr( 'checked' ).prop( 'checked', false ).trigger( 'change' );
				}
			} );
			$( '#forum-modmenu' ).appendTo( 'body' );

			$( document ).click( function ( e )
			{
				if ( $( '#forum-modmenu' ).is( ':visible' ) ) {
					if ( !$( e.target ).is( '#forum-modmenu' ) && !$( e.target ).parents( '#forum-modmenu' ).length && !$( e.target ).parents( '#moderate-check-all' ).length ) {
						$( 'li.active', $( '#moderate-check-all' ) ).removeClass( 'active' );
						$( '#forum-modmenu' ).fadeOut( 300 );
					}
				}
			} );

			$( 'li', $( '#moderate-check-all' ) ).click( function ( e )
			{
				e.preventDefault();

				if ( !$( '#forum-modmenu' ).is( ':visible' ) ) {
					$( this ).addClass( 'active' );
					$( '#forum-modmenu' ).css( {
						left: $( this ).offset().left,
						top: $( '#moderate-check-all' ).offset().top + $( '#moderate-check-all' ).outerHeight( true )
					} ).fadeIn( 300 );
				}
				else {
					$( '#forum-modmenu' ).fadeOut( 300 );
					$( this ).removeClass( 'active' );
				}
			} );

			function hideForumModMenu( timer )
			{
				$( '#forum-modmenu' ).fadeOut( (timer == 0 ? 0 : 300) );
				$( 'li', $( '#moderate-check-all' ) ).removeClass( 'active' );
			}

			function resetCheckBoxes()
			{
				$( '#thread-posts' ).find( '.moderate-check input:checkbox' ).removeAttr( 'checked' ).prop( 'checked', false ).trigger( 'change' );
				$( '#moderate-check-all' ).find( 'input:checkbox' ).removeAttr( 'checked' ).prop( 'checked', false ).trigger( 'change' );
			}

			function getChildren( data, node )
			{

				for ( var x = 0; x < data.length; ++x ) {
					var cbk = '<label for="to-' + data[x].forumid + '">'
						+ '<input name="to" id="to-' + data[x].forumid + '" type="radio" value="' + data[x].forumid + '"> '
						+ data[x].title + '</label>';

					if ( data[x].containposts != 1 ) {
						cbk = '<span>' + data[x].title + '</span>';
					}

					var li = $( '<li></li>' )
						.attr( 'rel', data[x].forumid )
						.append( cbk );

					if ( data[x].children && data[x].children.length ) {
						var ul = $( '<ul></ul>' );
						ul.appendTo( li );
						getChildren( data[x].children, ul );
						node.append( li );
					}
					else {
						if ( data[x].containposts == 1 ) {
							node.append( li );
						}
						else {
							var ul = $( '<ul><li><em>Empty Forum</em></li></ul>' );
							ul.appendTo( li );
							node.append( li );
						}
					}
				}
			}

			$( '#forum-modmenu' ).find( 'li' ).click( function ()
			{
				var ids = [];

				$( '#forumposts-list' ).find( 'li .moderate-check input:checkbox:checked' ).each( function ()
				{
					ids.push( parseInt( this.value ) );
				} );

				if ( !ids.length || !$( this ).attr( 'rel' ) ) {
					return;
				}

				var action = $( this ).attr( 'rel' );

				switch ( action.toLowerCase() ) {
					case 'move':

						var offset = $( '#forum-modmenu' ).offset();

						if ( $( '#mod-forumlist-menu' ).length ) {
							hideForumModMenu();

							var dialog = $( '#mod-forumlist-menu' );
							self.setDialogPos( dialog, 'center' );
							$( '.forum-dialog-mask' ).fadeIn( 300 );
							dialog.fadeIn( 300 );

							/*
							 $( '#mod-forumlist-menu' ).css( {
							 left: $( '#moderate-check-all li:first' ).offset().left,
							 top: $( '#moderate-check-all' ).offset().top + $( '#moderate-check-all' ).outerHeight( true )
							 } ).fadeIn( 200 );
							 */
						}
						else {
							$( '#forum-ajax-mask' ).animate( {top: 0}, {
								duration: 300,
								complete: function ()
								{
									$.ajax( {
										url: 'index.php',
										method: 'POST',
										async: false,
										data: {
											cp: 'plugin',
											plugin: 'forum',
											action: 'run',
											getforums: true
										}
									} ).done( function ( data )
										{

											resetCheckBoxes();

											$( '#forum-ajax-mask' ).animate( {top: 0 - $( '#forum-ajax-mask' ).outerHeight( true ) - 10}, {duration: 500} );

											if ( responseIsOk( data ) ) {
												var tmpList = $( '<div class="secundary"></div>' );
												var tmpUlList = $( '<ul></ul>' );

												if ( data.forums ) {
													var forums = data.forums;
													for ( var x = 0; x < forums.length; ++x ) {

														var cbk = $( '<label for="to-' + forums[x].forumid + '">'
															+ '<input name="to" id="to-' + forums[x].forumid + '" type="radio" value="' + forums[x].forumid + '"> '
															+ forums[x].title + '</label>' );

														cbk.addClass( 'cat' );
														if ( forums[x].parent == 0 || forums[x].parent == 'root' ) {
															cbk.addClass( 'cat-root' );
														}

														if ( forums[x].containposts != 1 ) {
															cbk = $( '<span>' + forums[x].title + '</span>' );
														}

														var li = $( '<li></li>' )
															.attr( 'rel', forums[x].forumid )
															.append( cbk );

														if ( forums[x].children ) {
															var ul = $( '<ul></ul>' );
															getChildren( forums[x].children, ul );
															ul.appendTo( li );
															li.appendTo( tmpUlList );
														}
														else {
															if ( forums[x].containposts == 1 ) {
																li.appendTo( tmpUlList );
															}
															else {
																var ul = $( '<ul><li><em>Empty Forum</em></li></ul>' );
																ul.appendTo( li );
																li.appendTo( tmpUlList );
															}
														}
													}
												}

												tmpUlList.appendTo( tmpList );

												var dialog = self.dialog( {
													title: 'Veschieben in',
													content: tmpList,
													pos: 'center',
													mode: 'modal',
													height: 300
												} );

												dialog.attr( 'id', 'mod-forumlist-menu' ).addClass( 'forum-move-dialog' )
													.hide()
													.find( 'div.forum-dialog-footer' )
													.append( $( '<span class="btn btn-ok">Übernehmen</span>' ) )
													.append( $( '<span class="btn btn-cancel">Abbrechen</span>' ) );

												dialog.find( '.btn-cancel' ).click( function ()
												{
													$( '#mod-forumlist-menu,.forum-dialog-mask' ).fadeOut( 300 );
												} );

												$( 'body' ).append( dialog ).append( '<div class="forum-dialog-mask"></div>' );
												self.setDialogPos( dialog, 'center' );

												//
												dialog.find( '.btn-ok' ).click( function ( ev )
												{
													var id = parseInt( $( '#mod-forumlist-menu' ).find( 'input:checked' ).val() );

													if ( id > 0 ) {
														self.executeForumModAction( 'movethread', {
															threadid: ids,
															toforumid: id
														}, function ( ajaxdata )
														{
															$( '#mod-forumlist-menu,.forum-dialog-mask' ).fadeOut( 300 );

															if ( responseIsOk( ajaxdata ) ) {
																for ( var i = 0; i < ids.length; ++i ) {
																	$( '#thread-' + ids[i] ).delay( 50 ).fadeOut( 300, function ()
																	{
																		$( this ).remove();

																		if ( i + 1 >= ids.length ) {
																			// window.location.reload( true );
																		}
																	} );
																}
															}

														} );
													}

												} );

												hideForumModMenu( 0 );

												dialog.fadeIn( 350 );
											}
										} );
								}
							} );
						}

						break;

					case 'open':
						hideForumModMenu();
						self.executeForumModAction( 'publishthread', {
							threadid: ids,
							mode: 1
						}, function ( ajaxdata )
						{
							if ( responseIsOk( ajaxdata ) ) {
								for ( var i = 0; i < ids.length; ++i ) {
									var img = $( '#thread-' + ids[i] ).find( '.thread-postfoldericon img' );
									img.removeClass( 'locked' );
									img.attr( 'src', img.attr( 'src' ).replace( '_lock', '' ) );
								}
							}
						} );
						break;
					case 'close':
						hideForumModMenu();
						self.executeForumModAction( 'publishthread', {
							threadid: ids,
							mode: 1
						}, function ( ajaxdata )
						{
							resetCheckBoxes();
							if ( responseIsOk( ajaxdata ) ) {
								for ( var i = 0; i < ids.length; ++i ) {
									var img = $( '#thread-' + ids[i] ).find( '.thread-postfoldericon img' );
									img.addClass( 'locked' );
									img.attr( 'src', img.attr( 'src' ).replace( '_lock', '' ).replace( '.', '_lock.' ) );
								}
							}
						} );
						break;

					case 'publish':
						hideForumModMenu();
						self.executeForumModAction( 'publishthread', {
							threadid: ids,
							mode: 1
						}, function ( ajaxdata )
						{
							resetCheckBoxes();
							if ( responseIsOk( ajaxdata ) ) {
								for ( var i = 0; i < ids.length; ++i ) {
									$( '#thread-' + ids[i] ).removeClass( 'unpublished' );
								}
							}
						} );
						break;
					case 'unpublish':
						hideForumModMenu();
						self.executeForumModAction( 'publishthread', {
							mode: 0,
							threadid: ids
						}, function ( ajaxdata )
						{
							resetCheckBoxes();
							if ( responseIsOk( ajaxdata ) ) {
								for ( var i = 0; i < ids.length; ++i ) {
									$( '#thread-' + ids[i] ).addClass( 'unpublished' );
								}
							}
						} );
						break;
					case 'pin':
						hideForumModMenu();
						self.executeForumModAction( 'pin', {
							threadid: ids
						}, function ( ajaxdata )
						{
							if ( responseIsOk( ajaxdata ) ) {
								for ( var i = 0; i < ids.length; ++i ) {
									$( '#thread-' + ids[i] ).find( 'div:first' ).addClass( 'is-pinned' ).find( '.thread-pinned' ).remove();
									$( '<span class="thread-pinned">Pinned</span>' ).insertBefore( $( '#thread-' + ids[i] ).find( '.thread-subject a' ) );
								}
							}
						} );
						break;
					case 'unpin':
						hideForumModMenu();
						self.executeForumModAction( 'unpin', {
							threadid: ids
						}, function ( ajaxdata )
						{
							resetCheckBoxes();
							if ( responseIsOk( ajaxdata ) ) {
								for ( var i = 0; i < ids.length; ++i ) {
									$( '#thread-' + ids[i] ).find( 'div:first' ).removeClass( 'is-pinned' ).find( '.thread-pinned' ).remove();
								}
							}
						} );
						break;
					case 'delete':
						self.executeForumModAction( 'deletethread', {
							threadid: ids
						}, function ( ajaxdata )
						{
							if ( responseIsOk( ajaxdata ) ) {
                                if (self.forumID) {
                                    document.location.href = '/forum/' + self.forumID;
                                }
                                else {
                                    $('#thread-posts').empty().append( '<div><h1>Thema wurde gelöscht</h1></div>' );

                                }
							}
						} );
						break;
				}

			} );
		},

		bindThreadModMenu: function ()
		{
			var self = this;
			if ( !$( '#forum-modmenu' ).length ) {
				return;
			}

			$( '#mod-check-all' ).change( function ()
			{
				if ( $( this ).is( ':checked' ) ) {
					$( '#thread-posts' ).find( '.moderate-check input:checkbox' ).attr( 'checked', 'checked' ).prop( 'checked', true ).trigger( 'change' );
				}
				else {
					$( '#thread-posts' ).find( '.moderate-check input:checkbox' ).removeAttr( 'checked' ).prop( 'checked', false ).trigger( 'change' );
				}
			} );

			$( '#forum-modmenu' ).appendTo( 'body' );

			$( document ).click( function ( e )
			{
				if ( $( '#forum-modmenu' ).is( ':visible' ) ) {
					if ( !$( e.target ).is( '#forum-modmenu' ) && !$( e.target ).parents( '#forum-modmenu' ).length && !$( e.target ).parents( '#moderate-check-all' ).length ) {
						$( 'li.active', $( '#moderate-check-all' ) ).removeClass( 'active' );
						$( '#forum-modmenu' ).fadeOut( 300 );
					}
				}
			} );

			$( '#thread-posts li .jump-to-modmenu' ).click( function ()
			{
				$( 'html, body' ).animate( { scrollTop: $( '#thread-posts-header' ).offset().top }, 'fast' );
			} );

			$( 'li', $( '#moderate-check-all' ) ).click( function ( e )
			{
				e.preventDefault();

				if ( !$( '#forum-modmenu' ).is( ':visible' ) ) {
					$( this ).addClass( 'active' );
					$( '#forum-modmenu' ).css( {
						left: ($( this ).offset().left + $( this ).outerWidth( true )) - $( '#forum-modmenu' ).width(),
						top: $( '#moderate-check-all' ).offset().top + $( '#moderate-check-all' ).outerHeight( true )
					} ).fadeIn( 300 );
				}
				else {
					$( '#forum-modmenu' ).fadeOut( 300 );
					$( this ).removeClass( 'active' );
				}
			} );

			function hideForumModMenu()
			{
				$( '#forum-modmenu' ).fadeOut( 300 );
				$( 'li', $( '#moderate-check-all' ) ).removeClass( 'active' );
			}

			function resetCheckBoxes()
			{
				$( '#thread-posts' ).find( '.moderate-check input:checkbox' ).removeAttr( 'checked' ).prop( 'checked', false ).trigger( 'change' );
				$( '#moderate-check-all' ).find( 'input:checkbox' ).removeAttr( 'checked' ).prop( 'checked', false ).trigger( 'change' );
			}

			$( '#forum-modmenu' ).find( 'li[rel]' ).click( function ()
			{
				var ids = [];

				$( '#thread-posts' ).find( '.moderate-check input:checkbox:checked' ).each( function ()
				{
					ids.push( parseInt( this.value ) );
				} );

				var action = $( this ).attr( 'rel' );

				switch ( action.toLowerCase() ) {
					case 'move':

						var offset = $( '#forum-modmenu' ).offset();

						if ( $( '#mod-forumlist-menu' ).length ) {
							hideForumModMenu();
							$( '#mod-forumlist-menu,.forum-dialog-mask' ).fadeIn( 300 );
							self.setDialogPos( $( '#mod-forumlist-menu' ), 'center' );

						}
						else {
							$( '#forum-ajax-mask' ).animate( {top: 0}, {
								duration: 300,
								complete: function ()
								{
									$.ajax( {
										url: 'index.php',
										method: 'POST',
										async: false,
										data: {
											cp: 'plugin',
											plugin: 'forum',
											action: 'run',
											getforums: true
										}
									} ).done( function ( data )
										{

											$( '#forum-ajax-mask' ).animate( {top: 0 - $( '#forum-ajax-mask' ).outerHeight( true ) - 10}, {duration: 500} );

											if ( responseIsOk( data ) ) {
												var tmpList = $( '<div ></div>' );
												var tmpUlList = $( '<ul></ul>' );

												if ( data.forums ) {
													var forums = data.forums;
													for ( var x = 0; x < forums.length; ++x ) {
														tmpUlList.append( $( '<li></li>' ).attr( 'rel', forums[x].forumid ).append( forums[x].title ) );
													}
												}

												tmpUlList.appendTo( tmpList );

												var dialog = self.dialog( {
													title: 'Veschieben in',
													content: tmpList,
													pos: 'center',
													mode: 'modal',
													height: 350
												} );

												dialog.attr( 'id', 'mod-forumlist-menu' )
													.hide()
													.find( 'div.forum-dialog-footer' )
													.append( $( '<span class="btn btn-cancel">Abbrechen</span>' ) );

												dialog.find( '.btn-cancel' ).click( function ()
												{
													$( '#mod-forumlist-menu,.forum-dialog-mask' ).fadeOut( 300 );
												} );

												$( 'body' ).append( dialog ).append( '<div class="forum-dialog-mask"></div>' );
												self.setDialogPos( dialog, 'center' );

												//
												$( '#mod-forumlist-menu' ).find( 'li' ).click( function ( ev )
												{
													var id = parseInt( $( this ).attr( 'rel' ) );

													if ( id > 0 ) {
														self.executeForumModAction( 'movethread', {
															threadid: ids,
															toforumid: id
														}, function ( ajaxdata )
														{
															if ( responseIsOk( ajaxdata ) ) {
																for ( var i = 0; i < ids.length; ++i ) {
																	$( '#thread-' + ids[i] ).fadeOut( 300, function ()
																	{
																		$( this ).remove();
																	} );
																}
															}
														} );
													}

													$( '#mod-forumlist-menu' ).fadeOut( 200 );
												} );

												hideForumModMenu();
												dialog.fadeIn( 200 );
											}
										} );
								}
							} );
						}

						break;

					case 'open':
						hideForumModMenu();
						self.executeForumModAction( 'closethread', {
							mode: 0
						}, function ( ajaxdata )
						{
							resetCheckBoxes();

							if ( responseIsOk( ajaxdata ) ) {
								$( 'div.thread-buttons span[rel="thread-closed"]' ).hide();
								$( 'div.thread-buttons [rel="thread-replythread"]' ).show();

							}
						} );
						break;
					case 'close':
						hideForumModMenu();
						self.executeForumModAction( 'closethread', {
							mode: 1
						}, function ( ajaxdata )
						{
							resetCheckBoxes();

							if ( responseIsOk( ajaxdata ) ) {
								$( 'div.thread-buttons span[rel="thread-closed"]' ).show();
								$( 'div.thread-buttons [rel="thread-replythread"]' ).hide();
							}
						} );
						break;

					case 'publishpost':
						if ( !ids.length || !$( this ).attr( 'rel' ) ) {
							return;
						}

						hideForumModMenu();
						self.executeForumModAction( 'publishpost', {
							postid: ids,
							mode: 1
						}, function ( ajaxdata )
						{
							resetCheckBoxes();

							if ( responseIsOk( ajaxdata ) ) {
								for ( var i = 0; i < ids.length; ++i ) {
									$( '#post_' + ids[i] ).removeClass( 'unpublished' );
								}
							}
						} );
						break;
					case 'unpublishpost':

						if ( !ids.length || !$( this ).attr( 'rel' ) ) {
							return;
						}

						hideForumModMenu();
						self.executeForumModAction( 'publishpost', {
							mode: 0,
							postid: ids
						}, function ( ajaxdata )
						{
							resetCheckBoxes();

							if ( responseIsOk( ajaxdata ) ) {
								for ( var i = 0; i < ids.length; ++i ) {
									$( '#post_' + ids[i] ).addClass( 'unpublished' );
								}
							}
						} );
						break;

					// ------------ Thread Publishing
					case 'publish':
						hideForumModMenu();
						self.executeForumModAction( 'publishthread', {
							postid: ids,
							mode: 1
						}, function ( ajaxdata )
						{
							resetCheckBoxes();

							if ( responseIsOk( ajaxdata ) ) {

								$( '#thread-posts,#thread-posts-header' ).removeClass( 'unpublished' );

							}
						} );
						break;
					case 'unpublish':
						hideForumModMenu();
						self.executeForumModAction( 'publishthread', {
							mode: 0,
							postid: ids
						}, function ( ajaxdata )
						{
							resetCheckBoxes();

							if ( responseIsOk( ajaxdata ) ) {
								$( '#thread-posts,#thread-posts-header' ).addClass( 'unpublished' );

							}
						} );
						break;

					case 'pin':
						hideForumModMenu();
						self.executeForumModAction( 'pin', {
							mode: 1
						}, function ( ajaxdata )
						{
							resetCheckBoxes();

							if ( responseIsOk( ajaxdata ) ) {
								$( '#thread-posts-header' ).find( '.thread-pinned' ).remove();
								$( '<span class="thread-pinned">Pinned</span>' ).insertBefore( $( '#thread-posts-header' ).find( 'span.thread-label' ) );
							}
						} );
						break;
					case 'unpin':
						hideForumModMenu();
						self.executeForumModAction( 'pin', {
							mode: 0
						}, function ( ajaxdata )
						{
							resetCheckBoxes();

							if ( responseIsOk( ajaxdata ) ) {
								$( '#thread-posts-header' ).find( '.thread-pinned' ).remove();
							}
						} );
						break;
					case 'delete':

						if ( !self.threadID || !$( this ).attr( 'rel' ) ) {
							return;
						}

						self.executeForumModAction( 'deletethread', {
							threadid: self.threadID
						}, function ( ajaxdata )
						{
                            if ( responseIsOk( ajaxdata ) ) {
                                if (self.forumID) {
                                    document.location.href = '/forum/' + self.forumID;
                                }
                                else {
                                    $('#thread-posts').empty().append( '<div><h1>Thema wurde gelöscht</h1></div>' );

                                }
                            }
						} );
						break;

				}

			} );

			return;

			$( '#mod-menu' ).appendTo( 'body' );
			$( document ).click( function ( e )
			{
				if ( $( '#mod-menu' ).is( ':visible' ) ) {
					if ( !$( e.target ).is( '#mod-menu' ) && !$( e.target ).parents( '#mod-menu' ).length ) {
						$( '#mod-menu' ).fadeOut( 300 );
					}
				}
			} );

			$( '#moderator-button' ).click( function ( e )
			{
				e.stopPropagation();

				if ( !$( '#mod-menu' ).is( ':visible' ) ) {
					$( '#mod-menu' ).css( {left: $( this ).offset().left + $( this ).outerWidth( true ) - $( '#mod-menu' ).outerWidth( true ),
						top: $( this ).offset().top + $( this ).outerHeight( true ) } ).fadeIn( 300 );
				}
				else {
					$( '#mod-menu' ).fadeOut( 300 );
				}
			} );

			$( '#mod-menu' ).find( 'li' ).each( function ()
			{
				if ( $( this ).find( 'a' ).length == 1 ) {
					var href = $( this ).find( 'a' ).attr( 'href' );
					if ( href ) {
						$( this ).find( 'a' ).attr( 'href', 'javascript:void(0)' );
						$( this ).attr( 'rel', href ).on( 'click', function ( e )
						{
							var href = $( this ).attr( 'rel' );

							$( '#mod-menu' ).fadeOut( 300 );

							$.get( href, function ( data )
							{
								if ( responseIsOk( data ) ) {
									if ( data.msg ) {
										if ( href.match( /deletethread/i ) ) {
											document.location.href = 'forum/' + self.forumID;
										}
										else {
											if ( href.match( /deletepost/i ) ) {
												window.location.reload( true )
												//document.location.href = document.location.href;
											}
											else if ( href.match( /publish/i ) ) {
												if ( href.match( /post/i ) ) {
													window.location.reload( true )
													//document.location.href = document.location.href;
												}
												else {
													//document.location.href = document.location.href;
													window.location.reload( true )
												}
											}
											else {
												window.location.reload( true )
												//document.location.href = document.location.href;
											}
										}
									}
								}
							} );

						} );
					}
				}
			} );
		},
		setModMessage: function ()
		{
			var self = this, location = document.location.href;

			if ( this.isMod && location.match( /mod=1/ ) ) {
				var msgBox = null;

				if ( location.match( /postpublish=1/ ) ) {
					msgBox = $( '<div class="mod-message"><div class="mod-message-inner">Beitrag wurde veröffentlicht</div></div>' );
					//$('body').append('<div class="mod-message"><div class="mod-message-inner">Beitrag wurde veröffentlicht</div></div>');
				}
				$( 'body' ).append( msgBox );
				msgBox.css( {visibility: 'visible', top: $( '#container' ).offset().top + 10, left: ($( document ).width() / 2) - (msgBox.width() / 2)} );

				$( 'div.mod-message' ).delay( 3000 ).animate( {height: 0, width: 0, left: '+=' + (msgBox.width() / 2), opacity: '0'}, 300, function ()
				{
					$( this ).remove();
				} );

			}
		},
		setUploadSettings: function ( opts )
		{
			this.uploadSetting = $.extend( this.uploadSetting, opts );
			return this;
		},
		setThreadID: function ( threadID )
		{
			this.threadID = threadID;
			return this;
		},
		setPostID: function ( postID )
		{
			this.postID = postID;
			return this;
		},
		setForumID: function ( forumID )
		{
			if ( forumID > 0 ) {
				this.forumID = forumID;
			}
			return this;
		},
		bindForumEvents: function ()
		{

		},
		bindPostForm: function ()
		{
			var div, posturl = null, backloaction;

			if ( this.mode == 'newthread' ) {
				backloaction = 'plugin/forum/thread/{threadid}/{alias}.{suffix}#post-{postid}'
				posturl = 'plugin/forum/newthread/' + this.forumID;
			}
			else if ( this.mode == 'replythread' ) {
				posturl = 'plugin/forum/replythread/' + this.threadID;
				backloaction = 'plugin/forum/thread/' + this.threadID + '/{alias}.{suffix}#post-{postid}'
			}
			else {
				return;
			}

			if ( !$( '#bbform' ).find( 'textarea.bbcodeCommentTextarea' ).syncComment() ) {
				return;
			}

			if ( !$( '#form-message' ).length ) {
				div = $( '<div id="form-message" class="validation"></div>' )
				div.insertBefore( $( '#bbform' ) );
				div.hide();
			}
			else {
				div = $( '#form-message' );
				div.insertBefore( $( '#bbform' ) );
				div.hide();
			}

			$( '#bbform' ).unbind().registerFormFE( {
				exiturl: '',
				save: function ( exit )
				{

					$( '#bbform' ).find( 'textarea.wysibb-texarea' ).sync();

					var _self = this;
					div.hide().empty();
					var postData = $( '#bbform' ).serialize();

					$.post( posturl, postData, function ( data )
					{

						if ( responseIsOk( data ) ) {
							var span = $( '<span/>' ).append( data.msg );

							$( '#bbform' ).hide();
							div.removeClass( 'error' ).addClass( 'success' );
							div.append( span ).show();

							$( span ).effect( 'pulsate', {
								times: 3,
								easing: 'easeInOutBounce'
							}, 300 );

							backloaction = backloaction.replace( '{threadid}', data.threadid ).replace( '{postid}', data.postid );
							backloaction = backloaction.replace( '{alias}', data.alias ).replace( '{suffix}', data.suffix );

							setTimeout( function ()
							{
								document.location.href = backloaction;
							}, 2000 );
						}
						else {
							_self.error( data );
						}

					}, 'json' );
				}
			} );

			if ( this.uploadSetting.token && this.uploadSetting.maxuploadsize > 0 ) {
				this.bindUpload();
			}
		},
		bindUpload: function ()
		{
			$( '#fsize' ).text( format_size( uploadSetting.maxuploadsize * 1024 ) + 'MB' );
			$( '#upload-container' ).empty();

			var exts = uploadSetting.uploadextensions.split( ',' );
			var clean = [];

			for ( var x = 0; x < exts.length; ++x ) {
				var str = exts[x];
				str = str.replace( /\s*\t*/, '' );
				str = str.replace( /^\*\./, '.' ); // remove *.png to .png
				if ( str ) {
					clean.push( '*' + str );
				}
			}

			var swfu = upload = null;
			var upload = new DCMS_MultiUploadControl( {
				'control': "upload-container",
				'adm': "",
				'action': "",
				file_upload_limit: "0",
				file_queue_limit: "1",
				'url': "plugin/forum/upload",
				file_type_mask: (clean.length ? clean.join( ', ' ) : '*.*'),
				file_types: "*.*",
				'uiqtoken': uploadSetting.token,
				'file_type_text': "Alle Dateien",
				max_file_size: format_size( uploadSetting.maxuploadsize * 1024 ),
				customSettings: {
					postFunction: function ( data )
					{
						var del = $( '<span/>' ).addClass( 'delete-upload-file' );
						var div = $( '<div/>' ).addClass( 'uploadfile' ).append( data.filename );
						div.append( $( '<input/>' ).attr( {
							'name': 'uploadfiles[]',
							'type': 'hidden'
						} ).val( data.attachmentid ) );

						del.click( function ( e )
						{
							$.get( 'forum/upload/remove/' + data.attachmentid, {}, function ( dat )
							{
								if ( responseIsOk( dat ) ) {
									del.unbind( 'click' );
									div.remove();
								}
							} );
						} );
						div.append( del );
						$( '#upload-files' ).append( div );
					}
				}
			} );
		},
		bindThreadEvents: function ()
		{
			if ( this.threadID > 0 ) {
				var self = this;

				$( '.postrow' ).find( 'a.like' ).click( function ( e )
				{
					var postid = $( this ).attr( 'rel' );
					postid = postid.replace( /^postid([-_])([\d]+)/i, '$2' );

					self._like( 'like', postid, e );
				} );

				$( '.postrow' ).find( 'a.dislike' ).click( function ( e )
				{
					var postid = $( this ).attr( 'rel' );
					postid = postid.replace( /^postid([-_])([\d]+)/i, '$2' );

					self._like( 'dislike', postid, e );
				} );
			}
		},
		// ----------------------------------------------------------
		// All private functions
		// ----------------------------------------------------------

		/**
		 * Private
		 * @param {string} _do
		 * @param {integer} postid
		 * @returns {undefined}
		 */
		_like: function ( _do, postid, e )
		{

			$.post( 'index.php', {cp: 'forum', action: 'thread', threadid: this.threadID, postid: postid, 'do': _do}, function ( data )
			{
				if ( responseIsOk( data ) ) {
					if ( data.update ) {
						if ( _do === 'like' ) {
							var s = parseInt( $( e.target ).find( '.counter span' ).text(), 0 );
							$( e.target ).find( '.counter span' ).text( s + 1 );
						}
						else if ( _do === 'dislike' ) {
							var s = parseInt( $( e.target ).find( '.counter span' ).text(), 0 );
							$( e.target ).find( '.counter span' ).text( s + 1 );
						}
					}
				}
			} );
		},

		setDialogPos: function ( dialog, pos )
		{
			if ( pos == 'center' ) {
				dialog.css( {
					left: ($( window ).width() / 2) - (dialog.width() / 2),
					top: ($( window ).height() / 2) - (dialog.height() / 2)
				} );
			}
		},
		dialog: function ( opt )
		{
			var container = $( '<div></div>' ).addClass( 'forum-dialog' );
			var containerInner = $( '<div></div>' ).addClass( 'forum-dialog-inner' );
			var header = $( '<h3></h3>' ).addClass( 'forum-dialog-header' ).append( opt.title );
			var contentContainer = $( '<div></div>' ).addClass( 'forum-dialog-content' ).append( opt.content );
			var footer = $( '<div></div>' ).addClass( 'forum-dialog-footer' );

			container.append( containerInner.append( header ).append( contentContainer ).append( footer ) );

			if ( opt.width > 0 ) {
				contentContainer.width( opt.width );
			}

			if ( opt.height > 0 ) {
				contentContainer.height( opt.height );
			}

			if ( opt.draggable ) {

			}

			if ( opt.mode == 'modal' ) {
				container.css( {position: 'fixed'} );
			}

			if ( opt.pos == 'center' ) {
				container.css( {
					left: $( window ).width() / 2 - (container.width() / 2),
					top: $( window ).height() / 2 - (container.height() / 2)
				} );
			}
			else if ( typeof opt.pos == 'object' ) {
				container.css( {
					left: opt.pos.left,
					top: opt.pos.top
				} );
			}

			return container;
		},

		initBBCodeEditor: function ( objectname, opt )
		{

			CURLANG = WBBLANG['de'] || WBBLANG['en'] || CURLANG;
			var smilies = [];

			if ( opt.smilies && opt.smiliepath ) {
				for ( var x = 0; x < opt.smilies.length; ++x ) {
					opt.smilies[x].img = '<img src="' + opt.smiliepath.replace( /\/$/, '' ) + '/' + opt.smilies[x].imgpath + '" class="sm">';
				}

				smilies = opt.smilies;
			}


            var posthash = $( objectname).parents('form:first').find('input[name=posthash]').val();

			$( objectname ).wysibb( {
                imgupload: true,
                img_uploadurl: "/index.php",
                img_maxwidth: 1400,
                img_maxheight: 1200,
                imgupload_postdata: {
                    cp: 'plugin',
                    posthash: (typeof posthash == 'string' ? posthash : ''),
                    plugin: 'forum',
                    action: 'upload',
                    mode: 'wbb'
                },
                bodyClass: "content",
                buttons: 'undo,redo,|,bold,italic,underline,strike,sup,sub,|,justifyleft,justifycenter,justifyright,|,fontcolor,fontsize,fontfamily,|,img,video,link,map,|,quote,bullist,numlist,|,quote,offtop,|,code,php,css,sql,|,img,linkfontsize,fontcolor,smilebox,|,removeFormat',
                autoresize: false,
                minheight: 300,
                resize_maxheight: 420,
				smileList: smilies
			} );
		},

		initUpload: function ( opts )
		{

			if ( typeof opts.uploadid != 'string' || opts.uploadid === '' ) {
				console.log( "SWFUpload: INVALID ID" );
				return false;
			}

			this.uploadid = opts.uploadid;

			this.boxes = [];
			this.uploaders = [];

			var exts = (typeof opts.allowedExtensions == 'string' && opts.allowedExtensions != '' ? opts.allowedExtensions : '*.*' );
			exts = exts.replace( /\s/g, '' );
			opts.allowedExtensions = exts.split( ',' );

			var uploader = false;

			if ( jimAuld.utils.flashsniffer.meetsMinVersion( 9 ) && typeof SWFUpload != 'undefined' ) {
				opts.useFlash = true;

				$( '#upload-container' ).empty();

				uploader = new Forum.SWFUpload();
			}
			else {
				opts.useFlash = false;

				$( '#upload-container' ).empty().append(
					$( '<input type="file" class="static-upload" name="uploadFile"/>' )
				);

			}

			if ( uploader ) {
				// Show the button and info
				$( 'add_files_' + this.uploadid ).show();
				$( 'space_info_' + this.uploadid ).show();

				this.uploaders[ this.uploadid ] = uploader;
				uploader.init( opts );
			}

		},


		bindLightboxes: function() {
			var index = 0;


			$('li.postrow' ).each(function() {

				// group by post
				$(this).find('a[rel=forumlightbox]' ).each(function(){
					$(this).attr('rel', $(this ).attr('rel') +'-' + index );
				});

				$(this).find('a[rel=forumlightbox-'+index+ ']').fancybox({
					'padding': 10,
					'transitionIn': 'elastic',
					'transitionOut': 'elastic',
					'easingIn': 'swing',
					'easingOut': 'swing',
					'speedIn': 700,
					'speedOut': 500,
					'titlePosition': 'over',
					'titleShow': true,
					'type': 'image',
					errorMessage: 'Sie müssen angemeldet sein um das Bild vergrößern zu können.',
					onComplete: function (currentArray, currentIndex, currentOpts) {
						$("#fancybox-inner").unbind('hover').hover(function () {
							$("#fancybox-title-over").slideUp(300);
						}, function () {
							$("#fancybox-title-over").slideDown(300);
						});
					},
					'titleFormat': function (title, currentArray, currentIndex, currentOpts) {
						var descriptionLayout = $(currentArray[currentIndex] ).find('img' ).attr('title');
						return '<span id="fancybox-title-over">' + (descriptionLayout ? '<strong>Beitrag: ' + descriptionLayout+'</strong><br/>' : '')  + 'Bild ' + (currentIndex + 1) + ' von ' + currentArray.length + '</span>';

					}
				});

				index++;
			});
		}

	};
}

Forum.SWFUpload = function ( opts )
{
	this.obj = null;
	this.opt = {};
	this.uploadOpts = {};
	this.uploadid = null;
	this.boxes = [];

	this.startedUploading = function ( handler )
	{

	};

	this.finishedUploading = function ( attachid, fileindex, file )
	{
		if ( !Forum.uploaders[ attachid ] ) { return; }
		if ( !Forum.uploaders[ attachid ].boxes[ fileindex ] ) { return; }

		var self = this, row = Forum.uploaders[ attachid ].boxes[ fileindex ];

		if ( $( '#' + row ).length == 1 ) {

			var link = $( '#' + row ).find( '.add_to_post' );

			if ( $( link ) ) {
				$( link ).attr( 'fileindex', fileindex ).attr( 'attachid', attachid );
				$( link ).bind( 'click', this.insertIntoPost );
			}


			link = $( '#' + row ).find( '.delete' );
			if ( $( link ) ) {
				$( link ).attr( 'fileindex', fileindex ).attr( 'attachid', attachid );
				$( link ).bind( 'click', function(e) {
					self.deleteFile(e);
				});
			}



		}
	};

	this.insertIntoPost = function (e) {
		e.preventDefault();

		var self = this, liID = Forum.uploaders[ $(e.target ).attr('attachid') ].boxes[ $(e.target ).attr('fileindex') ];
		if ( $( '#' + liID ).length == 1 ) {
			var data = $( '#' + liID ).data('filedata');

			if (data) {
				console.log(data);
			}
		}
	};

	this.deleteFile = function(e) {
		e.preventDefault();
		var index = $(e.target ).attr('fileindex') ;
		var self = this, liID = Forum.uploaders[ $(e.target ).attr('attachid') ].boxes[ index ];

		if ( $( '#' + liID ).length == 1 ) {
			var data = $( '#' + liID ).data('filedata');
			if (data) {
				console.log(data);


				if (data.id > 0) {

					this.opt.post_params.id = data.id;
					this.opt.post_params.do = 'remove';

					$.post('index.php', this.opt.post_params, function(d) {
						if (responseIsOk(d)) {
							self._updateInfo( index, d.msg );
							$( '#' + liID ).delay(1000).fadeOut(500, function() {
								$(this).remove();
							});
						}
						else {
							console.log(d);
						}
					});
				}


			}
		}
	};

	this.init = function ( opts )
	{
		var tmp = [];
		this.uploadid = opts.uploadid;

		if ( opts.allowedExtensions[0] == '*.*' || !opts.allowedExtensions.length ) {
			tmp.push( '*.*' );
		}
		else {
			for ( var i = 0; i < opts.allowedExtensions.length; ++i ) {
				if ( opts.allowedExtensions[i] != '' ) {

					var str = opts.allowedExtensions[i].replace( /^(\*\.)([a-z0-9\.]+?)$/ig, '$2' )
					if ( str != '' ) {
						tmp.push( '*.' + str );
					}
				}
			}
			console.log( tmp );

			if ( tmp.length == 0 ) {
				tmp.push( '*.*' );
			}
		}

		if ( !opts.url.match( /^https?:\/\// ) && opts.url != '' ) {
			opts.url = systemUrl + '/' + opts.url;
		}

		this.opt = {
			upload_url: opts.url,
			file_post_name: 'uploadFile',
			file_types: tmp.join( ';' ),
			file_types_description: 'Datei/-en auswählen',
			file_size_limit: format_size( opts.maxUploadSize ),
			file_upload_limit: 0,
			file_queue_limit: 10,
			flash_color: '#FFFFFF',
			custom_settings: {

			},
			post_params: {
				'swfupload_sid': session_id
			}
		};

		if ( typeof opts.post_params == 'object' ) {
			this.opt.post_params = $.extend( this.opt.post_params, opts.post_params );
		}

		this.uploadOpts = $.extend( {}, this.opt, opts );

		// Set up SWFU
		try {
			var swfu;

			$( '#add_files_' + this.uploadid ).show();

			var settings = {
				upload_url: this.opt.upload_url,
				flash_url: this.opt.swfupload_swf || systemUrl + '/public/html/js/swfupload/swfupload.swf',
				file_post_name: this.opt.file_post_name,
				file_types: this.opt.file_types,
				file_types_description: this.opt.file_types_description,
				file_size_limit: this.opt.file_size_limit,
				file_upload_limit: this.opt.file_upload_limit,
				file_queue_limit: this.opt.file_queue_limit,
				custom_settings: this.opt.custom_settings,
				post_params: this.opt.post_params,
				debug: true,

				// ---- BUTTON SETTINGS ----
				button_placeholder_id: 'buttonPlaceholder',
				button_width: $( '#add_files_' + this.uploadid ).outerWidth(),
				button_height: $( '#add_files_' + this.uploadid ).outerHeight(),
				button_window_mode: SWFUpload.WINDOW_MODE.TRANSPARENT,
				button_cursor: SWFUpload.CURSOR.HAND,

				// ---- EVENTS ----
				upload_error_handler: this._uploadError.bind( this ),
				upload_start_handler: this._uploadStart.bind( this ),
				upload_success_handler: this._uploadSuccess.bind( this ),
				upload_complete_handler: this._uploadComplete.bind( this ),
				upload_progress_handler: this._uploadProgress.bind( this ),
				file_dialog_complete_handler: this._fileDialogComplete.bind( this ),
				file_queue_error_handler: this._fileQueueError.bind( this ),
				queue_complete_handler: this._queueComplete.bind( this ),
				file_queued_handler: this._fileQueued.bind( this )
			};

			// $( '#add_files_' + this.uploadid ).hide();

			swfu = new SWFUpload( settings );
			this.obj = swfu;
			/*
			 // Now we have to get existing files
			 var getExisting = ipb.vars['base_url'] + "app=core&module=attach&section=attach&do=attach_upload_show&attach_rel_module=" +
			 options['attach_rel_module'] + "&attach_rel_id=" + options['attach_rel_id'] + "&attach_post_key=" +
			 options['attach_post_key'] + "&forum_id=" + options['forum_id'] + "&attach_id=" + id + '&secure_key=' + ipb.vars['secure_hash'] + '&fetch_all=1';

			 // Send request to get the uploads
			 new Ajax.Request( getExisting,
			 {
			 method: 'get',
			 evalJSON: 'force',
			 hideLoader: true,
			 onSuccess: function ( t )
			 {
			 if ( Object.isUndefined( t.responseJSON ) ) {
			 alert( ipb.lang['action_failed'] );
			 return;
			 }

			 if ( t.responseJSON.current_items ) {
			 ipb.attach._buildBoxes( t.responseJSON.current_items );
			 }
			 }
			 } );
			 */
			this.obj.onmouseover = $( '#SWFUpload_0' ).focus();
			//		$( 'SWFUpload_0' ).writeAttribute( "tabindex", "-1" );
			console.log( "SWFUpload: (ID " + this.uploadid + ") Created uploader" );
			return true;
		}
		catch ( e ) {
			console.log( "SWFUpload: (ID " + this.uploadid + ") " + e );
			return false;
		}
	};

	/**
	 * Updates the info string for an upload
	 *
	 * @param    {object}    file        The file object from SWFU
	 * @param    {string}    msg            The message to set
	 */
	this._updateInfo = function ( index, msg )
	{
		$( '#' + this.boxes[ index ] ).find( '.info' ).html( msg );
	};
	/**
	 * Builds the list row for each upload
	 *
	 * @param    {object}    file    The file object passed from SWFU
	 */
	this._buildBox = function ( file )
	{
		temp = this.uploadOpts.template
			.replace( /\[id\]/, this.uploadid + '_' + file.index )
			.replace( /\[name\]/, file.name );

		this.boxes[ file.index ] = 'ali_' + this.uploadid + '_' + file.index;

		$( this.uploadOpts.attachmentContainer ).show().append( temp );

		//new Effect.Appear( $( this.boxes[ file.index ] ), { duration: 0.3 } );
		$( '#' + this.boxes[ file.index ] ).fadeIn( 250 );
		this._updateInfo( file.index, format_size( file.size ) );
	};
	/**
	 * Sets a CSS class on the box depending on status
	 *
	 * @param    {object}    file        The file object from SWFU
	 * @param    {string}    type        Status to set
	 */
	this._setStatus = function ( index, type )
	{
		// Remove old statuses
		if ( $( '#' + this.boxes[ index] ).hasClass( 'complete' ) ) {
			$( '#' + this.boxes[index] ).removeClass( 'complete' );
		}
		if ( $( '#' + this.boxes[ index] ).hasClass( 'in_progress' ) ) {
			$( '#' + this.boxes[ index] ).removeClass( 'in_progress' );
		}
		if ( $( '#' + this.boxes[index ] ).hasClass( 'error' ) ) {
			$( '#' + this.boxes[ index] ).removeClass( 'error' );
		}

		// add new state
		$( '#' + this.boxes[ index ] ).addClass( type );
	};

	this._uploadError = function ( file, errorCode, message )
	{
		var msg;

		switch ( errorCode ) {
			case SWFUpload.UPLOAD_ERROR.HTTP_ERROR:
				msg = 'Fehler ' + message;
				break;
			case SWFUpload.UPLOAD_ERROR.UPLOAD_FAILED:
				msg = message;
				break;
			case SWFUpload.UPLOAD_ERROR.IO_ERROR:
				msg = 'Fehler ' + " IO";
				break;
			case SWFUpload.UPLOAD_ERROR.SECURITY_ERROR:
				msg = 'error_security';
				break;
			case SWFUpload.UPLOAD_ERROR.UPLOAD_LIMIT_EXCEEDED:
				msg = 'upload_limit_hit';
				break;
			case SWFUpload.UPLOAD_ERROR.FILE_VALIDATION_FAILED:
				msg = 'invalid_mime_type';
				break;
			default:
				msg = 'Fehler' + ": " + errorCode;
				break;
		}

		this._setStatus( file.index, 'error' );
		this._updateInfo( file.index, 'Übersprungen' + " (" + msg + ")" );

		console.log( "SWFUpload: (ID " + this.uploadid + ", uploadError) " + errorCode + ": " + message );
		return false;
	};
	this._uploadStart = function ()
	{
		this.startedUploading( this.uploadid );
		console.log( "SWFUpload: (ID " + this.uploadid + ", uploadStart) " );
	};
	this._uploadSuccess = function ( file, serverData )
	{

		if ( typeof serverData == 'string' && serverData != '' ) {
			serverData = jQuery.parseJSON(serverData);
		}

		if ( typeof serverData != 'object' ) {
			this._setStatus( file.index, 'error' );
			this._updateInfo( file.index, 'Fehler 1 ' + (typeof serverData) );
			return;
		}
		else {
			if ( responseIsOk( serverData ) ) {
				this._setStatus( file.index, 'complete' );


				if ( serverData.msg ) { this._updateInfo( file.index, serverData.msg ); }
				else {
					this._updateInfo( file.index, 'Upload abgeschlossen' );
				}
				$( '#' + this.boxes[ file.index ] ).data('filedata',serverData ).find( '.progress_bar' ).fadeOut( 600 );
				$( '#' + this.boxes[ file.index ] ).find( '.links' ).fadeIn( 600 );
			}
			else {
				this._setStatus( file.index, 'error' );
				if (serverData.msg) { this._updateInfo( file.index, serverData.msg ); }
				else {
					this._updateInfo( file.index, 'Fehler 2' );
				}
				$( '#' + this.boxes[ file.index ] ).find( '.progress_bar,.links' ).hide();
			}
		}
	};
	this._uploadComplete = function ( file )
	{

		progress_bar = $( '#' + this.boxes[ file.index ] ).find( '.progress_bar span' );
		progress_bar.css( {width: "100%"} ).text( "100%" );

		//this._setStatus( file.index, 'complete' );

		this.finishedUploading( this.uploadid, file.index, file );

		console.log( "SWFUpload: (ID " + this.uploadid + ", uploadComplete)" );
	};
	this._uploadProgress = function ( file, bytesLoaded, bytesTotal )
	{
		var percent = Math.ceil( (bytesLoaded / bytesTotal) * 100 );

		progress_bar = $( '#' + this.boxes[ file.index ] ).find( '.progress_bar' ).find( 'span:first' );
		progress_bar.css( {width: percent + "%"} ); //.text( percent + "%" );

		this._setStatus( file.index, 'in_progress' );
		this._updateInfo( file.index, 'Uploade [use] von [total]'.replace( '[use]', format_size( bytesLoaded ) ).replace( '[total]', format_size( bytesTotal ) ) );

		console.log( "SWFUpload: (ID " + this.uploadid + ", uploadProgress)" );
	};
	this._fileDialogComplete = function ( number, queued )
	{
		console.log( "SWFUpload: (ID " + this.uploadid + ", fileDialogComplete) Number: " + number + ", Queued: " + queued );
		this.obj.startUpload();
	};
	this._fileQueueError = function ( file, errorCode, message )
	{
		var msg;

		try {
			if ( errorCode === SWFUpload.QUEUE_ERROR.QUEUE_LIMIT_EXCEEDED ) {
				alert( 'upload_queue: ' + message );
				return false;
			}

			switch ( errorCode ) {
				case SWFUpload.QUEUE_ERROR.FILE_EXCEEDS_SIZE_LIMIT:
					msg = 'Datei ist zu groß';
					break;
				case SWFUpload.QUEUE_ERROR.ZERO_BYTE_FILE:
					msg = 'Keine Datei ausgewählt';
					break;
				case SWFUpload.QUEUE_ERROR.INVALID_FILETYPE:
					msg = 'Fehlerhafter Dateityp';
					break;
				default:
					if ( file !== null ) {
						msg = 'Upload Fehlgeschlagen' + " " + errorCode;
					}
					break;
			}

			// Have to manually build box
			this._buildBox( file );
			this._setStatus( file.index, 'error' );
			this._updateInfo( file.index, 'Übersprungen' + " (" + msg + ")" );
			$( '#' + this.boxes[ file.index ] ).find( '.progress_bar' ).hide();
			$( '#' + this.boxes[ file.index ] ).find( '.links' ).hide();

			console.log( "SWFUpload: (ID " + this.uploadid + ", fileQueueError) " + errorCode + ": " + message );
		}
		catch ( err ) {
			console.log( "SWFUpload: (ID " + this.uploadid + ", fileQueueError) " + errorCode + ": " + message );
		}
	};

	this._queueComplete = function ( numFiles )
	{
		console.log( "SWFUpload: (ID " + this.uploadid + ", " + numFiles + " finished uploading" );
	};
	this._fileQueued = function ( file )
	{
		this._buildBox( file );
		$( '#' + this.boxes[ file.index ] ).addClass( 'in_progress' );
		this._updateInfo( file.index, 'Ausstehend' );
	};

};

/*
 Copyright (c) 2007, James Auldridge
 All rights reserved.
 Code licensed under the BSD License:
 http://www.jaaulde.com/license.txt

 Version 1.0

 Change Log:
 * 09 JAN 07 - Version 1.0 written

 */
//Preparing namespace
var jimAuld = window.jimAuld || {};
jimAuld.utils = jimAuld.utils || {};
jimAuld.utils.flashsniffer = {
	lastMajorRelease: 10,
	installed: false,
	version: null,
	detect: function ()
	{
		var fp, fpd, fAX;
		if ( navigator.plugins && navigator.plugins.length ) {
			fp = navigator.plugins["Shockwave Flash"];
			if ( fp ) {
				jimAuld.utils.flashsniffer.installed = true;
				if ( fp.description ) {
					fpd = fp.description;
					jimAuld.utils.flashsniffer.version = fpd.substr( fpd.indexOf( '.' ) - 2, 2 ).replace( /^\s*/g, '' ).replace( /\s*$/g, '' );
					console.log( jimAuld.utils.flashsniffer.version );
				}
			}
			else {
				jimAuld.utils.flashsniffer.installed = false;
			}
			if ( navigator.plugins["Shockwave Flash 2.0"] ) {
				jimAuld.utils.flashsniffer.installed = true;
				jimAuld.utils.flashsniffer.version = 2;
			}
		}
		else if ( navigator.mimeTypes && navigator.mimeTypes.length ) {
			fp = navigator.mimeTypes['application/x-shockwave-flash'];
			if ( fp && fp.enabledPlugin ) {
				jimAuld.utils.flashsniffer.installed = true;
			}
			else {
				jimAuld.utils.flashsniffer.installed = false;
			}
		}
		else {
			for ( var i = jimAuld.utils.flashsniffer.lastMajorRelease; i >= 2; i-- ) {
				try {
					fAX = new ActiveXObject( "ShockwaveFlash.ShockwaveFlash." + i );
					jimAuld.utils.flashsniffer.installed = true;
					jimAuld.utils.flashsniffer.version = i;
					break;
				}
				catch ( e ) {
				}
			}
			if ( jimAuld.utils.flashsniffer.installed == null ) {
				try {
					fAX = new ActiveXObject( "ShockwaveFlash.ShockwaveFlash" );
					jimAuld.utils.flashsniffer.installed = true;
					jimAuld.utils.flashsniffer.version = 2;
				}
				catch ( e ) {
				}
			}
			if ( jimAuld.utils.flashsniffer.installed == null ) {
				jimAuld.utils.flashsniffer.installed = false;
			}
			fAX = null;
		}

	},
	isVersion: function ( exactVersion )
	{
		return (jimAuld.utils.flashsniffer.version != null && jimAuld.utils.flashsniffer.version == exactVersion);
	},
	isLatestVersion: function ()
	{
		return (jimAuld.utils.flashsniffer.version != null && jimAuld.utils.flashsniffer.version == jimAuld.utils.flashsniffer.lastMajorRelease);
	},
	meetsMinVersion: function ( minVersion )
	{
		return (jimAuld.utils.flashsniffer.version != null && jimAuld.utils.flashsniffer.version >= minVersion);
	}
};
jimAuld.utils.flashsniffer.detect();

$( document ).ready( function ()
{
	setTimeout( function ()
	{
		Forum.init();
	}, 50 );   // little timeout
} );