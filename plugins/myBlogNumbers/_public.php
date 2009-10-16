<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of myBlogNumbers, a plugin for Dotclear 2.
# 
# Copyright (c) 2009 JC Denis and contributors
# jcdenis@gdwd.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')){return;}

require dirname(__FILE__).'/_widgets.php';

function myBlogNumbersWidgetPublic($w)
{
	global $core;

	$content = $addons = '';

	# Home only
	if ($w->homeonly && $core->url->type != 'default') return;

	# Entry
	if ($w->entry_show)
	{
		$title = ($w->entry_title ? 
			'<strong>'.html::escapeHTML($w->entry_title).'</strong> ' : '');

		$count = $core->blog->getPosts(array(),true)->f(0);

		if ($count == 0) {
			$text = sprintf(__('none'),$count);
		}
		elseif ($count == 1) {
			$text = sprintf(__('one entry'),$count);
		}
		else {
			$text = sprintf(__('%s entries'),$count);
		}

		$content .= sprintf('<li>%s%s</li>',$title,$text);
	}

	# Cat
	if ($w->cat_show)
	{
		$title = ($w->cat_title ? 
			'<strong>'.html::escapeHTML($w->cat_title).'</strong> ' : '');

		$count = $core->blog->getCategories(array())->count();

		if ($count == 0) {
			$text = sprintf(__('none'),$count);
		}
		elseif ($count == 1) {
			$text = sprintf(__('one category'),$count);
		}
		else {
			$text = sprintf(__('%s categories'),$count);
		}

		$content .= sprintf('<li>%s%s</li>',$title,$text);
	}

	# Comment
	if ($w->comment_show)
	{
		$title = ($w->comment_title ? 
			'<strong>'.html::escapeHTML($w->comment_title).'</strong> ' : '');
		
		$params = array(
			'post_type' => 'post',
			'comment_status' => 1,
			'comment_trackback' => 0
		);
		$count = $core->blog->getComments($params,true)->f(0);

		if ($count == 0) {
			$text = sprintf(__('none'),$count);
		}
		elseif ($count == 1) {
			$text = sprintf(__('one comment'),$count);
		}
		else {
			$text = sprintf(__('%s comments'),$count);
		}

		$content .= sprintf('<li>%s%s</li>',$title,$text);
	}

	# Trackback
	if ($w->trackback_show)
	{
		$title = ($w->trackback_title ? 
			'<strong>'.html::escapeHTML($w->trackback_title).'</strong> ' : '');

		$params = array(
			'post_type' => 'post',
			'comment_status' => 1,
			'comment_trackback' => 1
		);
		$count = $core->blog->getComments($params,true)->f(0);

		if ($count == 0) {
			$text = sprintf(__('none'),$count);
		}
		elseif ($count == 1) {
			$text = sprintf(__('one trackback'),$count);
		}
		else {
			$text = sprintf(__('%s trackbacks'),$count);
		}

		$content .= sprintf('<li>%s%s</li>',$title,$text);
	}

	# Tag
	if ($core->plugins->moduleExists('metadata') && $w->tag_show)
	{
		$title = ($w->tag_title ? 
			'<strong>'.html::escapeHTML($w->tag_title).'</strong> ' : '');

		$count = $core->con->select(
			'SELECT count(M.meta_id) AS count '.
			'FROM '.$core->prefix.'meta M '.
			'LEFT JOIN '.$core->prefix.'post P ON P.post_id=M.post_id '.
			"WHERE M.meta_type='tag' ".
			"AND P.blog_id='".$core->blog->id."' "
		)->f(0);

		if ($count == 0) {
			$text = sprintf(__('none'),$count);
		}
		elseif ($count == 1) {
			$text = sprintf(__('one tag'),$count);
		}
		else {
			$text = sprintf(__('%s tags'),$count);
		}

		$content .= sprintf('<li>%s%s</li>',$title,$text);
	}

	# User (that post)
	if ($w->user_show)
	{
		$title = ($w->user_title ? 
			'<strong>'.html::escapeHTML($w->user_title).'</strong> ' : '');

		$count = $core->blog->getPostsUsers(array(),true)->count();

		if ($count == 0) {
			$text = sprintf(__('none'),$count);
		}
		elseif ($count == 1) {
			$text = sprintf(__('one author'),$count);
		}
		else {
			$text = sprintf(__('%s authors'),$count);
		}

		$content .= sprintf('<li>%s%s</li>',$title,$text);
	}


	# --BEHAVIOR-- myBlogNumbersWidgetParse
	$addons = $core->callBehavior('myBlogNumbersWidgetParse',$core,$w);


	# Nothing to display
	if (!$content && !$addons) return;

	# Display
	return 
	'<div class="myblognumbers">'.
	($w->title ? '<h2>'.html::escapeHTML($w->title).'</h2>' : '').
	'<ul>'.$content.$addons.'</ul>'.
	'</div>';
}
?>