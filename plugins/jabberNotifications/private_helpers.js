$(function() {
	$('#jn_notify').change(function()
	{
		if ($(this).val() == 'never')
			$('#jn_jabberid').hide('slow');
		else
			$('#jn_jabberid').show('slow');
	});
	
	if ($('#jn_notify').val() == 'never')
		$('#jn_jabberid').hide();
});
