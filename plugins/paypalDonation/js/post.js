$(function(){
	/* toogle plugin in sidebar */
	$("#paypaldonation-form-title").toggleWithLegend($("#paypaldonation-form-content"),{cookie:"dcx_paypaldonation_admin_form_sidebar"});

	/* hide fields */
	if(!$('#ppd_use').attr('checked')){$('#paypaldonation-form-fields').hide();}
	$('#ppd_use').change(function(){if($(this).attr('checked')){$('#paypaldonation-form-fields').slideDown();}else{$('#paypaldonation-form-fields').slideUp();}});
});