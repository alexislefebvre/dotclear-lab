<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of dcLibFoursquare, a plugin for Dotclear 2.
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
	__('API Foursquare'),
	'plugin.php?p=dcLibFoursquare','index.php?pf=dcLibFoursquare/icon.png',
	preg_match('/plugin.php\?p=dcLibFoursquare(&.*)?$/',$_SERVER['REQUEST_URI']),
	$core->auth->isSuperAdmin()
);
?>