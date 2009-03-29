<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of eventdata, a plugin for Dotclear 2.
#
# Copyright (c) 2009 JC Denis and contributors
# jcdenis@gdwd.com
#
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')) return;

# Get new version
$version = $core->plugins->moduleInfo('eventdata','version');
# Compare versions
if (version_compare($core->getVersion('eventdata'),$version,'>=')) return;
# Install
try {
	eventdataInstall::setTable($core);
	eventdataInstall::setSettings($core);
	eventdataInstall::setVersion($core);
}
catch (Exception $e) {
	$core->error->add($e->getMessage());
}
return true;
?>