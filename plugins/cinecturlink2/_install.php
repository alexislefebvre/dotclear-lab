<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of cinecturlink2, a plugin for Dotclear 2.
# 
# Copyright (c) 2009 JC Denis and contributors
# jcdenis@gdwd.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')){return;}

$new_version = $core->plugins->moduleInfo('cinecturlink2','version');
$old_version = $core->getVersion('cinecturlink2');

if (version_compare($old_version,$new_version,'>=')) return;

try {
	# Is DC 2.1.5 ?
	if (!version_compare(DC_VERSION,'2.1.5','>=')) {

		throw new Exception('cinecturlink2 requires Dotclear 2.1.5');
	}
	# Database
	$s = new dbStruct($core->con,$core->prefix);
	$s->cinecturlink2
		->link_id ('bigint',0,false)
		->blog_id ('varchar',32,false)
		->cat_id ('bigint',0,true)
		->user_id ('varchar',32,true)
		->link_type ('varchar',32,false,"'cinecturlink'")
		->link_title ('varchar',255,false)
		->link_desc ('varchar',255,false)
		->link_author ('varchar',255,false)
		->link_lang ('varchar',5,false,"'en'")
		->link_url ('varchar',255,false)
		->link_img ('varchar',255,false)
		->link_creadt ('timestamp',0,false,'now()')
		->link_upddt ('timestamp',0,false,'now()')
		->link_pos ('integer',4,false)
		->link_note('integer',2,false,"'10'")

		->primary('pk_cinecturlink2','link_id')
		->index('idx_cinecturlink2_title','btree','link_title')
		->index('idx_cinecturlink2_author','btree','link_author')
		->index('idx_cinecturlink2_blog_id','btree','blog_id')
		->index('idx_cinecturlink2_cat_id','btree','cat_id')
		->index('idx_cinecturlink2_user_id','btree','user_id')
		->index('idx_cinecturlink2_type','btree','link_type');

	$s->cinecturlink2_cat
		->cat_id ('bigint',0,false)
		->blog_id ('varchar',32,false)
		->cat_title ('varchar',255,false)
		->cat_desc ('varchar',255,false)
		->cat_creadt ('timestamp',0,false,'now()')
		->cat_upddt ('timestamp',0,false,'now()')
		->cat_pos ('integer',4,false)

		->primary('pk_cinecturlink2_cat','cat_id')
		->index('idx_cinecturlink2_cat_blog_id','btree','blog_id')
		->unique('uk_cinecturlink2_cat_title','cat_title','blog_id');

	$si = new dbStruct($core->con,$core->prefix);
	$changes = $si->synchronize($s);

	# Setting
	$s =& $core->blog->settings;
	$s->setNameSpace('cinecturlink2');

	$s->put('cinecturlink2_active',true,'boolean','Enable cinecturlink2',false,true);
	$s->put('cinecturlink2_widthmax',100,'integer','Maximum width of picture',false,true);
	$s->put('cinecturlink2_folder','cinecturlink','string','Public folder of pictures',false,true);
	$s->put('cinecturlink2_triggeronrandom',false,'boolean','Open link in new window',false,true);

	$s->setNameSpace('system');

	# Version
	$core->setVersion('cinecturlink2',$core->plugins->moduleInfo('cinecturlink2','version'));

	return true;
}
catch (Exception $e) {
	$core->error->add($e->getMessage());
}
return false;
?>