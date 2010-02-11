<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of xiti, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2010 JC Denis and contributors
# jcdenis@gdwd.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')){return;}
require dirname(__FILE__).'/_widgets.php';

$_menu['Plugins']->addItem(
	__('XITI'),
	'plugin.php?p=xiti','index.php?pf=xiti/icon.png',
	preg_match('/plugin.php\?p=xiti(&.*)?$/',$_SERVER['REQUEST_URI']),
	$core->auth->check('admin',$core->blog->id)
);
?>