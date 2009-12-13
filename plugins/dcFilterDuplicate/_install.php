<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of dcFilterDuplicate, a plugin for Dotclear 2.
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
$new_version = $core->plugins->moduleInfo('dcFilterDuplicate','version');
$old_version = $core->getVersion('dcFilterDuplicate');
# Compare versions
if (version_compare($old_version,$new_version,'>=')) {return;}
# Install or update
try {
	# Check DC version (dev on)
	if (!version_compare(DC_VERSION,'2.1.5','>=')) {
		throw new Exception('Plugin called dcFilterDuplicate requires Dotclear 2.1.5 or higher.');
	}
	# Check DC version (new settings)
	if (version_compare(DC_VERSION,'2.2','>=')) {
		throw new Exception('Plugin called dcFilterDuplicate requires Dotclear up to 2.2.');
	}
	# Settings
	$s = null;
	$s =& $core->blog->settings;
	$s->setNameSpace('dcFilterDuplicate');
	$s->put('dcfilterduplicate_minlen',30,'integer','Minimum lenght of comment to filter',false,true);
	$s->setNameSpace('system');
	# Version
	$core->setVersion('dcFilterDuplicate',$new_version);
	# All right baby
	return true;
}
catch (Exception $e) {
	$core->error->add($e->getMessage());
	return false;
}
?>