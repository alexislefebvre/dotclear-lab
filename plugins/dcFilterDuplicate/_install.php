<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of dcFilterDuplicate, a plugin for Dotclear 2.
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
$new_version = $core->plugins->moduleInfo('dcFilterDuplicate','version');
$old_version = $core->getVersion('dcFilterDuplicate');

# Compare versions
if (version_compare($old_version,$new_version,'>=')) {return;}

# Install or update
try
{
	# Check version
	if (version_compare(str_replace("-r","-p",DC_VERSION),'2.2-alpha','<'))
	{
		throw new Exception('dcFilterDuplicate requires Dotclear 2.2');
	}
	
	# Settings
	$core->blog->settings->addNamespace('dcFilterDuplicate');
	$core->blog->settings->dcFilterDuplicate->put('dcfilterduplicate_minlen',30,'integer','Minimum lenght of comment to filter',false,true);

	# Version
	$core->setVersion('dcFilterDuplicate',$new_version);

	return true;
}
catch (Exception $e)
{
	$core->error->add($e->getMessage());
	return false;
}
?>