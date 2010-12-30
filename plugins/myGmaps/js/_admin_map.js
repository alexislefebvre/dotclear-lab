$(function () {

    if (!document.getElementById) {
        return;
    }

    if (document.getElementById('map_canvas'))
    {

        //Map picker
		function trim (myString) {
			return myString.replace(/^\s+/g,'').replace(/\s+$/g,'')
		} 
		
		
		
		if ($('input[name=myGmaps_center]').attr('value') =='') {
			var default_location = new google.maps.LatLng(43.0395797336425, 6.126280043989323);
			var default_zoom = '12';
			var default_type = 'roadmap';
		} else {
			var parts = $('input[name=myGmaps_center]').attr('value').split(",");
			var lat = parseFloat(trim(parts[0]));
			var lng = parseFloat(trim(parts[1]));
			var default_location = new google.maps.LatLng(lat, lng);
			var default_zoom = $('input[name=myGmaps_zoom]').attr('value');
			var default_type = $('input[name=myGmaps_type]').attr('value');
		}
		
		var myOptions = {
            zoom: parseFloat(default_zoom),
            center: default_location,
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
		
		$(".part-tabs li a").click(function () {
			resizeMap();
        });
		
		function resizeMap() {
			if ($('#settings').css('display') == 'block') {
				if ($('input[name=myGmaps_center]').attr('value') =='') {
					var default_location = new google.maps.LatLng(43.0395797336425, 6.126280043989323);
					var default_zoom = '12';
					var default_type = 'roadmap';
				} else {
					var parts = $('input[name=myGmaps_center]').attr('value').split(",");
					var lat = parseFloat(trim(parts[0]));
					var lng = parseFloat(trim(parts[1]));
					var default_location = new google.maps.LatLng(lat, lng);
					var default_zoom = $('input[name=myGmaps_zoom]').attr('value');
					var default_type = $('input[name=myGmaps_type]').attr('value');
				}
				google.maps.event.trigger(map, 'resize');
				map.setCenter(default_location);
				map.setZoom(parseFloat(default_zoom));
				if (default_type == 'roadmap') {
					map.setOptions({mapTypeId: google.maps.MapTypeId.ROADMAP});
				} else if (default_type == 'satellite') {
					map.setOptions({mapTypeId: google.maps.MapTypeId.SATELLITE});
				} else if (default_type == 'hybrid') {
					map.setOptions({mapTypeId: google.maps.MapTypeId.HYBRID});
				} else if (default_type == 'terrain') {
					map.setOptions({mapTypeId: google.maps.MapTypeId.TERRAIN});
				}
			}
		}
		
        //Place marker
		
		marker = new google.maps.Marker({
			position: default_location,
			draggable: true,
			map: map
		});		

		google.maps.event.addListener(marker, "dragend", function () {
			map.setCenter(this.position);
		});
		
		$('#settings-form').submit(function() {
			var default_location = map.getCenter().lat()+', '+map.getCenter().lng();
			
			var default_zoom = map.getZoom();
			var default_type = map.getMapTypeId();
			
			$('input[name=myGmaps_center]').attr('value',default_location);
			$('input[name=myGmaps_zoom]').attr('value',default_zoom);
			$('input[name=myGmaps_type]').attr('value',default_type);
			return true;

		});
    }
});