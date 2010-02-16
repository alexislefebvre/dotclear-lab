<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of fac, a plugin for Dotclear 2.
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
$new_version = $core->plugins->moduleInfo('fac','version');
$old_version = $core->getVersion('fac');

# Compare versions
if (version_compare($old_version,$new_version,'>=')){return;}

# Install or update
try {
	# Check DC version (dev on)
	if (!version_compare(DC_VERSION,'2.1.6','>='))
	{
		throw new Exception('Plugin called "fac" requires Dotclear 2.1.6 or higher.');
	}
	# Check DC version (new settings)
	if (version_compare(DC_VERSION,'2.2','>='))
	{
		throw new Exception('Plugin called "fac" requires Dotclear up to 2.2.');
	}
	# Need metadata
	if (!$core->plugins->moduleExists('metadata'))
	{
		throw new Exception('Plugin called "fac" requires plugin "metadata".');
	}
	# Settings
	$s =& $core->blog->settings;
	$s->setNameSpace('fac');
	$s->put('fac_active',false,'boolean','Enabled fac plugin',false,true);
	$s->put('fac_public_tpltypes',serialize(array('post','tag','archive')),'string','List of templates types which used fac',false,true);
	$s->put('fac_public_limit',5,'integer','Number of feeds to show',false,true);
	$s->put('fac_public_title','','string','Title of feed',false,true);
	$s->setNameSpace('system');
	# Version
	$core->setVersion('fac',$new_version);
	# Ok
	return true;
}
catch (Exception $e) {
	$core->error->add($e->getMessage());
	return false;
}
?>