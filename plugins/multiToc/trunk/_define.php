<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of multiToc, a plugin for Dotclear.
# 
# Copyright (c) 2009-2015 Tomtom and contributors
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

$this->registerModule(
		/* Name */			"multiToc",
		/* Description*/		"Makes posts or content's tables of content",
		/* Author */			"Tomtom, Kozlika, Franck Paul, Pierre Van Glabeke",
		/* Version */			'1.8',
	/* Properties */
	array(
		'permissions' => 'usage,contentadmin',
		'type' => 'plugin',
		'dc_min' => '2.7',
		'support' => 'http://lab.dotclear.org/wiki/plugin/multiToc/fr',
		'details' => 'http://plugins.dotaddict.org/dc2/details/multiToc'
		)
);