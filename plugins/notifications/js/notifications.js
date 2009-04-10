/*
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of notifications, a plugin for Dotclear.
# 
# Copyright (c) 2009 Tomtom
# http://blog.zesntyle.fr/
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------
*/
$(document).ready(function() {
	getNotifications();
});

/**
 * Get a new random comment in database
 */
function getNotifications()
{
	$.get('services.php',{f:'getNotifications'},function(data) {
		var rsp = $(data).children('rsp')[0];
		
		if (rsp.attributes[0].value == 'ok') {
			$(rsp).find('notification').each(function() {
				var options = {
					theme: this.attributes[1].value,
					header: this.attributes[2].value,
					position: this.attributes[4].value,
					life: parseInt(this.attributes[5].value),
					sticky: this.attributes[6].value === 'true'
				};
				$.jGrowl(this.attributes[3].value,options);
			});
		};
		setTimeout('getNotifications()',notifications_ttl);
	});
}