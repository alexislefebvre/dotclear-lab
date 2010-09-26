$(document).ready(ofcEmailOptionnel);

function ofcEmailOptionnel() {
	$('#c_mail').parent().find('label').text(ofcMsg['email']+' ('+ofcMsg['optional']+') :');
	
	$('#c_mail').change(function(){
		if ($(this).val()=='') {
			$('#c_remember').removeAttr('checked').attr('disabled',true);
			$('#subscribeToComments').removeAttr('checked').attr('disabled',true);
		}
		else {
			$('#c_remember').removeAttr('disabled');
			$('#subscribeToComments').removeAttr('disabled');
		}
	});
}