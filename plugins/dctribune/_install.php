<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of dctribune, a plugin for Dotclear 2.
# 
# Copyright (c) 2009 Osku  and contributors
# Many thanks to Pep, Tomtom and JcDenis
# Originally from Antoine Libert
#
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')) { exit; }

$version = $core->plugins->moduleInfo('dctribune','version');

if (version_compare($core->getVersion('dctribune'),$version,'>=')) {
	return;
}

try
{
	/* Database schema
	-------------------------------------------------------- */
	$s = new dbStruct($core->con,$core->prefix);

	$s->tribune
		->tribune_id('bigint',0,false)
		->blog_id('varchar',32,false)
		->tribune_nick('varchar',255,false)
		->tribune_ip('varchar',15,false)
		->tribune_dt('timestamp',0,false,'now()')
		->tribune_msg('varchar',255,false)
		->tribune_state('smallint',0,false,1)

		->primary('pk_tribune','tribune_id')
		;

	$s->tribune->index('idx_tribune_blog_id','btree','blog_id');
	$s->tribune->reference('fk_tribune_blog','blog_id','blog','blog_id','cascade','cascade');

	# Schema installation
	$si = new dbStruct($core->con,$core->prefix);
	$si->synchronize($s);

	return true;
}

catch (Exception $e)
{
	$core->error->add($e->getMessage());
}
return false;
?>