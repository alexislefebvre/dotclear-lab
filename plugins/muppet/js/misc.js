$(function() {
	var selected = $('#icon option:selected').val();
	$('#icon').css('background-image','url(' + dotclear.icon_base_url + selected +')').change(
		function(){
			//$('#icon-preview').attr('src',dotclear.icon_base_url+this.value).attr('title',this.value).attr('alt',this.value);
			$(this).css('background-image','url('+dotclear.icon_base_url+this.value+')');
		}
	);
	$('#icon option').each(function(){
		var name = $(this).val();
		$(this).css('background-image','url(' + dotclear.icon_base_url + name +')');
	});
});

