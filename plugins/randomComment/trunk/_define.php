<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of randomComment, a plugin for Dotclear.
# 
# Copyright (c) 2009-2015 Tomtom and contributors
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')) { return; }

$this->registerModule(
		/* Name */			'randomComment',
		/* Description */		'Display random comments on your blog',
		/* Author */			'Tomtom, Pierre Van Glabeke',
		/* Version */			'1.2',
	/* Properties */
	array(
		'permissions' => 'usage',
		'type' => 'plugin',
		'dc_min' => '2.6',
		'support' => 'http://lab.dotclear.org/wiki/plugin/randomComment',
		'details' => 'http://plugins.dotaddict.org/dc2/details/randomComment'
		)
);