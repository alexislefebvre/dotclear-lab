<?php
# ***** BEGIN LICENSE BLOCK *****
#
# This file is part of Clock, a plugin for Dotclear 2
# Copyright (C) 2007-2015 Moe (http://gniark.net/)
#
# Clock is free software; you can redistribute it and/or
# modify it under the terms of the GNU General Public License v2.0
# as published by the Free Software Foundation.
#
# Clock is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program; If not, see <http://www.gnu.org/licenses/>.
#
# ***** END LICENSE BLOCK *****
if (!defined('DC_RC_PATH')) {return;}

$this->registerModule(
    /* Name */             "Clock",
    /* Description*/       "Display the date of a time zone with the strftime() format in a widget",
    /* Author */           "Moe (http://gniark.net/)",
    /* Version */          '1.3',
	/* Properties */
	array(
		'permissions' => 'admin',
		'type' => 'plugin',
		'dc_min' => '2.7',
		'support' => 'http://lab.dotclear.org/wiki/plugin/clock',
		'details' => 'http://plugins.dotaddict.org/dc2/details/clock'
	)
);