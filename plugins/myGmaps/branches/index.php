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

$p_url = 'plugin.php?p=myGmaps';

$go = isset($_GET['go']) ? $_GET['go'] : 'maps';

if ($go === 'maps') {
	require_once dirname(__FILE__).'/maps.php';
}
elseif ($go === 'map') {
	require_once dirname(__FILE__).'/map.php';
}
elseif ($go === 'popup') {
	require_once dirname(__FILE__).'/popup.php';
}

?>