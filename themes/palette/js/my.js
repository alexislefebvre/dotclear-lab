$(document).ready(function()
{
	$("#sidebar img").animate({ 
        opacity: 0.4,
      }, 1500 );
	  	
	$("#sidebar img").hover(function(){
		$(this).animate({
			opacity: 1
		}, 500)
	},function(){
		$(this).animate({
			opacity: 0.4
		}, 500)
	});
 
	$("#navigation ul li").hover(function(){
		$(this).animate({
			paddingBottom: "55px"
		}, 300)
	},function(){
		$(this).animate({
			paddingBottom: "6px"
		}, 200)
	});

});
