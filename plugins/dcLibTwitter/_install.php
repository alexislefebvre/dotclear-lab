<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of dcLibTwitter, a plugin for Dotclear 2.
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
$new_version = $core->plugins->moduleInfo('dcLibTwitter','version');
$old_version = $core->getVersion('dcLibTwitter');

# Compare versions
if (version_compare($old_version,$new_version,'>=')) {return;}

# Install or update
try
{
	if (version_compare(str_replace("-r","-p",DC_VERSION),'2.2-alpha','<'))
	{
		throw new Exception('dcLibTwitter requires Dotclear 2.2');
	}
	
	# Settings
	$core->blog->settings->addNamespace('dcLibTwitter');
	$s = $core->blog->settings->dcLibTwitter;
	
	$social_sharer = base64_encode(serialize(array(
		'via'=>''
	)));
	$social_profil = base64_encode(serialize(array(
		'badge_color' => 'a',
		//'screen_name'=>'',
		'extra_height' =>  '300',
		'extra_width' => '250',
		'extra_small_bgcolor' => '#1985B5',
		'extra_small_color' => '#000000',
		'extra_shell_bgcolor' => '#8EC1DA',
		'extra_shell_color' => '#FFFFFF',
		'extra_tweets_bgcolor' => '#FFFFFF',
		'extra_tweets_color' => '#000000',
		'extra_tweets_lncolor' => '#1985B5',
		'extra_avatars' => false
	)));
	$social_writer = $social_reader = base64_encode(serialize(array()));
	
	$s->put('soCialMe_sharer',$social_sharer,'string','config for soCialMe Sharer',false,true);
	$s->put('soCialMe_profil',$social_profil,'string','config for soCialMe Profil',false,true);
	$s->put('soCialMe_writer',$social_writer,'string','config for soCialMe Writer',false,true);
	$s->put('soCialMe_reader',$social_reader,'string','config for soCialMe Reader',false,true);
	$s->put('optionsForComment_enable',false,'boolean','Enable twitter login on optionForComment',false,true);
	
	# Version
	$core->setVersion('dcLibTwitter',$new_version);
	
	return true;
}
catch (Exception $e)
{
	$core->error->add($e->getMessage());
	return false;
}
?>