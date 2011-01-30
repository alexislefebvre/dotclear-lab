<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of oAuthManager, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2011 JC Denis and contributors
# jcdenis@gdwd.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------
if (!defined('DC_CONTEXT_ADMIN')){return;}# Get new version$new_version = $core->plugins->moduleInfo('oAuthManager','version');$old_version = $core->getVersion('oAuthManager');# Compare versionsif (version_compare($old_version,$new_version,'>=')) {return;}# Install or updatetry{	if (version_compare(str_replace("-r","-p",DC_VERSION),'2.2-alpha','<'))	{		throw new Exception('oAuthManager requires Dotclear 2.2');	}		# Table	$t = new dbStruct($core->con,$core->prefix);	$t->oauthclient		->uid('bigint',0,false)		->blog_id('varchar',32,false)		->plugin_id('varchar',255,false)		->client_id('varchar',255,false)		->user_id('varchar',32,true)		->name('varchar',255,true,null)		->state('smallint',0,false,0)		->token('varchar',128,true,null)		->secret('varchar',128,true,null)		->mtime('timestamp',0,false,'now()')		->expiry('timestamp',0,true,null)				->primary('pk_oauthclient','uid')		->index('idx_oauthclient_blog_id','btree','blog_id')		->index('idx_oauthclient_user_id','btree','user_id');		$ti = new dbStruct($core->con,$core->prefix);	$changes = $ti->synchronize($t);		# Settings	$core->blog->settings->addNamespace('oAuthManager');	$s = $core->blog->settings->oAuthManager;	$s->put('active',true,'boolean','Enabled oAuthManager plugin',false,true);		# Version	$core->setVersion('oAuthManager',$new_version);	return true;}catch (Exception $e){	$core->error->add($e->getMessage());	return false;}?>