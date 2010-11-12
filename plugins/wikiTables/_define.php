<?php
# ***** BEGIN LICENSE BLOCK *****
#
# This file is part of Wiki Tables, a plugin for Dotclear 2
# Copyright (C) 2009,2010 Moe (http://gniark.net/)
#
# Wiki Tables is free software; you can redistribute it and/or
# modify it under the terms of the GNU General Public License v2.0
# as published by the Free Software Foundation.
#
# Wiki Tables is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program; if not, write to the Free Software Foundation,
# Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
#
# Icon (icon.png) is from Silk Icons :
# http://www.famfamfam.com/lab/icons/silk/
#
# ***** END LICENSE BLOCK *****

if (!defined('DC_RC_PATH')) {return;}

$this->registerModule(
	/* Name */					'Wiki Tables',
	/* Description*/		'Create XHTML <table>s in Wiki with Wikimedia syntax',
	/* Author */				'Moe (http://gniark.net/)',
	/* Version */				'0.1.1',
	/* Permissions */		'usage,contentadmin'
);

?>