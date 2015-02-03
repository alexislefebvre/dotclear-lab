<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of dayMode, a plugin for Dotclear 2.
#
# Copyright (c) 2006-2010 Pep and contributors
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------
if (!defined('DC_RC_PATH')) return;

$this->registerModule(
	/* Name */		"dayMode",
	/* Description*/	"Provides daily archives and an associated calendar widget",
	/* Author */		"Pep and contributors",
	/* Version */		'1.1-RC3',
	/* Properties */
	array(
		'permissions' => 'admin',
		'type' => 'plugin',
		'dc_min' => '2.7',
		'support' => 'http://lab.dotclear.org/wiki/plugin/dayMode',
		'details' => 'http://plugins.dotaddict.org/dc2/details/dayMode'
	)
);