<?php  if (!defined('DC_CONTEXT_ADMIN')) {return;}
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of flvplayerconfig, a plugin for Dotclear 2.
# 
# Copyright (c) 2010 lipki and contributors
# kevin@lepeltier.info
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

# Get new version
$new_version = $core->plugins->moduleInfo('flvplayerconfig','version');
$old_version = $core->getVersion('flvplayerconfig');

# Compare versions
if (version_compare($old_version,$new_version,'>=')) return;

# Install or update
try {
	# Check DC version
	if (version_compare(DC_VERSION,'2.2-x','<'))
		throw new Exception('flvplayerconfig requires Dotclear 2.2');
	
	# Settings
	$core->blog->settings->addNameSpace('flvplayerconfig');
	$core->blog->settings->flvplayerconfig->put('enabled',false,'boolean','Enable this plugin',false);

	$args = array(
		'margin' => 1,
		'showvolume' => 1,
		'showtime' => 1,
		'showfullscreen' => 1,
		'buttonovercolor' => 'ff9900',
		'slidercolor1' => 'cccccc',
		'slidercolor2' => '999999',
		'sliderovercolor' => '0066cc',
		'width' => 600,
		'height' => 150
	);

	$core->blog->settings->addNameSpace('themes');
	$core->blog->settings->themes->put('flvplayer_style', serialize($args), 'string', 'flvplayer config', false);


	# Version
	$core->setVersion('flvplayerconfig',$new_version);
	return true;
	
} catch (Exception $e) {
	$core->error->add($e->getMessage());
	return false;
}