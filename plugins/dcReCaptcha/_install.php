<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of dcReCaptcha, a plugin for Dotclear 2.
#
# Copyright (c) 2013 Benoit de Marne and contributors
# benoit.de.marne@gmail.com
# Many thanks to Association Dotclear
#
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')) { return; }

$moduleName = "dcReCaptcha";

# check version
$m_version = $core->plugins->moduleInfo($moduleName, 'version');
$i_version = $core->getVersion($moduleName);
if (version_compare($i_version, $m_version, '>=')) {
	return;
}

# update version
$core->setVersion('dcReCaptcha', $m_version);
return true;
