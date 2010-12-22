<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of dcDevKit, a plugin for Dotclear.
# 
# Copyright (c) 2010 Tomtom, Dsls and contributors
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')) { return; }

$_menu['Plugins']->addItem(__('Developers kit'),'plugin.php?p=dcDevKit','index.php?pf=dcDevKit/icon.png',
	preg_match('/plugin.php\?p=dcDevKit(&.*)?$/',$_SERVER['REQUEST_URI']),
	$core->auth->isSuperAdmin());

?>