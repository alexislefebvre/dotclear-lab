$(function() {
	$('.checkboxes-helpers').each(function() {dotclear.checkboxesHelpers(this);});
	$('#users-requests tr.line').each(function(){dotclear.requestExpander(this);});
	$('#users-requests').submit(function(){var action=$(this).find('select[name="action"]').val();
		var checked=false;$(this).find('input[name="users[]"]').each(function(){
			if(this.checked){checked=true;}});
			if(!checked){return false;}
		return true;
	});
})