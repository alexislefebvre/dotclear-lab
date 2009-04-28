<?php
# ***** BEGIN LICENSE BLOCK *****
# This file is part of iPhoneView.
# Copyright (c) 2009 Hadrien Lanneau.
# All rights reserved.
#
# Pixearch is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 2 of the License, or
# (at your option) any later version.
# 
# iPhoneView is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
# 
# You should have received a copy of the GNU General Public License
# along with iPhoneView; if not, write to the Free Software
# Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
#
# http://www.alti.info/pages/iPhoneView
#
# ***** END LICENSE BLOCK *****

$core->url->register(
	'iphone-home',
	'iphone-home',
	'^iphone/?$',
	array(
		'iPhoneViewUrls',
		'home'
	)
);
$core->url->register(
	'iphone-home-page',
	'iphone-home-page',
	'^iphone/page/(.+)$',
	array(
		'iPhoneViewUrls',
		'home'
	)
);

$core->url->register(
	'iphone-post',
	'iphone-post',
	'^iphone/post/(.+)$',
	array(
		'iPhoneViewUrls',
		'post'
	)
);

$core->url->register(
	'iphone-category',
	'iphone-category',
	'^iphone/category/(.+)$',
	array(
		'iPhoneViewUrls',
		'category'
	)
);

$core->url->register(
	'iphone-category-page',
	'iphone-category-page',
	'^iphone/category/((.+)/page/(.+))$',
	array(
		'iPhoneViewUrls',
		'category'
	)
);

$core->url->register(
	'iphone-tag',
	'iphone-tag',
	'^iphone/tag/(.+)$',
	array(
		'iPhoneViewUrls',
		'tag'
	)
);

$core->url->register(
	'iphone-tag-page',
	'iphone-tag-page',
	'^iphone/tag/((.+)/page/(.+))$',
	array(
		'iPhoneViewUrls',
		'tag'
	)
);

