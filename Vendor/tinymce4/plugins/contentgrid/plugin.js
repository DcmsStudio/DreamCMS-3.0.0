/**
 * Created by marcel on 21.03.14.
 */

tinymce.PluginManager.requireLangPack('contentgrid');
tinymce.PluginManager.add( 'contentgrid', function ( editor )
{

	var layouts = {
		5050: '<div class="contentcols"><div class="c50">xxx0</div><div class="c50">xxx0</div></div>',
		3366: '<div class="contentcols"><div class="c33">xxx0</div><div class="c66">xxx0</div></div>',
		6633: '<div class="contentcols"><div class="c66">xxx0</div><div class="c33">xxx0</div></div>',
		3862: '<div class="contentcols"><div class="c38">xxx0</div><div class="c62">xxx0</div></div>',
		6238: '<div class="contentcols"><div class="c62">xxx0</div><div class="c38">xxx0</div></div>',
		2575: '<div class="contentcols"><div class="c25">xxx0</div><div class="c75">xxx0</div></div>',
		7525: '<div class="contentcols"><div class="c75">xxx0</div><div class="c25">xxx0</div></div>',
		333333: '<div class="contentcols"><div class="c33">xxx0</div><div class="c33">xxx0</div><div class="c33">xxx0</div></div>',
		25252525: '<div class="contentcols"><div class="c25">xxx0</div><div class="c25">xxx0</div><div class="c25">xxx0</div><div class="c25">xxx0</div></div>',
		2020202020: '<div class="contentcols"><div class="c20">xxx0</div><div class="c20">xxx0</div><div class="c20">xxx0</div><div class="c20">xxx0</div><div class="c20">xxx0</div></div>',

		255025: '<div class="contentcols"><div class="c25">xxx0</div><div class="c50">xxx0</div><div class="c25">xxx0</div></div>',
		252550: '<div class="contentcols"><div class="c25">xxx0</div><div class="c25">xxx0</div><div class="c50">xxx0</div></div>',
		502525: '<div class="contentcols"><div class="c50">xxx0</div><div class="c25">xxx0</div><div class="c25">xxx0</div></div>',

		8020: '<div class="contentcols"><div class="c80">xxx0</div><div class="c20">xxx0</div></div>',
		2080: '<div class="contentcols"><div class="c20">xxx0</div><div class="c80">xxx0</div></div>'

	};

	function getContentGrid( value )
	{

		if ( value ) {
			if ( typeof layouts[value] === 'string' ) {
				var layout = layouts[value], x = 0;

				return '<p> </p>' + layout.replace( /xxx0/g, '<div class="c"><p>'+ tinyMCE.translate('cgColumn') +'</p></div>' ).replace( /"contentcols"/g, '"contentcols" data-options="' + value + '"' ) + '<p> </p>';
			}
		}
		return '';
	}

	editor.on( 'init', function ()
	{
		var loader = new tinymce.dom.StyleSheetLoader( document );
		loader.load( tinyMCE.baseURL + "/plugins/contentgrid/css/content.css" );
	} );

	var selected = false;

	editor.on( 'NodeChange', function ( e )
	{
		selected = false;

		if ( e.element ) {
			if ( editor.dom.getParents( e.element, 'div.contentcols' ).length ) {
				var p = editor.dom.getParents( e.element, 'div.contentcols' );
				selected = p[0].getAttribute( 'data-options' );
			}
			else {
				selected = false;
			}
		}

	} );




	editor.addButton( 'contentgrid', function ()
	{
		var items = [];

		function additem( k, v )
		{
			items.push( {
				text: {raw: k},
				value: v
			} );
		}

		// Add some values to the list box
		additem( '50 x 50', '5050' );
		//
		additem( '33 x 66', '3366' );
		additem( '66 x 33', '6633' );
		additem( '38 x 62', '3862' );
		additem( '62 x 38', '6238' );
		additem( '25 x 75', '2575' );
		additem( '75 x 25', '7525' );
		additem( '20 x 80', '2080' );
		additem( '80 x 20', '8020' );
		additem( '33 x 33 x 33', '333333' );
		additem( '25 x 50 x 25', '255025' );
		additem( '25 x 25 x 50', '252550' );
		additem( '50 x 25 x 25', '502525' );
		additem( '25 x 25 x 25 x 25', '25252525' );
		additem( '20 x 20 x 20 x 20 x 20', '2020202020' );

		function getHtml()
		{
			var url = tinyMCE.baseURL + '/plugins/contentgrid/img/';
			var list = '<div style="height: 300px;width: 486px">';

			list += '<span class="contentgrid-desc">'+ tinyMCE.translate('cgTip') +'</span>';

			for ( var i = 0; i < items.length; i++ ) {
				var extra = '';
				if ( selected && selected == items[i].value ) {
					extra += ' sel';
				}

				list += '<div class="contentgrid-cols' + extra + '" rel="' + items[i].value + '">' +
                    '<span style="background: url(' + url + 'contentgrid-' + items[i].value + '.png)"></span> ' +
                    //'<img width="150" height="74" src="' + url + 'contentgrid-' + items[i].value + '.png" style="vertical-align:middle;" />' +
                    '</div>';
			}

			list += '</div>';

			return list;
		}

		return {
			type: 'panelbutton',
			text: '',
			tooltip: 'Insert Contentgrid',
			//	values: items,
			fixedWidth: true,
			height: 130,
			autofix: true,
            align: 'center',
			role: 'contentgrid',
			stateSelector: ['div[class=contentcols]'],
			onclick: function ( e )
			{

				// console.log(e)
				var sel = editor.selection.getNode();

				if ( e.control && e.control.panel && e.control.panel._id ) {
					if ( editor.dom.getParents( sel, 'div.contentcols' ).length ) {
						var p = editor.dom.getParents( sel, 'div.contentcols' );
						var type = p[0].getAttribute( 'data-options' );

						var d = new tinymce.dom.DomQuery( e.control );
						var panel = $( 'div#' + e.control.panel._id );
						$( 'div.contentgrid-cols', panel ).removeClass( 'sel' );
						var item = $( 'div[rel=' + type + ']', panel );

						if ( item.length ) {
							item.addClass( 'sel' )
						}
					}
				}

			},
			panel: {
				role: 'application',
				autohide: false,
				html: getHtml,
                align: 'center',
				onclick: function ( e )
				{

                    this.hide();

                    var sel = editor.selection.getNode();
					var el = editor.dom.getParent( e.target, 'div' );

                    var bookmark = editor.selection.getBookmark();

					if ( el && el.getAttribute( 'rel' ) ) {

						var rel = el.getAttribute( 'rel' );
						var v = getContentGrid( rel );


						if (editor.dom.getParents( sel, 'div.contentcols' ).length && !e.metaKey) {
							var p = editor.dom.getParents( sel, 'div.contentcols' );
							editor.selection.select( p[0] );

							var cloned = $( p[0] ).clone();
							var allChilds = $(p[0]).find('div.c' ).children();
							allChilds.each(function() {
								if ($(this ).get(0 ).innerHTML == tinyMCE.translate('cgColumn')) {
									$(this ).remove();
								}
							});



							var _v = $(v);
								_v.find('div.c:first' ).empty().append(allChilds);
							var span = $('<span></span>' ).append(_v);
							var html = span.get(0 ).innerHTML;

							$(p[0]).replaceWith( html );

							editor.undoManager.transact(function() {
								editor.setContent(editor.getContent({raw: true}));
							});

							editor.nodeChanged();
						}
						else {
							if ( sel && sel.innerHTML != '<br data-mce-bogus="1">' && rel ) {
								editor.selection.select( sel );


								var cloned = $( sel ).clone();
								cloned.each(function() {
									if ($(this ).get(0 ).innerHTML == tinyMCE.translate('cgColumn')) {
										$(this ).remove();
									}
								});



								var vs = $(v);
								vs.find( 'div.c:first p' ).empty().append( cloned );


								var span = $('<span></span>' ).append(vs);
								var html = span.get(0 ).innerHTML;
								// sel.outerHTML = html;

								$(sel).replaceWith( html );

								editor.undoManager.transact(function() {
									editor.setContent(editor.getContent({raw: true}));
								});
								editor.nodeChanged();
							}
							else {
								editor.execCommand( 'mceInsertContent', true, v );
							}
						}


					}

                    editor.selection.moveToBookmark(bookmark);


				}
			}
		};
	} );

} );