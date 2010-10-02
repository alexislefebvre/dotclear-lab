<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of kezako, a plugin for Dotclear 2.
# 
# Copyright (c) 2010 Franck Paul and contributors
# carnet.franck.paul@gmail.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')) { exit; }

$version = $core->plugins->moduleInfo('kezako','version');

if (version_compare($core->getVersion('kezako'),$version,'>=')) {
	return;
}

/* Database schema
 -------------------------------------------------------- */
$s = new dbStruct($core->con,$core->prefix);

$s->kezako
	->blog_id('varchar',32,false)
	->thing_id('varchar',255,false)
	->thing_type('varchar',8,false)
	->thing_subtype('varchar',32,true,null)
	->thing_lang('varchar',5,true,null)
	->thing_text('text',0,true)

// thing_subtype should be 64 chars long for full compatibility with
// metadata plugin, _but_ since key size is very limited with MySQL 4.1
// in UTF8 mode, I shorten all keys hoping it is enough.

	->primary('pk_kezako','blog_id','thing_id','thing_type','thing_subtype','thing_lang');

$s->kezako->reference('fk_kezako_blog','blog_id','blog','blog_id','cascade','cascade');

# Schema installation
$si = new dbStruct($core->con,$core->prefix);
$changes = $si->synchronize($s);

$core->setVersion('kezako',$version);
return true;
?>