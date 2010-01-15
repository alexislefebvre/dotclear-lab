<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of cinecturlink2, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2010 JC Denis and contributors
# jcdenis@gdwd.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')){return;}

require_once dirname(__FILE__).'/_widgets.php';

# Admin menu
$_menu['Plugins']->addItem(
	__('Cinecturlink 2'),
	'plugin.php?p=cinecturlink2','index.php?pf=cinecturlink2/icon.png',
	preg_match('/plugin.php\?p=cinecturlink2(&.*)?$/',$_SERVER['REQUEST_URI']),
	$core->auth->check('content',$core->blog->id));
?>