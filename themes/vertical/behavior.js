$(function() {
	if ($('#content').height() < 925) {
		$('#content').css('height','925px');
	}
	
	var divs = $('#blognav>div');
	divs.each(function(i) {
		evenOdd(this,divs,i);
	});
	
	divs = $('#blogextra>div');
	divs.each(function(i) {
		evenOdd(this,divs,i);
	});
	
	function evenOdd(e,divs,i) {
		if(i == 0) {
			if(i%2 != 1)
				$(e).addClass('first-odd');
			else
				$(e).addClass('first');
		} else if(i == divs.length - 1) {
			if(i%2 != 1)
				$(e).addClass('last-odd');
			else
				$(e).addClass('last');
		} else {
			if(i%2 != 1)
				$(e).addClass('odd');
		}
	};
});