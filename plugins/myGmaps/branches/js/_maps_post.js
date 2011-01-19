$(function() {
	$('.checkboxes-helpers').each(function() {
		dotclear.checkboxesHelpers(this);
	});
	$('#form-entries td input[type=checkbox]').enableShiftClick();
	$('#form-entries').submit(function() {
		var checked = false;
		
		$(this).find('input[name="entries[]"]').each(function() {
			if (this.checked) {
				checked = true;
			}
		});
		
		if (!checked) { return false; }
		
		return true;
	});
	$('input[name=autofit]').click(function() {
		myGmaps.autoFit();
		return false;
	});
	
	var opts = {};
	
	opts.center = {
		lat: parseFloat($('input[name=center]').val().split(',')[0]),
		lng: parseFloat($('input[name=center]').val().split(',')[1])
	}
	opts.zoom = $('input[name=zoom]').val();
	opts.map_type = $('input[name=map_type]').val();
	opts.scrollwheel = $('input[name=scrollwheel]').val();
	
	opts.mode = 'view';
	
	myGmaps.init(opts);
});