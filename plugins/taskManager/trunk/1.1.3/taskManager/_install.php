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
$taskA = new dbStruct($core->con,$core->prefix);
$objA = new dbStruct($core->con,$core->prefix);

$taskA->TM_task
	->id			('bigint',	0,	false)
	->blog_id		('varchar',	32,	false)
	->name			('varchar',	255,	false)
	->desc			('varchar',	255,	false)
	->public_show		('bigint',	0,	false)
	->order			('bigint',	0,	false)
	->primary('pk_link','id');
// 	->index('idx_TM_task_blog_id','btree','blog_id')
// 	->reference('fk_TM_task_blog','blog_id','blog','blog_id','cascade','cascade');

$objA->TM_object
	->id			('bigint',	0,	false)
	->task_id		('bigint',	0,	false)
	->blog_id		('varchar',	32,	false)
	->name			('varchar',	255,	false)
	->desc			('varchar',	255,	false)
	->finished		('bigint',	0,	false)
	->order			('bigint',	0,	false)
	->primary('pk_link','id');
//	->index('idx_TM_object_blog_id','btree','blog_id')
// 	->index('idx_TM_object_task_id','btree','task_id')
// 	->reference('fk_TM_object_task','task_id','TM_task','id','cascade','cascade');

# Schema installation
$taskB = new dbStruct($core->con,$core->prefix);
$objB = new dbStruct($core->con,$core->prefix);
$changes = $objB->synchronize($objA);
$changes = $taskB->synchronize($taskA);
$core->con->execute('ALTER TABLE `'.$core->blog->prefix .'TM_task` CHANGE `id` `id` BIGINT( 20 ) NOT NULL AUTO_INCREMENT');
$core->con->execute('ALTER TABLE `'.$core->blog->prefix .'TM_object` CHANGE `id` `id` BIGINT( 20 ) NOT NULL AUTO_INCREMENT');
$core->setVersion('taskManager',$version);
return true;

?>