<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of enhancePostContent, a plugin for Dotclear 2.
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

# Admin menu
$_menu['Plugins']->addItem(
	__('Enhance post content'),
	'plugin.php?p=enhancePostContent','index.php?pf=enhancePostContent/icon.png',
	preg_match('/plugin.php\?p=enhancePostContent(&.*)?$/',$_SERVER['REQUEST_URI']),
	$core->auth->check('content',$core->blog->id));
?>