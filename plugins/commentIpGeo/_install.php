<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of commentIpGeo, a plugin for Dotclear.
#
# Copyright (c) 2007-2009 Frederic PLE
# dotclear@frederic.ple.name
#
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')) { return; }
 
$m_version = $core->plugins->moduleInfo('commentIpGeo','version');
 
$i_version = $core->getVersion('commentIpGeo');

if (version_compare($i_version,$m_version,'>=')) {
	return;
}
 
# Création du setting (s'il existe, il ne sera pas écrasé)
$settings = new dcSettings($core,null);
$settings->setNamespace('commentIpGeo');
$settings->put('commentIpGeo_active',true,'boolean',__('Activer'),false,false);
# Modification du schema de la base
$s = new dbStruct($core->con,$core->prefix);
$s->comment->comment_ip_geo('varchar',2,true);
$si = new dbStruct($core->con,$core->prefix);
$changes = $si->synchronize($s);
$core->setVersion('commentIpGeo',$m_version);
?>
