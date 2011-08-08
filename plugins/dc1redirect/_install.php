<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of dc1redirect, a plugin for DotClear2.
# Copyright (c) 2011 Olivier Mengué.
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------
if (!defined('DC_CONTEXT_ADMIN')) return;

$package_version = $core->plugins->moduleInfo('dc1redirect','version');
$installed_version = $core->getVersion('dc1redirect');

if (version_compare($installed_version,$package_version,'>=')) {
	return;
}

try {
	// Default settings
	$core->blog->settings->addNameSpace('dc1redirect');
	$core->blog->settings->dc1redirect->put('dc1_redirect',	true,'boolean','',true,false);
	//$core->blog->settings->dc1redirect->put('dc1_old_url',$old_url,'string','',true,false);

	$core->setVersion('dc1redirect',$package_version);
	unset($package_version,$installed_version);
	return true;
}
catch (Exception $e) {
	$core->error->add($e->getMessage());
	unset($package_version,$installed_version);
	return false;
}
?>
