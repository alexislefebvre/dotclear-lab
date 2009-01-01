

$(function() {
	if (!document.getElementById) { return; }

	// Default hreflang
	var langField = document.getElementById('post_lang');
	jsToolBar.prototype.elements.link.default_hreflang = langField.value;
	
	// Get document format and prepare toolbars
	var formatField = $('#post_format').get(0);
	$(formatField).change(function() {
		excerptTb.switchMode(this.value);
		contentTb.switchMode(this.value);
	});
	
	var excerptTb = new jsToolBar(document.getElementById('post_excerpt'));
	var contentTb = new jsToolBar(document.getElementById('post_content'));
	excerptTb.context = contentTb.context = 'post';
	
	dotclear.hideLockable();
	
	// Add date picker
	var post_dtPick = new datePicker($('#post_dt').get(0));
	post_dtPick.img_top = '1.5em';
	post_dtPick.draw();
	
	// Confirm post deletion
	$('input[@name="delete"]').click(function() {
			return window.confirm(dotclear.msg.confirm_delete_post);
	});
	
	// Hide some fields
	$('#notes-area label').toggleWithLegend($('#notes-area').children().not('label'),{
		cookie: 'dcx_post_notes',
		hide: $('#post_notes').val() == ''
	});
	$('#post_lang').parent().toggleWithLegend($('#post_lang'),{
		cookie: 'dcx_post_lang'
	});
	$('#post_password').parent().toggleWithLegend($('#post_password'),{
		cookie: 'dcx_post_password',
		hide: $('#post_password').val() == ''
	});
	
	// We load toolbar on excerpt only when it's ready
	$('#excerpt-area label').toggleWithLegend($('#excerpt-area').children().not('label'),{
		fn: function() { excerptTb.switchMode(formatField.value); },
		cookie: 'dcx_post_excerpt',
		hide: $('#post_excerpt').val() == ''
	});
	
	// Load toolbars
	contentTb.switchMode(formatField.value);
	
	// Replace attachment remove links by a POST form submit
	$('a.attachment-remove').click(function() {
		this.href = '';
		if (window.confirm(dotclear.msg.confirm_remove_attachment)) {
			var f = $('#attachment-remove-hide').get(0);
			f.elements['media_id'].value = this.id.substring(11);
			f.submit();
		}
		return false;
	});
		
});

