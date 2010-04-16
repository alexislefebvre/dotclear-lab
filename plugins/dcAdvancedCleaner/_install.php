<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of dcAdvancedCleaner, a plugin for Dotclear 2.
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
$new_version = $core->plugins->moduleInfo('dcAdvancedCleaner','version');
$old_version = $core->getVersion('dcAdvancedCleaner');

# Compare versions
if (version_compare($old_version,$new_version,'>=')) {return;}

# Install or update
try
{
	# Check DC version (dev on)
	if (version_compare(DC_VERSION,'2.1.6','<'))
	{
		throw new Exception('Plugin called dcAdvancedCleaner requires Dotclear 2.1.6 or higher.');
	}

	# Settings
	$s = dcAdvancedCleanerSettings($core);
	$s->put('dcAdvancedCleaner_behavior_active',true,'boolean','',false,true);
	$s->put('dcAdvancedCleaner_dcproperty_hide',true,'boolean','',false,true);

	# Version
	$core->setVersion('dcAdvancedCleaner',$new_version);

	return true;
}
catch (Exception $e)
{
	$core->error->add($e->getMessage());
	return false;
}
?>