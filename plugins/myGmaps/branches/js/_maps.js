$(function() {
	$('.checkboxes-helpers').each(function() {
		dotclear.checkboxesHelpers(this);
	});
	//$('#form-entries td input[type=checkbox]').enableShiftClick();
	//dotclear.postsActionsHelper();
	
	$('#map-details-area label').toggleWithLegend($('#map-details'), {
		cookie: 'dcx_map_detail'
	});
	
	// Configuration tab
	$('#config').tabload(function() {
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
		myGmaps.scrollwheel = $('input[name=scrollwheel]').attr('checked');
		myGmaps.init();
		myGmaps.startDraw('none');
		myGmaps.updDetails();
		
		var icon = new google.maps.MarkerImage(
			'index.php?pf=myGmaps/images/target_icon.png',
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
		
		google.maps.event.addListener(myGmaps.map, 'click', function (event) {
			marker.setPosition(event.latLng);
			myGmaps.map.panTo(event.latLng);
			myGmaps.updDetails();
		});
		google.maps.event.addListener(marker, 'dragend', function () {
			myGmaps.map.panTo(this.position);
			myGmaps.updDetails();
		});
		$('input[name=scrollwheel]').click(function() {
			myGmaps.map.setOptions({
				scrollwheel: $(this).attr('checked')
			});
		});
		$('input[name=q]').keypress(function(event) {
			if (event.keyCode == 13) {
				$('input[name=mq]').click();
				return false;
			}
		});
	});
	$('#config').tabload();
	
	// Icons tab
	$('#icons-form li').each(function() {
		var css = {
			'background-position': 'middle center',
			'cursor': 'pointer',
			'height': '50px'
		};
		$(this).css(css);
		$(this).children('input').css('display','none');
		$(this).click(function() {
			if ($(this).children('input').is(':checked')) {
				$(this).children('input').removeAttr('checked');
				$(this).css('background-color','transparent');
			}
			else {
				$(this).children('input').attr('checked',true);
				$(this).css('background-color','#E2DFCA');
			}
		});
	});
	$('#icons-form input[name=delete]').click(function() {
		if ($('#icons-form li input:checked').size() == 0) {
			alert(myGmaps.msg.no_icon_selected);
			return false;
		}
	});
});