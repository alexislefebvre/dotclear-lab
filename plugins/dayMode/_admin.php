<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of dayMode, a plugin for Dotclear 2.
#
# Copyright (c) 2006-2009 Pep and contributors
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------
if (!defined('DC_CONTEXT_ADMIN')) { return; }

require_once dirname(__FILE__).'/_widgets.php';

$_menu['Plugins']->addItem('dayMode','plugin.php?p=dayMode','index.php?pf=dayMode/icon.png',
		preg_match('/plugin.php\?p=dayMode(&.*)?$/',$_SERVER['REQUEST_URI']),
		$core->auth->isSuperAdmin());
?>