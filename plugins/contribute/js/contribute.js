/* from /dotclear/admin/js/common.js
Dotclear common object
-------------------------------------------------------- */
var dotclear = {
	msg: {}
};

/* from /dotclear/admin/js/_post.js
-------------------------------------------------------- */
$(function() {
	if (!document.getElementById) { return; }
	
	var excerptTb = new jsToolBar(document.getElementById('post_excerpt'));
	var contentTb = new jsToolBar(document.getElementById('post_content'));
	excerptTb.context = contentTb.context = 'post';

	// Load toolbars
	excerptTb.switchMode('wiki');
	contentTb.switchMode('wiki');
});

/* tags
-------------------------------------------------------- */
$(function() {
	$('#available-tags .tags a').click(function () {		
		var separator = ',';
		var text = $(this).text();
		if ($('#post_tags').val() == '') {var separator = '';}
		if ($('#post_tags').val().indexOf(text) == -1) {
			$("#post_tags").val($("#post_tags").val()+separator+text);
		}
		$(this).remove();
		if ($('#available-tags .tags').find('a').length == 0) {
			$('#available-tags').hide();
		}
		return false;
	});
});