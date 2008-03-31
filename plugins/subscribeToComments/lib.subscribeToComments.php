<?php
# ***** BEGIN LICENSE BLOCK *****
#
# This file is part of Subscribe to comments.
# Copyright 2008 Moe (http://gniark.net/)
#
# Subscribe to comments is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 3 of the License, or
# (at your option) any later version.
#
# Subscribe to comments is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program.  If not, see <http://www.gnu.org/licenses/>.
#
# Icon (icon.png) is from Silk Icons : http://www.famfamfam.com/lab/icons/silk/
#
# Inspired by http://txfx.net/code/wordpress/subscribe-to-comments/
#
# ***** END LICENSE BLOCK *****

/**
@ingroup Subscribe to comments
@brief Generic functions
*/
class subscribeToComments
{
	/** check if the string is a valid email address
	@param	email	<b>string</b>	Email address
	@return	<b>boolean</b> Is an email address ?
	*/
	public static function checkEmail(&$email)
	{
		$email = urldecode($email);
		if (!text::isEmail($email))
		{
			throw new Exception(__('Invalid email address.'));
		}	
	}

	/** check if the string is a valid key
	@param	key	<b>string</b>	Key
	@return	<b>boolean</b> Is a key ?
	\see http://www.php.net/manual/fr/function.md5.php#40251
	*/
	public static function checkKey($key)
	{
		if (!(preg_match('/^[a-f0-9]{40}$/',$key)))
		{
			throw new Exception(__('Invalid key.'));
		}
	}

	/**
	remove old temporary keys
	*/
	public static function cleanKeys()
	{
		# 1206375551
		global $core;

		if ($_SERVER['REQUEST_TIME'] <= 
			$core->blog->settings->subscribetocomments_clean_keys) {return;}

		$core->blog->settings->setNameSpace('subscribetocomments');
		$core->blog->settings->put('subscribetocomments_clean_keys',strtotime('+1 hour'),
		'integer','Clean temporary keys');

		# delete old temporary keys
		$cur = $core->con->openCursor($core->prefix.'comment_subscriber');
		$cur->temp_key = null;
		$cur->temp_expire = null;
		$cur->update('WHERE ((temp_expire IS NOT NULL)'.
			' AND (temp_expire < \''.date('Y-m-d H:i:s').'\'))');
	}

	/**
	get the URL of the subscriptions page
	@return	<b>string</b> URL
	*/
	public static function url()
	{
		global $core;

		$core->url->register('subscribetocomments','subscribetocomments',
			'^subscribetocomments(/.+)?$',array('subscribeToCommentsDocument','page'));
		return($core->blog->url.$core->url->getBase('subscribetocomments'));
	}

	/**
	get informations about a post
	@param	post_id <b>integer</b> Post ID
	@return	<b>array</b>	Array with informations
	*/
	public static function getPost($post_id)
	{
		global $core;

		$rs = $core->blog->getPosts(array('no_content' => true, 'post_id' => $post_id,
			'post_open_comment' => 1));

		if ($rs->isEmpty()) {return(false);}

		$array['title'] = $rs->post_title;
		# from getURL()
		$array['url'] = $core->blog->url.$core->url->getBase('post').'/'.
			html::sanitizeURL($rs->post_url);
		# /from getURL()
		return($array);
	}

	/**
	send emails
	@param	cur <b>cursor</b> Cursor
	@param	comment_id <b>integer</b> Comment ID
	*/
	public static function send($cur,$comment_id)
	{
		# from emailNotification (modified)
		# We don't want notification for spam
		if ($cur->comment_status != 1) {
			return;
		}
		# /from emailNotification

		global $core;

		# we send only one mail to notify the subscribers
		# won't send multiple emails when updating an email from the backend
		$rs = $core->con->select(
			'SELECT sent FROM '.$core->prefix.'comment_notification '.
			'WHERE (comment_id = '.$comment_id.') AND (sent = 1);'
		);

		if ($rs->isEmpty())
		{
			# get the subscribers' email addresses
			$rs = $core->con->select(
				'SELECT S.id, S.email, S.user_key FROM '.
				$core->prefix.'comment_subscriber S '.
				'INNER JOIN '.$core->prefix.'meta M ON '.
				'(M.post_id = \''.$cur->post_id.'\') AND '.
				'(M.meta_type = \'subscriber\') AND (S.id = M.meta_id)'.
				' AND (S.email != \''.$cur->comment_email.'\')'.
				' AND (S.status = \'1\');'
			);

			if (!$rs->isEmpty())
			{
				# remember that the comment's notification was sent
				$cur_sent = $core->con->openCursor($core->prefix.'comment_notification');
				$cur_sent->comment_id = $comment_id;
				$cur_sent->sent = 1;
				$cur_sent->insert();

				$post = self::getPost($cur->post_id);

				while ($rs->fetch())
				{
					# email
					$subject = sprintf(
						$core->blog->settings->subscribetocomments_comment_subject,
						$core->blog->name,$core->blog->url,$rs->email,
						subscriber::pageLink($rs->email,$rs->user_key),
						$post['title'],$post['url'],$post['url'].'#c'.$comment_id,
						$cur->comment_author,html::clean($cur->comment_content));
					$content = sprintf(
						$core->blog->settings->subscribetocomments_comment_content,
						$core->blog->name,$core->blog->url,$rs->email,
						subscriber::pageLink($rs->email,$rs->user_key),
						$post['title'],$post['url'],$post['url'].'#c'.$comment_id,
						$cur->comment_author,html::clean($cur->comment_content));
					self::mail($rs->email,$subject,$content);
				}
			}
		}
	}

	/**
	send an email
	@param	to <b>string</b> Email recipient
	@param	subject <b>string</b> Email subject
	@param	content <b>string</b> Email content
	*/
	public static function mail($to,$subject,$content)
	{
		$headers = array(
			'MIME-Version: 1.0',
			'From: dotclear@'.$_SERVER['HTTP_HOST'],
			'Content-Type: text/plain; charset=UTF-8;',
			'X-Mailer: Dotclear'
		);

		# from /dotclear/admin/auth.php : mb_encode_mimeheader($subject,'UTF-8','B')

		mail::sendMail($to,mb_encode_mimeheader($subject,'UTF-8','B'),
			wordwrap($content,70),$headers);
	}

	/**
	redirect to an URL with a message and exit
	@param	get <b>string</b> GET URL
	*/
	public static function redirect($get='')
	{
		global $core;

		$separator = '?';
		if ($core->blog->settings->url_scan == 'query_string') {$separator = '&';}
		if (isset($get)) {$get = $separator.'message='.$get;}
		http::redirect(subscribeToComments::url().$get);
		exit();
	}
}

?>