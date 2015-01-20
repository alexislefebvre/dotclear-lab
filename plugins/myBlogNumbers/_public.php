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

if (!defined('DC_RC_PATH')) {

	return null;
}

require dirname(__FILE__).'/_widgets.php';

function myBlogNumbersWidgetPublic($w)
{
	global $core;

	$content = $addons = '';
	$s_line = '<li>%s%s</li>';
	$s_title = '<strong>%s</strong> ';

		if ($w->offline)
			return;

	# Home only
	if ($w->homeonly == 1 && $core->url->type != 'default'
	 || $w->homeonly == 2 && $core->url->type == 'default'
	) {
		return;
	}

	# Entry
	if ($w->entry_show) {
		$title = $w->entry_title ? 
			sprintf($s_title, html::escapeHTML($w->entry_title)) : '';

		$count = $core->blog->getPosts(array(), true)->f(0);

		$text = $count == 0 ?
			sprintf(__('no entries'), $count) :
			sprintf(__('one entry', '%s entries', $count), $count);

		$content .= sprintf($s_line, $title, $text);
	}

	# Cat
	if ($w->cat_show) {
		$title = $w->cat_title ? 
			sprintf($s_title, html::escapeHTML($w->cat_title)) : '';

		$count = $core->blog->getCategories(array())->count();

		$text = $count == 0 ?
			sprintf(__('no categories'), $count) :
			sprintf(__('one category', '%s categories', $count), $count);

		$content .= sprintf($s_line, $title, $text);
	}

	# Comment
	if ($w->comment_show) {
		$title = $w->comment_title ? 
			sprintf($s_title, html::escapeHTML($w->comment_title)) : '';

		$params = array(
			'post_type' => 'post',
			'comment_status' => 1,
			'comment_trackback' => 0
		);
		$count = $core->blog->getComments($params, true)->f(0);

		$text = $count == 0 ?
			sprintf(__('no comments'),$count) :
			sprintf(__('one comment', '%s comments', $count), $count);

		$content .= sprintf($s_line,$title,$text);
	}

	# Trackback
	if ($w->trackback_show) {
		$title = $w->trackback_title ? 
			sprintf($s_title, html::escapeHTML($w->trackback_title)) : '';

		$params = array(
			'post_type' => 'post',
			'comment_status' => 1,
			'comment_trackback' => 1
		);
		$count = $core->blog->getComments($params, true)->f(0);

		$text = $count == 0 ?
			sprintf(__('no trackbacks'),$count) :
			sprintf(__('one trackback', '%s trackbacks', $count), $count);

		$content .= sprintf($s_line,$title,$text);
	}

	# Tag
	if ($core->plugins->moduleExists('tags') && $w->tag_show) {
		$title = $w->tag_title ? 
			sprintf($s_title,html::escapeHTML($w->tag_title)) : '';

		$count = $core->con->select(
			'SELECT count(M.meta_id) AS count '.
			'FROM '.$core->prefix.'meta M '.
			'LEFT JOIN '.$core->prefix.'post P ON P.post_id=M.post_id '.
			"WHERE M.meta_type='tag' ".
			"AND P.blog_id='".$core->blog->id."' "
		)->f(0);

		$text = $count == 0 ?
			sprintf(__('no tags'), $count) :
			sprintf(__('one tag', '%s tags', $count), $count);

		$content .= sprintf($s_line, $title, $text);
	}

	# User (that post)
	if ($w->user_show) {
		$title = $w->user_title ? 
			sprintf($s_title, html::escapeHTML($w->user_title)) : '';

		$count = $core->blog->getPostsUsers(array(),true)->count();

		$text = $count == 0 ?
			sprintf(__('no author'), $count) :
			sprintf(__('one author', '%s authors', $count), $count);

		$content .= sprintf($s_line,$title,$text);
	}

	# --BEHAVIOR-- myBlogNumbersWidgetParse
	$addons = $core->callBehavior('myBlogNumbersWidgetParse', $core, $w);

	# Nothing to display
	if (!$content && !$addons) {

		return null;
	}

	# Display
		$res =
		($w->title ? $w->renderTitle(html::escapeHTML($w->title)) : '').
		'<ul>'.$content.$addons.'</ul>';

		return $w->renderDiv($w->content_only,'myblognumbers '.$w->class,'',$res);
}
