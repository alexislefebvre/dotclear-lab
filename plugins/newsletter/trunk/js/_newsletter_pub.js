/* -- BEGIN LICENSE BLOCK ----------------------------------
 *
 * This file is part of newsletter, a plugin for Dotclear 2.
 * 
 * Copyright (c) 2009-2013 Benoit de Marne and contributors
 * benoit.de.marne@gmail.com
 * Many thanks to Association Dotclear
 * 
 * Licensed under the GPL version 2.0 license.
 * A copy of this license is available in LICENSE file or at
 * http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 *
 * -- END LICENSE BLOCK ------------------------------------*/

$(document).ready(function(){
	
	function afficher(data){
		$("#message").empty();
		$("#message").html('<img src="index.php?pf=newsletter/progress.gif" alt="'+please_wait+'" />');
		
		if ($(data).find('rsp').attr('status') == 'ok') {
			var item=$(data).find('item');
			var email=$(item).attr('email');
			var result=$(item).attr('result');
			
			$("#nl_form").empty();
			$("#message").empty();
			$("#message").html(email+' <img src="index.php?pf=newsletter/check-on.png" alt="OK" />');
			$("#message").append('<br />'+result);
						
		} else {
			$("#message").empty();
			$("#message").html('<img src="index.php?pf=newsletter/check-off.png" alt="KO" /> '+$(data).find('message').text());
		}
	}
	
	/*
	function afficher(json){
		//$("#message").append(json);
		var json_val = eval('(' + json + ')');
		$("#message").empty();
		$("#message").html('<img src="index.php?pf=newsletter/progress.gif" alt="'+please_wait+'" />');

		//$("#message").append('json.status='+json_val.status);
		if (json_val.status  == 'ok') {
			//$("#message").html('<img src="index.php?pf=newsletter/check-on.png" alt="OK" />');
			$("#nl_form").empty();
			$("#message").empty();
			//$("#message").append(json_val.data[0].email);
			//$("#message").append('<br />option='+json_val.data[0].option+'<br />');
			$("#message").append('<br />'+json_val.data[0].result);
		} else {
			$("#message").html('<img src="index.php?pf=newsletter/check-off.png" alt="KO" /> '+json_val.message);
		}
	}
	*/
	
	$('#nl_form').submit(function(){
		$.post('index.php?newsletterRest', {
			f: 'newsletterSubmitWidget',
			  	email: $('#nl_email').val(),
			  	option: $('#nl_option').val(),
			  	captcha: $('#nl_captcha').val(),
			  	modesend: $('#nl_modesend').val()
		}, afficher
		);
		return false;
	});
	
});

	
	
