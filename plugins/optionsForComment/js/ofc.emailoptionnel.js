/* -- BEGIN LICENSE BLOCK ----------------------------------
 * This file is part of optionsForComment, a plugin for Dotclear 2.
 * 
 * Copyright (c) 2009-2011 JC Denis and contributors
 * jcdenis@gdwd.com
 * 
 * Licensed under the GPL version 2.0 license.
 * A copy of this license is available in LICENSE file or at
 * http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * -- END LICENSE BLOCK ------------------------------------*/

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