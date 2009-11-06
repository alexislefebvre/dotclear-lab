<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of usersManagement, a plugin for Dotclear 2.
#
# Copyright (c) 2009 Johan Pustoch and contributors
# johan.pustoch@crdp.ac-versailles.fr
#
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------


$_menu['Blog']->addItem(__('blogUsers'),'plugin.php?p=usersManagement','index.php?pf=usersManagement/icon.png',
		preg_match('/plugin.php\?p=usersManagement(&.*)?$/',$_SERVER['REQUEST_URI']),
		$core->auth->check('admin',$core->blog->id));
?>