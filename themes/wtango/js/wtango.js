$(document).ready(function(){
            $('body .post-excerpt a,body .post-content a, #attachments a,#comments a, #pings a').colorHover(500,'#4e9a06','#75507b'); 
            $('pre').colorHover (600,'#3465a4','#2e3436');
            $('#sidebar a').colorHover(500,'#2e3436','#75507b'); 



		$("#search h2").hide();
		$("#search form").show();
		$("#sidebar h2").click(function(){
			var target = $(this).next('#sidebar ul')
			$("#sidebar ul:visible").not(target).slideUp();
			target.slideToggle();
			$("#footer").blur();
     		});

		$("#search input").hide();
		$("#search p").prepend("<span title=\"Recherche\">&nbsp;</span>");
		$("#search span").hover((function () {
			$(this).css("cursor","pointer")
			           // .animate( { backgroundColor:'#CCFF37' }, 1500)
                 }),(function () {
			//$(this).animate( { backgroundColor:'#DDD' }, 1500)
                 }));
		$("#search span").toggle(function(){
			$("#search input").fadeIn("8000");$(this).show();
     		},function(){
			$("#search input").fadeOut("8000");$(this).show();
                 });
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