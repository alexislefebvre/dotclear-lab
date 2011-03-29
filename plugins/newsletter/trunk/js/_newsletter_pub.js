/* -- BEGIN LICENSE BLOCK ----------------------------------
 * This file is part of Newsletter, a plugin for Dotclear.
 * 
 * Copyright (c) 2011 Benoit de Marne and contributors.
 * benoit.de.marne@gmail.com
 *  
 * Licensed under the GPL version 2.0 license.
 * A copy of this license is available in LICENSE file or at
 * http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * -- END LICENSE BLOCK ------------------------------------*/

$(function(){

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
			$("#message").append(json_val.data[0].email);
			//$("#message").append('<br />option='+json_val.data[0].option+'<br />');
			$("#message").append('<br />'+json_val.data[0].result);
		} else {
			$("#message").html('<img src="index.php?pf=newsletter/check-off.png" alt="KO" /> '+json_val.message);
		}
	}
	
	$('#nl_form').submit(function(){
		$.post('index.php?rest/newsletter', {
			  f: 'submitWidget',
			  	email: $('#nl_email').val(),
			  	option: $('#nl_option').val(),
			  	captcha: $('#nl_captcha').val(),
			  	modesend: $('#nl_modesend').val()
		}, afficher
		);
		return false;
	});
	
});

	
	
