// script récupéré sur http://damienalexandre.fr/Info-Bulle-en-Javascript.html
// et modifié par mes soins.
/*
var i=false;
 
function move(e) {
    if(i) {
	    if (navigator.appName!="Microsoft Internet Explorer") {
		$("#curseur").css("left",(e.pageX-5-$("#curseur").width())+"px");
		$("#curseur").css("top",e.pageY + 10+"px");
	} else {
	    if(document.documentElement.clientWidth>0) {
		$("#curseur").css("left",((20+event.x+document.documentElement.scrollLeft-$("#curseur").width())+"px"));
		$("#curseur").css("top",(10+event.y+document.documentElement.scrollTop+"px"));
	    } else {
		$("#curseur").css("left",(20+event.x+document.body.scrollLeft+"px"));
		$("#curseur").css("top",(10+event.y+document.body.scrollTop+"px"));
	    }
	}
    }
}
 
function showinfo(text) {
    if(i==false) {
	$("#curseur").css("visibility","visible");
	$("#curseur").html(text);
	i=true;}
}

function hideinfo() {
    if(i==true) {
	$("#curseur").css("visibility","hidden"); 
	i=false;}
}
document.onmousemove=move;

$("*").attr("onmouseout","hideinfo()");
*/