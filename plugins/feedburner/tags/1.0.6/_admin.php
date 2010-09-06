<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of feedburner, a plugin for Dotclear.
# 
# Copyright (c) 2009-2010 Tomtom
# http://blog.zenstyle.fr/
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')) { return; }

$_menu['Plugins']->addItem(
	__('Feedburner'),
	'plugin.php?p=feedburner',
	'index.php?pf=feedburner/icon.png',
	preg_match('/plugin.php\?p=feedburner(&.*)?$/',
	$_SERVER['REQUEST_URI']),
	$core->auth->isSuperAdmin()
);

?>