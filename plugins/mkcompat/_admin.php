<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of mkcompat, a plugin for Dotclear 2.
#
# Copyright (c) 2009 Dotclear Team and contributors
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------

$_menu['Plugins']->addItem(__('Compatibility 2.1.6'),'plugin.php?p=mkcompat','index.php?pf=mkcompat/icon.png',
		preg_match('/plugin.php\?p=mkcompat(&.*)?$/',$_SERVER['REQUEST_URI']),
		$core->auth->check('admin',$core->blog->id));
?>