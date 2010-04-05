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
	# Update fields name on old version (fixed pgSQL compatibility)
	if ($old_version !== null && version_compare($old_version,'0.5.1.1','<'))
	{
		$fields = array(
			array('id','feed_id',"BIGINT( 20 ) NOT NULL DEFAULT '0'"),
			array('creadt','feed_creadt',"TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00'"),
			array('upddt','feed_upddt',"TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00'"),
			array('type','feed_type',"VARCHAR( 32 ) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 'feed'"),
			array('upd_int','feed_upd_int',"INT( 11 ) NOT NULL DEFAULT '3600'"),
			array('upd_last','feed_upd_last',"INT( 11 ) NOT NULL DEFAULT '0'"),
			array('status','feed_status',"SMALLINT( 6 ) NOT NULL DEFAULT '0'"),
			array('name','feed_name',"VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL"),
			array('desc','feed_desc',"LONGTEXT CHARACTER SET utf8 COLLATE utf8_bin NULL DEFAULT NULL"),
			array('url','feed_url',"VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL"),
			array('feed','feed_feed',"VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL"),
			array('tags','feed_tags',"VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_bin NULL DEFAULT NULL"),
			array('owner','feed_owner',"VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL"),
			array('lang','feed_lang',"VARCHAR( 5 ) CHARACTER SET utf8 COLLATE utf8_bin NULL DEFAULT NULL"),
			array('nb_out','feed_nb_out',"INT( 11 ) NOT NULL DEFAULT '0'"),
			array('nb_in','feed_nb_in',"INT( 11 ) NOT NULL DEFAULT '0'")
		);

		if ($core->con->driver() == 'pgsql')
		{
			foreach($fields as $k => $field) {
				$core->con->execute(
					'ALTER TABLE '.$core->prefix.'zc_feed '.
					'RENAME COLUMN '.
					$core->con->escapeSystem($field[0]).
					' TO '.
					$core->con->escapeSystem($field[1]).';'
				);
			}
		}
		else
		{
			foreach($fields as $k => $field) {
				$core->con->execute(
					'ALTER TABLE '.$core->prefix.'zc_feed '.
					'CHANGE '.
					$core->con->escapeSystem($field[0]).' '.
					$core->con->escapeSystem($field[1]).' '.
					$field[2].';'
				);
			}
		}
	}

	# Tables
	$s = new dbStruct($core->con,$core->prefix);
	$s->zc_feed
		->feed_id ('bigint',0,false)
		->feed_creadt ('timestamp',0,false,'now()')
		->feed_upddt ('timestamp',0,false,'now()')
		->feed_type ('varchar',32,false,"'feed'")
		->blog_id ('varchar',32,false)
		->cat_id ('bigint',0,true)
		->feed_upd_int ('integer',0,false,3600)
		->feed_upd_last ('integer',0,false,0)
		->feed_status ('smallint',0,false,0)
		->feed_name ('varchar',255,false)
		->feed_desc ('text',0,true) //!pgsql reserved 'desc'
		->feed_url ('varchar',255,false)
		->feed_feed ('varchar',255,false)
		->feed_tags ('varchar',255,true)
		->feed_owner ('varchar',255,false)
		->feed_lang ('varchar',5,true)
		->feed_nb_out ('integer',0,false,0)
		->feed_nb_in ('integer',0,false,0)

		->primary('pk_zcfs','feed_id')
		->index('idx_zcfs_type','btree','feed_type')
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
	$s->put('zoneclearFeedServer_post_title_redir',serialize(array('feed')),'string','List of templates types for redirection to original post',false,true);
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