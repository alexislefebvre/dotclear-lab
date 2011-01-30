/* -- BEGIN LICENSE BLOCK ----------------------------------
 * This file is part of soCialMe, a plugin for Dotclear 2.
 * 
 * Copyright (c) 2009-2011 JC Denis and contributors
 * jcdenis@gdwd.com
 * 
 * Licensed under the GPL version 2.0 license.
 * A copy of this license is available in LICENSE file or at
 * http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * -- END LICENSE BLOCK ------------------------------------*/

$(document).ready(socialwriter_init);

function socialwriter_init(){
	//socialwriter_state($('#post_status').val());
	//$('#post_status').change(function(){socialwriter_state($('#post_status option:selected').val());});
	$('#socialwriter-form-title').toggleWithLegend($('#socialwriter-form-content'),{cookie:'dcx_socialwriter_admin_form_sidebar'});
}

function socialwriter_state(state){
	if (state != 1){
        $('#socialwriter_send').removeAttr("checked").attr("disabled",true);
	}else{
        $('#socialwriter_send').attr("disabled",false);
	}
}