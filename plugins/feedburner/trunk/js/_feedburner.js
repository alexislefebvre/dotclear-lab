/*
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of feedburner, a plugin for Dotclear.
# 
# Copyright (c) 2009 Tomtom
# http://blog.zenstyle.fr/
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------
*/

$(function() {
	feeds = ['rss2','rss2_comments','atom','atom_comments'];
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
