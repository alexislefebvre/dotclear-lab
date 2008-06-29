$(document).ready(function() {
	$('span.commentAuthor').each(function() {
		$(this).parent().append(atReply);
	});
	$('img.at_reply').click( function () {
		var name = '';
		var id = $(this).parent().attr('id');
		name = $(this).parent().children('.commentAuthor').text();
		var str = '@['+name+'|#'+id+'] : ';
		$('#c_content').val($('#c_content').val()+str);
		$('#c_content').focus();
	});
});