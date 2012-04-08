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

if ($_REQUEST['do'] == 'edit') {
	$edit = 'map';
	
} elseif (isset($_GET['add_map_filters']) && $_REQUEST['do'] != 'list') {
	require_once dirname(__FILE__).'/addmap.php';
	
} elseif ($_REQUEST['do'] == 'list' || isset($_POST['saveconfig']) || isset($_GET['maps_filters'])) {
	$edit = 'maps';
	
} else {
	$actions = ($_REQUEST['action']) ? true : false;
}

if ($actions) {
	require_once dirname(__FILE__).'/maps_actions.php';

} elseif ($edit == 'map') {
	require_once dirname(__FILE__).'/map.php';

} elseif ($edit == 'maps') {
	require_once dirname(__FILE__).'/maps.php';

} else {
	require_once dirname(__FILE__).'/addmap.php';
}
?>