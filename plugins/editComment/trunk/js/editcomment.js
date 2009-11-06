/*
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of editComment, a plugin for Dotclear.
# 
# Copyright (c) 2009 Tomtom
# http://blog.zenstyle.fr/
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------
*/
$('input.submit').click(function() {
	$.post(
		$('#comment-form.edit-form').attr('action')+'/ajax',
		{	c_name: $('#comment-form.edit-form #c_name').val(),
			c_mail: $('#comment-form.edit-form #c_mail').val(),
			c_site: $('#comment-form.edit-form #c_site').val(),
			c_content: $('#comment-form.edit-form #c_content').val(),
			c_id: $('#comment-form.edit-form #c_id').val()
		},
		function(str) {
			if (str === '') {
				tb_remove();
				location.reload();
			}
			else {
				$('#comment-form.edit-form').prepend('<p class="error">'+str+'<p>');
			}
			
		}
	);
	return false;
});