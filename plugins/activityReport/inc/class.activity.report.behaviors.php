<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of activityReport, a plugin for Dotclear 2.
# 
# Copyright (c) 2009 JC Denis and contributors
# jcdenis@gdwd.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')){return;}

if (!$core->activityReport instanceof activityReport){return;}

# Dashboarditems
if ($core->activityReport->getSetting('dashboardItem'))
{
	$core->addBehavior(
		'adminDashboardHeaders',
		array('activityReportBehaviors','dashboardHeaders')
	);
	$core->addBehavior(
		'adminDashboardItems',
		array('activityReportBehaviors','dashboardItems')
	);
}


/* Blog 
-------------------------*/
$core->activityReport->addGroup('blog',__('Actions on blog'));

# Not use as it is global : BEHAVIOR adminAfterBlogCreate in admin/blog.php

# from BEHAVIOR adminAfterBlogUpdate in admin/blog_pref.php
$core->activityReport->addAction(
	'blog',
	'update',
	__('updating blog'),
	__('Blog was updated by "%s"'),
	'adminAfterBlogUpdate',
	array('activityReportBehaviors','blogUpdate')
);

# from BEHAVIOR publicHeadContent in template
$core->activityReport->addAction(
	'blog',
	'p404',
	__('404 error'),
	__('New 404 error page at "%s"'),
	'publicHeadContent',
	array('activityReportBehaviors','blogP404')
);


/* Post 
-------------------------*/
$core->activityReport->addGroup('post',__('Actions on posts'));

# from BEHAVIOR coreAfterPostCreate in inc/core/class.dc.blog.php (DC 2.2)
# duplicate adminAfterPostCreate in admin/post.php
# duplicate adminAfterPostCreate in admin/services.php
$core->activityReport->addAction(
	'post',
	'create',
	__('post creation'),
	__('A new post called "%s" was created by "%s" at %s'),
	'adminAfterPostCreate',
	array('activityReportBehaviors','postCreate')
);

# from BEHAVIOR coreAfterPostUpdate in inc/core/class.dc.blog.php (DC2.2)
# duplicate adminAfterPostUpdate in admin/post.php
$core->activityReport->addAction(
	'post',
	'update',
	__('updating post'),
	__('Post called "%s" has been updated by "%s" at %s'),
	'adminAfterPostUpdate',
	array('activityReportBehaviors','postUpdate')
);

# from BEHAVIOR adminBeforePostDelete in admin/posts_actions.php
# from BEHAVIOR adminBeforePostDelete in admin/post.php
$core->activityReport->addAction(
	'post',
	'delete',
	__('post deletion'),
	__('Post called "%s" have been deleted by "%s"'),
	'adminBeforePostDelete',
	array('activityReportBehaviors','postDelete')
);


/* Comment 
-------------------------*/
$core->activityReport->addGroup('comment',__('Actions on comments'));

# from BEHAVIOR coreAfterCommentCreate in inc/core/class.dc.blog.php
# duplicate adminAfterCommentCreate in admin/comment.php
# duplicate publicAfterCommentCreate in inc/public/lib.urlhandlers.php
$core->activityReport->addAction(
	'comment',
	'create',
	__('comment creation'),
	__('A new comment was created by "%s" on post "%s" at %s'),
	'coreAfterCommentCreate',
	array('activityReportBehaviors','commentCreate')
);

# from BEHAVIOR coreAfterCommentUpdate in inc/core/class.dc.blog.php
# duplicate adminAfterCommentUpdate in admin/comment.php
$core->activityReport->addAction(
	'comment',
	'update',
	__('updating comment'),
	__('Comment have been updated by "%s" at %s'),
	'coreAfterCommentUpdate',
	array('activityReportBehaviors','commentUpdate')
);

# Missing coreBeforeCommentDelete in inc/core/class.dc.blog.php 
# Missing adminBeforeCommentDelete in admin/comment.php

# from BEHAVIOR coreAfterCommentCreate in inc/core/class.dc.blog.php
# duplicate publicAfterTrackbackCreate in inc/core/class.dc.trackback.php
$core->activityReport->addAction(
	'comment',
	'trackback',
	__('trackback creation'),
	__('A new trackback to "%" at "%s" was created on post "%s" at %s'),
	'coreAfterCommentCreate',
	array('activityReportBehaviors','trackbackCreate')
);


/* Category 
-------------------------*/
$core->activityReport->addGroup('category',__('Actions on categories'));

# from BEHAVIOR adminAfterCategoryCreate in admin/category.php
$core->activityReport->addAction(
	'category',
	'create',
	__('category creation'),
	__('A new category called "%s" was created by "%s" at %s'),
	'adminAfterCategoryCreate',
	array('activityReportBehaviors','categoryCreate')
);

# from BEHAVIOR adminAfterCategoryUpdate in admin/category.php
$core->activityReport->addAction(
	'category',
	'update',
	__('updating category'),
	__('Category called "%s" have been updated by "%s" at %s'),
	'adminAfterCategoryUpdate',
	array('activityReportBehaviors','categoryUpdate')
);

# Missing adminBeforeCategoryDelete in admin/category.php


/* User 
-------------------------*/
$core->activityReport->addGroup('user',__('Actions on users'));

# from BEHAVIOR adminAfterUserCreate in admin/user.php
$core->activityReport->addAction(
	'user',
	'create',
	__('user creation'),
	__('A new user named "%s" was added by "%s"'),
	'adminAfterUserCreate',
	array('activityReportBehaviors','userCreate')
);

# from BEHAVIOR adminAfterUserUpdated in admin/user.php
$core->activityReport->addAction(
	'user',
	'update',
	__('updating user'),
	__('User named "%s" have been updated by "%s"'),
	'adminAfterUserUpdate',
	array('activityReportBehaviors','userUpdate')
);

# from BEHAVIOR adminBeforeUserDelete in admin/users.php
$core->activityReport->addAction(
	'user',
	'delete',
	__('user deletion'),
	__('User named "%s" have been deleted by "%"'),
	'adminBeforeUserDelete',
	array('activityReportBehaviors','userDelete')
);


class activityReportBehaviors
{
	public static function blogUpdate($cur,$blog_id)
	{
		global $core;

		$logs = array($core->auth->getInfo('user_cn'));

		$core->activityReport->addLog('blog','update',$logs);
	}

	public static function blogP404()
	{
		global $core;
		if ($core->url->type != '404') return;

		$logs = array($core->blog->url.$_SERVER['QUERY_STRING']);

		$core->activityReport->addLog('blog','p404',$logs);
	}

	public static function postCreate($cur,$post_id)
	{
		global $core;
		
		$type = $cur->post_type ? $cur->post_type : 'post';
		$post_url = $core->blog->getPostURL('',$cur->post_dt,$cur->post_title,$post_id);

		$logs = array(
			$cur->post_title,
			$core->auth->getInfo('user_cn'),
			$core->blog->url.$core->url->getBase($type).'/'.$post_url
		);

		$core->activityReport->addLog('post','create',$logs);
	}

	public static function postUpdate($cur,$post_id)
	{
		global $core;
		
		$type = $cur->post_type ? $cur->post_type : 'post';
		$post_url = $core->blog->getPostURL('',$cur->post_dt,$cur->post_title,$post_id);

		$logs = array(
			$cur->post_title,
			$core->auth->getInfo('user_cn'),
			$core->blog->url.$core->url->getBase($type).'/'.$post_url
		);

		$core->activityReport->addLog('post','update',$logs);
	}

	public static function postDelete($post_id)
	{
		global $core;
		$posts = $core->blog->getPosts(array('post_id'=>$post_id,'limit'=>1));

		$logs = array(
			$posts->post_title,
			$core->auth->getInfo('user_cn')
		);

		$core->activityReport->addLog('post','delete',$logs);
	}

	public static function commentCreate($blog,$cur)
	{
		global $core;
		if ($cur->comment_trackback) return;

		$posts = $core->blog->getPosts(array('post_id'=>$cur->post_id,'limit'=>1));

		$logs = array(
			$cur->comment_author,
			$posts->post_title,
			$core->blog->url.$core->url->getBase($posts->post_type).
				'/'.$posts->post_url.'#c'.$cur->comment_id
		);

		$core->activityReport->addLog('comment','create',$logs);
	}

	public static function commentUpdate($blog,$cur,$old)
	{
		global $core;
		$posts = $core->blog->getPosts(array('post_id'=>$old->post_id,'limit'=>1));

		$logs = array(
			$core->auth->getInfo('user_cn'),
			$posts->post_title,
			$core->blog->url.$core->url->getBase($posts->post_type).
				'/'.$posts->post_url.'#c'.$old->comment_id
		);

		$core->activityReport->addLog('comment','update',$logs);
	}

	public static function trackbackCreate($cur,$comment_id)
	{
		global $core;
		if (!$cur->comment_trackback) return;

		$posts = $core->blog->getPosts(array('post_id'=>$cur->post_id,'limit'=>1));

		$logs = array(
			$cur->comment_author,
			$cur->comment_url,
			$posts->post_title,
			$core->blog->url.$core->url->getBase($posts->post_type).
				'/'.$posts->post_url
		);

		$core->activityReport->addLog('comment','trackback',$logs);
	}

	public static function categoryCreate($cur,$cat_id)
	{
		global $core;

		$logs = array(
			$cur->cat_title,
			$core->auth->getInfo('user_cn'),
			$core->blog->url.$core->url->getBase('category').'/'.$cur->cat_url
		);

		$core->activityReport->addLog('category','create',$logs);
	}

	public static function categoryUpdate($cur,$cat_id)
	{
		global $core;

		$logs = array(
			$cur->cat_title,
			$core->auth->getInfo('user_cn'),
			$core->blog->url.$core->url->getBase('category').'/'.$cur->cat_url
		);

		$core->activityReport->addLog('category','update',$logs);
	}

	public static function userCreate($cur,$user_id)
	{
		global $core;
		$user_cn = dcUtils::getUserCN($cur->user_id, $cur->user_name,
			$cur->user_firstname, $cur->user_displayname);

		$logs = array(
			$user_cn,
			$core->auth->getInfo('user_cn')
		);

		$core->activityReport->addLog('user','create',$logs);
	}

	public static function usertUpdate($cur,$user_id)
	{
		global $core;
		$user_cn = dcUtils::getUserCN($cur->user_id, $cur->user_name,
			$cur->user_firstname, $cur->user_displayname);

		$logs = array(
			$user_cn,
			$core->auth->getInfo('user_cn')
		);

		$core->activityReport->addLog('user','update',$logs);
	}

	public static function userDelete($user_id)
	{
		global $core;
		$users = $core->getUser($id);
		$user_cn = dcUtils::getUserCN($users->user_id, $users->user_name,
			$users->user_firstname, $users->user_displayname);

		$logs = array(
			$user_cn,
			$core->auth->getInfo('user_cn')
		);

		$core->activityReport->addLog('user','delete',$logs);
	}
}
?>