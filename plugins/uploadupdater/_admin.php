<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of Dotclear 2 "Upload Updater" plugin.
#
# Copyright (c) 2003-2010 DC Team
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------

$_menu['System']->addItem(__('Upload Update'),'plugin.php?p=uploadupdater','images/menu/update.png',
		preg_match('/plugin.php\?p=uploadupdater(&.*)?$/',$_SERVER['REQUEST_URI']),
		$core->auth->isSuperAdmin() && is_readable(DC_DIGESTS));
?>