$(document).ready(function(){
	$(".shortArchives li ul:not(:first)").hide();

	$(".shortArchives li span").each( function () { 
        var txt = $(this).text(); 
        $(this).replaceWith('<a href="" class="archives-year">' + txt + '<\/a>') ; 
    } ) ;     
    
	$(".shortArchives li a.archives-year").click(function(){
		if ($(this).next(".shortArchives li ul:visible").length != 0) {
			$(".shortArchives li ul:visible").slideUp("normal", function () { $(this).parent().removeClass("open") });
		} else {
			$(".shortArchives li ul").slideUp("normal", function () { $(this).parent().removeClass("open") });
			$(this).next("ul").slideDown("normal", function () { $(this).parent().addClass("open") });
		}
		return false;
	});
});
