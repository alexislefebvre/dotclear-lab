$(function() {
	var t = $('#templator-control');
	t.css('display','inline');
	$('#add-template').hide();
	t.click(function() {
		$('#add-template').show();
		$(this).hide();
		return false;
	});

	$("#filecat").parent().parent().hide();
	
	 $("#filesource").change(function() { 
		var f = $(this).val();
		if (f == 'category') {
			 $("#filename, #filetitle").parent().parent().hide();
			 $("#filecat").parent().parent().show();
		}
		else {
			$("#filename, #filetitle").parent().parent().show();
			 $("#filecat").parent().parent().hide();
		}
	 });

		
});
