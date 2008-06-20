<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of Dotclear 2 Gallery plugin.
#
# Copyright (c) 2003-2008 Olivier Meunier and contributors
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------
if (!defined('DC_CONTEXT_ADMIN')) exit;

$this_version = $core->plugins->moduleInfo('gallery','version');
$installed_version = $core->getVersion('gallery');
 
if (version_compare($installed_version,$this_version,'>=')) {
	return;
}

$core->setVersion('gallery',$this_version);

return true;
?>
