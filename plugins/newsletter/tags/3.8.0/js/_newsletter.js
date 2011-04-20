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

$(function() {
	$('.checkboxes-helpers').each(function() {
		dotclear.checkboxesHelpers(this);
	});
	dotclear.postsActionsHelper();

	$('#subscribers_list').submit(function(){
		var action=$(this).find('select[name="op"]').val();
		if(action=='remove'){
			return window.confirm(dotclear.msg.confirm_delete_subscribers);
		}
		return true;
	});

	$('#erasingnewsletter').submit(function() {
		return window.confirm(dotclear.msg.confirm_erasing_datas);
	});	

	$('#import').submit(function() {
		return window.confirm(dotclear.msg.confirm_import_backup);
	});	

	$('#letters_list').submit(function(){
		var action=$(this).find('select[name="action"]').val();
		if(action=='delete'){
			return window.confirm(dotclear.msg.confirm_delete_letters);
		}
		return true;
	});

});
