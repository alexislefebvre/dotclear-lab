<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of My URL handlers, a plugin for Dotclear.
# 
# Copyright (c) 2007-2008 Oleksandr Syenchuk
# <sacha@xn--phnix-csa.net>
# 
# Licensed under the GPL version 2.0 license.
# A copy is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')) { return; }

$label = basename(dirname(__FILE__));
$m_version = $core->plugins->moduleInfo($label,'version');
$i_version = $core->getVersion($label);

if (version_compare($i_version,$m_version,'>=')) {
	return;
}

$settings =& $core->blog->settings;
$settings->setNamespace(strtolower($label));
$settings->put('url_handlers','','string','Personalized URL handlers',false);

$core->setVersion($label,$m_version);
return true;
?>