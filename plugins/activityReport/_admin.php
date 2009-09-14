<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of activityReport, a plugin for Dotclear 2.
#
# Copyright (c) 2009 JC Denis and contributors
# jcdenis@gdwd.com
#
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')){return;}

# Plugin menu
$_menu['Plugins']->addItem(
	__('Activity report'),
	'plugin.php?p=activityReport','index.php?pf=activityReport/icon.png',
	preg_match('/plugin.php\?p=activityReport(&.*)?$/',
	$_SERVER['REQUEST_URI']),
	$core->auth->check('admin',$core->blog->id)
);
?>