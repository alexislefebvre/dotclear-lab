$(function() {
	var favicon_url = '';
	
	// favicon_enable checkbox
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
	
	// favicon_ie6 checkbox
	$("#favicon_ie6").change(function()
	{
		if (this.checked) {
			$("#favicon_warn").show();
		}
		else {
			$("#favicon_warn").hide();
		}
	});
	
	if (!document.getElementById('favicon_ie6').checked) {
		$("#favicon_warn").hide();
	}
});

