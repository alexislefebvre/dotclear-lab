<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of Dotclear 2 "Fake Me Up" plugin.
#
# Copyright (c) 2010-2015 Bruno Hondelatte, and contributors.
# Many, many thanks to Olivier Meunier and the Dotclear Team.
# Licensed under the GPL version 2.0 license.
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------
if (!defined('DC_RC_PATH')) { return; }

$this->registerModule(
	/* Name */			"Fake Me Up",
	/* Description*/		"Fakes Dotclear digest to force automatic updates",
	/* Author */			"Bruno Hondelatte",
	/* Version */			'1.6',
	/* Properties */
	array(
		'permissions' => 'admin',
		'type' => 'plugin',
		'dc_min' => '2.3',
		'support' => 'http://forum.dotclear.org/viewtopic.php?pid=310117',
		'details' => 'http://plugins.dotaddict.org/dc2/details/fakemeup'
		)
);