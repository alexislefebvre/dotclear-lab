<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of multiToc, a plugin for Dotclear.
# 
# Copyright (c) 2009-2010 Tomtom and contributors
# http://blog.zenstyle.fr/
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')) { return; }

$core->addBehavior('adminPostHeaders',array('multiTocBehaviors','postHeaders'));
$core->addBehavior('adminPageHeaders',array('multiTocBehaviors','postHeaders'));

$_menu['Plugins']->addItem(
	__('Tables of content'),
	'plugin.php?p=multiToc',
	'index.php?pf=multiToc/icon.png',
	preg_match('/plugin.php\?p=multiToc(&.*)?$/',
	$_SERVER['REQUEST_URI']),
	$core->auth->isSuperAdmin()
);

?>