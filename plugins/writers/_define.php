<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of Writers, a plugin for Dotclear.
#
# Copyright (c) 2003-2008 Olivier Meunier and contributors
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------
if (!defined('DC_RC_PATH')) {
  return null;
}

$this->registerModule(
	/* Name */			"Writers",
	/* Description*/		"Invite people to write on your blog",
	/* Author */			"Olivier Meunier",
	/* Version */			'1.2',
	/* Properties */
	array(
		'permissions' => 'admin',
		'type' => 'plugin',
		'dc_min' => '2.6',
		'support' => 'http://lab.dotclear.org/wiki/plugin/writers',
		'details' => 'http://plugins.dotaddict.org/dc2/details/writers'
		)
);