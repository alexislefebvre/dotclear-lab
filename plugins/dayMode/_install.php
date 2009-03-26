<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of dayMode, a plugin for Dotclear 2.
#
# Copyright (c) 2006-2009 Pep and contributors
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------
if (!defined('DC_CONTEXT_ADMIN')) exit;

$package_version = $core->plugins->moduleInfo('dayMode','version');
$installed_version = $core->getVersion('dayMode');
if (version_compare($installed_version,$package_version,'>=')) {
	return;
}

$core->blog->settings->setNameSpace('daymode');
$core->blog->settings->put('daymode_active',false,'boolean','plugin activation',false,true);
$core->setVersion('dayMode',$package_version);
return true;
?>