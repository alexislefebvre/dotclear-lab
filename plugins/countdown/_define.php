<?php 
# ***** BEGIN LICENSE BLOCK *****
#
# This file is part of CountDown, a plugin for Dotclear 2
# Copyright 2007,2010 Moe (http://gniark.net/)
#
# CountDown is free software; you can redistribute it and/or
# modify it under the terms of the GNU General Public License v2.0
# as published by the Free Software Foundation.
#
# CountDown is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public
# License along with this program. If not, see
# <http://www.gnu.org/licenses/>.
#
# ***** END LICENSE BLOCK *****

if (!defined('DC_RC_PATH')) {return;}

$this->registerModule(
	/* Name */					"CountDown",
	/* Description*/		"Countdown and stopwatch",
	/* Author */				"Moe (http://gniark.net/), Pierre Van Glabeke",
	/* Version */				'1.5',
	/* Properties */
	array(
		'permissions' => 'admin',
		'type' => 'plugin',
		'dc_min' => '2.9',
		'support' => 'http://lab.dotclear.org/wiki/plugin/countdown',
		'details' => 'http://plugins.dotaddict.org/dc2/details/countdown'
		)
);