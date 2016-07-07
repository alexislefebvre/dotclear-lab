<?php
# ***** BEGIN LICENSE BLOCK *****
# This file is part of robotsTxt, a plugin for DotClear2.
# Copyright (c) 2008 William Dauchy and contributors. All rights
# reserved.
#
# This plugin is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 2 of the License, or
# (at your option) any later version.
#
# This plugin is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this plugin; if not, write to the Free Software
# Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
#
# ***** END LICENSE BLOCK *****

$this->registerModule(
/* Name */                      "robotsTxt",
/* Description*/                "robots.txt file generator",
/* Author */                    "William Dauchy and contributors",
/* Version */                   '0.3',
	/* Properties */
	array(
		'permissions' => 'contentadmin',
		'type' => 'plugin',
		'dc_min' => '2.9',
		'support' => 'https://forum.dotclear.org/viewforum.php?id=16',
		'details' => 'http://lab.dotclear.org/wiki/plugin/robotsTxt'
		)
);