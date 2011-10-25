<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of myGmaps, a plugin for Dotclear 2.
#
# Copyright (c) 2010 Philippe aka amalgame
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')) return;

/* Redirect
-------------------------------------------------------- */

if (!isset($_REQUEST['post_id'])) {
	require_once dirname(__FILE__).'/config.php';
} else {
	require_once dirname(__FILE__).'/addbutton.php';
}
?>