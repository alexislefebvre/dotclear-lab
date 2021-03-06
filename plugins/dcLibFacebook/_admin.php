<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of dcLibFacebook, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2011 JC Denis and contributors
# jcdenis@gdwd.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')){return;}

# Plugin menu
$_menu['System']->addItem(
	__('API Facebook'),
	'plugin.php?p=dcLibFacebook','index.php?pf=dcLibFacebook/icon.png',
	preg_match('/plugin.php\?p=dcLibFacebook(&.*)?$/',$_SERVER['REQUEST_URI']),
	$core->auth->isSuperAdmin()
);
?>