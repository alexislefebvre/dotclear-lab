$(function() {
	$('.checkboxes-helpers').each(function() {
		dotclear.checkboxesHelpers(this);
	});
	$('#form-entries td input[type=checkbox]').enableShiftClick();
	dotclear.postsActionsHelper();
	
	$('#map-details-area label').toggleWithLegend($('#map-details'), {
		cookie: 'dcx_map_detail'
	});
	
	// Configuration tab
	if ($('input#center').val() != '') {
		myGmaps.center.lat = $('input#center').val().split(',')[0];
		myGmaps.center.lng = $('input#center').val().split(',')[1];
	}
	if ($('input#zoom').val() != '') {
		myGmaps.zoom = $('input#zoom').val();
	}
	if ($('input#map_type').val() != '') {
		myGmaps.type = $('input#map_type').val();
	}
	myGmaps.scrollwheel = $('input#scrollwheel').attr('checked');
	myGmaps.init();
	myGmaps.updDetails();
	
	var icon = new google.maps.MarkerImage(
		'index.php?pf=myGmaps/icons/target_icon.png',
		null,
		null,
		new google.maps.Point(32, 32)
	);
	var marker = new google.maps.Marker({
		icon: icon,
		raiseOnDrag: false,
		position: new google.maps.LatLng(parseFloat(myGmaps.center.lat), parseFloat(myGmaps.center.lng)),
		draggable: true
	});
	
	marker.setMap(myGmaps.map);
	
	google.maps.event.addListener(marker, 'dragend', function () {
		myGmaps.map.panTo(this.position);
		myGmaps.updDetails();
	});
	$('input#scrollwheel').click(function() {
		myGmaps.map.setOptions({
			scrollwheel: $(this).attr('checked')
		});
	});
});