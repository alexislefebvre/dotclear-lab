<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of dcLibFacebook, a plugin for Dotclear 2.
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
$new_version = $core->plugins->moduleInfo('dcLibFacebook','version');
$old_version = $core->getVersion('dcLibFacebook');

# Compare versions
if (version_compare($old_version,$new_version,'>=')) {return;}

# Install or update
try
{
	if (version_compare(str_replace("-r","-p",DC_VERSION),'2.2-alpha','<'))
	{
		throw new Exception('dcLibFacebook requires Dotclear 2.2');
	}
	
	# Settings
	$core->blog->settings->addNamespace('dcLibFacebook');
	$s = $core->blog->settings->dcLibFacebook;
	
	$social_sharer = base64_encode(serialize(array(
		'colorscheme'=>'light'
	)));
	
	$s->put('soCialMe_sharer',$social_sharer,'string','config for soCialMe Sharer',false,true);
	
	$s->put('oauth_admin','','string','oAuth2 app ref for admin side',false,true);
	$s->put('oauth_public','','string','oAuth2 app ref for public side',false,true);//not use yet
	
	# Version
	$core->setVersion('dcLibFacebook',$new_version);
	
	return true;
}
catch (Exception $e)
{
	$core->error->add($e->getMessage());
	return false;
}
?>