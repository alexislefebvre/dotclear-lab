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

function finish(id,state)
{
	$("#answer").html(loading).fadeIn(1000);
	if (state==1) {
		params = {
			xd_check: dotclear.nonce,
			active_object: id
		};
		state_inverse=0;
		show=obj_reseted;
		check='';
	}
	else if (state==0) {
		params = {
			xd_check: dotclear.nonce,
			finish_object: id
		};
		state_inverse=1;
		show=obj_reached;
		check= 'checked="checked"';
	}
	$.post("plugin.php?p=taskManager",params,function(data){
		$("#answer").html(show).fadeTo(1000,1).fadeTo(1000, 0);
	});
	renew="finish("+id+","+state_inverse+")";
	$("#objId"+id+" .finish").replaceWith('<td onclick="'+renew+'" class="finish action"><input type="checkbox" '+check+'/></td>');
}

function del(id,choice,button_nbr)
{
	$("#answer").html(loading).fadeIn(2000);
	if (choice==1){
		params = {
			xd_check: dotclear.nonce,
			obj_delete: id
		};
		show=obj_deleted;
		typename="#objId"+id;
		typename2="cet objectif ?";
	}
	else if (choice==0) {
		params = {
			xd_check: dotclear.nonce,
			task_delete: id
		};
		show=task_deleted;
		typename="#taskId"+id;
		typename2="cette tâche ?";
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
	$("#answer").html(show).fadeTo(2000,1).fadeTo(2000, 0.00);
}

function saveTask(id,add)
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
		val2 = $("#dont_add_task").val();
		$("#add_task").replaceWith('<input id="add_task" type="button" onclick="showNewTask('+(id+1)+');" value="'+val+'"/>');
		$("#dont_add_task").replaceWith('<input id="dont_add_task" type="button" onclick="hideNewTask('+(id+1)+');" value="'+val2+'"/>');
		$("#add_task").show(0);
		$("#dont_add_task").hide(0);
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
		$("#answer").html(show).fadeTo(1000,1).fadeTo(1000, 0.00);
	});
	$("#taskId"+id+" .taskname").attr("size",$("#taskId"+id+" .taskname").val().length);
	$("#taskId"+id+" .taskname").attr("disabled","disabled");
	$("#taskId"+id+" .taskname").css("border","none");
	$("#taskId"+id+" .taskname").css("color","black");
	$("#taskId"+id+" .taskname").css("background","white");
	
	$("#taskId"+id+" .taskname").attr("size",$("#taskId"+id+" .taskdesc").val().length);
	$("#taskId"+id+" .taskdesc").attr("disabled","disabled");
	$("#taskId"+id+" .taskdesc").css("border","none");
	$("#taskId"+id+" .taskdesc").css("color","black");
	$("#taskId"+id+" .taskdesc").css("background","white");
	
	$("#taskId"+id+" .savetask").fadeTo(0,0.4);
	$("#taskId"+id+" .hidetask").fadeTo(0,0.4);
	$("#taskId"+id+" .showtask").fadeTo(0,1);
	$("#taskId"+id+" .deltask").fadeTo(0,1);
}

function saveObj(id,linked,add)
{
	$("#answer").html(loading).fadeIn(1000);
	if (typeof(add) != "undefined" ) {
		params = {
			xd_check: dotclear.nonce,
			add_obj: 1,
			linked_with : linked,
			obj_newname : $("#objId"+id+" .objname").val(),
			obj_newdesc : $("#objId"+id+" .objdesc").val()
		}
		show = obj_added;
		$("#objId"+id).attr("class","objLine");
		$("#taskId"+linked+" .minus:last").hide(0);
		$("#taskId"+linked+" .plus:last").show(0);
		replace_with=
		$("#objId"+id+" .saveobj").replaceWith('<td onclick="saveObj('+id+','+linked+',0)" class="saveobj action"><img src="index.php?pf=taskManager/img/disk.png" alt="" /></td>');
	}
	else {
		params = {
			xd_check: dotclear.nonce,
			put_obj_id: id,
			obj_newname : $("#objId"+id+" .objname").val(),
			obj_newdesc : $("#objId"+id+" .objdesc").val()
		}
		show = obj_updated;
	}
	
	$.post("plugin.php?p=taskManager",params,function(callback){
			$("#answer").html(show).fadeTo(1000,1).fadeTo(2000, 0);
			if (typeof(add) != "undefined" ) {
				var str=callback;
				var motif=/<tr class="objLine new"((.){0,}\n){0,}(.){0,}tr>/m;
				str = str.match(motif).toString();
				str = str.substr(0,(str.length-16));
				$("#objTable"+linked+" table").append(str);
			}
	});
	$("#objId"+id+" .objname").attr("size",$("#objId"+id+" .objname").val().length);
	$("#objId"+id+" .objname").attr("disabled","disabled");
	$("#objId"+id+" .objname").css("border","none");
	$("#objId"+id+" .objname").css("color","black");
	$("#objId"+id+" .objname").css("background","white")

	$("#objId"+id+" .objdesc").attr("size",$("#objId"+id+" .objdesc").val().length);
	$("#objId"+id+" .objdesc").attr("disabled","disabled");
	$("#objId"+id+" .objdesc").css("border","none");
	$("#objId"+id+" .objdesc").css("color","black");
	$("#objId"+id+" .objdesc").css("background","white");

	$("#objId"+id+" .saveobj").fadeTo(0,0.4);
	$("#objId"+id+" .hideobj").fadeTo(0,0.4);
	$("#objId"+id+" .showobj").fadeTo(0,1);
	$("#objId"+id+" .delobj").fadeTo(0,1);
}


