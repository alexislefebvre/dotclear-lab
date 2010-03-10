<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of Dotclear 2 Mystique Config plugin.
#
# Copyright (c) 2010 Bruno Hondelatte, and contributors. 
# Many, many thanks to Olivier Meunier and the Dotclear Team.
# This file is hugely inspired from blowupConfig admin page
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------
if (!defined('DC_CONTEXT_ADMIN')) { return; }

if (!empty($_REQUEST['m'])) {
	switch ($_REQUEST['m']) {
		case 'config' :
			require dirname(__FILE__).'/index_config.php';
			break;
	}
} else {
	require dirname(__FILE__).'/index_layout.php';
}
?>