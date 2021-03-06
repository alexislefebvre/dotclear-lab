<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of switchWelcome, a plugin for Dotclear.
# 
# Copyright (c) 2009-2015 Tomtom and contributors
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------
if (!defined('DC_RC_PATH')) {
  return null;
}
$this->registerModule(
		/* Name */			"switchWelcome",
		/* Description*/		"Welcome your visitors by a personnal message and help them to navigate",
		/* Author */			"Tomtom, Pierre Van Glabeke",
		/* Version */			'0.3',
	/* Properties */
	array(
		'permissions' => 'usage',
		'type' => 'plugin',
		'dc_min' => '2.6',
		'support' => 'http://lab.dotclear.org/wiki/plugin/switchWelcome',
		'details' => 'http://plugins.dotaddict.org/dc2/details/switchWelcome'
		)
);