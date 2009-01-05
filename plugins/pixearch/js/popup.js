$(function() {
	$('#media-insert-cancel').click(function() {
		window.close();
	});
	
	$('#media-insert-ok').click(
		function()
		{
			var insert_form = $('#pixearchForm').get(0);
			
			if (insert_form == undefined) { return; }
			
			var tb = window.opener.the_toolbar;
			var data = tb.elements.pixearch.data;
			
			for (var i = 0; insert_form.pixearch_uri[i]; i++)
			{
				if (insert_form.pixearch_uri[i].checked)
				{
					data.pixearchUri =
						insert_form.pixearch_uri[i].value;
				}
			}
			
			
			data.pixearchTitle =
				insert_form.pixearch_title.value;
			
			for (var i = 0; insert_form.pixearch_align[i]; i++)
			{
				if (insert_form.pixearch_align[i].checked)
				{
					data.pixearchAlign =
						insert_form.pixearch_align[i].value;
				}
			}
			
			for (var i = 0; insert_form.pixearch_insert[i]; i++)
			{
				if (insert_form.pixearch_insert[i].checked)
				{
					data.pixearchInsert =
						insert_form.pixearch_insert[i].value;
				}
			}
			
			tb.elements.pixearch.fncall[tb.mode].call(tb);
			
			window.close();
		}
	);
});