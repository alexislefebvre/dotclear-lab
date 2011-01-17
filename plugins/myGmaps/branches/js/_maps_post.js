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
	
	myGmaps.init();
});