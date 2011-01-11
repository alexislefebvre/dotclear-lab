<?php if (!defined('DC_CONTEXT_ADMIN')) { return; }
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of lePluginDuJour, a plugin for Dotclear 2.
# 
# Copyright (c) 2010 lipki and contributors
# kevin@lepeltier.info
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

# Get new version
$new_version = $core->plugins->moduleInfo('lePluginDuJour','version');
$old_version = $core->getVersion('lePluginDuJour');

# Compare versions
if (version_compare($old_version,$new_version,'>=')) return;

# Install or update
try {
	# Check DC version
	if (version_compare(DC_VERSION,'2.2-x','<'))
		throw new Exception('leplugindujour requires Dotclear 2.2');
	
	# Settings
	$core->blog->settings->addNameSpace('leplugindujour');
	$core->blog->settings->leplugindujour->put('enabled',false,'boolean','Enable this plugin',false);
	$core->blog->settings->leplugindujour->put('plugins_xml',
										'http://update.dotaddict.org/dc2/plugins.xml','string',
										'Plugins XML feed location',true,true);
	$core->blog->settings->leplugindujour->put('day', '', 'string','',true,true);
	$core->blog->settings->leplugindujour->put('plugin', '', 'string','',true,true);

	# Version
	$core->setVersion('lePluginDuJour',$new_version);
	return true;
	
} catch (Exception $e) {
	$core->error->add($e->getMessage());
	return false;
}