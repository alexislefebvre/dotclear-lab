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
$table1 = new dbStruct($core->con,$core->prefix);
$table2 = new dbStruct($core->con,$core->prefix);
$table3 = new dbStruct($core->con,$core->prefix);

$table1->TM_task
	->id			('bigint',	0,	false)
	->name			('varchar',	255,	false)
	->desc			('varchar',	255,	false)
	->order			('bigint',	0,	false)
	->primary('pk_link','id');

$table2->TM_object
	->task_id		('bigint',	0,	false)
	->id			('bigint',	0,	false)
	->name			('varchar',	255,	false)
	->desc			('varchar',	255,	false)
	->finished		('bigint',	0,	false)
	->order			('bigint',	0,	false)
	->primary('pk_link','id');
/*$table3->TM_link
	->task_id		('bigint',	0,	false)
	->obj_id		('bigint',	0,	false)
	->compt 		('bigint',	0,	false)
	->primary('pk_link','obj_id');
*/
# Schema installation
$table1s = new dbStruct($core->con,$core->prefix);
$changes = $table1s->synchronize($table1);
$core->con->execute('ALTER TABLE `'.$core->blog->prefix .'TM_task` CHANGE `id` `id` BIGINT( 20 ) NOT NULL AUTO_INCREMENT');
$table2i = new dbStruct($core->con,$core->prefix);
$changes = $table2i->synchronize($table2);
$core->con->execute('ALTER TABLE `'.$core->blog->prefix .'TM_object` CHANGE `id` `id` BIGINT( 20 ) NOT NULL AUTO_INCREMENT');
/*
$table3i = new dbStruct($core->con,$core->prefix);
$changes = $table3i->synchronize($table3);
$core->con->execute('ALTER TABLE `'.$core->blog->prefix .'TM_link` CHANGE `obj_id` `obj_id` BIGINT( 20 ) NOT NULL AUTO_INCREMENT');
$core->con->execute('ALTER TABLE `'.$core->blog->prefix .'TM_link` CHANGE `task_id` `task_id` BIGINT( 20 ) NOT NULL AUTO_INCREMENT');
*/
$core->setVersion('taskManager',$version);
return true;

?>