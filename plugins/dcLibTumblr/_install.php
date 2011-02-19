<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of dcLibTumblr, a plugin for Dotclear 2.
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
$new_version = $core->plugins->moduleInfo('dcLibTumblr','version');
$old_version = $core->getVersion('dcLibTumblr');

# Compare versions
if (version_compare($old_version,$new_version,'>=')) {return;}

# Install or update
try
{
	if (version_compare(str_replace("-r","-p",DC_VERSION),'2.2-alpha','<'))
	{
		throw new Exception('dcLibTumblr requires Dotclear 2.2');
	}
	
	# Settings
	$core->blog->settings->addNamespace('dcLibTumblr');
	$s = $core->blog->settings->dcLibTumblr;
	
	$social_writer = base64_encode(serialize(array(
		'email'=>'',
		'password'=>''
	)));
	$social_profil = base64_encode(serialize(array(
		'email'=>'',
		'password'=>'',
		'id' => ''
	)));
	
	$s->put('soCialMe_writer',$social_writer,'string','config for soCialMe Writer',false,true);
	$s->put('soCialMe_profil',$social_profil,'string','config for soCialMe Profil',false,true);
	
	# Version
	$core->setVersion('dcLibTumblr',$new_version);
	
	return true;
}
catch (Exception $e)
{
	$core->error->add($e->getMessage());
	return false;
}
?>