<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of licenseBootstrap, a plugin for Dotclear 2.
# 
# Copyright (c) 2009 JC Denis and contributors
# jcdenis@gdwd.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')){return;}

# Admin menu
$_menu['Plugins']->addItem(
	__('License bootstrap'),
	'plugin.php?p=licenseBootstrap','index.php?pf=licenseBootstrap/icon.png',
	preg_match('/plugin.php\?p=licenseBootstrap(&.*)?$/',$_SERVER['REQUEST_URI']),
	$core->auth->check('admin',$core->blog->id));
?>