<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of dcLibFoursquare, a plugin for Dotclear 2.
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
$new_version = $core->plugins->moduleInfo('dcLibFoursquare','version');
$old_version = $core->getVersion('dcLibFoursquare');

# Compare versions
if (version_compare($old_version,$new_version,'>=')) {return;}

# Install or update
try
{
	if (version_compare(str_replace("-r","-p",DC_VERSION),'2.2-alpha','<'))
	{
		throw new Exception('dcLibFoursquare requires Dotclear 2.2');
	}
	
	# Settings
	$core->blog->settings->addNamespace('dcLibFoursquare');
	$s = $core->blog->settings->dcLibFoursquare;
	
	$social_profil = $social_reader = base64_encode(serialize(array()));
	
	$s->put('soCialMe_profil',$social_profil,'string','config for soCialMe Profil',false,true);
	$s->put('soCialMe_preader',$social_reader,'string','config for soCialMe Reader',false,true);
	
	$s->put('oauth_admin','','string','oAuth2 app ref for admin side',false,true);
	$s->put('oauth_public','','string','oAuth2 app ref for public side',false,true);//not use
	
	# Version
	$core->setVersion('dcLibFoursquare',$new_version);
	
	return true;
}
catch (Exception $e)
{
	$core->error->add($e->getMessage());
	return false;
}
?>