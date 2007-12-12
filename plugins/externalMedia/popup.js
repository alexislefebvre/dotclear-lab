$(function() {
	$('#media-insert-cancel').click(function() {
		window.close();
	});
	
	$('#media-insert-ok').click(function() {
		sendClose();
		window.close();
	});
	
	function sendClose() {
		var insert_form = $('#media-insert-form').get(0);
		if (insert_form == undefined) { return; }
		
		var tb = window.opener.the_toolbar;
		var data = tb.elements.extmedia.data;
		
		data.alignment = $(insert_form).find('input[@name="alignment"]').radioVal();
		data.title = insert_form.m_title.value;
		data.url = insert_form.m_url.value;
		data.m_object = insert_form.m_object.value;
		
		tb.elements.extmedia.fncall[tb.mode].call(tb);
	};
});