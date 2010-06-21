<?php
# ***** BEGIN LICENSE BLOCK *****
# This file is part of DotClear REST plugin.
# Copyright (c) 2007 Bruno Hondelatte,  and contributors. 
# Many, many thanks to Olivier Meunier and the Dotclear Team.
# All rights reserved.
#
# DotClear is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 2 of the License, or
# (at your option) any later version.
# 
# DotClear is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
# 
# You should have received a copy of the GNU General Public License
# along with DotClear; if not, write to the Free Software
# Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
#
# ***** END LICENSE BLOCK *****

class blogRest 
{
	public static function getPosts($core,$get)
	{
		$allowed_params=array('post_id','post_url','user_id','cat_id','cat_url',
			'post_selected','post_year','post_month','post_day','post_lang',
			'search','order','limit','post_type','offset');
		$no_content=isset($get['no_content']);
		$count_only = isset($get['count_only']);


		$params = jsonRestServer::getFilteredParams($allowed_params,$get);
		if (!isset($params['post_type']))
			$params['post_type']='post';

		if ($no_content)
			$params['no_content']=1;
			
		if (isset($params['offset']) && isset($params['limit'])) {
			$params['limit'] = array($params['offset'],$params['limit']);
			unset($params['offset']);
		}


		$rs = $core->blog->getPosts($params,$count_only);
		if ($count_only)
			return (int)$rs->f(0);
		$rsp = array();
		while ($rs->fetch())
		{
			$post=array();
			$post['id'] = $rs->post_id;
			$post['title'] = $rs->post_title;
			if (!$no_content) {
				$post['excerpt'] = $rs->post_excerpt_xhtml;
				$post['content'] = $rs->post_content_xhtml;
			}
			$post['user'] = $rs->user_id;
			$post['creadt'] = $rs->post_creadt;
			$post['upddt'] = $rs->post_upddt;
			$post['url'] = $rs->post_url;
			$post['selected'] = ($rs->post_selected == 1);
			$post['nb_comments'] = (int)$rs->nb_comment;
			$post['nb_trackbacks'] = (int)$rs->nb_trackback;
			$post['category'] = (int)$rs->cat_id;

			$rsp[]=$post;
		}
		return $rsp;
	}

	public static function getCategories($core,$get)
	{
		$allowed_params=array('post_type','cat_url','cat_id','id'=>'cat_id');
		$params = jsonRestServer::getFilteredParams($allowed_params,$get);
		if (!isset($params['post_type']))
			$params['post_type']='post';

		$rs = $core->blog->getCategories($params);
		$rsp = array();
		while ($rs->fetch())
		{
			$cat=array();
			$cat['id'] = (int)$rs->cat_id;
			$cat['title'] = $rs->cat_title;
			$cat['url'] = $rs->cat_url;
			$cat['desc'] = $rs->cat_desc;
			$cat['nb_post'] = (int)$rs->nb_post;
			$cat['position'] = (int)$rs->cat_position;
			$rsp[]=$cat;
		}
		return $rsp;
	}

	public static function getLangs($core,$get)
	{
		$allowed_params=array('post_type','lang');
		$params = jsonRestServer::getFilteredParams($allowed_params,$get);

		$rs = $core->blog->getLangs($params);
		$rsp = array();
		while ($rs->fetch())
		{
			$lang=array();
			$lang['lang'] = $rs->post_lang;
			$lang['nb_post'] = (int)$rs->nb_post;
			$rsp[]=$lang;
		}
		return $rsp;
	}

	public static function getDates($core,$get)
	{
		$allowed_params=array('post_type','year','month','day','cat_id','cat_url',
			'post_lang','next','previous','order');
		$params = jsonRestServer::getFilteredParams($allowed_params,$get);
		if (!isset($params['post_type']))
			$params['post_type']='post';

		$rs = $core->blog->getDates($params);
		$rsp = array();
		while ($rs->fetch())
		{
			$date=array();
			$date['date'] = $rs->dt;
			$date['nb_post'] = (int)$rs->nb_post;
			$rsp[]=$date;
		}
		return $rsp;
	}

	public static function getPostsUsers($core,$get)
	{
		$post_type = isset($get['post_type'])?$get['post_type']:null;

		$rs = $core->blog->getPostsUsers($post_type);
		$rsp = array();
		while ($rs->fetch())
		{
			$user=array();
			$user['id'] = $rs->user_id;
			$user['name'] = $rs->user_name;
			$user['displayname'] = $rs->user_displayname;
			$rsp[]=$user;
		}
		return $rsp;
	}

	public static function getComments($core,$get)
	{
		$allowed_params=array('post_type','post_id','cat_id','comment_id',
			'comment_trackback','post_url','user_id','q_author','order',
			'limit');
		$no_content=isset($get['no_content']);
		$count_only = isset($get['count_only']);


		$params = jsonRestServer::getFilteredParams($allowed_params,$get);
		if (!isset($params['post_type']))
			$params['post_type']='post';

		if ($no_content)
			$params['no_content']=1;


		$rs = $core->blog->getComments($params,$count_only);
		if ($count_only)
			return (int)$rs->f(0);
		$rsp = array();
		while ($rs->fetch())
		{
			$comment=array();
			$comment['id'] = $rs->comment_id;
			if (!$no_content) {
				$comment['content'] = $rs->comment_content;
			}
			$comment['date'] = $rs->comment_dt;
			$comment['upddt'] = $rs->comment_upddt;
			$comment['author'] = $rs->comment_author;
			$comment['site'] = $rs->comment_site;
			$comment['post_title'] = $rs->post_title;
			$comment['post_title'] = $rs->post_title;
			$comment['post_title'] = $rs->post_title;
			$comment['post_title'] = $rs->post_title;
			$comment['post_title'] = $rs->post_title;
			$comment['upddt'] = $rs->comment_upddt;
			$comment['upddt'] = $rs->comment_upddt;
			$comment['upddt'] = $rs->comment_upddt;

			$comment['user'] = $rs->user_id;
			$comment['url'] = $rs->comment_url;
			$comment['selected'] = ($rs->comment_selected == 1);
			$comment['nb_comments'] = (int)$rs->nb_comment;
			$comment['nb_trackbacks'] = (int)$rs->nb_trackback;
			$comment['category'] = (int)$rs->cat_id;

			$rsp[]=$comment;
		}
		return $rsp;
	}

	
	public static function getPostMedia($core,$get)
	{
		if (!isset($get['post_id']))
			throw new Exception ('No post ID specified');
		
		$post_id = (integer) $get['post_id'];
		$media_id=isset($get['media_id'])?(integer)$get['media_id']:null;

		$rs = $core->media->getPostMedia($post_id,$media_id);

		$rsp = array();
		foreach ($rs as $m)
		{
			$media=array(
				'url'=> $m->file_url,
				'id'=> $m->media_id,
				'title'=> $m->media_title,
				'dt' => $m->media_dt,
				'thumbs' => $m->media_thumb);

			$rsp[]=$media;
		}
		return $rsp;
	}

	public static function getMedia($core,$get)
	{
		if (!isset($get['media_id']))
			throw new Exception ('No media ID specified');
		
		$media_id = (integer) $get['media_id'];
		$media_id=isset($get['media_id'])?(integer)$get['media_id']:null;

		$m = $core->media->getFile($media_id);
		if ($m == null)
			throw new Exception ('Media not found');
		$rsp=array(
			'url'=> $m->file_url,
			'id'=> $m->media_id,
			'title'=> $m->media_title,
			'dt' => $m->media_dt,
			'thumbs' => $m->media_thumb);

		return $rsp;
	}
}
?>