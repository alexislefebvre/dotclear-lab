$(function () {

	if (!document.getElementById) {
		return;
	}

	if (document.getElementById('edit-entry')) {
		dotclear.hideLockable();

		// Get document format and prepare toolbars
		var formatField = $('#post_format').get(0);
		$(formatField).change(function () {

			contentTb.switchMode(this.value);
		});

		var contentTb = new jsToolBar(document.getElementById('post_content'));

		contentTb.context = 'post';

		// Load content toolbar
		contentTb.switchMode(formatField.value);

		

		function getLocale() {
			if (navigator) {
				if (navigator.language) {
					return navigator.language;
				} else if (navigator.browserLanguage) {
					return navigator.browserLanguage;
				} else if (navigator.systemLanguage) {
					return navigator.systemLanguage;
				} else if (navigator.userLanguage) {
					return navigator.userLanguage;
				}
			}
		}

		//Date picker
		if (document.getElementById('post_dt')) {
			var post_dtPick = new datePicker($('#post_dt').get(0));
			post_dtPick.img_top = '1.5em';
			post_dtPick.draw();
		}

		// Hide some fields
		$('#notes-area label').toggleWithLegend($('#notes-area').children().not('label'), {
			cookie: 'dcx_post_notes',
			hide: $('#post_notes').val() == ''
		});
		
		$('#description-area label').toggleWithLegend($('#description-area').children().not('label'), {
			cookie: 'dcx_map_description',
			hide: $('#post_content').val() == 'Pas de description'
		});
		
		function trim(myString) {
			return myString.replace(/^\s+/g, '').replace(/\s+$/g, '')
		}

		//Map picker
		var markersArray = [];
		var polylinesArray = [];
		var kmlsArray = [];
		var markersList = [];

		if ($('input[name=myGmaps_center]').attr('value') == '') {
			var latlng = new google.maps.LatLng(43.0395797336425, 6.126280043989323);
			var default_zoom = '12';
			var default_type = 'roadmap';
		} else {
			var parts = $('input[name=myGmaps_center]').attr('value').split(",");
			var lat = parseFloat(trim(parts[0]));
			var lng = parseFloat(trim(parts[1]));
			var latlng = new google.maps.LatLng(lat, lng);
			var default_zoom = $('input[name=myGmaps_zoom]').attr('value');
			var default_type = $('input[name=myGmaps_type]').attr('value');
		}
		var myOptions = {
			zoom: parseFloat(default_zoom),
			center: latlng,
			scrollwheel: false,
			mapTypeId: google.maps.MapTypeId.ROADMAP
		};
		var map = new google.maps.Map(document.getElementById("map_canvas"), myOptions);
		
		if (default_type == 'roadmap') {
			map.setOptions({mapTypeId: google.maps.MapTypeId.ROADMAP});
		} else if (default_type == 'satellite') {
			map.setOptions({mapTypeId: google.maps.MapTypeId.SATELLITE});
		} else if (default_type == 'hybrid') {
			map.setOptions({mapTypeId: google.maps.MapTypeId.HYBRID});
		} else if (default_type == 'terrain') {
			map.setOptions({mapTypeId: google.maps.MapTypeId.TERRAIN});
		}
		
		google.maps.event.addListener(map, 'click', function (event) {
			infowindow.close();
			addMarker(event.latLng);
		});

		var infowindow = new google.maps.InfoWindow({});
		if (getLocale() == 'fr') {
			var icon_msg = 'Choisir un icône';
			var polyline_msg = 'Choisir une couleur';
		} else {
			var icon_msg = 'Choose an icon';
			var polyline_msg = 'Choose a color';
		}
		var infowindowIcons = '<div id="infowindow_icons" style="cursor:pointer;">' +
		'<h3>' + icon_msg + '</h3>' +
		'<img src="index.php?pf=myGmaps/icons/port.png" alt="" width="32" height="32" />' +
		'<img src="index.php?pf=myGmaps/icons/bars.png" alt="" width="32" height="32" />' +
		'<img src="index.php?pf=myGmaps/icons/dining.png" alt="" width="32" height="32" />' +
		'<img src="index.php?pf=myGmaps/icons/sailing.png" alt="" width="32" height="32" />' +
		'<img src="index.php?pf=myGmaps/icons/sunny.png" alt="" width="32" height="32" />' +
		'<img src="index.php?pf=myGmaps/icons/restau.png" alt="" width="32" height="32" />' +
		'<img src="index.php?pf=myGmaps/icons/star.png" alt="" width="32" height="32" />' +
		'<img src="index.php?pf=myGmaps/icons/triangle.png" alt="" width="32" height="32" />' +
		'<img src="index.php?pf=myGmaps/icons/vigne.png" alt="" width="32" height="32" />' +
		'</div>';

		$('#infowindow_icons img').live('click', function () {
			markersArray[0].setIcon($(this).attr("src"));
			markersList.length = 0;
			var lines = postMarkersList.split("\n");
			for (var i = 0; i < lines.length; i++) {
				if (lines[i].length > 1) {
					var parts = lines[i].split("|");
					var color = parts[3];
				}
			}
			var icon = $(this).attr("src");
			for (i in markersArray) {
				markersList.push(markersArray[i].position.lat() + "|" + markersArray[i].position.lng() + "|" + icon + "|" + color);
			}
			var list = markersList.join('\n');
			$('#post_excerpt').val(list);
			infowindow.close();
		});

		var infowindowPolyline = '<div id="infowindow_polyline" style="cursor:pointer;">' + '<h3>' + polyline_msg + '</h3>' + '<img style="display:block;margin:5px 0;" title="#0000FF" src="index.php?pf=myGmaps/images/blue-0000FF.png" alt="" width="200" height="5" />' + '<img style="display:block;margin:5px 0;" title="#FF0000" src="index.php?pf=myGmaps/images/red-FF0000.png" alt="" width="200" height="5" />' + '<img style="display:block;margin:5px 0;" title="#00FF00" src="index.php?pf=myGmaps/images/green-00FF00.png" alt="" width="200" height="5" />' + '<img style="display:block;margin:5px 0;" title="#666666" src="index.php?pf=myGmaps/images/grey-666666.png" alt="" width="200" height="5" />' + '<img style="display:block;margin:5px 0;" title="#FF00FF" src="index.php?pf=myGmaps/images/purple-FF00FF.png" alt="" width="200" height="5" />' + '<img style="display:block;margin:5px 0;" title="#FFFF00" src="index.php?pf=myGmaps/images/yellow-FFFF00.png" alt="" width="200" height="5" />' + '</div>';

		$('#infowindow_polyline img').live('click', function () {
			var This = $(this).attr("title");
			for (i in polylinesArray) {
				polylinesArray[i].setOptions({
					strokeColor: This
				});
			}
			markersList.length = 0;
			var icon = 'index.php?pf=myGmaps/icons/mini-marker.png';
			var color = This;
			for (i in markersArray) {
				markersList.push(markersArray[i].position.lat() + "|" + markersArray[i].position.lng() + "|" + icon + "|" + color);
			}
			var list = markersList.join('\n');
			$('#post_excerpt').val(list);
			infowindow.close();
		});

		var icon = 'index.php?pf=myGmaps/icons/marker.png';

		//Place existing markers

		function is_url(str) {
			var exp = new RegExp("^(http://)[a-zA-Z0-9.-]*[a-zA-Z0-9/_-]", "g");
			return exp.test(str);
		}
		var postMarkersList = $('textarea[name=post_excerpt]').attr('value');
		//var ttl = $('input[name=post_title]').attr('value');
		if (is_url(postMarkersList)) {
			layer = new google.maps.KmlLayer(postMarkersList, {
				preserveViewport: true
			});
			kmlsArray.push(layer);
			layer.setMap(map);
		} else if (postMarkersList != '') {


			var lines = postMarkersList.split("\n");
			for (var i = 0; i < lines.length; i++) {
				if (lines[i].length > 1) {
					var parts = lines[i].split("|");
					var lat = parseFloat(parts[0]);
					var lng = parseFloat(parts[1]);
					var icon = parts[2];
					var color = parts[3];
					var location = new google.maps.LatLng(lat, lng);
					marker = new google.maps.Marker({
						position: location,
						draggable: true,
						map: map
					});

					if (markersArray.length == 0) {
						marker.icon = icon;
						infowindow.setContent(infowindowIcons);
					} else if (markersArray.length == 1) {
						infowindow.setContent(infowindowPolyline);
						markersArray[0].setIcon('index.php?pf=myGmaps/icons/mini-marker.png');
						marker.icon = 'index.php?pf=myGmaps/icons/mini-marker.png';
					} else {
						infowindow.setContent(infowindowPolyline);
						marker.icon = 'index.php?pf=myGmaps/icons/mini-marker.png';
					}

					markersArray.push(marker);

					markersList.push(location.lat() + "|" + location.lng() + "|" + icon + "|" + color);

					google.maps.event.addListener(marker, 'click', function () {
						infowindow.open(map, this);

					});

					google.maps.event.addListener(marker, "dragend", function () {
						markersList.length = 0;

						if (polylinesArray) {
							for (i in polylinesArray) {
								polylinesArray[i].setMap(null);
							}
							polylinesArray.length = 0;
						}

						for (i in markersArray) {
							var icon = markersArray[i].icon;
							infowindow.setContent(infowindowIcons);
							markersList.push(markersArray[i].position.lat() + "|" + markersArray[i].position.lng() + "|" + icon + "|" + color);
						}
						if (markersArray.length > 1) {
							infowindow.setContent(infowindowPolyline);
							var polylineCoordinates = [];
							for (i in markersArray) {
								var pos = new google.maps.LatLng(markersArray[i].position.lat(), markersArray[i].position.lng());
								polylineCoordinates.push(pos);
							}
							var polyline = new google.maps.Polyline({
								path: polylineCoordinates,
								strokeColor: color,
								strokeOpacity: 0.5,
								strokeWeight: 3
							});
							polylinesArray.push(polyline);
							polyline.setMap(map);
						}
						var list = markersList.join('\n');
						$('#post_excerpt').val(list);
					});
				}

			}
			if (markersArray.length > 1) {

				var polylineCoordinates = [];
				for (i in markersArray) {
					var pos = new google.maps.LatLng(markersArray[i].position.lat(), markersArray[i].position.lng());
					markersArray[i].title = parseInt(i) + 1;
					polylineCoordinates.push(pos);
				}
				var polyline = new google.maps.Polyline({
					path: polylineCoordinates,
					strokeColor: color,
					strokeOpacity: 0.5,
					strokeWeight: 3
				});
				polylinesArray.push(polyline);
				polyline.setMap(map);
			}
		}

		//Add markers

		function addMarker(location) {
			var post_maps = $('#post_maps').val();
			if (post_maps == 'none') {
				$('#post_maps').val('point of interest');
			}
			var postMarkersList = $('textarea[name=post_excerpt]').attr('value');
			if (postMarkersList != '' && !is_url(postMarkersList)) {
				var lines = postMarkersList.split("\n");
				for (var i = 0; i < lines.length; i++) {
					if (lines[i].length > 1) {
						var parts = lines[i].split("|");
						var color = parts[3];
					}
				}
			} else {
				var color = '#FF0000';
			}
			marker = new google.maps.Marker({
				position: location,
				draggable: true,
				map: map
			});

			google.maps.event.addListener(marker, 'click', function () {
				infowindow.open(map, this);
			});

			if (polylinesArray) {
				for (i in polylinesArray) {
					polylinesArray[i].setMap(null);
				}
			}

			if (kmlsArray) {
				for (i in kmlsArray) {
					kmlsArray[i].setMap(null);
				}
			}
			markersList.length = 0;
			polylinesArray.length = 0;
			kmlsArray.length = 0;

			if (markersArray.length == 0) {
				infowindow.setContent(infowindowIcons)
				marker.setIcon('index.php?pf=myGmaps/icons/marker.png');
				$('#post_maps').val('point of interest');

			} else {
				infowindow.setContent(infowindowPolyline)
				icon = 'index.php?pf=myGmaps/icons/mini-marker.png';
				for (i in markersArray) {
					markersArray[i].setIcon(icon);
					markersList.push(markersArray[i].position.lat() + "|" + markersArray[i].position.lng() + "|" + icon + "|" + color);
				}
			}
			markersArray.push(marker);

			if (markersArray.length > 1) {
				$('#post_maps').val('polyline');
				var polylineCoordinates = [];
				for (i in markersArray) {
					var pos = new google.maps.LatLng(markersArray[i].position.lat(), markersArray[i].position.lng());
					markersArray[i].title = parseInt(i) + 1;
					markersArray[i].setIcon('index.php?pf=myGmaps/icons/mini-marker.png');
					polylineCoordinates.push(pos);
				}
				var polyline = new google.maps.Polyline({
					path: polylineCoordinates,
					strokeColor: color,
					strokeOpacity: 0.5,
					strokeWeight: 3
				});
				polylinesArray.push(polyline);
				polyline.setMap(map);
			}
			markersList.push(location.lat() + "|" + location.lng() + "|" + marker.icon + "|" + color);

			var list = markersList.join('\n');
			$('#post_excerpt').val(list);

			//Add event listeners
			google.maps.event.addListener(marker, 'click', function () {
				infowindow.open(map, this);

			});

			google.maps.event.addListener(marker, "dragend", function () {
				var postMarkersList = $('textarea[name=post_excerpt]').attr('value');
				var lines = postMarkersList.split("\n");
				for (var i = 0; i < lines.length; i++) {
					if (lines[i].length > 1) {
						var parts = lines[i].split("|");
						var color = parts[3];
					}
				}
				markersList.length = 0;
				if (polylinesArray) {
					for (i in polylinesArray) {
						polylinesArray[i].setMap(null);
					}
					polylinesArray.length = 0;
				}
				for (i in markersArray) {
					var icon = markersArray[i].icon;
					markersList.push(markersArray[i].position.lat() + "|" + markersArray[i].position.lng() + "|" + icon + "|" + color);
				}
				if (markersArray.length > 1) {
					var polylineCoordinates = [];
					for (i in markersArray) {
						var pos = new google.maps.LatLng(markersArray[i].position.lat(), markersArray[i].position.lng());
						polylineCoordinates.push(pos);
					}
					var polyline = new google.maps.Polyline({
						path: polylineCoordinates,
						strokeColor: color,
						strokeOpacity: 0.5,
						strokeWeight: 3
					});
					polylinesArray.push(polyline);
					polyline.setMap(map);
				}
				var list = markersList.join('\n');
				$('#post_excerpt').val(list);
			});
		}


		$("#delete_overlays").click(function () {
			deleteOverlays();
		});

		$("#use_kml_file").click(function () {
			var msg = prompt('URL:', '')
			if (msg != null && msg != '' && is_url(msg)) {
				deleteOverlays();
				layer = new google.maps.KmlLayer(msg, {
					preserveViewport: true
				});
				kmlsArray.push(layer);
				layer.setMap(map);
				$('#post_maps').val('included kml file');
				$('#post_excerpt').val(msg);
			}
		});

		//Remove markers

		function deleteOverlays() {
			if (markersArray) {
				for (i in markersArray) {
					markersArray[i].setMap(null);
				}
			}
			if (polylinesArray) {
				for (i in polylinesArray) {
					polylinesArray[i].setMap(null);
				}
			}
			if (kmlsArray) {
				for (i in kmlsArray) {
					kmlsArray[i].setMap(null);
				}
			}
			markersArray.length = 0;
			markersList.length = 0;
			polylinesArray.length = 0;
			kmlsArray.length = 0;
			$('#post_maps').val('none');
			$('#post_excerpt').val('');
		};

		$('#entry-form').submit(function () {
			var post_maps = $('#post_maps').val();
			if (post_maps == '') {
				$('#post_maps').val('none');
			}
			var content = $("textarea[name=post_content]").val();
			if (content == '') {
				$("textarea[name=post_content]").val('Pas de description');
			}
			
			var default_location = map.getCenter().lat()+','+map.getCenter().lng();
			var default_zoom = map.getZoom();
			var default_type = map.getMapTypeId();
			
			$('input[name=myGmaps_center]').attr('value',default_location);
			$('input[name=myGmaps_zoom]').attr('value',default_zoom);
			$('input[name=myGmaps_type]').attr('value',default_type);
			return true;

		});
	}
});