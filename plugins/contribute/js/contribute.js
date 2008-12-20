/* from /dotclear/admin/js/common.js
ChainHandler, py Peter van der Beken
-------------------------------------------------------- */
function chainHandler(obj, handlerName, handler) {
	obj[handlerName] = (function(existingFunction) {
		return function() {
			handler.apply(this, arguments);
			if (existingFunction)
				existingFunction.apply(this, arguments); 
		};
	})(handlerName in obj ? obj[handlerName] : null);
};

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
	
	// Get document format and prepare toolbars
	var formatField = $('#post_format').get(0);
	$(formatField).change(function() {
		excerptTb.switchMode(this.value);
		contentTb.switchMode(this.value);
		if (this.value == 'wiki') {
			$('#wiki-syntax-reference').show();
		} else {
			$('#wiki-syntax-reference').hide();
		}
		if (this.value == 'xhtml') {
			$('#p-convert-xhtml').hide();
		} else {
			$('#p-convert-xhtml').show();
		}
	});
	
	if (document.getElementById('post_excerpt')) {
		var excerptTb = new jsToolBar(document.getElementById('post_excerpt'));
		excerptTb.context = 'post';
		// Load toolbar
		excerptTb.switchMode(formatField.value);
	}
	var contentTb = new jsToolBar(document.getElementById('post_content'));
	contentTb.context = 'post';
	
	// Load toolbar
	contentTb.switchMode(formatField.value);
});

/* tags
-------------------------------------------------------- */
$(function() {
	if ($('#post_format').val() != 'wiki') {
		$('#wiki-syntax-reference').hide();
	}
	if ($('#post_format').val() == 'xhtml') {
		$('#p-convert-xhtml').hide();
	}
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