/*
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of plugin randomComment for Dotclear 2.
# Copyright (c) 2008 Thomas Bouron.
#
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------
*/
$(document).ready(function() {
	getRandomComment();
});

/**
 * Get a new random comment in database
 */
function getRandomComment()
{
	$.ajax({
		type: 'GET',
		url: random_comment_url,
		success: function(str){
			$('#rd_content').html(str);
			$('#rd_content').fadeIn(1000,function() {
				setTimeout('reloadRandomComment()',random_comment_ttl);
			});
		},
		async: true
	});
}

/**
 * Reload the function to get a new random comment
 */
function reloadRandomComment()
{
	$('#rd_content').fadeOut(1000,function() {
		getRandomComment();
	});
}
