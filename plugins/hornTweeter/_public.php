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

if (!defined('DC_RC_PATH')){return;}

# Settings ns
$core->blog->settings->addNamespace('hornTweeter');

# Public behaviors
$core->addBehavior('publicBeforeDocument',array('publicHornTweeter','publicBeforeDocument'));
$core->addBehavior('publicAfterCommentCreate',array('publicHornTweeter','publicAfterCommentCreate'));

class publicHornTweeter
{
	# Try to tweet pusblished scheduled entries
	public static function publicBeforeDocument($core)
	{
		if (!in_array($core->url->type,array('default','feed'))
		 || !empty($_GET['q']) 
		 || !$core->blog->setting->hornTweeter->active 
		 || !$core->plugins->moduleExists('TaC'))
		{
			return;
		}
		
		$shortener = $core->blog->settings->hornTweeter->api_url;
		$twitter_msg = $core->blog->settings->hornTweeter->post_msg;
		
		if (!$twitter_msg) {
			return;
		}
		
		try {
			// get unpublished entries
			$rs = $this->con->select(
				'SELECT post_id, post_dt, post_tz '.
				'FROM '.$core->prefix.'post '.
				'WHERE post_status = -1 '.
				"AND blog_id = '".$core->con->escape($core->blog->id)."' "
			);
			
			// nothing scheduled
			if ($rs->isEmpty()) {
				return;
			}
			
			// compare dates
			$now = dt::toUTC(time());
			$to_change = array();
			
			while ($rs->fetch())
			{
				# Now timestamp with post timezone
				$now_tz = $now + dt::getTimeOffset($rs->post_tz,$now);
				
				# Post timestamp
				$post_ts = strtotime($rs->post_dt);
				
				# If now_tz >= post_ts, we publish the entry
				if ($now_tz >= $post_ts) {
					$to_change[] = (integer) $rs->post_id;
				}
			}
			
			// nothing goes pusblished
			if (empty($to_change)) {
				return;
			}
			
			// load TaC
			$tac = new tac($core,'hornTweeter',null);
			
			// check registry
			if (!($tac->checkRegistry())) {
				return;
			}
			
			// check access
			if (!($tac->checkAccess())) {
				return;
			}
			
			// get posts info
			$posts_params = array(
				'no_content' => true,
				'post_id' => $to_change,
				'post_type' => ''
			);
			$posts = $core->auth->sudo($core->blog,'getPosts',$posts_params);
			
			// no post?!
			if ($posts->isEmpty()) {
				return;
			}
			
			while($posts->fetch())
			{
				// shorten url
				$posturl = '';
				if ($shortener) {
					$posturl = tacTools::shorten($posts->getURL(),false,$shortener);
				}
				$posturl = $posturl ? $posturl : $posts->getURL();
				
				// get tags
				$meta_list = '';
				$metas_params = array('meta_type'=>'tag','post_id'=>$posts->post_id);
				$metas = $core->auth->sudo($core->meta,'getMetadata',$metas_params);
				if (!$metas->isEmpty()) {
					while($metas->fetch()) {
						$meta_list .= ' #'.$metas->meta_id;
					}
				}
				
				// parse message
				$msg = str_replace(
					array('%blog%','%title%','%url%','%author%','%tags%'),
					array($core->blog->name,$posts->post_title,$posturl,$posts->getAuthorCN(),$meta_list),
					$twitter_msg
				);
				
				// send message
				if (!empty($msg)) {
					$tac->post('statuses/update',array('status'=>$msg));
				}
			}
		}
		catch (Exception $e) { }
	}
	
	# /inc/public/lib.urlhandlers.php#L424
	public static function publicAfterCommentCreate($cur,$comment_id)
	{
		global $core;
		
		if ($core->blog->settings->hornTweeter->active 
		 && $cur->comment_status == 1 
		 && $core->blog->settings->hornTweeter->comment_auto 
		 && $core->plugins->moduleExists('TaC'))
		{
			$shortener = $core->blog->settings->hornTweeter->api_url;
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
}
?>