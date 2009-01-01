/*
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of plugin feedburner for Dotclear 2.
# Copyright (c) 2008 Thomas Bouron.
#
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------
*/

$(function() {
	feeds = ['rss','rssco','atom','atomco'];
	$.each(feeds,function(i) { 
		if ($("input[name='"+feeds[i]+"']").val().length > 0) {
			$("input[name='"+feeds[i]+"']").next('p').show("slow");
		}
		$("input[name='"+feeds[i]+"']").keyup(function() {
			if ($(this).val().length > 0) { $(this).next('p').show("slow"); };
			if ($(this).val().length == 0) { $(this).next('p').hide("slow"); };
		});
	});
});
