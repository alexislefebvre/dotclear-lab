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

if (!defined('DC_CONTEXT_ADMIN')) { return; }

$_menu['Plugins']->addItem(

	__('ColorBox'),
	'plugin.php?p=colorbox','index.php?pf=colorbox/icon.png',
	preg_match('/plugin.php\?p=colorbox(&.*)?$/',$_SERVER['REQUEST_URI']),
	$core->auth->check('admin',$core->blog->id));

?>