<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of Jabber Notifications, a plugin for Dotclear.
# 
# Copyright (c) 2007,2008,2011 Alex Pirine <alex pirine.fr>, Olivier Tétard
# 
# Licensed under the GPL version 2.0 license.
# A copy is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')) { return; }

$core->addBehavior('publicAfterCommentCreate', array('publicJabberNotifications', 'sendNotifications'));
$core->addBehavior('publicAfterTrackbackCreate', array('publicJabberNotifications', 'sendNotifications'));

class publicJabberNotifications
{
	public static function sendNotifications(&$cur, $comment_id)
	{
		global $core,$_ctx;
		
		# Spam or Jabber notifications not enabled
		if ($cur->comment_status == -2 || !$core->blog->settings->jabbernotifications->jn_enab) {
			return;
		}
		
		# Get post info if trackback
		if ($cur->comment_trackback) {
			$params = new ArrayObject();
			$params['no_content'] = 1;
			$params['post_id'] = $cur->post_id;
			$_ctx->posts = $core->blog->getPosts($params);
			unset($params);
		}
		
		$author = $_ctx->posts->user_id;
		$blog = $core->blog->id;
		$users = self::getBlogUsers($blog);
		
		$notifications = array();
		
		# Notifications list
		foreach ($users as $user=>$u)
		{
			if (!$u['notify'] || $u['notify'] == 'never' || !$u['jabberid']) {
				continue;
			}

			if (($u['notify'] == 'entries' && $user == $author) ||
				($u['notify'] == 'blog' && $u['default_blog'] == $blog) ||
				($u['notify'] == 'blogs' && $u['own_blog']) ||
				($u['notify'] == 'all' && $u['super'])) {
				$notifications[$user] = $u['jabberid'];
			}
		}
		
		if (empty($notifications)) {
			return;
		}
		
		$s = &$core->blog->settings->jabbernotifications;
		
		$settings = array(
			'enab'=>$s->jn_enab,
			'serv'=>$s->jn_serv,
			'user'=>$s->jn_user,
			'pass'=>@base64_decode($s->jn_pass),
			'port'=>$s->jn_port,
			'con'=>$s->jn_con,
			'gateway'=>$s->jn_gateway
		);
		$j = new jabberNotifier($settings['serv'],$settings['port'],$settings['user'],$settings['pass'],$settings['con']);
		
		$msg = html::decodeEntities(html::clean($cur->comment_content));
		$msg .= "\n\n--\n".
			sprintf(__('Entry: %s <%s>'),$_ctx->posts->post_title,$_ctx->posts->getURL())."\n".
			sprintf(__('Comment by: %s <%s>'),$cur->comment_author,$cur->comment_email)."\n".
			sprintf(__('Website: %s'),$cur->comment_site);
		$msg = sprintf(
				$cur->comment_trackback
					? __('You received a new trackback on the blog \'%s\':')
					: __('You received a new comment on the blog \'%s\':'),
				$core->blog->name)."\n\n".$msg;
		
		$j->setMessage($msg);
		foreach ($notifications as $user=>$jabberid)
		{
			$j->addDestination($jabberid);
		}
		
		if (empty($settings['gateway'])) {
			$j->commit(3);
		} else {
			$j->commitThroughGateway($settings['gateway']);
		}
	}
	
	public static function getBlogUsers($blog_id)
	{
		global $core;
		
		$res = array();
		
		# Super users
		$strReq =
		'SELECT U.user_id AS user_id, user_default_blog, user_options '.
		'FROM '.$core->prefix.'user U '.
		'WHERE user_super = 1';
		
		$rs = $core->con->select($strReq);
		$rs->extend('rsExtUser');
		
		while ($rs->fetch())
		{
			$res[$rs->user_id] = array(
				'default_blog'=>$rs->user_default_blog,
				'notify'=>$rs->option('jn_notify'),
				'jabberid'=>$rs->option('jn_jabberid'),
				'super'=>true,
				'own_blog'=>false
			);
		}
		
		# Blog users (may override super users)
		$strReq =
		'SELECT U.user_id as user_id, user_default_blog, user_options, user_super '.
		'FROM '.$core->prefix.'user U '.
		'JOIN '.$core->prefix.'permissions P ON U.user_id = P.user_id '.
		"WHERE blog_id = '".$core->con->escape($blog_id)."' ";
		
		$rs = $core->con->select($strReq);
		$rs->extend('rsExtUser');

		while ($rs->fetch())
		{
			$res[$rs->user_id] = array(
				'default_blog'=>$rs->user_default_blog,
				'notify'=>$rs->option('jn_notify'),
				'jabberid'=>$rs->option('jn_jabberid'),
				'super'=>(boolean) $rs->user_super,
				'own_blog'=>true
			);
		}
		
		return $res;
	}
}
?>