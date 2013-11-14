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

	function recharger(data){
		if ($(data).find('rsp').attr('status') == 'ok') {
			var item=$(data).find('item');
			var src=$(item).attr('src');
			var checkid=$(item).attr('checkid');
		
			$("#nl_captcha_img").attr('src',src);
			$("#nl_checkid").attr('value',checkid);
			$("#nl_captcha_imgname").attr('src',src);

			$.post('index.php?newsletterRest', {
				f: 'newsletterDeleteImgCaptcha',
				captcha_imgname: src		  	
			}, deleteImgCaptcha	
			);
		} else {
			$("#newsletter-pub-message").empty();
			$("#newsletter-pub-message").html('<img src="index.php?pf=newsletter/check-off.png" alt="KO" /> '+'unable to reload');
		}		
	}
	
	$('#nl_reload_captcha').live("click", function() {
		$.post('index.php?newsletterRest', {
			f: 'newsletterReloadCaptcha'
		}, recharger);
		return false;		
	});
	
	function afficher(data){
		$("#newsletter-pub-message").empty();
		$("#newsletter-pub-message").html('<img src="index.php?pf=newsletter/progress.gif" alt="'+please_wait+'" />');
		
		if ($(data).find('rsp').attr('status') == 'ok') {
			var item=$(data).find('item');
			var email=$(item).attr('email');
			var result=$(item).attr('result');
			
			$("#nl_form").empty();
			$("#newsletter-pub-message").empty();
			$("#newsletter-pub-message").html(email+' <img src="index.php?pf=newsletter/check-on.png" alt="OK" />');
			$("#newsletter-pub-message").append('<br />'+result);
						
		} else {
			$("#newsletter-pub-message").empty();
			$("#newsletter-pub-message").html('<img src="index.php?pf=newsletter/check-off.png" alt="KO" /> '+$(data).find('message').text());
		}
	}
	
	$('#nl_form').submit(function(){
		params = {
				f: 'newsletterSubmitFormSubscribe',
			  	email: $('#nl_email').val(),
			  	option: $('#nl_option').val(),
			  	captcha: $('#nl_captcha').val(),
			  	checkid: $('#nl_checkid').val(),
			  	modesend: $('#nl_modesend').val(),
			  	captcha_imgname: $('#nl_captcha_imgname').val()		
		}
		$.post('index.php?newsletterRest', params, afficher);
		return false;
	});

	function deleteImgCaptcha(data){
		if ($(data).find('rsp').attr('status') == 'ok') {
			var item=$(data).find('item');
			var result=$(item).attr('result');
			//$("#newsletter-pub-message").html(result+' <img src="index.php?pf=newsletter/check-on.png" alt="OK" />');
		} else {
			$("#newsletter-pub-message").empty();
			//$("#newsletter-pub-message").html('<img src="index.php?pf=newsletter/check-off.png" alt="KO" /> '+'unable to delete temporary file');
		}		
	}		

	$(window).load(function(){
		$.post('index.php?newsletterRest', {
			f: 'newsletterDeleteImgCaptcha',
			captcha_imgname: $('#nl_captcha_imgname').val()		  	
		}, deleteImgCaptcha
		);
		return false;
	});

});
