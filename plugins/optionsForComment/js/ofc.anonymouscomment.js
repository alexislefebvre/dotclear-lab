/* -- BEGIN LICENSE BLOCK ----------------------------------
 * This file is part of optionsForComment, a plugin for Dotclear 2.
 * 
 * Copyright (c) 2009-2011 JC Denis and contributors
 * jcdenis@gdwd.com
 * 
 * Licensed under the GPL version 2.0 license.
 * A copy of this license is available in LICENSE file or at
 * http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * -- END LICENSE BLOCK ------------------------------------*/

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