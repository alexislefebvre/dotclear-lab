$(function() {
	var favicon_url = '';
	var favicon_ie_url = '';
	
	// favicon_enable checkbox
	$("#favicon_enable").change(function()
	{
		if (this.checked) {
			$("#favicon_config").show();
			$("#favicon_url").val(favicon_url);
			$("#favicon_ie_url").val(favicon_ie_url);
		}
		else {
			favicon_url = $("#favicon_url").val();
			favicon_ie_url = $("#favicon_url").val();
			$("#favicon_url").val('');
			$("#favicon_ie_url").val('');
			$("#favicon_config").hide();
		}
	});
	
	if (!document.getElementById('favicon_enable').checked) {
		$("#favicon_config").hide();
	}
});