<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of zoneclearFeedServer, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2010 JC Denis, BG and contributors
# jcdenis@gdwd.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')){return;}

$new_version = $core->plugins->moduleInfo('zoneclearFeedServer','version');
$old_version = $core->getVersion('zoneclearFeedServer');

if (version_compare($old_version,$new_version,'>=')) return;

try {
	# Check DC version (dev on)
	if (!version_compare(DC_VERSION,'2.1.5','>='))
	{
		throw new Exception('Plugin called zoneclearFeedServer requires Dotclear 2.1.5 or higher.');
	}
	# Check DC version (new settings)
	if (version_compare(DC_VERSION,'2.2','>='))
	{
		throw new Exception('Plugin called zoneclearFeedServer requires Dotclear up to 2.2.');
	}
	if (!$core->plugins->moduleExists('metadata'))
	{
		throw new Exception('Plugin called zoneclearFeedServer requires metadata plugin');
	}
	# Tables
	$s = new dbStruct($core->con,$core->prefix);
	$s->zc_feed
		->id ('bigint',0,false)
		->creadt ('timestamp',0,false,'now()')
		->upddt ('timestamp',0,false,'now()')
		->type ('varchar',32,false,"'feed'")
		->blog_id ('varchar',32,false)
		->cat_id ('bigint',0,true)
		->upd_int ('integer',0,false,3600)
		->upd_last ('integer',0,false,0)
		->status ('smallint',0,false,0)
		->name ('varchar',255,false)
		->desc ('text',0,true)
		->url ('varchar',255,false)
		->feed ('varchar',255,false)
		->tags ('varchar',255,true)
		->owner ('varchar',255,false)
		->lang ('varchar',5,true)
		->nb_out ('integer',0,false,0)
		->nb_in ('integer',0,false,0)

		->primary('pk_zcfs','id')
		->index('idx_zcfs_type','btree','type')
		->index('idx_zcfs_blog','btree','blog_id');

	$si = new dbStruct($core->con,$core->prefix);
	$changes = $si->synchronize($s);

	# Settings
	$s =& $core->blog->settings;

	$s->setNameSpace('zoneclearFeedServer');
	$s->put('zoneclearFeedServer_active',false,'boolean','Enable zoneclearBlogServer',false,true);
	$s->put('zoneclearFeedServer_timer',0,'integer','Timer between 2 updates',false,true);
	$s->put('zoneclearFeedServer_post_status_new',true,'boolean','Enable auto publish new posts',false,true);
	$s->put('zoneclearFeedServer_update_limit',5,'integer','Number of feeds to update at one time',false,true);
	$s->put('zoneclearFeedServer_user','','string','User id that has right on post',false,true);
	$s->put('zoneclearFeedServer_post_full_tpl',serialize(array('post','category','tag','archive')),'string','List of templates types for full feed',false,true);
	$s->setNameSpace('system');

	# Version
	$core->setVersion('zoneclearFeedServer',$new_version);

	return true;
}
catch (Exception $e) {
	$core->error->add($e->getMessage());
}
return false;
?>