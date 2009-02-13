$(function() {
	$('#media-insert-cancel').click(function() {
		window.close();
	});
	
	$('#media-insert-ok').click(function() {
		sendClose();
		window.close();
	});
	
	function sendClose() {
		var insert_form = $('#media-insert-form').get(0);
		if (insert_form == undefined) { return; }
		
		var tb = window.opener.the_toolbar;
		var data = tb.elements.dcflickr.data;
		
		data.dcflickrTitle = insert_form.dcflickrTitle.value;
		data.dcflickrAlignment = $(insert_form).find('input[@name="dcflickrAlignment"]').radioVal();
		data.dcflickrImg = $(insert_form).find('input[@name="dcflickrImg"]').radioVal();
		data.dcflickrHref = $(insert_form).find('input[@name="dcflickrHref"]').radioVal();
		data.dcflickrPhotopage = insert_form.dcflickrPhotopage.value;
		
		tb.elements.dcflickr.fncall[tb.mode].call(tb);
	};
	
	//Code fourni par Alain Vagner. Merci pour le coup de main.
	var insert_form = $('#media-insert-form').get(0);
	if (insert_form != undefined) 
	{
		if ($(insert_form).find('input[@name="dcflickrFastInsert"]')[0] &&
        $(insert_form).find('input[@name="dcflickrFastInsert"]')[0].value == "true") 
    {
			sendClose();
			window.close();	
		}
	}	
});