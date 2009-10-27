$(document).ready(function(){
	$("p.smilies").insertBefore("p.field:eq(3)")
});

function InsertSmiley(textarea, smiley) { 
	smiley = ' '+smiley+' '; 
	textarea = document.getElementById(textarea); 
	textarea.focus(); 
	var start, end, scrollPos; 
	if (typeof(document["selection"]) != 'undefined') {
		document.selection.createRange().text = smiley; textarea.caretPos += smiley.length; 
		} 
	else if (typeof(textarea['setSelectionRange']) != 'undefined') { 
		start = textarea.selectionStart;
		end = textarea.selectionEnd; 
		scrollPos = textarea.scrollTop; 
		textarea.value = textarea.value.substring(0, start)+smiley+textarea.value.substring(end); 
		textarea.setSelectionRange(start + smiley.length, start + smiley.length);
		textarea.scrollTop = scrollPos; 
		} 
}