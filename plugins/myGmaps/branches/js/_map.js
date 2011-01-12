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
	if ($('input[name=map_type]').val() != '') {
		myGmaps.type = $('input[name=map_type]').val();
	}
	if ($('input[name=elt_type]').val() != '') {
		myGmaps.elt_type = $('input[name=elt_type]').val();
	}
	if (myGmaps.elt_type == 'polyline') {
		myGmaps.options[myGmaps.elt_type].strokeWeight = $('input[name=stroke_weight]').val();
		myGmaps.options[myGmaps.elt_type].strokeOpacity = $('input[name=stroke_opacity]').val();
		myGmaps.options[myGmaps.elt_type].strokeColor = $('input[name=stroke_color]').val();
	}
	if (myGmaps.elt_type == 'polygon') {
		myGmaps.options[myGmaps.elt_type].strokeWeight = $('input[name=stroke_weight]').val();
		myGmaps.options[myGmaps.elt_type].strokeOpacity = $('input[name=stroke_opacity]').val();
		myGmaps.options[myGmaps.elt_type].strokeColor = $('input[name=stroke_color]').val();
		myGmaps.options[myGmaps.elt_type].strokeWeight = $('input[name=stroke_weight]').val();
	}
	
	myGmaps.init();
	myGmaps.loadData();
	
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
		var content = $("textarea[name=post_content]").val();
		if (content == '') {
			$("textarea[name=post_content]").val('Pas de description');
		}
		myGmaps.setMapPoints();
		return true;
	});
});