$(document).ready(function(){
	$("p.field:last").addClass("content");
	//$("p.form-help").text("Les commentaires comprennent la syntaxe Wiki. Utilisez-la à bon escient.");
});


$(function() {
	var link = $('<a href="#" class="add" title="Ajouter un message"><span class="button">' + 'Plop ?' + '</span></a>').click(function() {
		$(this).hide();
		$('#sidebar .litribune fieldset').fadeIn("slow");
		$('#tribnick').focus();
		return false;
	});
	$('#sidebar .litribune ').append(link);
	$('#sidebar .litribune fieldset').hide();
});


$.fn.colorHover = function (animtime,fromColor,toColor) { //link hovers color
	$(this).hover(function () {
		return $(this).css('color',fromColor).stop().animate({'color': toColor},animtime);
		}, function () {
		return $(this).stop().animate({'color': fromColor},animtime);
	});
}


$.fn.backgroundColorHover = function (animtime,fromColor,toColor) { //link hovers color
	$(this).hover(function () {
		return $(this).css('backgroundColor',fromColor).stop().animate({'backgroundColor': toColor},animtime);
		}, function () {
		return $(this).stop().animate({'backgroundColor': fromColor},animtime);
	});
}