<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of dcCron, a plugin for Dotclear.
# 
# Copyright (c) 2009-2010 Tomtom
# http://blog.zenstyle.fr/
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
$settings->addNamespace('dccron');
$settings->dccron->put('dccron_tasks',serialize(array()),'string','dcCron tasks',false,true);

$core->setVersion('dcCron',$m_version);

?>