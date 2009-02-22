<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of dcCron, a plugin for Dotclear.
# 
# Copyright (c) 2009 Tomtom
# http://blog.zesntyle.fr/
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')) { return; }

$m_version = $core->plugins->moduleInfo('dcCron','version');

$i_version = $core->getVersion('dcCron');

if (version_compare($i_version,$m_version,'>=')) {
	return;
}

$settings = new dcSettings($core,null);
$settings->setNamespace('dccron');
$settings->put('dccron_tasks',serialize(array()),'string','dcCron tasks',false);
$settings->put('dccron_errors',serialize(array()),'string','dcCron errors',false);

$core->setVersion('dcCron',$m_version);

?>