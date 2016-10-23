<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of lastBlogUpdate, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2016 Jean-Christian Denis and contributors
# contact@jcdenis.fr http://jcdenis.net
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

function lastBlogUpdateWidgetPublic($w)
{
	global $core;

	# offline mod
	if ($w->offline)
		return;

	# Nothing to display
	if ($w->homeonly == 1 && $core->url->type != 'default' 
	||  $w->homeonly == 2 && $core->url->type == 'default' 
	|| !$w->blog_show && !$w->post_show && !$w->comment_show && !$w->media_show 
	|| !$w->blog_text && !$w->post_text && !$w->comment_text && !$w->media_text) return;

	$blog = $post = $comment = $media = $addons = '';

	# Blog
	if ($w->blog_show && $w->blog_text) {
		$title = $w->blog_title ? '<strong>'.html::escapeHTML($w->blog_title).'</strong> ' : '';
		$text = dt::str($w->blog_text, $core->blog->upddt, $core->blog->settings->system->blog_timezone);
		$blog = sprintf('<li>%s%s</li>', $title, $text);
	}

	# Post
	if ($w->post_show && $w->post_text) {
		$rs = $core->blog->getPosts(array('limit' => 1, 'no_content' => true));
		if (!$rs->isEmpty()) {
			$title = $w->post_title ? '<strong>'.html::escapeHTML($w->post_title).'</strong> ' : '';
			$text = dt::str($w->post_text, strtotime($rs->post_upddt), $core->blog->settings->system->blog_timezone);
			$link = $rs->getURL();
			$over = $rs->post_title;

			$post = sprintf('<li>%s<a href="%s" title="%s">%s</a></li>', $title, $link, $over, $text);
		}
	}

	# Comment
	if ($w->comment_show && $w->comment_text) {
		$rs = $core->blog->getComments(array('limit' => 1, 'no_content' => true));
		if (!$rs->isEmpty()) {
			$title = $w->comment_title ? '<strong>'.html::escapeHTML($w->comment_title).'</strong> ' : '';
			$text = dt::str($w->comment_text, strtotime($rs->comment_upddt), $core->blog->settings->system->blog_timezone);
			$link = $core->blog->url.$core->getPostPublicURL($rs->post_type, html::sanitizeURL($rs->post_url)).'#c'.$rs->comment_id;
			$over = $rs->post_title;

			$comment = sprintf('<li>%s<a href="%s" title="%s">%s</a></li>', $title, $link, $over, $text);
		}
	}

	# Media
	if ($w->media_show && $w->media_text) {
		$rs = $core->con->select(
			'SELECT media_upddt FROM '.$core->prefix.'media '.
			"WHERE media_path='".$core->con->escape($core->blog->settings->system->public_path)."' ".
			'ORDER BY media_upddt DESC '.$core->con->limit(1)
		);
		
		if (!$rs->isEmpty()) {
			$title = $w->media_title ? '<strong>'.html::escapeHTML($w->media_title).'</strong> ' : '';
			$text = dt::str($w->media_text, strtotime($rs->f('media_upddt')), $core->blog->settings->system->blog_timezone);

			$media = sprintf('<li>%s%s</li>', $title, $text);
		}
	}

	# --BEHAVIOR-- lastBlogUpdateWidgetParse
	$addons = $core->callBehavior('lastBlogUpdateWidgetParse', $core, $w);

	# Nothing to display
	if (!$blog && !$post && !$comment && !$media && !$addons) {

		return null;
	}

	# Display
	$res =
	($w->title ? $w->renderTitle(html::escapeHTML($w->title)) : '').
	'<ul>'.$blog.$post.$comment.$media.$addons.'</ul>';

	return $w->renderDiv($w->content_only,'lastblogupdate '.$w->class,'',$res);
}