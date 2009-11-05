<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of filesAlias, a plugin for Dotclear 2.
# 
# Copyright (c) 2009 Osku and contributors
#
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------
if (!defined('DC_CONTEXT_ADMIN')) { return; }

$version = $core->plugins->moduleInfo('filesAlias','version');

if (version_compare($core->getVersion('filesAlias'),$version,'>=')) {
	return;
}

/* Database schema
-------------------------------------------------------- */
$s = new dbStruct($core->con,$core->prefix);

$s->filesalias
	->blog_id('varchar',32,false)
	->filesalias_url('varchar',255,false)
	->filesalias_destination('varchar',255,false)
	->filesalias_position('smallint',0,false,1)
	
	->primary('pk_filesalias','blog_id','filesalias_url')
	
	->index('idx_filesalias_blog_id','btree','blog_id')
	->index('idx_filesalias_blog_id_filesalias_position','btree','blog_id','filesalias_position')
	
	->reference('fk_filesalias_blog','blog_id','blog','blog_id','cascade','cascade')
	;

# Schema installation
$si = new dbStruct($core->con,$core->prefix);
$changes = $si->synchronize($s);

$core->setVersion('filesAlias',$version);
return true;
?>