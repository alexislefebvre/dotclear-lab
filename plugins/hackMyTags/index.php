<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of hackMyTags,
# a plugin for DotClear2.
#
# Copyright (c) 2008 Bruno Hondelatte and contributors
#
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/gpl-2.0.txt
#
# -- END LICENSE BLOCK ------------------------------------
if (!defined('DC_CONTEXT_ADMIN')) { exit; }

$dcHackMyTags = new dcHackMyTags($core);

if (!empty($_REQUEST['m'])) {
	switch ($_REQUEST['m']) {
		case 'edit' :
			require dirname(__FILE__).'/index_edit.php';
			break;
	}
} else {
	require dirname(__FILE__).'/index_home.php';
}
?>