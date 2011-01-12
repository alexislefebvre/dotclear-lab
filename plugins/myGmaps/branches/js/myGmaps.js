var myGmaps = {
	// Object parameter
	map: null,
	center: {
		lat: '43.0395797336425',
		lng: '6.126280043989323'
	},
	zoom: '12',
	type: 'roadmap',
	elt_type: 'none',
	scrollwheel: false,
	events: [],
	markers: [],
	kmls: [],
	path: {
		polyline: null,
		polygon: null
	},
	objects: {
		polyline: null,
		polygon: null
	},
	infowindow: null,
	msg: {},
	
	// Initialization
	init: function() {
		var opts = this.getMapOptions();
		myGmaps.map = new google.maps.Map(document.getElementById("map_canvas"), opts);
		// Init info windows
		myGmaps.infowindow = new google.maps.InfoWindow();
		// Init path
		myGmaps.path.polyline = new google.maps.MVCArray;
		myGmaps.path.polygon = new google.maps.MVCArray;
		// Init objects
		myGmaps.objects.polyline = new google.maps.Polyline({
			strokeColor: '#000000',
			strokeWeight: 3,
			strokeOpacity: 0.5
		});
		myGmaps.objects.polygon = new google.maps.Polygon({
			fillColor: '#5555FF',
			strokeColor: '#000000',
			strokeWeight: 3,
			strokeOpacity: 0.5
		});
		myGmaps.objects.polyline.setPath(new google.maps.MVCArray([myGmaps.path.polyline]));
		myGmaps.objects.polygon.setPath(new google.maps.MVCArray([myGmaps.path.polygon]));
		myGmaps.objects.polyline.setMap(this.map);
		myGmaps.objects.polygon.setMap(this.map);
		
		google.maps.event.addListener(this.map, 'center_changed', myGmaps.setMapOptions);
		google.maps.event.addListener(this.map, 'zoom_changed', myGmaps.setMapOptions);
		google.maps.event.addListener(this.map, 'maptypeid_changed', myGmaps.setMapOptions);
		myGmaps.select(myGmaps.elt_type);
	},
	
	getMapOptions: function() {
		if (myGmaps.type != 'roadmap' && myGmaps.type != 'satellite' && myGmaps.type != 'hybrid' && myGmaps.type != 'terrain') {
			myGmaps.type = 'roadmap';
		}
		
		var opts = {
			zoom: parseFloat(myGmaps.zoom),
			center: new google.maps.LatLng(parseFloat(myGmaps.center.lat), parseFloat(myGmaps.center.lng)),
			scrollwheel: myGmaps.scrollwheel,
			mapTypeId: myGmaps.type
		};
		
		return opts;
	},
	
	loadData: function() {
		var list = $('input[name=post_excerpt]').val().split("\n");
		for (var i = 0; i < list.length; i++) {
			if (list[i].length > 0) {
				if (this.is_url(list[i])) {
					myGmaps.addKml(list[i]);
				}
				else {
					var latLng = new google.maps.LatLng(list[i].split('|')[0],list[i].split('|')[1]);
					myGmaps.addPoint(latLng);
				}
			}
		}
	},
	
	addPoint: function(latLng) {
		myGmaps.infowindow.close();
		var marker = new google.maps.Marker({
			position: latLng,
			draggable: true
		});
		myGmaps.markers.push(marker);
		// Add markers
		if (myGmaps.elt_type == 'marker') {
			if (myGmaps.markers.length > 1) {
				myGmaps.markers[0].setMap(null);
				myGmaps.markers.splice(0, 1);
			}
			marker.setMap(myGmaps.map);
			marker.setTitle("#" + myGmaps.markers.length);
			google.maps.event.addListener(marker, 'dblclick', function() {
				marker.setMap(null);
				for (var i = 0, I = myGmaps.markers.length; i < I && myGmaps.markers[i] != marker; ++i);
				myGmaps.markers.splice(i, 1);
				myGmaps.infowindow.close();
				myGmaps.updDetails();
			});
			google.maps.event.addListener(marker, 'dragend', function() {
				for (var i = 0, I = myGmaps.markers.length; i < I && myGmaps.markers[i] != marker; ++i);
				myGmaps.markers[i] = marker;
				myGmaps.infowindow.close();
				myGmaps.updDetails();
			});
			myGmaps.map.panTo(marker.getPosition());
		}
		// Add polyline
		if (myGmaps.elt_type == 'polyline') {
			myGmaps.path.polyline.insertAt(myGmaps.path.polyline.length, latLng);
			marker.setMap(myGmaps.map);
			marker.setTitle("#" + myGmaps.path.polyline.length);
			google.maps.event.addListener(marker, 'dblclick', function() {
				marker.setMap(null);
				for (var i = 0, I = myGmaps.markers.length; i < I && myGmaps.markers[i] != marker; ++i);
				myGmaps.markers.splice(i, 1);
				myGmaps.path.polyline.removeAt(i);
				myGmaps.infowindow.close();
				myGmaps.updDetails();
			});
			google.maps.event.addListener(marker, 'dragend', function() {	
				for (var i = 0, I = myGmaps.markers.length; i < I && myGmaps.markers[i] != marker; ++i);
				myGmaps.path.polyline.setAt(i, marker.getPosition());
				myGmaps.infowindow.close();
				myGmaps.updDetails();
			});
		}
		// Add polygon
		if (myGmaps.elt_type == 'polygon') {
			myGmaps.path.polygon.insertAt(myGmaps.path.polygon.length, latLng);
			marker.setMap(myGmaps.map);
			marker.setTitle("#" + myGmaps.path.polygon.length);
			google.maps.event.addListener(marker, 'dblclick', function() {
				marker.setMap(null);
				for (var i = 0, I = myGmaps.markers.length; i < I && myGmaps.markers[i] != marker; ++i);
				myGmaps.markers.splice(i, 1);
				myGmaps.path.polygon.removeAt(i);
				myGmaps.infowindow.close();
				myGmaps.updDetails();
			});
			google.maps.event.addListener(marker, 'dragend', function() {
				for (var i = 0, I = myGmaps.markers.length; i < I && myGmaps.markers[i] != marker; ++i);
				myGmaps.path.polygon.setAt(i, marker.getPosition());
				myGmaps.infowindow.close();
				myGmaps.updDetails();
			});
		}
		myGmaps.updDetails();
	},
	
	addKml: function(url) {
		if (url != null && url != '' && myGmaps.is_url(url)) {
			myGmaps.delOverlays(true);
			kml = new google.maps.KmlLayer(url, { preserveViewport: true });
			myGmaps.kmls.push(kml)
			if (myGmaps.kmls.length > 1) {
				kml[0].setMap(null);
				kml.splice(0, 1);
			}
			kml.setMap(myGmaps.map);
		}
		else {
			alert('Wrong format');
		}
	},
	
	startDraw: function(elt_type) {
		if (myGmaps.elt_type != elt_type) {
			myGmaps.elt_type = elt_type;
			myGmaps.select(elt_type);
			myGmaps.delOverlays(false);
			for (i in myGmaps.events) {
				if (i == 'map') {
					google.maps.event.removeListener(myGmaps.events[i]);
				}
				if (i == 'jquery') {
					$(myGmaps.events[i]).unbind();
				}
			}
			myGmaps.events['jquery'] = $('input[name=mq]').bind('click',function() {
				var search = $('input[name=q]').val();
				var geocoder = new google.maps.Geocoder();
				geocoder.geocode({ 'address': search}, function(results, status) {
					if (status == google.maps.GeocoderStatus.OK) {
						myGmaps.map.panTo(results[0].geometry.location);
						myGmaps.addPoint(results[0].geometry.location);
					} else {
						alert("Geocode was not successful for the following reason: " + status);
					}
				});
			});
			myGmaps.events['map'] = google.maps.event.addListener(myGmaps.map, 'click', function(event) {
				myGmaps.addPoint(event.latLng)
			});
		}
	},
	
	updPolylineOptions: function(event) {
		var content = 
		// Colors
		'<div>' +
		'<p><label>Fill color:' +
		'<input type="text" id="line" size="7" value="'+myGmaps.objects.polyline.strokeColor+'" />' +
		'</label></p>' +
		'</div>' +
		// Sliders
		'<div><p><label>Line stroke:' +
		'<input type="text" id="stroke" size="3" value="'+myGmaps.objects.polyline.strokeWeight+'"/>' +
		'<div id="slider-stroke"></div>' +
		'</label></p>' +
		'<p><label>Opacity:' +
		'<input type="text" id="opacity" size="3" value="'+myGmaps.objects.polyline.strokeOpacity+'" />' +
		'</label></p>' +
		'<div id="slider-opacity"></div>' +
		'</div>' +
		'<p><input type="button" class="submit" id="apply" value="Apply" /></p>';
		myGmaps.infowindow.setContent(content);
		myGmaps.infowindow.setPosition(event.latLng);
		myGmaps.infowindow.open(myGmaps.map);
		$('input#line').colorPicker();
		$('input#apply').click(function() {
			var opts = {};
			if ($('input#line').val() != '') {
				opts.strokeColor = $('input#line').val();
			}
			if ($('input#stroke').val() != '') {
				opts.strokeWeight = $('input#stroke').val();
			}
			if ($('input#opacity').val() != '') {
				opts.strokeOpacity = $('input#opacity').val();
			}
			myGmaps.objects.polyline.setOptions(opts);
		});
	},
	
	updPolygonOptions: function(event) {
		var content = 
		// Colors
		'<div class="two-cols"><div class="col">' +
		'<p><label>Fill color:' +
		'<input type="text" id="fill" size="7" value="'+myGmaps.objects.polygon.fillColor+'" />' +
		'</label></p>' +
		'</div><div class="col">' +
		'<p><label>Line color:' +
		'<input type="text" id="line" size="7" value="'+myGmaps.objects.polygon.strokeColor+'" />' +
		'</div></div>' +
		// Sliders
		'<div><p><label>Line stroke:' +
		'<input type="text" id="stroke" size="3" value="'+myGmaps.objects.polygon.strokeWeight+'"/>' +
		'<div id="slider-stroke"></div>' +
		'</label></p>' +
		'<p><label>Opacity:' +
		'<input type="text" id="opacity" size="3" value="'+myGmaps.objects.polygon.strokeOpacity+'" />' +
		'</label></p>' +
		'<div id="slider-opacity"></div>' +
		'</div>' +
		'<p><input type="button" class="submit" id="apply" value="Apply" /></p>';
		myGmaps.infowindow.setContent(content);
		myGmaps.infowindow.setPosition(event.latLng);
		myGmaps.infowindow.open(myGmaps.map);
		$('input#fill').colorPicker();
		$('input#line').colorPicker();
		$('input#apply').click(function() {
			var opts = {};
			if ($('input#fill').val() != '') {
				opts.fillColor = $('input#fill').val();
			}
			if ($('input#line').val() != '') {
				opts.strokeColor = $('input#line').val();
			}
			if ($('input#stroke').val() != '') {
				opts.strokeWeight = $('input#stroke').val();
			}
			if ($('input#opacity').val() != '') {
				opts.strokeOpacity = $('input#opacity').val();
			}
			myGmaps.objects.polygon.setOptions(opts);
		});
	},
	
	updDetails: function() {
		var type = '';
		var points = [];
		
		if (myGmaps.path.polyline.length > 0) {
			type = 'Polyline';
			points = myGmaps.markers;
		}
		else if (myGmaps.path.polygon.length > 0) {
			type = 'Polygon';
			points = myGmaps.markers;
		}
		else if (myGmaps.markers.length > 0) {
			type = 'Point';
			points = myGmaps.markers;
		}
		else {
			type = 'Center';
			var marker = new google.maps.Marker({
				position: new google.maps.LatLng(myGmaps.map.getCenter().lat(),myGmaps.map.getCenter().lng())
			});
			points.push(marker);
		}
		
		var content = '<h3>Type: ' + type + '</h3>' + '<ul>';
		
		for (i in points) {
			content += '<li>#' + (parseInt(i) + 1) + ' - Coordinates: ' + points[i].getPosition().toString() + '</li>';
			
		}
		
		content += '</ul>';
		
		$('#map-details').html(content);
	},
	
	delOverlays: function(overwrite) {
		if (!overwrite) {
			if (myGmaps.elt_type == 'none') {
				return;
			}
			if (myGmaps.elt_type == 'marker' && myGmaps.markers.length > 0 && myGmaps.path.polyline.length == 0 && myGmaps.path.polygon.length == 0) {
				return;
			}
			if (myGmaps.elt_type == 'polyline' && myGmaps.path.polyline.length > 0) {
				return;
			}
			if (myGmaps.elt_type == 'polygon' && myGmaps.path.polygon.length > 0) {
				return;
			}
		}
		
		for (i in myGmaps.kmls) {
			myGmaps.kmls[i].setMap(null);
		}
		for (i in myGmaps.markers) {
			myGmaps.markers[i].setMap(null);
		}
		for (i in myGmaps.path.polyline) {
			myGmaps.path.polyline.removeAt(i);
		}
		for (i in myGmaps.path.polygon) {
			myGmaps.path.polygon.removeAt(i);
		}
		myGmaps.kmls.length = 0;
		myGmaps.markers.length = 0;
		myGmaps.path.polyline.length = 0;
		myGmaps.path.polygon.length = 0;
		myGmaps.updDetails();
	},
	
	setMapOptions: function() {
		$('input[name=center]').val(myGmaps.map.getCenter().lat() + ',' + myGmaps.map.getCenter().lng());
		$('input[name=zoom]').val(myGmaps.map.getZoom());
		$('input[name=map_type]').val(myGmaps.map.getMapTypeId());
	},
	
	select: function(id) {
		$('li#none').removeClass('selected');
		$('li#marker').removeClass('selected');
		$('li#polyline').removeClass('selected');
		$('li#polygon').removeClass('selected');
		$('li#' + id).addClass('selected');
	},
	
	trim: function(str) {
		return str.replace(/^\s+/g,'').replace(/\s+$/g,'')
	},
	
	is_url: function(str) {
		var exp = new RegExp("^(http://)[a-zA-Z0-9.-]*[a-zA-Z0-9/_-]", "g");
		return exp.test(str);
	}
};