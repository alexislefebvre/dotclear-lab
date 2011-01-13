$(function() {
	$('a.add-maps').click(function() {
		var newwindow = window.open(
			$(this).attr('href'),
			'dc_popup',
			'alwaysRaised=yes,dependent=yes,toolbar=yes,height=500,width=760,' +
			'menubar=no,resizable=yes,scrollbars=yes,status=no'
		);
		if (window.focus) {newwindow.focus()}
		return false;
	});
});