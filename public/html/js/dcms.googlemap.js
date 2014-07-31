/**
 * DreamCMS 3.0
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 * PHP Version 5
 *
 * @package
 * @version     3.0.0 Beta
 * @category
 * @copyright    2008-2013 Marcel Domke
 * @license     http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author      Marcel Domke <http://www.dcms-studio.de>
 * @link        http://www.dcms-studio.de
 * @file
 */


function Maps( elStr, opts )
{

	var self = this, opt = $.extend( {}, opts );
	var el = false, apiKey = false, map;


	function rotate90() {
		var heading = map.getHeading() || 0;
		map.setHeading(heading + 90);
	}

	function autoRotate() {
		// Determine if we're showing aerial imagery
		if (map.getTilt() != 0) {
			window.setInterval(rotate90, 3000);
		}
	}

	this.init = function ()
	{
		var mapType = google.maps.MapTypeId.ROADMAP;
		var zoom = ((typeof opt.zoom == 'number' && opt.zoom > 0 && opt.zoom < 21) ? opt.zoom : 7);

		if ( typeof opt.mapType == 'string' ) {

			switch ( opt.mapType.toUpperCase() ) {

				case 'HYBRID':
					mapType = google.maps.MapTypeId.HYBRID;
					break;
				case 'SATELLITE':
					mapType = google.maps.MapTypeId.SATELLITE;
					break;
				case 'TERRAIN':
					mapType = google.maps.MapTypeId.TERRAIN;
					break;
				case 'ROADMAP':
					mapType = google.maps.MapTypeId.ROADMAP;
					break;
			}
		}


		var o = {
			center: new google.maps.LatLng( opt.lat, opt.lon ),
			zoom: zoom,
			scaleControl: (!opt.showScale ? false : true),
			mapTypeId: mapType
		};

		if ( typeof opt.showScale != 'undefined' && opt.showScale !== true ) {
			o.scrollwheel = false;
			o.navigationControl = false;
			o.mapTypeControl = false;
			o.scaleControl = false;
			o.draggable = false;
			o.disableDefaultUI = true;
			o.maxZoom = zoom;
			o.minZoom = zoom;
		}

		map = new google.maps.Map( document.getElementById( elStr ), o );
		map.setTilt(45);
		map.setHeading(90);


		var marker = new google.maps.Marker( {
			position: new google.maps.LatLng( opt.lat, opt.lon ),
			map: map,
			title: opts.markerTitle || '',
			animation: google.maps.Animation.DROP,
			draggable: (opt.draggableMarker ? true : false)
		} );

		this.opt = o;
		this.marker = marker;
		this.map = map;
	};

	this.getObjects = function ()
	{
		return [this.map, this.marker];
	}

	if ( elStr.match( /^\./ ) || elStr.match( /^#/ ) ) {
		el = $( elStr );

		elStr = elStr.replace( '.', '' ).replace( '#', '' );
	}

	if ( el ) {

		if ( typeof google != 'undefined' && typeof google.maps != 'undefined' ) {
			this.init();
		}
	}
};

if (typeof jQuery != 'undefined')
{
	$(document).ready(function() {

        var xmaps = $('div.googlemaps'), mlen = xmaps.length;

		//$('div.googlemaps' ).each(function() {
        for (var x = 0; x<mlen; ++x ) {
            var m = $(xmaps[x]);

			if (m.attr('id') && m.data('options')  ) {
				var o = JSON.parse( unescape( m.data('options') )) ;
				if ( typeof o.lat != 'undefined' && typeof o.lon != 'undefined' ) {
					var mapOpts = {
						lat: o.lat,
						lon: o.lon,
						zoom: parseInt(o.zoom),
						size: {
							width: o.width,
							height: o.height
						},
						mapType: o.mapType,
						showScale: o.showScale
					};

                    m.width(o.width).height(o.height);

					new Maps( '#'+ m.attr('id'), mapOpts);
				}
			}
            //)});
        }
	})

}