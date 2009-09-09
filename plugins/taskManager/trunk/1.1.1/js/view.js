/*
# -- BEGIN LICENSE BLOCK -------------------------
# This file is part of taskManager, a plugin for Dotclear.
#
# Copyright (c) 2009 Louis Cherel
# cherel.louis@gmail.com
#
# Licensed under the GPL version 3.0 license.
# See COPIRIGHT file or
# http://www.gnu.org/licenses/gpl-3.0.txt
# -- END LICENCE BlOC -----------------------------
*/

function showObj(id) {
	$("#taskId"+id+" .minus:first").show(0);
	$("#taskId"+id+" .plus:first").hide(0);
	$("#objTable"+id).show(0);
}

function showNewObj(id) {
	showObj(id);
	$("#objTable"+id+" .new").show(0);
	$("#objTable"+id+" .anyobj").hide(0);
	$("#taskId"+id+" .minus:last").show(0);
	$("#taskId"+id+" .plus:last").hide(0);
}

function showNewTask(id) {
	$("#taskId"+id).show(0);
	$("#dont_add_task").show(0);
	$("#add_task").hide(0);
}

function showAll(id) {
	// à remplir ...
}

function hideNewObj(id) {
	$("#objTable"+id+" .anyobj").show(0);
	$("#objTable"+id+" .new").hide(0);
	$("#taskId"+id+" .minus:last").hide(0);
	$("#taskId"+id+" .plus:last").show(0);
}

function hideObj(id) {
	hideNewObj(id);
	$("#taskId"+id+" .minus:first").hide(0);
	$("#taskId"+id+" .plus:first").show(0);
	$("#objTable"+id).hide(0);
}

function hideNewTask(id) {
	hideNewObj(id);
	$("#taskId"+id).hide(0);
	$("#dont_add_task").hide(0);
	$("#add_task").show(0);
}

function modify(id,choice,linked) {
	if(choice == 1) {
		$("#objTable"+linked+" #objId"+id+" .objname").removeAttr("disabled");
		$("#objTable"+linked+" #objId"+id+" .objdesc").removeAttr("disabled");
		$("#objTable"+linked+" #objId"+id+" .objnamememory").val($("#objTable"+linked+" #objId"+id+" .objname").val());
		$("#objTable"+linked+" #objId"+id+" .objdescmemory").val($("#objTable"+linked+" #objId"+id+" .objdesc").val());
		$("#objTable"+linked+" #objId"+id+" .saveobj").fadeTo(0,1);
		$("#objTable"+linked+" #objId"+id+" .hideobj").fadeTo(0,1);
		$("#objTable"+linked+" #objId"+id+" .showobj").fadeTo(0,0.4);
		$("#objTable"+linked+" #objId"+id+" .delobj").fadeTo(0,0.4);
		
		$("#objTable"+linked+" #objId"+id+" .objname").css("border","");
		$("#objTable"+linked+" #objId"+id+" .objname").css("color","");
		$("#objTable"+linked+" #objId"+id+" .objname").css("background","");
		$("#objTable"+linked+" #objId"+id+" .objdesc").css("border","");
		$("#objTable"+linked+" #objId"+id+" .objdesc").css("color","");
		$("#objTable"+linked+" #objId"+id+" .objdesc").css("background","");
	}
	else if(choice == 0) {
		$("#taskId"+id+" .taskname").removeAttr("disabled");
		$("#taskId"+id+" .taskdesc").removeAttr("disabled");
		$("#taskId"+id+" .tasknamememory").val($("#taskId"+id+" .taskname").val());
		$("#taskId"+id+" .taskdescmemory").val($("#taskId"+id+" .taskdesc").val());
		$("#taskId"+id+" .savetask").fadeTo(0,1);
		$("#taskId"+id+" .hidetask").fadeTo(0,1);
		$("#taskId"+id+" .showtask").fadeTo(0,0.4);
		$("#taskId"+id+" .deltask").fadeTo(0,0.4);

		$("#taskId"+id+" .taskdesc").css("border","");
		$("#taskId"+id+" .taskdesc").css("color","");
		$("#taskId"+id+" .taskdesc").css("background","");
		$("#taskId"+id+" .taskname").css("border","");
		$("#taskId"+id+" .taskname").css("color","");
		$("#taskId"+id+" .taskname").css("background","");
	}
}

function cancel(id,choice,linked)
{
	if(choice == 1) {
		$("#objTable"+linked+" #objId"+id+" .objname").val($("#objTable"+linked+" #objId"+id+" .objnamememory").val());
		$("#objTable"+linked+" #objId"+id+" .objname").attr("size",$("#objTable"+linked+" #objId"+id+" .objname").val().length)
		$("#objTable"+linked+" #objId"+id+" .objname").attr("disabled","disabled");
		$("#objTable"+linked+" #objId"+id+" .objname").css("border","none");
		$("#objTable"+linked+" #objId"+id+" .objname").css("color","black");
		$("#objTable"+linked+" #objId"+id+" .objname").css("background","white");
		
		$("#objTable"+linked+" #objId"+id+" .objdesc").val($("#objTable"+linked+" #objId"+id+" .objdescmemory").val());
		$("#objTable"+linked+" #objId"+id+" .objdesc").attr("size",$("#objTable"+linked+" #objId"+id+" .objdesc").val().length)
		$("#objTable"+linked+" #objId"+id+" .objdesc").attr("disabled","disabled");
		$("#objTable"+linked+" #objId"+id+" .objdesc").css("border","none");
		$("#objTable"+linked+" #objId"+id+" .objdesc").css("color","black");
		$("#objTable"+linked+" #objId"+id+" .objdesc").css("background","white");
		
		$("#objTable"+linked+" #objId"+id+" .saveobj").fadeTo(0,0.4);
		$("#objTable"+linked+" #objId"+id+" .hideobj").fadeTo(0,0.4);
		$("#objTable"+linked+" #objId"+id+" .showobj").fadeTo(0,1);
		$("#objTable"+linked+" #objId"+id+" .delobj").fadeTo(0,1);
	}
	else if(choice == 0) {
		$("#taskId"+id+" .taskname").val($("#taskId"+id+" .tasknamememory").val());
		$("#taskId"+id+" .taskname").attr("size",$("#taskId"+id+" .taskname").val().length)
		$("#taskId"+id+" .taskname").attr("disabled","disabled");
		$("#taskId"+id+" .taskname").css("border","none");
		$("#taskId"+id+" .taskname").css("color","black");
		$("#taskId"+id+" .taskname").css("background","white");
		
		$("#taskId"+id+" .taskdesc").val($("#taskId"+id+" .taskdescmemory").val());
		$("#taskId"+id+" .taskdesc").attr("size",$("#taskId"+id+" .taskdesc").val().length)
		$("#taskId"+id+" .taskdesc").attr("disabled","disabled");
		$("#taskId"+id+" .taskdesc").css("border","none");
		$("#taskId"+id+" .taskdesc").css("color","black");
		$("#taskId"+id+" .taskdesc").css("background","white");
		
		$("#taskId"+id+" .savetask").fadeTo(0,0.4);
		$("#taskId"+id+" .hidetask").fadeTo(0,0.4);
		$("#taskId"+id+" .showtask").fadeTo(0,1);
		$("#taskId"+id+" .deltask").fadeTo(0,1);
	}
}

function modChamp(id,isDesc,isObj,linked) {
	if (isObj == 0) {
		if (isDesc == 0) {
			nbrChar = $("#taskId"+id+" .taskname").val().length;
			$("#taskId"+id+" .taskname").attr("size",nbrChar)
		}
		else if (isDesc == 1) {
			nbrChar = $("#taskId"+id+" .taskdesc").val().length;
			$("#taskId"+id+" .taskdesc").attr("size",nbrChar)
		}
	}

	else if (isObj == 1) {
		if (isDesc == 0) {
			nbrChar = $("#objTable"+linked+" #objId"+id+" .objname").val().length;
			$("#objTable"+linked+" #objId"+id+" .objname").attr("size",nbrChar)
		}
		else if (isDesc == 1) {
			nbrChar = $("#objTable"+linked+" #objId"+id+" .objdesc").val().length;
			$("#objTable"+linked+" #objId"+id+" .objdesc").attr("size",nbrChar)
		}
	}
}

// à partir d'ici:
// script récupéré sur http://damienalexandre.fr/Info-Bulle-en-Javascript.html


var i=false; // La variable i nous dit si la bulle est visible ou non
 
function move(e) {
  if(i) {  // Si la bulle est visible, on calcul en temps reel sa position ideale
    if (navigator.appName!="Microsoft Internet Explorer") { // Si on est pas sous IE
    $("#curseur").css("left",e.pageX + 5+"px");
    $("#curseur").css("top",e.pageY + 10+"px");
    }
    else { // Modif proposé par TeDeum, merci à  lui
    if(document.documentElement.clientWidth>0) {
$("#curseur").css("left",(20+event.x+document.documentElement.scrollLeft+"px"));
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
  $("#curseur").css("visibility","visible"); // Si il est cacher (la verif n'est qu'une securité) on le rend visible.
  $("#curseur").html(text); // on copie notre texte dans l'élément html
  i=true;
  }
}

function hideinfo() {
if(i==true) {
$("#curseur").css("visibility","hidden"); // Si la bulle est visible on la cache
i=false;
}
}
document.onmousemove=move; // dès que la souris bouge, on appelle la fonction move pour mettre à jour la position de la bulle.

$("*").attr("onmouseout","hideinfo()");