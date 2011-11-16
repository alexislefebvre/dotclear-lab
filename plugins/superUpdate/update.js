$(function() {
	var update_html =
	'<li>' + dotclear.msg.forced_update_warning + 
	'<p><a href="update.php?step=download">' + dotclear.msg.update_anyway + '</a>' +
	' - <a href="update.php?hide_msg=1">' + dotclear.msg.cancel_update + '</a>' +
	'</p></li>';
	
	$('#update_err_files_changed').parent().after(update_html)
});