<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of joliprint, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2011 JC Denis and contributors
# jcdenis@gdwd.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')){return;}

# Settings NS
$core->blog->settings->addNamespace('joliprint');

# Plugin menu
$_menu['Plugins']->addItem(
	__('Joliprint'),
	'plugin.php?p=joliprint','index.php?pf=joliprint/icon.png',
	preg_match('/plugin.php\?p=joliprint(&.*)?$/',$_SERVER['REQUEST_URI']),
	$core->auth->check('admin',$core->blog->id)
);

# Widget
require_once dirname(__FILE__).'/_widgets.php';
?>