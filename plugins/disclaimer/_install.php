<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of disclaimer, a plugin for Dotclear 2.
# 
# Copyright (c) 2009 JC Denis and contributors
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
	# Check DC version (dev on)
	if (!version_compare(DC_VERSION,'2.1.5','>='))
	{
		throw new Exception('Plugin called disclaimer requires Dotclear 2.1.5 or higher.');
	}
	# Check DC version (new settings)
	if (version_compare(DC_VERSION,'2.2','>='))
	{
		throw new Exception('Plugin called disclaimer requires Dotclear up to 2.2.');
	}

	# Settings
	$s = null;
	$s =& $core->blog->settings;

	$bots =
	'bot;Scooter;Slurp;Voila;WiseNut;Fast;Index;Teoma;'.
	'Mirago;search;find;loader;archive;Spider;Crawler';

	$s->setNameSpace('disclaimer');
	$s->put('disclaimer_active',false,'boolean','Enable disclaimer plugin',false,true);
	$s->put('disclaimer_remember',false,'boolean','Remember user who seen disclaimer',false,true);
	$s->put('disclaimer_redir','http://google.com','string','Redirection if disclaimer is refused',false,true);
	$s->put('disclaimer_title','Disclaimer','string','Title for disclaimer',false,true);
	$s->put('disclaimer_text','You must accept this term before entering','string','Description for disclaimer',false,true);
	$s->put('disclaimer_bots_unactive',false,'boolean','Bypass disclaimer for bots',false,true);
	$s->put('disclaimer_bots_agents',$bots,'string','List of know bots',false,true);
	$s->setNameSpace('system');

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