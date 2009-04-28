<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of dcWikipedia, a plugin for Dotclear.
# 
# Copyright (c) 2009 Tomtom
# http://blog.zenstyle.fr/
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')) { return; }

$popup = isset($_GET['popup']) ? true : false;

if ($popup) {
	require dirname(__FILE__).'/popup.php';
}

?>