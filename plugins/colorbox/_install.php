<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of ColorBox, a plugin for Dotclear 2.
#
# Copyright (c) 2009 Philippe Amalgame and contributors
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')) { exit; }
 
$m_version = $core->plugins->moduleInfo('colorbox','version');
 
$i_version = $core->getVersion('colorbox');
 
if (version_compare($i_version,$m_version,'>=')) {
	return;
}


$settings = new dcSettings($core,null);

#test compatibilité des settings
if (!version_compare(DC_VERSION,'2.1.6','<='))
{
	$settings->addNamespace('colorbox');
	
} else {
	$settings->setNamespace('colorbox');
}

$settings->put('colorbox_enabled',false,'boolean',true);
$settings->put('colorbox_theme','3','integer',true);
$settings->put('colorbox_zoom_icon',false,'boolean',true);
$settings->put('colorbox_zoom_icon_permanent',false,'boolean',true);
$settings->put('colorbox_position',false,'boolean',true);

$core->setVersion('colorbox',$m_version);
return true;
?>