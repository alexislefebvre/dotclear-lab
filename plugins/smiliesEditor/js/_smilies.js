var dragsort = ToolMan.dragsort();
$(function() {
	$('.checkboxes-helpers').each(function() {
		dotclear.checkboxesHelpers(this);
	});
	
	$('#smilepic').change(
		function(){
			$('#smiley-preview').attr('src',dotclear.smilies_base_url+this.value).attr('title',this.value).attr('alt',this.value);
		}
	);
	
	dragsort.makeTableSortable($("#smilies-list").get(0),
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
		var order = document.getElementById('smilies_order');
		group.register('dragend', function() {
			order.value = '';
			items = item.parentNode.getElementsByTagName('tr');
			
			for (var i=0; i<items.length; i++) {
				order.value += items[i].id.substr(2)+',';
			}
		});
	}
};
