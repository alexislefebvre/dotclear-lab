<?php
# ***** BEGIN LICENSE BLOCK *****
# This file is part of DotClear.
# Copyright (c) 2007 Olivier Meunier and contributors.
# All rights reserved.
#
# DotClear is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 2 of the License, or
# (at your option) any later version.
# 
# DotClear is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
# 
# You should have received a copy of the GNU General Public License
# along with DotClear; if not, write to the Free Software
# Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
#
# ***** END LICENSE BLOCK *****
# This file is part of TwitterTrackback
# Hadrien Lanneau http://www.alti.info/pages/TwitterTrackback-extension-pour-trackbacker-les-tweets-retrolien-plugin-dotclear
#

include_once(dirname(__FILE__) . '/inc/TwitterTrackback.class.php');

$_menu['Plugins']->addItem(
	'Twitter Trackback',
	'plugin.php?p=twitterTrackback',
	'index.php?pf=twitterTrackback/img/icon_16.png',
	preg_match(
		'/plugin.php\?p=twitterTrackback(&.*)?$/',
		$_SERVER['REQUEST_URI']
	),
	$core->auth->check(
		'usage,contentadmin',
		$core->blog->id
	)
);
