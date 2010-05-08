<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of dcLog, a plugin for Dotclear.
# 
# Copyright (c) 2010 Tomtom
# http://blog.zenstyle.fr/
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------
if (!defined('DC_CONTEXT_ADMIN')) { return; }

$_menu['System']->addItem(__('Log'),'plugin.php?p=dcLog','index.php?pf=dcLog/icon.png',
		preg_match('/plugin.php\?p=dcLog(&.*)?$/',$_SERVER['REQUEST_URI']),
		$core->auth->isSuperAdmin());

?>