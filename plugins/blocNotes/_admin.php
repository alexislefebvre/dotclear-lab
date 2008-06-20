<?php
# ***** BEGIN LICENSE BLOCK *****
#
# This file is part of Bloc-Notes.
# Copyright 2008 Moe (http://gniark.net/)
#
# Bloc-Notes is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 3 of the License, or
# (at your option) any later version.
#
# Bloc-Notes is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program.  If not, see <http://www.gnu.org/licenses/>.
#
# Icons (*.png) are from Tango Icon theme : http://tango.freedesktop.org/Tango_Icon_Gallery
#
# ***** END LICENSE BLOCK *****

	require_once(dirname(__FILE__).'/lib.blocNotes.php');

	# dashboard
	if ($core->auth->check('usage,contentadmin',$core->blog->id))
	{
		# <= 2.0-beta7
		if (isset($__dashboard_icons)) {
			$__dashboard_icons[] = array(__('Notebook'),'plugin.php?p=blocNotes','index.php?pf=blocNotes/icon-big.png');
		}
		# > 2.0-beta7
		$core->addBehavior('adminDashboardIcons',array('blocNotes','adminDashboardIcons'));
	}

	# post
	$core->addBehavior('adminPostForm',array('blocNotes','form'));
	$core->addBehavior('adminAfterPostCreate',array('blocNotes','putSettings'));
	$core->addBehavior('adminAfterPostUpdate',array('blocNotes','putSettings'));
	$core->addBehavior('adminPostHeaders',array('blocNotes','adminPostHeaders'));

	$_menu['Plugins']->addItem(__('Notebook'),
	'plugin.php?p=blocNotes',
	'index.php?pf=blocNotes/icon.png',
	preg_match('/plugin.php\?p=blocNotes(&.*)?$/',$_SERVER['REQUEST_URI']),
	$core->auth->check('usage,contentadmin',$core->blog->id));
?>