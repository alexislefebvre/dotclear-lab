<?php
# ***** BEGIN LICENSE BLOCK *****
# This file is part of DotClear.
# Copyright (c) 2005 Olivier Meunier and contributors. All rights
# reserved.
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

class notificationBehaviors
{
	public static function adminUserForm(&$core)
	{
		global $user_options;
		
		$notify_comments = !empty($user_options['notify_comments']) ? $user_options['notify_comments'] : '0';
		
		$opt = array(
			__('never') => '0',
			__('my entries') => 'mine',
			__('all entries') => 'all'
		);
		
		echo
		'<fieldset><legend>'.__('Email notification').'</legend>'.
		'<p><label class="classic">'.
		__('Notify new comments by email:').' '.
		form::combo('notify_comments',$opt,$notify_comments).
		
		'</label></p>'.
		'</fieldset>';
	}
	
	public static function adminBeforeUserUpdate(&$cur,$user_id='')
	{
		$cur->user_options['notify_comments'] = $_POST['notify_comments'];
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
			if (!$users->user_email) {
				continue;
			}
			
			$notification_pref = self::notificationPref(rsExtUser::options($users));
			
			if ($notification_pref == 'all'
			|| ($notification_pref == 'mine' && $users->user_id == $rs->user_id) )
			{
				$ulist[$users->user_id] = $users->user_email;
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
			
			$subject = '['.$core->blog->name.'] '.sprintf(__('New comment on "%s"'),$rs->post_title);
			$subject = mail::B64Header($subject);
			
			$msg = preg_replace('%</p>\s*<p>%msu',"\n\n",$rs->comment_content);
			$msg = html::clean($msg);
			
			$msg .= "\n\n-- \n".
			sprintf(__('Blog: %s'),$core->blog->name)."\n".
			sprintf(__('Entry: %s <%s>'),$rs->post_title,$rs->getPostURL())."\n".
			sprintf(__('Comment by: %s <%s>'),$rs->comment_author,$rs->comment_email)."\n".
			sprintf(__('Website: %s'),$rs->getAuthorURL());
			
			$msg = __('You received a new comment on your blog:')."\n\n".$msg;
			
			foreach ($ulist as $email) {
				$h = array_merge(array('From: '.$email),$headers);
				mail::sendMail($email,$subject,$msg,$h);
			}
		}
	}
	
	protected static function notificationPref($o)
	{
		if (is_array($o) && isset($o['notify_comments'])) {
			return $o['notify_comments'];
		}
		return null;
	}
}
?>