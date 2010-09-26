$(document).ready(ofcTwitterLogin);

function ofcTwitterLogin() {
	
	if (ofcTwitterLogin_has_access==1) {
		$('#c_anonymous').parent().hide();
		$('#c_name').parent().hide();
		$('#c_mail').parent().hide();
		$('#c_site').parent().hide();
		$('#c_remember').parent().hide();
		$('#subscribeToComments').removeAttr('checked').attr('disabled',true);
	}
}