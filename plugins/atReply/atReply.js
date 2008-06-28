$(document).ready(function() {
	$('#comments dt').each(function() {
		$(this).append(atReply);
	});
	$('#comments dt img').click( function () {
		var name = '';
		var id = $(this).parent().attr('id');
		name = $(this).parent().children('.commentAuthor').text();
		var str = '@['+name+'|#'+id+'] : ';
		$('#c_content').val($('#c_content').val()+str);
		$('#c_content').focus();
	});
});