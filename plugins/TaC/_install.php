<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of TaC, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2010 JC Denis and contributors
# jcdenis@gdwd.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')){return;}

# Get new version
$new_version = $core->plugins->moduleInfo('TaC','version');
$old_version = $core->getVersion('TaC');

# Compare versions
if (version_compare($old_version,$new_version,'>=')){return;}

# Install or update
try
{
	# Check DC version
	if (version_compare(str_replace("-r","-p",DC_VERSION),'2.2-alpha','<'))
	{
		throw new Exception('TaC requires Dotclear 2.2');
	}
	# Table
	$t = new dbStruct($core->con,$core->prefix);
	// registry
	$t->tac_registry
		->registry_id('bigint',0,false)
		->registry_dt('timestamp',0,false,'now()')
		->cr_id('varchar',255,false)
		->cr_key('varchar',128,false)
		->cr_secret('varchar',128,false)
		->cr_expiry('bigint',0,false,0)
		->cr_url_request('varchar',255,false)
		->cr_url_access('varchar',255,false)
		->cr_url_autorize('varchar',255,false)
		->cr_url_authenticate('varchar',255,false)
		->cr_sig_method('varchar',32,false,"'HMAC-SHA1'")
		
		->primary('pk_tac_registry','registry_id')
		->unique('uk_tac_registry_cr','cr_id');
	
	// token
	$t->tac_access
		->access_id('bigint',0,false) // unique id
		->access_dt('timestamp',0,false,'now()') // date of grant access
		//->cr_id('bigint',0,false)
		->blog_id('varchar',32,false)
		->user_id('varchar',32,true) // DC user id can be null if it is a blog access
		->registry_id('bigint',0,false)
		->ct_id('varchar',255,false) //ex: Twitter user_id
		->ct_token('varchar',64,false)
		->ct_token_secret('varchar',64,false)
		
		->primary('pk_tac_access','access_id');
	
	$ti = new dbStruct($core->con,$core->prefix);
	$changes = $ti->synchronize($t);
	# Settings
	//$core->blog->settings->addNamespace('TaC');
	//$core->blog->settings->dcTwitterAuth->put('active',false,'boolean','Enabled TaC plugin',false,true);
	# Version
	$core->setVersion('TaC',$new_version);
	# Well done
	return true;
}
catch (Exception $e)
{
	$core->error->add($e->getMessage());
	return false;
}
?>