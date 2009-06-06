$(document).ready(function(){
	$(".shortArchives li ul:not(:first)").hide();
	$(".shortArchives li a.archives-year").click(function(){
		$(".shortArchives li ul:visible").slideUp("slow");
		$(this).next().slideDown("slow");
		return false;
	});
});
