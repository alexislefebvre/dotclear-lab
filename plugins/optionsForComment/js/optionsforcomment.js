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

$(document).ready(ofcInit);

function ofcInit() {
	$('#c_name').change(function(){});
}

function ofcSetState(k,v) {

	if (k=='name'){
		if (v) {
			$('#c_name').parent().show();
		}
		else {
			$('#c_name').parent().hide();
		}
	}
}