<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of lastBlogUpdate, a plugin for Dotclear 2.
#
# Copyright (c) 2009 JC Denis and contributors
# jcdenis@gdwd.com
#
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')) { return; }
 
$core->addBehavior('initWidgets',array('lastBlogUpdateWidget','init'));

class lastBlogUpdateWidget
{
	public static function init(&$w)
	{
		$w->create('lastblogupdate',__('Dates of last updates'),
			array('lastBlogUpdateWidget','parse'));
		$w->lastblogupdate->setting('title',__('Title:'),
			__('Dates of last updates'),'text');
		$w->lastblogupdate->setting('blog_title',__('Title for last blog update:'),
			__('Blog:'),'text');
		$w->lastblogupdate->setting('blog_text',__('Text for last blog update:'),
			__('%Y-%m-%d %H:%M'),'text');
		$w->lastblogupdate->setting('post_title',__('Title for last entry update:'),
			__('Entries:'),'text');
		$w->lastblogupdate->setting('post_text',__('Text for last entry update:'),
			__('%Y-%m-%d %H:%M'),'text');
		$w->lastblogupdate->setting('comment_title',__('Title for last comment update:'),
			__('Comments:'),'text');
		$w->lastblogupdate->setting('comment_text',__('Text for last comment update:'),
			__('%Y-%m-%d %H:%M'),'text');
		$w->lastblogupdate->setting('homeonly',__('Home page only'),1,'check');
	}

	public static function parse(&$w)
	{
		global $core;

		if ($w->homeonly && $core->url->type != 'default' 
		|| !$w->blog_text && !$w->post_text && !$w->comment_text) return;

		$blog = $post = $comment = '';
		if ($w->blog_text) {

			$title = ($w->blog_title ? 
				'<strong>'.html::escapeHTML($w->blog_title).'</strong> ' : '');
			$text = dt::str($w->blog_text,$core->blog->upddt);

			$blog = sprintf('<li>%s%s</li>',$title,$text);
		}

		if ($w->post_text) {
			$rs = $core->blog->getPosts(array('limit'=>1,'no_content'=>true));
			if (!$rs->isEmpty()) {

				$title = $w->post_title ? 
					'<strong>'.html::escapeHTML($w->post_title).'</strong> ' : '';
				$text = dt::str($w->post_text,strtotime($rs->post_upddt));
				$link = $rs->getURL();
				$over = $rs->post_title;

				$post = sprintf('<li>%s<a href="%s" title="%s">%s</a></li>',
					$title,$link,$over,$text);
			}
		}

		if ($w->comment_text) {
			$rs = $core->blog->getComments(array('limit'=>1,'no_content'=>true));
			if (!$rs->isEmpty()) {

				$title = $w->comment_title ? 
					'<strong>'.html::escapeHTML($w->comment_title).'</strong> ' : '';
				$text = dt::str($w->comment_text,strtotime($rs->comment_upddt));
				$link = $core->blog->url.$core->getPostPublicURL(
					$rs->post_type,html::sanitizeURL($rs->post_url)).
					'#c'.$rs->comment_id;
				$over = $rs->post_title;

				$comment = sprintf('<li>%s<a href="%s" title="%s">%s</a></li>',
					$title,$link,$over,$text);
			}
		}

		return 
		'<div class="lastblogupdate">'.
		($w->title ? '<h2>'.html::escapeHTML($w->title).'</h2>' : '').
		'<ul>'.$blog.$post.$comment.'</ul>'.
		'</div>';
	}
}
?>