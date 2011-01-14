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

$p_url	= 'plugin.php?p=myGmaps';
$filters	= array(
	'user_id' => '',
	'cat_id' => '',
	'status' => '',
	'post_maps' => '',
	'month' => '',
	'lang' => '',
	'sortby' => '',
	'order' => '',
	'page' => '',
	'nb' => ''
);

$go = isset($_GET['go']) ? $_GET['go'] : 'maps';

if ($go === 'maps') {
	require_once dirname(__FILE__).'/maps.php';
}
elseif ($go === 'map') {
	require_once dirname(__FILE__).'/map.php';
}
elseif ($go === 'maps_actions') {
	require_once dirname(__FILE__).'/maps_actions.php';
}
elseif ($go === 'maps_popup') {
	require_once dirname(__FILE__).'/maps_popup.php';
}


?>