<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of pacKman, a plugin for Dotclear 2.
#
# Copyright (c) 2009 JC Denis and contributors
# jcdenis@gdwd.com
#
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')) return;

# Admin menu
$_menu['Plugins']->addItem(
	__('pacKman'),
	'plugin.php?p=pacKman','index.php?pf=pacKman/icon.png',
	preg_match('/plugin.php\?p=pacKman(&.*)?$/',$_SERVER['REQUEST_URI']),
	$core->auth->isSuperAdmin());

# Plugins tab
if ($core->blog->settings->packman_menu_plugins && $core->auth->isSuperAdmin())
	$core->addBehavior('pluginsToolsTabs','packmanPluginsToolsTabs');

function packmanPluginsToolsTabs($core)
{
	libPackman::tab($core->plugins->getModules(),'plugins','plugins.php');
}
?>