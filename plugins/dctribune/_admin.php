<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of dctribune, a plugin for Dotclear 2.
# 
# Copyright (c) 2009 Osku  and contributors
# Many thanks to Pep, Tomtom and JcDenis
# Originally from Antoine Libert
#
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------
if (!defined('DC_CONTEXT_ADMIN')) { return; }

$_menu['Plugins']->addItem(__('Free chatbox'),
			'plugin.php?p=dctribune','index.php?pf=dctribune/icon-small.png',
			preg_match('/plugin.php\?p=dctribune(&.*)?$/',$_SERVER['REQUEST_URI']),
			$core->auth->check('contentadmin,tribune',$core->blog->id));

$core->auth->setPermissionType('tribune',__('manage chatbox'));
?>
