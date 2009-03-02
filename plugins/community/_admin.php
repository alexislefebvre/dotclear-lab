<?php 
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of community, a plugin for Dotclear.
# 
# Copyright (c) 2009 Tomtom
# http://blog.zenstyle.fr/
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')) { return; }

require dirname(__FILE__).'/_widgets.php';

$_menu['System']->addItem(
	__('Community'),
	'plugin.php?p=community',
	'index.php?pf=community/icon.png',
	preg_match('/plugin.php\?p=community(&.*)?$/',
	$_SERVER['REQUEST_URI']),
	$core->auth->isSuperAdmin()
);

?>