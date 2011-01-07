<?php if (!defined('DC_CONTEXT_ADMIN')) { return; }
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of Hyphenator plugin for Dotclear 2.
#
# Copyright (c) 2009 kévin Lepeltier and contributors
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------

# Get new version
$new_version = $core->plugins->moduleInfo('hyphenator','version');
$old_version = $core->getVersion('hyphenator');

# Compare versions
if (version_compare($old_version,$new_version,'>=')) return;

# Install or update
try {
	# Check DC version
	if (version_compare(DC_VERSION,'2.2','<'))
		throw new Exception('hyphenator requires Dotclear 2.2');
	
	# Settings
	$core->blog->settings->addNameSpace('hyphenator');
	$core->blog->settings->hyphenator->put('enabled',false,'boolean','Enable this plugin',false);

	# Version
	$core->setVersion('hyphenator',$new_version);
	return true;
	
} catch (Exception $e) {
	$core->error->add($e->getMessage());
	return false;
}