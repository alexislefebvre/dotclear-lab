<?php 
# ***** BEGIN LICENSE BLOCK *****
#
# This file is part of Versions Manager, a plugin for Dotclear 2
# Copyright 2010-2015 Moe (http://gniark.net/)
#
# Versions Manager is free software; you can redistribute it and/or
# modify it under the terms of the GNU General Public License v2.0
# as published by the Free Software Foundation.
#
# Versions Manager is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public
# License along with this program. If not, see
# <http://www.gnu.org/licenses/>.
#
# ***** END LICENSE BLOCK *****

if (!defined('DC_CONTEXT_ADMIN')) {exit;}

$_menu['Plugins']->addItem(__('Versions manager'),
	'plugin.php?p=versionsManager',
	'index.php?pf=versionsManager/icon.png',
	preg_match('/plugin.php\?p=versionsManager(&.*)?$/',
	$_SERVER['REQUEST_URI']),$core->auth->check('admin',$core->blog->id));

$core->addBehavior('adminDashboardFavorites','versionsManagerDashboardFavorites');

function versionsManagerDashboardFavorites($core,$favs)
{
	$favs->register('versionsManager', array(
		'title' => __('Versions manager'),
		'url' => 'plugin.php?p=versionsManager',
		'small-icon' => 'index.php?pf=versionsManager/icon.png',
		'large-icon' => 'index.php?pf=versionsManager/icon-big.png',
		'permissions' => 'usage,contentadmin'
	));
}