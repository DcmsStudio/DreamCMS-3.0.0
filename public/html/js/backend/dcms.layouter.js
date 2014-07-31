/**
 * This function opens up a window for editing a specific block item
 */


function EditBlock( id, type )
{
	id = parseInt( id );
	if ( id < 1 ) {
		return;
	}

	var tbHeight = '300';
	var tbWidth = '600';
	if ( type == 'htmlBlock' ) {
		tbHeight = '200'
	} else if ( type == 'customImage' ) {
		tbHeight = '230'
		tbWidth = '350';
	}

// create the window
	var win = $.fn.window.create( {
		uri: 'remote.php?section=layout&action=editBlock&blockid=' + id + '&type=' + type,
		title: 'Edit a Block',
		width: tbWidth,
		autoOpen: true
	} );
	// window buttons
	var submitBtn = $( '<button type="submit" style="font-weight: bold;">Save &amp; Add</button>' );
	var cancelBtn = $( '<button type="button" style="float: left;">Cancel</button>' );
	// set button events
	submitBtn.bind( 'click', submitBlock );
	cancelBtn.bind( 'click', function ()
	{
		win.close();
	} );
	// set the buttons and open it
	win.buttons( submitBtn.add( cancelBtn ) );
}
function DeleteBlock( deleteObj )
{

	var id = SiteLayout.RemoveCopyName( $( deleteObj ).parents( '.layoutListsMenusItem' ).attr( 'id' ) );
	var sectionId = $( deleteObj ).parents( '.layoutListsAccordian' ).attr( 'id' );
	var blockName = $( deleteObj ).parent().text();
	blockName = blockName.replace( /^\s+|\s+$/g, '' );
	if ( id.substr( 0, 4 ) == 'list' ) {
		if ( sectionId == 'layoutListsMenus' ) {
			if ( confirm( 'The \'%s\' menu/list will be removed from all web pages and deleted. Are you sure you want to continue? Click OK to confirm.'.replace( '%s', blockName ) ) ) {
				SiteLayout.HasMadeChanges = true;
				$.getJSON( 'remote.php?section=layout&action=deletelist&listid=' + id, function ( json )
				{
					if ( json.success ) {
						$( '#outerLayoutsContainer .' + id ).remove();
						$( '#MainMessage' ).successMessage( 'The block \'%s\' has been successfully removed from all web pages and deleted.'.replace( '%s', blockName ) );
					} else {
						$( '#MainMessage' ).errorMessage( 'Unable to delete the \'%s\' block.'.replace( '%s', blockName ) );
					}

					SiteLayout.RefreshOverlays();
				} );
			}
		} else {
			if ( confirm( "Are you sure you want to remove this list? Remember, you can re-add it later from the 'Menus & Lists' section on the left.\r\n\r\nClick OK to confirm this list should be removed.".replace( '%s', blockName ) ) ) {
				$( deleteObj ).parents( '.layoutListsMenusItem' ).remove();
			}
		}
	} else {
		if ( sectionId == 'layoutListsSaved' ) {
//	block is being deleted from the saved lists
			if ( confirm( "The \'%s\' block will be removed from all web pages and deleted. Are you sure you want to continue? Click OK to confirm".replace( '%s', blockName ) ) ) {
				SiteLayout.HasMadeChanges = true;
				$.getJSON( 'remote.php?section=layout&action=deleteblock&blockid=' + id, function ( json )
				{
					if ( json.success ) {
						$( '#outerLayoutsContainer .' + id ).remove();
						$( '#MainMessage' ).successMessage( 'The block \'%s\' has been successfully removed from all web pages and deleted.'.replace( '%s', blockName ) );
					} else {
						$( '#MainMessage' ).errorMessage( 'Unable to delete the \'%s\' block.'.replace( '%s', blockName ) );
					}

					SiteLayout.RefreshOverlays();
				} );
			}
		} else {
//	block is being deleted from the layout
			if ( confirm( "Are you sure you want to remove this block? Remember, you can re-add it later from the 'Saved Blocks' section on the left.\r\n\r\nClick OK to confirm this block should be removed.".replace( '%s', blockName ) ) ) {
				$( deleteObj ).parents( '.layoutListsMenusItem' ).remove();
			}
		}
	}
}

var dropped = false;
var draggable_sibling;
var droppeds = 0;
function Layouter()
{

	var xself = this;

	return {
		layoutContainer: null,
		// col3 is middle
		// col2 is right
		// col1 is left
		columnorder2: {
			0: ['left', 'middle'],
			1: ['middle', 'left'],
			2: ['left', 'middle'],
			3: ['middle', 'left']
		},
		columnorder3: {
			0: ['left', 'middle', 'right'],
			1: ['right', 'middle', 'left'],
			2: ['left', 'right', 'middle'],
			3: ['middle', 'right', 'left'],
			4: ['right', 'left', 'middle'],
			5: ['middle', 'left', 'right']
		},
		dropelements: {
			dp_5050: '<div class="subcolumns itemBox cbox" id="xxx1">\n\t<div class="c50l" id="xxx2">xxx0</div>\n\t<div class="c50r" id="xxx3">xxx0</div>\n</div>',
			dp_3366: '<div class="subcolumns itemBox cbox" id="xxx1"><div class="c33l" id="xxx2">xxx0</div><div class="c66r" id="xxx3">xxx0</div></div>',
			dp_6633: '<div class="subcolumns itemBox cbox" id="xxx1"><div class="c66l" id="xxx2">xxx0</div><div class="c33r" id="xxx3">xxx0</div></div>',
			dp_3862: '<div class="subcolumns itemBox cbox" id="xxx1"><div class="c38l" id="xxx2">xxx0</div><div class="c62r" id="xxx3">xxx0</div></div>',
			dp_6238: '<div class="subcolumns itemBox cbox" id="xxx1"><div class="c62l" id="xxx2">xxx0</div><div class="c38r" id="xxx3">xxx0</div></div>',
			dp_2575: '<div class="subcolumns itemBox cbox" id="xxx1"><div class="c25l" id="xxx2">xxx0</div><div class="c75r" id="xxx3">xxx0</div></div>',
			dp_7525: '<div class="subcolumns itemBox cbox" id="xxx1"><div class="c75l" id="xxx2">xxx0</div><div class="c25r" id="xxx3">xxx0</div></div>',
			dp_3333: '<div class="subcolumns itemBox cbox" id="xxx1"><div class="c33l" id="xxx2">xxx0</div><div class="c33l" id="xxx3">xxx0</div><div class="c33r" id="xxx4">xxx0</div></div>',
			dp_4425: '<div class="subcolumns itemBox cbox" id="xxx1"><div class="c25l" id="xxx2">xxx0</div><div class="c25l" id="xxx3">xxx0</div><div class="c25l" id="xxx4">xxx0</div><div class="c25r" id="xxx5">xxx0</div></div>',
			dp_1221: '<div class="subcolumns itemBox cbox" id="xxx1"><div class="c25l" id="xxx2">xxx0</div><div class="c50l" id="xxx3">xxx0</div><div class="c25r" id="xxx4">xxx0</div></div>',
			dp_1122: '<div class="subcolumns itemBox cbox" id="xxx1"><div class="c25l" id="xxx2">xxx0</div><div class="c25l" id="xxx3">xxx0</div><div class="c50r" id="xxx4">xxx0</div></div>',
			dp_2211: '<div class="subcolumns itemBox cbox" id="xxx1"><div class="c50l" id="xxx2">xxx0</div><div class="c25l" id="xxx3">xxx0</div><div class="c25r" id="xxx4">xxx0</div></div>',
			dp_subc: '<div class="subc" id="xxx1"><!-- Insert your subtemplate content here--></div>',
			dp_subcl: '<div class="subcl" id="xxx1"><!-- Insert your subtemplate content here--></div>',
			dp_subcr: '<div class="subcr" id="xxx1"><!-- Insert your subtemplate content here--></div>'
		},
		generatedCssCode: '',
		/**
		 *  config
		 */
		savedItems: '',
		savedList: '',
		templateColumns: '',
		nextDynID: null,
		selectedLayouts: '',
		savedSubColLayout: '',
		defaults: {
			doctype: 0,
			div_page: true,
			div_header: true,
			div_nav: true,
			div_teaser: false,
			div_teaser_position: '',
			div_footer: true,
			div_topnav: 0,
			template: 3,
			layout_align: 2,
			content_columns: 2,
			content_2col_order: 0,
			content_3col_order: 0,
			left_width: "25%",
			center_width: "50%",
			right_width: "25%",
			margin_left: "25%",
			margin_right: "25%",
			margin_left_ie: "25%",
			margin_right_ie: "25%",
			layout_width_unit: "%",
			left_unit: "%",
			center_unit: "%",
			right_unit: "%",
			lunit_equal: false,
			cunit_equal: false,
			layout_width: "auto",
			layout_minwidth: "740px",
			layout_maxwidth: "90em",
			dynamic_id: 1,
			user_id: 0,
			menu_template: 0,
			gfxborder: 0,
			column_divider: 0,
			ie_minmax: 1
		},
		settings: {
			layoutID: 0
		},
		draggable_elements: {
			connectToSortable: '#layoutsContainer div.mainBox,.layoutBoxCustomTop div.mainBox,.layoutBoxCustomBottom div.mainBox,.sortableSubCols,.subsort',
			zIndex: 999999,
			helper: 'clone',
			revert: 'invalid',
			forcePlaceholderSize: true,
			forceHelperSize: true,
			opacity: 0.7,
			scroll: false,
			start: function ( event, ui )
			{
				xself.disableSortableContents();
				$( '.ui-sortable', $( '#' + Win.windowID ) ).sortable( {
					disabled: true
				} );
			},
			stop: function ( event, ui )
			{
//alert( 'draggable_elements stop: ' + $(ui.helper).parent().html() );
				$( '.ui-sortable', $( '#' + Win.windowID ) ).sortable( {
					disabled: false
				} );
				xself.enableSortableContents();
			}

		},
		disableSortableContainers: function ()
		{
			$( '#layoutsContainer div.mainBox,.layoutBoxCustomTop div.mainBox,.layoutBoxCustomBottom div.mainBox,.sortableSubCols,.subsort,.subtemplate,.contentcolumn', $( '#' + Win.windowID ) ).sortable( {
				disables: true
			} );
		},
		enableSortableContainers: function ()
		{
			$( '#layoutsContainer div.mainBox,.layoutBoxCustomTop div.mainBox,.layoutBoxCustomBottom div.mainBox,.sortableSubCols,.subsort,.subtemplate,.contentcolumn', $( '#' + Win.windowID ) ).sortable( {
				disables: false
			} );
		},
		disableSortableContents: function ()
		{
			$( '.cbox' ).sortable( {
				disables: true
			} );
		},
		enableSortableContents: function ()
		{
			$( '.cbox', $( '#' + Win.windowID ) ).sortable( {
				disables: false
			} );
		},
		/**
		 * Content Box Droppables
		 * @type type
		 */
		droppables: {
			connectToSortable: ".sortzone",
			accept: '.cbox,.nosortables,.dropaccept,.tmpElement,li.m',
			tolerance: 'pointer',
			greedy: true,
			addClasses: true,
			placeholder: "ui-sortable-placeholder",
			cancel: '.contentPlaceholder',
			forcePlaceholderSize: true,
			forceHelperSize: true,
			scroll: false,
			appendTo: 'body',
			over: function ( event, ui )
			{
				dropToList = $( this ).parents().find( '.dropzonehover:first' );
			},
			drop: function ( event, ui )
			{
				//   $('#layoutsContainer img.dropaccept').remove()
				// $(ui.item).appendTo( $('.dropzoneactive') );

				alert( 'Drop To List: ' + $( dropToList ).html() );
				//alert('drop');
				//$('.dropzoneactive').removeClass('dropzoneactive');
			},
			out: function ( event, ui )
			{
				console.log( 'droppables out event updateDBSubCols' );
				$( '.dropzoneactive', $( '#' + Win.windowID ) ).removeClass( 'dropzoneactive' );
				xself.updateDBSubCols( $( ui.item ).parents( '.mainBox:first' ) );
			}
		},
		/**
		 * Item Dragables
		 * @type type
		 */
		contentDraggableOptions: {
			connectToSortable: '.sortzone:not(.subcolumns)',
			tolerance: 'pointer',
			zIndex: 99990,
			helper: 'clone',
			revert: 'invalid',
			opacity: 0.8,
			scroll: false,
			greedy: true,
			placeholder: "ui-sortable-placeholder",
			cancel: '.contentPlaceholder',
			forceHelperSize: true,
			forcePlaceholderSize: true,
			appendTo: 'body',
			cursorAt: {
				top: 3,
				left: 3
			},
			start: function ( event, ui )
			{
				$( ui.item ).addClass( 'drag' );
			},
			stop: function ( event, ui )
			{
				$( ui.helper ).removeClass( 'drag' );
				// Layouter.insertSubCols(event, ui);
			}
		},

		/**
		 * Column Sortable
		 * @type type
		 */
		columsSortableOptions: {
			connectWith: ".sortzone",
			placeholder: "ui-sortable-placeholder",
			accept: 'li.m,li.subtemplate,.itemBox:not(.dum),li.m,.tmpElement',
			items: ".contentcolumn,li.subtemplate,.itemBox:not(.dum),li.m,.tmpElement",
			helper: 'clone',
			// cursor: 'move',
			zIndex: 9999,
			opacity: 0.8,
			forceHelperSize: false,
			forcePlaceholderSize: true,
			dropOnEmpty: false,
			distance: 1,
			cancel: '.contentPlaceholder',
			revert: 'invalid',
			tolerance: 'intersect',
			scroll: false,
			handler: '.layoutmenu',
			appendTo: '.isWindowContainer.active',
			containment: "window",
			cursorAt: {
				top: 0,
				left: 0
			},
			start: function ( event, ui )
			{
				if ( $( ui.item ).hasClass( 'dum' ) || $( ui.item ).hasClass( 'layoutmenu' ) ) {
					return false;
				}

				if ( $( ui.item ).parents( '.subcolumns:first' ).length == 1 ) {
					$( ui.item ).parents( '.subcolumns:first' ).sortable( 'disable' );
				}

				document.body.style.cursor = 'move';
			},
			stop: function ( event, ui )
			{
				document.body.style.cursor = 'auto';
				var self = this;
				if ( $( ui.item ).parents( '.subcolumns:first' ).length == 1 ) {
					$( ui.item ).parents( '.subcolumns:first' ).sortable( 'disable' );
				}

				if ( $( ui.item ).hasClass( 'dum' ) || $( ui.item ).hasClass( 'layoutmenu' ) || $( ui.item ).hasClass( 'new-item' ) ) {
					alert( 'columsSortableOptions stop return' );
					return false;
				}

				// is existing item an will move only between subcols or root sections
				if ( $( ui.item ).hasClass( 'moving' ) && event.originalEvent.type == 'mouseup' ) {
					var to = ($( this ).hasClass( '.mainBox' ) ? $( this ) : $( this ).parents( '.mainBox:first' ));
					$( ui.item ).removeClass( 'moving' );
					//event.preventDefault();
					//event.stopPropagation();

					setTimeout( function ()
					{
						$( '#' + Win.windowID ).find( '.do-remove-draggable' ).remove();
						$( to ).find( '.moving' ).removeClass( 'moving' );
						//$(ui.item).draggable('disable');

						// now save the new html code to database
						xself.updateDBSubCols( to );
					}, 80 );
					return;
				}

				if ( $( ui.item ).hasClass( 'subtemplate' ) && $( ui.item ).hasClass( 'new-item' ) ) {
					// insert new subcolums
					var mainBox = $( ui.item ).parents( '.mainBox:first' );
					xself.insertSubCols( event, ui, function ()
					{

						// now save the new html code to database
						if ( mainBox.length ) {
							xself.updateDBSubCols( mainBox );
						}

						// add dummys
						xself.addDummys();
						// refresh sortables
						xself.initSortables();
					} );
				}
				else {



					// insert new content box
					if ( $( ui.item ).hasClass( 'new-item' ) ) {
						console.log( '// insert new content box $(ui.item).hasClass(\'new-item\')' );

						var to = ($( this ).hasClass( '.mainBox' ) ? $( this ) : $( this ).parents( '.mainBox:first' ));
						var inner = $( ui.item ).get( 0 ).innerHTML;
						var itemClass = $( ui.item ).attr( 'class' ), rel = $( ui.item ).attr( 'rel' );
						var item = $( '<div>' ).attr( 'id', $( ui.item ).attr( 'newid' ) );
						if ( rel ) {
							item.attr( 'rel', rel );
						}

						if ( typeof itemClass != 'undefined' ) {
							item.addClass( itemClass );
						}

						item.removeClass( 'new-item ui-draggable' );
						item.addClass( 'cbox' ).addClass( 'itemBox' ).removeClass( 'fromModulList' ).removeClass( 'tmpElement' );
						item.append( $( '<div>' ).addClass( 'contentbox-menu' ).css( {
							height: '22px'
						} ).html( inner ) );

						item.append( $( '<div>' ).addClass( 'contentbox-content' ).text( 'Insert Content here...' ) );

						// replace ui item
						$( ui.item ).replaceWith( item );

						$( item ).parent().find( 'li.org' ).remove();

						// add the item and update the dataid
						xself.addBlockToSection( $( item ).find( '.contentbox-menu' ), to, function ()
						{

							// add dummys
							xself.addDummys();
							// refresh sortables
							xself.initSortables();
							// now save the new html code to database
							xself.updateDBSubCols( to );
						} );
					}
				}
			}
		},
		addDummys: function ()
		{

			this.context.find( '.mainBox' ).each( function ()
			{
				if ( $( this ).find( '.itemBox' ).length == 0 ) {
					$( this ).empty().append( $( '<div>' ).addClass( 'itemBox' ).addClass( 'dum' ) );
				}
			} );

			this.context.find( '.subsort' ).each( function ()
			{
				if ( $( this ).find( '.itemBox' ).length == 0 ) {
					$( this ).empty().append( $( '<div>' ).addClass( 'itemBox' ).addClass( 'dum' ) );
				}
				else {
					$( this ).find( '.dum' ).remove();
				}
			} );
		},
		/**
		 * Set sortable events
		 * @returns {undefined}
		 */
		initSortables: function ()
		{
			this.addDummys();

			$( '#layoutBoxTop,#layoutBoxCustomTop,#layoutBoxLeft,#layoutBoxRight,#layoutBoxMiddle,#layoutBoxCustomBottom,#layoutBoxBottom,.subcolumns.itemBox .allowSubCols', this.context ).addClass( 'sortzone' );
			var stopDragNew = false, s = this;

			$( '.sortzone', this.context ).filter( "ui-sortable" ).sortable( 'destroy' );

			$( '.sortzone', this.context ).sortable( {
				connectWith: ".sortzone:not(.subcolumns)",
				accept: '.itemBox:not(.itemBox.dum)',
				zIndex: 9999,
				opacity: 0.9,
				cancel: '.contentPlaceholder,.dum',
				items: '.itemBox',
				// revert: 'invalid',
				tolerance: 'intersect',
				forceHelperSize: true,
				forcePlaceholderSize: true,
				// Need to drag across the entire window. Overflow issue.
				appendTo: "body",
				containment: "window",
				scroll: false,
				placeholder: "ui-sortable-placeholder",
				handle: ".layoutmenu,.contentbox-menu",
				helper: "clone",
				sort: function ()
				{
					// gets added unintentionally by droppable interacting with sortable
					// using connectWithSortable fixes this, but doesn't allow you to customize active/hoverClass options
					$( this ).removeClass( "ui-state-default" );
				}

				// End Overflow issue.
			} ).disableSelection();

			var $originalDragElement = null, stop = false;

			$( '#layoutBoxTop,#layoutBoxCustomTop,#layoutBoxLeft,#layoutBoxRight,#layoutBoxMiddle,#layoutBoxCustomBottom,#layoutBoxBottom,.subcolumns.itemBox .allowSubCols', this.context )
				.filter( "ui-droppable" )
				.droppable( 'destroy' );

			$( '#layoutBoxTop,#layoutBoxCustomTop,#layoutBoxLeft,#layoutBoxRight,#layoutBoxMiddle,#layoutBoxCustomBottom,#layoutBoxBottom,.subcolumns.itemBox .allowSubCols', this.context ).droppable( {
				tolerance: "pointer",
				accept: ".itemBox:not(.subcolumns),li.m,li.subtemplate",
				over: function ( e, ui )
				{
					$( this ).parent().addClass( 'drop-hover' );
					$toContainer = $( this );
				},
				out: function ()
				{

					$toContainer = null;
					s.context.find( '.drop-hover' ).removeClass( 'drop-hover' );

				},
				drop: function ( e, ui )
				{
					if ( stop == true ) {
						s.context.find( '.drop-hover' ).removeClass( 'drop-hover' );
						return;
					}

					var _self = this;

					var toContainer = null;
					var placeholder = null;
					var org = $originalDragElement;
					if ( $( this ).find( '.ui-sortable-placeholder' ).length == 1 ) {
						placeholder = $( this ).find( '.ui-sortable-placeholder' );
						placeholder.hide();
						if ( $( this ).find( '.ui-sortable-placeholder' ).parent().hasClass( 'mainBox' ) ) {
							toContainer = $( this ).find( '.ui-sortable-placeholder' ).parent();
						}
						else {
							toContainer = $( this );
						}
					}

					s.context.find( '.drop-hover' ).removeClass( 'drop-hover' );

					// insert items/columns
					if ( ui.helper.hasClass( 'new-item' ) ) {
						if ( e.originalEvent.type == 'mouseup' && !stop ) {
							var newID = $( ui.helper ).attr( 'newid' );

							if ( $( ui.helper ).hasClass( 'subtemplate' ) ) {
								console.log( 'e.originalEvent.type == \'mouseup\' && !stop $(ui.helper).hasClass(\'subtemplate\')' );
								// insert new subcolums
								var mainBox = $( this ).hasClass( 'mainBox' ) ? $( this ) : $( this ).parents( '.mainBox:first' );
								//$( ui.helper ).removeClass('m');
								ui.item = $( ui.draggable );
								stop = true;

								$( ui.item ).removeClass( 'ui-state-disabled ui-sortable-helper' );

								s.insertSubCols( e, ui, function ()
								{
									delete ui.item;

									setTimeout( function ()
									{
										$( ui.draggable ).remove();
										$( ui.helper ).remove();

										$( mainBox ).find( '.ui-sortable-placeholder,li.m' ).remove();

										// now save the new html code to database
										if ( mainBox.length ) {
											s.updateDBSubCols( mainBox );
										}

										// refresh sortables
										s.initSortables();
									}, 10 );

									return;
								} );
							}
							else if ( $( ui.helper ).hasClass( 'm' ) && newID && !stop ) {
								stop = true;

								console.log( 'e.originalEvent.type == \'mouseup\' && !stop $(ui.helper).hasClass(\'m\') && newID && !stop!!!' );

								var newContent = $( ui.helper ).clone( false, false );
								var liHidde = ui.draggable.parent().find( 'li.m' );
								var placeholderBase = ui.draggable.parent().find( '.ui-sortable-placeholder' );
								var placeholder = s.context.find( '.ui-sortable-placeholder' );

								if ( placeholderBase.length == 1 ) {
									var to = ($( this ).hasClass( '.mainBox' ) ? $( this ) : $( this ).parents( '.mainBox:first' ));

									if ( placeholder.length > 1 ) {
										// console.log( 'Placeholders: ' + placeholder.length + '!!!' );
										placeholder.each( function ()
										{
											if ( $( this ) !== placeholderBase ) {
												$( this ).remove();
											}
										} );
									}

									newContent.attr( 'id', $( newContent ).attr( 'newid' ) ).removeAttr( 'newid' );

									//newContent.removeAttr('style').removeAttr('newid').removeAttr('class').addClass('cbox itemBox m').insertAfter(placeholderBase);
									newContent.insertAfter( placeholderBase );
									placeholderBase.hide();
									newContent.show();

									s.addBlockToSection( $( newContent ).find( '.contentbox-menu' ), to.attr( 'id' ), function ()
									{
										setTimeout( function ()
										{

											placeholderBase.hide();
											newContent.removeAttr( 'style' ).removeAttr( 'newid' ).removeAttr( 'class' ).addClass( 'cbox itemBox m' ).insertAfter( placeholderBase );

											$( liHidde ).replaceWith( $( newContent ).removeAttr( 'style' ).removeAttr( 'newid' ).removeAttr( 'class' ).addClass( 'cbox itemBox m' ) );
											$( _self ).sortable( 'cancel' );

											$( newContent ).parent().find( 'li.m' ).remove();

											// now save the new html code to database
											s.updateDBSubCols( to );

											// refresh sortables
											s.initSortables();

											placeholder = null;
										}, 80 );
										//   $(ui.helper).remove();
										//   $(ui.draggable).remove();
										//   placeholderBase.remove();

										return false;
									} );

								}
								else if ( placeholderBase.length == 0 ) {
									/**
									 * Cancel Event
									 */


									$( _self ).filter('ui-sortable').sortable( 'cancel' );
									$( _self ).filter('ui-droppable').droppable( 'cancel' );
									$( _self ).filter('ui-draggable').draggable( 'cancel' );

									$( ui.helper ).remove();
									$( ui.draggable ).remove();

									return;
								}

								return;
							}

							$( this ).parents( '.drop-hover' ).removeClass( 'drop-hover' );
						}
					}

					// move
					if ( e.originalEvent.type == 'mouseup' && !ui.draggable.hasClass( 'new-item' ) && placeholder != null && placeholder.length == 1 ) {
						if ( toContainer.find( '.itemBox:not(.dum)' ).length ) {
							toContainer.removeClass( 'drop-hover' ).parents( '.drop-hover' ).removeClass( 'drop-hover' );
							if ( org !== null ) {
								console.log( 'e.originalEvent.type == \'mouseup\' && $(ui.helper).hasClass(\'new-item\') && placeholder != null && placeholder.length == 1 org !== null' );
								$( org ).removeClass( 'ui-state-disabled ui-sortable-helper' );
								var placeholder = $( ui.draggable ).parent().find( '.ui-sortable-placeholder:first' );
								if ( placeholder.length == 1 ) {
									$( org ).insertBefore( placeholder );
									$( org ).show();
									ui.draggable.hide();
									ui.helper.hide();
									setTimeout( function ()
									{

										placeholder.parent().find( '.dum' ).remove();
										ui.draggable.remove();
									}, 50 );
									//ui.helper.remove();
									// placeholder.remove();
									// placeholder = toContainer = null;
									$originalDragElement = null;
								}
								else {
									$( org ).show();
								}
							}
							else {
								if ( placeholder != null && placeholder.length == 1 ) {
									placeholder.show();
									console.log( 'e.originalEvent.type == \'mouseup\' && $(ui.helper).hasClass(\'new-item\') && placeholder != null && placeholder.length == 1 org == null' );
									//The following block works when helper="original" in the sortable options

									ui.draggable.show( 0, function ()
									{
										$( ui.draggable ).removeClass( 'ui-state-disabled ui-sortable-helper' );
										placeholder.after( $( ui.draggable ) );
										placeholder.hide();
										// placeholder.remove();
										$( ui.draggable ).show();
										$toContainer = null;
										// update the database here?

									} );
								}
							}
						}
						else {
							// is empty container
							toContainer.removeClass( 'drop-hover' ).parents( '.drop-hover' ).removeClass( 'drop-hover' );
							if ( org !== null ) {
								placeholder = $( ui.draggable ).parent().find( '.ui-sortable-placeholder:first' );

								if ( placeholder != null && placeholder.length == 1 ) {
									$( org ).removeClass( 'ui-state-disabled ui-sortable-helper' );
									$( org ).insertBefore( placeholder );
									$( org ).show();
									placeholder.parent().find( '.dum' ).remove();
									ui.draggable.hide();
									//ui.draggable.remove();
									ui.helper.hide();
									// placeholder.remove();
									placeholder = toContainer = null;
									$originalDragElement = null;
								}
								else {
									$( org ).show();
								}
							}
							else {
								if ( placeholder != null && placeholder.length == 1 ) {
									//The following block works when helper="original" in the sortable options
									ui.draggable.show();
									ui.draggable.hide( 1, function ()
									{
										$( this ).before( placeholder );
										placeholder.remove();
										$( this ).removeClass( 'ui-state-disabled ui-sortable-helper' ).show();

										$toContainer = null;
										// update the database here?

									} );
								}
							}
						}
					}

				}

			} );

			$( '.mainBox', this.context ).find( '.itemBox' ).each( function ()
			{
				if ( !$( this ).hasClass( 'subcolumns' ) && !$( this ).hasClass( 'dum' ) ) {
					$( this ).filter( "ui-draggable" ).draggable( 'destroy' );
					$( this ).draggable( {
						appendTo: "body",
						containment: "window",
						zIndex: 9999,
						connectToSortable: ".sortzone:not(.subcolumns)",
						helper: "clone",
						revert: 'invalid',
						scroll: false,
						start: function ( e, ui )
						{
							$originalDragElement = $( e.target );
							$( e.target ).addClass( 'move' ).hide();
							stop = false;
						},
						stop: function ( e, ui )
						{
							s.context.find( '.drop-hover' ).removeClass( 'drop-hover' );
							$( e.target ).removeClass( 'move' ).show();
							$originalDragElement = null;
						}
					} ).draggable( 'disable' );
				}
				else if ( $( this ).hasClass( 'subcolumns' ) ) {
					$( this ).filter( "ui-draggable" ).draggable( 'destroy' );
					$( this ).draggable( {
						appendTo: "body",
						containment: "window",
						zIndex: 9999,
						connectToSortable: ".sortzone.mainBox",
						helper: "clone",
						revert: 'invalid',
						scroll: false,
						start: function ( e, ui )
						{
							$originalDragElement = $( e.target );
							$( e.target ).addClass( 'move' ).hide();
							stop = false;
						},
						stop: function ( e, ui )
						{
							s.context.find( '.drop-hover' ).removeClass( 'drop-hover' );
							$( e.target ).removeClass( 'move' ).show();
							$originalDragElement = null;
						}
					} ).draggable( 'disable' );
				}
			} );

			var dragOpts = {
				appendTo: "body",
				containment: "window",
				zIndex: 9999,
				connectToSortable: ".sortzone:not(.subcolumns)",
				forceHelperSize: true,
				revert: 'invalid',
				scroll: false,
				helper: function ( e, ui )
				{
					var inner = $( this ).get( 0 ).innerHTML;
					var rel = $( this ).attr( 'rel' );
					var item = $( '<div>' ).attr( 'newid', $( this ).attr( 'id' ) );

					if ( rel ) {
						item.attr( 'rel', rel );
					}

					if ( $( this ).attr( 'dataid' ) ) {
						item.attr( 'dataid', $( this ).attr( 'dataid' ) );
					}

					// item.removeClass('new-item ui-draggable');
					item.addClass( 'cbox m' ).addClass( 'itemBox' ).removeClass( 'fromModulList' ).removeClass( 'tmpElement' );
					item.append( $( '<div>' ).addClass( 'contentbox-menu' ).css( {
						height: '22px'
					} ).html( inner ) );

					item.append( $( '<div>' ).addClass( 'contentbox-content' ).text( 'Insert Content here...' ) );

					return item;
				},
				start: function ( e, ui )
				{
					$( '#layoutBoxTop,#layoutBoxCustomTop,#layoutBoxLeft,#layoutBoxRight,#layoutBoxMiddle,#layoutBoxCustomBottom,#layoutBoxBottom,.subcolumns.itemBox .allowSubCols', s.context )
						.filter( "ui-droppable" ).droppable( 'enable' );

					$( '#layoutBoxTop,#layoutBoxCustomTop,#layoutBoxLeft,#layoutBoxRight,#layoutBoxMiddle,#layoutBoxCustomBottom,#layoutBoxBottom,.subcolumns.itemBox .allowSubCols', s.context )
						.filter( "ui-sortable" ).sortable( 'disable' );

					//   ui.draggable.attr('newid', $(e.target).attr('id')).addClass('new-item dragga');
					ui.helper.attr( 'newid', $( e.target ).attr( 'id' ) ).addClass( 'new-item help' );

					$( e.target ).addClass( 'new-item org' ).attr( 'newid', $( e.target ).attr( 'id' ) );
					stop = false;
					stopDragNew = false;
				},
				stop: function ( e, ui )
				{
					s.context.find( '.drop-hover' ).removeClass( 'drop-hover' );

					$( '#layoutBoxTop,#layoutBoxCustomTop,#layoutBoxLeft,#layoutBoxRight,#layoutBoxMiddle,#layoutBoxCustomBottom,#layoutBoxBottom,.subcolumns.itemBox .allowSubCols', s.context )
						.filter( "ui-droppable" ).droppable( 'disable' );

					$( '#layoutBoxTop,#layoutBoxCustomTop,#layoutBoxLeft,#layoutBoxRight,#layoutBoxMiddle,#layoutBoxCustomBottom,#layoutBoxBottom,.subcolumns.itemBox .allowSubCols', s.context )
						.filter( "ui-sortable" ).sortable( 'enable' );

					//    ui.helper.remove();
					//    ui.draggable.remove();
				}
			};

			this.Builder.find( '#content-blocks li,#content-modules li,#content-saved-blocks li' ).filter( "ui-draggable" ).draggable( 'destroy' );
			this.Builder.find( '#content-blocks li,#content-modules li,#content-saved-blocks li' ).draggable( dragOpts );

			// options for New Subcolumns
			dragOpts.helper = 'clone';
			dragOpts.connectToSortable = $( '.sortzone', this.context ).not( '.subcolumns .allowSubCols' ); // only to te main containers not in a subcolum
			dragOpts.start = function ( e, ui )
			{
				ui.helper.attr( 'newid', $( e.target ).attr( 'id' ) ).addClass( 'new-item' );
				$( e.target ).addClass( 'new-item' );
				$( '.subcolumns.itemBox .allowSubCols' ).filter( "ui-sortable" ).sortable( 'disable' );
				stop = false;
			};

			dragOpts.stop = function ( e, ui )
			{
				s.context.find( '.drop-hover' ).removeClass( 'drop-hover' );
				$( '.subcolumns.itemBox .allowSubCols' ).filter( "ui-sortable" ).sortable( 'enable' );
			};

			this.Builder.find( 'li.subtemplate' ).filter( "ui-draggable" ).draggable( 'destroy' );
			this.Builder.find( 'li.subtemplate' ).draggable( dragOpts );

			// reenable editing subcolumns
			$( '.subcolumns.itemBox .layoutmenu', this.context ).each( function ()
			{
				var subcol = $( this );

				if ( $( this ).find( '.addsubcols-cancel' ).length == 1 ) {
					var btn = $( this ).find( '.addsubcols-cancel' );

					btn.parents().find( '.dropaccept' ).removeClass( 'dropzone' ).addClass( 'dropprotect' );
					btn.parents( '.ui-sortable' ).sortable( {
						disabled: true
					} );
					btn.parent().parent().addClass( 'dropaccept' ).addClass( 'dropzone' );
					btn.parent().parent().sortable( {
						disabled: false
					} );
					btn.parent().next().find( '.ui-sortable' ).sortable( {
						disabled: false
					} );

					s.enableDraggableContentItems();
				}
			} );

		},
		enableDraggableContentItems: function ()
		{
			$( '.subcolumns.itemBox', this.context ).filter( "ui-sortable" ).sortable( 'enable' );
			$( '.subcolumns.itemBox', this.context ).filter( "ui-droppable" ).droppable( 'disable' );

			$( '.subcolumns.itemBox .allowSubCols', this.context ).filter( "ui-sortable" ).sortable( 'enable' );
			$( '.subcolumns.itemBox .allowSubCols', this.context ).filter( "ui-droppable" ).droppable( 'disable' );

			// enable draggable items
			$( '.mainBox', this.context ).find( '.itemBox' ).each( function ()
			{
				if ( !$( this ).hasClass( 'subcolumns' ) && !$( this ).hasClass( 'dum' ) ) {
					$( this ).filter( "ui-draggable" ).draggable( 'enable' );
				}
			} );
			return;
			/*        $('#layoutBoxTop,#layoutBoxCustomTop,#layoutBoxLeft,#layoutBoxRight,#layoutBoxMiddle,#layoutBoxCustomBottom,#layoutBoxBottom', $('#' + Win.windowID)).sortable('disable');
			 */
			// enable draggable items
			$( '.mainBox', $( '#' + Win.windowID ) ).find( '.itemBox' ).each( function ()
			{
				if ( !$( this ).hasClass( 'subcolumns' ) && !$( this ).hasClass( 'dum' ) && $( this ).parents( '.subcolumns' ).length == 0 ) {
					$( this ).draggable( 'enable' );
				}
			} );

			$( '#layoutBoxTop,#layoutBoxCustomTop,#layoutBoxLeft,#layoutBoxRight,#layoutBoxMiddle,#layoutBoxCustomBottom,#layoutBoxBottom' ).droppable( 'enable' ).sortable( 'enable' );

			$( '.subcolumns.itemBox .allowSubCols,', $( '#' + Win.windowID ) ).each( function ()
			{
				if ( $( this ).parents( '.layoutmenu:first' ).length == 1 && $( this ).parents( '.layoutmenu:first' ).find( '.addsubcols' ).length ) {
					$( this ).droppable( 'enable' );
				}
			} );
			//$('.subcolumns.itemBox .allowSubCols', $('#' + Win.windowID)).sortable('disable').droppable('enable');
		},
		disableDraggableContentItems: function ()
		{
			$( '.subcolumns.itemBox', this.context ).filter( "ui-sortable" ).sortable( 'enable' );
			$( '.subcolumns.itemBox', this.context ).filter( "ui-droppable" ).droppable( 'enable' );


			$( '.subcolumns.itemBox .allowSubCols', this.context ).filter( "ui-sortable" ).sortable( 'enable' );
			$( '.subcolumns.itemBox .allowSubCols', this.context ).filter( "ui-droppable" ).droppable( 'enable' );

			// disable draggable items
			$( '.mainBox', this.context ).find( '.itemBox' ).each( function ()
			{
				if ( !$( this ).hasClass( 'subcolumns' ) && !$( this ).hasClass( 'dum' ) ) {
					$( this ).filter( "ui-draggable" ).draggable( 'disable' );
				}
			} );

			return;
			$( '#layoutBoxTop,#layoutBoxCustomTop,#layoutBoxLeft,#layoutBoxRight,#layoutBoxMiddle,#layoutBoxCustomBottom,#layoutBoxBottom', this.context ).sortable( 'enable' );
			/**/
				// disable draggable items
			$( '.mainBox', this.context ).find( '.itemBox' ).each( function ()
			{
				if ( !$( this ).hasClass( 'subcolumns' ) && !$( this ).hasClass( 'dum' ) ) {
					$( this ).draggable( 'disable' );
				}
			} );
			$( '#layoutBoxTop,#layoutBoxCustomTop,#layoutBoxLeft,#layoutBoxRight,#layoutBoxMiddle,#layoutBoxCustomBottom,#layoutBoxBottom', this.context ).droppable( 'disable' );
			$( '.subcolumns.itemBox .allowSubCols', this.context ).each( function ()
			{
				if ( $( this ).parents( '.layoutmenu:first' ).length == 1 && $( this ).parents( '.layoutmenu:first' ).find( '.addsubcols' ).length == 0 ) {
					$( this ).droppable( 'disable' );
				}
			} );
			//$('.subcolumns.itemBox', $('#' + Win.windowID)).sortable('destroy');

			//$('.subcolumns.itemBox .allowSubCols', $('#' + Win.windowID)).droppable('disable').sortable('enable');
		},
		/**
		 *
		 * @param integer cols
		 * @returns {unresolved}
		 */
		updateLayoutSize: function ( cols )
		{
			return;
			var innerwidth = $( '.layoutBox-inner', this.context ).innerWidth();
			var boxlwidth = $( '.leftColumns', this.context ).width();
			var boxrwidth = $( '.rightColumns', this.context ).width();
			boxlwidth = ($( '.leftColumns', this.context ).is( ':visible' ) ? parseInt( boxlwidth ) + 15 : 0);
			boxrwidth = ($( '.rightColumns', this.context ).is( ':visible' ) ? parseInt( boxrwidth ) + 15 : 0);
			$( '.middleColumns', this.context ).css( {
				'width': (innerwidth - boxlwidth - boxrwidth)
			} );
		},
		/**
		 * Init the Current Layout
		 * @returns {undefined}
		 */
		prepareCurrentLayout: function ()
		{
			var self = this;
			this.context = ( Desktop.isWindowSkin ? $( '#' + Win.windowID ) : Core.getTabContent() );
			if ( typeof this.savedList == 'object' ) {
				var inner;
				var contentPlaceholder = false;

				for ( var i in this.savedList ) {
					var data = this.savedList[i];
					var item = $.parseHTML( this.savedList[i].item ); // Jquery >= 1.9.x Patch

					if ( $( item ).hasClass( 'contentPlaceholder' ) ) {
						contentPlaceholder = true;
						inner = this.context.find( '#' + data.block + ' div.contentPlaceholder' ).html();
						this.context.find( '#' + data.block + ' div.contentPlaceholder' ).remove();
					}

					var id = $( item ).attr( 'id' );

					if ( typeof id == 'undefined' ) {
						$( item ).each( function ()
						{
							var _id = $( this ).attr( 'id' );

							if ( typeof id == 'undefined' && typeof _id != 'undefined' ) {
								id = _id;
							}
						} );
					}

					var usePrepend = false, found = false, foundcontentPlaceholder = false;

					for ( var blockname in this.savedItems ) {
						if ( data.block == blockname && typeof this.savedItems[blockname] === 'string' ) {
							console.log( 'Block:' + blockname + ' ItemID: ' + id );

							var items = this.savedItems[blockname].split( ',' );

							for ( var y in items ) {
								var itemidname = items[y];

								if ( !found && itemidname ) {
									if ( itemidname == 'contentPlaceholder' ) {
										foundcontentPlaceholder = true;
									}

									if ( itemidname == id ) {
										found = true;

										if ( foundcontentPlaceholder ) {
											usePrepend = false;
										}
										else {
											usePrepend = true;
										}
									}
								}
							}

						}
					}

					if ( usePrepend ) {
						if ( this.context.find( '#' + data.block + ' div.contentPlaceholder' ).length ) {
							$( item ).insertBefore( this.context.find( '#' + data.block + ' div.contentPlaceholder' ) );
						}
						else {
							this.context.find( '#' + data.block ).append( $( item ) );
						}
					}
					else {
						this.context.find( '#' + data.block ).append( $( item ) );
					}

				}

				if ( contentPlaceholder && typeof inner != 'undefined' && inner != '' ) {
					this.context.find( '#' + data.block + ' div.contentPlaceholder' ).empty().append( inner );
					contentPlaceholder = false;
					inner = null;
				}
			}

			this.context.find( '.cbox' ).addClass( 'itemBox' );

			var dynID = 0;

			this.context.find( '.subcolumns' ).each( function ()
			{
				if ( typeof $( this ).attr( 'id' ) === 'string' && ($( this ).attr( 'id' ).match( /^subdyn_id/g ) || $( this ).attr( 'id' ).match( /^subcolsdyn_id/g )) ) {
					dynID++;
					$( this ).attr( 'id', 'subdyn_id' + dynID ).attr( 'class', 'subcolumns itemBox' );
				}
			} );

			this.context.find( '.subcolumns' ).find( 'div' ).each( function ()
			{
				if ( typeof $( this ).attr( 'id' ) === 'string' && $( this ).attr( 'id' ).match( /^dyn_id/g ) ) {
					dynID++;
					$( this ).attr( 'id', 'dyn_id' + dynID );
				}
			} );

			// prepare subcolums
			$( '#layoutBoxTop,#layoutBoxCustomTop,#layoutBoxLeft,#layoutBoxRight,#layoutBoxMiddle,#layoutBoxCustomBottom,#layoutBoxBottom', this.context ).find( '.subcolumns' ).each( function ()
			{
				var _self = this;

				var id = $( this ).attr( 'id' );
				id = id.replace( 'subdyn_id', '' );

				$( this ).attr( 'class', 'subcolumns itemBox' );

				$( this ).find( '.allowSubCols' ).each( function ()
				{
					if ( $( this ).find( '.itemBox' ).length == 0 ) {
						$( this ).append( $( '<div/>' ).addClass( 'itemBox' ).addClass( 'dum' ) );
					}
				} );

				// root id
				var menu = '<div id="xxx9" class="layoutmenu"> <span class="more">mehr...</span><div class="submenu"></div></div>';
				var tmp = menu.replace( 'xxx9', 'menu' + id );

				$( this ).prepend( $( tmp ) );

				// create buttons to column container
				self.createAddButton( '#menu' + id );
				self.createRemoveButton( '#menu' + id );
				self.createEmptyButton( '#menu' + id );

				if ( $( '#menu' + id, self.context ).find( '.submenu div:first' ).length ) {
					$( '#menu' + id, self.context ).find( '.more' ).click( function ()
					{
						$( '.layoutmenu .submenu:visible', self.context ).hide();
						$( this ).parent().find( '.submenu:first' ).slideToggle( 50, function ()
						{

							$( this ).unbind( 'mouseleave' ).bind( 'mouseleave', function ()
							{
								$( this ).hide();
							} );
						} );
					} );
				}
				else {
					// error is empty menu
					var parentObj = $( '#menu' + id, self.context ).parent();

					if ( parentObj.attr( 'id' ) ) {
						Debug.log( 'The container "' + parentObj.attr( 'id' ) + '" is not set! It will remove.' );
					}

					$( '#menu' + id, self.context ).parent().remove();
				}

			} );

			if ( dynID > 1 ) {
				this.settings.dynamic_id = parseInt( dynID ) + 1;
			}
			else {
				this.settings.dynamic_id = 1;
			}

			// prepare items
			if ( typeof this.savedItems == 'object' ) {

				for ( var blockname in this.savedItems ) {
					if ( typeof this.savedItems[blockname] === 'string' ) {
						var items = this.savedItems[blockname].split( ',' );
						for ( var y in items ) {
							var itemidname = items[y];
							if ( typeof itemidname != 'string' || !itemidname ) {
								continue;
							}

							var element = this.context.find( '#' + itemidname );

							if ( element.length == 1 && !element.hasClass( 'subcolumns' ) && !element.hasClass( 'contentPlaceholder' ) ) {
								var type = '';
								if ( element.attr( 'id' ) ) {
									type = element.attr( 'id' ).replace( /_\d*$/g, '' );
								}

								if ( type == '' || type == 'subdyn_id' || type == 'dyn_id' ) {
									continue;
								}

								var title = element.find( 'span:first' ).clone();
								title.addClass( type );
								$( element ).removeClass( type );
								$( element ).empty().addClass( 'm' ).append(
									$( '<div>' ).addClass( 'contentbox-menu' ).css( {
										height: '22px'
									} ).append( title ) );

								$( element ).append( $( '<div>' ).addClass( 'contentbox-content' ).text( 'Insert Content here...' ) );

								self.addButtonsToItem( $( '.contentbox-menu:first', element ) );

								//blockHTML.replace('[' + itemidname + ']', element);
							}
						}
					}
				}
			}

			this.context.find( '.cbox' ).addClass( 'itemBox' );
			this.context.find( '.allowSubCols').addClass( 'dropaccept' );
		},
		setupLayouterGui: function ()
		{

			var s = this, builderTabs = this.Builder.find( '.jquery_tabs li' );

			this.Builder.addClass('popup').attr('opener', Win.windowID).find( '.tcontainer' ).hide();
			this.Builder.addClass( 'isbuilderMoved' ).unbind();
			this.Builder.draggable( {
				scroll: false,
				zIndex: 1000,
				handle: '#ui_content>h3:first'
			} ).show();

			// setup tabs
			builderTabs.each( function ()
			{

				$( this ).click( function ( e )
				{
					var self = this;
					e.preventDefault();
					$( self ).parent().find( 'li.current' ).removeClass( 'current' );
					$( self ).addClass( 'current' );
					var tabC = $( self ).attr( 'id' ).replace( 'tab', '' );

					s.Builder.find( '.tcontainer' ).hide();
					s.Builder.find( '#t_' + tabC ).show();

					return false;
				} );

			} );
			/*
			 this.Builder.find( '.switch_pin' ).click( function ( e )
			 {
			 var self = this;
			 e.preventDefault();
			 if ( $( self ).find( 'img' ).attr( 'src' ).match( /pin-out\./ ) ) {
			 $( self ).find( 'img' ).attr( 'src', $( self ).find( 'img' ).attr( 'src' ).replace( 'pin-out', 'pin-in' ) );
			 s.Builder.css( {
			 position: 'fixed',
			 zIndex: 99999
			 } );
			 }
			 else {
			 $( self ).find( 'img' ).attr( 'src', $( self ).find( 'img' ).attr( 'src' ).replace( 'pin-in', 'pin-out' ) );
			 s.Builder.css( {
			 position: 'absolute',
			 zIndex: 99999
			 } );
			 }
			 return false;
			 } );

			 */
			this.Builder.find( '.switch_pin' ).remove();
			if ( this.Builder.find( '.jquery_tabs li.current' ).length ) {
				var id = this.Builder.find( '.jquery_tabs li.current' ).attr( 'id' );
				this.Builder.find( '#t_' + id.replace( 'tab', '' ) ).show();
			}

			// Hide all the content except the first
			$( '#layout-accordion h4:gt(0)', this.Builder ).next().hide();
			$( '#layout-accordion h4:lt(1)', this.Builder ).addClass( 'active' );
			// Show the correct cursor for the links
			$( '#layout-accordion h4', this.Builder ).css( 'cursor', 'pointer' );
			$( '#layout-accordion h4', this.Builder ).click( function ()
			{

				// Get the content that needs to be shown
				var cur = $( this ).next();
				// Get the content that needs to be hidden
				var old = $( '#layout-accordion h4.active' ).next();
				// Make sure the content that needs to be shown
				// isn't already visible
				if ( cur.is( ':visible' ) )
					return false;

				// Hide the old content
				old.slideToggle( 300 );
				// Show the new content
				cur.stop().slideToggle( 300, function ()
				{
					$( this ).parent().find( '>.active' ).removeClass( 'active' );
					$( this ).prev().addClass( 'active' );
				} );
			} );

			this.Builder.find( 'input,select,textarea' ).addClass( 'nodirty' );
		},
		tinit: null,
		/**
		 * Init the Layouter
		 * @param json initData
		 * @returns {unresolved}
		 */
		initLayouter: function ( initData )
		{
			var self = this;

			if ( !$( '#' + Win.windowID ).data( 'WindowManager' ) && Desktop.isWindowSkin ) {
				this.tinit = setTimeout( function ()
				{
					self.initLayouter( initData );
				}, 10 );
			}
			else {

				clearTimeout( this.tinit );
				if ( Desktop.isWindowSkin ) {
					if ( $( '#' + Win.windowID ).data( 'WindowManager' ).get( 'layouterInited' ) ) {
						return;
					}
				}
				var self = this;
				this.settings = $.extend( {}, this.defaults, initData );

				this.context = ( Desktop.isWindowSkin ? $( '#' + Win.windowID ) : Core.getTabContent() );

				console.log( 'Init layouter' );
				this.layoutContainer = $( '#layoutsContainer' );
				$( '.layout-builder,#builder' ).hide();


				var b = $( '#builder', this.context ).addClass( 'layout-builder' ).attr( 'windowid', Win.windowID );

				if ( !Desktop.isWindowSkin ) {
                    Core.addEvent( 'onBeforeHideTabContent', function ()
                    {
                        b.hide();
                    } );
                    Core.addEvent( 'onShowTabContent', function (content, hash)
                    {
                        var c = Core.getContentTabs();
                        if ( c && c.length ) {
                            c.find( '.tabbedMenu li' ).each( function ()
                            {
                                if ( $( this ).attr( 'id' ) == 'tab_1' && $( this ).hasClass( 'actTab' ) ) {
                                    b.show();
                                }
                            } );
                        }
                    } );
					Core.addEvent( 'onBeforeClose', function ()
					{
                        b.remove();
					} );
                     /*
					$( '#builder', this.context ).appendTo( 'body' );
					*/


                    b.appendTo( $('#fullscreenContainer') );

				}
				else {
					//$( '#builder', this.context ).appendTo( this.context );
				}

				this.Builder = b;
				this.Builder.hide();

				Win.prepareWindowFormUi(this.Builder);

				// this.Builder.hide();

				if ( Desktop.isWindowSkin ) {
					this.context.css( 'overflow', 'visible' ).data( 'WindowManager' ).set( 'layouterInited', true );
					this.context.data( 'WindowManager' ).set( 'onBeforeClose', function ( event, _wm, callback )
					{

						self.context.data( 'WindowManager' ).set( 'layouterInited', false );

						$( '.layout-builder' ).each( function ()
						{

							if ( $( this ).attr( 'windowid' ) == _wm.id ) {
								$( this ).remove();

								Win.removeWindowFormUi( _wm.id );

							}

							if ( Tools.isFunction( callback ) ) {
								callback();
							}
						} );
					} );

					this.context.data( 'WindowManager' ).set( 'onBeforeReload', function ( _wm, callback )
					{

						$( '#builder', this.context ).remove();
						self.context.data( 'WindowManager' ).set( 'layouterInited', false );
						if ( Tools.isFunction( callback ) ) {
							callback();
						}
					} );

				}

				if ( $( 'link.layouterCss', $( 'head' ) ).length == 0 ) {
					$( 'head' ).append( '<link rel="stylesheet" class="layouterCss" href="public/html/css/subcols.css" type="text/css"/>' );
					$( 'head' ).append( '<link rel="stylesheet" class="layouterCss" href="public/html/css/subcolsIEHacks.css" type="text/css"/>' );
					var path = Config.get( 'backendImagePath' );
					path = path.replace( '/img/', '/css/' );
					path = path.replace( Config.get( 'portalurl' ) + '/', '' );
					$( 'head' ).append( '<link rel="stylesheet" class="layouterCss" href="public/' + path + 'dcms.layouter.css" type="text/css"/>' );
					$( 'head' ).append( '<link rel="stylesheet" class="layouterCss" href="public/' + path + 'layoutbuilder.css" type="text/css"/>' );
				}

				this.selectedLayouts = initData.selectedLayouts;
				this.templateColumns = initData.templateColumns;
				this.savedSubColLayout = initData.savedSubColLayout;

				// prepare data cache
				this.savedList = initData.savedList;
				this.nextDynID = (parseInt( initData.nextDynID ) > 0 ? parseInt( initData.nextDynID ) : 1);
				this.savedItems = initData.savedItems;

				this.prepareCurrentLayout();
				this.setupLayouterGui();

				$( '.layout-header,.layout-footer,.layout-static,.layoutBoxTop,.layoutBoxBottom,.layoutBoxCustomTop,.layoutBoxCustomBottom', this.context ).hide();
				this.updateLayoutSize();

				if ( $( '#layout-header', this.Builder ).is( ':checked' ) ) {
					$( '.layout-header,.layoutBoxTop', this.context ).show( 0,
						function ()
						{
							if ( $( this ).is( ':visible' ) ) {
								$( this ).show();
							}
						} );
				}

				if ( $( '#layout-footer', this.Builder ).is( ':checked' ) ) {
					$( '.layout-footer,.layoutBoxBottom', this.context ).show( 0, function ()
					{
						if ( $( this ).is( ':visible' ) ) {
							$( this ).show();
						}
					} );
				}

				if ( $( '#layout-customheader', this.Builder ).is( ':checked' ) ) {
					$( '.layoutBoxCustomBottom', this.context ).show( 0, function ()
					{
						if ( $( this ).is( ':visible' ) ) {
							$( this ).show();
						}
					} );
				}

				if ( $( '#layout-customfooter', this.Builder ).is( ':checked' ) ) {
					$( '.layoutBoxCustomTop', this.context ).show( 0, function ()
					{
						if ( $( this ).is( ':visible' ) ) {
							$( this ).show();
						}
					} );
				}

				if ( $( '#layout-static', this.Builder ).is( ':checked' ) ) {
					$( '.layout-static', this.context ).show();
				}

				this.guiChangedCols( 0 );
				$( '.col-left,.col-right', this.context ).hide();

				var order_3_iu_id = $( '#order_3', this.Builder ).attr( 'sb' );
				var order_2_iu_id = $( '#order_2', this.Builder ).attr( 'sb' );

				this.Builder.find( 'input[name="cols"]:checked' ).each( function ()
				{

					if ( $( this ).is( ':checked' ) ) {
						$( this ).attr( 'checked', true );

					}
					else {
						$( this ).attr( 'checked', false );
					}

					var value = $( this ).val();
					value = value.replace( 'cols', '' );
					value = value.split( '-' );

					if ( value[0] == 2 ) {
						self.guiChangedCols( 2 );
						$( '.col-left,.col-right,.rightColumns,.leftColumns', self.context ).hide();
						if ( value[2] === 'left' ) {
							$( '.col-left,.leftColumns,.middleColumns', self.context ).show();
							self.updateLayoutSize( 'left' );
						}
						else if ( value[2] === 'right' ) {
							$( '.col-right,.rightColumns,.middleColumns', self.context ).show();
							self.updateLayoutSize( 'right' );
						}

						$( '#sbHolder_' + order_3_iu_id, self.context ).hide();
						$( '#sbHolder_' + order_2_iu_id, self.context ).show();
					}
					else if ( value[0] == 3 ) {
						$( '#sbHolder_' + order_2_iu_id, self.context ).hide();
						$( '#sbHolder_' + order_3_iu_id, self.context ).show();
						$( '.col-left,.col-right,.rightColumns,.leftColumns,.middleColumns', self.context ).show();
						self.guiChangedCols( 3 );
						self.updateLayoutSize( 3 );
					}
					else if ( value[0] == 0 ) {
						$( '#sbHolder_' + order_2_iu_id, self.context ).hide();
						$( '#sbHolder_' + order_3_iu_id, self.context ).hide();
						$( '.col-left,.col-right,.middleColumns,.rightColumns,.leftColumns', self.context ).hide();
						$( '.middleColumns' ).show();
						self.guiChangedCols( 0 );
						self.updateLayoutSize( 0 );
					}

					$( this ).parent().find( 'span:first' ).addClass( 'active' );
				} );

				this.Builder.find( 'input[name="cols"]' ).on( 'change', function ()
				{

					$( '.cols .active', self.context ).removeClass( 'active' );
					$( this ).parent().find( 'span:first' ).addClass( 'active' );
					var value = $( this ).val();
					value = value.replace( 'cols', '' );
					value = value.split( '-' );

					if ( value[0] == 2 ) {
						$( '#sbHolder_' + order_3_iu_id, self.context ).hide();
						$( '#sbHolder_' + order_2_iu_id, self.context ).show();
						self.guiChangedCols( 2 );
						$( '.col-left,.col-right,.rightColumns,.leftColumns', self.context ).hide();
						if ( value[2] === 'left' ) {
							$( '.col-left,.leftColumns,.middleColumns', self.context ).show();
							self.updateLayoutSize( 'left' );
						}
						else if ( value[2] === 'right' ) {
							$( '.col-right,.rightColumns,.middleColumns', self.context ).show();
							self.updateLayoutSize( 'right' );
						}
					}
					else if ( value[0] == 3 ) {
						$( '#sbHolder_' + order_2_iu_id, self.context ).hide();
						$( '#sbHolder_' + order_3_iu_id, self.context ).show();
						$( '.col-left,.col-right,.rightColumns,.leftColumns,.middleColumns', self.context ).show();
						self.guiChangedCols( 3 );
						self.updateLayoutSize( 3 );
					}
					else if ( value[0] == 0 ) {
						$( '#sbHolder_' + order_2_iu_id, self.context ).hide();
						$( '#sbHolder_' + order_3_iu_id, self.context ).hide();
						$( '.col-left,.col-right,.rightColumns,.leftColumns', self.context ).hide();
						$( '.middleColumns' ).show();
						self.guiChangedCols( 0 );
						self.updateLayoutSize( 0 );
					}

				} );

				/**
				 * setup layout boxes
				 */
				$( '.layoutBoxTop,.layoutBoxBottom,.layoutBoxBottom,.layoutBoxCustomBottom,.layoutBoxCustomTop,.layout-static', this.context ).hide();

				$( '#layout-header,#layout-footer,#layout-static,#layout-customheader,#layout-customfooter', this.Builder ).each(function ()
				{
					var el = $( this ).attr( 'id' );
					var show = false;
					if ( $( this ).is( ':checked' ) || $( this ).get( 0 ).checked ) {
						$( '.' + el ).show();
						show = true;
					}
					else {
						$( '.' + el ).hide();
					}

					self.setChangedCols( el, show );
				} ).change( function ( e )
					{
						e.preventDefault();
						var el = $( this ).attr( 'id' );
						var show = false;
						if ( $( this ).is( ':checked' ) ) {
							$( '.' + el ).show();
							show = true;
						}
						else {
							$( '.' + el ).hide();
						}

						self.setChangedCols( el, show );
					} );

				if ( Desktop.isWindowSkin ) {

					this.context.click( function ()
					{
						$( '.layout-builder:not([windowid="' + $( this ).attr( 'id' ) + '"])' ).hide();
						if ( $( '#tab_1', $( this ) ).hasClass( 'actTab' ) ) {
							$( '.layout-builder[windowid="' + $( this ).attr( 'id' ) + '"]' ).show();
						}
						else {

						}

					} );

					$( '.tabbedMenu li' ).click( function ()
					{
						if ( $( this ).attr( 'id' ) == 'tab_1' && $( this ).hasClass( 'actTab' ) ) {
							$( 'div.layout-builder[windowid="' + Win.windowID + '"]' ).show();
						}
						else {
							$( 'div.layout-builder' ).hide();
						}
					} );

					setTimeout( function ()
					{
						$( '.tabbedMenu li' ).each( function ()
						{
							if ( $( this ).attr( 'id' ) == 'tab_1' && $( this ).hasClass( 'actTab' ) ) {
								$( 'div.layout-builder[windowid="' + Win.windowID + '"]' ).show();
							}
							else {
								$( 'div.layout-builder[windowid="' + Win.windowID + '"]' ).hide();
							}
						} );
					}, 100 );

					// Window resize
					$( '#sidebar-tree' ).resize( function ()
					{
						self.updateLayoutSize()
					} );
				}
				else {

					setTimeout( function ()
					{
						var c = Core.getContentTabs();

						c.find( '.tabbedMenu li' ).each( function ()
						{
							$( this ).unbind( 'click.layouttab' ).bind( 'click.layouttab', function ()
							{
								if ( $( this ).attr( 'id' ) == 'tab_1' && $( this ).hasClass( 'actTab' ) ) {
									self.Builder.show();
								}
								else {
									self.Builder.hide();
								}
							} );

							if ( $( this ).attr( 'id' ) == 'tab_1' && $( this ).hasClass( 'actTab' ) ) {
								self.Builder.show();
							}
							else {
								self.Builder.hide();
							}
						} );


					}, 500 );
				}

				// lock/unlock buttons
				$( '.middleColumns .lockbtn .lockbtn-label,.rightColumns .lockbtn .lockbtn-label,.leftColumns .lockbtn .lockbtn-label', this.context ).click( function ()
				{

					if ( $( this ).parents( '.lockbtn:first' ).hasClass( 'locked' ) ) {
						$( this ).parents( '.lockbtn:first' ).removeClass( 'locked' );
						$( this ).parents( '.lockbtn:first' ).parent().removeClass( 'lockedMask' );
						$( this ).parents( '.lockbtn:first' ).parent().find( '.connectedSortable:first-child' ).removeClass( 'boxLocked' );
						$( this ).parents().find( '.ui-sortable:first-child' ).sortable( 'enable' );
						var box = $( this ).parent().parent().children().find( 'div.layoutBox' );
						$( this ).parents( '.lockbtn:first' ).parent().find( '.layoutBox-lock-mask' ).remove();
						$( this ).parent().parent().children().find( 'a' ).css( {
							cursor: 'pointer'
						} );
					}
					else {
						$( this ).parents( '.lockbtn:first' ).addClass( 'locked' );
						$( this ).parents( '.lockbtn:first' ).parent().find( '.connectedSortable:first-child' ).addClass( 'boxLocked' );
						$( this ).parents().find( '.ui-sortable:first-child' ).sortable( 'disable' );
						$( this ).parents( '.lockbtn:first' ).parent().addClass( 'lockedMask' );
						$( this ).parents( '.lockbtn:first' ).parent().append( $( '<div>' ).addClass( 'layoutBox-lock-mask' ) );
						$( this ).parent().parent().children().find( 'div.layoutBox' ).append( $( '<div>' ).addClass( 'layoutBox-lock-mask' ) );
						$( this ).parent().parent().children().find( 'div.layoutBox' ).addClass( 'boxLocked' ).sortable( 'disable' );
						$( this ).parent().parent().children().find( 'a' ).css( {
							cursor: 'not-allowed'
						} );
					}
				} );

				// lock/unlock buttons
				$( '.layoutBoxTop .lockbtn .lockbtn-label,.layoutBoxBottom .lockbtn .lockbtn-label,.layoutBoxCustomBottom .lockbtn .lockbtn-label,.layoutBoxCustomTop .lockbtn .lockbtn-label', this.context ).click( function ()
				{
					if ( $( this ).parent().hasClass( 'locked' ) ) {
						$( this ).parent().removeClass( 'locked' );
						$( this ).parent().children().removeClass( 'locked' );
						//    $(this).parent().children('.section-opts').enable();

						$( this ).parent().parent().removeClass( 'lockedMask' ).find( '.layoutBox-lock-mask' ).remove();
						$( this ).parent().parent().find( '.connectedSortable:first-child' ).removeClass( 'boxLocked' );
						$( this ).parents().find( '.ui-sortable:first-child' ).sortable( 'enable' );
						$( this ).parent().parent().children().find( 'div.layoutBox' ).removeClass( 'boxLocked' ).sortable( 'enable' );
						$( this ).parent().parent().children().find( 'a' ).css( {
							cursor: 'pointer'
						} );
					}
					else {
						$( this ).parent().addClass( 'locked' ); // .lockbtn
						$( this ).parent().children().addClass( 'locked' );
						// $(this).parent().children('.section-opts').disabled();

						$( this ).parent().parent().find( '.connectedSortable:first-child' ).addClass( 'boxLocked' );
						$( this ).parents().find( '.ui-sortable:first-child' ).sortable( 'disable' );
						$( this ).parent().parent().addClass( 'lockedMask' );
						$( this ).parent().parent().append( $( '<div>' ).addClass( 'layoutBox-lock-mask' ) );
						//$(this).parent().parent().children().find('div.layoutBox').addClass('boxLocked').sortable('disable');
						$( this ).parent().parent().children().find( 'a' ).css( {
							cursor: 'not-allowed'
						} );
					}

				} );

				$( '.section-opts', this.context ).hide();

				this.initSortables();

				// empty box buttons
				$( '.section-opts span', this.context ).click( function ( e )
				{
//self.clearSections($(this).parent().parent().prev().children('ul').attr('id'));
				} );
			}
		},
		set: function ( key, value )
		{
			this.settings[key] = value;
		},
		// change column states
		setChangedCols: function ( el, show )
		{
			if ( el == 'layout-header' && show ) {
				$( '.layoutBoxTop', this.context ).show();
			}
			else if ( el == 'layout-header' && !show ) {
				$( '.layoutBoxTop', this.context ).hide();
			}
			else if ( el == 'layout-footer' && show ) {
				$( '.layoutBoxBottom', this.context ).show();
			}
			else if ( el == 'layout-footer' && !show ) {
				$( '.layoutBoxBottom', this.context ).hide();
			}
			else if ( el == 'layout-customfooter' && show ) {
				$( '.layoutBoxCustomBottom', this.context ).show();
			}
			else if ( el == 'layout-customfooter' && !show ) {
				$( '.layoutBoxCustomBottom', this.context ).hide();
			}
			else if ( el == 'layout-customheader' && show ) {
				$( '.layoutBoxCustomTop', this.context ).show();
			}
			else if ( el == 'layout-customheader' && !show ) {
				$( '.layoutBoxCustomTop', this.context ).hide();
			}
		},
		// Update Gui Tool column orders
		guiChangedCols: function ( numcols )
		{

			$( '#order_3,#order_2', this.Builder ).hide();
			switch ( numcols ) {
				case 2:
					$( '#order_3', this.Builder ).prev().prev().hide();
					$( '.col-orders,#order_2', this.Builder ).show();
					$( '#order_2', this.Builder ).prev().prev().show();
					$( '.col-orders>th.secondary>.right', this.Builder ).hide();
					$( '.col-orders>td.center>span', this.Builder ).hide();
					$( '.col-orders>td.center>select', this.Builder ).show();
					$( '.col-orders>td.center>input', this.Builder ).show();
					$( '.col-orders>td.right', this.Builder ).hide();
					break;
				case 3:
					$( '.col-orders>th.secondary>.right', this.Builder ).show();
					$( '.col-orders>td.right', this.Builder ).show();
					$( '.col-orders>td.center>select', this.Builder ).hide();
					$( '.col-orders>td.center>input', this.Builder ).hide();
					$( '.col-orders>td.center>span', this.Builder ).show();
					$( '#order_2', this.Builder ).prev().prev().hide();
					$( '.col-orders,#order_3', this.Builder ).show();
					$( '#order_3', this.Builder ).prev().prev().show();
					break;
				case 0:
					$( '.col-orders,#order_3,#order_2', this.Builder ).hide();
					$( '.col-orders>td.center>select', this.Builder ).hide();
					$( '.col-orders>td.center>input', this.Builder ).hide();
					$( '#order_2,#order_3', this.Builder ).hide();
					$( '#order_2,#order_3', this.Builder ).prev().prev().hide();
					break;
			}

			this.guiSetEventColOrder( numcols );
		},
		// set gui column events
		guiSetEventColOrder: function ( numcols )
		{

// $('#order_3,#order_2').unbind('change');
			var self = this;
			switch ( numcols ) {
				case 0:
					self.initColOrder( 0 );
					break;
				case 2:
					self.initColOrder( 2 );
					$( '#order_2', this.Builder ).change( function ( e )
					{
						e.preventDefault();
						setTimeout( function ()
						{
							self.initColOrder( 2 );
						}, 300 );
					} );
					break;
				case 3:
					self.initColOrder( 3 );
					$( '#order_3', this.Builder ).change( function ( e )
					{
						e.preventDefault();
						setTimeout( function ()
						{
							self.initColOrder( 3 );
						}, 300 );
					} );
					break;
			}
		},
		/**
		 *
		 * @param integer numcols
		 * @returns {undefined}
		 */
		initColOrder: function ( numcols )
		{
			var self = this;
			margin_unit = 'px';
			margin_left = 8;
			margin_right = 8;
			left_width = '300px';
			right_width = '300px';
			center_width = '';
			left_unit = 'px';
			right_unit = 'px';
			center_unit = '';
			switch ( numcols ) {
				case 0:
					$( '#col1,#col2', this.context ).css( {
						'float': 'left',
						'width': left_width,
						'margin': '0',
						'margin-right': margin_right + margin_unit
					} );
					$( "#col3", this.context ).css( {
						'width': 'auto',
						'margin': '0'
					} );
					break;
				case 2:

					var orderEl = $( '#order_2', this.Builder );
					var content_order = orderEl.get( 0 ).selectedIndex;
					var order = self.columnorder2[content_order];
					self.updateColOrderLabels( 2, content_order );
					self.generateCssCode( 2, content_order );
					switch ( content_order ) {
						case 0:
							$( '#col1,#col2', this.context ).css( {
								'float': 'left',
								'width': left_width,
								'margin': '0',
								'margin-right': margin_right + margin_unit
							} );
							$( "#col3", this.context ).css( {
								'width': 'auto',
								'margin': '0'
							} );
							break;
						case 1:
							$( '#col1,#col2', this.context ).css( {
								'float': 'right',
								'width': left_width,
								'margin': '0',
								'margin-left': margin_left + margin_unit
							} );
							$( "#col3", this.context ).css( {
								'width': 'auto',
								'margin': '0'

							} );
							break
					}

					break;
				case 3:
					var content_order = $( '#order_3', this.Builder ).get( 0 ).selectedIndex;
					var order = self.columnorder3[content_order];
					self.updateColOrderLabels( 3, content_order );
					self.generateCssCode( 3, content_order );
					switch ( content_order ) {
						case 0:
							var a = parseInt( str_replace( left_unit, '', left_width ) ) + margin_left;
							var b = parseInt( str_replace( right_unit, '', right_width ) ) + margin_right;
							$( "#col1", this.context ).css( {
								"float": "left",
								"width": left_width,
								"margin": "0",
								'margin-right': margin_right + margin_unit
							} );
							$( "#col2", this.context ).css( {
								"float": "right",
								"width": right_width,
								"margin": "0",
								'margin-left': margin_left + margin_unit

							} );
							$( "#col3", this.context ).css( {
								"width": "auto",
								"margin": "0"

								//"margin": "0 " + b + right_unit + " 0 " + a + left_unit
							} );
							break;
						case 1:
							var c = parseInt( str_replace( right_unit, '', right_width ) );
							var d = parseInt( str_replace( left_unit, '', left_width ) );
							$( "#col1", this.context ).css( {
								"float": "right",
								"width": right_width,
								"margin": "0",
								'margin-left': margin_left + margin_unit

							} );
							$( "#col2", this.context ).css( {
								"float": "left",
								"width": left_width,
								"margin": "0",
								'margin-right': margin_right + margin_unit
							} );
							$( "#col3", this.context ).css( {
								"width": "auto",
								"margin": "0"


								//"margin": "0 " + c + right_unit + " 0 " + d + left_unit
							} );
							break;
						case 2:
							var f = parseInt( str_replace( left_unit, '', left_width ) );
							var g = parseInt( str_replace( right_unit, '', right_width ) );
							$( "#col1", this.context ).css( {
								"float": "left",
								"width": left_width,
								"margin": "0",
								'margin-right': margin_right + margin_unit

							} );
							$( "#col2", this.context ).css( {
								"float": "left",
								"width": right_width,
								"margin": "0",
								'margin-right': margin_right + margin_unit
							} );
							$( "#col3", this.context ).css( {
								"width": "auto",
								"margin": "0"
							} );
							break;
						case 3:
							var h = parseInt( str_replace( right_unit, '', right_width ) );
							var g = parseInt( str_replace( left_unit, '', left_width ) );
							$( "#col1", this.context ).css( {
								"float": "right",
								"width": right_width,
								"margin": "0"
							} );
							$( "#col2", this.context ).css( {
								"float": "right",
								"width": left_width,
								"margin": "0",
								'margin-right': margin_right + margin_unit,
								'margin-left': margin_left + margin_unit
							} );
							$( "#col3", this.context ).css( {
								"width": "auto",
								"margin": "0"
							} );
							break;
						case 4:
							var f = parseInt( str_replace( left_unit, '', left_width ) );
							var g = parseInt( str_replace( center_unit, '', center_width ) );
							$( "#col1", this.context ).css( {
								"float": "left",
								"width": left_width,
								"margin": "0 0 0 " + (parseInt( str_replace( right_unit, '', right_width ) ) + parseInt( str_replace( left_unit, '', left_width ) )) + right_unit
							} );
							$( "#col2", this.context ).css( {
								"float": "left",
								'left': 0,
								"width": right_width,
								"margin": "0"
							} );
							$( "#col3", this.context ).css( {
								"width": "auto",
								"margin": "0 0 0 " + String( f + g ) + center_unit
							} );
							break;
						case 5:
							var h = parseInt( str_replace( right_unit, '', right_width ) );
							var g = parseInt( str_replace( center_unit, '', center_width ) );
							$( "#col1", this.context ).css( {
								"float": "right",
								"width": left_width,
								"margin": "0 " + right_width + " 0 -" + String( h + g ) + center_unit
							} );
							$( "#col2", this.context ).css( {
								"float": "right",
								"width": right_width,
								"margin": "0"
							} );
							$( "#col3", this.context ).css( {
								"width": "auto",
								"margin": "0 " + String( h + g ) + center_unit + " 0 0"
							} );
							break
					}
					break;
			}
		},
		/**
		 * Change the Col Labels in the Builder
		 * @param integer numcols
		 * @param integer order
		 * @returns {undefined}
		 */
		updateColOrderLabels: function ( numcols, order )
		{

			var leftLabel = $( '.col-orders label[for=left_width]', this.Builder );
			var centerLabel = $( '.col-orders label[for=center_width]', this.Builder );
			var rightLabel = $( '.col-orders label[for=right_width]', this.Builder );
			switch ( numcols ) {
				case 2:
					switch ( order ) {
						case 0:
							leftLabel.text( 'Left' );
							rightLabel.text( '' );
							centerLabel.text( 'Content' );
							$( '#center_width', this.Builder ).hide();
							$( '#center_width', this.Builder ).next().show();
							$( '#left_width,#right_width', this.Builder ).show();
							$( '#left_width,#right_width', this.Builder ).next().hide();
							break;
						case 1:
							leftLabel.text( 'Content' );
							rightLabel.text( '' );
							centerLabel.text( 'Left' );
							$( '#center_width', this.Builder ).show();
							$( '#center_width', this.Builder ).next().hide();
							$( '#left_width,#right_width', this.Builder ).hide();
							$( '#left_width,#right_width', this.Builder ).next().show();
							break;
					}
					break;
				case 3:
					switch ( order ) {
						case 0:
							leftLabel.text( 'Left' );
							rightLabel.text( 'Right' );
							centerLabel.text( 'Content' );
							$( '.col-orders td.right', this.Builder ).find( '#right_width,.unit' ).show();
							$( '#right_width', this.Builder ).next().hide();
							$( '.col-orders td.left', this.Builder ).find( '#left_width,.unit' ).show();
							$( '#left_width', this.Builder ).next().hide();
							$( '.col-orders td.center', this.Builder ).find( '#center_width,.unit' ).hide();
							$( '#center_width', this.Builder ).next().show();
							break;
						case 1:
							leftLabel.text( 'Right' );
							rightLabel.text( 'Left' );
							centerLabel.text( 'Content' );
							$( '.col-orders td.right', this.Builder ).find( '#right_width,.unit' ).show();
							$( '#right_width', this.Builder ).next().hide();
							$( '.col-orders td.left', this.Builder ).find( '#left_width,.unit' ).show();
							$( '#left_width', this.Builder ).next().hide();
							$( '.col-orders td.center', this.Builder ).find( '#center_width,.unit' ).hide();
							$( '#center_width', this.Builder ).next().show();
							break;
						case 2:
							leftLabel.text( 'Left' );
							rightLabel.text( 'Content' );
							centerLabel.text( 'Right' );
							$( '.col-orders td.right', this.Builder ).find( '#right_width,.unit' ).hide();
							$( '#right_width', this.Builder ).next().show();
							$( '.col-orders td.left', this.Builder ).find( '#left_width,.unit' ).show();
							$( '#left_width', this.Builder ).next().hide();
							$( '.col-orders td.center', this.Builder ).find( '#center_width,.unit' ).show();
							$( '#center_width', this.Builder ).next().hide();
							break;
						case 3:
							leftLabel.text( 'Content' );
							rightLabel.text( 'Left' );
							centerLabel.text( 'Right' );
							$( '.col-orders td.right', this.Builder ).find( '#right_width,.unit' ).show();
							$( '#right_width', this.Builder ).next().hide();
							$( '.col-orders td.left', this.Builder ).find( '#left_width,.unit' ).hide();
							$( '#left_width', this.Builder ).next().show();
							$( '.col-orders td.center', this.Builder ).find( '#center_width,.unit' ).show();
							$( '#center_width', this.Builder ).next().hide();
							break;
					}
					break;
			}
		},
		/**
		 * Create the CSS Code for the Frontend layout
		 * @param integer numcols
		 * @param integer content_order
		 * @returns {String}
		 */
		generateCssCode: function ( numcols, content_order )
		{

			margin_unit = 'px';
			margin_left = 8;
			margin_right = 8;
			left_width = '300px';
			right_width = '300px';
			center_width = '';
			left_unit = 'px';
			right_unit = 'px';
			center_unit = '';
			var s = '';
			switch ( numcols ) {
				case 2:
					switch ( content_order ) {
						case 0:
							s += "#col1,#col2 { float:left; width:" + left_width + "; margin: 0; margin-right:" + margin_right + margin_unit + ";}\n";
							s += "#col3 { width: auto; margin: 0; }\n";
							break;
						case 1:
							s += "#col1,#col2 { float:left; width:" + left_width + "; margin: 0; margin-left:" + margin_left + margin_unit + ";}\n";
							s += "#col3 { width: auto; margin: 0; }\n";
							break
					}
					break;
				case 3:

					switch ( content_order ) {
						case 0:
							s += "#col1 { float: left; width:" + left_width + ";margin: 0;margin-right" + margin_right + margin_unit + ";}\n";
							s += "#col2 { float: right; width:" + right_width + ";margin: 0;margin-left:" + margin_left + margin_unit + ";}\n";
							s += "#col3 { width: auto; margin: 0; }\n";
							break;
						case 1:
							s += "#col2 { float: left; width:" + left_width + ";margin: 0;margin-right" + margin_right + margin_unit + ";}\n";
							s += "#col1 { float: right; width:" + right_width + ";margin: 0;margin-left:" + margin_left + margin_unit + ";}\n";
							s += "#col3 { width: auto; margin: 0; }\n";
							break;
						case 2:
							s += "#col1 { float: left; width:" + left_width + ";margin: 0;margin-right" + margin_right + margin_unit + ";}\n";
							s += "#col2 { float: left; width:" + right_width + ";margin: 0;margin-right:" + margin_right + margin_unit + ";}\n";
							s += "#col3 { width: auto; margin: 0; }\n";
							break;
						case 3:
							s += "#col1 { float: right; width:" + right_width + ";margin: 0;}\n";
							s += "#col2 { float: right; width:" + left_width + ";margin: 0;margin-right:" + margin_right + margin_unit + ";margin-left:" + margin_left + margin_unit + "; }\n";
							s += "#col3 { width: auto; margin: 0; }\n";
							break;
					}
					break;
			}

			this.generatedCssCode = s;
			return s;
		},
		/**
		 *
		 * @param event event
		 * @param object b jquery object
		 * @returns {undefined}
		 */
		insertSubCols: function ( event, b, callback )
		{
			var layoutID = $( b.item ).attr( 'layout' ), s = this;

			switch ( layoutID ) {
				case 'dp_5050':
				case 'dp_3366':
				case 'dp_6633':
				case 'dp_3862':
				case 'dp_6238':
				case 'dp_2575':
				case 'dp_7525':
				case 'dp_3333':
				case 'dp_4425':
				case 'dp_1221':
				case 'dp_1122':
				case 'dp_2211':

					var placeholder = $( '.ui-sortable-placeholder', this.context );

					if ( placeholder.length != 1 ) {
						return false;
					}

					//$(this).find( "div.dummy:first" ).remove();

					if ( layoutID == 'dp_4425' ) {
						var c = 5;
					} else if ( layoutID == 'dp_3333' || layoutID == 'dp_1221' || layoutID == 'dp_1122' || layoutID == 'dp_2211' ) {
						var c = 4;
					} else {
						var c = 3;
					}

					// Template
					var template = this.dropelements[layoutID];

					var list = $( '<div/>' ).addClass( 'nosortables' ); //.addClass('dropaccept');

					var rand = Math.floor( Math.random() * 10001 );
					var newId = 'subcols';
					// make sure we have a unique ID number
					if ( $( '#' + newId + '_' + rand ).length ) {
						while ( $( '#' + newId + '_' + rand ).exists() ) {
							rand = Math.floor( Math.random() * 10001 );
						}
					}

					newId = newId + '_' + rand;
					list.addClass( newId ).attr( 'id', 'subcolcontainer_' + rand );

					this.settings.dynamic_id++;

					var cidx = this.settings.dynamic_id;

					// sortable
					var currentID = 'subdyn_id' + String( this.settings.dynamic_id );

					/**
					 *  Content container
					 */
					var ul = '<div class="sortableSubCols equalize" title="Subtemplate Container: #' + 'dyn_id' + String( this.settings.dynamic_id ) + '"><div class="allowSubCols dropaccept subsort sortzone"></div></div>';

					/**
					 *  Content Container Menu
					 */
					var menu = '<div id="menu' + String( this.settings.dynamic_id ) + '" class="layoutmenu"><span class="more">mehr...</span><div class="submenu"></div></div>';

					template = template.replace( /xxx0/g, ul );
					template = template.replace( 'xxx1', currentID );

					this.settings.dynamic_id++;

					// prepare columns
					for ( var i = 2; i <= c; i++ ) {
						template = template.replace( 'xxx' + String( i ), 'dyn_id' + String( this.settings.dynamic_id ) );

						this.settings.dynamic_id++;
					}

					var items = $( template );
					items.prepend( $( menu ) );
					items.find( '.allowSubCols' ).append( $( '<div class="itemBox dum"/>' ) );

					list.append( items );

					list = this.formatXml( $( list ).html() );
					$( b.item ).removeClass( 'drag' );
					//$(b.item).replaceWith($(list));

					$( list ).insertBefore( placeholder );

					// add buttons to content container menu
					this.createAddButton( '#menu' + String( cidx ) );
					this.createRemoveButton( '#menu' + String( cidx ) );
					this.createEmptyButton( '#menu' + String( cidx ) );
					$( '#menu' + String( cidx ) ).find( '.more' ).click( function ()
					{
						$( '.layoutmenu .submenu:visible', s.context ).hide();
						$( this ).parent().find( '.submenu:first' ).slideToggle( 50, function ()
						{
							$( this ).unbind( 'mouseleave' ).bind( 'mouseleave', function ()
							{
								$( this ).hide();
							} );
						} );
					} );

					$( list ).find( '.subcolumns' ).addClass( 'equalize' ).addClass( 'itemBox' ).addClass( 'cbox' );

					// update dynamic id
					this.settings.dynamic_id = this.settings.dynamic_id + c + 1;
					if ( Tools.isFunction( callback ) ) {
						callback();
					}

					break;
			}
		},
		// Add new items or resort Items in this Subcolumn Container
		createAddButton: function ( elementid, blockid )
		{
			var s = this, button = $( '<span>' ).attr( 'title', 'Create/Close Dropzone' ).addClass( 'addsubcols' );
			button.click( function ()
			{
				if ( !$( this ).hasClass( 'addsubcols-cancel' ) ) {
					$( this ).addClass( 'addsubcols-cancel' );
					$( this ).parents().find( '.dropaccept' ).removeClass( 'dropzone' ).addClass( 'dropprotect' );
					$( this ).parents( '.sortzone' ).sortable( {
						disabled: true
					} );


					/*
					$( this ).parent().parent().addClass( 'dropaccept' ).addClass( 'dropzone' );
					$( this ).parent().parent().sortable( {
						disabled: false
					} );
					$( this ).parent().next().find( '.ui-sortable' ).sortable( {
						disabled: false
					} );
*/
					s.enableDraggableContentItems();
				}
				else {
					$( this ).removeClass( 'addsubcols-cancel' );
					$( this ).parents().find( '.dropaccept' ).removeClass( 'dropprotect' ).addClass( 'dropzone' );

					$( this ).parents( '.sortzone' ).sortable( {
						disabled: false
					} );
					/*
					$( this ).find( '.ui-sortable' ).sortable( {
						disabled: true
					} );
*/
					s.disableDraggableContentItems();
				 s.initSortables();
				}
			} );
			$( elementid ).prepend( $( button ) );
		},
		// Remove the current Subcolumn Container
		createRemoveButton: function ( elementid )
		{
			var _self = this;
			var button = $( '<span>' ).attr( 'title', 'Remove Container' ).addClass( 'removecontainer' ).append( 'Remove Container' );
			button.click( function ()
			{
				var self = this;

				var maincontainer = $( this ).parents( '.mainBox:first' );

				jConfirm( 'Möchtest du diesen Container wirklich löschen?', 'Bestätigung...', function ( r )
				{
					if ( r ) {
						console.log( 'createRemoveButton click' );

						// Remove the Subcolumn Container
						$( self ).parents( '.subcolumns:first' ).remove();

						// update the database
						_self.updateDBSubCols( maincontainer );
					}

					maincontainer = null;
				} );
			} );

			$( elementid ).find( '.submenu' ).append( $( '<div>' ).append( button ) );
		},
		// Empty current Subcolumn Container
		createEmptyButton: function ( elementid )
		{
			var _self = this;
			var button = $( '<span/>' ).attr( 'title', 'Empty Contents' ).addClass( 'emptycontent' ).append( 'Empty Contents' );
			button.click( function ()
			{
				var self = this;

				jConfirm( 'Möchtest du diesen Container-Inhalt wirklich löschen?', 'Bestätigung...', function ( r )
				{
					if ( r ) {

						var removedIds = [];

						// push all items to array
						$( self ).parents( '.subcolumns:first' ).find( '.itemBox' ).each( function ()
						{
							if ( !$( this ).hasClass( 'dum' ) && !$( this ).hasClass( 'subcolumns' ) && typeof $( this ).attr( 'id' ) == 'string' ) {
								removedIds.push( $( this ).attr( 'id' ) );
							}
						} );

						if ( removedIds.length > 0 ) {
							// remove item form layout
							for ( var x in removedIds ) {
								// Layouter.removeItem($('#' + removedIds[x]).find('.delete-btn'), false);
								$( '#' + removedIds[x], _self.context ).remove();
							}

							delete removedIds;

							console.log( 'createEmptyButton click' );

							//
							$( self ).parents( '.subcolumns:first' ).find( '.addsubcols-cancel' ).trigger( 'click' );

							// update the database
							_self.updateDBSubCols( $( self ).parents( '.mainBox:first' ) );
						}

					}
				} );
			} );
			$( elementid ).find( '.submenu' ).append( $( '<div/>' ).append( button ) );
		},
		/**
		 * Add a new Content Box to the obj (section)
		 * @param object obj is the menu container of the content box
		 * @returns {unresolved}
		 */
		addBlockToSection: function ( obj, currentSectionId, callback )
		{

			var self = this, newId = $( obj ).parent().attr( 'id' );
			if ( !newId || newId.match( /_\d+$/ ) || currentSectionId == $( obj ).parents( '.mainBox:first' ).attr( 'id' ) ) {
				return;
			}

			var blockObj = $( obj ).parents( '.mainBox:first' );
			var block = blockObj.attr( 'id' );
			if ( typeof block == 'undefined' ) {
				jAlert( 'Invalid Block! Could not instert this Block!' + "<p/><div style=\"overflow:auto; height: 250px\">" + $( obj ).parent().parent().html() + '</div>', 'Error' );
				return;
			}

			// create a new ID
			if ( !newId.match( /_\d+$/ ) ) {
				var rand = Math.floor( Math.random() * 10001 );
				// make sure we have a unique ID number
				if ( $( '#' + newId + '_' + rand ).length ) {
					while ( $( '#' + newId + '_' + rand ).exists() ) {
						rand = Math.floor( Math.random() * 10001 );
					}
				}

				newId = newId + '_' + rand;
				$( obj ).parent().attr( 'id', newId );
			}

			var cols = $( '.cols input:checked', this.Builder ).val();
			var layoutid = this.settings.layoutID; //$('#layout-id', $('#' + Win.windowID)).val();
			var dataid = $( obj ).parent().attr( 'dataid' );
			var rel = $( obj ).parent().attr( 'rel' );

			$.post( 'admin.php', {
				adm: 'layouter',
				action: 'addblock',
				layoutid: layoutid,
				current: newId,
				cols: cols,
				block: block,
				rel: rel,
				dataid: dataid
			}, function ( data )
			{

				//console.log( 'addBlockToSection ajax success' );
				if ( Tools.responseIsOk( data ) ) {
					// set the new id
					$( obj ).parent().attr( 'blockid', data.blockid );

					if ( data.dataid ) {
						$( obj ).parents( '.itemBox:first' ).attr( 'dataid', data.dataid );
					}

					self.addButtonsToItem( obj, data );
					//console.log( 'addBlockToSection ajax success updateDBSubCols' );
					if ( Tools.isFunction( callback ) ) {
						callback();
					}

				}
				else {
					if ( $( obj ).hasClass( 'itemBox' ) ) {
						$( obj ).remove();
					}

					if ( $( obj ).parents( '.itemBox:first' ) ) {
						$( obj ).parents( '.itemBox:first' ).remove();
					}

					console.error( [data] );
					//alert(data.msg);
				}
			}, 'json' );
		},
		/**
		 * Move Content Box
		 * @param object obj
		 * @param string currentpos ID of the current element
		 * @returns {Boolean}
		 */
		moveItem: function ( obj, currentpos )
		{
			var self = this, Id = obj.attr( 'id' );
			var cols = $( '.cols input:checked', this.context ).val();
			var blockname = obj.parent().attr( 'id' );
			var layoutid = this.settings.layoutID; //$('#layout-id', $('#' + Win.windowID)).val();

			if ( obj.parents( '.mainBox:first' ).hasClass( 'boxLocked' ) ) {
				return false;
			}

			var O = obj;
			if ( !obj.parents( '.mainBox:first' ).hasClass( 'boxLocked' ) ) {
				O = obj.parents( '.mainBox:first' );
				blockname = $( O ).attr( 'id' );
			}

			if ( $( '#' + currentpos, this.context ).length && !$( '#' + currentpos, this.context ).hasClass( 'boxLocked' ) ) {
				currentpos = $( '#' + currentpos ).parents( 'ul.layoutBox:first' );
			}

			var neworder = [];
			$( O ).find( 'li' ).each( function ()
			{

				if ( typeof $( this ).attr( 'id' ) != 'undefined' ) {
					neworder.push( $( this ).attr( 'id' ) );
				}
			} );

			var newdata = neworder.join( ',' );
			$.post( 'admin.php', {
					adm: 'layouter',
					action: 'moveblock',
					layoutid: layoutid,
					neworder: newdata,
					moved: Id,
					cols: cols,
					block: blockname,
					current: currentpos
				},
				function ( data )
				{
					if ( Tools.responseIsOk( data ) ) {
						console.log( 'moveItem' );
						self.updateDBSubCols( O );
					}
					else {
						console.log( [data] );
						//alert(data.msg);
					}
				}, 'json' );
		},
		/**
		 *
		 * @param object obj
		 * @returns string the ID of the obj
		 */
		getBlockId: function ( obj )
		{
			if ( $( obj ).attr( 'id' ) ) {
				return $( obj ).attr( 'id' );
			}
			else {
				return $( obj ).parents( '.itemBox:first' ).attr( 'id' );
			}
		},
		/**
		 *
		 * @param object obj
		 * @param json data
		 * @returns {undefined}
		 */
		addButtonsToItem: function ( obj, data )
		{
			var _self = this, blockid = $( obj ).parent().attr( 'blockid' );
			if ( typeof data == 'object' && Tools.exists( data, 'blockid' ) ) {
				blockid = data.blockid;
			}

			var _disable = $( '<span>' ).addClass( 'disable-btn' ).attr( 'title', 'Inhalt ausblenden/einblenden' );
			_disable.attr( 'blockid', blockid );

			var _edit = $( '<span>' ).addClass( 'edit-btn' ).attr( 'title', 'Inhalt bearbeiten' );
			_edit.attr( 'blockid', blockid );

			var _delete = $( '<span>' ).addClass( 'delete-btn' ).attr( 'title', 'Element entfernen' );
			_delete.attr( 'blockid', blockid );

			var id = this.getBlockId( obj );
			var addEdit = false;

			if ( typeof id != 'undefined' ) {
				if ( id.match( /^modul_/ ) ) {
					addEdit = true;
				}
			}

			_disable.click( function ()
			{
				var self = this;
				if ( $( self ).parents( '.mainBox:first' ).hasClass( 'boxLocked' ) ) {
					return false;
				}

				_self.disableBlock( $( this ) );
				return false;
			} );

			addEdit = true;
			$( _delete ).click( function ( e )
			{
				var self = this;
				if ( $( self ).parents( '.mainBox:first' ).hasClass( 'boxLocked' ) ) {
					return false;
				}

				jConfirm( 'Möchtest du diesen Inhalts Container wirklich löschen?', 'Bestätigung...', function ( r )
				{
					if ( r ) {
						_self.removeItem( obj );
						//$(self).parent().remove();
					}
				} );
				return false;
			} );

			$( obj ).append( _delete );
			$( obj ).append( _disable );

			if ( addEdit ) {
				$( obj ).append( _edit );
				$( _edit ).click( function ( e )
				{
					_self.editBlock( $( this ) );
					return false;
				} );
			}
		},
		/**
		 * Empty a Section
		 * @param string ulcol ID
		 * @returns {Boolean}
		 */
		clearSections: function ( ulcol )
		{
			if ( $( '#' + ulcol ).find( 'li:not(.contentPlaceholder)' ).length == 0 ) {
				return false;
			}
			var _self = this;
			jConfirm( 'Möchtest du wirklich alle Inhalts Container löschen?<p><strong>Es gehen alle Daten der Blöcke dabei verloren!!!</strong></p>', 'Bestätigung...', function ( r )
			{
				if ( r ) {
					$( '#' + ulcol ).parent().mask( 'Entferne Inhalte...' );
					$( '#' + ulcol ).removeClass( 'boxLocked' ).prev().removeClass( 'boxLocked' );
					var block = ulcol;
					var layoutid = _self.settings.layoutID; //$('#layout-id', $('#' + Win.windowID)).val();
					var cols = $( '.cols input:checked', _self.Builder ).val();
					var items = $( '#' + ulcol + ' li:not(.contentPlaceholder)', _self.context );
					items.each( function ()
					{
						var Id = _self.getBlockId( this );
						var item = this;
						$.get( 'admin.php?adm=layouter&action=removeblock&layoutid=' + layoutid + '&contentbox=' + Id + '&cols=' + cols + '&layoutblock=' + block, {
						}, function ( data )
						{

							$( '#' + ulcol, _self.context ).parent().unmask();

							if ( Tools.responseIsOk( data ) ) {
								$( item ).remove();
								console.log( 'clearSections' );
								_self.updateDBSubCols( $( '#' + ulcol ) );
							}
							else {
								console.log( [data] );
								// alert(data.msg);
							}
						}, 'json' );
					} );
				}
			} );
			return false;
		},
		/**
		 *
		 * @param object o
		 * @param boolean updateDBLayout
		 * @returns {Boolean}
		 */
		removeItem: function ( o, updateDBLayout )
		{

			var self = this, obj = $( o ).parents( '.cbox:first' );
			var Id = this.getBlockId( obj );
			var cols = $( '.cols input:checked', this.Builder ).val();
			var block = obj.parents( '.mainBox:first' ).attr( 'id' );
			var layoutid = this.settings.layoutID; //$('#layout-id', $('#' + Win.windowID)).val();

			if ( obj.parent().parents( '.mainBox:first' ).hasClass( 'boxLocked' ) ) {
				return false;
			}

			obj.parent().parents( '.mainBox:first' ).mask( 'Entferne Inhalte...' );
			$.get( 'admin.php?adm=layouter&action=removeblock&layoutid=' + layoutid + '&contentbox=' + Id + '&cols=' + cols + '&layoutblock=' + block, {
			}, function ( data )
			{

				obj.parent().parents( '.mainBox:first' ).unmask();
				if ( Tools.responseIsOk( data ) ) {
					obj.remove();
					// @todo remove this
					//if (typeof updateDBLayout == 'undefined' || updateDBLayout === true) {
					self.updateDBSubCols( obj.parents( '.mainBox:first' ) );
					//}
				}
				else {
					console.log( [data] );
					//alert(data.msg);
				}
			}, 'json' );
		},
		editBlock: function ( obj )
		{
			var self = this, blockID = $( obj, this.context ).attr( 'blockid' );
			var Id = this.getBlockId( obj );
			var cols = $( '.cols input:checked', this.Builder ).val();
			var block = obj.parents( 'div.mainBox:first' ).attr( 'id' );
			var dataid = obj.parents( 'div.itemBox:first' ).attr( 'dataid' );
			var layoutid = this.settings.layoutID; //$('#layout-id', $('#' + Win.windowID)).val();

			if ( $( block ).parents( '.mainBox:first' ).hasClass( 'boxLocked' ) ) {
				return;
			}
			var data = this.prepareSubColHTML( obj.parents( 'div.mainBox:first' ) );
			var html = data[1];

            var w = $('#'+ Win.windowID ).mask('laden...');


			$.ajax( {
				url: 'admin.php?adm=layouter&action=editblock&layoutid=' + layoutid + '&contentbox=' + Id + '&layoutblock=' + block + '&blockid=' + blockID + '&dataid=' + dataid + '&cols=' + cols,
				globals: false,
				cache: false,
				async: false,
				dataType: 'json'
			} ).done( function ( data )
				{
                    w.unmask();

					if ( Tools.responseIsOk( data ) ) {
						self.buildBlockForm( data, obj );

					}
					else {
						console.log( data.msg );
					}
				} );

		},
		disableBlock: function ( obj )
		{
			var self = this, blockID = $( obj, this.context ).attr( 'blockid' );
			var Id = this.getBlockId( obj );
			var cols = $( '.cols input:checked', this.Builder ).val();
			var block = obj.parents( 'div.mainBox:first' ).attr( 'id' );
			var dataid = obj.parents( 'div.itemBox:first' ).attr( 'dataid' );
			var layoutid = this.settings.layoutID; //$('#layout-id', $('#' + Win.windowID)).val();

			if ( $( block ).parents( '.mainBox:first' ).hasClass( 'boxLocked' ) ) {
				return;
			}

			$.get( 'admin.php?adm=layouter&action=editblock&disable=1&layoutid=' + layoutid + '&contentbox=' + Id + '&layoutblock=' + block + '&blockid=' + blockID + '&dataid=' + dataid + '&cols=' + cols, {
			}, function ( data )
			{
				if ( Tools.responseIsOk( data ) ) {
					if ( data.disabled ) {
						obj.parents( 'div.itemBox:first' ).addClass( 'isdisabled' );
					}
					else {
						obj.parents( 'div.itemBox:first' ).removeClass( 'isdisabled' );
					}
				}
				else {
					console.log( data.msg );
				}

			}, 'json' );
		},
		currentBlockEdit: null,
		/**
		 * Create the Edit form for the Content Block
		 * @param json data
		 * @param object obj
		 * @returns {undefined}
		 */
		buildBlockForm: function ( data, obj )
		{
			var self = this;
			this.currentBlockEdit = obj;

			Tools.createPopup( data.form, {
				title: data.formlabel,
				resizeable: true,
				WindowContent: data.form,
				WindowToolbar: data.toolbar,
				Width: 800,
                Height: 400,
				opener: Win.windowID,
				onAfterOpen: function ( winObj )
				{
					$( '#' + Win.windowID ).data( 'layouter', self );

					if ( Tools.exists( data, 'loadScripts' ) ) {
						var load = 0;
						if ( Tools.exists( data.loadScripts, 'css' ) && data.loadScripts.css.length ) {
							load += data.loadScripts.css.length;
						}

						if ( Tools.exists( data.loadScripts, 'js' ) && data.loadScripts.js.length ) {
							load += data.loadScripts.js.length;
						}

						if ( load ) {
							if ( Tools.exists( data.loadScripts, 'css' ) && data.loadScripts.css.length ) {
								for ( var x = 0; x < data.loadScripts.css.length; x++ ) {
									if ( data.loadScripts.css[x].substr( data.loadScripts.css[x].length - 4, data.loadScripts.css[x].length ) != '.css' ) {
										data.loadScripts.css[x] += '.css';
									}
									var cssh = hash;
									if ( !$( '#css-' + cssh ).length ) {
										Tools.loadCss( data.loadScripts.css[x], function ( styleTag )
										{
											styleTag.attr( 'id', 'css-' + cssh );
										} );
									}
								}

								if ( Tools.exists( data.loadScripts, 'js' ) && !data.loadScripts.js.length || !Tools.exists( data.loadScripts, 'js' ) ) {
									if ( $( data.form ).filter( 'script' ).length ) {

										//   console.log('Eval Scripts after window Created');
										Tools.eval( $( data.form ) );
									}
								}
							}
							if ( Tools.exists( data.loadScripts, 'js' ) && data.loadScripts.js.length ) {
								Tools.loadScripts( data.loadScripts.js, function ()
								{
									if ( $( data.form ).filter( 'script' ).length ) {

										//   console.log('Eval Scripts after window Created');
										Tools.eval( $( data.form ) );
									}
								} );
							}
						}
						else {
							if ( $( data.form ).filter( 'script' ).length ) {

								//   console.log('Eval Scripts after window Created');
								Tools.eval( $( data.form ) );
							}
						}
					}
					else {
						if ( $( data.form ).filter( 'script' ).length ) {

							//   console.log('Eval Scripts after window Created');
							Tools.eval( $( data.form ) );
						}
					}

				},
				onBeforeClose: function ( event, winObj, callback )
				{
					self.currentBlockEdit = null; // reset

					if ( Desktop.isWindowSkin ) {

						if ( winObj.$el.find( 'input[name=title]' ).length == 1 ) {
							// change the title of the content box
							obj.parents( '.itemBox:first' ).find( '.contentbox-menu:first>span:first' ).html( winObj.$el.find( 'input[name=title]' ).val() );
						}
					}
					else {
						if ( winObj.find( 'input[name=title]' ).length == 1 ) {
							obj.parents( '.itemBox:first' ).find( '.contentbox-menu:first>span:first' ).html( winObj.find( 'input[name=title]' ).val() );
						}
					}

					if ( typeof callback === 'function' ) {
						callback();
					}

					$( '#' + Win.windowID ).removeData( 'layouter' );
				}
			} );
		},
		onAfterSubmitBlockEdit: function ( exit, data, formObj )
		{
			if ( data.dataid ) {
				this.currentBlockEdit.parents( '.itemBox:first' ).attr( 'dataid', data.dataid );
			}
		},
		/**
		 * Format the giving Html Code
		 * @param string xml
		 * @returns {String}
		 */
		formatXml: function ( xml )
		{

			if ( typeof xml !== 'string' ) {
				return xml;
			}

			xml = xml.replace( /\s*\n*\t*\s*(<\/?[^>]*>)\s*\n*\t*\s*/, '\n$1\n' );
			xml = xml.replace( /\n*\s*<\/a>/, '</a>' );
			xml = xml.replace( /<a\s([^>]*)>\n*\s*/, '<a $1>' );

			var reg = /(>)(<)(\/*)/g;
			var wsexp = / *(.*) +\n/g;
			var contexp = /(<.+>)(.+\n)/g;
			xml = xml.replace( reg, '$1\n$2$3' ).replace( wsexp, '$1\n' ).replace( contexp, '$1\n$2' ).replace( /\n{1,}/g, '\n' ).replace( /    /g, '' );
			var pad = 0;
			var formatted = '';
			var lines = xml.split( '\n' );
			var indent = 0;
			var lastType = 'other';
			// 4 types of tags - single, closing, opening, other (text, doctype, comment) - 4*4 = 16 transitions
			var transitions = {
				'single->single': 0,
				'single->closing': -1,
				'single->opening': 0,
				'single->other': 0,
				'closing->single': 0,
				'closing->closing': -1,
				'closing->opening': 0,
				'closing->other': 0,
				'opening->single': 1,
				'opening->closing': 0,
				'opening->opening': 1,
				'opening->other': 1,
				'other->single': 0,
				'other->closing': -1,
				'other->opening': 0,
				'other->other': 0
			};
			for ( var i = 0; i < lines.length; i++ ) {
				var ln = lines[i];
				var single = Boolean( ln.match( /<.+\/>/ ) ); // is this line a single tag? ex. <br />
				var closing = Boolean( ln.match( /<\/.+>/ ) ); // is this a closing tag? ex. </a>
				var opening = Boolean( ln.match( /<[^!].*>/ ) ); // is this even a tag (that's not <!something>)
				var type = single ? 'single' : closing ? 'closing' : opening ? 'opening' : 'other';
				var fromTo = lastType + '->' + type;
				lastType = type;
				var padding = '';
				indent += transitions[fromTo];
				for ( var j = 0; j < indent; j++ ) {
					padding += '    ';
				}

				formatted += padding + ln + '\n';
			}

			return formatted;
		},
		/**
		 *
		 * @param object currentBlockObj
		 * @returns {Array}
		 */
		prepareSubColHTML: function ( currentBlockObj )
		{

			var html = '';
			var $clone = $( currentBlockObj ).clone( false, false );
			var subColHTML = [], newOrderedIds = [];

			$( $clone ).find( '.layoutmenu' ).remove();
			$( $clone ).find( '.dum' ).remove();

			$( $clone ).find( '[style],[title]' ).each( function ()
			{
				$( this ).removeAttr( 'style' ).removeAttr( 'title' );
			} );

			$( $clone ).find( '.itemBox' ).each( function ()
			{
				var id = $( this ).attr( 'id' );
				if ( $( this ).hasClass( 'dum' ) ) {
					$( this ).remove();
				}
				else {
					if ( !$( this ).parents( '.subcolumns' ).length && !$( this ).hasClass( 'subcolumns' ) && id !== 'undefined' ) {
						if ( id ) {
							newOrderedIds.push( id );
						}

						$( this ).replaceWith( '[' + id + ']' + "\n" );
					}
					else if ( $( this ).hasClass( 'subcolumns' ) && id !== 'undefined' ) {
						subColHTML.push( '[cols:' + id + ']' + $( this ).html() + '[/colsend:' + id + ']' );

						var str = '[START:' + id + ']' + "\n";
						$( '<p>' ).append( str ).insertBefore( $( this ) );

						str = '[/END:' + id + ']' + "\n";
						$( '<p>' ).append( str ).insertAfter( $( this ) );

						if ( id ) {
							newOrderedIds.push( id );

							$( this ).find( '.itemBox' ).each( function ()
							{
								var _id = $( this ).attr( 'id' );
								if ( $( this ).hasClass( 'dum' ) ) {
									$( this ).remove();
								}
								else {
									if ( !$( this ).hasClass( 'subcolumns' ) && _id !== 'undefined' ) {
										if ( _id ) {
											newOrderedIds.push( _id );
										}

										$( this ).replaceWith( '[' + _id + ']' + "\n" );
									}
								}
							} );

						}

					}
				}
			} );

			/*

			 $($clone).find('.subcolumns').each(function() {

			 $(this).removeAttr('aria-disabled').removeAttr('class').removeAttr('style').attr('class', 'subcolumns').removeAttr('title');

			 var id = $(this).attr('id');

			 subColHTML.push('[cols:' + id + ']' + $(this).html() + '[/colsend:' + id + ']');

			 var str = '[START:' + id + ']' + "\n";
			 $('<p>').append(str).insertBefore($(this));

			 str = '[/END:' + id + ']' + "\n";
			 $('<p>').append(str).insertAfter($(this));


			 //    html += '[START:' + id + ']' + "\n" + $(this).html() + '[/END:' + id + ']' + "\n";

			 if (id)
			 {
			 newOrderedIds.push(id);

			 $(this).find('.itemBox').each(function() {
			 var _id = $(this).attr('id');
			 if ($(this).hasClass('dum')) {
			 $(this).remove();
			 }
			 else
			 {
			 if (!$(this).hasClass('subcolumns') && _id !== 'undefined')
			 {
			 if (_id)
			 {
			 newOrderedIds.push(_id);
			 }

			 $(this).replaceWith('[' + _id + ']' + "\n");
			 }
			 }
			 });

			 }
			 });

			 */
			// console.log(html);

			$( $clone ).find( 'p' ).each( function ()
			{
				$( this ).replaceWith( $( this ).text() );
			} );

			$clone.removeAttr( 'title' ).removeClass( 'ui-droppable ui-draggable ui-droppable-disabled ui-state-disabled ui-sortable ui-sortable dropprotect sortzone ui-sortable-helper dropzone ui-draggable ui-draggable-disabled' ).removeAttr( 'aria-disabled' )
				.find( '.ui-droppable,.ui-droppable-disabled,.ui-draggable,.ui-state-disabled,.ui-sortable,.ui-sortable-disabled,.dropprotect,.sortzone,.dropaccept,.connectedSortable,.ui-sortable-helper,.dropzone' ).removeAttr( 'title' )
				.removeAttr( 'title' ).removeClass( 'ui-droppable ui-draggable ui-droppable-disabled ui-state-disabled ui-sortable ui-sortable dropprotect sortzone dropaccept connectedSortable ui-sortable-helper dropzoneui-draggable ui-draggable-disabled' ).removeAttr( 'aria-disabled' );

			var i;

			/*
			 var newhtml = $($clone).html();
			 if (newhtml)
			 {
			 var founds = newhtml.match(/\[([a-zA-Z0-9_\-:]*)\]/g);
			 for (i in founds) {
			 var str = founds[i];
			 str = str.replace('[', '');
			 str = str.replace(']', '');
			 str = str.replace('START:', '');
			 newOrderedIds.push(str);
			 }
			 }
			 */

			var strOrdered = newOrderedIds.join( ',' );

			var data = [];
			data.push( strOrdered );

			var cloneCode = $( $clone ).html();
			if ( typeof cloneCode !== 'string' ) {
				console.log( 'cloneCode is not a string' );
				//   console.log([currentBlockObj]);
				return data;
			}

			var html = this.formatXml( cloneCode );
			data.push( html );

			var subColCode = subColHTML.join( "\n" );
			if ( typeof subColCode !== 'string' ) {
				console.log( 'subColCode is not a string' );
				return data;
			}

			data.push( this.formatXml( subColCode ) );
			return data;
		},
		/**
		 * Will save all sub cols.
		 * @param function callback is optional
		 * @returns {undefined}
		 */
		saveColsLayout: function ( callback )
		{
			var self = this;
			$( 'body' ).css( 'cursor', 'wait' );

			var elements = this.context.find( '.mainBox' );
			elements.each( function ()
			{
				var parentVisible = $( this ).parent().is( ':visible' );
				var selfVisible = $( this ).is( ':visible' );
				//if (parentVisible && selfVisible)
				// {
				self.updateDBSubCols( $( this ) );
				//}
			} );

			$( 'body' ).css( 'cursor', '' );
			if ( typeof callback == 'function' ) {
				callback();
			}
		},
		/**
		 * Save cols in the currentBlockObj
		 * @param object currentBlockObj
		 * @returns {unresolved}
		 */
		updateDBSubCols: function ( currentBlockObj )
		{
			var block = $( currentBlockObj ).attr( 'id' );
			var cols = $( '.cols input:checked', this.Builder ).val();
			var layoutid = parseInt( this.settings.layoutID, 0 ); //$('#layout-id', $('#' + Win.windowID)).val();
			var neworder = [];

			$( 'body' ).css( 'cursor', 'wait' );

			$( currentBlockObj, this.context ).find( '.itemBox' ).each( function ()
			{
				if ( typeof $( this ).attr( 'id' ) != 'undefined' && !$( this ).hasClass( 'subcolumns' ) ) {
					neworder.push( $( this ).attr( 'id' ) );
				}
			} );

			var newdata = neworder.join( ',' );

			// hmmm
			if ( typeof block !== 'string' ) {
				$( 'body' ).css( 'cursor', '' );
				console.log( 'STOP updateDBSubCols!' );
				return;
			}

			var data = this.prepareSubColHTML( $( currentBlockObj ) );
			newdata = data[0];
			var html = typeof data[1] == 'string' ? data[1] : '';

			if ( typeof newdata !== 'string' ) {
				$( 'body' ).css( 'cursor', '' );
				console.log( 'prepareSubColHTML returns not the new HTML Code! STOP updateDBSubCols!' );
				return;
			}

			$.post( 'admin.php', {
					adm: 'layouter',
					action: 'savesubcols',
					layoutid: layoutid,
					cols: cols,
					layoutblock: block,
					neworder: newdata,
					//   subneworders: subneworders,
					htmldata: html
				},
				function ( data )
				{

					$( 'body' ).css( 'cursor', '' );

					if ( Tools.responseIsOk( data ) ) {

					}
					else {
						console.log( [data] );
						// alert(data.msg);
					}
				}, 'json' );
		}

	};
};


