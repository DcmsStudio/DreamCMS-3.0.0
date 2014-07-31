
if (typeof initMap !== 'function') {

	function initMap (opt) {
		var opts = $.extend({}, (opt || {}) );

		if (typeof opts.lat == 'undefined' || typeof opts.lon == 'undefined' || typeof opts.mapElement == 'undefined') {
			Debug.warn('Invalid Map config!');
			return;
		}

		var mapID = $(opts.mapElement ).attr('id');
		if (!mapID) {
			mapID = 'map-' + new Date().getTime();
			$(opts.mapElement ).attr('id', mapID)
		}



		var m = new Maps( '#'+mapID, opts);
		var o = m.getObjects();

		var map = o[0];
		var marker = o[1];
		if (opts.useMarker === true && marker) {
			var mapOptions = m.opt;

			var infowindow = new google.maps.InfoWindow({
				content: opts.infoWindow || ''
			});

			google.maps.event.addListener(marker, 'click', function () {
				infowindow.open(map, marker);
			});
		}

		return;











		var mapOptions = {
			zoom: opts.zoom > 0 ? opts.zoom : 8,
			center: new google.maps.LatLng( parseInt(opts.lat, 10), parseInt(opts.lon, 10) )
		};

		var map = new google.maps.Map($(opts.mapElement).get(0), mapOptions);

		var infowindow = new google.maps.InfoWindow({
			content: opts.infoWindow || ''
		});


		if (opts.useMarker === true) {
			var marker = new google.maps.Marker({
				position: mapOptions.center,
				map: map,
				title: opts.markerTitle || ''
			});

			google.maps.event.addListener(marker, 'click', function () {
				infowindow.open(map, marker);
			});
		}
	}
}