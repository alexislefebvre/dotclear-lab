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

function finish(id,state,linked)
{
	$("#answer").html(loading).fadeIn(1000);
	if (state==1) {
		state_inverse=0;
		show=obj_reseted;
		check='';
	}
	else if (state==0) {
		state_inverse=1;
		show=obj_reached;
		check= 'checked="checked"';
	}
	params = {
		xd_check: dotclear.nonce,
		finish_object: id,
		linked: linked
	};
	$.post("plugin.php?p=taskManager",params,function(data){
		$("#answer").html(show).fadeTo(1000,1).fadeTo(1000, 0);
	});
	renew="finish("+id+","+state_inverse+","+linked+")";
	$("#objTable"+linked+" #objId"+id+" .finish").replaceWith('<td onclick="'+renew+'" class="finish action"><input type="checkbox" '+check+'/></td>');
}

function del(id,choice,linked)
{
    if ((choice==0 && $("#taskId"+id+" .deltask").css("opacity")!="0.4") || (choice==1 && $("#objTable"+linked+" #objId"+id+" .delobj").css("opacity")!="0.4"))
    {
	$("#answer").html(loading).fadeIn(2000);
	if (choice==1){
		params = {
			xd_check: dotclear.nonce,
			obj_delete: id,
			linked : linked
		};
		show=obj_deleted;
		typename="#objTable"+linked+" #objId"+id;
		typename2="cette tâche?";
	}
	else if (choice==0) {
		params = {
			xd_check: dotclear.nonce,
			task_delete: id
		};
		show=task_deleted;
		typename="#taskId"+id;
		typename2="cet objectif? Cela supprimera toutes les tâches associées";
	}
	if(window.confirm("Etes-vous sur de vouloir supprimer "+typename2))
	{
		$.post("plugin.php?p=taskManager",params,function(){
			$(typename).remove();
			if (choice==0){
				$("#objTable"+id).remove();
			}
		})
	}
	else {
		show=cancelled;
	}
	$("#answer").html(show).fadeTo(1000,1).fadeTo(1000, 0);
    }
}

function saveTask(id,add)
{
    if ($("#taskId"+id+" .savetask").css("opacity")!="0.4")
    {
	$("#answer").html(loading).fadeIn(1000);
	if (typeof(add) != "undefined" ) {
		params = {
			xd_check: dotclear.nonce,
			add_task: 1,
			task_newname : $("#taskId"+id+" .taskname").val(),
			task_newdesc : $("#taskId"+id+" .taskdesc").val(),
			id : id
		}
		show = task_added;
		$("#taskId"+id).attr("class","taskLine");
		val = $("#add_task").val();
		$("#add_task").replaceWith('<input id="add_task" type="button" onclick="showNewTask('+(id+1)+');" value="'+val+'"/>');
		$("#add_task").show(0);
		val2 = $("#dont_add_task").val();
		$("#dont_add_task").replaceWith('<input id="dont_add_task" type="button" onclick="hideNewTask('+(id+1)+');" value="'+val2+'"/>');
		$("#dont_add_task").hide(0);
		$("#taskId"+id+" .savetask").replaceWith('<td onclick="saveTask('+id+')" class="savetask action"><img src="index.php?pf=taskManager/img/disk.png" alt="sauver" /></td>');
	}
	else {
		params = {
			xd_check: dotclear.nonce,
			put_task_id: id,
			task_newname : $("#taskId"+id+" .taskname").val(),
			task_newdesc : $("#taskId"+id+" .taskdesc").val()
		}
		show = task_updated;
	}

	$.post("plugin.php?p=taskManager",params,function(callback){
		if (typeof(add) != "undefined" ) {
			var str=callback;
			var motif=/<tr class="taskLine new"((.){0,}\n){0,}(.){0,}tr>/m;
			str = str.match(motif).toString();
			str = str.substr(0,(str.length-16));
			$("#taskManager").append(str);
		}
		$("#answer").html(show).fadeTo(1000,1).fadeTo(1000, 0);
	});
	$("#taskId"+id+" .taskname").attr("size",$("#taskId"+id+" .taskname").val().length);
	$("#taskId"+id+" .taskname").attr("disabled","disabled");
	$("#taskId"+id+" .taskname").css("border","none");
	$("#taskId"+id+" .taskname").css("color","black");
	$("#taskId"+id+" .taskname").css("background","white");
	
	$("#taskId"+id+" .taskdesc").attr("size",$("#taskId"+id+" .taskdesc").val().length);
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

function saveObj(order,linked,add)
{
    $("#answer").html(loading).fadeIn(1000);
    if ($("#taskId"+linked+" .savetask").css("opacity")!="0.4") {
	    $("#answer").html(task_not_saved).fadeTo(1000,1).fadeTo(2000, 0);
    }
    else if ($("#objTable"+linked+" #objId"+order+" .saveobj").css("opacity")!="0.4")
    {
	if (typeof(add) != "undefined" ) {
		params = {
			xd_check: dotclear.nonce,
			add_obj: 1,
			linked_with : linked,
			obj_newname : $("#objTable"+linked+" #objId"+order+" .objname").val(),
			obj_newdesc : $("#objTable"+linked+" #objId"+order+" .objdesc").val(),
			order: order}
		show = obj_added;
		$("#objTable"+linked+" #objId"+order).attr("class","objLine");
		$("#taskId"+linked+" .minus:last").hide(0);
		$("#taskId"+linked+" .plus:last").show(0);
		$("#objTable"+linked+" #objId"+order+" .saveobj").replaceWith('<td onclick="saveObj('+order+','+linked+')" class="saveobj action"><img src="index.php?pf=taskManager/img/disk.png"/></td>');
	}
	else {
		params = {
			xd_check: dotclear.nonce,
			order: order,
			obj_newname : $("#objTable"+linked+" #objId"+order+" .objname").val(),
			obj_newdesc : $("#objTable"+linked+" #objId"+order+" .objdesc").val(),
			linked : linked
		}
		show = obj_updated;
	}
	
	$.post("plugin.php?p=taskManager",params,function(callback){
			$("#answer").html(show).fadeTo(1000,1).fadeTo(1000, 0);
			if (typeof(add) != "undefined" ) {
				var str=callback;
				var motif=/<tr class="objLine new"((.){0,}\n){0,}(.){0,}<\/tr>/m;
				str = str.match(motif).toString();
				str = str.substr(0,(str.length-16));
				$("#objTable"+linked+" table").append(str);
			}
	});
	$("#objTable"+linked+" #objId"+order+" .objname")
		.attr("size",$("#objTable"+linked+" #objId"+order+" .objname").val().length);
	$("#objTable"+linked+" #objId"+order+" .objname").attr("disabled","disabled");
	$("#objTable"+linked+" #objId"+order+" .objname").css("border","none");
	$("#objTable"+linked+" #objId"+order+" .objname").css("color","black");
	$("#objTable"+linked+" #objId"+order+" .objname").css("background","white")

	$("#objTable"+linked+" #objId"+order+" .objdesc")
		.attr("size",$("#objTable"+linked+" #objId"+order+" .objdesc").val().length);
	$("#objTable"+linked+" #objId"+order+" .objdesc").attr("disabled","disabled");
	$("#objTable"+linked+" #objId"+order+" .objdesc").css("border","none");
	$("#objTable"+linked+" #objId"+order+" .objdesc").css("color","black");
	$("#objTable"+linked+" #objId"+order+" .objdesc").css("background","white");

	$("#objTable"+linked+" #objId"+order+" .saveobj").fadeTo(0,0.4);
	$("#objTable"+linked+" #objId"+order+" .hideobj").fadeTo(0,0.4);
	$("#objTable"+linked+" #objId"+order+" .showobj").fadeTo(0,1);
	$("#objTable"+linked+" #objId"+order+" .delobj").fadeTo(0,1);
    }
}

