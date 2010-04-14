/* -- BEGIN LICENSE BLOCK ----------------------------------
 * This file is part of postExpired, a plugin for Dotclear 2.
 * 
 * Copyright (c) 2009-2010 JC Denis and contributors
 * jcdenis@gdwd.com
 * 
 * Licensed under the GPL version 2.0 license.
 * A copy of this license is available in LICENSE file or at
 * http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * -- END LICENSE BLOCK ------------------------------------*/

$(function(){
	var post_pe_field=document.getElementById('post_expired_date');
	if(post_pe_field!=undefined){
		var post_pe_dtPick=new datePicker(post_pe_field);
		post_pe_dtPick.img_top='1.5em';
		post_pe_dtPick.draw();
	}
	var act_pe_field=document.getElementById('new_post_expired_date');
	if(act_pe_field!=undefined){
		var act_pe_dtPick=new datePicker(act_pe_field);
		act_pe_dtPick.img_top='1.5em';
		act_pe_dtPick.draw();
	}
	$('#postexpired-form-title').toggleWithLegend($('#postexpired-form-content'),{cookie:'dcx_postexpired_admin_form_sidebar'});
});