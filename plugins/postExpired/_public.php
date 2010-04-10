<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of postExpired, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2010 JC Denis and contributors
# jcdenis@gdwd.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')){return;}
if (!$core->plugins->moduleExists('metadata') || !in_array($core->url->type,array('default','feed'))) {return;}

$core->addBehavior('publicBeforeDocument',array('publicBehaviorPostExpired','unpublishExpiredEntries'));

class publicBehaviorPostExpired
{
	public static function unpublishExpiredEntries($core)
	{
		# Get expired dates
		$params['columns'][] = 'meta_id';
		$params['no_content'] = true;
		$params['post_status'] = 1;
		$params['from'] = ', '.$core->prefix.'meta META ';
		$params['sql'] = 'AND META.post_id = P.post_id ';
		$params['sql'] .= "AND META.meta_type = 'postexpired' ";
		$posts =  $core->auth->sudo(array($core->blog,'getPosts'),$params);
		# No expired date
		if ($posts->isEmpty()) {
			return;
		}
		# Get curent timestamp
		$now = dt::toUTC(time());
		# Prepared post cursor
		$post_cur = $core->con->openCursor($core->prefix.'post');
		# Loop through marked posts
		while($posts->fetch())
		{
			# Check if post is outdated
			$now_tz = $now + dt::getTimeOffset($posts->post_tz,$now);
			$meta_tz = strtotime($posts->meta_id);
			if ($now_tz > $meta_tz)
			{
				# Update post
				$post_cur->clean();
				$post_cur->post_upddt = date('Y-m-d H:i:s',$now_tz);
				$post_cur->post_status = 0;
				$post_cur->update(
					'WHERE post_id = '.$posts->post_id.' '.
					"AND blog_id = '".$core->con->escape($core->blog->id)."' "
				);
				# Remove expired date
				$meta = new dcMeta($core);
				$core->auth->sudo(array($meta,'delPostMeta'),$posts->post_id,'postexpired');
				# Say blog is updated
				$core->blog->triggerBlog();
			}
		}
	}
}
?>