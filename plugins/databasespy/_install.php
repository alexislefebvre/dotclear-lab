<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of databasespy, a plugin for Dotclear 2.
# 
# Copyright (c) 2009 JC Denis and contributors
# jcdenis@gdwd.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')){return;}

# Get versions
$new = $core->plugins->moduleInfo('databasespy','version');
$old = $core->getVersion('databasespy');
# Compare versions
if (version_compare($old,$new,'>=')) return;
# Set new version
try {
	$core->setVersion('databasespy',$new);
}
catch (Exception $e) {
	$core->error->add($e->getMessage());
}
return true;
?>