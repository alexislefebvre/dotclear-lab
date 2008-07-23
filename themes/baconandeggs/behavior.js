$(function() {
	$('#wrapper').css('height','auto');
	
	var occupedHeight = $('#top').height() + $('#footer').height();
	var newMainHeight = $('body').height() - occupedHeight;
	
	if(newMainHeight < $('#wrapper').height()) {
		newMainHeight = $('#wrapper').height();
	}
	$('#wrapper').css('height',newMainHeight + 'px');
});