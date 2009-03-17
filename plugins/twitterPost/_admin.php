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
# This file is part of TwitterPost
# Hadrien Lanneau http://www.alti.info/pages/TwitterPost
#
# ***** END LICENSE BLOCK *****
include_once(dirname(__FILE__) . '/inc/TwitterPost.class.php');
$core->addBehavior(
	'adminPostFormSidebar',
	array(
		'TwitterPost',
		'initPostFormSidebar'
	)
);

$core->addBehavior(
	'adminAfterPostCreate',
	array(
		'TwitterPost',
		'adminBeforePostUpdate'
	)
);
$core->addBehavior(
	'adminAfterPostUpdate',
	array(
		'TwitterPost',
		'adminBeforePostUpdate'
	)
);

$_menu['Plugins']->addItem(
	'Twitter Post',
	'plugin.php?p=twitterPost',
	'index.php?pf=twitterPost/img/icon_16.png',
	preg_match(
		'/plugin.php\?p=twitterPost(&.*)?$/',
		$_SERVER['REQUEST_URI']
	),
	$core->auth->check(
		'usage,contentadmin',
		$core->blog->id
	)
);
