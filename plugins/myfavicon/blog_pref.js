$(function() {
	var favicon_url = '';
	
	$("#favicon_enable").change(function()
	{
		if (this.checked) {
			$("#favicon_config").show();
			$("#favicon_url").val(favicon_url);
		}
		else {
			favicon_url = $("#favicon_url").val();
			$("#favicon_url").val('');
			$("#favicon_config").hide();
		}
	});
	
	if (!document.getElementById('favicon_enable').checked) {
		$("#favicon_config").hide();
	}
});

