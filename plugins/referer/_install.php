<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of referer, a plugin for Dotclear.
# 
# Copyright (c) 2008 Tomtom
# http://blog.zesntyle.fr/
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')) { return; }

$m_version = $core->plugins->moduleInfo('referer','version');

$i_version = $core->getVersion('referer');

if (version_compare($i_version,$m_version,'>=')) {
	//return;
}

$settings = new dcSettings($core,null);
$settings->setNamespace('referer');
$settings->put('last_referer',serialize(array()),'string','Last referers',false);
$settings->put('top_referer',serialize(array()),'string','Top referers',false);

$core->setVersion('referer',$m_version);

?>
