<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of notifications, a plugin for Dotclear.
# 
# Copyright (c) 2009 Tomtom
# http://blog.zenstyle.fr/
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

class notificationsBehaviors
{
	public static function postCreate($cur,$post_id)
	{
		global $core;

		$config = $core->blog->notifications->getConfig();

		if ($config['posts']) {
			$msg = sprintf(__('%s created'),'<a href="post.php?id='.$post_id.'">'.__('New entry').'</a>');
			$core->blog->notifications->add('new',$msg,$cur->user_id);
		}
	}

	public static function postUpdate($cur,$post_id)
	{
		global $core;

		$config = $core->blog->notifications->getConfig();

		if ($config['posts']) {
			$msg = sprintf(__('%s updated'),'<a href="post.php?id='.$post_id.'">'.__('Entry').'</a>');
			$core->blog->notifications->add('upd',$msg,$cur->user_id);
		}
	}

	public static function postDelete($post_id)
	{
		global $core;

		$config = $core->blog->notifications->getConfig();

		if ($config['posts']) {
			$core->blog->notifications->add('del',__('Entry deleted'));
		}
	}

	public static function categoryCreate($cur,$cat_id)
	{
		global $core;

		$config = $core->blog->notifications->getConfig();

		if ($config['categories']) {
			$msg = sprintf(__('%s created'),'<a href="category.php?id='.$cat_id.'">'.__('New category').'</a>');
			$core->blog->notifications->add('new',$msg,$cur->user_id);
		}
	}

	public static function categoryUpdate($cur,$cat_id)
	{
		global $core;

		$config = $core->blog->notifications->getConfig();

		if ($config['categories']) {
			$msg = sprintf(__('%s updated'),'<a href="category.php?id='.$cat_id.'">'.__('Category').'</a>');
			$core->blog->notifications->add('upd',$msg,$cur->user_id);
		}
	}

	public static function commentCreate($blog,$cur)
	{
		global $core;

		$config = $core->blog->notifications->getConfig();

		if ($config['comments'] && $blog->id == $core->blog->id && $cur->comment_status == '1') {
			$msg = sprintf(__('%s created'),'<a href="comment.php?id='.$cur->comment_id.'">'.__('New comment').'</a>');
			$core->blog->notifications->add('new',$msg,$cur->comment_author);
		}

		if ($config['spams'] && $blog->id == $core->blog->id && $cur->comment_status == '-2') {
			$msg = sprintf(__('%s detected'),'<a href="comment.php?id='.$cur->comment_id.'">'.__('New spam').'</a>');
			$core->blog->notifications->add('spm',$msg,$cur->comment_author);
		}
	}

	public static function commentUpdate($blog,$cur,$rs)
	{
		global $core;

		$config = $core->blog->notifications->getConfig();

		if ($config['comments'] && $blog->id == $core->blog->id) {
			$msg = sprintf(__('%s updated'),'<a href="comment.php?id='.$rs->comment_id.'">'.__('Comment').'</a>');
			$core->blog->notifications->add('upd',$msg,$rs->comment_author);
		}
	}

	public static function trackbacks($cur,$comment_id)
	{
		global $core;

		$config = $core->blog->notifications->getConfig();

		if ($config['trackbacks']) {
			$msg = sprintf(__('%s created'),'<a href="comment.php?id='.$comment_id.'">'.__('New trackback').'</a>');
			$core->blog->notifications->add('new',$msg,$cur->comment_author);
		}
	}

	public static function p404()
	{
		global $core;

		$config = $core->blog->notifications->getConfig();

		if ($config['404'] && $core->url->type == '404') {
			$msg = sprintf(__('New 404 error page displayed about %s'),'<a href="'.$core->blog->url.$_SERVER['QUERY_STRING'].'">'.__('this URL').'</a>');
			$core->blog->notifications->add('err',$msg);
		}
	}

	public static function headers()
	{
		global $core;

		$config = $core->blog->notifications->getConfig();

		$res = '<script type="text/javascript">'."\n";
		$res .= 'var notifications_ttl = "'.($config['refresh_time']*1000).'";'."\n";
		$res .= '</script>'."\n";
		$res .= '<script type="text/javascript" src="'.DC_ADMIN_URL.'index.php?pf=notifications/js/jgrowl/jgrowl.min.js"></script>'."\n";
		$res .= '<script type="text/javascript" src="'.DC_ADMIN_URL.'index.php?pf=notifications/js/notifications.min.js"></script>'."\n";
		$res .= '<link rel="stylesheet" href="'.DC_ADMIN_URL.'index.php?pf=notifications/js/jgrowl/jgrowl.min.css" type="text/css" />'."\n";
		return $res;
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
			$ref = $rs->isEmpty() ? time() + dt::getTimeOffset($core->blog->settings->blog_timezone) : strtotime($rs->log_dt);
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

	public static function clean($core)
	{
		$strReq = 
		"DELETE FROM ".$core->prefix."notification WHERE blog_id = '".$core->blog->id.
		"' AND notification_dt < (SELECT MIN(log_dt) AS min FROM ".$core->prefix.
		"log WHERE blog_id = '".$core->blog->id."')";

		$config = $core->blog->notifications->getConfig();

		if ((isset($config['autoclean'])) && ($config['autoclean'])) {
			$core->con->execute($strReq);
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