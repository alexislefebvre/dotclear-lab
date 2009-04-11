/*
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of eventdata, a plugin for Dotclear 2.
#
# Copyright (c) 2009 JC Denis and contributors
# jcdenis@gdwd.com
#
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------
*/
$(function() {

		var start_dtPick = new datePickerB($('.eventdata-date-start').get(0)); 
		start_dtPick.img_top = '1.5em'; 
		start_dtPick.draw(); 

		var end_dtPick = new datePickerC($('.eventdata-date-end').get(0)); 
		end_dtPick.img_top = '1.5em'; 
		end_dtPick.draw(); 

		$('#linked-eventdatas').toggleWithLegend($('#linked-eventdatas-form'));
});