<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of wikioWorld, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2010 JC Denis and contributors
# jcdenis@gdwd.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')){return;}

$new_version = $core->plugins->moduleInfo('wikioWorld','version');
$old_version = $core->getVersion('wikioWorld');

if (version_compare($old_version,$new_version,'>=')) return;

try
{
	# Check DC version
	if (version_compare(DC_VERSION,'2.2-beta','<'))
	{
		throw new Exception('wikioWorld requires Dotclear 2.2');
	}
	
	# Settings
	$core->blog->settings->addNamespace('wikioWorld');
	$core->blog->settings->wikioWorld->put('wikioWorld_active',false,'boolean','Enable wikioWorld',false,true);
	$core->blog->settings->wikioWorld->put('wikioWorld_entryvote_active',false,'boolean','Enable entry vote',false,true);
	$core->blog->settings->wikioWorld->put('wikioWorld_entryvote_style',false,'boolean','Enrty vote button style',false,true);
	$core->blog->settings->wikioWorld->put('wikioWorld_entryvote_place','after','string','Enrty vote button place',false,true);
	$core->blog->settings->wikioWorld->put('wikioWorld_blogrss_active',false,'boolean','Enable blog RSS button on footer',false,true);
	$core->blog->settings->wikioWorld->put('wikioWorld_blogrss_style','','string','Blog RSS button style on footer',false,true);
	$core->blog->settings->wikioWorld->put('wikioWorld_addwikio_active',false,'boolean','Enable add to wikio button on footer',false,true);
	
	# Version
	$core->setVersion('wikioWorld',$new_version);
	
	return true;
}
catch (Exception $e)
{
	$core->error->add($e->getMessage());
}
return false;
?>