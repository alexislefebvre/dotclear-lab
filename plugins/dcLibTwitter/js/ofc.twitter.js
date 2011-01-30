/* -- BEGIN LICENSE BLOCK ----------------------------------
 * This file is part of dcLibTwitter, a plugin for Dotclear 2.
 * 
 * Copyright (c) 2009-2011 JC Denis and contributors
 * jcdenis@gdwd.com
 * 
 * Licensed under the GPL version 2.0 license.
 * A copy of this license is available in LICENSE file or at
 * http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * -- END LICENSE BLOCK ------------------------------------*/

$(document).ready(ofcTwitter);

function ofcTwitter() {
	
	if (ofcTwitter_access=='1') {
		$('#c_anonymous').parent().hide();
		$('#c_name').parent().hide();
		$('#c_mail').parent().hide();
		$('#c_site').parent().hide();
		$('#c_remember').parent().hide();
		$('#subscribeToComments').removeAttr('checked').attr('disabled',true);
	}
}