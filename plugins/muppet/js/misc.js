$(function() {
	$('#icon').change(
		function(){
			$('#icon-preview').attr('src',dotclear.icon_base_url+this.value).attr('title',this.value).attr('alt',this.value);
		}
	);
});

