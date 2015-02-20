$(function() {
	if ($.isFunction(jsToolBar)) {
		var tbUser = new jsToolBar(document.getElementById('disclaimer_text'));
		tbUser.draw('xhtml');
	}
});