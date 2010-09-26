$(document).ready(ofcAnonymousCommentInit);

function ofcAnonymousCommentInit() {
	ofcAnonymousComment($('#c_anonymous').attr("checked"));
	$('#c_anonymous').click(function() {ofcAnonymousComment($(this).attr("checked"));});
}
function ofcAnonymousComment(state){
        if (state) {
			$('#c_name').parent().fadeOut();
			$('#c_mail').parent().fadeOut();
			$('#c_site').parent().fadeOut();
			$('#c_remember').parent().fadeOut();
			$('.ofc-twitterlogin').fadeOut();
			$('#subscribeToComments').removeAttr('checked').attr('disabled',true);
		}
		else {
			$('#c_name').parent().fadeIn();
			$('#c_mail').parent().fadeIn();
			$('#c_site').parent().fadeIn();
			$('#c_remember').parent().fadeIn();
			$('.ofc-twitterlogin').fadeIn();
			$('#subscribeToComments').removeAttr('disabled');
		}
}