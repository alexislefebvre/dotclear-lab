<?php
View::empecherCache();
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
echo $view->start_page();

if(isset($_POST['task_visibility']) && !empty($_POST['task_visibility'])) {
  $tM->taskPublicVisibility($_POST['id']);
}
else if(isset($_POST['finish_object'])) {
  $tM->changeObjState($_POST['finish_object'],$_POST['linked']);
}
else if(isset($_POST['add_task']) && !empty($_POST['add_task'])) {
  echo $tM->addTask(htmlspecialchars($_POST['task_newname']), htmlspecialchars($_POST['task_newdesc']), $_POST['id']);
}
else if(isset($_POST['add_obj']) && !empty($_POST['add_obj'])) {
  echo $tM->addObj(htmlspecialchars($_POST['obj_newname']), htmlspecialchars($_POST['obj_newdesc']), $_POST['linked_with']);
}
else if(isset($_POST['put_task_id']) && !empty($_POST['put_task_id'])) {
  $tM->modTask($_POST['put_task_id'], htmlspecialchars($_POST['task_newname']), htmlspecialchars($_POST['task_newdesc']));
}
else if(isset($_POST['order'])){
  $tM->modObj($_POST['order'], htmlspecialchars($_POST['obj_newname']), htmlspecialchars($_POST['obj_newdesc']), $_POST['linked']);
}
else if(isset($_POST['task_delete']) && !empty($_POST['task_delete'])) {
  $tM->delTask($_POST['task_delete']);
}
else if(isset($_POST['obj_delete']) && !empty($_POST['obj_delete'])) {
  $tM->delObj($_POST['obj_delete'],$_POST['linked']);
}
else {
  echo $view->show();
}

echo $view->end_page();
?>