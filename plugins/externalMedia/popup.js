$(function() {
	$('#media-insert-cancel').click(function() {
		window.close();
	});
	
	$('#media-insert-ok').click(function() {
		var url=$('#media-insert-form').get(0).m_url.value;
		$.getJSON('http://oohembed.com/oohembed/?url='+url+'&callback=?',
		function (data) {
			sendClose(data.html);
		});
	});
});	
function sendClose(object) {
	
	var insert_form = $('#media-insert-form').get(0);
	if (insert_form == undefined) { return; }
	
	var tb = window.opener.the_toolbar;
	var data = tb.elements.extmedia.data;
	
	data.alignment = $('input[name="alignment"]:checked',insert_form).val();
	data.title = insert_form.m_title.value;
	data.url = insert_form.m_url.value;
	data.m_object = object;
	
	tb.elements.extmedia.fncall[tb.mode].call(tb);
	window.close();
}
