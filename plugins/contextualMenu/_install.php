<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of contextualMenu, a plugin for Dotclear.
# 
# Copyright (c) 2008 Frédéric Leroy
# bestofrisk@gmail.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')) { exit; }

$version = $core->plugins->moduleInfo('contextualMenu','version');

if (version_compare($core->getVersion('contextualMenu'),$version,'>=')) {
	return;
}

/* Database schema
-------------------------------------------------------- */
$s = new dbStruct($core->con,$core->prefix);

$s->contextual_menu
	->link_id		('bigint',	0,	false)
	->blog_id		('varchar',	32,	false)
	->link_href		('varchar',	255,	false)
	->link_title	('varchar',	255,	false)
	->link_desc		('varchar',	255,	true)
	->link_lang		('varchar',	5,	true)
	->link_type		('varchar',	255,	true)
	->link_xfn		('varchar',	255,	true)
	->link_group	('varchar',	255,	true)
	->link_special_xfn		('varchar',	255,	true)	
	->link_special_group		('varchar',	255,	true)
	->link_special_widget		('varchar',	255,	true)
	->link_special_content		('varchar',	255,	true)
	->link_special_link_title	('integer',	0,	true,	0)
	->link_position	('integer',	0,	false,	0)
	
	->primary('pk_contextual_menu','link_id')
	;

$s->contextual_menu->index('idx_contextual_menu_blog_id','btree','blog_id');
# verifier cette ligne
$s->contextual_menu->reference('fk_contextual_menu_blog','blog_id','blog','blog_id','cascade','cascade');

# Schema installation
$si = new dbStruct($core->con,$core->prefix);
$changes = $si->synchronize($s);

$core->setVersion('contextualMenu',$version);
return true;
?>