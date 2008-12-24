/* Inspired by http://iyus.info/at-reply-petit-plugin-wordpress-inspire-par-twitter/ */
$(function() {
	$('span.commentAuthor').each(function() {
		$(this).parent().append(atReply);
	});
	
	$('a.at_reply').click( function () {
		var name = '';
		var id = $(this).parent().attr('id');
		name = $(this).parent().children('.commentAuthor').text();
		var str = '@['+name+'|#'+id+'] : ';
		$('#c_content').val($('#c_content').val()+str);
		$('#c_content').focus();
		return false;
	});
	
	$('#comments dt').hover(
	function () {
		$(this).find('.at_reply_title').show();
	},
	function () {
		$(this).find('.at_reply_title').hide();
	});
	
	$('#comments dd').hover(
	function () {
		$(this).prev().find('.at_reply_title').show();
	},
	function () {
		$(this).prev().find('.at_reply_title').hide();
	});
});