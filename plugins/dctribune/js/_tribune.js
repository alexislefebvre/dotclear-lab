$(function() {
	var t = $('#tribune-control');
	t.css('display','inline');
	$('#add-message-form').hide();
	t.click(function() {
		$('#add-message-form').show();
		$(this).hide();
		return false;
	});
});