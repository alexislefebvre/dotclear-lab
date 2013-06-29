<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of myBlogNumbers, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2013 Jean-Christian Denis and contributors
# contact@jcdenis.fr http://jcd.lv
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')){return;}

$core->addBehavior('initWidgets','myBlogNumbersWidgetAdmin');

function myBlogNumbersWidgetAdmin($w)
{
	global $core;
	
	$w->create('myblognumbers',__('My blog numbers'),
		'myBlogNumbersWidgetPublic');
	$w->myblognumbers->setting('title',__('Title:'),
		__('My blog numbers'),'text');
	
	# Entry
	$w->myblognumbers->setting('entry_show',__('Show entries count'),1,'check');
	$w->myblognumbers->setting('entry_title',__('Title for entries count:'),
		__('Entries:'),'text');
	
	# Cat
	$w->myblognumbers->setting('cat_show',__('Show categories count'),1,'check');
	$w->myblognumbers->setting('cat_title',__('Title for categories count:'),
		__('Categories:'),'text');
	
	# Comment
	$w->myblognumbers->setting('comment_show',__('Show comments count'),1,'check');
	$w->myblognumbers->setting('comment_title',__('Title for comments count:'),
		__('Comments:'),'text');
	
	# Trackback
	$w->myblognumbers->setting('trackback_show',__('Show trackbacks count'),1,'check');
	$w->myblognumbers->setting('trackback_title',__('Title for trackbacks count:'),
		__('Trackbacks:'),'text');
	
	if ($core->plugins->moduleExists('tags'))
	{
		# Tag
		$w->myblognumbers->setting('tag_show',__('Show tags count'),1,'check');
		$w->myblognumbers->setting('tag_title',__('Title for tags count:'),
			__('Tags:'),'text');
	}
	
	# Users (that post)
	$w->myblognumbers->setting('user_show',__('Show users count'),1,'check');
	$w->myblognumbers->setting('user_title',__('Title for users count:'),
		__('Authors:'),'text');
	
	# --BEHAVIOR-- myBlogNumbersWidgetInit
	$core->callBehavior('myBlogNumbersWidgetInit',$w);
	
	$w->myblognumbers->setting('homeonly',__('Display on:'),0,'combo',
		array(__('All pages') => 0, __('Home page only') => 1, __('Except on home page') => 2));
}
?>