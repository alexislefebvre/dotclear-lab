<?php
# ***** BEGIN LICENSE BLOCK *****
#
# This file is part of My Dashboard, a plugin for Dotclear 2
# Copyright (C) 2009 Moe (http://gniark.net/)
#
# My Dashboard is free software; you can redistribute it and/or
# modify it under the terms of the GNU General Public License v2.0
# as published by the Free Software Foundation.
#
# My Dashboard is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program; if not, write to the Free Software Foundation,
# Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
#
# Icon (icon.png) is from Silk Icons : http://www.famfamfam.com/lab/icons/silk/
#
# ***** END LICENSE BLOCK *****

if (!defined('DC_CONTEXT_ADMIN')) {return;}

require_once(dirname(__FILE__).'/inc/lib.myDashboard.php');

$_menu['Plugins']->addItem(__('My Dashboard'),'plugin.php?p=myDashboard',
	'index.php?pf=myDashboard/icon.png',
	preg_match('/plugin.php\?p=myDashboard(&.*)?$/',$_SERVER['REQUEST_URI']),
	$core->auth->isSuperAdmin());

# dashboard
$core->addBehavior('adminDashboardIcons',
	array('myDashboard','adminDashboardIcons'));

?>