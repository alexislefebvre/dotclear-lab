<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of lePluginDuJour, a plugin for Dotclear 2.
# 
# Copyright (c) 2010 lipki and contributors
# kevin@lepeltier.info
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------
 if (!defined('DC_CONTEXT_ADMIN')) { return; }

$m_version = $core->plugins->moduleInfo('lePluginDuJour','version');
$i_version = $core->getVersion('lePluginDuJour');
if (version_compare($i_version,$m_version,'>=')) return;

# Settings compatibility test
if (!version_compare(DC_VERSION,'2.2-x','<')) {
	$core->blog->settings->addNamespace('leplugindujour');
	$s = $core->blog->settings->leplugindujour;
} else {
	$core->blog->settings->setNamespace('leplugindujour');
	$s = $core->blog->settings;
}

# Création du setting
$s->put('leplugindujour_plugins_xml',
	'http://update.dotaddict.org/dc2/plugins.xml',
	'string','Plugins XML feed location',true,true);
$s->put('leplugindujour_day', '', 'string','',true,true);
$s->put('leplugindujour_plugin', '', 'string','',true,true);

$core->setVersion('lePluginDuJour',$m_version);
return true;