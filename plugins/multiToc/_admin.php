<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of plugin multiToc for Dotclear 2.
# Copyright (c) 2008 Thomas Bouron and contributors.
#
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')) { return; }

$_menu['Plugins']->addItem(
	__('MultiToc'),
	'plugin.php?p=multiToc',
	'index.php?pf=multiToc/icon.png',
	preg_match('/plugin.php\?p=multiToc(&.*)?$/',
	$_SERVER['REQUEST_URI']),
	$core->auth->isSuperAdmin()
);

require dirname(__FILE__).'/_widgets.php';

?>