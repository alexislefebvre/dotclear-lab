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
	$("#taskId"+id+" .addobjminus").show(0);
	$("#taskId"+id+" .addobjplus").hide(0);
}

function showNewTask(id) {
	$("#taskId"+id).show(0);
	$(".dont_add_task").show(0);
	$(".add_task").hide(0);
}

function showHideAllObj() {
	if($(".taskLine:not(.new):first .minus").css("display")!="inline")
	{
		$(".taskLine:not(.new) .minus").show(0);
		$(".taskLine:not(.new) .plus").hide(0);
		$(".objTable:not(.new)").show(0);
	}
	else {
		$(".taskLine:not(.new) .minus").hide(0);
		$(".taskLine:not(.new) .plus").show(0);
		$(".objTable:not(.new)").hide(0);
	}
}

function showHideAllObjFinished() {
	if($(".objLine:not(.new):has(.finish input[checked])").css("display")=="table-row")
	{
		$(".objLine:not(.new):has(.finish input[checked])").hide(0);
	}
	else {
		$(".objLine:not(.new):has(.finish input[checked])").show(0);
	}
}

function hideNewObj(id) {
	$("#objTable"+id+" .anyobj").show(0);
	$("#objTable"+id+" .new").hide(0);
	$("#taskId"+id+" .addobjminus").hide(0);
	$("#taskId"+id+" .addobjplus").show(0);
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
	$(".dont_add_task").hide(0);
	$(".add_task").show(0);
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
	var fact = 1;
	if (isObj == 0) {
		if (isDesc == 0) {
			nbrChar = $("#taskId"+id+" .taskname").val().length;
			nbrChar = nbrChar / fact;
			$("#taskId"+id+" .taskname").attr("size",nbrChar)
		}
		else if (isDesc == 1) {
			nbrChar = $("#taskId"+id+" .taskdesc").val().length;
			nbrChar = nbrChar / fact;
			$("#taskId"+id+" .taskdesc").attr("size",nbrChar)
		}
	}

	else if (isObj == 1) {
		if (isDesc == 0) {
			nbrChar = $("#objTable"+linked+" #objId"+id+" .objname").val().length;
			nbrChar = nbrChar / fact;
			$("#objTable"+linked+" #objId"+id+" .objname").attr("size",nbrChar)
		}
		else if (isDesc == 1) {
			nbrChar = $("#objTable"+linked+" #objId"+id+" .objdesc").val().length;
			nbrChar = nbrChar / fact;
			$("#objTable"+linked+" #objId"+id+" .objdesc").attr("size",nbrChar)
		}
	}
}


/*
$(window).unload( function () {
    if (modification == "en cours") {
	if (confirm("Some modifications were not saved.\n Do you want to leave anymore?")) {} } 
});
*/