<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of disclaimer, a plugin for Dotclear 2.
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
$new_version = $core->plugins->moduleInfo('disclaimer','version');
$old_version = $core->getVersion('disclaimer');

# Compare versions
if (version_compare($old_version,$new_version,'>=')) {return;}

# Install or update
try
{
	# Check DC version
	if (version_compare(DC_VERSION,'2.2-beta','<'))
	{
		throw new Exception('disclaimer requires Dotclear 2.2');
	}
	
	$bots =
	'bot;Scooter;Slurp;Voila;WiseNut;Fast;Index;Teoma;'.
	'Mirago;search;find;loader;archive;Spider;Crawler';

	# Settings
	$core->blog->settings->addNamespace('disclaimer');
	$core->blog->settings->disclaimer->put('disclaimer_active',false,'boolean','Enable disclaimer plugin',false,true);
	$core->blog->settings->disclaimer->put('disclaimer_remember',false,'boolean','Remember user who seen disclaimer',false,true);
	$core->blog->settings->disclaimer->put('disclaimer_redir','http://google.com','string','Redirection if disclaimer is refused',false,true);
	$core->blog->settings->disclaimer->put('disclaimer_title','Disclaimer','string','Title for disclaimer',false,true);
	$core->blog->settings->disclaimer->put('disclaimer_text','You must accept this term before entering','string','Description for disclaimer',false,true);
	$core->blog->settings->disclaimer->put('disclaimer_bots_unactive',false,'boolean','Bypass disclaimer for bots',false,true);
	$core->blog->settings->disclaimer->put('disclaimer_bots_agents',$bots,'string','List of know bots',false,true);

	# Version
	$core->setVersion('disclaimer',$new_version);

	return true;
}
catch (Exception $e)
{
	$core->error->add($e->getMessage());
	return false;
}
?>