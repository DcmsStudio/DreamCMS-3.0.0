var w = getWin();
var editor = parent.tinymce.EditorManager.activeEditor;
var params = editor.windowManager.getParams();
var trans = w.tinymce.util.I18n;
var intialCoordinates;
var isDraggable;
var map;
var marker;
var htmlCode = '';

function unloadGooglemap()
{

}

function getWin()
{
	return(!window.frameElement && window.dialogArguments) || opener || parent || top;
}

function prepareLang()
{
	var html = document.getElementsByTagName( 'html' )[0].innerHTML;
	var m = html.match( /\{#?([a-z0-9_\.]+)\}/gi );
	for ( var i = 0; i < m.length; i++ ) {
		html = html.replace( m[i], trans.translate( m[i].replace( /([\{\}#]+?)/g, '' ) ) );
	}
	document.getElementsByTagName( 'html' )[0].innerHTML = html;
}

function setCoords( coords )
{
	if ( coords ) {
		var s = coords.replace( ' ', '' ).split( ',' );
		lat = s[0];
		lon = s[1];
	}

	if ( isNaN( lat ) || isNaN( lon ) ) {
		return;
	}

	showAddress( lat, lon );
}

function showAddress( lat, lon )
{
	if ( isNaN( lat ) || isNaN( lon ) ) {
		return;
	}

	var ln = new google.maps.LatLng( lat, lon );
	map.setCenter( ln );
	marker.position = ln;
	document.forms[0].coords.value = lat + "," + lon;
}

function getCurrentMapType( t )
{
	switch ( t ) {
		case 'satellite':
			document.forms[0].mapType.value = "SATELLITE";
			break;
		case 'hybrid':
			document.forms[0].mapType.value = "HYBRID";
			break;
		case 'roadmap':
			document.forms[0].mapType.value = "ROADMAP";
			break;
		case 'terrain':
			document.forms[0].mapType.value = "TERRAIN";
			break;
		case '':
			document.forms[0].mapType.value = "PHYSICAL";
			break;
	}
}

function init()
{
	var head = document.getElementsByTagName( "head" )[0];

	for ( var i = 0; i < head.children.length; i++ ) {
		var h = head.children[i];

		if ( h.src != undefined ) {
			if ( h.src.indexOf( 'maps.google.com' ) != -1 ) {
				h.src.replace( 'API-KEY', params.apiKey );
			}
		}
	}

	if ( !params.coordinates )
		initialCoordinates = '48.123351, 11.54353';
	else
		initialCoordinates = params.coordinates;

	var f = document.forms[0];

	f.akey.value = params.apiKey;
	f.coords.value = initialCoordinates;
	f.zoomLevel.value = params.zoom > 0 ? params.zoom : 7;
	f.zoom.value = f.zoomLevel.value;

	if ( params.size ) {
		//var s = params.size.split( ',' );
		f.width.value = s[0];
		f.height.value = s[1];
	}
	if ( params.width ) {
		f.width.value = params.width;
	}
	if ( params.height ) {
		f.height.value = params.height;
	}

	if ( params.showScale ) {
		f.chkScale.checked = true;
	}

	if ( params.showOverview ) {
		f.chkOverview.checked = true;
	}

	if ( params.locationLookupEnabled == false ) {
		isDraggable = false;

		var divLookup = document.getElementById( 'divLookup' );
		divLookup.style.display = "none";

		var divMap = document.getElementById( 'map' );
		divMap.style.height = "390px"; //390

		var cmdSetCoords = document.getElementById( 'btnSetCoords' );
		cmdSetCoords.disabled = true;

	}
	else {
		isDraggable = true;
	}

	var lat = 48.123351, lon = 11.54353;

	if ( initialCoordinates ) {
		var s = initialCoordinates.replace( ' ', '' ).split( ',' );
		lat = s[0];
		lon = s[1];
	}

	var maptypeValue = params.mapType;
	var zoom = parseInt( params.zoom );

	if ( google ) {

		if ( typeof maptypeValue === 'string' ) {
			switch ( maptypeValue.toUpperCase() ) {
				case 'SATELLITE':
					document.forms[0].mapType.value = "SATELLITE";
					break;
				case 'HYBRID':
					document.forms[0].mapType.value = "HYBRID";
					break;
				case 'ROADMAP':
					document.forms[0].mapType.value = "ROADMAP";
					break;
				case 'TERRAIN':
					document.forms[0].mapType.value = "TERRAIN";
					break;

				case 'ROADMAP':
				default:
					document.forms[0].mapType.value = "ROADMAP";
					break;
			}

			switch ( maptypeValue.toUpperCase() ) {
				case 'SATELLITE':
					maptypeValue = google.maps.MapTypeId.SATELLITE;
					break;
				case 'HYBRID':
					maptypeValue = google.maps.MapTypeId.HYBRID;
					break;
				case 'TERRAIN':
					maptypeValue = google.maps.MapTypeId.TERRAIN;
					break;
				case 'ROADMAP':
				default:

					maptypeValue = google.maps.MapTypeId.ROADMAP;
					break;
			}

			if ( !maptypeValue ) {
				maptypeValue = google.maps.MapTypeId.ROADMAP;
			}

		}
		else {
			maptypeValue = google.maps.MapTypeId.ROADMAP;
		}

		var o = {
			center: new google.maps.LatLng( lat, lon ),
			zoom: zoom > 0 ? zoom : 7,
			scaleControl: true,
			mapTypeId: maptypeValue
		};
		o.scrollwheel = true;
		o.navigationControl = true;
		o.mapTypeControl = true;
		o.scaleControl = true;
		o.draggable = true;
		o.disableDefaultUI = false;

		map = new google.maps.Map( document.getElementById( "map" ), o );
//		map.setTilt(45);
//		map.setHeading(90);

		marker = new google.maps.Marker( {
			position: new google.maps.LatLng( lat, lon ),
			map: map,
			animation: google.maps.Animation.DROP,
			draggable: true
		} );

		var searchBox = new google.maps.places.SearchBox( document.getElementById( "address" ) );
		searchBox.bindTo( "bounds", map );
		google.maps.event.addListener( searchBox, "places_changed", function ()
		{
			var k = searchBox.getPlaces()[0];
			showAddress( k.geometry.location.lat(), k.geometry.location.lng() );
		} );

		google.maps.event.addDomListener( map, "maptypeid_changed", function ()
		{
			var i = map.getMapTypeId();
			console.log( i )
			getCurrentMapType( i );

		} );

		google.maps.event.addListener( map, 'zoom_changed', function ()
		{
			document.forms[0].zoomLevel.value = map.getZoom();
			$( '#zoom' ).val( map.getZoom() );
		} );

		google.maps.event.addListener( marker, "drag", function ()
		{
			document.forms[0].coords.value = marker.getPosition().lat() + "," + marker.getPosition().lng();
		} );

		$( '#zoom' ).change( function ()
		{
			var v = parseInt( $( this ).val() );
			document.forms[0].zoomLevel.value = v;
			map.setZoom( v );
		} );

		return;

		map = new GMap2( document.getElementById( "map" ) );
		map.setCenter( new GLatLng.fromUrlValue( initialCoordinates ), 4 );

		setHud( "GLargeMapControl3D" );

		var mapTypeControl = new GHierarchicalMapTypeControl();
		map.addControl( mapTypeControl );
		map.enableContinuousZoom();
		map.enableDoubleClickZoom();

		document.forms[0].zoomLevel.value = zoom > 0 ? zoom : map.getZoom();

		getCurrentMapType();

		GEvent.addListener( map, 'zoomend', function ()
		{
			document.forms[0].zoomLevel.value = map.getZoom();
		} );

		GEvent.addListener( map, 'maptypechanged', function ()
		{
			getCurrentMapType();
		} );

		// "tiny" marker icon
		var icon = new GIcon();
		icon.image = "http://labs.google.com/ridefinder/images/mm_20_red.png";
		icon.shadow = "http://labs.google.com/ridefinder/images/mm_20_shadow.png";
		icon.iconSize = new GSize( 12, 20 );
		icon.shadowSize = new GSize( 22, 20 );
		icon.iconAnchor = new GPoint( 6, 20 );
		icon.infoWindowAnchor = new GPoint( 5, 1 );

		//Draggable markers
		var point = new GLatLng.fromUrlValue( initialCoordinates );
		marker = new GMarker( point, {icon: G_DEFAULT_ICON, draggable: isDraggable} );
		map.addOverlay( marker );

		if ( zoom > 0 ) {
			map.setZoom( zoom );
		}

		GEvent.addListener( marker, "drag", function ()
		{
			document.forms[0].coords.value = marker.getPoint().lat() + "," + marker.getPoint().lng();
		} );

	}
}

function generateCodeMap()
{
	var frm = document.getElementById( 'g-maps' );
	var akey = document.getElementById( 'akey' ).value;
	var coords = frm.coords.value;
	var width = frm.width.value;
	var height = frm.height.value;
	var zoom = frm.zoomLevel.value;
	// var hud = frm.cmbHud.value;
	var showScale = frm.chkScale.checked ? true : false;
	//var showOverview = frm.chkOverview.checked ? true : false;
	var mapType = frm.mapType.value;

	var l = coords.replace( ' ', '' ).split( ',' );
	//getMapId();

	switch ( mapType ) {
		case "SATELLITE":
			mapType = 'SATELLITE';
			break;
		case "HYBRID":
			mapType = 'HYBRID';
			break;
		case "NORMAL":
		case "PHYSICAL":
			mapType = '';
			break;
	}

	strHtml = '	var mapOpts = {\n';
	strHtml += '			lat: ' + l[0] + ',\n';
	strHtml += '			lon: ' + l[1] + ',\n';
	strHtml += '			zoom: ' + zoom + ',\n';
	strHtml += '			size: {width: ' + width + ', height: ' + height + '},\n';
	switch ( mapType.toUpperCase() ) {
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

	if ( showScale ) {
		strHtml += '			showScale: true,\n';
	}
	else {
		strHtml += '			showScale: false,\n';
	}

	strHtml = strHtml.replace( /,\n$/g, '' );
	strHtml += '		};\n';

	var v = {
		//	id: '{{mapid}}',
		akey: akey,
		// 'marker': marker,
		lat: l[0],
		lon: l[1],
		coordinates: coords,
		// 	hud: hud,
		zoom: parseInt( zoom ),
		width: parseInt( width ),
		height: parseInt( height ),
		showScale: showScale,
	//	showOverview: showOverview,
		mapType: mapType.toLowerCase(),
		//	jsonOpts: strHtml
	};

	return v;
}


$( document ).ready( function ()
{
	prepareLang();
	$( 'input.spin' ).dcmsInputSpin();
	init();
} );

