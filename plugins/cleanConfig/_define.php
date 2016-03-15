<?php 
# ***** BEGIN LICENSE BLOCK *****
#
# This file is part of clean:config, a plugin for Dotclear 2
# Copyright (C) 2007-2016 Moe (http://gniark.net/)
#
# clean:config is free software; you can redistribute it and/or
# modify it under the terms of the GNU General Public License v2.0
# as published by the Free Software Foundation.
#
# clean:config is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public
# License along with this program. If not, see
# <http://www.gnu.org/licenses/>.
#
# Icon (icon.png) and images are from Silk Icons :
# <http://www.famfamfam.com/lab/icons/silk/>
#
# ***** END LICENSE BLOCK *****

if (!defined('DC_RC_PATH')) {return;}

$this->registerModule(
	/* Name        */		"clean:config",
	/* Description */		"Delete blog and global settings",
	/* Author      */		"Moe (http://gniark.net/)",
	/* Version     */		"1.4.4",
	/* Properties */
	array(
		'permissions' => null,
		'type' => 'plugin',
		'dc_min' => '2.9',
		'support' => 'http://lab.dotclear.org/wiki/plugin/cleanConfig',
		'details' => 'http://plugins.dotaddict.org/dc2/details/cleanConfig'
		)
);