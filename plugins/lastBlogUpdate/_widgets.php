<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of lastBlogUpdate, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2013 Jean-Christian Denis and contributors
# contact@jcdenis.fr http://jcd.lv
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')) {

	return null;
}

$core->addBehavior('initWidgets', 'lastBlogUpdateWidgetAdmin');

function lastBlogUpdateWidgetAdmin($w)
{
	global $core;
	
	$w->create(
		'lastblogupdate',
		__('Dates of last updates'),
		'lastBlogUpdateWidgetPublic',
		null,
		"Show the dates of last updates of your blog in a widget"
	);
	$w->lastblogupdate->setting(
		'title',
		__('Title:'),
		__('Dates of last updates'),
		'text'
	);
	$w->lastblogupdate->setting(
		'blog_show',
		__('Show blog update'),
		1,
		'check'
	);
	$w->lastblogupdate->setting(
		'blog_title',
		__('Title for blog update:'),
		__('Blog:'),
		'text'
	);
	$w->lastblogupdate->setting(
		'blog_text',
		__('Text for blog update:'),
		__('%Y-%m-%d %H:%M'),
		'text'
	);
	
	$w->lastblogupdate->setting(
		'post_show',
		__('Show entry update'),
		1,
		'check'
	);
	$w->lastblogupdate->setting(
		'post_title',
		__('Title for entries update:'),
		__('Entries:'),
		'text'
	);
	$w->lastblogupdate->setting(
		'post_text',
		__('Text for entries update:'),
		__('%Y-%m-%d %H:%M'),
		'text'
	);
	
	$w->lastblogupdate->setting(
		'comment_show',
		__('Show comment update'),
		1,
		'check'
	);
	$w->lastblogupdate->setting(
		'comment_title',
		__('Title for comments update:'),
		__('Comments:'),
		'text'
	);
	$w->lastblogupdate->setting(
		'comment_text',
		__('Text for comments update:'),
		__('%Y-%m-%d %H:%M'),
		'text'
	);
	
	$w->lastblogupdate->setting(
		'media_show',
		__('Show media update'),
		1,
		'check'
	);
	$w->lastblogupdate->setting(
		'media_title',
		__('Title for media update:'),
		__('Medias:'),
		'text'
	);
	$w->lastblogupdate->setting(
		'media_text',
		__('Text for media update:'),
		__('%Y-%m-%d %H:%M'),
		'text'
	);

	# --BEHAVIOR-- lastBlogUpdateWidgetInit
	$core->callBehavior('lastBlogUpdateWidgetInit', $w);

	$w->lastblogupdate->setting(
		'homeonly',
		__('Display on:'),
		0,
		'combo',
		array(
			__('All pages') => 0,
			__('Home page only') => 1,
			__('Except on home page') => 2
		)
	);
	$w->lastblogupdate->setting(
		'content_only',
		__('Content only'),
		0,
		'check'
	);
	$w->lastblogupdate->setting(
		'class',
		__('CSS class:'),
		''
	);
}
