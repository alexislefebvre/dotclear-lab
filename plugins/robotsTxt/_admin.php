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
if (!defined('DC_CONTEXT_ADMIN')) { return; }

$_menu['Blog']->addItem(__('robotsTxt'),'plugin.php?p=robotsTxt','index.php?pf=robotsTxt/icon.png',
		preg_match('/plugin.php\?p=robotsTxt(&.*)?$/',$_SERVER['REQUEST_URI']),
		$core->auth->check('contentadmin',$core->blog->id));

$core->addBehavior('adminDashboardFavorites','robotsTxtDashboardFavorites');

function robotsTxtDashboardFavorites($core,$favs)
{
	$favs->register('robotsTxt', array(
		'title' => __('robotsTxt'),
		'url' => 'plugin.php?p=robotsTxt',
		'small-icon' => 'index.php?pf=robotsTxt/icon.png',
		'large-icon' => 'index.php?pf=robotsTxt/icon-big.png',
		'permissions' => 'usage,contentadmin'
	));
}