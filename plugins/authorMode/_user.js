$(function() {
	if (!document.getElementById) { return; }
	
	var tbUser = new jsToolBar(document.getElementById('user_desc'));
	tbUser.draw('xhtml');
});