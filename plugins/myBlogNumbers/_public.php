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

require dirname(__FILE__).'/_widgets.php';

function myBlogNumbersWidgetPublic($w)
{
	global $core;
	
	$content = $addons = '';
	$s_line = '<li>%s%s</li>';
	$s_title = '<strong>%s</strong> ';
	
	# Home only
	if (($w->homeonly == 1 && $core->url->type != 'default') ||
		($w->homeonly == 2 && $core->url->type == 'default')) {
		return;
	}
	
	# Entry
	if ($w->entry_show)
	{
		$title = $w->entry_title ? 
			sprintf($s_title,html::escapeHTML($w->entry_title)) : '';
		
		$count = $core->blog->getPosts(array(),true)->f(0);
		
		if ($count == 0)
		{
			$text = sprintf(__('no entries'),$count);
		}
		elseif ($count == 1)
		{
			$text = sprintf(__('one entry'),$count);
		}
		else
		{
			$text = sprintf(__('%s entries'),$count);
		}
		
		$content .= sprintf($s_line,$title,$text);
	}
	
	# Cat
	if ($w->cat_show)
	{
		$title = $w->cat_title ? 
			sprintf($s_title,html::escapeHTML($w->cat_title)) : '';
		
		$count = $core->blog->getCategories(array())->count();
		
		if ($count == 0)
		{
			$text = sprintf(__('no categories'),$count);
		}
		elseif ($count == 1)
		{
			$text = sprintf(__('one category'),$count);
		}
		else
		{
			$text = sprintf(__('%s categories'),$count);
		}
		
		$content .= sprintf($s_line,$title,$text);
	}
	
	# Comment
	if ($w->comment_show)
	{
		$title = $w->comment_title ? 
			sprintf($s_title,html::escapeHTML($w->comment_title)) : '';
		
		$params = array(
			'post_type' => 'post',
			'comment_status' => 1,
			'comment_trackback' => 0
		);
		$count = $core->blog->getComments($params,true)->f(0);
		
		if ($count == 0)
		{
			$text = sprintf(__('no comments'),$count);
		}
		elseif ($count == 1)
		{
			$text = sprintf(__('one comment'),$count);
		}
		else
		{
			$text = sprintf(__('%s comments'),$count);
		}
		
		$content .= sprintf($s_line,$title,$text);
	}
	
	# Trackback
	if ($w->trackback_show)
	{
		$title = $w->trackback_title ? 
			sprintf($s_title,html::escapeHTML($w->trackback_title)) : '';
		
		$params = array(
			'post_type' => 'post',
			'comment_status' => 1,
			'comment_trackback' => 1
		);
		$count = $core->blog->getComments($params,true)->f(0);
		
		if ($count == 0)
		{
			$text = sprintf(__('no trackbacks'),$count);
		}
		elseif ($count == 1)
		{
			$text = sprintf(__('one trackback'),$count);
		}
		else
		{
			$text = sprintf(__('%s trackbacks'),$count);
		}
		
		$content .= sprintf($s_line,$title,$text);
	}
	
	# Tag
	if ($core->plugins->moduleExists('tags') && $w->tag_show)
	{
		$title = $w->tag_title ? 
			sprintf($s_title,html::escapeHTML($w->tag_title)) : '';
		
		$count = $core->con->select(
			'SELECT count(M.meta_id) AS count '.
			'FROM '.$core->prefix.'meta M '.
			'LEFT JOIN '.$core->prefix.'post P ON P.post_id=M.post_id '.
			"WHERE M.meta_type='tag' ".
			"AND P.blog_id='".$core->blog->id."' "
		)->f(0);
		
		if ($count == 0)
		{
			$text = sprintf(__('no tags'),$count);
		}
		elseif ($count == 1)
		{
			$text = sprintf(__('one tag'),$count);
		}
		else
		{
			$text = sprintf(__('%s tags'),$count);
		}
		
		$content .= sprintf($s_line,$title,$text);
	}
	
	# User (that post)
	if ($w->user_show)
	{
		$title = $w->user_title ? 
			sprintf($s_title,html::escapeHTML($w->user_title)) : '';
		
		$count = $core->blog->getPostsUsers(array(),true)->count();
		
		if ($count == 0)
		{
			$text = sprintf(__('no author'),$count);
		}
		elseif ($count == 1)
		{
			$text = sprintf(__('one author'),$count);
		}
		else
		{
			$text = sprintf(__('%s authors'),$count);
		}
		
		$content .= sprintf($s_line,$title,$text);
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