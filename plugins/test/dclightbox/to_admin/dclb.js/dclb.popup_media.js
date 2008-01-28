$(function() {
	var toolBar = window.opener.the_toolbar.textarea;
	
	$('#media-insert').onetabload(function() {
		$('#media-insert-cancel').click(function() {
			window.close();
		});
		
		$('#media-insert-ok').click(function() {
			sendClose();
			window.close();
		});
	});
	
	function sendClose() {
		var insert_form = $('#media-insert-form').get(0);
		if (insert_form == undefined) { return; }
		
		var tb = window.opener.the_toolbar;
		tb.elements.img_select.data.src = tb.stripBaseURL($(insert_form).find('input[@name="src"]').radioVal());
		tb.elements.img_select.data.alignment = $(insert_form).find('input[@name="alignment"]').radioVal();
		tb.elements.img_select.data.link =  $(insert_form).find('input[@name="insertion"]').radioVal() == 'link' || $(insert_form).find('input[@name="insertion"]').radioVal() == 'lboxlink';
		tb.elements.img_select.data.title =  insert_form.elements.title.value;
		tb.elements.img_select.data.url = tb.stripBaseURL(insert_form.elements.url.value);
		tb.elements.img_select.data.lbox = $(insert_form).find('input[@name="insertion"]').radioVal() == 'lboxlink';
		tb.elements.img_select.data.gname =  insert_form.elements.gname.value;
		tb.elements.img_select.fncall[tb.mode].call(tb);
	};
});