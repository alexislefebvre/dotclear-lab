/* -- BEGIN LICENSE BLOCK ----------------------------------
 * This file is part of hornTweeter, a plugin for Dotclear 2.
 * 
 * Copyright (c) 2009-2010 JC Denis and contributors
 * jcdenis@gdwd.com
 * 
 * Licensed under the GPL version 2.0 license.
 * A copy of this license is available in LICENSE file or at
 * http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * -- END LICENSE BLOCK ------------------------------------*/

$(document).ready(horntweeter_init);

function horntweeter_init(){
	horntweeter_state($('#post_status').val());
	$('#post_status').change(function(){horntweeter_state($('#post_status option:selected').val());});
	$('#horntweeter-form-title').toggleWithLegend($('#horntweeter-form-content'),{cookie:'dcx_horntweeter_admin_form_sidebar'});
}

function horntweeter_state(state){
	if (state != 1){
        $('#horntweeter_send').removeAttr("checked").attr("disabled",true);
	}else{
        $('#horntweeter_send').attr("disabled",false);
	}
}