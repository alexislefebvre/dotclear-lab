/*
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of dcLog, a plugin for Dotclear.
# 
# Copyright (c) 2010 Tomtom
# http://blog.zenstyle.fr/
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------
*/
$(function() {
	$('.checkboxes-helpers').each(function() {
		dotclear.checkboxesHelpers(this);
	});
	$('input[name="del_logs"]').click(function() {
		return window.confirm(dotclear.msg.confirm_delete_selected_log);
	});
	$('input[name="del_all_logs"]').click(function() {
		return window.confirm(dotclear.msg.confirm_delete_all_log);
	});
});