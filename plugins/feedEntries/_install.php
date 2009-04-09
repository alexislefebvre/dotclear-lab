<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of feedEntries, a plugin for Dotclear 2.
#
# Copyright (c) 2008-2009 Pep and contributors
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------
if (!defined('DC_CONTEXT_ADMIN')) exit;

$package_version = $core->plugins->moduleInfo('feedEntries','version');
$installed_version = $core->getVersion('feedEntries');
if (version_compare($installed_version,$package_version,'>=')) {
	return;
}

$core->setVersion('feedEntries',$package_version);
return true;
?>