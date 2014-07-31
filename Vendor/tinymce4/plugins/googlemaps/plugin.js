tinymce.PluginManager.requireLangPack( 'googlemaps' );

tinymce.PluginManager.add( 'googlemaps', function ( editor )
{

	if (typeof tinymce.mapID == 'undefined') {
		tinymce.mapID = 0;
	}

	function generateMapStaticUrl( opt )
	{
		function a( g )
		{
			var e = [];
			for ( var f in g ) {
				e.push( f + "=" + escape( g[f] ) );
			}
			return e.join( "&" );
		}

		function d()
		{
			var e = [];
			for ( var f = 0; f < opt.marker.length; f++ ) {
				e.push( opt.marker[f].slice( 0, 2 ).join( "," ) );
			}
			return e.join( "|" );
		}

		var c = {
			maptype: opt.mapType.toLowerCase(),
			center: opt.lat + "," + opt.lon,
			zoom: opt.zoom,
			size: opt.width + "x" + opt.height,
			maptype: opt.mapType,
			markers: opt.lat + "," + opt.lon,
			sensor: false,
			format: 'png32',
			scale: 1
		};


		if (typeof editor.getParam('gApiKey') != 'undefined') {
			c.key = editor.getParam('gApiKey');
		}



		return "http://maps.googleapis.com/maps/api/staticmap?" + a( c );
	}

	function b( opt, o )
	{
		return tinyMCE.DOM.createHTML( "img", {
			width: o.width,
			height: o.height,
			id: opt.id,
			"data-mce-object": "googlemaps",
			'data-mce-resize': true,
			src: (opt.url),
			"class": "googlemaps",
			'data-maps': 'true',
			"data-options": escape( opt.options )
		//	"data-html": escape( opt.html )
		} );
	}

	// get a unique id for the div
	function getMapId()
	{
		var i = 1;


		do {
			tinymce.mapID = "googlemap-" + (i++).toString();
		} while ( editor.dom.doc.getElementById( tinymce.mapID ) );



		return tinymce.mapID;
	}

	function editorPostInitFunc( editor, b )
	{
		function a( f )
		{
			var d = new tinymce.html.Serializer().serialize( f );
			var e = document.createElement( "div" );
			e.innerHTML = d;
			return e.firstChild.innerHTML;
		}

		function b( opt, o )
		{
			return tinyMCE.DOM.createHTML( "img", {
				width: o.width,
				height: o.height,
				id: opt.id,
				"data-mce-object": "googlemaps",
				'data-mce-resize': true,
				src: (opt.url),
				"class": "googlemaps",
				'data-maps': 'true',
				"data-options": escape( opt.options )
			//	"data-html": escape( opt.html )
			} );
		}

        var bookmark = editor.selection.getBookmark();


		editor.parser.addNodeFilter( "div", function ( d, e )
		{
			for ( var g = 0; g < d.length; g++ ) {
				var node = d[g];

				if ( node.attr( "data-options" ) && node.attr( "class" ) == "googlemaps" ) {
					//var f = a( node );
					//var h = f.match( /googlemap\-\d+/ )[0];
					//var w = win.getContentWindow();
					//var c = node.clone();
					//var f = a( c );
					var t = b( {
						id: getMapId(),
						url: generateMapStaticUrl( JSON.parse( unescape( node.attr( "data-options" ) ) ) ),
						options: unescape( node.attr( "data-options" ) )
					//	html: node.clone()
					}, JSON.parse( unescape( node.attr( "data-options" ) ) ) );

					node.replace(
						(new tinyMCE.html.DomParser( {
							validate: false
						} )).parse( t ) );

				}

			}
		} );

        editor.selection.moveToBookmark(bookmark);
	}

	function editorPreInitFunc( editor, b )
	{
		function a( f )
		{
			var d = new tinymce.html.Serializer().serialize( f );
			var e = document.createElement( "div" );
			e.innerHTML = d;
			return e.firstChild.innerHTML;
		}

		return function ()
		{
            var bookmark = editor.selection.getBookmark();

			editor.serializer.addNodeFilter( "img", function ( d, e )
			{

				for ( var f = 0; f < d.length; f++ ) {

					if ( d[f].attr( "data-options") && d[f].attr( "class" ) == "googlemaps"  ) {

						d[f].replace( (new tinyMCE.html.DomParser( {
							validate: false
						} )).parse( '<div class="googlemaps" id="'+ d[f].attr('id') +'" data-options="' + d[f].attr( "data-options" ) + '"> &nbsp;- </div>'
						/* + unescape( d[f].attr( "data-html" ) ) + "</div>" */
							) );
					}
				}
			} );

			editor.parser.addNodeFilter( "div", function ( d, e )
			{


				for ( var g = 0; g < d.length; g++ ) {
					node = d[g];
					if ( node.attr( "class" ) == "googlemaps" && node.attr( "data-options" ) ) {
						if ( node.attr( "data-options" ) ) {
							//var f = a( node );
							//var h = f.match( /googlemap\-\d+/ )[0];
							//var w = win.getContentWindow();

							var t = b( {
								id: node.attr('id'),
								url: generateMapStaticUrl( JSON.parse( unescape( node.attr( "data-options" ) ) ) ),
								options: unescape( node.attr( "data-options" ) )
							//	html: f
							}, JSON.parse( unescape( node.attr( "data-options" ) ) ) );

							node.replace(
								(new tinyMCE.html.DomParser( {
									validate: false
								} )).parse( t ) );
						}
					}
				}


            } );
            editor.selection.moveToBookmark(bookmark);
		};
	}

	function getHtmlCode( opt )
	{
		return '';

		strHtml = '<div id="' + opt.id + '" style="position:relative;width: ' + opt.width + 'px;height:' + opt.height + 'px"></div>';
		return strHtml;

		strHtml += '<script type="application/javascript"> if ( typeof jQuery != \'undefined\' ) {\n';
		strHtml += '	$(document).ready(function() {\n';
		strHtml += '		var opts = {\n';

		strHtml += '			lat: ' + opt.lat + ',\n';
		strHtml += '			lon: ' + opt.lon + ',\n';
		strHtml += '			zoom: ' + opt.zoom + ',\n';
		strHtml += '			size: {width: ' + opt.width + ', height: ' + opt.height + '},\n';

		switch ( opt.mapType.toUpperCase() ) {
			case 'SATELLITE':
				strHtml += '			mapType: "SATELLITE",\n';
				break;
			case 'HYBRID':
				strHtml += '			mapType: "HYBRID",\n';
				break;
			case 'NORMAL':
			case 'PHYSICAL':
			default:
				strHtml += '			mapType: "",\n';
				break;
		}

		if ( opt.showScale ) {
			strHtml += '			showScale: true,\n';
		}
		else {
			strHtml += '			showScale: false,\n';
		}
		strHtml = strHtml.replace( /,\n$/g, '' );
		strHtml += '		};\n';
		strHtml += '		if (typeof Maps != "undefined") { ';
		strHtml += '   			var map = new Maps("#' + opt.id + '", opts); ';
		strHtml += '		}';
		strHtml += '	});';
		strHtml += '}';
		strHtml += 'else { document.write("<br/><br/>No jQuery found...<br/><br/>"); }</script>';

		return strHtml.replace( '{{mapid}}', opt.id );
	}

	function openmanager()
	{

		var elm = editor.selection.getNode();
		var startcoordinates = '', zoom = false, size = false, maptype = false;

		var params = {};

		if ( editor.dom.hasClass( elm, 'googlemaps' ) ) {
			editor.selection.select( elm );
			params = JSON.parse( unescape( elm.getAttribute( "data-options" ) ) );

			if ( elm.offsetWidth ) {
				params.width = elm.offsetWidth;
			}

			if ( elm.offsetHeight ) {
				params.height = elm.offsetHeight;
			}
		}

		params.locationLookupEnabled = true;
		params.apiKey = editor.getParam( 'googleapikey' ) //|| top.Config.get('googleapikey');

		var title = "Google Maps";
		win = editor.windowManager.open( {
			title: title,
			file: tinyMCE.baseURL + '/plugins/googlemaps/googlemaps.htm',
			width: 640,
			height: 450,
			inline: 1,
			buttons: [
				{
					text: 'Insert',
					subtype: 'primary',
					onclick: function ()
					{
						// Top most window object
						var win = editor.windowManager.getWindows()[0];
						var u = win.getContentWindow().generateCodeMap();
						u.id = getMapId();

						var t = b( {
							id: u.id,
							url: generateMapStaticUrl( u ),
							options: JSON.stringify( u )//,
						//	html: getHtmlCode( u )
						}, u );

						var sel = editor.selection.getNode();

						if (editor.dom.hasClass( sel, 'googlemaps' ) && sel.tagName == 'IMG' && sel.getAttribute('data-mce-object') === 'googlemaps' ) {
							$(sel).replaceWith(t);
						}
						else {
							// Insert
							editor.insertContent( t )
						}

						// Close the window
						win.close();
					}
				},

				{text: 'Close', onclick: 'close'}
			]
		}, params );
	}

	editor.on( 'ObjectResized', function ( e )
	{

		if ( e.target.getAttribute( "class" ) == "googlemaps" ) {
			var unescaped = unescape( e.target.getAttribute( "data-options" ) );
			var opt = JSON.parse( unescaped );
			opt.width = e.width;
			opt.height = e.height;

			var html = getHtmlCode( opt );

			//	e.target.setAttribute("data-options", escape(JSON.stringify(opt)) );

			var url = generateMapStaticUrl( opt );
			e.target.setAttribute( "src", url );
			e.target.setAttribute( "data-options", escape( JSON.stringify( opt ) ) );
			// e.target.setAttribute( "data-html", escape( html ) );
		}

	} );

	editor.addButton( 'googlemaps', {
		icon: ' fa fa-map-marker',
		//image: tinyMCE.baseURL + '/plugins/googlemaps/img/map.gif',
		tooltip: 'Create Google Map',
		stateSelector: ['img[data-mce-object=googlemaps]'],
		onclick: openmanager
	} );

	editor.on( "preInit", editorPreInitFunc( editor, b ) );
/*
	editor.on( 'PostProcess', function ( e )
	{
		editorPostInitFunc( editor, e )
	} );
*/
	//editor.on( "postInit", editorPostInitFunc( editor, b ) );

} );
/*
 (function ()
 {
 var DOM = tinymce.DOM;

 tinymce.PluginManager.requireLangPack( 'mcegooglemaps' );
 tinymce.create( 'tinymce.plugins.GooglemapsPlugin', {
 ed: null,
 init: function ( ed, url )
 {
 var d = this;
 d.editor = ed;
 this.ed = ed;

 ed.addCommand( 'mcegooglemap', function ()
 {

 var elm = ed.selection.getNode();
 var startcoordinates = '', zoom = false, size = false, maptype = false;
 if ( ed.dom.hasClass( elm, 'googlemaps_dummy' ) ) {

 var rel = elm.getAttribute( 'rel' );
 var s = rel.split( ';' );

 startcoordinates = s[0];
 zoom = s[1];
 size = s[2];
 maptype = s[3];
 }

 ed.windowManager.open( {
 file: url + '/googlemaps.htm?plugin_googleMaps_apiKey=' + top.Config.get( 'googleapikey' ),
 width: 640 + parseInt( ed.getLang( 'googlemaps.delta_width', 0 ) ),
 height: 500 + parseInt( ed.getLang( 'googlemaps.delta_height', 0 ) ),
 inline: 1
 },
 {
 plugin_url: url,
 plugin_googleMaps_apiKey: ed.getParam( "plugin_googleMaps_apiKey", top.Config.get( 'googleapikey' ) ),
 plugin_googleMaps_coordinates: startcoordinates ? startcoordinates : ed.getParam( "plugin_googleMaps_coordinates", '' ),
 plugin_googleMaps_showCoordinates: ed.getParam( "plugin_googleMaps_showCoordinates", "true" ),
 plugin_googleMaps_locationLookupEnabled: ed.getParam( "plugin_googleMaps_locationLookupEnabled", "true" ),
 plugin_googleMaps_zoom: zoom,
 plugin_googleMaps_size: size,
 plugin_googleMaps_maptype: maptype
 } );
 } );

 ed.onClick.add( function ( ed, e )
 {
 e = e.target;

 if ( e.nodeName === 'div' && ed.dom.hasClass( e, 'googlemaps_dummy' ) ) {
 ed.selection.select( e );
 }
 } );

 ed.addCommand( 'mceGooglemapDelete', function ()
 {
 var gdoc = ed.getDoc();
 ed.dom.remove( gdoc.getElementById( 'spangooglemaps' ) );
 } );

 ed.onNodeChange.add( function ( ed, cm, n )
 {
 if ( n.nodeName.toLowerCase() === 'div' && ed.dom.hasClass( n, 'googlemaps_dummy' ) ) {
 cm.setActive( 'mcegooglemaps', true );
 }
 else {
 cm.setActive( 'mcegooglemaps', false );
 }
 //cm.setActive('mcegooglemaps', (n.nodeName === 'div' && ed.dom.hasClass(n, 'googlemaps_dummy') || n.nodeName === 'div' && ed.dom.hasClass(n, 'googlemaps_dummy') ) );
 } );

 ed.addCommand( "mceInsertGoogleMap", this._insertGoogleMap, this );

 ed.onVisualAid.add( this._visualAid, this );

 ed.addButton( 'mcegooglemaps', {title: 'googlemaps.desc', cmd: 'mcegooglemap', image: url + '/img/map_add.gif'} );
 ed.addButton( 'googlemapsdel', {title: 'googlemaps.deldesc', cmd: 'mceGooglemapDelete', image: url + '/img/map_delete.gif'} );
 },
 _visualAid: function ( ed, e, s )
 {
 var dom = ed.dom;

 tinymce.each( dom.select( 'div.googlemaps_dummy', e ), function ( e )
 {
 if ( /^(absolute|relative|fixed)$/i.test( e.style.position ) || !e.style.position ) {
 if ( s )
 dom.addClass( e, 'mceItemVisualAid' );
 else
 dom.removeClass( e, 'mceItemVisualAid' );

 dom.addClass( e, 'mceItemLayer' );
 }
 } );
 },
 getInfo: function ()
 {
 return {
 longname: 'MCEGoogleMaps',
 author: 'Christian Ladewig (Original by: Cees Rijken <http://www.connectcase.nl>); Further development by Jeroen van der Stijl (jeroen@viverosoft.com)',
 authorurl: 'http://www.klmedien.de',
 infourl: 'https://sourceforge.net/projects/mcegooglemap',
 version: "1.0.0 alpha"
 };
 },
 _insertGoogleMap: function ( ui, v )
 {

 var mapId;
 var i = 0;
 // get a unique id for the div
 do {
 mapId = "divMap" + (i++).toString();
 } while ( document.getElementById( mapId ) != null )

 var strHtml = '<span>\n';

 strHtml += '<div class="googlemaps_dummy mceItemVisualAid mceItemLayer" id="' + mapId + '" rel="' + v.coordinates + ';' + v.zoom + ';' + v.width + ',' + v.height + ';' + v.mapType + '" style="background:#d3d3d3;padding:3px;width: ' + v.width + 'px; height: ' + v.height + 'px;" class="googlemaps_dummy">Placeholder for GoogleMap. Use the preview button to see the real map.';

 strHtml += '<script type="text/javascript">\n';
 /*
 strHtml += '<script src="http://maps.google.com/maps?file=api&amp;v=2&amp;key=' + v.akey + '" type="text/javascript"></script>';
 strHtml += '<script type="text/javascript">\n';

 strHtml += 'function load() \n';
 strHtml += '{\n';
 strHtml += '  if (GBrowserIsCompatible())\n';
 strHtml += '  {\n';
 strHtml += '  var map = new GMap2(document.getElementById("' + mapId + '"))\;\n';
 strHtml += '  var center = new GLatLng(' + v.coordinates + ')\;\n';
 strHtml += '  map.setCenter(center, ' + v.zoom + ')\;\n';
 strHtml += '  map.addOverlay(new GMarker(center))\;\n';

 strHtml += '\n\n';

 switch (v.hud) {
 case "GLargeMapControl3D":
 strHtml += '  var mapControl = new GLargeMapControl3D();\n';
 strHtml += '  map.addControl(mapControl);\n';
 break;
 case "GLargeMapControl":
 strHtml += '  var mapControl = new GLargeMapControl();\n';
 strHtml += '  map.addControl(mapControl);\n';
 break;
 case "GSmallMapControl":
 strHtml += '  var mapControl = new GSmallMapControl();\n';
 strHtml += '  map.addControl(mapControl);\n';
 break;
 case "GSmallZoomControl3D":
 strHtml += '  var mapControl = new GSmallZoomControl3D();\n';
 strHtml += '  map.addControl(mapControl);\n';
 break;
 case "GSmallZoomControl":
 strHtml += '  var mapControl = new GSmallZoomControl();\n';
 strHtml += '  map.addControl(mapControl);\n';
 break;
 }

 if (v.showScale) {
 strHtml += '\n\n';
 strHtml += '  var scaleControl = new GScaleControl();\n';
 strHtml += '  map.addControl(scaleControl);\n';
 }
 else {
 strHtml += 'var customUI = map.getDefaultUI(); customUI.controls.scalecontrol = false; ';


 }

 if (v.showOverview) {
 strHtml += '\n\n';
 strHtml += '  var overviewControl = new GOverviewMapControl();\n';
 strHtml += '  map.addControl(overviewControl);\n';
 }

 strHtml += '\n\n';

 strHtml += '  var mapTypeControl = new GHierarchicalMapTypeControl();\n';
 strHtml += '  map.addControl(mapTypeControl);\n';
 strHtml += '  map.addMapType(G_PHYSICAL_MAP);\n';

 strHtml += '\n\n';

 switch (v.mapType) {
 case "G_NORMAL_MAP":
 strHtml += '  map.setMapType(G_NORMAL_MAP);\n';
 break;
 case "G_SATELLITE_MAP":
 strHtml += '  map.setMapType(G_SATELLITE_MAP);\n';
 break;
 case "G_HYBRID_MAP":
 strHtml += '  map.setMapType(G_HYBRID_MAP);\n';
 break;
 case "G_PHYSICAL_MAP":
 strHtml += '  map.setMapType(G_PHYSICAL_MAP);\n';
 break;
 }

 strHtml += '  }\n';
 strHtml += '}\n';

 strHtml += 'function addLoadEvent(func) \n';
 strHtml += '{\n';
 strHtml += 'var oldonload = window.onload;\n';
 strHtml += '  if (typeof window.onload != \'function\') \n';
 strHtml += '  {\n';
 strHtml += '  window.onload = func;\n';
 strHtml += '  } \n';
 strHtml += '  else \n';
 strHtml += '  {\n';
 strHtml += '  window.onload = function() \n';
 strHtml += '    {\n';
 strHtml += '    if (oldonload) \n';
 strHtml += '    {\n';
 strHtml += '    oldonload();\n';
 strHtml += '    }\n';
 strHtml += '    func();\n';
 strHtml += '}\n';
 strHtml += '}\n';
 strHtml += '}\n';

 strHtml += 'addLoadEvent(load)\;\n';

 strHtml += 'if (window.attachEvent) {\n';
 strHtml += '  window.attachEvent("onunload", function() {\n';
 strHtml += '  GUnload();      // Internet Explorer\n';
 strHtml += '        });\n';
 strHtml += '} else {\n';
 strHtml += 'window.addEventListener("unload", function() {\n';
 strHtml += 'GUnload(); // Firefox and standard browsers\n';
 strHtml += '    }, false);\n';
 strHtml += '}\n';
 * /
 strHtml += 'if ( typeof $ != \'undefined\' ) { $(document).ready(function() { var opts = {\n';

 var l = v.coordinates.split( ',' );

 strHtml += 'lat: ' + l[0] + ',\n';
 strHtml += 'lon: ' + l[1] + ',\n';
 strHtml += 'zoom: ' + v.zoom + ',\n';
 strHtml += 'size: {width: ' + v.width + ', height: ' + v.height + '},\n';

 switch ( v.mapType ) {
 case "G_NORMAL_MAP":
 strHtml += 'mapType: "",\n';
 break;
 case "G_SATELLITE_MAP":
 strHtml += 'mapType: "SATELLITE",\n';
 break;
 case "G_HYBRID_MAP":
 strHtml += 'mapType: "HYBRID",\n';
 break;
 case "G_PHYSICAL_MAP":
 strHtml += 'mapType: "",\n';
 break;
 }

 if ( v.showScale ) {
 strHtml += 'showScale: true,\n';
 }
 else {
 strHtml += 'showScale: false,\n';
 }

 strHtml += '};\n';
 strHtml += 'var map = new Maps(\'#' + mapId + '\', opts); }); } else { document.write("<br/><br/>No jQuery found...<br/><br/>"); }';
 strHtml += '</script>\n';
 +'</div>\n';
 strHtml += '</span>\n';
 var el = false, elm = this.ed.selection.getNode();
 if ( elm ) {
 // this.ed.selection.setContent(strHtml);
 if ( this.ed.dom.hasClass( elm, 'googlemaps_dummy' ) ) {
 elm.outerHTML = strHtml;
 //this.editor.execCommand('mceInsertContent', false, strHtml);
 }
 else {
 this.ed.selection.setContent( strHtml );
 }

 }
 else {
 this.editor.execCommand( 'mceInsertContent', false, strHtml );
 }
 }
 } );

 tinymce.PluginManager.add( 'mcegooglemaps', tinymce.plugins.GooglemapsPlugin );
 })();

 */