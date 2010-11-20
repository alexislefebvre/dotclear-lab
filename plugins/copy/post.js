$(function() {
	html = ' <input type="submit" value="'+dotclear.msg.create_copy+'" name="copy" title="'+dotclear.msg.save_as_new+'" />';
	$('input[type=submit][name=delete]').after(html);
});