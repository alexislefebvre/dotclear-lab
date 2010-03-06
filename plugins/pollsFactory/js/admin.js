/* -- BEGIN LICENSE BLOCK ----------------------------------
 * This file is part of pollsFactory, a plugin for Dotclear 2.
 * 
 * Copyright (c) 2009-2010 JC Denis and contributors
 * jcdenis@gdwd.com
 * 
 * Licensed under the GPL version 2.0 license.
 * A copy of this license is available in LICENSE file or at
 * http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * -- END LICENSE BLOCK ------------------------------------*/

/* Date pickers */
$(function(){
	var pollstart=document.getElementById('_poll_strdt');
	
	if(pollstart!=undefined){
		var pollstart_dtPick=new datePicker(pollstart);
		pollstart_dtPick.img_top='1.4em';
		pollstart_dtPick.draw();
	}
	var pollend=document.getElementById('_poll_enddt');
	
	if(pollend!=undefined){
		var pollstart_dtPick=new datePicker(pollend);
		pollstart_dtPick.img_top='1.4em';
		pollstart_dtPick.draw();
	}
});

/* List order */
var dragsort = ToolMan.dragsort();
$(function() {
	dragsort.makeTableSortable($("#queries-list").get(0),
	dotclear.sortable.setHandle,dotclear.sortable.saveOrder);

});

dotclear.sortable = {
	setHandle: function(item) {
		var handle = $(item).find('td.handle').get(0);
		while (handle.firstChild) {
			handle.removeChild(handle.firstChild);
		}

		item.toolManDragGroup.setHandle(handle);
		handle.className = handle.className+' handler';
	},

	saveOrder: function(item) {
		var group = item.toolManDragGroup;
		var order = document.getElementById('queries_order_js');
		group.register('dragend', function() {
			order.value = '';
			items = item.parentNode.getElementsByTagName('tr');

			for (var i=0; i<items.length; i++) {
				order.value += items[i].id.substr(2)+',';
			}
		});
	}
};
$(function() {
	$(".list_hideable").hide();
});