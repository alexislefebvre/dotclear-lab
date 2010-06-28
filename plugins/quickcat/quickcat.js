/*
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of Dotclear 2 "QuickCat" plugin.
#
# Copyright (c) 2010 Bruno Hondelatte, and contributors. 
# Many, many thanks to Olivier Meunier and the Dotclear Team.
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------
*/
var div_cat = $('<div id="change_cat">'
+'<form action="#" id="form_cat" name="form_cat">'
+'<fieldset><legend>'+dotclear.msg.new_category+'</legend>'
+'<p><label>'+dotclear.msg.cat_title+'<input type="text" size="30" name="cat_title" id="cat_title" maxlength="255"  /></label></p>'
+'<p><label>'+dotclear.msg.parent_category+'<select name="parent_cat" id="parent_cat"></select></label></p>'
+'<input name="submit_cat" id="submit_cat" type="submit" value="'+dotclear.msg.create_cat+'" />'
+'</fieldset></form></div>').css({border: 'none', padding: '10px'});
var catModal='';

function onGetCategories(data) {
	$("#wait_cat").remove();
	if ($(data).find('rsp').attr('status') != 'ok') {
		$("#cat_id").removeAttr("disabled");
		alert($(data).find('message').text());
	} else {
		$('#cat_id').replaceWith($(data).find('rsp').text());
		$('<option value="new">'+dotclear.msg.new_category+'</option>').insertAfter('#cat_id option:first');
	}
}
function onCreateCategory(data) {
	if ($(data).find('rsp').attr('status') != 'ok') {
		$("#wait_cat").remove();
		$("#cat_id").removeAttr("disabled");
		alert($(data).find('message').text());
	} else {
		var cat_id = $(data).find('rsp').text();
		$.get('services.php',
		{f:'getCategoriesAsSelect',
		select:cat_id},
		onGetCategories);
	}
}

$(function() {
	$('#form_cat').live('submit',function() {
		$.post('services.php',
			{f:"createCategory",
			cat_title:$('#cat_title',div_cat)[0].value,
			parent_cat:$('#parent_cat',div_cat)[0].value,
			xd_check: dotclear.nonce},
			onCreateCategory
		);
		if (catModal != '')
			catModal.removeOverlay();
		$("#cat_id").attr("disabled","disabled");
		$('<img id="wait_cat" src="index.php?pf=quickcat/progress.gif"/>').insertBefore("#cat_id");
		return false;
	});
	$('#parent_cat',div_cat).append($("#cat_id").html());
	$('<option value="new">'+dotclear.msg.new_category+'</option>').insertAfter('#cat_id option:first');
	$('#cat_id').live("change",function() {
		if ($(this).val()=="new") {
			$(this).val('');
			catModal = new $.modal(div_cat);
		}
	});
});