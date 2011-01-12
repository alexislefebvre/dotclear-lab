$(function () {
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
	
	//Date picker
	if (document.getElementById('post_dt')) {
		var post_dtPick = new datePicker($('#post_dt').get(0));
		post_dtPick.img_top = '1.5em';
		post_dtPick.draw();
	}
	
	// Hide some fields	
	$('#description-area label').toggleWithLegend($('#description-area').children().not('label'), {
		cookie: 'dcx_map_description',
		hide: $('#post_content').val() == 'Pas de description'
	});
	$('#map-details-area label').toggleWithLegend($('#map-details'), {
		cookie: 'dcx_map_detail'
	});
	$('#notes-area label').toggleWithLegend($('#notes-area').children().not('label'), {
		cookie: 'dcx_post_notes',
		hide: $('#post_notes').val() == ''
	});
	
	// Map initialization
	if ($('input[name=center]').val() != '') {
		myGmaps.center.lat = $('input[name=center]').val().split(',')[0];
		myGmaps.center.lng = $('input[name=center]').val().split(',')[1];
	}
	if ($('input[name=zoom]').val() != '') {
		myGmaps.zoom = $('input[name=zoom]').val();
	}
	if ($('input[name=map_type]').val() != '') {
		myGmaps.type = $('input[name=map_type]').val();
	}
	if ($('input[name=scrollwheel]').val() != '') {
		myGmaps.scrollwheel = $('input[name=scrollwheel]').val() == '1' ? true : false;
	}
	myGmaps.init();
	myGmaps.updDetails();
	
	// Events
	google.maps.event.addListener(myGmaps.objects.polyline, 'click', myGmaps.updPolylineOptions);
	google.maps.event.addListener(myGmaps.objects.polygon, 'click', myGmaps.updPolygonOptions);
	$('li#none,li#marker,li#polyline,li#polygon').click(function() {
		myGmaps.startDraw($(this).attr('id'));
	});
	$('input[name=reset]').click(function () {
		myGmaps.delOverlays(true);
	});
	$('input[name=q]').keypress(function(event) {
		if (event.keyCode == 13) {
			$('input[name=mq]').click();
			return false;
		}
	});
	$('input[name=kml]').click(function () {
		var msg = prompt('URL:', '');
		myGmaps.addKml(msg);
	});
	$('#entry-form').submit(function () {
		var post_maps = $('#post_maps').val();
		if (post_maps == '') {
			$('#post_maps').val('none');
		}
		var content = $("textarea[name=post_content]").val();
		if (content == '') {
			$("textarea[name=post_content]").val('Pas de description');
		}
		setConfig(); return false;
		var center = map.getCenter().lat()+','+map.getCenter().lng();
		var zoom = map.getZoom();
		var map_type = map.getMapTypeId();
		var elt_type = type == 'hand' ? 'none' : type;
		
		$('input[name=center]').val(center);
		$('input[name=zoom]').val(zoom);
		$('input[name=map_type]').val(map_type);
		$('input[name=elt_type]').val(elt_type);
		
		var list = [];
		var icon = '';
		for (i in markers) {
			if (is_url(markers[i])) {
				list.push(markers[i]);
			}
			else {
				list.push(markers[i].getPosition().lat() + "|" + markers[i].getPosition().lng() + "|" + icon);
			}
		}
		$('#post_excerpt').val(list.join('\n'));
		return true;
	});
});