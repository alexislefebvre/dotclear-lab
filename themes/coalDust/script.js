$(function() {
	var blognav = $('#blognav').addClass('sidebar');
	var blogextra = $('#blogextra').addClass('sidebar');
	
	// Move navigation in #top
	$('#wrapper').before($('#topnav'));
	
	// blognav on top
	$('#wrapper').prepend(blognav);
	var m = createMenu(blognav);
	if (m) { blognav.before(m); }
	
	// blogextra out from sidebar
	$('#sidebar').before(blogextra).remove();
	m = createMenu(blogextra);
	if (m) { blogextra.before(m); }
	
	// Show search if filed
	if ($('#search #q').val()) {
		$('#search').show();
	}
	
	function createMenu(bl) {
		if ($('div',bl).length == 0) {
			return false;
		}
		var l = $(document.createElement('ul'));
		l.addClass('sidebarmenu');
		
		$('div',bl).each(function() {
			$('>h2',this).hide();
			$(this).hide();
			
			var li = $(document.createElement('li'));
			var target = this;
			
			var a = $(document.createElement('a'));
			a.attr('href','#');
			a.append($('>h2',this).text());
			a.click(function() {
				return toggleMenu(target,bl);
			});
			li.append(a);
			l.append(li);
		});
		return l;
	};
	
	function toggleMenu(target,bl) {
		$('>div',bl).not(target).hide(100);
		$(target).toggle(100);
		return false;
	};
	
	return;
});
