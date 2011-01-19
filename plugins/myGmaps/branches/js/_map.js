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
		cookie: 'dcx_map_description'
	});
	$('#notes-area label').toggleWithLegend($('#notes-area').children().not('label'), {
		cookie: 'dcx_post_notes'
	});
	
	var opts = {};
	
	opts.center = {
		lat: parseFloat($('input[name=center]').val().split(',')[0]),
		lng: parseFloat($('input[name=center]').val().split(',')[1])
	}
	opts.zoom = $('input[name=zoom]').val();
	opts.map_type = $('input[name=map_type]').val();
	opts.scrollwheel = $('input[name=scrollwheel]').val();
	opts.mode = 'edit';
	
	myGmaps.init(opts);
	
	var polyline = {};
	var polygon = {};
	
	if ($('input[name=stroke_color]').val() != '') {
		polyline.strokeColor = $('input[name=stroke_color]').val();
		polygon.strokeColor = $('input[name=stroke_color]').val();
	}
	if ($('input[name=stroke_weight]').val() != '') {
		polyline.strokeWeight = parseFloat($('input[name=stroke_weight]').val());
		polygon.strokeWeight = parseFloat($('input[name=stroke_weight]').val());
	}
	if ($('input[name=stroke_opacity]').val() != '') {
		polyline.strokeOpacity = parseFloat($('input[name=stroke_opacity]').val());
		polygon.strokeOpacity = parseFloat($('input[name=stroke_opacity]').val());
	}
	if ($('input[name=fill_color]').val() != '') {
		polygon.fillColor = $('input[name=fill_color]').val();
	}
	if ($('input[name=fill_opacity]').val() != '') {
		polygon.fillOpacity = parseFloat($('input[name=fill_opacity]').val());
	}
	
	myGmaps.setObjectsOptions('polyline',polyline);
	myGmaps.setObjectsOptions('polygon',polygon);
	
	var item = {
		type: '',
		markers: [],
		icon: '',
		infowindow: '',
		url: '',
		o: []
	}
	var markers = $('textarea[name=post_excerpt]').val().split("\n");
	
	item.type = $('input[name=elt_type]').val();
	item.icon = $('input[name=icon]').val();
	
	if (item.type != 'none') {
		for (i in markers) {
			if (myGmaps.isUrl(markers[i])) {
				item.url = markers[i];
			}
			else {
				item.markers.push({
					lat: markers[i].split('|')[0],
					lng: markers[i].split('|')[1]
				});
			}
		}
		myGmaps.addItems(item);
	}
	
	$('#entry-form').submit(function () {
		var content = $("textarea[name=post_content]").val();
		if (content == '') {
			$("textarea[name=post_content]").val(myGmaps.msg.no_description);
		}
		myGmaps.savePoints();
		return true;
	});
});