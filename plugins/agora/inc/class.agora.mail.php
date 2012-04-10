<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of agora, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2011 Osku ,Tomtom and contributors
#
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------
if (!defined('DC_RC_PATH')) { return; }

class mailAgora
{
	public static function sendActivationEmail($user)
	{
		global $core;
		$key = $core->auth->setRecoverKey($user['login'],$user['email']);
		$link = $core->blog->url.$core->url->getURLFor('register');
		$link .= $core->blog->settings->system->url_scan == 'query_string' ? '&' : '?';
		$url_login = $core->blog->url.$core->url->getURLFor('login');
		$sub = __('Account confirmation request');
		$msg = 
		sprintf(__('Welcome to %s'),$core->blog->name)."\n\n".
		__('To verify your e-mail address, please click on the following link:').
		"\n\n".
		$link.'key='.$key.
		"\n\n".
		__('Your informations:')."\n".
		"\t".sprintf(__('User: %s'),$user['login'])."\n".
		"\t".sprintf(__('Password: %s'),$user['pwd'])."\n\n".
		sprintf(__('Connection URL: %s'),$url_login)."\n";

		self::sendEmail($user['email'],$sub,$msg);
	}
	
	public static function sendWelcomeEmail($user)
	{
		global $core;
		$sub = __('Account validation');
		$url_login = $core->blog->url.$core->url->getURLFor('login');
		$msg = 
		sprintf(__('Hello %s'),$user->user_id)."\n\n".
		sprintf(__('Your account has been validated by the administrators. You can participate to %s.'),$core->blog->name).
		"\n\n".
		__('Reminder:')."\n".
		"\t".sprintf(__('Connection URL: %s'),$url_login)."\n";

		self::sendEmail($user->user_email,$sub,$msg);
	}

	public static function sendRecoveryEmail($user)
	{
		global $core;
		$sub =__('Password reset');
		$recover_url = $core->blog->url.$core->url->getBase('recover').'/';
		$msg =
		__('Someone has requested to reset the password for the following website and username.')."\n\n".
		$core->blog->url."\n".__('Username:').' '.$user['login']."\n\n".
		__('To reset your password visit the following address, otherwise just ignore this email and nothing will happen.')."\n".
		$recover_url.$user['key'];
		self::sendEmail($user['email'],$sub,$msg);
	}
	
	public static function sendNewPasswordEmail($user)
	{//($recover_res['user_email'],$recover_res['user_id'],$recover_res['new_pass']);
		$sub = __('Your new password');
		$msg =
		__('Username:').' '.$user['user_id']."\n".
		__('Password:').' '.$user['new_pass']."\n\n";

		self::sendEmail($user['user_email'],$sub,$msg);
	}
	
	protected static function sendEmail($dest,$sub,$msg)
	{
		global $core;
		
		$headers = array(
		'From: '.mail::B64Header($core->blog->name).
		'<no-reply@'.str_replace('http://','',http::getHost()).' >',
		'Content-Type: text/plain; charset=UTF-8;'.
		'X-Originating-IP: '.http::realIP(),
		'X-Mailer: Dotclear',
		'X-Blog-Id: '.mail::B64Header($core->blog->id),
		'X-Blog-Name: '.mail::B64Header($core->blog->name),
		'X-Blog-Url: '.mail::B64Header($core->blog->url)
		);

		$msg .= 
		"\n\n".
		"--------------------\n".
		__('This is a post-only mailing. Replies to this message are not monitored or answered.').
		"\n\n";   
		
		$sub = '['.$core->blog->name.'] '.$sub;
		$sub = mail::B64Header($sub);
		mail::sendMail($dest,$sub,$msg,$headers);
	}
}
?>
