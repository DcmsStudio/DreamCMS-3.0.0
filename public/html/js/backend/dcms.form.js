var tinymceT;

function updateTextareaFields( form )
{

	/*
	 jQuery(tinymce.get()).each(function(i, el){
	 if ( $('#'+ el.editorId ).length ) {
	 var content = el.getContent();
	 $('#'+ el.editorId).val(content);
	 $('#'+ el.editorId).get(0).value = content;
	 }
	 });
	 */
	if ( typeof tinymce != 'undefined' ) {
		for ( var i = 0; i < tinymce.editors.length; i++ ) {
			if ( tinymce.editors[i].id ) {
				var val = tinymce.editors[i].getContent();
				val = val.replace( /<p>\s*<\/p>/g, '' );

				$( '#' + tinymce.editors[i].id.replace( 'inline-', '' ) ).val( val );
			}
		}
	}
}

var TinyCallback = {
	cleanXHTML: function ( f, d )
	{
		var e = "";
		var b = d.match( /<a[^>]*>/gi );
		if ( b != null ) {
			for ( var c = 0; c < b.length; c++ ) {
				e = b[c].replace( /target="_blank"/gi, 'onclick="window.open(this.href); return false;"' );
				d = d.replace( b[c], e )
			}
		}
		return d.replace( /<br>/, "<br/>" )
	},
	cleanHTML: function ( b, a )
	{
		a = a.replace( /<br \/>/, "<br>" );
		a = a.replace( /^\s*/ig, "" );
		return a
	},
	getScrollOffset: function ( a )
	{
		// tinymce.dom.Event.add((tinymce.isGecko?a.getDoc():a.getWin()),"focus",function(b){Backend.getScrollOffset()})
	},
	handleEventCallback: function ( e )
	{

		if ( typeof Config.get( 'TinyMCE_HandleEventCallback' ) === 'function' ) {
			clearTimeout( tinymceT );
			tinymceT = setTimeout( function ()
			{
				Config.get( 'TinyMCE_HandleEventCallback' )( e );
			}, 250 );
		}

	},
	nodeChangeHandler: function ( editor_id, node, undo_index, undo_levels, visual_aid, any_selection )
	{

	},
	onChangeHandler: function ( inst )
	{
		/*
		 if (inst.isDirty())
		 {
		 Form.setDirty(false, $('#' + inst.editorId).parents('form:first'));
		 }
		 else {
		 Form.resetDirty($('#' + inst.editorId).parents('form:first'));
		 }
		 */
		var triggerFunction = Config.get( 'onTinyMceChangeHandler', false );

		if ( typeof triggerFunction === 'function' ) {
			triggerFunction( inst );
		}

	}
};

var Form = (function ()
{
	return {
		allowedButtonClasses: ['save', 'save_exit', 'cancel', 'reset', 'draft', 'run', 'run_exit'],
		windowID: null,
		formID: null,
		window: null,
		form: null,
		config: {},
		defaults: {
			url: 'admin.php',
			exiturl: '',
			contentTable: '',
			useContentTags: false,
			focus_first: false,
			isDirty: false,
			autosave: false, // use numbers for save delay
			onAfterSubmit: false, // external event
			onBeforeSerialize: false,
			onBeforeSubmit: false, // external event
			runAfterSubmit: false, // external event
			// prepare other form data
			onBeforeSend: false, // external event
			onReset: false, //
			contentIdentifierID: 'content-id', // record ID
			identifierType: '',
			baseField: '',
			formid: null,
			rebuildIdentifier: function ( type )
			{
				this.rebuildPageIdentifier( this.config.baseField, type, $( '#' + this.config.contentIdentifierID ).val() );
			},
			error: function ( data )
			{
				if ( data.msg !== '' ) {
					this.setFormStatus( data.msg );
				}
				else {
					this.setFormStatus( cmslang.formsave_error );
				}
			}

		},
		registerForm: function ( formID, options )
		{
            Form._register( formID, options );
		},
		_regTo: null,
		_register: function ( formID, options )
		{
			if ( !formID  ) {
				return;
			}

			var self = this, $_window = (Config.get( 'isSeemode' ) ? $( 'body' ) : $( '#' + Win.windowID ));//Application.getWindow();
            if (!$_window.length && typeof Core.getWindow === 'function') {
                $_window = Core.getWindow();
            }

            if ( $_window && $_window.find( '#' + formID ).hasClass('registred') ) {
                return;
            }

			if ( options.isPopup ) {
				$_window = $( '#' + formID ).parents( '.popup:first' );
			}

			if ( Config.get( 'isSeemode' ) ) {
				$_window.attr( 'id', 'seemode-win' );
			}

			if ( typeof $_window != 'object' || $( $_window ).find( '#' + formID ).length == 0 ) {
				this._regTo = setTimeout( function ()
				{
					self._register( formID, options );
				}, 10 );
				//   console.log([$_window]);
				//   Debug.info('Wait for Form registry...');
			}
			else {
				clearTimeout( this._regTo );

				var options = options || {};

				this.config = $.extend( {}, this.defaults, options );
				this.config.url = this.config.url.replace( Config.get( 'portalurl' ) + '/', '' );
				this.config.url = Config.get( 'portalurl' ) + '/' + this.config.url;
                this.config._formid = formID;


				this.formid = formID;
				this.window = $_window;

				if ( $_window ) {
					if ( $_window.find( '#' + formID ).length == 1 ) {

						this.form = $( '#' + formID, $_window).addClass('registred');
						this.formID = formID;
						var windowID = $_window.attr( 'id' );
                        this.windowID = windowID;

                        // set the form config
						$( this.window ).data( 'formConfig', this.config );
                        $( this.window ).data( 'formID', formID );

                        //
                        this.form.data( 'windowID', windowID );
                        this.form.data( 'formConfig', this.config );

						// add dirty event
                        this.form.find( 'input,select,textarea' ).each( function ()
						{

							var el = $( this );
							if ( (el.is( 'input' ) || el.is( 'textarea' )) && !el.hasClass( 'nodirty' ) ) {
								el.unbind( 'keyup.form' ).bind( 'keyup.form', function ( e )
								{
									self.validation( formID, $( this ) );
								} );
							}

							if ( /*!$(this).parents('#document-metadata').length && */ !el.hasClass( 'nodirty' ) ) {
								el.unbind( 'change.form' ).bind( 'change.form', function ( e )
								{
									//e.preventDefault();


									if ( el.is( 'input' ) || el.is( 'textarea' ) ) {
										self.validation( formID, el );
									}

									self.liveSetDirty( e, $( '#' + formID, $_window ), formID );

								} );
							}
						} );

						if ( this.config.useContentTags ) {
							this.registerContentTags( this.formID );
						}

						this.registerButtonEvents( formID, $_window );


                        // set the form config
                        $_window.data( 'formConfig', this.config );
                        $_window.data( 'formID', formID );

                        //
                        this.form.data( 'windowID', windowID );
                        this.form.data( 'formConfig', this.config );

                        if ( !Desktop.isWindowSkin )
                        {
                            Core.resetDirty( true );
                        }

						//  Debug.log('The Form "' + formID + '" is registred.');
						// this.setDisableButtons($('#'+ formID, $(this.window)), true);

					}
					else {
						Debug.log( 'The Form "' + formID + '" is not in the active Window!' );
						return false;
					}
				}
				else {
					Debug.log( 'The Form "' + formID + '" is not in the active Window!' );
					return false;
				}
			}
		},
        registerAutosave: function(formID) {
            var cfg = $( '#' + formID, $('#'+ Win.windowID ) ).data( 'formConfig' );

            if (cfg)
            {
                if ( cfg.autosave > 0 ) {


                    if ( typeof cfg.contentIdentifierID == 'string' && cfg.contentIdentifierID != '' )
                    {
                        var autosave = new Autosave(formID, Win.windowID, {
                            delay: cfg.autosave,
                            idfieldname: cfg.contentIdentifierID,
                            postid: $( '#' + Win.windowID ).find( '#' + cfg.contentIdentifierID ).val()
                        });

                        cfg.autoSaveInstance = autosave;

                        $( '#' + Win.windowID ).data( 'formConfig', cfg );

                        autosave.start();

                    }
                    else {
                        Debug.log( 'The Form "' + formID + '" could not use autosave!' );
                    }
                }
            }

        },
        destroy: function(formObj, formID)
        {
            var cfg = formObj.data( 'formConfig' );

            if (cfg)
            {
                if ( cfg.autoSaveInstance )
                {
                    cfg.autoSaveInstance.destroy();
                }

                Core.removeShortcutHelp('Alt+R', true);
                Core.removeShortcutHelp('Alt+S', true);
                Core.removeShortcutHelp('Ctrl+Alt+E', true);
                Core.removeShortcutHelp('Alt+C', true);

                formObj.unbind();
            }

            if ( formID )
            {
                $(document).unbind('keydown.form' + formID );
                $( 'input.content-tags', formObj).unbind();
            }
        },

		setContentLockAction: function ( action, formID )
		{
            var cfg = $( '#' + formID, $('#'+ Win.windowID ) ).data( 'formConfig' );

            if (cfg)
            {
                cfg.contentlockaction = action;
                $( '#' + formID, $('#'+ Win.windowID ) ).data( 'formConfig', cfg );
            }
		},

		registerLiveEventsForMetadata: function ( formID, window )
		{

			if ( Config.get( 'isSeemode' ) ) {
				return;
			}

			var realFormID = $( '#documentmetadata' + formID ).data( 'realFormID' ), self = this;

			setTimeout( function ()
			{

				$( '#documentmetadata' + formID ).find( 'input,select,textarea' ).each( function ()
				{
					$( this ).unbind( 'change.metaform' ).on( 'change.metaform', function ( e )
					{

						//e.preventDefault();
						self.liveSetDirty( e, $( '#' + realFormID ), realFormID );

					} );

				} );

				$( '#documentmetadata' + formID ).find( 'label' ).each( function ()
				{
					$( this ).unbind( 'click.metaform' );
					$( this ).bind( 'click.metaform', function ( e )
					{
						e.preventDefault();

						if ( $( this ).find( 'input' ).length === 1 ) {
							var el = $( this ).find( 'input:first' );
							var checked = el.attr( 'checked' );
							//do not allow deselecting of a radio-group
							if ( el.attr( 'type' ) == 'radio' && checked )
								return;

							//$(el).checked = ($(el).get(0).checked ? false : true);
							$( el ).get( 0 ).checked = ($( el ).get( 0 ).checked ? false : true);
							$( el ).prop( "checked", $( el ).get( 0 ).checked );
							$( el ).change();
							//  self.liveSetDirty(e, $('#'+ $('#documentmetadata'+ formID).data('realFormID') )   );
						}
						else if ( $( this ).prev( 'input' ) ) {
							var el = $( this ).find( 'input:first' );
							var checked = el.attr( 'checked' );
							//do not allow deselecting of a radio-group
							if ( el.attr( 'type' ) == 'radio' && checked )
								return;

							//$(el).checked = ($(el).get(0).checked ? false : true);
							$( el ).get( 0 ).checked = ($( el ).get( 0 ).checked ? false : true);
							$( el ).prop( "checked", $( el ).get( 0 ).checked );
							$( el ).change();
							//  self.liveSetDirty(e, $('#'+ $('#documentmetadata'+ formID).data('realFormID') )   );
						}
					} );

				} );

			}, 20 );

		},
		registerContentTags: function ( formID )
		{
			if ( !$( 'div.content-tag', $( '#' + formID ) ).length ) {
				$( '.tag-table', $( '#' + formID ) ).hide();
			}

			var self = this, fields = $( 'input.content-tags', $( '#' + formID ) );
			var fo = $( '#' + formID );

			fields.each( function ()
			{

				$( this ).attr( 'formid', formID );

				var hiddenField = $( this ).css( 'float', 'left' ).prev();
				var divResult, addTag;

				if ( $( this ).next().hasClass( 'addtag-btn' ) ) {
					addTag = $( this ).next();
					divResult = $( '#live-tag-result' );

					addTag.unbind();
				}
				else {
					var addTag = $( '<span>' ).css( {
						'cursor': 'pointer',
						'float': 'left'
					} ).addClass( 'addtag-btn' );

					divResult = $( '#live-tag-result' );
					if ( !divResult.length ) {
						divResult = $( '<div id="live-tag-result">' ).addClass( 'live-tag-result' );

						if ( $( '#fullscreenContainer' ).length ) {
							$( '#fullscreenContainer' ).append( divResult );
						}
						else {
							$( 'body' ).append( divResult );
						}

					}
					$( this ).addClass( 'live-search' );
					//      divResult = $('<div>').addClass('live-tag-result');
					addTag.insertAfter( $( this ) );
					//      divResult.insertAfter(addTag);
				}

				var timeout = null, self = this;
				var inputfield = $( this );
				var currentValue = '';

				$( this ).unbind( 'blur.tagform' ).bind( 'blur.tagform', function ( e )
				{
					setTimeout( function ()
					{
						inputfield.removeClass( 'tag-loading' );
						clearTimeout( timeout );
						$( divResult ).hide();
					}, 300 );
				} );

				// live search tags
				$( this ).unbind( 'keyup.tagform' ).bind( 'keyup.tagform', function ( e )
				{
					inputfield.removeClass( 'tag-loading' );
					clearTimeout( timeout );
					$( divResult ).hide();
					var val = $( this ).val();
					var _self = this;
					if ( val.length >= 3 && e.keyCode != 27 ) {
						currentValue = hiddenField.val().trim();
						currentValue = currentValue.replace( /^0([,]?)$/g, '' );
						inputfield.addClass( 'tag-loading' );

						timeout = setTimeout( function ()
						{
							var params = {};
							params.adm = 'tags';
							params.action = 'search';
							params.q = val;
							params.table = $( _self ).attr( 'data-table' );
							params.skip = currentValue;
							params.ajax = 1;
							if ( typeof params.token == 'undefined' ) {
								params.token = Config.get( 'token' );
							}
							$.post( 'admin.php', params, function ( data )
							{
								if ( Tools.responseIsOk( data ) ) {
									divResult.empty();

									for ( var i in data.tags ) {
										if ( data.tags[i].tag ) {
											var divTag = $( '<div>' ).addClass( 'content-tag' );
											divTag.append( $( '<span>' ).append( data.tags[i].tag ) );
											divTag.append( $( '<span>' ) );

											// insert add tag
											divTag.attr( 'rel', data.tags[i].id ).css( 'cursor', 'pointer' ).click( function ()
											{

												var v = hiddenField.val().trim();
												v = v.replace( /^0([,]?)$/g, '' );

												hiddenField.val( (v != '' ? v + ',' + $( this ).attr( 'rel' ) : $( this ).attr( 'rel' )) );

												$( this ).find( 'span:last' ).addClass( 'delete-tag' ).attr( 'title', 'Diesen Tag entfernen' ).click( function ( ev )
												{
													// ev.preventDefault();
													Form.updateTagsIdField( $( this ).parent() );
												} );

												$( this ).appendTo( $( '.tag-table', fo ) );

												$( '.tag-table', fo ).show();
												$( divResult ).empty().hide();

											} );

											$( divResult ).append( divTag );

										}
									}

									if ( data.tags.length > 0 ) {
										var offset = inputfield.offset();
										$( divResult ).css( {visible: 'hidden', zIndex: 8000} ).show();

										var height = $( divResult ).outerHeight( true );

										if ( offset.top + inputfield.outerHeight( true ) + height <= $( document ).height() ) {
											$( divResult ).css( {top: offset.top + inputfield.outerHeight( true ), left: offset.left, visible: ''} );
										}
										else {
											$( divResult ).css( {top: offset.top - height, left: offset.left, visible: ''} );
										}
									}

									inputfield.removeClass( 'tag-loading' );
								}
								else {
									alert( data.msg );
								}
							}, 'json' );

						}, 300 );
					}
				} );

				// click add tag button
				addTag.unbind( 'click.tagform' ).on( 'click.tagform', function ( e )
				{
					var val = $( self ).val().trim();

					e.preventDefault();

					if ( val.length >= 3 ) {
						var params = {};
						params.adm = 'tags';
						params.action = 'add';
						params.tag = val;
						params.table = $( self ).attr( 'data-table' );
						params.send = 1;
						params.ajax = 1;
						if ( typeof params.token == 'undefined' ) {
							params.token = Config.get( 'token' );
						}
						$.post( 'admin.php', params, function ( data )
						{
							if ( Tools.responseIsOk( data ) ) {

								hiddenField.val( (currentValue ? hiddenField.val() + ',' + data.newid : data.newid) );

								var div = $( '<div>' ).attr( 'rel', data.newid ).addClass( 'content-tag' );
								var removeLink = $( '<span>' ).css( 'cursor', 'pointer' ).addClass( 'delete-tag' ).attr( 'title', 'Diesen Tag entfernen' );
								removeLink.click( function ( ev )
								{
									// ev.preventDefault();
									Form.updateTagsIdField( $( this ).parent() );
								} );
								div.append( $( '<span>' ).append( val ) );
								div.append( removeLink );
								div.appendTo( $( '.tag-table', fo ) );

								$( '.tag-table', fo ).show();
							}
							else {
								jAlert( data.msg );
							}
						}, 'json' );
					}
				} );

			} );

			// Register Tag delete Buttons
			var tags = $( '.tag-table .delete-tag', fo );
			tags.each( function ()
			{
				$( this ).attr( 'title', 'Diesen Tag entfernen' ).unbind( 'click.tagrem' ).on( 'click.tagrem', function ()
				{
					Form.updateTagsIdField( $( this ).parent() );
				} );
			} );
		},
		updateTagsIdField: function ( obj )
		{
			var id = $( obj ).attr( 'rel' );
			if ( !id ) {
				Debug.error( 'ID to delete the tag not set.' );
				return;
			}

			var hiddenField = $( obj ).parents( '.contenttags:first' ).find( 'input.content-tags' );
			hiddenField = hiddenField.prev();

			var currentValue = hiddenField.val().trim();
			var splited = currentValue.split( ',' );
			var tmp = '';

			for ( var i = 0; i < splited.length; i++ ) {
				if ( splited[i] && splited[i] != id ) {
					tmp = (tmp != '' ? tmp + ',' + splited[i] : splited[i]);
				}
			}

			hiddenField.val( tmp );
			$( obj ).remove();

			if ( $( obj ).parents( 'div.tag-table:first' ).find( 'div' ).length == 0 ) {
				$( obj ).parents( 'div.tag-table:first' ).hide();
			}

		},
		tagRemoveEvent: function ( obj, inForm )
		{
			$( obj ).unbind( 'click.tagremove' ).on( 'click.tagremove', function ()
			{
				jConfirm( 'Möchtest du diesen Tag wirklich löschen?', 'Bestätigung...', function ( r )
				{
					if ( r ) {
						var id = $( obj ).attr( 'rel' );
						var hiddenField = $( obj ).parents( '.contenttags:first' ).find( 'input.content-tags' );
						hiddenField = hiddenField.prev();
						/*
						 currentValue = hiddenField.val().trim();
						 var splited = currentValue.split(',');
						 var tmp = '';

						 for (var i=0;i<splited.length;i++)
						 {
						 if (splited[i] != id)
						 {
						 tmp = (tmp != '' ? tmp+','+ splited[i] : splited[i]);
						 }
						 }

						 hiddenField.val(tmp);
						 $(obj).parents('div:first').remove();
						 */

						var params = {};
						params.adm = 'tags';
						params.action = 'delete';
						//	params.table = $(_self ).attr('data-table');
						params.id = id;
						params.ajax = 1;
						if ( typeof params.token == 'undefined' ) {
							params.token = Config.get( 'token' );
						}
						$.post( 'admin.php', params, function ( deldata )
						{
							if ( Tools.responseIsOk( deldata ) ) {
								var hiddenField = $( obj ).parents( '.contenttags:first' ).find( 'input.content-tags' );
								hiddenField = hiddenField.prev();

								var currentValue = hiddenField.val().trim();
								var splited = currentValue.split( ',' );
								var tmp = '';

								for ( var i = 0; i < splited.length; i++ ) {
									if ( splited[i] != id ) {
										tmp = (tmp != '' ? tmp + ',' + splited[i] : splited[i]);
									}
								}

								hiddenField.val( tmp );
								$( obj ).parent().remove();

								if ( $( obj ).parents( 'div.tag-table:first' ).find( 'div' ).length == 0 ) {
									$( obj ).parents( 'div.tag-table:first' ).hide();
								}
							}
							else {
								alert( deldata.msg );
							}
						}, 'json' );

					}
				} );
			} );
		},
		/**
		 *  set button state
		 */
		enableButton: function ( btn )
		{
			$( btn ).attr( 'disabled', false );
			$( btn ).removeAttr( 'disabled' ).removeClass( 'disabled' );

		},
		disableButton: function ( btn )
		{
			$( btn ).attr( 'disabled', true ).addClass( 'disabled' );
		},
		setDisableButtons: function ( form, disabled )
		{
			var formConfig = $( '#' + $( form ).data( 'windowID' ) ).data( 'formConfig' );
			var formID = $( form ).attr( 'id' );

			var self = this, toolbar = (Config.get( 'isSeemode' ) ? $( '#seemode-content-control' ) : Desktop.getActiveWindowToolbar());

			if ( typeof formConfig.useToolbar == 'object' ) {
				toolbar = formConfig.useToolbar;
			}

			if ( toolbar.is( ':visible' ) && toolbar.find( 'button' ).length > 0 ) {
				toolbar.find( 'button' ).each( function ()
				{

					var buttonClassName = $( this ).attr( 'class' );
					buttonClassName = buttonClassName.replace( /\s*(button|action\-button|disabled)\s*/g, '' );
					var rel = $( this ).attr( 'rel' );

					if ( rel == formID ) {
						// console.log('setDisableButtons buttons for form:'+formID + ' new state: '+disabled);

						// now register the events
						switch ( buttonClassName ) {
							case "reset":

								if ( disabled ) {
									self.disableButton( (this) );
								}
								else {
									self.enableButton( (this) );
								}

								break;

							case 'run':
								if ( disabled ) {
									self.disableButton( (this) );
								}
								else {
									self.enableButton( (this) );
								}

								break;
							case 'run_exit':

								if ( disabled ) {
									self.disableButton( (this) );
								}
								else {
									self.enableButton( (this) );
								}

								break;

							case 'save':
								if ( disabled ) {
									self.disableButton( (this) );
								}
								else {
									self.enableButton( (this) );
								}
								break;

							case 'save_exit':

								if ( disabled ) {
									self.disableButton( (this) );
								}
								else {
									self.enableButton( (this) );
								}
								break;

							case 'draft':
								if ( disabled ) {
									self.disableButton( (this) );
								}
								else {
									self.enableButton( (this) );
								}
								break;
						}
					}

				} );

			}
			else {
				console.log( 'No toolbar was found for enable/disable buttons' );
			}

		},
		makeDirty: function ( formID, windowID )
		{
			if ( $( '#' + formID, $( '#' + windowID ) ).length == 1 ) {
				if ( !$( '#' + formID, $( '#' + windowID ) ).data( 'formConfig' ) ) {
					$( '#' + formID, $( '#' + windowID ) ).data( 'formConfig', {
						isDirty: false
					} );
				}

				var config = $( '#' + formID, $( '#' + windowID ) ).data( 'formConfig' );
				config = $.extend( config, {
					isDirty: true
				} );

				if ( !Config.get( 'isSeemode' ) ) {
					if ( Desktop.isWindowSkin ) {
						$( '#' + windowID ).data( 'formConfig', config );
						$( '#' + windowID ).find( '.win-title' ).find( 'sup' ).remove();
						$( '#' + windowID ).find( '.win-title' ).addClass( 'dirty' ).append( ' <sup>*</sup>' );
					}
					else {
						Core.setDirty( true );
					}
				}
				else {
					SeemodeEdit.setDirty();
				}

				//  $('#' + windowID).find('.window-titlebar').find('.title sup').remove();
				//  $('#' + windowID).find('.window-titlebar').find('.title span').addClass('dirty').append(' <sup>*</sup>');
			}
		},
		makeReset: function ( formID, windowID )
		{
			if ( $( '#' + formID, $( '#' + windowID ) ).length == 1 ) {
				if ( !$( '#' + formID, $( '#' + windowID ) ).data( 'formConfig' ) ) {
					$( '#' + formID, $( '#' + windowID ) ).data( 'formConfig', {
						isDirty: false
					} );
				}

				var config = $( '#' + formID, $( '#' + windowID ) ).data( 'formConfig' );
				config = $.extend( config, {
					isDirty: false
				} );
				if ( !Config.get( 'isSeemode' ) ) {
					if ( Desktop.isWindowSkin ) {
						$( '#' + windowID ).data( 'formConfig', config );
						$( '#' + windowID ).find( '.win-title' ).find( 'sup' ).remove();
						$( '#' + windowID ).find( '.win-title' ).removeClass( 'dirty' );
					}
					else {
						Core.resetDirty( true );
					}
				}
				else {
					SeemodeEdit.removeDirty();
				}

			}
		},
		resetDirty: function ( form, btn )
		{
			var formID = $( form ).attr( 'id' );
			var windowID = $( form ).data( 'windowID' );

			var config = $( form ).data( 'formConfig' );
			config.isDirty = false;
			$( form ).data( 'formConfig', config );

			if ( !Config.get( 'isSeemode' ) ) {

				if ( Desktop.isWindowSkin ) {
					$( '#' + windowID ).data( 'formConfig', config );
					$( '#' + windowID ).find( '.win-title' ).removeClass( 'dirty' ).find( 'sup' ).remove();
				}
				else {
					Core.resetDirty( true, btn );
				}

			}
			else {
				SeemodeEdit.removeDirty();
			}
		},
		setDirty: function ( event, form )
		{
			this.liveSetDirty( event, form );
		},

		liveSetDirty: function ( event, form, formID )
		{
			var self = this, windowID = (event && event.target ? $( event.target ).parents( 'form:first' ).data( 'windowID' ) : $( form ).data( 'windowID' ));

            if ( formID ) {
                windowID = $('#' + formID).data( 'windowID' );
            }

			if ( (typeof windowID == 'undefined' || !windowID) && form ) {
				windowID = $( form ).data( 'windowID' );
			}

            if ( !windowID && form && Win.windowID ) {
                windowID = Win.windowID;
            }


			if ( !windowID ) {
				console.log( 'Undefined form config for WindowID: ' + windowID );
				return;
			}

			var config = $( '#' + windowID ).data( 'formConfig' );

			if ( typeof config == 'undefined' || typeof config.isDirty == 'undefined' || config.isDirty === null ) {
				console.log( 'Undefined form config for WindowID: ' + windowID );
			}
			else {
                var FormID = $( '#' + windowID ).data( 'formID' );


				config = $.extend( config, {
					isDirty: true
				} );


				if ( Tools.isString( FormID ) ) {
					$( '#' + FormID, $( '#' + windowID ) ).data( 'formConfig', config );
				}

				if ( !Config.get( 'isSeemode' ) ) {
					if ( Desktop.isWindowSkin ) {
						$( '#' + windowID ).data( 'formConfig', config );
						$( '#' + windowID ).find( '.win-title' ).find( 'sup' ).remove();
						$( '#' + windowID ).find( '.win-title' ).addClass( 'dirty' ).append( ' <sup>*</sup>' );
					}
					else {
						Core.setDirty( true );
					}
				}
				else {
					// SeemodeEdit.setDirty();
				}
			}
		},
		isDirty: function ( formID, windowID )
		{
			var config = null;

			if ( !windowID ) {
				var windowID = $( '#' + formID ).data( 'windowID' );
				config = $( '#' + formID, $( '#' + windowID ) ).data( 'formConfig' );
			}
			else {
				config = $( '#' + formID, $( '#' + windowID ) ).data( 'formConfig' );
			}

			if ( typeof config == 'undefined' || typeof config.isDirty == 'undefined' ) {
				console.log( 'Undefined Form configuration for Form.isDirty.' );
				return null;
			}

			if ( config.isDirty ) {
				return true;
			}

			return false;

		},



		registerButtonEvents: function ( formID, window )
		{
			var self = this, toolbar = (Config.get( 'isSeemode' ) ? $( '#seemode-content-control' ) : (Desktop.isWindowSkin ? $( window ).getToolbar() : Desktop.getActiveWindowToolbar()));

			if ( typeof this.config.useToolbar == 'object' ) {
				toolbar = this.config.useToolbar;
			}

			if ( toolbar && toolbar.find( 'button' ).length > 0 ) {
				toolbar.find( 'button' ).each( function ()
				{
					if ( !$( this ).parents( '#VersioningForm' ).length ) {



						var buttonClassName = $( this ).attr( 'class' );
						if ( buttonClassName ) {
							$( this ).unbind( 'click.form' );
							buttonClassName = buttonClassName.replace( /.*\s?(reset|run|run_exit|save|save_exit|cancel|draft)\s.*/g, '$1' );
							//console.log('Button buttonClassName:' + buttonClassName);

							var rel = $( this ).attr( 'rel' );
							//console.log('Button rel:' + rel);

							if ( rel == formID ) {
								// set the button data first
								$( this ).data( 'formID', formID ).data( 'windowID', self.windowID );

                                var button = $(this);

								// now register the events
								switch ( buttonClassName ) {
									case 'reset':


                                        button.unbind( 'click.form' ).bind( 'click.form', function ( e )
										{
                                            e.preventDefault();

											self.formID = $( this ).data( 'formID' );
											self.windowID = $( this ).data( 'windowID' );
											self.resetForm( e );
										} );


                                        Core.addShortcutHelp('Alt+R', 'Reset Dokument', true);
                                        $(document).bind('keydown.form' + formID, function(e) {
                                            var char = String.fromCharCode(e.keyCode).toLocaleLowerCase();
                                            if (e.altKey && char == 'r') {
                                                button.trigger('click.form');
                                            }
                                        });



										break;

									case 'run':

                                        Core.addShortcutHelp('Alt+S', 'Execute', true);
                                        $(document).bind('keydown.form' + formID, function(e) {
                                            var char = String.fromCharCode(e.keyCode).toLocaleLowerCase();
                                            if (e.altKey && char == 's') {
                                                button.trigger('click.form');
                                            }
                                        });


                                        button.unbind( 'click.form' ).bind( 'click.form', function ( e )
										{
                                            e.preventDefault();

											self.formID = $( this ).data( 'formID' );
											self.windowID = $( this ).data( 'windowID' );

											self.save( e, false, false, self.formID, self.windowID );
										} );



										break;
									case 'run_exit':

                                        Core.addShortcutHelp('Ctrl+Alt+E', 'Run and Exit the Document', true);
                                        $(document).bind('keydown.form' + formID, function(e) {
                                            var char = String.fromCharCode(e.keyCode).toLocaleLowerCase();
                                            if (e.altKey && e.ctrlKey && char == 'e') {
                                                button.trigger('click.form');
                                            }
                                        });

                                        button.unbind( 'click.form' ).bind( 'click.form', function ( e )
										{
                                            e.preventDefault();

											self.formID = $( this ).data( 'formID' );
											self.windowID = $( this ).data( 'windowID' );

											if ( !$( this ).parents( '.switch-content-window' ).length ) {

												self.save( e, true, false, self.formID, self.windowID );
											}
											else {
												self.save( e, false, false, self.formID, self.windowID );
												if ( !Config.get( 'isSeemode' ) ) {
													var _self = this;
													setTimeout( function ()
													{
														$( _self ).parents( '.switch-content-window' ).data( 'WindowManager' ).switchSingleContent( e, 'main' );
													}, 250 );
												}
											}
										} );
										break;

									case 'save':

                                        Core.addShortcutHelp('Alt+S', 'Save the Document', true);

                                        $(document).bind('keydown.form' + formID, function(e) {
                                            var char = String.fromCharCode(e.keyCode).toLocaleLowerCase();
                                            if (e.altKey && char == 's') {
                                                button.trigger('click.form');
                                            }
                                        });

                                        button.unbind( 'click.form' ).bind( 'click.form', function ( e )
										{
                                            e.preventDefault();

											self.formID = $( this ).data( 'formID' );
											self.windowID = $( this ).data( 'windowID' );
											self.save( e, false, false, self.formID, self.windowID );
										} );

										break;

									case 'save_exit':

                                        Core.addShortcutHelp('Ctrl+Alt+E', 'Save and Exit the Document', true);

                                        $(document).bind('keydown.form' + formID, function(e) {
                                            var char = String.fromCharCode(e.keyCode).toLocaleLowerCase();
                                            if (e.altKey && e.ctrlKey && char == 'e') {
                                                button.trigger('click.form');
                                            }
                                        });

										$( this ).unbind( 'click.form' ).bind( 'click.form', function ( e )
										{
                                            e.preventDefault();

											self.formID = $( this ).data( 'formID' );
											self.windowID = $( this ).data( 'windowID' );

											if ( !$( this ).parents( '.switch-content-window' ).length ) {

												self.save( e, true, false, self.formID, self.windowID );
											}
											else {
												self.save( e, false, false, self.formID, self.windowID );

												if ( !Config.get( 'isSeemode' ) ) {
													var _self = this;
													setTimeout( function ()
													{
														$( _self ).parents( '.switch-content-window' ).data( 'WindowManager' ).switchSingleContent( e, 'main' );
													}, 250 );
												}
											}
										} );

										break;

									case 'cancel':


                                        Core.addShortcutHelp('Alt+C', 'Cancel the Document', true);

                                        $(document).bind('keydown.form' + formID, function(e) {
                                            var char = String.fromCharCode(e.keyCode).toLocaleLowerCase();
                                            if (e.altKey && char == 'c') {
                                                button.trigger('click.form');
                                            }
                                        });




										if ( !Config.get( 'isSeemode' ) ) {
                                            button.unbind( 'click.form' ).bind( 'click.form', function ( e )
											{
                                                e.preventDefault();

												self.formID = $( this ).data( 'formID' );
												self.windowID = $( this ).data( 'windowID' );

												if ( !$( this ).parents( '.switch-content-window' ).length ) {
													if ( $( '#' + self.windowID ).data( 'WindowManager' ) ) {
														// Doc.unload(self.windowID);
														$( '#' + self.windowID ).data( 'WindowManager' ).set( 'isForceClose', true );
														$( '#' + self.windowID ).data( 'WindowManager' ).close();
														//Desktop.getActiveWindowButton('close').trigger('click');
													}
													else {
														Core.Tabs.closeActiveTab();
													}
												}
												else {
													$( this ).parents( '.switch-content-window' ).data( 'WindowManager' ).switchSingleContent( e, 'main' );
												}

											} );
										}
										else {
                                            button.unbind( 'click.form' ).bind( 'click.form', function ( e )
											{
                                                e.preventDefault();

												SeemodeEdit.sendDokumentRollback( true );
											} );
										}
										break;

									case 'draft':

                                        Core.addShortcutHelp('Alt+D', 'Save the Document as Draft', true);

                                        $(document).bind('keydown.form' + formID, function(e) {
                                            var char = String.fromCharCode(e.keyCode).toLocaleLowerCase();
                                            if (e.altKey && char == 'd') {
                                                button.trigger('click.form');
                                            }
                                        });

                                        button.unbind( 'click.form' ).bind( 'click.form', function ( e )
										{
                                            e.preventDefault();

											self.formID = $( this ).data( 'formID' );
											self.windowID = $( this ).data( 'windowID' );

											self.save( e, true, true, self.formID, self.windowID );
										} );
										break;
								}
							}
							else {
								// console.log('Skip Form Registry button! ID:' + formID);
							}

						}
					}

				} );
			}
			else {
				console.log( 'Toolbar is not visible. Used for Form Registry!!!' );
			}
		},
		// ----------------------------------------------------------
		// helper functions
		// ----------------------------------------------------------
		_getWindowID: function ( e )
		{
			// return the Button data store
			return $( e.target ).data( 'windowID' ) ? $( e.target ).data( 'windowID' ) : $( e.target ).parents( '.action-button:first' ).data( 'windowID' );
		},
		_getFormID: function ( e )
		{
			// return the Button data store
			return $( e.target ).data( 'formID' ) ? $( e.target ).data( 'formID' ) : $( e.target ).parents( '.action-button:first' ).data( 'formID' );
		},
		_getForm: function ( e )
		{
			// return the Button data store

			var id = this._getFormID( e );
			var wid = this._getWindowID( e );

			return $( '#' + wid ).find( '#' + id );
		},
		// ----------------------------------------------------------
		// form events
		// ----------------------------------------------------------
		resetForm: function ( event, btn )
		{
			this.formID = this._getFormID( event );
			this.windowID = this._getWindowID( event );
			this.config = $( '#' + this.windowID ).data( 'formConfig' );

			// stop reset click if not dirty
			if ( (typeof this.config.isDirty != 'undefined' && !this.config.isDirty) ) {
				return;
			}
			this.form = {};
			this.form = this._getForm( event );

			$( '#' + this.windowID ).find( '#' + this.formID ).get( 0 ).reset();

			if ( Tools.isFunction( this.config.onReset ) ) {
				this.config.onReset( this.formID, this.config );
			}

			Doc.resetDocumentSettings( this.windowID, this.formID );

			if ( !Config.get( 'isSeemode' ) ) {
				Win.resetWindowFormUi( this.windowID, this.formID );
			}
			else {
				$( '#' + this.formID ).get( 0 ).reset();
			}

			this.resetDirty( this.form, btn );

		},
		updateSelectDefaultAttr: function ( formID, windowID )
		{
			if ( !Config.get( 'isSeemode' ) ) {
				Win.updateFormUiDefaults( formID, windowID );

			}
		},
		validateForm: function ( event )
		{
			this.formID = this._getFormID( event );
			this.windowID = this._getWindowID( event );

			this.form = {};
			this.form = this._getForm( event );

			$( '#' + this.windowID ).find( '#' + this.formID ).get( 0 ).reset();
			this.resetDirty( this.form );

			// var wincontent = Desktop.getActiveWindowContent();
		},
		saveCallBack: function ( event, data, exit, isdraft, formID, windowID, postData )
		{
			var $form = $( '#' + windowID ).find( '#' + formID ); //this._getForm(event);
			var $_win = $( '#' + windowID );
			var config = $_win.data( 'formConfig' );

			if ( typeof config != 'object' ) {
				console.log( 'invalid form config!' );
				return;
			}
			var docID = null, self = this;

			if ( config.contentIdentifierID && $( '#' + windowID ).find( '#' + config.contentIdentifierID ).val() > 0 ) {
				docID = $( '#' + windowID ).find( '#' + config.contentIdentifierID ).val();
			}

			$form.focus();
			$form.get( 0 ).focus();

			self.formID = formID;
			self.config = config;

			if ( Tools.responseIsOk( data ) ) {
				if ( typeof data.versionselection != 'undefined' && data.versionselection != '' ) {
					var versioning;
					if ( $( '#' + windowID ).data( 'WindowManager' ) ) {
						versioning = $( '#' + windowID ).find( '#setVersion' );
					}
					else {
						var tb = Core.getToolbar();
						if ( tb ) {
							versioning = tb.find( '#setVersion' );
						}
					}
					if ( versioning.length == 1 ) {
						versioning.SelectBox( 'destroy' );
						versioning.empty().append( data.versionselection );
						versioning.SelectBox();
					}
				}

				self.resetDirty( $form );

				if ( docID == null && data.newid > 0 && typeof config.contentIdentifierID == 'string' && config.contentIdentifierID != '' ) {
					$( '#' + windowID ).find( '#' + config.contentIdentifierID ).val( data.newid );
					docID = data.newid;
				}

				// update content tree item
				var treeItem = $( '#tree-node-' + docID ).filter( '[modul=' + $form.find( 'input[name=adm]' ).val() + ']' );
				if ( treeItem.length == 1 ) {
					var d = treeItem.data( 'nodeData' );
					var hash = windowID.replace( 'tab-', '' ).replace( 'content-', '' );

					if ( $( '#meta-' + hash ).length && $( '#meta-' + hash ).find( '#meta-published' ).length ) {

						var sel = parseInt( $( '#meta-published', $( '#meta-' + hash ) ).find( ':selected' ).val() );

						if ( sel == 1 || sel == 2 ) {
							treeItem.removeClass( 'tree-node-unpublished' ).addClass( 'tree-node-published' );
							if ( d && typeof d.published != 'undefined' ) {
								d.published = 1;
							}
						}
						else {
							treeItem.addClass( 'tree-node-unpublished' ).removeClass( 'tree-node-published' );
							if ( d && typeof d.published != 'undefined' ) {
								d.published = 0;
							}
						}

					}
					else {
						if ( $form.find( '[name=published]' ).length ) {

							if ( $form.find( '[name=published]' ).is( 'select' ) ) {
								var s = null;
								var sel = parseInt( $( this ).find( ':selected' ).val() );

								if ( sel === 1 || sel === 2 ) {
									treeItem.removeClass( 'tree-node-unpublished' ).addClass( 'tree-node-published' );
									if ( d && typeof d.published != 'undefined' ) {
										d.published = 1;
									}

									s = true;
								}
								else {
									if ( s === null ) {
										treeItem.addClass( 'tree-node-unpublished' ).removeClass( 'tree-node-published' );
										if ( d && typeof d.published != 'undefined' ) {
											d.published = 0;
										}
									}
								}
							}
							else {
								var s = null;
								$( this ).each( function ()
								{
									var sel = $( this ).is( ':selected' );
									if ( sel === 1 || sel === 2 ) {
										s = true;
										treeItem.removeClass( 'tree-node-unpublished' ).addClass( 'tree-node-published' );
										if ( d && typeof d.published != 'undefined' ) {
											d.published = 1;
										}
									}
									else {
										if ( s === null ) {
											treeItem.addClass( 'tree-node-unpublished' ).removeClass( 'tree-node-published' );
											if ( d && typeof d.published != 'undefined' ) {
												d.published = 0;
											}
										}
									}
								} );
							}
						}
					}

					if ( exit === true ) {
						treeItem.removeClass( 'locked' );
						var src = treeItem.find( '.tree-node-icon:first' ).attr( 'src' );
						if ( src ) {
							treeItem.find( '.tree-node-icon:first' ).attr( 'src', src.replace( '-locked', '' ) );
						}

						if ( d && typeof d.locked != 'undefined' ) {
							d.locked = 0;
						}
					}

					if ( d ) {
						treeItem.data( 'nodeData', d );
					}
				}

				// patch
				self.updateSelectDefaultAttr( $form );

				if ( event == 'autosave' )
                {
					return true;
				}

				// refresh the opener window
				var openerID = $_win.attr( 'opener' );

				console.log( 'openerID: ' + openerID + ' docID:' + docID );

				$_win.unmask();

				if ( exit === true && !Config.get( 'isSeemode' ) ) {

					// Doc.unload();

					// remove rollback attribute from window.
					// this stop the ajax rollback call in dcms.application.js at onBeforeWindowClose()
					if ( exit ) {
						//$_win.removeAttr('rollback');
					}

					var w = $_win.data( 'WindowManager' );

					Notifier.display( 1, (typeof data.msg != 'undefined' ? data.msg : 'Formular wurde erfolgreich gespeichert') );

					if ( w ) {
						$( '#' + Win.windowID ).removeData( 'formConfig' );
						w.close( event, $_win.data( 'WindowManager' ), function ()
						{

							// highlight the edited content :)
							if ( !self.config.forceNoRefresh && typeof openerID == 'string' && docID != null && parseInt( docID ) > 0 ) {
								Win.refreshOpenerWindow( openerID, function ()
								{
									setTimeout( function ()
									{

										if ( typeof config.onAfterSubmit === 'function' ) {
											config.onAfterSubmit( exit, data, $form );
										}

										var tr = $( '#' + openerID ).find( '#data-' + docID );
										if ( tr.length == 1 ) {
											var bgColor, color;
											if ( tr.is( 'tr' ) ) {
												bgColor = tr.find( 'td:first' ).css( 'backgroundColor' );
												color = tr.find( 'td:first' ).css( 'color' );
											}
											else {
												bgColor = tr.css( 'backgroundColor' );
												color = tr.css( 'color' );
											}

											bgColor = bgColor || '#ffffff';
											color = color || '#333333';

											tr.animate( {
												backgroundColor: '#FF235D',
												color: "#fff"
											}, 150, function ()
											{
												$( this ).animate( {
													backgroundColor: bgColor,
													color: color
												}, 150, function ()
												{
													$( this ).animate( {
														backgroundColor: '#FF235D',
														color: "#fff"
													}, 150, function ()
													{
														$( this ).animate( {
															backgroundColor: bgColor,
															color: color
														}, 150, function ()
														{
															$( this ).animate( {
																backgroundColor: '#FF235D',
																color: "#fff"
															}, 150, function ()
															{

																$( this ).animate( {
																	backgroundColor: bgColor,
																	color: color
																}, 150, function ()
																{
																	$( this ).css( {
																		backgroundColor: '',
																		color: ''
																	} );
																} );
															} );
														} );
													} );
												} );
											} );
										}
									}, 150 );
								} );
							}
						} );
					}
					else {
						/**
						 * Only for none Window Skin
						 */

						Core.closeTab( function ()
						{

							// highlight the edited content :)
							if ( !self.config.forceNoRefresh && typeof openerID == 'string' && docID != null && parseInt( docID ) > 0 ) {
								Win.refreshOpenerWindow( openerID, function ()
								{
									setTimeout( function ()
									{

										if ( typeof config.onAfterSubmit === 'function' ) {
											config.onAfterSubmit( exit, data, $form );
										}

										var hash;
										if ( openerID.match( /^tab-/ ) ) {
											hash = openerID.replace( 'tab-', '' );
										}

										if ( openerID.match( /^content-/ ) ) {
											hash = openerID.replace( 'content-', '' );
										}

										if ( openerID.match( /^meta-/ ) ) {
											hash = openerID.replace( 'meta-', '' );
										}

										if ( hash ) {

											var tr = $( '#content-' + hash ).find( '#data-' + docID );
											if ( tr.length == 1 ) {
												var bgColor, color;
												if ( tr.is( 'tr' ) ) {
													bgColor = tr.find( 'td:first' ).css( 'backgroundColor' );
													color = tr.find( 'td:first' ).css( 'color' );
												}
												else {
													bgColor = tr.css( 'backgroundColor' );
													color = tr.css( 'color' );
												}

												bgColor = bgColor || '#ffffff';
												color = color || '#333333';

												tr.animate( {
													backgroundColor: '#FF235D',
													color: "#fff"
												}, 150, function ()
												{
													$( this ).animate( {
														backgroundColor: bgColor,
														color: color
													}, 150, function ()
													{
														$( this ).animate( {
															backgroundColor: '#FF235D',
															color: "#fff"
														}, 150, function ()
														{
															$( this ).animate( {
																backgroundColor: bgColor,
																color: color
															}, 150, function ()
															{
																$( this ).animate( {
																	backgroundColor: '#FF235D',
																	color: "#fff"
																}, 150, function ()
																{

																	$( this ).animate( {
																		backgroundColor: bgColor,
																		color: color
																	}, 150, function ()
																	{
																		$( this ).css( {
																			backgroundColor: '',
																			color: ''
																		} );
																	} );
																} );
															} );
														} );
													} );
												} );
											}

										}
									}, 200 );
								} );
							}
							else {
								if ( typeof config.onAfterSubmit === 'function' ) {
									config.onAfterSubmit( exit, data, $form );
								}
							}
						}, (typeof data.unlock_content != 'undefined' && data.unlock_content == true ? true : false), self.config.contentlockaction );
					}

				}
				else if ( exit === true && Config.get( 'isSeemode' ) ) {
					SeemodeEdit.triggerFormSave( exit, data );
					Notifier.display( 1, (typeof data.msg != 'undefined' ? data.msg : 'Formular wurde erfolgreich gespeichert') );
				}
				else if ( exit !== true && !Config.get( 'isSeemode' ) ) {
					Notifier.display( 1, (typeof data.msg != 'undefined' ? data.msg : 'Formular wurde erfolgreich gespeichert') );

					// highlight the edited content :)
					if ( Desktop.isWindowSkin && !self.config.forceNoRefresh && typeof openerID == 'string' && docID != null && parseInt( docID ) > 0 ) {

						Win.refreshOpenerWindow( openerID, function ()
						{
							setTimeout( function ()
							{

								if ( typeof config.onAfterSubmit === 'function' ) {
									config.onAfterSubmit( exit, data, $form );
								}

								var tr = $( '#' + openerID ).find( '#data-' + docID );
								console.log( [tr] );
								if ( tr.length == 1 ) {
									var bgColor, color;
									if ( tr.is( 'tr' ) ) {
										bgColor = tr.find( 'td:first' ).css( 'backgroundColor' );
										color = tr.find( 'td:first' ).css( 'color' );
									}
									else {
										bgColor = tr.css( 'backgroundColor' );
										color = tr.css( 'color' );
									}

									tr.animate( {
										backgroundColor: '#FF235D',
										color: "#fff"
									}, 150, function ()
									{
										$( this ).animate( {
											backgroundColor: bgColor,
											color: color
										}, 150, function ()
										{
											$( this ).animate( {
												backgroundColor: '#FF235D',
												color: "#fff"
											}, 150, function ()
											{
												$( this ).animate( {
													backgroundColor: bgColor,
													color: color
												}, 150, function ()
												{
													$( this ).animate( {
														backgroundColor: '#FF235D',
														color: "#fff"
													}, 150, function ()
													{

														$( this ).animate( {
															backgroundColor: bgColor,
															color: color
														}, 150, function ()
														{
															$( this ).css( {
																backgroundColor: '', color: ''
															} );
														} );
													} );
												} );
											} );
										} );
									} );

								}
								else {

								}
							}, 150 );
						} );
					}
					else {
						if ( typeof config.onAfterSubmit === 'function' ) {
							config.onAfterSubmit( exit, data, $form );
						}
					}


                    return true;
				}
			}
			else {
				if ( event == 'autosave' ) {

                    var tb = Core.getToolbar();

                    if ( tb && tb.length ) {
                        $( 'button[rel=' + formID + ']', tb ).removeClass( 'autosave' );
                    }
                    else {
                        $( 'button[rel=' + formID + ']' ).removeClass( 'autosave' );
                    }

					console.log( 'Autosave Error:' + (typeof data.msg != 'undefined' ? data.msg : 'Es ist ein Fehler aufgetreten') );

					return false;
				}

				Notifier.display( 'error', (typeof data.msg != 'undefined' ? data.msg : 'Es ist ein Fehler aufgetreten') );
			}

			$_win.unmask();
		},
		getFormPostData: function ( form )
		{
			var rCRLF = /\r?\n/g;
			return jQuery.param( form.find( 'input,select,textarea' ).map( function ( i, elem )
			{
				if ( elem.name && !jQuery( this ).is( ":disabled" ) ) {
					var type = this.type;
					var val = null;
					if ( type == 'radio' || type == 'checkbox' ) {
						val = this.checked ? jQuery( this ).val() : null;
					}
					else {
						val = jQuery( this ).val();
					}

					if ( val != null ) {
						var subcontainer = jQuery( this ).parents( '.form-sub-container:first' );
						if ( subcontainer.length ) {
							// add only post fields if the container is visible :)
							if ( subcontainer.is( ':visible' ) ) {
								return jQuery.isArray( val ) ?
									jQuery.map( val, function ( _val )
									{
										return {name: elem.name, value: _val.replace( rCRLF, "\r\n" )};
									} ) : {name: elem.name, value: val.replace( rCRLF, "\r\n" )};
							}
						}
						else {
							return jQuery.isArray( val ) ?
								jQuery.map( val, function ( _val )
								{
									return {name: elem.name, value: _val.replace( rCRLF, "\r\n" )};
								} ) : {name: elem.name, value: val.replace( rCRLF, "\r\n" )};
						}
					}

					return null;
				}
			} ) );
		},
		save: function ( event, exit, isdraft, formID, windowID )
		{
			var autosave = false, self = this; //this._getWindowID(event);

			var $_win = $( '#' + windowID );
            var $form = $( 'form#' + formID ); //this._getForm(event);

			var config = $form.data( 'formConfig' ), error = false;

			if ( typeof config != 'object' ) {
				console.log( 'invalid form config!' );
				return false;
			}

			updateTextareaFields( $form );



			if ( event != 'autosave' ) {
                $form.find( 'input,textarea' ).each( function ()
                {
                    if ( !error && /*$( this ).parents().is( ':visible' ).length &&*/ $( this ).is( ':visible' ) ) {
                        if ( Form.validation( formID, $( this ) ) ) {
                            console.log( 'Form error for field: ' + $( this ).attr( 'name' ) );
                            error = true;
                        }
                    }
                } );

                if ( error ) {
                    return false;
                }

				$_win.mask( cmslang.save_notify );
			}
			else
            {
                autosave = true;

                $form.find( 'input,textarea' ).each( function ()
                {
                    if ( !error && /*$( this ).parents().is( ':visible' ).length &&*/ $( this ).is( ':visible' ) ) {
                        if ( Form.validation( formID, $( this ), true) ) {
                            error = true;
                        }
                    }
                } );

                if ( error ) {
                    return false;
                }


                var tb = Core.getToolbar();
                if ( tb && tb.length ) {
                    $( 'button[rel=' + formID + ']', tb ).removeClass( 'autosave' );
                }
				else {
                    $( 'button[rel=' + formID + ']', $_win ).addClass( 'autosave' );
                }
			}

			this.form = {};
			var docID = null, self = this, stop = false, savevalid;

			setTimeout( function ()
			{
				if ( config.contentIdentifierID && $( '#' + windowID ).find( '#' + config.contentIdentifierID ).val() > 0 ) {
					docID = $( '#' + windowID ).find( '#' + config.contentIdentifierID ).val();
				}

				$form.focus();
				$form.get( 0 ).focus();

				self.formID = formID;
				self.config = config;

				//  console.log('action save. formID:' + formID + ' windowID:' + windowID);
				// this.config = $(this.form).data('formConfig');

				/**
				 * prepare Date before serialize the form
				 */
				if ( typeof config.onBeforeSerialize === 'function' ) {
					config.onBeforeSerialize( $form, config, $_win );
				}

				var postData = $form.serialize(); // $form.dcmsSerialize(); // this.getFormPostData($form); //$form.serialize();

				if ( typeof postData.token == 'undefined' ) {
					postData.token = Config.get( 'token' );
				}

				if ( typeof config.onBeforeSend === 'function' ) {
					postData = config.onBeforeSend( postData );
				}

				// prepare data to post the form
				postData += '&ajax=1&exit=' + (exit ? 1 : 0);

				if ( typeof self.config.contentlockaction === 'string' ) {
					postData += '&unlockaction=' + self.config.contentlockaction;
				}

				if ( $( '#documentmetadata' + formID ).length ) {
					postData += '&' + $( '#documentmetadata' + formID ).serialize();
				}

				if ( typeof isdraft != 'undefined' && isdraft == true ) {
					postData += '&savedraft=1';
				}

				if ( typeof config.onBeforeSubmit === 'function' ) {
					stop = config.onBeforeSubmit( postData );
				}

				if ( stop ) {
					return false;
				}

				if ( typeof config.save === 'function' ) {
					config.save( event, exit, isdraft, formID, windowID, postData );
					return false;
				}


				// update statusbar to saving
				if ( !Desktop.isWindowSkin ) {

                    if (!autosave) {
                        Core.setSaving( true );
                    }

					setTimeout( function ()
					{
						$.ajax( {
							type: "POST",
							url: 'admin.php',
							'data': postData,
							timeout: 10000,
							dataType: 'json',
							cache: false,
							async: false,
							success: function ( data )
							{
                                if ( self.saveCallBack( event, data, exit, isdraft, formID, windowID, postData ) )
                                {
                                    config.autoSaveInstance.saveCallback( true );
                                }

                                return false;
							}
						} );

					}, 10 );

				}
				else {

					$.ajax( {
						type: "POST",
						url: 'admin.php',
						'data': postData,
						timeout: 10000,
						dataType: 'json',
						cache: false,
						async: false,
						success: function ( data )
						{
                            self.saveCallBack( event, data, exit, isdraft, formID, windowID, postData );
						}
					} );
				}
			}, 10 );

		},
		// register Tag input fields
		registerContentTagInput: function ()
		{
			if ( !$( '.tag-table', $( this.window ) ).find( 'div.content-tag' ).length ) {
				$( '.tag-table', $( this.window ) ).hide();
			}

			var _self = this;
			var fields = $( this.window ).find( '.tag-inputs' ).find( 'input.content-tags' );
			fields.each( function ()
			{
				var hiddenField = $( this ).css( 'float', 'left' ).prev();
				var divResult = $( '<div>' ).addClass( 'live-tag-result' );

				var addTag = $( '<span>' ).css( {
					'cursor': 'pointer',
					'float': 'left'
				} ).addClass( 'addtag-btn' );

				addTag.insertAfter( $( this ) );
				divResult.insertAfter( $( this ) );

				var self = this;
				var currentValue = '';
				// live search tags
				$( this ).bind( 'keyup', function ()
				{

					var val = $( self ).val();

					currentValue = hiddenField.val().trim();
					$( divResult ).hide();
					if ( val.length >= 4 ) {
						var params = {};
						params.adm = 'tags';
						params.action = 'search';
						params.q = val;
						params.table = $( this ).attr( 'data-table' );
						params.skip = currentValue;
						params.ajax = 1;
						if ( typeof params.token == 'undefined' ) {
							params.token = Config.get( 'token' );
						}
						$.post( 'admin.php', params, function ( data )
						{
							if ( Tools.responseIsOk( data ) ) {
								divResult.empty();

								for ( i in data.tags ) {

									var div = $( '<div>' ).addClass( 'content-tag' ).append( $( '<span>' ).append( data.tags[i].tag ) );
									var addLink = $( '<span>' ).attr( 'rel', data.tags[i].id ).css( 'cursor', 'pointer' );

									div.append( addLink );
									$( divResult ).show().append( div );

									addLink.click( function ()
									{
										hiddenField.val( (hiddenField.val() != '' ? hiddenField.val() + ',' + $( this ).attr( 'rel' ) : $( this ).attr( 'rel' )) );

										$( this ).addClass( 'delete-tag' );
										$( this ).unbind( 'click' );
										_self.tagRemoveEvent( $( this ) );

										div.appendTo( $( '.tag-table' ) );
										$( '.tag-table' ).show();

									} );

								}
							}
							else {
								jAlert( data.msg );
							}
						}, 'json' );
					}
				} );

				$( 'body' ).click( function ()
				{
					divResult.hide().empty();
				} );

				// click add tag button
				addTag.click( function ()
				{
					var val = $( self ).val().trim();
					if ( val.length >= 4 ) {
						var params = {};
						params.adm = 'tags';
						params.action = 'add';
						params.tag = val;
						params.table = $( self ).attr( 'data-table' );
						params.send = 1;
						params.ajax = 1;

						if ( typeof params.token == 'undefined' ) {
							params.token = Config.get( 'token' );
						}

						$.post( 'admin.php', params, function ( data )
						{
							if ( Tools.responseIsOk( data ) ) {

								hiddenField.val( (currentValue ? hiddenField.val() + ',' + data.newid : data.newid) );

								var div = $( '<div>' ).addClass( 'content-tag' );
								var removeLink = $( '<span>' ).attr( 'rel', data.newid ).css( 'cursor', 'pointer' ).addClass( 'delete-tag' );

								div.append( $( '<span>' ).append( val ) );
								div.append( removeLink );
								div.appendTo( $( '.tag-table' ) );

								_self.tagRemoveEvent( $( removeLink ) );

								$( '.tag-table', $( _self.window ) ).show();
							}
							else {
								jAlert( data.msg );
							}
						}, 'json' );
					}
				} );
			} );
		},
		/*
		 tagRemoveEvent: function(obj)
		 {
		 $(obj).click(function() {
		 jConfirm('Möchtest du diesen Tag wirklich löschen?', 'Bestätigung...', function(r) {
		 if (r) {
		 var id = $(obj).attr('rel');
		 var hiddenField = $(obj).parents('.contenttags:first').find('input.content-tags');
		 hiddenField = hiddenField.prev();

		 var currentValue = hiddenField.val().trim();
		 var splited = currentValue.split(',');
		 var tmp = '';

		 for (var i = 0; i < splited.length; i++)
		 {
		 if (splited[i] != id)
		 {
		 tmp = (tmp != '' ? tmp + ',' + splited[i] : splited[i]);
		 }
		 }

		 hiddenField.val(tmp);
		 $(obj).parents('div:first').remove();

		 if ($('.tag-table').find('div').length == 0)
		 {
		 $('.tag-table').hide();
		 }

		 var params = {};
		 params.adm = 'tags';
		 params.action = 'delete';
		 //params.table = contentTable;
		 params.id = id;
		 params.ajax = 1;

		 $.post('admin.php', params, function(deldata) {
		 if (Desktop.responseIsOk(deldata))
		 {
		 var hiddenField = $(obj).parents('.contenttags:first').find('input.content-tags');
		 hiddenField = hiddenField.prev();

		 currentValue = hiddenField.val().trim();
		 var splited = currentValue.split(',');
		 var tmp = '';

		 for (var i = 0; i < splited.length; i++)
		 {
		 if (splited[i] != data.newid)
		 {
		 tmp = (tmp != '' ? tmp + ',' + splited[i] : splited[i]);
		 }
		 }

		 hiddenField.val(tmp);
		 $(obj).parents('div:first').remove();

		 }
		 else
		 {
		 jAlert(deldata.msg);
		 }
		 }, 'json');


		 }
		 });
		 });
		 }, */
		// register Alias input fields
		rebuildPageIdentifier: function ( baseField, pagetype, contentid )
		{
			var _self = this, identifiers = $( '.pageident', $( this.window ) );
			identifiers.each( function ()
			{

				var mode = $( this ).attr( 'name' );
				var self = this;

				if ( mode != '' ) {

					var insertAfter = $( self, $( _self.window ) );

					if ( insertAfter.next().hasClass( 'dropdown' ) ) {
						insertAfter = insertAfter.next().next();
					}

					if ( $( '.identifiercheck', $( _self.window ) ).length == 0 ) {
						if ( mode == 'identifier' ) {
							insertAfter.after( '<span class="identifiercheck">[<a href="javascript:void(0)" id="ident_' + mode + '">Identifier Check</a>]</span><div style="display:none;" id="identifier-url-spacer">&nbsp;</div>' );
						}
						else {
							insertAfter.next().after( '<span class="identifiercheck">[<a href="javascript:void(0)" id="ident_' + mode + '">Alias Check</a>]</span><div id="identifier-url" style="display:none;"></div>' );
						}
					}

					$( '#ident_' + mode, $( _self.window ) ).click( function ()
					{
						var baseData = '';
						var _basefield = $( "input[name='" + Tools.escapeJqueryRegex( baseField ) + "']", $( _self.window ) );

						if ( _basefield.length ) {
							baseData = $( "input[name='" + Tools.escapeJqueryRegex( baseField ) + "']", $( _self.window ) ).val(); // Basis Textfeld
						}
						else if ( $( "input#" + baseField ).length ) {
							baseData = $( "input#" + baseField, $( _self.window ) ).val(); // Basis Textfeld
						}

						if ( baseData != '' ) {
							$( self ).addClass( 'fieldValidation' );

							var lastIdentifierName = $( "#maincontent input[name='identifier']", $( _self.window ) ).val();
							var rw_controller = $( "#maincontent input[name='rw_controller']", $( _self.window ) ).val();
							var rw_action = $( "#maincontent input[name='rw_action']", $( _self.window ) ).val();
							var suffix = $( self, $( _self.window ) ).next().find( 'option:selected' ).val();

							var _data = $( self, $( _self.window ) ).val();

							// current alias
							var currentaliasname = '';
							if ( mode == 'alias' ) {
								currentaliasname = $( self, $( _self.window ) ).val();
							}

							if ( typeof contentid == "undefined" ) {
								contentid = 0;
							}

							//alert('admin.php?adm=identifier&pagetype='+ pagetype +'&contentid='+ contentid +'&mode='+ mode +'&base='+ baseData +'&identifier='+ identifierData +'&data='+ _data +'&current='+ originaldata);

							$.get( 'admin.php?action=checkalias&pagetype=' + pagetype + '&contentid=' + contentid + '&base=' + baseData + '&data=' + _data + '&current=' + currentaliasname + '&suffix=' + suffix, {}, function ( data )
							{
								if ( Tools.responseIsOk( data ) ) {
									if ( mode == 'alias' ) {
										var url = data.url;
										url += (suffix ? '.' + suffix : '');

										$( self, $( _self.window ) ).val( data.str );

										//if ( identifierData == '' )
										//{
										$( "input[name='identifier']" ).val( data.str );
										//}

										$( '#identifier-url-spacer', $( _self.window ) ).show();
										$( '#identifier-url', $( _self.window ) ).html( '<span>' + cmslang.formidentifier_example + '</span> <code>' + cmsurl + url + '</code>' ).show();
										$( self, $( _self.window ) ).removeClass( 'fieldValidation' );
									}
									else if ( mode == 'identifier' ) {
										$( self, $( _self.window ) ).val( data.str );
									}
								}
								else {
									$( self, $( _self.window ) ).removeClass( 'fieldValidation' );
									jAlert( data.msg );
								}

							}, 'json' );
						}
					} );
				}
			} );
		},
		addMetaDataForm: function ( windowID, formID )
		{
			var timeControll = $( '#timecontrol', $( '#' + windowID ) );
			var mpublish = $( '#meta-published', $( '#' + windowID ) );
			var selectedState = mpublish.find( ':selected' ).val();

			mpublish.attr( 'window', windowID ).attr( 'form', formID );

			if ( selectedState != 2 ) {
				timeControll.hide();
			}

			if ( selectedState == 2 ) {
				$( '#publish-state', $( '#' + windowID ) ).removeClass( 'online' ).removeClass( 'offline' ).addClass( 'timecontrol' );
			}
			else if ( selectedState == 1 ) {
				$( '#publish-state', $( '#' + windowID ) ).removeClass( 'timecontrol' ).removeClass( 'offline' ).addClass( 'online' );
			}
			else {
				$( '#publish-state', $( '#' + windowID ) ).removeClass( 'timecontrol' ).removeClass( 'online' ).addClass( 'offline' );
			}

			mpublish.change( function ()
			{
				var selectedState = $( this ).find( ':selected' ).val();

				if ( selectedState != 2 ) {
					timeControll.hide();
				}
				else {
					timeControll.show();
				}

				var windowID = $( this ).attr( 'window' );

				if ( selectedState == 2 ) {
					$( '#publish-state', $( '#' + windowID ) ).removeClass( 'online' ).removeClass( 'offline' ).addClass( 'timecontrol' );
				}
				else if ( selectedState == 1 ) {
					$( '#publish-state', $( '#' + windowID ) ).removeClass( 'timecontrol' ).removeClass( 'offline' ).addClass( 'online' );
				}
				else {
					$( '#publish-state', $( '#' + windowID ) ).removeClass( 'timecontrol' ).removeClass( 'online' ).addClass( 'offline' );
				}
			} );
		},
		/**
		 * Custom form tools
		 */

		doValidate: function ( mode, value )
		{
			switch ( mode ) {
				case 'mail':
					return value.match( /^((([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*)|((\x22)((((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(([\x01-\x08\x0b\x0c\x0e-\x1f\x7f]|\x21|[\x23-\x5b]|[\x5d-\x7e]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(\\([\x01-\x09\x0b\x0c\x0d-\x7f]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))))*(((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(\x22)))@((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?$/i );
					break;

				case 'url':
					return value.match( /^(https?|ftp):\/\/(((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:)*@)?(((\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5]))|((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?)(:\d*)?)(\/((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)+(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*)?)?(\?((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|[\uE000-\uF8FF]|\/|\?)*)?(\#((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|\/|\?)*)?$/i );
					break;

				case 'alphanum':
					return value.match( /^[0-9a-zA-ZäüöÄÜÖß]+$/ );
					break;

				case 'alpha':
					return value.match( /^[a-zA-ZäüöÄÜÖß]+$/ );
					break;

				case 'number':
					return value.match( /^[\-\+]?(([0-9]+)([\.,]([0-9]+))?|([\.,]([0-9]+))?)$/ );
					break;

				case 'integer':
					return value.match( /^[\-\+]?\d+$/ );
					break;
			}
		},
		validation: function ( formID, jqElement, returnOnly )
		{
			var classNames = jqElement.attr( 'class' ), formData = $( '#' + formID ).data( 'formConfig' ), error = false;
			if ( formData && classNames ) {
				if ( classNames.match( /require/i ) ) {

					var value = jqElement.val(), isTextarea = jqElement.is( 'textarea' );
					var fixName = jqElement.attr( 'name' ).replace( '[', '-' ).replace( ']', '-' );

					if ( value && value.length ) {

						var message = false;

						if ( classNames.match( /(e)mail/i ) && !Form.doValidate( 'mail', value ) ) {
							message = cmslang.validation_invalid_email;
						}
						else if ( classNames.match( /integer/i ) && !Form.doValidate( 'integer', value ) ) {
							message = cmslang.validation_invalid_integer;
						}
						else if ( classNames.match( /number/i ) && !Form.doValidate( 'number', value ) ) {
							message = cmslang.validation_invalid_number;
						}
						else if ( classNames.match( /alpha/i ) && !Form.doValidate( 'alpha', value ) ) {
							message = cmslang.validation_invalid_alpha;
						}
						else if ( classNames.match( /alphanum/i ) && !Form.doValidate( 'alphanum', value ) ) {
							message = cmslang.validation_invalid_alphanum;
						}
						else if ( classNames.match( /url/i ) && !Form.doValidate( 'url', value ) ) {
							message = cmslang.validation_invalid_url;
						}
					}
					else {
						message = cmslang.validation_invalid_input;
					}

					if ( message ) {
						error = true;

                        if ( !returnOnly ) {
                            jqElement.addClass('error');
                            var position = jqElement.position(), offset = jqElement.offset();

                            if ( $( '#' + fixName + '_' + formID ).length ) {
                                var after = jqElement.next();
                                if ( jqElement.parent().hasClass('input-tooltip') ) {
                                    after = jqElement.parent().next();
                                }
                                after.show().find( '.content' ).html( message );
                            }
                            else {

                                var after = jqElement;
                                if ( jqElement.parent().hasClass('input-tooltip') ) {
                                    after = jqElement.parent();
                                }

                                var errorContainer = $( '<div id="' + fixName + '_' + formID + '" rel="validate_' + formID + '" class="validation"><span class="content"></span></div>' );
                                errorContainer.insertAfter( after );
                                errorContainer.find( '.content' ).html( message ).show();
                            }
                        }
					}
					else
                    {
                        jqElement.removeClass('error');
						$( '#' + fixName + '_' + formID ).hide();
					}
				}
			}

			return error;
		},
		trigger: function ( element, eventName, position )
		{

			if ( eventName == 'calctime' ) {
				var value = element.val(), converted, label = '';

				if ( value ) {
					converted = convertMS( value );

					if ( converted.day && converted.day > 0 ) {
						label += converted.day + (converted.day > 1 ? ' days, ' : ' day, ');
					}

					if ( converted.hour && converted.hour > 0 ) {
						label += converted.hour + (converted.hour > 1 ? ' Hours, ' : ' Hour, ');
					}

					if ( converted.min && converted.min > 0 ) {
						label += converted.min + ' min, ';
					}

					if ( converted.sec && converted.sec > 0 ) {
						label += converted.sec + 'sec';
					}

					label = label.replace( /,\s*$/, '' );

					if ( position.toLowerCase() == 'before' ) {
						if ( element.prev().hasClass( 'input-description' ) ) {
							element.prev().text( label );
						}
						else if ( element.prev().prev().hasClass( 'input-description' ) ) {
							element.prev().prev().text( label );
						}
						else {

							if ( element.prev().hasClass( 'icon-prepend' ) ) {
								$( '<span class="input-description">' + label + '</span>' ).insertBefore( element.prev() );
							}
							else {
								$( '<span class="input-description">' + label + '</span>' ).insertBefore( element );
							}

						}

						return;
					}
					else {
						if ( element.next().hasClass( 'input-description' ) ) {
							element.next().text( label );
						}
						else if ( element.next().next().hasClass( 'input-description' ) ) {
							element.next().next().text( label );
						}
						else {

							if ( element.next().hasClass( 'tooltip' ) ) {
								$( '<span class="input-description">' + label + '</span>' ).insertAfter( element.next() );
							}
							else {
								$( '<span class="input-description">' + label + '</span>' ).insertAfter( element );
							}
						}

						return;
					}
				}
			}

			if ( position.toLowerCase() == 'before' && element.prev().hasClass( 'input-description' ) ) {
				if ( element.next().hasClass( 'tooltip' ) ) {

				}
				else {

				}
				element.prev().hasClass( 'input-description' ).hide();
			}
			else if ( position.toLowerCase() == 'after' && element.next().hasClass( 'input-description' ) ) {
				if ( element.prev().hasClass( 'icon-prepend' ) ) {

				}
				else {

				}
				element.next().hasClass( 'input-description' ).hide();
			}

		}

	};
})( window );