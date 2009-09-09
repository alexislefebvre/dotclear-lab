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

# TM && tM = Task Manager

if (!defined('DC_RC_PATH')) { return; } 

$tM = new DcTaskManager($core->blog);
$view = new View();

$nbrTasks = $tM->getNbrTasks();
$taskId   = $tM->getTaskId();
$nbrObj   = $tM->getNbrObj();
$objId    = $tM->getObjId();
$objIdMax = $tM->getObjIdMax();

echo $view->start_page();

if(isset($_POST['finish_object']) && !empty($_POST['finish_object'])) {
	$tM->changeObjState($_POST['finish_object'],1);
}

elseif(isset($_POST['active_object']) && !empty($_POST['active_object'])) {
	$tM->changeObjState($_POST['active_object'],0);
}

elseif(isset($_POST['add_task']) && !empty($_POST['add_task'])) {
	$tM->addTask(htmlspecialchars($_POST['task_newname']),htmlspecialchars($_POST['task_newdesc']),$_POST['id']);
	echo $view->new_task($_POST['id']+1);
}

elseif(isset($_POST['add_obj']) && !empty($_POST['add_obj'])) {
	$tM->addObj( htmlspecialchars($_POST['obj_newname']),htmlspecialchars($_POST['obj_newdesc']),$_POST['linked_with'],$_POST['id']);
	for ($j=0;$j<(count($taskId)-1);$j++) {
		if ($taskId[$j]==$_POST['linked_with']) {
			$k=$j;
			break;
		}
	}
	echo $view->new_objective(($objIdMax+$nbrTasks+1),$_POST['linked_with'],$nbrObj[$k]+1);
}

elseif(isset($_POST['put_task_id']) && !empty($_POST['put_task_id'])) {
	$tM->modTask($_POST['put_task_id'],htmlspecialchars($_POST['task_newname']),htmlspecialchars($_POST['task_newdesc']));
}

elseif(isset($_POST['put_obj_id']) && !empty($_POST['put_obj_id'])) {
 	$tM->modObj($_POST['put_obj_id'],htmlspecialchars($_POST['obj_newname']),htmlspecialchars($_POST['obj_newdesc']));
}

elseif(isset($_POST['task_delete']) && !empty($_POST['task_delete'])) {
	$tM->delTask($_POST['task_delete']);
}

elseif(isset($_POST['obj_delete']) && !empty($_POST['obj_delete'])) {
	$tM->delObj($_POST['obj_delete']);
}

else {
	echo $view->show();
}
echo $view->end_page();
?>