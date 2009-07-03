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

	public function css() {
		return '
	<style type="text/css">
		.savetask,.hidetask,.saveobj,.hideobj { opacity: 0.4; }
		.minus,#dont_add_task,.objTable,.new { display: none; }
		.taskname[disabled="disabled"],.taskdesc[disabled="disabled"],.objname[disabled="disabled"],.objdesc[disabled="disabled"] {
			border: none;
			color: black;
			background: white;
		}
		.action { width: 25px; }
		.objTable .new .saveobj,.new .savetask { opacity: 1; }
		.objTable .new .hideobj,.objTable .new .delobj,.objTable .new .showobj { opacity: 0.3; }
		.new .hidetask,.new .deltask,.new .addobj,.new .showtask { opacity: 0.3; }
	</style>';
	}
	
	public function js() {
		global $core;
		$url = $core->blog->getQmarkURL().'pf=taskManager';
		return '
		<script type="text/javascript">
		      var loading="<p class=\'message\'>Veuillez patienter...</p>";
		      var task_added="<p class=\'message\'>'.__('Task has been successfully added').'</p>";
		      var obj_added="<p class=\'message\'>'.__('Objective has been successfully added').'</p>";
		      var task_deleted="<p class=\'message\'>'.__('Task has been successfully deleted').'</p>";
		      var task_updated="<p class=\'message\'>'.__('Task has been successfully updated').'</p>";
		      var obj_reseted="<p class=\'message\'>'.__('Objective has been successfully reseted').'</p>";
		      var obj_deleted="<p class=\'message\'>'.__('Objective has been successfully deleted').'</p>";
		      var obj_updated="<p class=\'message\'>'.__('Objective has been successfully updated').'</p>";
		      var obj_reached="<p class=\'message\'>' . __('Objective has been successfully reached') . '</p>";
		      var cancelled="<p class=\'message\'>' . __('Operation cancelled') . '</p>";
		</script>
		<script type="text/javascript" src="'.$url.'/js/view.js"></script>
		<script type="text/javascript" src="'.$url.'/js/ajax.js"></script>
		';
	}
	
	public function start_form() {
		global $core;
		return '<h2>' . html::escapeHTML($core->blog->name) . ' &rsaquo; Task Manager</h2>
		<div id="answer"></div>
		<table class="maximal dragable" id="taskManager">
			<tr>
				<th></th>
				<th>'.__('Name').'</th>
				<th>'.__('Description').'</th>
				<th colspan="5" >'.__('Actions').'</th>
			</tr>';
	}
	
	public function tasks($id,$name,$desc,$objid) {
	return '<tr class="taskLine" id="taskId'.$id.'">
			<td class="action" class="showobj">
				<img src="index.php?pf=taskManager/img/plus.png" onclick="showObj('.$id.')" class="plus" alt="'.__('more').'"/>
				<img src="index.php?pf=taskManager/img/moins.png" onclick="hideObj('.$id.')" class="minus" alt="'.__('less').'"/>
			</td>
			<td><input type="text" value="'.$name.'" class="taskname" size="'.strlen($name).'" disabled="disabled"/></td>
			<td><input type="text" value="'.$desc.'" class="taskdesc" size="'.strlen($desc).'" disabled="disabled"/></td>
			<td onclick="saveTask('.$id.')" class="savetask action"><img src="index.php?pf=taskManager/img/disk.png" alt="'.__('save').'" /></td>
			<td onclick="del('.$id.',0)" class="deltask action"><img src="index.php?pf=taskManager/img/delete.png" alt="'.__('delete').'" /></td>
			<td onclick="cancel('.$id.',0)" class="hidetask action"><img src="index.php?pf=taskManager/img/pencil_delete.png" alt="'.__('dont\' make changes').'" /></td>
			<td onclick="modify('.$id.',0)" class="showtask action"><img src="index.php?pf=taskManager/img/pencil_add.png" alt="'.__('make changes').'" /></td>
			<td class="addobj action">
				<img onclick="showNewObj('.$id.')" src="index.php?pf=taskManager/img/calendar_add.png" class="plus" alt="'.__('add an objective').'" />
				<img onclick="hideNewObj('.$id.')" src="index.php?pf=taskManager/img/calendar_delete.png" class="minus" alt="'.__('don\'t add an objective').'" />
			</td>
		</tr>
		<tr id="objTable'.$id.'" class="objTable">
			<td></td>
			<td colspan="6">
				<table class="maximal dragable">';
	}
	
	public function new_task($id) {
	return '<tr class="taskLine new" id="taskId'.$id.'">
			<td class="action">
				<img src="index.php?pf=taskManager/img/plus.png" onclick="showObj('.$id.')" class="plus" alt="'.__('more').'"/>
				<img src="index.php?pf=taskManager/img/moins.png" onclick="hideObj('.$id.')" class="minus" alt="'.__('less').'"/>
			</td>
 			<td>
				<input type="text" value="'.__('My New Task').'" class="taskname" size="'.strlen(__('My New Task')).'"/>
			</td>
			<td>
				<input type="text" value="'.__('Desc Of My New Task').'" class="taskdesc" size="'.strlen(__('Desc Of My New Task')).'"/>
			</td>
			<td onclick="saveTask('.$id.',1)" class="savetask action"><img src="index.php?pf=taskManager/img/disk.png" alt="'.__('save').'" /></td>
			<td onclick="del('.$id.',0)" class="deltask action"><img src="index.php?pf=taskManager/img/delete.png" alt="'.__('delete').'" /></td>
			<td onclick="cancel('.$id.',0)" class="hidetask action"><img src="index.php?pf=taskManager/img/pencil_delete.png" alt="'.__('dont\' make changes').'" /></td>
			<td onclick="modify('.$id.',0)" class="showtask action"><img src="index.php?pf=taskManager/img/pencil_add.png" alt="'.__('make changes').'" /></td>
			<td class="addobj action">
				<img onclick="showNewObj('.$id.')" src="index.php?pf=taskManager/img/calendar_add.png" class="plus" alt="'.__('add an objective').'" />
				<img onclick="hideNewObj('.$id.')" src="index.php?pf=taskManager/img/calendar_delete.png" class="minus" alt="'.__('don\'t add an objective').'" />
			</td>
		</tr>
		<tr id="objTable'.$id.'" class="objTable new">
			<td></td>
			<td colspan="6">
				<table class="maximal dragable">';
	}
	
	public function first_objective($id,$taskId) {
	return '<tr class="objLine anyobj">
			<td colspan="2">'.__('You don\'t have any Objective').' <button type="button" 
			onclick="showNewObj('.$taskId.')">'.__('add One!').' <img src="index.php?pf=taskManager/img/calendar_add.png" /></button> </td>
		</tr>';
	}
	
	public function objectives($id,$name,$desc,$finished) {
		$checked =  $finished==1 ? 'checked="checked"' : '';
		
	return '		<tr class="objLine" id="objId'.$id.'">
					<td>
						<input type="text" value="'.$name.'" class="objname" size="'.strlen($name).'" disabled="disabled"/>
					</td>
					<td>
						<input type="text" value="'.$desc.'" class="objdesc" size="'.strlen($desc).'" disabled="disabled"/>
					</td>
					<td onclick="saveObj('.$id.')" class="saveobj action">
						<img src="index.php?pf=taskManager/img/disk.png" alt="'.__('save').'" />
					</td>
					<td onclick="del('.$id.',1)" class="delobj action">
						<img src="index.php?pf=taskManager/img/delete.png" alt="'.__('delete').'" />
					</td>
					<td onclick="cancel('.$id.',1)" class="hideobj action">
						<img src="index.php?pf=taskManager/img/pencil_delete.png" alt="'.__('dont\' make changes').'" />
					</td>
					<td onclick="modify('.$id.',1)" class="showobj action">
						<img src="index.php?pf=taskManager/img/pencil_add.png" alt="'.__('make changes').'" />
					</td>
					<td onclick="finish('.$id.','.$finished.')" class="finish action">
						<input type="checkbox" '.$checked.' />
					</td>
		</tr>';
	}
	
	public function new_objective($id,$taskId) {
	return '
				<tr class="objLine new" id="objId'.$id.'">
					<td>
						<input type="text" value="'.__('My New Objective').'" class="objname" size="'.strlen(__('My New Objective')).'"/>
					</td>
					<td>
						<input type="text" value="'.__('Desc Of My New Objective').'" class="objdesc" size="'.strlen(__('Desc Of My New Objective')).'"/>
					</td>
					<td onclick="saveObj('.$id.','.$taskId.',1)" class="saveobj action">
						<img src="index.php?pf=taskManager/img/disk.png" alt="'.__('save').'" />
					</td>
					<td onclick="del('.$id.',1)" class="delobj action">
						<img src="index.php?pf=taskManager/img/delete.png" alt="'.__('delete').'" />
					</td>
					<td onclick="cancel('.$id.',1)" class="hideobj action">
						<img src="index.php?pf=taskManager/img/pencil_delete.png" alt="'.__('dont\' make changes').'" />
					</td>
					<td onclick="modify('.$id.',1)" class="showobj action">
						<img src="index.php?pf=taskManager/img/pencil_add.png" alt="'.__('make changes').'" />
					</td>
					<td onclick="finish('.$id.',0)" class="finish action">
						<input type="checkbox"/>
					</td>
				</tr>
		';
	}
	
	public function end_objectives() {
		return '		</table>
				</td><td>
				</td>
			</tr>';
	}
	
	public function last_task($objId,$taskId) {
		return 	$this->new_task($taskId).
			$this->first_objective($objId,$taskId).
			$this->new_objective($objId,$taskId).
			$this->end_objectives().
	'
	</table>
	<input id="add_task" type="button" onclick="showNewTask('.$taskId.');" value="'.__('Add a Task').'"/>
	<input id="dont_add_task" type="button" onclick="hideNewTask('.$taskId.');" value="'.__('Don\'t Add a task').'"/>'.dcPage::helpBlock('taskManager');
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

		$nbrObj   = $tM->getNbrObj();
		$objName  = $tM->getObjName();
		$objDesc  = $tM->getObjDesc();
		$objId    = $tM->getObjId();
		$objFinished = $tM->getObjFinished();
		//$objIdMax = $tM->getObjIdMax();
		//$nbrObjTotal = $tM->getNbrObjTotal();
		
		$toReturn = $this->start_form();
		
		for($i=0;$i<$nbrTasks;$i++) {
			$toReturn .= $this->tasks($taskId[$i],$taskName[$i],$taskDesc[$i],$nbrObj[$i]);
			for($y=0;$y<$nbrObj[$i];$y++) {
				$toReturn .= $this->objectives($objId[$i][$y],$objName[$i][$y],$objDesc[$i][$y],$objFinished[$i][$y]);
			}
			if ($nbrObj[$i] == 0) {
				$toReturn .= $this->first_objective(($nbrObj[$i]+1),$taskId[$i]);
			}
			$toReturn .= $this->new_objective(($nbrObj[$i]+1),$taskId[$i]) . $this->end_objectives();
		}
		$last_task_id = $taskId[count($taskId)-1] < ($nbrTasks+1) ? ($nbrTasks+1):$taskId[count($taskId)-1]+1;
 		$toReturn .= $this->last_task(0,$last_task_id);
		return $toReturn; 
	}
}

?>