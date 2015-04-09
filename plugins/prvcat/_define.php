<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# Copyright (c) 2010-2015 Arnaud Renevier
# published under the modified BSD license.
# -- END LICENSE BLOCK ------------------------------------
if (!defined('DC_RC_PATH')) { return; }

$this->registerModule(
	/* Name */			"prvcat",
	/* Description*/		"Make private all tickets a category",
	/* Author */			"Arno Renevier",
	/* Version */			'0.2.4',
	/* Properties */
	array(
		'permissions' => 'categories',
		'type' => 'plugin',
		'dc_min' => '2.6',
		'support' => 'http://forum.dotclear.org/viewtopic.php?id=42409',
		'details' => 'http://lab.dotclear.org/wiki/plugin/prvcat/fr'
		)
);