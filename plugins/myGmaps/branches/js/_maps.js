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
	$('#config').tabload(function() {
		var opts = {};
		var item = {
			type: 'marker',
			markers: [],
			icon: new google.maps.MarkerImage(
				'index.php?pf=myGmaps/images/target_icon.png',
				null,
				null,
				new google.maps.Point(32, 32)
			),
			infowindow: '',
			url: '',
			o: []
		}
		
		if ($('input[name=center]').val() != '') {
			item.markers.push({
				lat: parseFloat($('input[name=center]').val().split(',')[0]),
				lng: parseFloat($('input[name=center]').val().split(',')[1])
			});
			opts.center = {
				lat: parseFloat($('input[name=center]').val().split(',')[0]),
				lng: parseFloat($('input[name=center]').val().split(',')[1])
			}
		}
		if ($('input[name=zoom]').val() != '') {
			opts.zoom = $('input[name=zoom]').val();
		}
		if ($('input[name=map_type]').val() != '') {
			opts.map_type = $('input[name=map_type]').val();
		}
		opts.scrollwheel = $('input[name=scrollwheel]').attr('checked');
		opts.mode = 'config';
		
		myGmaps.init(opts);
		myGmaps.addItems(item);
		
		$('input[name=scrollwheel]').click(function() {
			myGmaps.map.setOptions({
				scrollwheel: $(this).attr('checked')
			});
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