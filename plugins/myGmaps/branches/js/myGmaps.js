var myGmaps = {
	map: null,
	infowindow: null,
	target: '#map_canvas',
	mode: 'view',
	zoom: 12,
	map_type: 'roadmap',
	elt_type: 'none',
	center: {
		lat: '43.0395797336425',
		lng: '6.126280043989323'
	},
	scrollwheel: false,
	options: {
		polyline: {
			strokeColor: '#000000',
			strokeWeight: 3,
			strokeOpacity: 0.5
		},
		polygon: {
			strokeColor: '#000000',
			strokeWeight: 3,
			strokeOpacity: 0.5,
			fillColor: '#5555FF',
			fillOpacity: 0.3
		}
	},
	msg: {},
	items: [],
	events: [],
	
	init: function(opts) {
		jQuery.extend(myGmaps,opts);
		myGmaps.map = new google.maps.Map($(myGmaps.target).get(0), myGmaps.getMapOptions());
		myGmaps.infowindow = new google.maps.InfoWindow();
		
		var modes = [
			'view',
			'edit',
			'config'
		];
		var found = false;
		
		for (i in modes) {
			if (modes[i] == myGmaps.mode) found = true;
		}
		if (!found) {
			myGmaps.mode = 'view';
		}
		
		myGmaps.drawToolbar();
		myGmaps.initListerner();
	},
	
	initListerner: function() {
		google.maps.event.addListener(myGmaps.map, 'center_changed', myGmaps.setMapOptions);
		google.maps.event.addListener(myGmaps.map, 'zoom_changed', myGmaps.setMapOptions);
		google.maps.event.addListener(myGmaps.map, 'maptypeid_changed', myGmaps.setMapOptions);
	},
	
	setMapOptions: function() {
		$('input[name=center]').val(myGmaps.map.getCenter().lat() + ',' + myGmaps.map.getCenter().lng());
		$('input[name=zoom]').val(myGmaps.map.getZoom());
		$('input[name=map_type]').val(myGmaps.map.getMapTypeId());
	},
	
	getMapOptions: function() {
		if (myGmaps.map_type != 'roadmap' && myGmaps.map_type != 'satellite' && myGmaps.map_type != 'hybrid' && myGmaps.map_type != 'terrain') {
			myGmaps.map_type = 'roadmap';
		}
		
		var opts = {
			zoom: parseFloat(myGmaps.zoom),
			center: new google.maps.LatLng(parseFloat(myGmaps.center.lat), parseFloat(myGmaps.center.lng)),
			scrollwheel: myGmaps.scrollwheel,
			mapTypeId: myGmaps.map_type
		};
		
		return opts;
	},
	
	setObjectsOptions: function(object,opts) {
		if (myGmaps.options[object]) {
			myGmaps.options[object] = jQuery.extend(myGmaps.options[object],opts);
		}
	},
	
	addItems: function(item) {
		if ((myGmaps.mode == 'edit' || myGmaps.mode == 'config') && myGmaps.items.length > 0) {
			myGmaps.cleanMap();
		}
		
		if (item instanceof Array) {
			myGmaps.items = myGmaps.items.concat(item);
		}
		else {
			myGmaps.items.push(item);
			
		}
		myGmaps.loadItems();
		myGmaps.setListeners(myGmaps.map,'map');
	},
	
	loadItems: function() {
		for (i in myGmaps.items) {
			var item = myGmaps.items[i];
			var path = new google.maps.MVCArray;
			
			if (item.loaded) continue;
			
			myGmaps.elt_type = item.type;
				
			if (item.type == 'polyline') {
				item.o.push(new google.maps.Polyline(jQuery.extend({map: myGmaps.map},myGmaps.options.polyline)));
				item.o[0].setPath(new google.maps.MVCArray([path]));
			}
			if (item.type == 'polygon') {
				item.o.push(new google.maps.Polygon(jQuery.extend({map: myGmaps.map},myGmaps.options.polygon)));
				item.o[0].setPath(new google.maps.MVCArray([path]));
			}
			if (item.type == 'kml') {
				item.o.push(new google.maps.KmlLayer(item.url, {map: myGmaps.map, preserveViewport: true}));
			}
			
			for (j in item.markers) {
				var draggable = myGmaps.mode != 'view' ? true : false;
				var raiseondrag = myGmaps.mode == 'config' ? false : true;
				var marker = new google.maps.Marker({
					position: new google.maps.LatLng(item.markers[j].lat,item.markers[j].lng),
					icon: item.icon,
					draggable: draggable,
					raiseOnDrag: raiseondrag,
					title: '#' + (parseInt(j) + 1),
					map: myGmaps.map
				});
				item.o.push(marker);
				if (item.type == 'polyline' || item.type == 'polygon') {
					path.insertAt(path.length, new google.maps.LatLng(item.markers[j].lat,item.markers[j].lng));
				}
			}
			
			for (k in item.o) {
				myGmaps.setListeners(item.o[k],(parseInt(k) == 0 ? item.type : 'marker'),item.infowindow);
			}
			
			item.loaded = true;
		}
		myGmaps.updDetails();
	},
	
	addPoint: function(latlng) {
		if (myGmaps.mode == 'edit') {
			var item = myGmaps.items[0];
			var point = {
				lat: latlng.lat(),
				lng: latlng.lng()
			};
			var title = '#' + (item.type == 'marker' ? 1 : item.o.length);
			var marker = new google.maps.Marker({
				position: new google.maps.LatLng(latlng.lat(),latlng.lng()),
				draggable: true,
				map: myGmaps.map,
				title: title
			});
		
			if (item.type == 'marker' && item.o.length > 0) {
				item.markers[0] = point;
				item.o[0].setMap(null);
				item.o[0] = marker;
			}
			else {
				item.markers.push(point);
				item.o.push(marker);
			}
		
			if (item.type == 'polyline' || item.type == 'polygon') {
				item.o[0].getPath().insertAt(item.o[0].getPath().length, new google.maps.LatLng(latlng.lat(),latlng.lng()));
			}
			
			myGmaps.setListeners(marker,'marker');
			myGmaps.updDetails();
		}
	},
	
	updPoint: function(latlng,i) {
		var index = i;
		var item = myGmaps.items[0];
		
		if (item.type == 'polyline' || item.type == 'polygon') {
			index = parseInt(i) - 1;
			item.o[0].getPath().setAt(index,latlng);
		}
		item.markers[index] = {
			lat: latlng.lat(),
			lng: latlng.lng()
		};
		item.o[i].setPosition(latlng);
	},
	
	delPoint: function(i) {
		var index = i;
		var item = myGmaps.items[0];
		
		if (item.type == 'polyline' || item.type == 'polygon') {
			index = parseInt(i) - 1;
			item.o[0].getPath().removeAt(index);
		}
		item.markers.splice(index, 1);
		item.o[i].setMap(null);
		item.o.splice(i, 1);
		
		for (j = (item.type == 'marker' ? 0 : 1); j < item.o.length; j++) {
			item.o[j].setTitle('#' + (parseInt(j) + (item.type == 'marker' ? 1 : 0)));
		}
	},
	
	savePoints: function() {
		var type = '';
		var icon = '';
		var stroke_color = '';
		var stroke_weight = '';
		var stroke_opacity = '';
		var fill_color = '';
		var fill_opacity = '';
		var points = [];
		
		if (myGmaps.items.length > 0) {
			var item = myGmaps.items[0];
			
			type = item.type;
			icon = item.icon;
			
			if (item.type == 'polyline') {
				stroke_color = item.o[0].strokeColor;
				stroke_weight = item.o[0].strokeWeight;
				stroke_opacity = item.o[0].strokeOpacity;
			}
			if (item.type == 'polygon') {
				stroke_color = item.o[0].strokeColor;
				stroke_weight = item.o[0].strokeWeight;
				stroke_opacity = item.o[0].strokeOpacity;
				fill_color = item.o[0].fillColor;
				fill_opacity = item.o[0].fillOpacity;
			}
			
			if (item.type == 'kml') {
				points.push(item.url);
			}
			for (i in item.markers) {
				points.push(item.markers[i].lat + "|" + item.markers[i].lng);
			}
		}
		else {
			type = 'none';
		}
		
		$('input[name=elt_type]').val(type);
		$('input[name=icon]').val(icon);
		$('input[name=stroke_color]').val(stroke_color);
		$('input[name=stroke_weight]').val(stroke_weight);
		$('input[name=stroke_opacity]').val(stroke_opacity);
		$('input[name=fill_color]').val(fill_color);
		$('input[name=fill_opacity]').val(fill_opacity);
		$('textarea[name=post_excerpt]').val(points.join("\n"));
	},
	
	setListeners: function(item,type,infowindow) {
		if (myGmaps.mode == 'view') {
			if (type == 'marker' || type == 'polyline' || type == 'polygon') {
				myGmaps.events.push(google.maps.event.addListener(item, 'click', function(event) {
					var latlng = type != 'marker' ? event.latLng : item.getPosition();
					myGmaps.infowindow.close();
					myGmaps.infowindow.setContent(infowindow);
					myGmaps.infowindow.setPosition(latlng);
					myGmaps.infowindow.open(myGmaps.map);
				}));
			}
		}
		if (myGmaps.mode == 'edit') {
			if (type == 'polyline') {
				myGmaps.events.push(google.maps.event.addListener(item, 'click', myGmaps.updPolylineOptions));
			}
			if (type == 'polygon') {
				myGmaps.events.push(google.maps.event.addListener(item, 'click', myGmaps.updPolygonOptions));
			}
			if (type == 'marker') {
				myGmaps.events.push(google.maps.event.addListener(item, 'click', function() {
					for (var i = 0, I = myGmaps.items[0].o.length; i < I && myGmaps.items[0].o[i] != item; ++i);
					myGmaps.infowindow.close();
					myGmaps.delPoint(i);
					myGmaps.updDetails();
				}));
				myGmaps.events.push(google.maps.event.addListener(item, 'dragend', function() {
					for (var i = 0, I = myGmaps.items[0].o.length; i < I && myGmaps.items[0].o[i] != item; ++i);
					myGmaps.infowindow.close();
					myGmaps.updPoint(item.getPosition(),i);
					myGmaps.updDetails();
				}));
				myGmaps.events.push(google.maps.event.addListener(item, 'rightclick', myGmaps.updMakerOptions));
			}
			if (type == 'map') {
				myGmaps.events.push(google.maps.event.addListener(item, 'click', function(event) {
					myGmaps.infowindow.close();
					myGmaps.addPoint(event.latLng);
					myGmaps.infowindow.close();
				}));
			}
		}
		if (myGmaps.mode == 'config') {
			myGmaps.events.push(google.maps.event.addListener(myGmaps.map, 'click', function (event) {
				myGmaps.updPoint(event.latLng,0);
				myGmaps.map.panTo(event.latLng);
			}));
		}
	},
	
	cleanMap: function() {
		if (myGmaps.mode == 'edit') {
			myGmaps.elt_type = 'none';
			myGmaps.markAsSelected(myGmaps.elt_type);
		}
		for (i in myGmaps.items) {
			for (j in myGmaps.items[i].o) {
				myGmaps.items[i].o[j].setMap(null);
			}
		}
		for (j in myGmaps.events) {
			google.maps.event.removeListener(myGmaps.events[j]);
		}
		myGmaps.items.length = 0;
		myGmaps.events.length = 0;
	},
	
	autoFit: function() {
		var bounds = new google.maps.LatLngBounds();
		
		for (i in myGmaps.items) {
			var start = 0;
			var item = myGmaps.items[i];
			for (i in item.markers) {
				bounds.extend(new google.maps.LatLng(item.markers[i].lat,item.markers[i].lng));
			}
			if (item.type == 'kml') {
				bounds.union(item.o[0].getDefaultViewport());
			}
		}
		
		myGmaps.map.fitBounds(bounds);
	},
	
	drawToolbar: function() {
		var tb = $('<ul/>');
		
		myGmaps.drawSelectButtons(tb);
		myGmaps.drawSearchButton(tb);
		myGmaps.drawAutoFitButton(tb);
		myGmaps.drawResetButton(tb);
		myGmaps.drawKmlButton(tb);
		
		if ((myGmaps.mode == 'edit' || myGmaps.mode == 'config') && $(document).find('#map_toolbar').size() == 0) {
			$('<div/>').attr('id','map_toolbar').append(tb).insertBefore($(myGmaps.target));
		}
	},
	
	drawSelectButtons: function(tb) {
		if (myGmaps.mode != 'edit') return;
		
		tb.append($('<li/>').attr('id','none'));
		tb.append($('<li/>').attr('id','marker'));
		tb.append($('<li/>').attr('id','polyline'));
		tb.append($('<li/>').attr('id','polygon'));
		tb.after($('<p/>').attr('class','form-note').append(myGmaps.msg.select_instructions));
		
		tb.find('li#none,li#marker,li#polyline,li#polygon').click(function() {
			var elt_type = $(this).attr('id');
			var prev_elt_type = myGmaps.items.length > 0 ? myGmaps.items[0].type : myGmaps.elt_type;
			myGmaps.elt_type = elt_type;
			if (elt_type != prev_elt_type && elt_type != 'none') {
				var o = {
					type: myGmaps.elt_type,
					markers: [],
					infowindow: '',
					url: '',
					o: []
				};
				myGmaps.addItems(o);
			}
			myGmaps.markAsSelected(elt_type);
			myGmaps.infowindow.close();
		});
	},
	
	drawSearchButton: function(tb) {
		tb.append($('<li/>').
			append($('<input/>').attr({name:'q',type:'text',size:'50',maxlength:'255'})).
			append($('<input/>').attr({name:'mq',type:'button',class:'submit'}).val(myGmaps.msg.search))
		);
		
		tb.find('input[name=q]').keypress(function(event) {
			if (event.keyCode == 13) {
				tb.find('input[name=mq]').click();
				return false;
			}
		});
		tb.find('input[name=mq]').click(function(event) {
			var search = tb.find('input[name=q]').val();
			var geocoder = new google.maps.Geocoder();
			geocoder.geocode({ 'address': search}, function(results, status) {
				if (status == google.maps.GeocoderStatus.OK) {
					myGmaps.map.panTo(results[0].geometry.location);
				} else {
					alert(myGmaps.msg.geocoder_error + ' ' +status);
				}
			});
		});
	},
	
	drawAutoFitButton: function(tb) {
		if (myGmaps.mode != 'edit') return;
		
		tb.append($('<li/>').
			append($('<input/>').attr({name:'autofit',type:'button',class:'submit'}).val(myGmaps.msg.autofit))
		);
		
		tb.find('input[name=autofit]').click(function() {
			myGmaps.autoFit();
		});
	},
	
	drawResetButton: function(tb) {
		if (myGmaps.mode != 'edit') return;
		
		tb.append($('<li/>').
			append($('<input/>').attr({name:'reset',type:'button',class:'submit'}).val(myGmaps.msg.reset))
		);
		
		tb.find('input[name=reset]').click(function() {
			myGmaps.cleanMap();
		});
	},
	
	drawKmlButton: function(tb) {
		if (myGmaps.mode != 'edit') return;
		
		tb.append($('<li/>').
			append($('<input/>').attr({name:'kml',type:'button',class:'submit'}).val(myGmaps.msg.kml))
		);
		
		tb.find('input[name=kml]').click(function() {
			var url = prompt('URL:', '');
			if (url != null) {
				if (myGmaps.isUrl(url)) {
					myGmaps.addItems({
						type: 'kml',
						marker: [],
						infowindow: '',
						url: url,
						o: []
					});
				}
				else  {
					alert(myGmaps.msg.invalid_url);
				}
			}
		});
	},
	
	updMakerOptions: function() {
		var content = $('<div/>').attr({id:'marker_options',class:'infowindow'});
		var list = $('<ul/>');
		
		for (i in icons) {
			list.append($('<li/>').append(
				$('<img/>').attr('src',icons[i])
			));
		}
		
		content.append($('<fieldset/>').attr('id','icons-form').
			append($('<legend/>').append(myGmaps.msg.marker_options)).
			append($('<p/>').append('choose the marker icon:')).
			append(list)
		);
		
		myGmaps.infowindow.close();
		myGmaps.infowindow.setContent(content.get(0));
		myGmaps.infowindow.setPosition(this.getPosition());
		myGmaps.infowindow.open(myGmaps.map);
		
		$('fieldset#icons-form li').click(function() {
			var start = 0;
			if (myGmaps.elt_type == 'polyline' || myGmaps.elt_type == 'polygon') {
				start = 1;
			}
			for (i = start; i < myGmaps.items[0].o.length; i++) {
				myGmaps.items[0].o[i].setIcon($(this).find('img').attr('src'));
				myGmaps.items[0].icon = $(this).find('img').attr('src');
			}
		});
	},
	
	updPolylineOptions: function(event) {
		var content = $('<div/>').attr({id:'polyline_options',class:'infowindow'});
		
		content.append($('<fieldset/>').
			append($('<legend/>').append(myGmaps.msg.line_options)).
			append($('<p/>').
				append($('<label/>').append(myGmaps.msg.stroke_color)).
				append($('<input/>').attr('type','text').attr({id:'stroke_color',size:'7',class:'colorpicker'}).val(myGmaps.items[0].o[0].strokeColor))
			).
			append($('<p/>').
				append($('<label/>').append(myGmaps.msg.stroke_weight)).
				append($('<input/>').attr({type:'text',id:'stroke_weight',size:'3'}).val(myGmaps.items[0].o[0].strokeWeight)).
				append($('<div/>').attr('id','slider_stroke_weight'))
			).
			append($('<p/>').
				append($('<label/>').append(myGmaps.msg.stroke_opacity)).
				append($('<input/>').attr({type:'text',id:'stroke_opacity',size:'3'}).val(myGmaps.items[0].o[0].strokeOpacity)).
				append($('<div/>').attr('id','slider_stroke_opacity'))
			)
		);
		
		myGmaps.infowindow.close();
		myGmaps.infowindow.setContent(content.get(0));
		myGmaps.infowindow.setPosition(event.latLng);
		myGmaps.infowindow.open(myGmaps.map);
		
		var opts = {};
		
		$('.infowindow input').change(function() {
			var opts = {
				strokeColor: $('input#stroke_color').val(),
				strokeWeight: $('input#stroke_weight').val(),
				strokeOpacity: $('input#stroke_opacity').val()
			}
			myGmaps.items[0].o[0].setOptions(opts);
		});
		$('div#polyline_options input.colorpicker').colorPicker();
		$('div#slider_stroke_weight').slider({
			value: myGmaps.items[0].o[0].strokeWeight,
			min: 1,
			max: 20,
			step: 1,
			slide: function(event, ui) {
				$('input#stroke_weight').val(ui.value);
				$('input#stroke_weight').change();
			}
		});
		$('input#stroke_weight').val($('div#slider_stroke_weight').slider('value'));
		$('div#slider_stroke_opacity').slider({
			value: myGmaps.items[0].o[0].strokeOpacity,
			min: 0.1,
			max: 1,
			step: 0.01,
			slide: function(event, ui) {
				$('input#stroke_opacity').val(ui.value);
				$('input#stroke_opacity').change();
			}
		});
		$('input#stroke_opacity').val($('div#slider_stroke_opacity').slider('value'));
		$('input').keypress(function(event) {
			if (event.keyCode == 13) {
				$('input#apply').click();
				return false;
			}
		});
	},
	
	updPolygonOptions: function(event) {
		var content = $('<div/>').attr({id:'polygon_options',class:'infowindow two-cols'});
		
		content.append($('<div/>').attr('class','col').append(
			$('<fieldset/>').
				append($('<legend/>').append(myGmaps.msg.line_options)).
				append($('<p/>').
					append($('<label/>').append(myGmaps.msg.stroke_color)).
					append($('<input/>').attr('type','text').attr({id:'stroke_color',size:'7',class:'colorpicker'}).val(myGmaps.items[0].o[0].strokeColor))
				).
				append($('<p/>').
					append($('<label/>').append(myGmaps.msg.stroke_weight)).
					append($('<input/>').attr({type:'text',id:'stroke_weight',size:'3'}).val(myGmaps.items[0].o[0].strokeWeight)).
					append($('<div/>').attr('id','slider_stroke_weight'))
				).
				append($('<p/>').
					append($('<label/>').append(myGmaps.msg.stroke_opacity)).
					append($('<input/>').attr({type:'text',id:'stroke_opacity',size:'3'}).val(myGmaps.items[0].o[0].strokeOpacity)).
					append($('<div/>').attr('id','slider_stroke_opacity'))
				)
			)
		);
		content.append($('<div/>').attr('class','col').append(
			$('<fieldset/>').
				append($('<legend/>').append(myGmaps.msg.fill_options)).
				append($('<p/>').
					append($('<label/>').append(myGmaps.msg.fill_color)).
					append($('<input/>').attr({type:'text',id:'fill_color',size:'7',class:'colorpicker'}).val(myGmaps.items[0].o[0].fillColor))
				).
				append($('<p/>').
					append($('<label/>').append(myGmaps.msg.fill_opacity)).
					append($('<input/>').attr({type:'text',id:'fill_opacity',size:'3'}).val(myGmaps.items[0].o[0].fillOpacity)).
					append($('<div/>').attr('id','slider_fill_opacity'))
				)
			)
		);
		
		myGmaps.infowindow.setContent(content.get(0));
		myGmaps.infowindow.setPosition(event.latLng);
		myGmaps.infowindow.open(myGmaps.map);
		$('input.colorpicker').colorPicker();
		
		var opts = {};
		
		$('.infowindow input').change(function() {
			var opts = {
				strokeColor: $('input#stroke_color').val(),
				strokeWeight: $('input#stroke_weight').val(),
				strokeOpacity: $('input#stroke_opacity').val(),
				fillColor: $('input#fill_color').val(),
				fillOpacity: $('input#fill_opacity').val()
			}
			myGmaps.items[0].o[0].setOptions(opts);
		});
		$('div#slider_stroke_weight').slider({
			value: myGmaps.items[0].o[0].strokeWeight,
			min: 1,
			max: 20,
			step: 1,
			slide: function(event, ui) {
				$('input#stroke_weight').val(ui.value);
				$('input#stroke_weight').change();
			}
		});
		$('input#stroke_weight').val($('div#slider_stroke_weight').slider('value'));
		$('div#slider_stroke_opacity').slider({
			value: myGmaps.items[0].o[0].strokeOpacity,
			min: 0.1,
			max: 1,
			step: 0.01,
			slide: function(event, ui) {
				$('input#stroke_opacity').val(ui.value);
				$('input#stroke_opacity').change();
			}
		});
		$('input#stroke_opacity').val($('div#slider_stroke_opacity').slider('value'));
		$('div#slider_fill_opacity').slider({
			value: myGmaps.items[0].o[0].fillOpacity,
			min: 0.1,
			max: 1,
			step: 0.01,
			slide: function(event, ui) {
				$('input#fill_opacity').val(ui.value);
				$('input#fill_opacity').change();
			}
		});
		$('input#fill_opacity').val($('div#slider_fill_opacity').slider('value'));
		$('input').keypress(function(event) {
			if (event.keyCode == 13) {
				return false;
			}
		});
	},
	
	updDetails: function() {
		var type = '';
		
		var list = $('<ul/>');
		
		for (i in myGmaps.items) {
			var item = myGmaps.items[i];
			var points = $('<ul/>');
			var markers = item.markers;
			
			for (j in item.markers) {
				points.append($('<li/>').append(
					'#' + (parseInt(j) + 1) + ' - ' + myGmaps.msg.coordinates + ': ' +
					item.markers[j].lat + ', ' + item.markers[j].lng
				));
			}
			list.append($('<li/>').append(myGmaps.msg.type + ' - ' + item.type).append(points));
		}
		
		$('#map-details').html(list.get(0));
	},
	
	markAsSelected: function(id) {
		$('li#none').removeClass('selected');
		$('li#marker').removeClass('selected');
		$('li#polyline').removeClass('selected');
		$('li#polygon').removeClass('selected');
		$('li#' + id).addClass('selected');
	},
	
	trim: function(str) {
		return str.replace(/^\s+/g,'').replace(/\s+$/g,'')
	},
	
	isUrl: function(str) {
		var exp = new RegExp("^(http://)[a-zA-Z0-9.-]*[a-zA-Z0-9/_-]", "g");
		return exp.test(str);
	}
}