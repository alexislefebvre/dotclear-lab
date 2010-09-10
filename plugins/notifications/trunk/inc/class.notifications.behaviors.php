<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of notifications, a plugin for Dotclear.
# 
# Copyright (c) 2009-2010 Tomtom
# http://blog.zenstyle.fr/
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

class notificationsBehaviors
{
	public static function registerComponents($notifications)
	{
		$notifications->registerComponent('notifications',__('Notifications'));
		$notifications->registerComponent('post',__('Entries'),'images/menu/entries.png');
		$notifications->registerComponent('page',__('Pages'),'index.php?pf=pages/icon.png');
		$notifications->registerComponent('comment',__('Comments'),'images/menu/comments.png');
		$notifications->registerComponent('category',__('Categories'),'images/menu/categories.png');
		$notifications->registerComponent('media',__('Medias'),'images/menu/media.png');
		$notifications->registerComponent('system',__('System'),'images/menu/dashboard.png');
	}
	
	public static function postCreate($cur,$post_id)
	{
		global $core;
		
		if (!notifications::isDisabled('post')) {
			$n = new notifications($core);
			$n->pushNotification(
				sprintf(__('%s created'),'<a href="post.php?id='.$post_id.'">'.__('New Entry').'</a>'),
				'new',
				'post'
			);
		}
	}
	
	public static function postUpdate($cur,$post_id)
	{
		global $core;
		
		if (!notifications::isDisabled('post')) {
			$n = new notifications($core);
			$n->pushNotification(
				sprintf(__('%s updated'),'<a href="post.php?id='.$post_id.'">'.__('Entry').'</a>'),
				'upd',
				'post'
			);
		}
	}
	
	public static function postDelete($post_id)
	{
		global $core;
		
		if (!notifications::isDisabled('post')) {
			$n = new notifications($core);
			$n->pushNotification(
				__('Entry deleted'),
				'del',
				'post'
			);
		}
	}
	
	public static function categoryCreate($cur,$cat_id)
	{
		global $core;
		
		if (!notifications::isDisabled('category')) {
			$n = new notifications($core);
			$n->pushNotification(
				sprintf(__('%s created'),'<a href="category.php?id='.$cat_id.'">'.__('New category').'</a>'),
				'new',
				'category'
			);
		}
	}
	
	public static function categoryUpdate($cur,$cat_id)
	{
		global $core;
		
		if (!notifications::isDisabled('category')) {
			$n = new notifications($core);
			$n->pushNotification(
				sprintf(__('%s updated'),'<a href="category.php?id='.$cat_id.'">'.__('Category').'</a>'),
				'upd',
				'category'
			);
		}
	}
	
	public static function commentCreate($blog,$cur)
	{
		global $core;
		
		if (!notifications::isDisabled('comment')) {
			$n = new notifications($core);
		
			if ($cur->comment_status == '1') {
				$n->pushNotification(
					sprintf(__('%s created'),'<a href="comment.php?id='.$cur->comment_id.'">'.__('New comment').'</a>'),
					'new',
					'comment'
				);
			}
			if ($cur->comment_status == '-2') {
				$n->pushNotification(
					sprintf(__('%s detected'),'<a href="comment.php?id='.$cur->comment_id.'">'.__('New spam').'</a>'),
					'spm',
					'comment'
				);
			}
		}
	}
	
	public static function commentUpdate($blog,$cur,$rs)
	{
		global $core;
		
		if (!notifications::isDisabled('comment')) {
			$n = new notifications($core);
			$n->pushNotification(
				sprintf(__('%s updated'),'<a href="comment.php?id='.$rs->comment_id.'">'.__('Comment').'</a>'),
				'upd',
				'comment'
			);
		}
	}
	
	public static function trackbacks($cur,$comment_id)
	{
		global $core;
		
		if (!notifications::isDisabled('comment')) {
			$n = new notifications($core);
			$n->pushNotification(
				sprintf(__('%s created'),'<a href="comment.php?id='.$comment_id.'">'.__('New trackback').'</a>'),
				'new',
				'comment'
			);
		}
	}
	
	public static function adminPageHTMLHead()
	{
		global $core;
		$ttl = $core->blog->settings->notifications->refresh_time*1000;
		$life = $core->blog->settings->notifications->display_time*1000;
		$sticky = $core->blog->settings->notifications->sticky ? 'true' : 'false';
		$position = $core->blog->settings->notifications->position;
		
		$res = '<script type="text/javascript">'."\n";
		$res .= 'var notifications_ttl = '.$ttl.';'."\n";
		$res .= 'var notifications_life = '.$life.';'."\n";
		$res .= 'var notifications_sticky = '.$sticky.';'."\n";
		$res .= 'var notifications_position = "'.$position.'";'."\n";
		$res .= '</script>'."\n";
		$res .= '<script type="text/javascript" src="index.php?pf=notifications/js/jquery.jgrowl.js"></script>'."\n";
		$res .= '<script type="text/javascript" src="index.php?pf=notifications/js/notifications.js"></script>'."\n";
		$res .= '<link rel="stylesheet" href="index.php?pf=notifications/jgrowl.css" type="text/css" />'."\n";
		$res .= '<link rel="stylesheet" href="index.php?pf=notifications/notifications.css" type="text/css" />'."\n";
		$res .= '<style type="text/css">'."\n";
		
		$notifications = new notifications($core);
		foreach ($notifications->getComponents() as $id => $component) {
			$res .= sprintf('#jGrowl .%s { background: transparent url(%s) no-repeat top left; }',$id,$component['icon'])."\n";
		}
		$res .= '</style>'."\n";
		
		echo $res;
	}
	
	public static function update($core,$ref = '')
	{
		$strReq = 'SELECT MAX(log_id) as max, log_table FROM '.$core->prefix.'log '.
		"WHERE log_table = '".$core->prefix."notifications' GROUP BY log_id";
		
		$id = $core->con->select($strReq)->f(0) + 1;
		
		$strReq =
		'SELECT log_id, log_dt FROM '.$core->prefix."log WHERE user_id = '".
		$core->auth->userID()."' AND blog_id = '".$core->blog->id.
		"' AND log_table = '".$core->prefix."notifications' ";
		
		$rs = $core->con->select($strReq);
		
		if (empty($ref)) {
			$ref = $rs->isEmpty() ? time() + dt::getTimeOffset($core->blog->settings->system->blog_timezone) : strtotime($rs->log_dt);
		}
		
		$cur				= $core->con->openCursor($core->prefix.'log');
		$cur->log_id		= $rs->isEmpty() ? $id : $rs->log_id;
		$cur->user_id		= $core->auth->userID();
		$cur->blog_id		= $core->blog->id;
		$cur->log_table	= $core->prefix.'notifications';
		$cur->log_dt		= date('Y-m-d H:i:s',$ref);
		$cur->log_ip		= http::realIP();
		$cur->log_msg		= __('Last visit on administration interface');
		
		if ($rs->isEmpty()) {
			$cur->insert();
		}
		elseif ($rs->log_dt != $ref) {
			$cur->update("WHERE user_id = '".$core->auth->userID()."' AND blog_id = '".$core->blog->id."' AND log_table = '".$core->prefix."notifications'");
		}
	}
	
	public static function adminUserForm($args)
	{
		if ($args instanceof dcCore) {
			$opts = $args->auth->getOptions();
		}
		elseif ($args instanceof record) {
			$opts = $args->options();
		}
		else {
			$opts = array();
		}
		
		$value = array_key_exists('user_notifications',$opts) ? $opts['user_notifications'] : false;
		
		echo
		'<fieldset><legend>'.__('Notifications').'</legend>'.
		'<p><label class="classic">'.
		form::checkbox('user_notifications',1,$value).
		__('Enabled notifications').
		'</label></p></fieldset>';
	}
	
	public static function setUserNotifications($cur,$user_id = null)
	{
		if (!is_null($user_id)) {
			$cur->user_options['user_notifications'] = isset($_POST['user_notifications']) ? true : false;
		}
	}
	
	public static function exportFull($core,$exp)
	{
		$exp->exportTable('notification');
	}
	
	public static function exportSingle($core,$exp,$blog_id)
	{
		$exp->export('notification',
			'SELECT * '.
			'FROM '.$core->prefix.'notification '.
			"WHERE blog_id = '".$blog_id."'"
		);
	}
}

?>