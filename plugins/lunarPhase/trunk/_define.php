<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of lunarPhase, a plugin for Dotclear.
# 
# Copyright (c) 2009-2014 Tomtom
# Contributor: Pierre Van Glabeke
#
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')) { return; }

$this->registerModule(
	/* Name */			     'lunarPhase',
	/* Description */		 'Display the moon phases',
	/* Author */			   'Tomtom, Pierre Van Glabeke',
	/* Verion */			   '1.2',
	/* Properties */
	array(
		'permissions' => 'usage,contentadmin',
		'type' => 'plugin',
		'dc_min' => '2.6',
		'support' => 'http://lab.dotclear.org/wiki/plugin/lunarPhase',
		'details' => 'http://plugins.dotaddict.org/dc2/details/lunarPhase'
	)
);
