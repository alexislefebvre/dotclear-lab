<?php 

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

class View
{
	public function start_page() {
	return '<html>
			<head>
				<title>Task Manager</title>
				'.$this->css() . $this->js().'
			</head>
			<body>';
	}

	 function empecherCache()
	{
		header('Pragma: no-cache');
		header('Expires: 0');
		header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
		header('Cache-Control: no-cache, must-revalidate');
	}

	public function css() {
		return '
	<style type="text/css">
		.savetask,.hidetask,.saveobj,.hideobj { opacity: 0.4; }
		.order {display:none;}
		.minus,.addobjminus,.dont_add_task,.objTable,.new,.tasknamememory,.taskdescmemory,.objnamememory,.objdescmemory { display: none; }
		[disabled="disabled"],[disabled=""]{
			border: none;
			color: black;
			background: white;
		}
		.action { width: 20px;}
		.action img{cursor:pointer;}
		.action[opacity] img{cursor:default;}
		.objTable .new .saveobj,.new .savetask { opacity: 1; }
		.objTable .new .hideobj,.objTable .new .delobj,.objTable .new .showobj { opacity: 0.4; }
		.new .hidetask,.new .deltask,.new .addobj,.new .showtask { opacity: 0.4; }
	.infobulle{
		position: absolute;   
		visibility : hidden;
		border: 1px solid Black;
		padding: 5px;
		font-family: Verdana, Arial;
		font-size: 10px;
		background-color: #FFFFCC;
		z-index: 1000;
	  }
	#help {
		z-index: 1001;
	}
	.tDnD_whileDrag {
		background-color:#EEEEEE;
	}
	.tDnD_whileDrag input {
		background: #EEEEEE;
	}
	</style>';
	}
	
	public function js() {
		return '
		<script type="text/javascript">
		      var loading="<p class=\'message\'>Veuillez patienter...</p>";
		      var task_added="<p class=\'message\'>'.__('Objective has been successfully added').'</p>";
		      var obj_added="<p class=\'message\'>'.__('Task has been successfully added').'</p>";
		      var task_deleted="<p class=\'message\'>'.__('Objective has been successfully deleted').'</p>";
		      var task_updated="<p class=\'message\'>'.__('Objective has been successfully updated').'</p>";
		      var obj_reseted="<p class=\'message\'>'.__('Task has been successfully reseted').'</p>";
		      var obj_deleted="<p class=\'message\'>'.__('Task has been successfully deleted').'</p>";
		      var obj_updated="<p class=\'message\'>'.__('Task has been successfully updated').'</p>";
		      var obj_reached="<p class=\'message\'>' . __('Task has been successfully reached') . '</p>";
		      var cancelled="<p class=\'message\'>' . __('Operation cancelled') . '</p>";
		      var task_not_saved="<p class=\'message\'>' . __('You didn\'t save the objective before saving the task'). '</p>";
		      var delete_task="'.__('this objective?\n\nIt will delete all the associated tasks.').'";
		      var delete_obj="'.__('this task?').'";
		      var delete_="'.__('Are you sure you want to delete ').'";
		      var task_hidden = "<p class=\'message\'>'.__('Objective is now hidden to your visitors').'</p>";
		      var task_visible = "<p class=\'message\'>'.__('Objective is now visible to your visitors').'</p>";
		      var blog_updated = "<p class=\'message\'>'.__('Your blog has been successfuly updated').'</p>";
		      var task_visibility_text = "<p class=\'message\'>'.__('Show/Hide on the blog').'</p>";
		</script>'.dcPage::jsLoad('index.php?pf=taskManager/js/jquery.tablednd_0_5.js').
		dcPage::jsLoad('index.php?pf=taskManager/js/view.js').
		dcPage::jsLoad('index.php?pf=taskManager/js/ajax.js');
	}
	
	public function start_form($taskId) {
		global $core;
		return '<h2>' . html::escapeHTML($core->blog->name) . ' &rsaquo; Task Manager</h2>
		<div id="answer"></div>
		<div id="curseur" class="infobulle"></div>
		<!--<input class="add_task" type="button" onclick="showNewTask('.$taskId.');" value="'.__('Add an objective').'"/>
		<input class="dont_add_task" type="button" onclick="hideNewTask('.$taskId.');" value="'.__('Do not add an objective').'"/>
		<input type="button" onclick="showHideAllObj()" value="'.__('Show/Hide all the tasks').'"/>
		<input type="button" onclick="showHideAllObjFinished()" value="'.__('Show/Hide all the finished tasks').'"/> -->
		<table class="maximal dragable" id="taskManager">
			<tr>
				<th></th>
				<th>'.__('Objectives').'</th>
				<th>'.__('Description').'</th>
				<th colspan="6">'.__('Actions').'</th>
			</tr>';
	}
	
	public function tasks($id,$name,$desc,$taskpublicview) {
		$checked =  $taskpublicview==1 ? 'checked="checked"' : '';
	return '<tr class="taskLine" id="taskId'.$id.'">
	<td class="action" class="showobj">
		<img src="index.php?pf=taskManager/img/plus.png" onclick="showObj('.$id.')" class="plus" alt="'.__('more').'" />
		<img src="index.php?pf=taskManager/img/moins.png" onclick="hideObj('.$id.')" class="minus" alt="'.__('less').'" />
	</td>
	<td class="minimal">
		<input type="text" value="'.$name.'" onkeyup="modChamp('.$id.',0,0)" class="taskname" size="'.strlen($name).'" disabled="disabled"/>
		<input type="text" value="" class="tasknamememory"/>
	</td>
	<td class="maximal">
		<input type="text" value="'.$desc.'" onkeyup="modChamp('.$id.',1,0)" class="taskdesc" size="'.strlen($desc).'" disabled="disabled"/>
		<input type="text" value="" class="taskdescmemory"/>
	</td>
	<td onclick="saveTask('.$id.')" class="savetask action">
		<img src="index.php?pf=taskManager/img/disk.png" title="'.__('save').'" alt="'.__('save').'" /></td>
	<td onclick="cancel('.$id.',0)" class="hidetask action">
		<img src="index.php?pf=taskManager/img/arrow_undo.png" title="'.__('remove modifications').'" alt="'.__('remove modifications').'" /></td>
	<td onclick="modify('.$id.',0)" class="showtask action">
		<img src="index.php?pf=taskManager/img/note_edit.png" title="'.__('modify').'" alt="'.__('modify').'" /></td>
	<td class="addobj action">
		<img onclick="showNewObj('.$id.')" src="index.php?pf=taskManager/img/pencil_add.png" class="addobjplus" title="'.__('add a task').'" alt="'.__('add a task').'"/>
		<img onclick="hideNewObj('.$id.')" src="index.php?pf=taskManager/img/pencil_delete.png" class="addobjminus" title="'.__('Do not add a task').'" alt="'.__('Do not add a task').'" />
	</td>
	<td class="taskvisibility action" onclick="publicShow('.$id.','.$taskpublicview.')">
		<input type="checkbox" '.$checked.' title="'.__('Show/Hide on the blog').'"/>
	</td>
	<td onclick="del('.$id.',0)" class="deltask action">
		<img src="index.php?pf=taskManager/img/bin.png" title="'.__('remove').'" alt="'.__('remove').'" /></td>
	<!-- <td class="handle minimal handler"/> -->
</tr>
<tr id="objTable'.$id.'" class="objTable">
	<td></td>
	<td colspan="9">
		<table id="'.$id.'" >';
	}
	
	public function new_objective($order,$taskId) {
	return '
<tr class="objLine new" id="objId'.$order.'">
	<td onclick="finish('.$order.',0,'.$taskId.')" class="finish action">
		<input title="'.__('mark task as accomplished').'" type="checkbox"/></td>
	<td class="minimal">
		<input type="text" value="'.__('My New Task').'" onkeyup="modChamp('.$order.',0,1,'.$taskId.')" class="objname" size="'.strlen(__('My New Task')).'"/>
		<input type="text" value="" class="objnamememory"/>
	</td>
	<td class="maximal">
		<input type="text" value="'.__('Desc Of My New Task').'" onkeyup="modChamp('.$order.',1,1,'.$taskId.')" class="objdesc" size="'.strlen(__('Desc Of My New Task')).'"/>
		<input type="text" value="" class="objdescmemory"/>
	</td>
	<td onclick="saveObj('.$order.','.$taskId.',1)" class="saveobj action">
		<img src="index.php?pf=taskManager/img/disk.png" title="'.__('save').'" alt="'.__('save').'" /></td>
	<td onclick="cancel('.$order.',1,'.$taskId.')" class="hideobj action">
		<img src="index.php?pf=taskManager/img/arrow_undo.png" title="'.__('remove modifications').'" alt="'.__('remove modifications').'" />
	</td>
	<td onclick="modify('.$order.',1,'.$taskId.')" class="showobj action">
		<img src="index.php?pf=taskManager/img/note_edit.png" title="'.__('modify').'" alt="'.__('modify').'" />
	</td>
	<td onclick="del('.$order.',1,'.$taskId.')" class="delobj action">
		<img src="index.php?pf=taskManager/img/bin.png" title="'.__('delete').'" alt='.__('delete').'" />
	</td>
	<td class="handle minimal handler order" value="'.$order.'" title="'.$order.'"/>
</tr>
		';
	}

	public function first_objective($taskId) {
	return '<tr class="objLine anyobj">
			<td colspan="2">'.__('You don\'t have any task. ').__('To add one, click on the image').' <img src="index.php?pf=taskManager/img/pencil_add.png"/></td>
		</tr>';
	}
	
	public function new_task($taskId) {
	return '<tr class="taskLine new" id="taskId'.$taskId.'">
		<td class="action">
			<img src="index.php?pf=taskManager/img/plus.png" onclick="showObj('.$taskId.')" class="plus" title="'.__('more').'" alt="'.__('more').'" />
			<img src="index.php?pf=taskManager/img/moins.png" onclick="hideObj('.$taskId.')" class="minus" title="'.__('less').'" alt="'.__('less').'"/>
		</td>
		<td class="minimal">
			<input type="text" value="'.__('My New Objective').'" onkeyup="modChamp('.$taskId.',0,0)" class="taskname" size="'.strlen(__('My New Objective')).'"/>
			<input type="text" value="" class="tasknamememory"/>
		</td>
		<td class="maximal">
		    <input type="text" value="'.__('Desc Of My New Objective').'" onkeyup="modChamp('.$taskId.',1,0)" class="taskdesc" size="'.strlen(__('Desc Of My New Objective')).'"/>
		    <input type="text" value="" class="taskdescmemory"/>
		</td>
		<td onclick="saveTask('.$taskId.',1)" class="savetask action">
			<img src="index.php?pf=taskManager/img/disk.png" title="'.__('save').'" alt="'.__('save').'" /></td>
		<td onclick="cancel('.$taskId.',0)" class="hidetask action">
			<img src="index.php?pf=taskManager/img/arrow_undo.png" title="'.__('remove modifications').'" alt="'.__('remove modifications').'" /></td>
		<td onclick="modify('.$taskId.',0)" class="showtask action">
			<img src="index.php?pf=taskManager/img/note_edit.png" title="'.__('modify').'" alt="'.__('modify').'" /></td>
		<td class="addobj action">
			<img onclick="showNewObj('.$taskId.')" src="index.php?pf=taskManager/img/pencil_add.png" class="addobjplus" title="'.__('add a task').'" alt="'.__('add a task').'" />
			<img onclick="hideNewObj('.$taskId.')" src="index.php?pf=taskManager/img/pencil_delete.png" class="addobjminus" title="'.__('do not add a task').'" alt="'.__('do not add a task').'" />
		</td>
	<td class="taskvisibility action" onclick="publicShow('.$taskId.',1)">
		<input type="checkbox" '.$checked.' title="'.__('Show/Hide on the blog').'"/>
	</td>
		<td onclick="del('.$taskId.',0)" class="deltask action">
			<img src="index.php?pf=taskManager/img/bin.png" title="'.__('delete').'" alt="'.__('delete').'" /></td>
	<!-- <td class="handle minimal handler"/> -->
	</tr>
	<tr id="objTable'.$taskId.'" class="objTable new">
		<td></td>
		<td colspan="9">
			<table id="'.$taskId.'" class="maximal dragable">
			    '. View::first_objective($taskId) .View::new_objective(0,$taskId);
	}
	
	public function objectives($order,$name,$desc,$finished,$link) {
		$checked =  $finished==1 ? 'checked="checked"' : '';
		
	return '<tr class="objLine" id="objId'.$order.'">
		<td onclick="finish('.$order.','.$finished.','.$link.')" class="finish action">
			<input title="'.__('mark task as accomplished').'" type="checkbox" '.$checked.' /></td>
		<td class="minimal">
			<input type="text" value="'.$name.'" onkeyup="modChamp('.$order.',0,1,'.$link.')" class="objname" size="'.strlen($name).'" disabled="disabled"/>
			<input type="text" value="" class="objnamememory"/>
		</td>
		<td class="maximal">
			<input type="text" value="'.$desc.'" onkeyup="modChamp('.$order.',1,1,'.$link.')" class="objdesc" size="'.strlen($desc).'" disabled="disabled"/>
			<input type="text" value="" class="objdescmemory"/>
		</td>
		<td onclick="saveObj('.$order.','.$link.')" class="saveobj action">
			<img src="index.php?pf=taskManager/img/disk.png" title="'.__('save').'" alt="'.__('save').'" /></td>
		<td onclick="cancel('.$order.',1,'.$link.')" class="hideobj action">
			<img src="index.php?pf=taskManager/img/arrow_undo.png" title="'.__('remove modifications').'" alt="'.__('remove modifications').'" /></td>
		<td onclick="modify('.$order.',1,'.$link.')" class="showobj action">
			<img src="index.php?pf=taskManager/img/note_edit.png" title="'.__('modify').'" alt="'.__('modify').'" /></td>
		<td onclick="del('.$order.',1,'.$link.')" class="delobj action">
			<img src="index.php?pf=taskManager/img/bin.png" title="'.__('delete').'" alt="'.__('delete').'" /></td>
		<td class="handle minimal handler" value="'.$order.'" title="'.$order.'"/>
		<input type="text" value="'.$order.'" title="'.$order.'" class="order" size="2"/>
</tr>';
	}
	
	public function end_objectives() {
		return '		</table>
				</td><td>
				</td>
			</tr>';
	}
	
	public function last_task($taskId) {
		return 	$this->new_task($taskId) . $this->end_objectives().
	'
	</table>
	<input class="add_task" type="button" onclick="showNewTask('.$taskId.');" value="'.__('Add an objective').'"/>
	<input class="dont_add_task" type="button" onclick="hideNewTask('.$taskId.');" value="'.__('Do not add an objective').'"/>
	<input type="button" onclick="showHideAllObj()" style="float:right;" value="'.__('Show/Hide all the tasks').'"/> <br /><br />
	<input type="button" onclick="refreshBlog();" value="'.__('Update public view').'"/>
	<input type="button" onclick="showHideAllObjFinished()" style="float:right;" value="'.__('Show/Hide all the finished tasks').'"/>
	'.dcPage::helpBlock('taskManager');
	}

	public function end_page() {
		return '</body></html>';
	}
	
	public function show() {
		global $core;
		$tM = new DcTaskManager($core->blog);

		$nbrTasks = $tM->getNbrTasks();
		$taskName = $tM->getTaskName();
		$taskDesc = $tM->getTaskDesc();
		$taskId   = $tM->getTaskId();
		$taskNextId = $tM->getTaskNextId();
		$taskPublicView = $tM->getTaskPublicView();
		
		$nbrObj   = $tM->getNbrObj();
		$objName  = $tM->getObjName();
		$objDesc  = $tM->getObjDesc();
		$objId    = $tM->getObjId();
		$objOrder    = $tM->getObjOrder();
		$objFinished = $tM->getObjFinished();
		
		$toReturn = $this->start_form($taskNextId);
		
		for($i=0;$i<$nbrTasks;$i++) {
			$toReturn .= $this->tasks($taskId[$i],$taskName[$i],$taskDesc[$i],$taskPublicView[$i]);
			for($y=0;$y<$nbrObj[$i];$y++) {
				$toReturn .= $this->objectives($objOrder[$i][$y],$objName[$i][$y],$objDesc[$i][$y],$objFinished[$i][$y],$taskId[$i]);
			}
			if ($nbrObj[$i] == 0) {
				$toReturn .= $this->first_objective(($nbrObj[$i]+1),$taskId[$i]);
			}
			$objNextId = isset($objOrder[$i][$nbrObj[$i]-1]) ? $objOrder[$i][$nbrObj[$i]-1]+1:0;
			$toReturn .= $this->new_objective($objNextId,$taskId[$i]) . $this->end_objectives(); 
		}
 		$toReturn .= $this->last_task($taskNextId);
		return $toReturn; 
	}
}

?>