/**
 * Created by marcel on 21.03.14.
 */
tinymce.PluginManager.add( 'contenttabs', function ( editor )
{
	var items = [];

	function additem( k, v )
	{
		items.push( {
			text: {raw: k},
			value: v
		} );
	}

	for (var i = 2; i < 11; ++i) {
		additem(i + ' Tabs', i);
	}


	function getTabs( e )
	{
		if ( e.control.settings.value ) {

			var tabs = parseInt(e.control.settings.value, 10);
			if (tabs) {

				var e = editor, Id, i = 0;
				// get a unique id for the div
				do {
					Id = (i++).toString();
				} while ( e.contentDocument.getElementById('ct-'+Id) != null)


				var cachedIds = [];
				for (var i = 0; i < tabs; ++i) {
					cachedIds.push(Id);
					Id++;
				}

				var out = '<div class="tabsection"><ul class="nav nav-tabs">';

				for (var i = 0; i < tabs; ++i) {
					out += '<li class="'+ (i == 0?'active':'') +'"><a href="#ct-'+ cachedIds[i] +'" data-toggle="tab">Your label '+ i +'</a></li>';
				}
				out += '</ul>';
				out += '<div class="tab-content">';
				for (var i = 0; i < tabs; ++i)
				{
					out += '<div id="ct-'+ cachedIds[i] +'" class="tab-pane c-'+ i +' fade'+ (i == 0?' active in':'') +'"><p>Your Tab Content '+ i +'</p></div>';
				}
				out += '</div></div>';
				return out;
			}
		}
		return '';
	}


	editor.on( 'init', function ()
	{
		var loader = new tinymce.dom.StyleSheetLoader(document);
		loader.load(tinyMCE.baseURL + "/plugins/contenttabs/css/content.css" );
	} );

	editor.addButton( 'contenttabs', function ()
	{



		return {
			type: 'listbox',
			text: 'Content Tabs',
			tooltip: 'Content Tabs',
			values: items,
			fixedWidth: true,
			height: 200,
			onselect: function ( e )
			{
				if ( e.control.settings.value ) {
					var v = getTabs(e);
					editor.execCommand('mceInsertContent', true, v);
				}
				this.value(null); // reset
			}
		};

	} );





});



