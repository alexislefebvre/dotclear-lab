<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of tinyPacker, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2016 Jean-Christian Denis and contributors
# contact@jcdenis.fr http://jcdenis.net
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')) {

	return null;
}

$this->registerModule(
	/* Name */
	"Tiny packer",
	/* Description*/
	"Quick pack theme or plugin into public dir",
	/* Author */
	"Jean-Christian Denis",
	/* Version */
	'0.3',
	/* Properties */
	array(
		'permissions' => null,
		'type' => 'plugin',
		'dc_min' => '2.6',
		'support' => 'http://jcd.lv?q=tinyPacker',
		'details' => 'http://plugins.dotaddict.org/dc2/details/tinyPacker'
	)
);
