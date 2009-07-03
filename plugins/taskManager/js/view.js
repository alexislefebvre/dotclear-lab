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

function showNewTask(id) {
	$("#taskId"+id).show(0);
	$("#dont_add_task").show(0);
	$("#add_task").hide(0);
}

function hideNewTask(id) {
	hideNewObj(id);
	$("#taskId"+id).hide(0);
	$("#dont_add_task").hide(0);
	$("#add_task").show(0);
}

function modify(id,choice) {
	if(choice == 1) {
		$("#objId"+id+" .objname").removeAttr("disabled");
		$("#objId"+id+" .objdesc").removeAttr("disabled");
		$("#objId"+id+" .saveobj").fadeTo(0,1);
		$("#objId"+id+" .hideobj").fadeTo(0,1);
		$("#objId"+id+" .showobj").fadeTo(0,0.4);
		$("#objId"+id+" .delobj").fadeTo(0,1);
		
		$("#objId"+id+" .objname").css("border","");
		$("#objId"+id+" .objname").css("color","");
		$("#objId"+id+" .objname").css("background","");
		$("#objId"+id+" .objdesc").css("border","");
		$("#objId"+id+" .objdesc").css("color","");
		$("#objId"+id+" .objdesc").css("background","");
	}
	else if(choice == 0) {
		$("#taskId"+id+" .taskname").removeAttr("disabled");
		$("#taskId"+id+" .taskdesc").removeAttr("disabled");
		$("#taskId"+id+" .savetask").fadeTo(0,1);
		$("#taskId"+id+" .hidetask").fadeTo(0,1);
		$("#taskId"+id+" .showtask").fadeTo(0,0.4);
		$("#taskId"+id+" .deltask").fadeTo(0,1);

		$("#taskId"+id+" .taskdesc").css("border","");
		$("#taskId"+id+" .taskdesc").css("color","");
		$("#taskId"+id+" .taskdesc").css("background","");
		$("#taskId"+id+" .taskname").css("border","");
		$("#taskId"+id+" .taskname").css("color","");
		$("#taskId"+id+" .taskname").css("background","");
	}
}

function cancel(id,choice)
{
	if(choice == 1) {
		$("#objId"+id+" .objname").attr("disabled","disabled");
		$("#objId"+id+" .objname").css("border","none");
		$("#objId"+id+" .objname").css("color","black");
		$("#objId"+id+" .objname").css("background","white");
		
		$("#objId"+id+" .objdesc").attr("disabled","disabled");
		$("#objId"+id+" .objdesc").css("border","none");
		$("#objId"+id+" .objdesc").css("color","black");
		$("#objId"+id+" .objdesc").css("background","white");
		
		$("#objId"+id+" .saveobj").fadeTo(0,0.4);
		$("#objId"+id+" .hideobj").fadeTo(0,0.4);
		$("#objId"+id+" .showobj").fadeTo(0,1);
		$("#objId"+id+" .delobj").fadeTo(0,1);
	}
	else if(choice == 0) {
		$("#taskId"+id+" .taskname").attr("disabled","disabled");
		$("#taskId"+id+" .taskname").css("border","none");
		$("#taskId"+id+" .taskname").css("color","black");
		$("#taskId"+id+" .taskname").css("background","white");
		
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