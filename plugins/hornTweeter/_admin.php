<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of hornTweeter, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2010 JC Denis and contributors
# jcdenis@gdwd.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')){return;}

# Plugin menu
$_menu['Plugins']->addItem(
	__('Horn tweeter'),
	'plugin.php?p=hornTweeter','index.php?pf=hornTweeter/icon.png',
	preg_match('/plugin.php\?p=hornTweeter(&.*)?$/',$_SERVER['REQUEST_URI']),
	$core->auth->check('admin',$core->blog->id)
);

# Settings ns
$core->blog->settings->addNamespace('hornTweeter');

# Admin behaviors
$core->addBehavior('adminPostHeaders',array('adminHornTweeter','adminPostHeaders'));
$core->addBehavior('adminPostFormSidebar',array('adminHornTweeter','adminPostFormSidebar'));
$core->addBehavior('adminAfterPostUpdate',array('adminHornTweeter','adminAfterPostSave'));
$core->addBehavior('adminAfterPostCreate',array('adminHornTweeter','adminAfterPostSave'));
$core->addBehavior('adminPostsActions',array('adminHornTweeter','adminPostsActions'));

$core->addBehavior('adminAfterCommentDesc',array('adminHornTweeter','adminAfterCommentDesc'));
$core->addBehavior('adminAfterCommentCreate',array('adminHornTweeter','adminAfterCommentSave'));
$core->addBehavior('adminAfterCommentUpdate',array('adminHornTweeter','adminAfterCommentSave'));
$core->addBehavior('adminCommentsActions',array('adminHornTweeter','adminCommentsActions'));

class adminHornTweeter
{
	# Added expandable feature
	# /admin/post.php#L292
	public static function adminPostHeaders($posts_actions=true)
	{
		return dcPage::jsLoad('index.php?pf=hornTweeter/js/horntweeter.js');
	}
	
	# Added hidden field to check post status change for auto tweet
	# /admin/post.php#L447
	public static function adminPostFormSidebar($post)
	{
		global $core;
		
		if (!$core->blog->settings->hornTweeter->active) {
			return;
		}
		
		$old_post_status = $post !== null ? $post->post_status : 0;
		
		$can_publish = $core->auth->check('publish,contentadmin',$core->blog->id);
		if (!$core->auth->check('contentadmin',$core->blog->id))
		{
			if ($post === null) {
				$can_publish = false;
			}
			else {
				$rs = $this->con->select(
					'SELECT post_id '.
					'FROM '.$this->prefix.'post '.
					'WHERE post_id = '.$id.' '.
					"AND user_id = '".$core->con->escape($core->auth->userID())."' "
				);
				$can_publish = !$rs->isEmpty();
			}
		}
		
		if ($can_publish) {
			echo 
			'<h3 id="horntweeter-form-title">'.__('Horn tweeter').'</h3>'.
			'<div id="horntweeter-form-content">'.
			'<p class="label"><label class="classic">'.
			form::checkbox('horntweeter_send','1',false).' '.
			__('Tweet this').'</label>'.
			form::hidden(array('horntweeter_old_post_status'),(string) $old_post_status).
			'</p></div>';
		}
		else {
			echo '<div>'.
			form::hidden(array('horntweeter_old_post_status'),(string) $old_post_status).
			'</div>';
		}
	}
	
	# /admin/post.php#L230
	# /admin/post.php#L251
	public static function adminAfterPostSave($cur,$post_id)
	{
		global $core;
		
		if ($core->blog->settings->hornTweeter->active 
		 && $cur->post_status == 1 
		 && (
		  $_POST['horntweeter_old_post_status'] != 1 && $core->blog->settings->hornTweeter->post_auto 
		  || !empty($_POST['horntweeter_send']) && $core->auth->check('publish,contentadmin',$core->blog->id)
		 )
		 && $core->plugins->moduleExists('TaC'))
		{
			$has_tac = $has_registry = $has_access = false;
			$shortener = $core->blog->settings->hornTweeter->api_url;
			$twitter_msg = $core->blog->settings->hornTweeter->post_msg;
			
			if (!$twitter_msg) {
				return;
			}
			
			$tac = new tac($core,'hornTweeter',null);
			
			if (!($tac->checkRegistry())) {
				return;
			}
			if (!($tac->checkAccess())) {
				return;
			}
			
			// shorten url
			$url = $core->blog->url.$core->getPostPublicURL($cur->post_type,html::sanitizeURL($cur->post_url));
			$posturl = '';
			if ($shortener) {
				$posturl = tacTools::shorten($url,false,$shortener);
			}
			$posturl = $posturl ? $posturl : $url;
			
			// author
			$user = $core->getUser($cur->user_id);
			$postauthor = dcUtils::getUserCN($user->user_id,$user->user_name,$user->user_firstname,$user->user_displayname);
			
			// get tags
			$metas = '';
			$rs_metas = $core->meta->getMetadata(array('meta_type'=>'tag','post_id'=>$post_id));
			if (!$rs_metas->isEmpty()) {
				while($rs_metas->fetch()) {
					$metas .= ' #'.$rs_metas->meta_id;
				}
			}
			
			// parse message
			$msg = str_replace(
				array('%blog%','%title%','%url%','%author%','%tags%'),
				array($core->blog->name,$cur->post_title,$posturl,$postauthor,$metas),
				$twitter_msg
			);
			
			// send message
			if (!empty($msg)) {
				$tac->post('statuses/update',array('status'=>$msg));
			}
		}
	}
	
	# Check status changing on multiple posts update for auto tweet
	# /admin/posts_actions.php#L62
	public static function adminPostsActions($core,$posts,$action,$redir)
	{
		if ($action == 'publish' 
		 && $core->auth->check('publish,contentadmin',$core->blog->id) 
		 && $core->blog->settings->hornTweeter->active 
		 && $core->blog->settings->hornTweeter->post_auto 
		 && $core->plugins->moduleExists('TaC'))
		{
			$has_tac = $has_registry = $has_access = false;
			$shortener = $core->blog->settings->hornTweeter->api_url;
			$twitter_msg = $core->blog->settings->hornTweeter->post_msg;
			
			if (!$twitter_msg) {
				return;
			}
			
			try {
				$tac = new tac($core,'hornTweeter',null);
				
				if (!($tac->checkRegistry())) {
					return;
				}
				if (!($tac->checkAccess())) {
					return;
				}
				
				// user status
				$req_user = '';
				if (!$core->auth->check('contentadmin',$core->blog->id)) {
					$req_user = "AND user_id = '".$core->con->escape($core->auth->userID())."' ";
				}
				
				while ($posts->fetch()) {
				
					// get old status to tweet only post goes pusblished
					$rs_olds = $core->con->select(
						'SELECT post_id FROM '.$core->prefix.'post '.
						"WHERE blog_id = '".$core->con->escape($core->blog->id)."' ".
						'AND post_status != 1 '.
						$req_user.
						'AND post_id = '.$posts->post_id
					);
					
					// if status goes published
					if (!$rs_olds->isEmpty()) {
						
						// shorten url
						$posturl = '';
						if ($shortener) {
							$posturl = tacTools::shorten($posts->getURL(),false,$shortener);
						}
						$posturl = $posturl ? $posturl : $posts->getURL();
						
						// get tags
						$metas = '';
						$rs_metas = $core->meta->getMetadata(array('meta_type'=>'tag','post_id'=>$posts->post_id));
						if (!$rs_metas->isEmpty()) {
							while($rs_metas->fetch()) {
								$metas .= ' #'.$rs_metas->meta_id;
							}
						}
						
						// parse message
						$msg = str_replace(
							array('%blog%','%title%','%url%','%author%','%tags%'),
							array($core->blog->name,$posts->post_title,$posturl,$posts->getAuthorCN(),$metas),
							$twitter_msg
						);
						
						// send message
						if (!empty($msg)) {
							$tac->post('statuses/update',array('status'=>$msg));
						}
					}
				}
			}
			catch (Exception $e) {
				$core->error->add($e->getMessage());
			}
		}
	}
	
	# /admin/comment.php#L217
	public static function adminAfterCommentDesc($rs)
	{
		echo '<div>'.
		form::hidden(array('horntweeter_old_comment_status'),(string) $rs->comment_status).
		'</div>';
	}
	
	# /admin/comment.php#L58
	# /admin/comment.php#L132
	public static function adminAfterCommentSave($cur,$comment_id)
	{
		global $core;
		
		if ($core->blog->settings->hornTweeter->active 
		 && $cur->comment_status == 1 
		 && $core->blog->settings->hornTweeter->comment_auto
		 && (
			!isset($_POST['horntweeter_old_comment_status'])
			|| $_POST['horntweeter_old_comment_status'] != 1
		 )
		 && $core->plugins->moduleExists('TaC'))
		{
			$twitter_msg = $core->blog->settings->hornTweeter->comment_msg;
			
			if (!$twitter_msg) {
				return;
			}
			
			$tac = new tac($core,'hornTweeter',null);
			
			if (!($tac->checkRegistry())) {
				return;
			}
			if (!($tac->checkAccess())) {
				return;
			}
			
			// get related post info
			$post_params = array(
				'no_content'=>true,
				'post_type'=>'',
				'post_id'=>$cur->post_id
			);
			$post = $core->blog->getPosts($post_params);
			if ($post->isEmpty()) {
				return;
			}
			
			// shorten url
			$shortener = $core->blog->settings->hornTweeter->api_url;
			$url = $url = $post->getURL().'#c'.$comment_id;
			$posturl = '';
			if ($shortener) {
				$posturl = tacTools::shorten($url,false,$shortener);
			}
			$posturl = $posturl ? $posturl : $url;
			
			// parse message
			$msg = str_replace(
				array('%blog%','%user%','%title%','%url%'),
				array($core->blog->name,$cur->comment_author,$post->post_title,$posturl),
				$twitter_msg
			);
			
			// send message
			if (!empty($msg)) {
				$tac->post('statuses/update',array('status'=>$msg));
			}
		}
	}
	
	# /admin/comments_actions.php#L55
	public static function adminCommentsActions($core,$co,$action,$redir)
	{
		if ($action == 'publish' 
		 && $core->auth->check('publish,contentadmin',$core->blog->id) 
		 && $core->blog->settings->hornTweeter->active 
		 && $core->blog->settings->hornTweeter->comment_auto 
		 && $core->plugins->moduleExists('TaC'))
		{
			$shortener = $core->blog->settings->hornTweeter->api_url;
			$twitter_msg = $core->blog->settings->hornTweeter->comment_msg;
			
			if (!$twitter_msg) {
				return;
			}
			
			try {
				$tac = new tac($core,'hornTweeter',null);
				
				if (!($tac->checkRegistry())) {
					return;
				}
				if (!($tac->checkAccess())) {
					return;
				}
				
				// user status
				$req_user = '';
				if (!$core->auth->check('contentadmin',$core->blog->id)) {
					$req_user = "AND P.user_id = '".$core->con->escape($core->auth->userID())."' ";
				}
				
				while ($co->fetch()) {
				
					// get old status to tweet only comment goes pusblished
					$post = $core->con->select(
						'SELECT P.post_title, P.post_type, P.post_url '.
						'FROM '.$core->prefix.'comment C '.
						'INNER JOIN '.$core->prefix.'post P ON C.post_id = P.post_id '.
						"WHERE P.blog_id = '".$core->con->escape($core->blog->id)."' ".
						'AND P.post_status == 1 '.
						'AND C.comment_status != 1 '.
						$req_user.
						'AND C.post_id = '.$co->post_id.' '.
						'AND C.comment_id = '.$co->comment_id
					);
					
					// if status goes published
					if (!$post->isEmpty()) {
						
						// shorten url
						$url = $core->blog->url.$core->getPostPublicURL($post->post_type,html::sanitizeURL($post->post_url)).'#c'.$co->comment_id;
						$posturl = '';
						if ($shortener) {
							$posturl = tacTools::shorten($url,false,$shortener);
						}
						$posturl = $posturl ? $posturl : $url;
						
						// parse message
						$msg = str_replace(
							array('%blog%','%user','%title%','%url%'),
							array($core->blog->name,$cur->comment_author,$post->post_title,$posturl),
							$twitter_msg
						);
						
						// send message
						if (!empty($msg)) {
							$tac->post('statuses/update',array('status'=>$msg));
						}
					}
				}
			}
			catch (Exception $e) {
				$core->error->add($e->getMessage());
			}
		}
	}
}
?>