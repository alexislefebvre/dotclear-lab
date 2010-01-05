/*
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of dcOpenSearch, a plugin for Dotclear.
# 
# Copyright (c) 2009 Tomtom
# http://blog.zenstyle.fr/
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------
*/

var dragsort = ToolMan.dragsort();
$(function() {
	$("#engines-list").each(function() {
		dragsort.makeTableSortable(this,dotclear.sortable.setHandle, dotclear.sortable.saveOrder);
	});
});

dotclear.sortable = {
	setHandle: function(item) {
		var handle = $(item).find('td.handle').get(0);
		while (handle.firstChild) {
			handle.removeChild(handle.firstChild);
		}
		
		item.toolManDragGroup.setHandle(handle);
		$(handle).addClass('handler');
	},
	
	saveOrder: function(item) {
		var group = item.toolManDragGroup;
		var order = $('#engines_order').get(0);
		group.register('dragend', function() {
			order.value = '';
			items = item.parentNode.getElementsByTagName('tr');
			
			for (var i=0; i<items.length; i++) {
				order.value += items[i].id.substr(2)+',';
			}
		});
	}
};