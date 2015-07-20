<?php 
# ***** BEGIN LICENSE BLOCK *****
#
# This file is part of Widgets to Template, a plugin for Dotclear 2
# Copyright 2010 Moe (http://gniark.net/)
#
# Widgets to Template is free software; you can redistribute it and/or
# modify it under the terms of the GNU General Public License v2.0
# as published by the Free Software Foundation.
#
# Widgets to Template is distributed in the hope that it will be useful,
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
  /* Name */				'Widgets to Template',
  /* Description*/	'Get widget code for templates',
  /* Author */			'Moe (http://gniark.net/)',
  /* Version */			'0.2',
	/* Properties */
	array(
		'permissions' => 'admin',
		'type' => 'plugin',
		'dc_min' => '2.6',
		'support' => 'http://lab.dotclear.org/wiki/plugin/widgets2Tpl',
		'details' => 'http://lab.dotclear.org/wiki/plugin/widgets2Tpl'
	)
);