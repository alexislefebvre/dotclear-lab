<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of soCialMe, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2011 JC Denis and contributors
# jcdenis@gdwd.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')){return;}

# Get new version
$new_version = $core->plugins->moduleInfo('soCialMe','version');
$old_version = $core->getVersion('soCialMe');

# Compare versions
if (version_compare($old_version,$new_version,'>=')) {return;}

# Install or update
try
{
	if (version_compare(str_replace("-r","-p",DC_VERSION),'2.2-alpha','<'))
	{
		throw new Exception('soCialMe requires Dotclear 2.2');
	}
	
	# Table
	$t = new dbStruct($core->con,$core->prefix);
	$t->socialcache
		->cache_id('bigint',0,false)
		->blog_id('varchar',32,false)
		->cache_dt('timestamp',0,false,'now()')
		->cache_type('varchar',32,false,"'unknow'")
		->cache_method('varchar',64,false)
		->cache_content('text',0,false)
		
		->primary('pk_socialcache','cache_id')
		->index('idx_socialcache_blog_id','btree','blog_id')
		->index('idx_socialcache_type','btree','cache_type');
	
	$ti = new dbStruct($core->con,$core->prefix);
	$changes = $ti->synchronize($t);
	
	# Settings
	$sharer_css = 
	".social-sharers { margin: 0 auto; } \n".
	".social-sharers ul { display: inline-block; list-style: none; margin: 0; padding: 0; border: none !important; } \n".
	".social-sharers ul li { display: inline-block; margin: 0 2px; padding: 0; border: none !important;} \n".
	".social-sharer { margin: 1px; } ";
	$reader_css = 
	".social-reader div { margin: 0 !important; } \n".
	".social-reader { margin-bottom: 1em; } \n".
	".social-reader img { float: left; margin-right: 2px; } \n".
	".social-reader .reader-title { font-weight: bold; } \n".
	".social-reader .reader-content { clear: both; font-size: 1.2em; } \n".
	".social-reader .reader-url { float: right; } \n".
	".reader-icon { float:right; margin: 2px; }";
	$profil_css = 
	".social-profils { margin: 0 auto; } \n".
	".social-profils ul { display: inline-block; list-style: none; margin: 0; padding: 0; border: none !important; } \n".
	".social-profils ul li { display: inline-block; margin: 0 2px; padding: 0; border: none !important;} \n.".
	".social-profil { margin: 1px; } ";
	
	$core->blog->settings->addNamespace('soCialMe');
	$core->blog->settings->soCialMe->put('active',true,'boolean','Enable soCialMe plugin',false,true);
	
	$core->blog->settings->addNamespace('soCialMeSharer');
	$core->blog->settings->soCialMeSharer->put('active',false,'boolean','Enable soCialMe Sharer',false,true);
	$core->blog->settings->soCialMeSharer->put('css',$sharer_css,'string','Additional CSS for soCialMe Sharer',false,true);
	
	$core->blog->settings->addNamespace('soCialMeReader');
	$core->blog->settings->soCialMeReader->put('active',false,'boolean','Enable soCialMe Reader',false,true);
	$core->blog->settings->soCialMeReader->put('css',$reader_css,'string','Additional CSS for soCialMe Reaer',false,true);
	
	$core->blog->settings->addNamespace('soCialMeWriter');
	$core->blog->settings->soCialMeWriter->put('active',false,'boolean','Enable soCialMe Writer',false,true);
	
	$core->blog->settings->addNamespace('soCialMeProfil');
	$core->blog->settings->soCialMeProfil->put('active',false,'boolean','Enable soCialMe Profil',false,true);
	$core->blog->settings->soCialMeProfil->put('css',$profil_css,'string','Additional CSS for soCialMe Profil',false,true);
	
	# Version
	$core->setVersion('soCialMe',$new_version);
	
	return true;
}
catch (Exception $e)
{
	$core->error->add($e->getMessage());
	return false;
}
?>