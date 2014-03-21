<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of Dotclear 2.
#
# Copyright (c) 2003-2008 Olivier Meunier and contributors
#               2014 Vincent Danjean
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------
if (!defined('DC_RC_PATH')) {return;}

class notificationBehaviors
{
	public static function adminUserForm($rs)
	{
		global $core;
		global $user_id;
		
		$up = new dcPrefs($core, $user_id);
		self::adminForm($up, 1);
	}

	public static function adminPreferencesForm($core)
	{
		self::adminForm($core->auth->user_prefs, 0);
	}

	private static function adminForm(&$up, $kind) #kind: 0: preferences.php, 1: users.php
	{
		$up->addWorkspace('emailNotification');

		#$notify_from = $core->auth->user_prefs->emailNotification->notify_from;
		#if ($notify_from == null) {
		#	$notify_from = "";
		#}
		$notify_posts = $up->emailNotification->notify_posts;
		if ($notify_posts == null) {
			$notify_posts = 0;
		}
		$notify_comments = $up->emailNotification->notify_comments;
		if ($notify_comments == null) {
			global $user_options;
			if (empty($user_options['notify_comments'])) {
				$notify_comments = 0;
			} else {
				$notify_comments = $user_options['notify_comments'];
				//unset($user_options['notify_comments']);
				$up->emailNotification->put('notify_comments', $notify_comments, 'string');
			}
		}
		
		$opt_comments = array(
			__('never') => '0',
			__('my entries') => 'mine',
			__('all entries') => 'all'
		);

		$opt_posts = array(
			__('never') => '0',
			__('on creation') => 'create',
			__('on publish') => 'publish',
			__('on publish and futher update') => 'update',
			__('on any update (even not published)') => 'always'
		);

		switch ($kind) {
		case 0:
			$start='<fieldset><legend>'.__('Email notification').'</legend>'.
			   '<fieldset><legend>'.__('Posts').'</legend>';
			$middle='</fieldset>'.
			   '<fieldset><legend>'.__('Comments').'</legend>';
			$end='</fieldset></fieldset>';
			break;
		case 1:
			$start='<div><h4>'.__('Email notification').'</h4>'.
			   '<div><h5>'.__('Posts').'</h5>';
			$middle='</div><div><h5>'.__('Comments').'</h5>';
			$end='</div></div>';
			break;
		default:
		}
		echo
		$start.
		'<p><label class="classic" for="notify_posts">'.
		__('Notify new posts by email:').' '.
		form::combo('notify_posts',$opt_posts,$notify_posts).
		'</label></p>'.
		$middle.
		'<p><label class="classic" for="notify_comments">'.
		__('Notify new comments by email:').' '.
		form::combo('notify_comments',$opt_comments,$notify_comments).
		'</label></p>'.
	        '<p><label class="classic" for="notify_spam_comments">'.
		form::checkbox('notify_spam_comments',1,$up->emailNotification->notify_spam_comments).
		__('Notify comments even if they are spams').
		'</label></p>'.
		$end;
	}
	
	public static function adminBeforeUserUpdate($cur,$user_id='')
	{
		global $core;
		$up = new dcPrefs($core, $user_id);
		$up->addWorkspace('emailNotification');
		$up->emailNotification->put('notify_comments', $_POST['notify_comments'], 'string');
		$up->emailNotification->put('notify_spam_comments', !empty($_POST['notify_spam_comments']), 'boolean');
		$up->emailNotification->put('notify_posts', $_POST['notify_posts'], 'string');
	}
	
	public static function afterCommentCreate($cur,$comment_id)
	{
		global $core;
		
		# Information on comment author and post author
		$rs = $core->auth->sudo(array($core->blog,'getComments'), array('comment_id'=>$comment_id));
		
		if ($rs->isEmpty()) {
			return;
		}
		
		# Information on blog users
		$strReq =
		'SELECT U.user_id, user_email '.
		'FROM '.$core->blog->prefix.'user U JOIN '.$core->blog->prefix.'permissions P ON U.user_id = P.user_id '.
		"WHERE blog_id = '".$core->con->escape($core->blog->id)."' ".
		'UNION '.
		'SELECT user_id, user_email '.
		'FROM '.$core->blog->prefix.'user '.
		'WHERE user_super = 1 ';
		
		$users = $core->con->select($strReq);
		
		# Create notify list
		$ulist = array();
		while ($users->fetch()) {
			if (!$users->user_email) {
				continue;
			}
			$opts = new dcWorkspace($core, $users->user_id, 'emailNotification');
			
			$notification_pref = $opts->notify_comments;
			
			if ($notification_pref == 'all'
			|| ($notification_pref == 'mine' && $users->user_id == $rs->user_id) )
			{
				if ($cur->comment_status != -2 || $opts->notify_spam_comments) {
					$ulist[$users->user_id] = $users->user_email;
				}
			}
		}
		
		if (count($ulist) > 0)
		{
			# Author of the post wants to be notified by mail
			$headers = array(
				'Reply-To: '.$rs->comment_email,
				'Content-Type: text/plain; charset=UTF-8;',
				'X-Mailer: Dotclear',
				'X-Blog-Id: '.mail::B64Header($core->blog->id),
				'X-Blog-Name: '.mail::B64Header($core->blog->name),
				'X-Blog-Url: '.mail::B64Header($core->blog->url)
			);
			
			$subject = '['.$core->blog->name.'] '.sprintf(__('"%s" - New comment'),$rs->post_title);
			$subject = mail::B64Header($subject);
			
			$msg = preg_replace('%</p>\s*<p>%msu',"\n\n",$rs->comment_content);
			$msg = html::clean($msg);

			$cs = $core->blog->getAllCommentStatus($status);
			if (isset($cs[$cur->comment_status]))
			{
				$status = $cs[$cur->comment_status];
			}
			else
			{
				# unknown status
				$status = $cur->comment_status;
			}
			
			$msg .= "\n\n-- \n".
			sprintf(__('Blog: %s'),$core->blog->name)."\n".
			sprintf(__('Entry: %s <%s>'),$rs->post_title,$rs->getPostURL())."\n".
			sprintf(__('Comment by: %s <%s>'),$rs->comment_author,$rs->comment_email)."\n".
			sprintf(__('Website: %s'),$rs->getAuthorURL())."\n".
			sprintf(__('Comment status: %s'),$status)."\n".
			sprintf(__('Edit this comment: <%s>'),DC_ADMIN_URL.
				((substr(DC_ADMIN_URL,-1) != '/') ? '/' : '').
				'comment.php?id='.$cur->comment_id.
				'&switchblog='.$core->blog->id)."\n".
			__('You must log in on the backend before clicking on this link to go directly to the comment.');
			
			$msg = __('You received a new comment on your blog:')."\n\n".$msg;

			# --BEHAVIOR-- emailNotificationAppendToEmail
			$msg .= $core->callBehavior('emailNotificationAppendToEmail',$cur);
			
			foreach ($ulist as $email) {
				$h = array_merge(array('From: '.$email),$headers);
				mail::sendMail($email,$subject,$msg,$h);
			}
		}
	}
	
	private static $posts_status=array();
	private static function postNotify(&$blog, $post_id, $prev_status, $cur_status, $update=false)
	{
		global $core;
		
		if (!array_key_exists($post_id, self::$posts_status) && isset($prev_status)) {
			error_log('Strange state for post '.$post_id.' with previous status '.$prev_status);
		}
		self::$posts_status[$post_id]=$cur_status;

		# Information on post author
		$rs = $core->auth->sudo(array($core->blog,'getPosts'), array('post_id'=>$post_id, 'no_content'));
		
		if ($rs->isEmpty()) {
			return;
		}

		# Information on blog users
		$strReq =
		'SELECT U.user_id, user_email '.
		'FROM '.$core->blog->prefix.'user U JOIN '.$core->blog->prefix.'permissions P ON U.user_id = P.user_id '.
		"WHERE blog_id = '".$core->con->escape($core->blog->id)."' ".
		'UNION '.
		'SELECT user_id, user_email '.
		'FROM '.$core->blog->prefix.'user '.
		'WHERE user_super = 1 ';
		
		$users = $core->con->select($strReq);
		
		# Create notify list
		$ulist = array();
		while ($users->fetch()) {
			if (!$users->user_email) {
				continue;
			}
			$opts = new dcWorkspace($core, $users->user_id, 'emailNotification');
			
			$notification_pref = $opts->notify_posts;
			
			$loc_update=$update;
			switch ($notification_pref) {
			case 'create':
				if ($prev_status != null) break;
				# else fall-through
			case 'always':
				$ulist[$users->user_id] = $users->user_email;
				break;
			case 'publish':
				$loc_update = false;
				# fall-through
			case 'update':
				if ($cur_status != 1) break ;
				if ($prev_status == 1 && ! $loc_update) break ;
				$ulist[$users->user_id] = $users->user_email;
			}
		}

		if (count($ulist) > 0)
		{
			$info=array();
			$reason=__('Updated post');
			if ($cur_status == 1) {
				if ($prev_status == 1) {
					array_push($info, __("Post is published"));
				} else {
					array_push($info, __("Post just become published"));
				}
				$reason=__('New published post');
			} elseif (!$update) {
				$reason=sprintf(__('Post becomes %s'), $blog->getPostStatus($cur_status));
			}
			if (!isset($prev_status)) {
				array_unshift($info, __("Post was created"));
				$reason=__('New post');
			} else {
				if ($update) {
					array_push($info, __("Post was edited"));
				}
				if ($cur_status != $prev_status) {
					array_push($info, sprintf(__("Post state changed from %s to %s"),$blog->getPostStatus($prev_status),$blog->getPostStatus($cur_status)));
				}
			}

			# Author of the post wants to be notified by mail
			$headers = array(
				'Reply-To: '.$rs->user_email,
				'Content-Type: text/plain; charset=UTF-8;',
				'X-Mailer: Dotclear',
				'X-Blog-Id: '.mail::B64Header($core->blog->id),
				'X-Blog-Name: '.mail::B64Header($core->blog->name),
				'X-Blog-Url: '.mail::B64Header($core->blog->url)
			);
			
			$subject = '['.$core->blog->name.'] '.sprintf('"%s" - %s',$rs->post_title, $reason);
			$subject = mail::B64Header($subject);
			
			$msg = join("\n", $info);
			
			#$msg = preg_replace('%</p>\s*<p>%msu',"\n\n",$rs->comment_content);
			#$msg = html::clean($msg);
			$msg .= "\n\n";
			if ($cur_status == 1) {
				$msg .= sprintf(__("Public URL: <%s>"), $rs->getURL())."\n";
			}
			$msg .= sprintf(__("Edit this post: <%s>"), DC_ADMIN_URL.
					((substr(DC_ADMIN_URL,-1) != '/') ? '/' : '').
					'post.php?id='.$rs->post_id.
					'&switchblog='.$blog->id
					)."\n";

			$msg .= "\n\n-- \n".
			sprintf(__('Blog: %s'),$core->blog->name)."\n".
			sprintf(__('Entry: %s <%s>'),$rs->post_title,$rs->getURL())."\n".
			sprintf(__('Author: %s (%s %s) <%s>'),$rs->user_displayname, $rs->user_firstname, $rs->user_name, $rs->user_email)."\n".
			sprintf(__('Website: %s'),$rs->user_url)."\n".
			sprintf(__('Entry status: %s'),$blog->getPostStatus($cur_status))."\n".
			sprintf(__('Edit this entry: <%s>'),DC_ADMIN_URL.
				((substr(DC_ADMIN_URL,-1) != '/') ? '/' : '').
				'post.php?id='.$rs->post_id.
				'&switchblog='.$core->blog->id)."\n".
			__('You must log in on the backend before clicking on this link to go directly to the post.');
			
			if (isset($prev_status)) {
				$msg = __('Modification of a post on your blog:')."\n\n".$msg;
			} else {
				$msg = __('Creation of a post on your blog:')."\n\n".$msg;
			}

			# --BEHAVIOR-- emailNotificationAppendToEmailPost
			$msg .= $core->callBehavior('emailNotificationAppendToEmailPost',$post_id);
			
			foreach ($ulist as $email) {
				$h = array_merge(array('From: '.$email),$headers);
				#mail::sendMail($email,$subject,$msg,$h);
				mail::sendMail('vincent@danjean.fr',$subject,$msg,$h);
			}
		}
	}

	public static function adminAfterPostCreate(&$cur, $post_id)
	{
		global $core;
		self::postNotify($core->blog, $post_id, null, $cur->post_status, true);
	}

	private static $old_status;
	public static function adminBeforePostUpdate(&$cur, $post_id)
	{
		self::$old_status=$cur->post_status;
	}

	public static function adminAfterPostUpdate(&$cur, $post_id)
	{
		global $core;
		self::postNotify($core->blog, $post_id, self::$old_status, $cur->post_status, true);
	}

	private static $posts_status_loaded=false;
	public static function loadBlogPostsStatus()
	{
		global $core;
		if (self::$posts_status_loaded) {
			return;
		}
		$rs=$core->blog->getPosts(array('no_content'));
		while ($rs->fetch()) {
			self::$posts_status[$rs->post_id]=$rs->post_status;
		}
		self::$posts_status_loaded=true;
		return;
	}

	public static function coreBlogAfterTriggerBlog(&$cur)
	{
		if (!self::$posts_status_loaded) {
			error_log('posts status not loaded!');
			return;
		}
		global $core;
		$rs=$core->blog->getPosts(array('no_content'));
		while ($rs->fetch()) {
			if (!array_key_exists($rs->post_id, self::$posts_status)) {
				self::postNotify($core->blog, $rs->post_id, null, $rs->post_status);
			} elseif (self::$posts_status[$rs->post_id] != $rs->post_status) {
				self::postNotify($core->blog, $rs->post_id, self::$posts_status[$rs->post_id], $rs->post_status);
			}
		}
	}
}
