/* -- BEGIN LICENSE BLOCK ----------------------------------
 * This file is part of kezako, a plugin for Dotclear 2.
 * 
 * Copyright (c) 2010 Franck Paul and contributors
 * carnet.franck.paul@gmail.com
 * 
 * Licensed under the GPL version 2.0 license.
 * A copy of this license is available in LICENSE file or at
 * http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * -- END LICENSE BLOCK ------------------------------------*/

$(function() {
	update_visibility();
	var tbCategory = new jsToolBar(document.getElementById('desc_desc'));
	tbCategory.draw('xhtml');
});
function update_visibility() {
	var x = document.getElementById('type_id').value;
	if (x == 'metadata-tag') {
		document.getElementById('id_ste').style.display = 'none';
		document.getElementById('id_cat').style.display = 'none';
		document.getElementById('id_tag').style.display = 'block';
	} else if (x == 'category-cat') {
		document.getElementById('id_ste').style.display = 'none';
		document.getElementById('id_cat').style.display = 'block';
		document.getElementById('id_tag').style.display = 'none';
	} else if (x == 'somethingelse') {
		document.getElementById('id_ste').style.display = 'block';
		document.getElementById('id_cat').style.display = 'none';
		document.getElementById('id_tag').style.display = 'block';
	}
}