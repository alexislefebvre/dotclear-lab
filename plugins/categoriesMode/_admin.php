<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of categoriesMode, a plugin for Dotclear 2.
#
# Copyright (c) 2007-2011 Adjaya and contributors
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------
if (!defined('DC_CONTEXT_ADMIN')) { return; }

require_once dirname(__FILE__).'/_widgets.php';

$_menu['Blog']->addItem('categoriesMode','plugin.php?p=categoriesMode','index.php?pf=categoriesMode/icon.png',
		preg_match('/plugin.php\?p=categoriesMode(&.*)?$/',$_SERVER['REQUEST_URI']),
		$core->auth->isSuperAdmin());
?>