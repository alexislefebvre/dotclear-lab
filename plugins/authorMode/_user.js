$(function() {
	if ($.isFunction(jsToolBar)) {
		var tbUser = new jsToolBar(document.getElementById('user_desc'));
		tbUser.draw('xhtml');
	}
});