<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of muppet, a plugin for Dotclear 2.
# 
# Copyright (c) 2010 Osku and contributors
#
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------
if (!defined('DC_CONTEXT_ADMIN')) { exit; }
 
if (version_compare(DC_VERSION,'2.2-beta','<'))
{
     $core->plugins->deactivateModule('muppet');
     return false;
}

$new_version = $core->plugins->moduleInfo('muppet','version');
 
$current_version = $core->getVersion('muppet');
 
if (version_compare($current_version,$new_version,'>=')) {
	return;
}

$s = $core->blog->settings->muppet;
$excludetypes = array('post','page','gal','galitem','thread','related','pollsfactory','flatpage','eventhandler');
$integrated_urls = array('default','default-page','search','category','tag','archive');

$s->put('muppet_types','','string','My supplementary post types',true,true);
// Hidden options
$s->put('muppet_excludes',serialize($excludetypes),'string','Post types excludes from muppet management',true,true);
$s->put('muppet_allow_page',false,'boolean','Allows to change to post_type "page"',true,true);
$s->put('muppet_urls_integration',serialize($integrated_urls),'string','URL types to show others set posts',true,true);

$core->setVersion('muppet',$new_version);
return true;
?>