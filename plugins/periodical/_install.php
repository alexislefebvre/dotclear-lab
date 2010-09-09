<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of periodical, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2010 JC Denis and contributors
# jcdenis@gdwd.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')){return;}

$new_version = $core->plugins->moduleInfo('periodical','version');
$old_version = $core->getVersion('periodical');

if (version_compare($old_version,$new_version,'>=')) return;

try
{
	# Check DC version
	if (version_compare(DC_VERSION,'2.2-beta','<'))
	{
		throw new Exception('periodical requires Dotclear 2.2');
	}
	
	# Tables
	$t = new dbStruct($core->con,$core->prefix);

	# Table principale des sondages
	$t->periodical
		->periodical_id ('bigint',0,false)
		->blog_id('varchar',32,false)
		->periodical_type ('varchar',32,false,"'post'")
		->periodical_title ('varchar',255,false,"''")
		->periodical_tz ('varchar',128,false,"'UTC'")
		->periodical_curdt ('timestamp',0,false,'now()')
		->periodical_enddt ('timestamp',0,false,'now()')
		->periodical_pub_int ('varchar',32,false,"'day'")
		->periodical_pub_nb ('smallint',0,false,1)
		
		->primary('pk_periodical','periodical_id')
		->index('idx_periodical_type','btree','periodical_type');
	
	$ti = new dbStruct($core->con,$core->prefix);
	$changes = $ti->synchronize($t);
	
	# Settings
	$core->blog->settings->addNamespace('periodical');
	$s = $core->blog->settings->periodical;
	$s->put('periodical_active',false,'boolean','Enable extension',false,true);
	$s->put('periodical_upddate',true,'boolean','Update post date',false,true);
	$s->put('periodical_updurl',false,'boolean','Update post url',false,true);
	$s->put('periodical_pub_order','post_dt asc','string','Order of publication',false,true);
	$s->put('periodical_statusnet_login','','string','Login to connect toStatusNet service',false,true);
	$s->put('periodical_statusnet_pass','','string','Password to connect to StatusNet service',false,true);
	$s->put('periodical_statusnet_msg','','string','Message to send to StatusNet service',false,true);
	
	# Version
	$core->setVersion('periodical',$new_version);
	
	return true;
}
catch (Exception $e)
{
	$core->error->add($e->getMessage());
}
return false;
?>