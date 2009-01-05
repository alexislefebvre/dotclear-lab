/* Inspired by http://iyus.info/at-reply-petit-plugin-wordpress-inspire-par-twitter/ */
$(function() {
	/* create link */
	var atReplyLink = ' <a href="#" title="'+atReplyTitle+'">'+ 
		'<img src="'+atReplyImage+'" alt="'+atReplyTitle+'" /> '+ 
		'<span class="at_reply_title" style="display:none;">'+
				atReplyTitle+'</span></a>';
	
	$('span.commentAuthor').each(function() {
		/* duplicate the link to create an element on-the-fly,
			because the element with its event can't be used twice */
		var link = $(atReplyLink).click( function () {
			var commentAuthor = $(this).parent().children('.commentAuthor');
			var id = commentAuthor.attr('id').replace('atreply_','c');
			var name = commentAuthor.attr('title');
			$('#c_content').val($('#c_content').val()+'@['+name+'|#'+id+'] : ');
			/* show comment form on Noviny theme and its derivatives */
			$('#comment-form h3').find('a').trigger('click');
			/* Noviny will put the focus on the name field,
				set the focus to the comment content*/
			$('#c_content').focus();
			return false;
		});
		/* add the link and an hover event*/
		$(this).parent().append(link).hover(
		function () {
			$(this).find('.at_reply_title').show();
		},
		function () {
			$(this).find('.at_reply_title').hide();
		});
	});
});