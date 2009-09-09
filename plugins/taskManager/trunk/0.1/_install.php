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

if (!defined('DC_CONTEXT_ADMIN')) { return; } 

$version = $core->plugins->moduleInfo('taskManager','version');

if (version_compare($core->getVersion('taskManager'),$version,'>=')) {
	return;
}

/* Database schema
-------------------------------------------------------- */
$s = new dbStruct($core->con,$core->prefix);
$ss = new dbStruct($core->con,$core->prefix);

$s->TM_task
	->task_id			('bigint',	0,	false)
	->task_name			('varchar',	255,	false)
	->task_desc			('varchar',	255,	false);
$ss->TM_object
	->obj_id			('bigint',	0,	false)
	->obj_name			('varchar',	255,	false)
	->obj_desc			('varchar',	255,	false)
	->obj_linked_with		('bigint',	0,	false)
	->obj_finished			('bigint',	0,	false);
	
# Schema installation
$si = new dbStruct($core->con,$core->prefix);
$changes = $si->synchronize($s);

$ssi = new dbStruct($core->con,$core->prefix);
$changes = $ssi->synchronize($ss);
//$core->con->execute("ALTER TABLE `blog_TM_task` CHANGE `task_id` `task_id` BIGINT( 20 ) NOT NULL AUTO_INCREMENT");
//$core->con->execute("ALTER TABLE `blog_TM_object` CHANGE `obj_id` `obj_id` BIGINT( 20 ) NOT NULL AUTO_INCREMENT");
$core->setVersion('taskManager',$version);
return true;

?>