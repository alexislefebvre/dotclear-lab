
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
