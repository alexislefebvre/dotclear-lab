<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of kezako, a plugin for Dotclear 2.
# 
# Copyright (c) 2010 Franck Paul and contributors
# carnet.franck.paul@gmail.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')) {return;}

$_menu['Blog']->addItem(__('Kezako'),
	'plugin.php?p=kezako',
	'index.php?pf=kezako/icon.png',
	preg_match('/plugin.php\?p=kezako/',$_SERVER['REQUEST_URI']),
	$core->auth->check('editor',$core->blog->id));

$core->auth->setPermissionType('editor',__('manage translations and descriptions'));

?>