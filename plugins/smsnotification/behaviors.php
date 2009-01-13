<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of Dotclear 2.
#
# Copyright (c) 2003-2008 Olivier Meunier and contributors
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------

class smsNotificationBehaviors
{
	public static function adminUserForm(&$core)
	{
		global $user_options;
		
		$sms_notify_comments = !empty($user_options['sms_notify_comments']) ? $user_options['sms_notify_comments'] : '0';
		$google_calendar_user = !empty($user_options['google_calendar_user']) ? $user_options['google_calendar_user'] : '';
		$google_calendar_passwd = !empty($user_options['google_calendar_passwd']) ? $user_options['google_calendar_passwd'] : '';
		$google_calendar_feed = !empty($user_options['google_calendar_feed']) ? $user_options['google_calendar_feed'] : '';
		
		$opt = array(
			__('never') => '0',
			__('my entries') => 'mine',
			__('all entries') => 'all'
		);
		
		echo
		'<fieldset><legend>'.__('SMS notification').'</legend>'.
		'<p><label class="classic">'.
		__('Notify new comments by SMS:').' '.
		form::combo('sms_notify_comments',$opt,$sms_notify_comments).
		'</label></p>'.
		'<p><label class="classic">'.
		__('Google Calendar user:').' '.
		form::field('google_calendar_user',40,40,$google_calendar_user).
		'</label></p>'.
		'<p><label class="classic">'.
		__('Google Calendar password:').' '.
		form::password('google_calendar_passwd',40,40,$google_calendar_passwd).
		'</label></p>'.
		'<p><label class="classic">'.
		__('Google Calendar user:').' '.
		form::field('google_calendar_feed',80,80,$google_calendar_feed).
		'</label></p>'.
		'</fieldset>';
	}
	
	public static function adminBeforeUserUpdate(&$cur,$user_id='')
	{
		$cur->user_options['sms_notify_comments'] = $_POST['sms_notify_comments'];
		$cur->user_options['google_calendar_user'] = $_POST['google_calendar_user'];
		$cur->user_options['google_calendar_passwd'] = $_POST['google_calendar_passwd'];
		$cur->user_options['google_calendar_feed'] = $_POST['google_calendar_feed'];
	}
	
	public static function publicAfterCommentCreate(&$cur,$comment_id)
	{
		# We don't want notification for spam
		if ($cur->comment_status == -2) {
			return;
		}
		
		global $core;
		
		# Information on comment author and post author
		$rs = $core->auth->sudo(array($core->blog,'getComments'), array('comment_id'=>$comment_id));
		
		if ($rs->isEmpty()) {
			return;
		}
		
		# Information on blog users
		$strReq =
		'SELECT U.user_id, user_email, user_options '.
		'FROM '.$core->blog->prefix.'user U JOIN '.$core->blog->prefix.'permissions P ON U.user_id = P.user_id '.
		"WHERE blog_id = '".$core->con->escape($core->blog->id)."' ".
		'UNION '.
		'SELECT user_id, user_email, user_options '.
		'FROM '.$core->blog->prefix.'user '.
		'WHERE user_super = 1 ';
		
		$users = $core->con->select($strReq);
		
		# Create notify list
		$ulist = array();
		while ($users->fetch()) {
			$opt = rsExtUser::options($users);
			if (is_array($opt) && isset($opt['sms_notify_comments'])) {
				$notification_pref = $opt['sms_notify_comments'];
				$gcal_user=array(
					'user' => $opt['google_calendar_user'],
					'passwd' => $opt['google_calendar_passwd'],
					'feed' => $opt['google_calendar_feed']);
				if ($notification_pref == 'all'
				|| ($notification_pref == 'mine' && $users->user_id == $rs->user_id) )
				{
					$ulist[] = $gcal_user;
				}
				
			}
		}
		
		if (count($ulist) > 0)
		{
			$localtime_assoc = localtime(time(), true);
			$heure = time() - 3270+3600; // -1 heures + 5 minutes + 30 secondes
			$now = date('H:i:s', $heure);
			// On lui ajoute 15 sec
			$heure15sec = time() - 2835+3600; // -1 heures + 15 minutes + 15 secondes
			$now15sec = date('H:i:s', $heure15sec); 
			$msg = preg_replace('%</p>\s*<p>%msu',"\n\n",$rs->comment_content);
			$msg = html::clean($msg);
			$event = array();
			$event["title"] = "Comment by ".$rs->comment_author. "on [".$core->blog->name."]";
			$event["content"] = "";
			$event["where"] = $msg;
			$event["startDay"] = date('Y-m-d',$heure);
			$event["startTime"] = $now;
			$event["endDay"] = date('Y-m-d', $heure15sec);
			$event["endTime"] = $now15sec;
			foreach ($ulist as $u) {
				$gc = new GoogleCalendarWrapper($u['user'],$u['passwd']);
				$gc->login();
				$gc->add_event($event);
			}
		}
	}
	
}
?>
