<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of Dotclear 2.
#
# Copyright (c) 2003-2008 Olivier Meunier and contributors
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------
if (!defined('DC_CONTEXT_ADMIN')) { return; }

$version = $core->plugins->moduleInfo('alias','version');

if (version_compare($core->getVersion('alias'),$version,'>=')) {
	return;
}

/* Database schema
-------------------------------------------------------- */
$s = new dbStruct($core->con,$core->prefix);

$s->alias
	->blog_id('varchar',32,false)
	->alias_url('varchar',255,false)
	->alias_destination('varchar',255,false)
	->alias_position('smallint',0,false,1)
	
	->primary('pk_alias','blog_id','alias_url')
	
	->index('idx_alias_blog_id','btree','blog_id')
	->index('idx_alias_blog_id_alias_position','btree','blog_id','alias_position')
	
	->reference('fk_alias_blog','blog_id','blog','blog_id','cascade','cascade')
	;

# Schema installation
$si = new dbStruct($core->con,$core->prefix);
$changes = $si->synchronize($s);

$core->setVersion('alias',$version);
return true;
?>