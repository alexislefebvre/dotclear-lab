$(function() {
	var t = $('#muppet-control');
	t.css('display','inline');
	$('#add-post-type').hide();
	t.click(function() {
		$('#add-post-type').show();
		$(this).hide();
		return false;
	});
});
