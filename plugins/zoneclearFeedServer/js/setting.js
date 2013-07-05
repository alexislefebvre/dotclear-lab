/* -- BEGIN LICENSE BLOCK ----------------------------------
 *
 * This file is part of zoneclearFeedServer, a plugin for Dotclear 2.
 * 
 * Copyright (c) 2009-2013 Jean-Christian Denis, BG and contributors
 * contact@jcdenis.fr http://jcd.lv
 * 
 * Licensed under the GPL version 2.0 license.
 * A copy of this license is available in LICENSE file or at
 * http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 *
 * -- END LICENSE BLOCK ------------------------------------*/

$(function() {
	var zcfsForm=$('#setting-form');
	if (zcfsForm!=undefined){
		dotclear.jcTools.formFieldsetToMenu(zcfsForm);
	}
});