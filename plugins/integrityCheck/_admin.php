<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of Dotclear 2 "Integrity Check" plugin.
#
# Copyright (c) 2003-2010 DC Team
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------
if (!defined('DC_CONTEXT_ADMIN')) { return; }

$_menu['System']->addItem(__('Integrity Check'),'plugin.php?p=integrityCheck','images/check-on.png',
		preg_match('/plugin.php\?p=integrityCheck(&.*)?$/',$_SERVER['REQUEST_URI']),
		$core->auth->isSuperAdmin() && is_readable(DC_DIGESTS));
?>