jQuery.fn.itemCheck = function() {
	this.find("input[type=checkbox]").check();
	this.addClass("checked");
};
jQuery.fn.itemUncheck = function() {
	this.find("input[type=checkbox]").check().toggleCheck();
	this.removeClass("checked");
}
jQuery.fn.itemToggleCheck = function() {
	this.find("input[type=checkbox]").toggleCheck();
	this.toggleClass("checked");
}



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
	
	$(".grid-item input[type=checkbox]").hide();
	$(".grid-item").each(function() {
		var loc=$(this).find("a").attr('href');
		$(this).find(".info").append($('<a href="'+loc+'" alt="edit"><img src="images/edit-mini.png" title="edit"/></a>'));
	});
	$(".grid-item a.selectable").click(function (event) {
		event.preventDefault();
		/*var parent=$(this).parent(".grid-item");
		$(parent).find("input[type=checkbox]").toggleCheck();
		$(parent).toggleClass("checked");*/
		$(this).parent(".grid-item").itemToggleCheck();
		return false;
	});
	
	$(".grid-item").dblclick(function (event) {
		var loc=$(this).find("a").attr('href');
		$(location).attr('href',loc);
	});
	
	$("a.sel_all").click(function(event) {
		$(".grid-item").itemCheck();
	});
	$("a.sel_none").click(function(event) {
		$(".grid-item").itemUncheck();
	});
	$("a.sel_invert").click(function(event) {
		$(".grid-item").itemToggleCheck();	
	});
	var imgContainer = $("#all-items");
	var firstItem = $(".grid-item:first");
	var nbDisplayedItems = $(".grid-item").length;
	
	if (nbDisplayedItems > 2)
		return;

	var itemsPerLine = parseInt(imgContainer.innerWidth()/(firstItem.next().offset().left - firstItem.offset().left));
	var nbitems = 5*itemsPerLine;
	var currentUrl = document.location.toString();

	if (nbitems == nbDisplayedItems)
		return;
	if (!/forcenb=/.test(currentUrl)) {
		$(location).attr('href',currentUrl+"&forcenb="+nbitems);
	}

	

});
