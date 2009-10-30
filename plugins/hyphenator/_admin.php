<?php 
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of Hyphenator plugin for Dotclear 2.
#
# Copyright (c) 2009 kévin Lepeltier and contributors
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------

/* Add menu item in extension list */
$_menu['Plugins']->addItem(__('Hyphenator'),'plugin.php?p=hyphenator','index.php?pf=hyphenator/icon.png',
		preg_match('/plugin.php\?p=hyphenator(&.*)?$/',$_SERVER['REQUEST_URI']),
		$core->auth->check('contentadmin',$core->blog->id));