<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of whiteListCom, a plugin for Dotclear 2.
#
# Copyright (c) 2009 JC Denis and contributors
# jcdenis@gdwd.com
#
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')){return;}

class whiteListComBehaviors
{
	public static function publicBeforeCommentPreview($preview)
	{
		global $core, $_ctx;

		$author = $preview['name'];
		$mail = $preview['mail'];

		# Moderate active
		if (!$core->blog->settings->whiteListCom_reserved) return;

		# Get white list
		$white_list = self::decode($core->blog->settings->whiteListCom_list);
		if (empty($white_list)) return;

		# Check if name is reserved
		$list = array_flip($white_list);
		if (isset($list[$author]) && $list[$author] != $email)
		{
			$_ctx->form_error = __('This name is reserved to another people, choose another one.');
			$_ctx->form_error;
		}
	}

	public static function publicBeforeCommentCreate($cur)
	{
		global $core;

		$is_white = false;
		$author = $cur->comment_author;
		$mail = $cur->comment_email;

		# Get white list
		$white_list = self::decode($core->blog->settings->whiteListCom_list);
		if (empty($white_list)) return;

		# Check if name is reserved by another email
		$list = array_flip($white_list);
		if ($core->blog->settings->whiteListCom_reserved && 
			isset($list[$author]) && $list[$author] != $email)
		{
			throw new Exception(
				__('This name is reserved to another people, choose another one.')
			);
		}

		# Check if mame/email is in white list
		if ($core->blog->settings->whiteListCom_pub 
		&& !$core->blog->settings->comments_pub)
		{
			# Set comment status
			$cur->comment_status = 
			isset($white_list[$email]) && $white_list[$email] == $author ? 1 : -1;
		}
	}

	public static function adminBlogPreferencesForm($core,$blog_settings)
	{
		$list = self::arr2str(self::decode($blog_settings->whiteListCom_list));

		echo
		'<fieldset><legend>'.__('Comment moderation').'</legend>'.
		'<p>'.__('If you moderate comments, you can use a white list of people whose comments will be published directly.').'</p>'.

		'<p><label class="classic">'.
		form::checkbox('whiteListCom_pub','1',$blog_settings->whiteListCom_pub).
		__('Use the publication of comments of the whitelist').'</label></p>'.
		'<p class="form-note">'.__('You must enable moderation to use this.').'</p>'.

		'<p><label class="classic">'.
		form::checkbox('whiteListCom_reserved','1',$blog_settings->whiteListCom_reserved).
		__('Use the block of reserved names whitelist').'</label></p>'.
		'<p class="form-note">'.__('This works even if moderation is not enabled.').'</p>'.

		'<div class="col">'.
		'<p><label>'.__('White list:').
		form::field('whiteListCom_list',120,255,html::escapeHTML($list)).
		'</label></p>'.
		'<p class="form-note">'.
		__('Write white list like email1;name1|email2;name2|email3...').
		'</p>'.

		'</fieldset>';
	}

	public static function adminBeforeBlogSettingsUpdate($blog_settings)
	{
		$list = self::encode(self::str2arr($_POST['whiteListCom_list']));

		$blog_settings->setNameSpace('whiteListCom');

		$blog_settings->put('whiteListCom_pub',!empty($_POST['whiteListCom_pub']));
		$blog_settings->put('whiteListCom_reserved',!empty($_POST['whiteListCom_reserved']));
		$blog_settings->put('whiteListCom_list',$list);

		$blog_settings->setNameSpace('system');
	}

	public static function encode($x)
	{
		$s = is_array($x) ? $x : array();
		return base64_encode(serialize($s));
	}

	public static function decode($x)
	{
		$s = unserialize(base64_decode($x));
		return is_array($s) ? $s : array();
	}

	public static function str2arr($x)
	{
		if (!is_string($x)) return array();

		$x = explode('|',$x);

		$s = array();
		foreach($x as $user)
		{
			$user = explode(';',$user);
			if (empty($user[1]) || !text::isEmail($user[0])) continue;

			$s[$user[0]] = $user[1];
		}
		return $s;
	}

	public static function arr2str($x)
	{
		if (!is_array($x)) return '';

		$s = '';
		foreach($x as $email => $author)
		{
			if (empty($author) || !text::isEmail($email)) continue;

			$s .= $email.';'.$author.'|';
		}
		return $s;
	}
}
?>