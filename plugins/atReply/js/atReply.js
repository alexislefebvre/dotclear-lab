/* Inspired by
 * http://iyus.info/at-reply-petit-plugin-wordpress-inspire-par-twitter/
 */
function atReply() {
	// empty and remove old links,
	//  see http://api.jquery.com/remove/#comment-41237758
	$('a.at_reply_link').empty().remove();
	
	// add links to each comment
	$('span.commentAuthor').each(function() {
		/* duplicate the link to create an element on-the-fly,
			because the element with its event can't be used twice */
		
		var id = $(this).attr('id').replace('atreply_','c');
		var name = $(this).attr('title');
		
		var titleWithAuthor =
			atReplyTitle.replace("{author}", name);
				
		var $link = $('<a></a>')
			.attr('href', '#')
			.attr('title', titleWithAuthor)
			.addClass('at_reply_link');
		
		var $image = $('<img />')
			.attr('src', atReplyImage)
			.attr('alt', titleWithAuthor);
		
		var $author_span = $('<span></span>')
			.addClass('at_reply_title')
			.css( { 'display': 'none'} )
			.attr('title', titleWithAuthor)
			.html(titleWithAuthor);
		
		$link
			.append($image, $author_span);
		
		/* "click" event */
		$link
			.click( function () {
				$('#c_content').val(
					/* get current content */
					$('#c_content').val() +
					/* append @Reply link in <textarea> */
					'@[' + name + '|' +
					/* URL without hash, see http://stackoverflow.com/a/1397347/2257664 */
					window.location.href.split('#')[0]
						/* Dotclear add '&pub=1' when a comment has been sent */
						.replace('&pub=1', '') +
					'#' + id + '] : ');
				/* show comment form on Noviny theme and its derivatives */
				$('#comment-form h3').find('a').trigger('click');
				/* Noviny will put the focus on the name field,
					set the focus to the comment content*/
				$('#c_content').focus();
				
				return false;
			});
		
		/* add the link */
		$(this)
			.parent()
			.append($link);
		
		if (atReplyDisplayTitle)
		{
			/* add an hover effect */
			$(this).parent().hover(
				function () {
					$(this).find('.at_reply_title').show();
				},
				function () {
					$(this).find('.at_reply_title').hide();
				}
			);
		}
	});
}

$(function() {
	atReply();
});
