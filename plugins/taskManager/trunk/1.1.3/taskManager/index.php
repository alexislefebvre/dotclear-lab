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

global $core;
$tM = new DcTaskManager($core->blog);
$view = new View();
echo $view->start_page();

if(isset($_POST['refreshBlog']) && !empty($_POST['refreshBlog'])) {
    $core->blog->triggerBlog();
}
else if(isset($_POST['task_visibility']) && !empty($_POST['task_visibility'])) {
    $tM->taskPublicVisibility($_POST['id']);
    echo "\n\n\n\n\n\n ------- taskPublicVisibility -------- \n\n\n\n\n\n";
}
if(isset($_POST['order1']) && isset($_POST['order2'])) {
    $rep = $tM->inverseObjOrder($_POST['order1'],$_POST['order2'],$_POST['task_id']);
    echo "\n\n\n\n\n\n ------- inverseObjOrder(".$_POST['order1'].",".$_POST['order2'].",".$_POST['task_id']."); --------\n
 ".$rep." \n\n\n\n\n\n";
}
else if(isset($_POST['finish_object'])) {
    $tM->changeObjState($_POST['finish_object'],$_POST['linked']);
    echo "\n\n\n\n\n\n ------- changeObjState -------- \n\n\n\n\n\n";
}
else if(isset($_POST['add_task']) && !empty($_POST['add_task'])) {
    echo $tM->addTask(htmlspecialchars($_POST['task_newname']), htmlspecialchars($_POST['task_newdesc']), $_POST['id']);
    echo "\n\n\n\n\n\n ------- addTask -------- \n\n\n\n\n\n";
}
else if(isset($_POST['add_obj']) && !empty($_POST['add_obj'])) {
    echo $tM->addObj(htmlspecialchars($_POST['obj_newname']), htmlspecialchars($_POST['obj_newdesc']), $_POST['linked_with']);
    echo "\n\n\n\n\n\n ------- addObj -------- \n\n\n\n\n\n";
}
else if(isset($_POST['put_task_id']) && !empty($_POST['put_task_id'])) {
    $tM->modTask($_POST['put_task_id'], htmlspecialchars($_POST['task_newname']), htmlspecialchars($_POST['task_newdesc']));
    echo "\n\n\n\n\n\n ------- modTask -------- \n\n\n\n\n\n";
}
else if(isset($_POST['order'])){
    $tM->modObj($_POST['order'], htmlspecialchars($_POST['obj_newname']), htmlspecialchars($_POST['obj_newdesc']), $_POST['linked']);
    echo "\n\n\n\n\n\n ------- modObj -------- \n\n\n\n\n\n";
}
else if(isset($_POST['task_delete']) && !empty($_POST['task_delete'])) {
    $tM->delTask($_POST['task_delete']);
    echo "\n\n\n\n\n\n ------- delTask -------- \n\n\n\n\n\n";
}
else if(isset($_POST['obj_delete']) && !empty($_POST['obj_delete'])) {
    $tM->delObj($_POST['obj_delete'],$_POST['linked']);
    echo "\n\n\n\n\n\n ------- delObj -------- \n\n\n\n\n\n";
}
else {
	echo $view->show();
}

echo $view->end_page();
?>