
$(function() {
	$('.checkboxes-helpers').each(function() {
		dotclear.checkboxesHelpers(this);
		var a=document.createElement('a');
		a.href="#";
		$(a).append(document.createTextNode(text_show_hide_thumbnails));
		a.onclick = function () {
			$(".img-hideable").toggle();
		}
		$(this).append(document.createTextNode(' - '));
		$(this).append(a);
	});
	dotclear.postsActionsHelper();
	$(".img-hideable").hide();
});

/*gallery.prototype = {
		text_show_hide_thumbnails: 'Show / Hide thumbnails'
}*/
