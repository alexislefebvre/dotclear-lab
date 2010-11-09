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
	public static function registerComponents($components)
	{
		$components[] = array('notifications',__('Notifications'));
		$components[] = array('post',__('Entries'),'images/menu/entries.png');
		$components[] = array('page',__('Pages'),'index.php?pf=pages/icon.png');
		$components[] = array('comment',__('Comments'),'images/menu/comments.png');
		$components[] = array('category',__('Categories'),'images/menu/categories.png');
		$components[] = array('system',__('System'),'images/menu/dashboard.png');
	}
	
	public static function sendNotifications($notification)
	{
		global $core;
		
		$n = new notifications($core);
		$cur = $core->con->openCursor($core->prefix.'notification');
		$cur->notification_msg = isset($notification[0]) && $notification[0] !== '' ? $notification[0] : null;
		$cur->notification_component = isset($notification[1]) && $notification[1] !== '' ? $notification[1] : null;
		$cur->notification_type = isset($notification[2]) && $notification[2] !== '' ? $notification[2] : null;
			
		try {
			$n->addNotification($cur);
		} catch (Exception $e) {
			$cur->notification_msg = sprintf(__('Impossible to push notification : "%s" because : "%s"'),$msg,$e->getMessage());
			$cur->notification_component = 'notifications';
			$cur->notification_type = 'err';
			$n->addNotification($cur);
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
	
	public static function autoClean()
	{
		global $core;
		
		$strReq = 
		"DELETE FROM ".$core->prefix."notification WHERE blog_id = '".$core->blog->id.
		"' AND notification_dt < (SELECT MIN(log_dt) AS min FROM ".$core->prefix.
		"log WHERE blog_id = '".$core->blog->id."' AND log_table = 'notifications')";
		
		if ($core->blog->settings->notifications->auto_clean) {
			$core->con->execute($strReq);
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
	
	public static function adminAfterPostCreate($cur,$post_id)
	{
		global $core;
		
		$n = array(
			sprintf(__('%s created'),'<a href="post.php?id='.$post_id.'">'.__('New entry').'</a>'),
			'post','new'
		);
		
		$core->callBehavior('notificationsSender',$n);	
	}
	
	public static function adminAfterPostUpdate($cur,$post_id)
	{
		global $core;
		
		$n = array(
			sprintf(__('%s updated'),'<a href="post.php?id='.$post_id.'">'.__('Entry').'</a>'),
			'post','upd'
		);
		
		$core->callBehavior('notificationsSender',$n);	
	}
	
	public static function adminBeforePostDelete($post_id)
	{
		global $core;
		
		$n = array(
			__('Entry deleted'),
			'post','del'
		);
		
		$core->callBehavior('notificationsSender',$n);
	}
	
	public static function adminAfterPageCreate($cur,$post_id)
	{
		global $core;
		
		$n = array(
			sprintf(__('%s created'),'<a href="plugin.php?p=pages&act=page&id='.$post_id.'">'.__('New page').'</a>'),
			'page','new'
		);
		
		$core->callBehavior('notificationsSender',$n);	
	}
	
	public static function adminAfterPageUpdate($cur,$post_id)
	{
		global $core;
		
		$n = array(
			sprintf(__('%s updated'),'<a href="plugin.php?p=pages&act=page&id='.$post_id.'">'.__('Page').'</a>'),
			'page','upd'
		);
		
		$core->callBehavior('notificationsSender',$n);	
	}
	
	public static function adminBeforePageDelete($post_id)
	{
		global $core;
		
		$n = array(
			__('Page deleted'),
			'page','del'
		);
		
		$core->callBehavior('notificationsSender',$n);
	}
	
	public static function adminAfterCategoryCreate($cur,$cat_id)
	{
		global $core;
		
		$n = array(
			sprintf(__('%s created'),'<a href="category.php?id='.$cat_id.'">'.__('New category').'</a>'),
			'category','new'
		);
		
		$core->callBehavior('notificationsSender',$n);
	}
	
	public static function adminAfterCategoryUpdate($cur,$cat_id)
	{
		global $core;
		
		$n = array(
			sprintf(__('%s updated'),'<a href="category.php?id='.$cat_id.'">'.__('Category').'</a>'),
			'category','upd'
		);
		
		$core->callBehavior('notificationsSender',$n);
	}
	
	public static function coreAfterCommentCreate($blog,$cur)
	{
		global $core;
		
		$n = array(
			ssprintf(__('%s created'),'<a href="comment.php?id='.$cur->comment_id.'">'.__('New comment').'</a>'),
			'comment','new'
		);
		
		$core->callBehavior('notificationsSender',$n);
	}
	
	public static function coreAfterCommentUpdate($blog,$cur,$rs)
	{
		global $core;
		
		$n = array(
			sprintf(__('%s updated'),'<a href="comment.php?id='.$rs->comment_id.'">'.__('Comment').'</a>'),
			'comment','upd'
		);
		
		$core->callBehavior('notificationsSender',$n);
	}
	
	public static function publicAfterTrackbackCreate($cur,$comment_id)
	{
		global $core;
		
		$n = array(
			sprintf(__('%s created'),'<a href="comment.php?id='.$comment_id.'">'.__('New trackback').'</a>'),
			'comment','new'
		);
		
		$core->callBehavior('notificationsSender',$n);
	}
	
	public static function adminBeforeBlogSettingsUpdate($blog_settings)
	{
		global $core;
		
		$n = array(
			sprintf(__('Blog %s updated'),'<a href="index.php?switchblog='.$core->blog->id.'">'.$core->blog->name.'</a>'),
			'system','upd'
		);
		
		$core->callBehavior('notificationsSender',$n);
	}
	
	public static function themeAfterDelete($theme)
	{
		global $core;
		
		$n = array(
			sprintf(__('Theme %s deleted'),$theme->name),
			'system','del'
		);
		
		$core->callBehavior('notificationsSender',$n);
	}
	
	public static function pluginsAfterDelete($plugin)
	{
		global $core;
		
		$n = array(
			sprintf(__('Plugin %s deleted'),$plugin->name),
			'system','del'
		);
		
		$core->callBehavior('notificationsSender',$n);
	}
	
	public static function adminAfterUserCreate($cur,$user_id)
	{
		global $core;
		
		$n = array(
			sprintf(__('User %s created'),'<a href="user.php?id='.$user_id.'">'.dcUtils::getUserCN($user_id,$cur->user_name,$cur->user_firstname,$cur->user_displayname).'</a>'),
			'system','new'
		);
		
		$core->callBehavior('notificationsSender',$n);
	}
	
	public static function adminAfterUserUpdate($cur,$user_id)
	{
		global $core;
		
		$n = array(
			sprintf(__('User %s updated'),'<a href="user.php?id='.$user_id.'">'.dcUtils::getUserCN($user_id,$cur->user_name,$cur->user_firstname,$cur->user_displayname).'</a>'),
			'system','upd'
		);
		
		$core->callBehavior('notificationsSender',$n);
	}
	
	public static function adminBeforeUserDelete($user_id)
	{
		global $core;
		
		$n = array(
			sprintf(__('User %s deleted'),$user_id),
			'system','del'
		);
		
		$core->callBehavior('notificationsSender',$n);
	}
}

?>