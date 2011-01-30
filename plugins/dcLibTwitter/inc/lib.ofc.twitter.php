<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of dcLibTwitter, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2011 JC Denis and contributors
# jcdenis@gdwd.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')){return;}

# This class play with plugin optionsForComment to add Twitter login button
class ofcTwitter extends optionsForComment
{
	public static function optionsForCommentAdminPrepend($core,$action)
	{
		if (!defined('DC_CONTEXT_ADMIN')){return;}
		
		if ($action == 'savesettings')
		{
			$core->blog->settings->dcLibTwitter->put('optionsForComment_enable',isset($_POST['optionsForComment_enable']));
		}
	}
	
	public static function optionsForCommentAdminHeader($core)
	{
		echo '
		<style type="text/css">
		p.success { background: transparent url(images/check-on.png) no-repeat left center; padding-left: 18px; }
		p.failed { background: transparent url(images/check-off.png) no-repeat left center; padding-left: 18px; }
		</style>';
	}
	
	public static function optionsForCommentAdminForm($core)
	{
		if (!defined('DC_CONTEXT_ADMIN')){return;}
		
		$p = (integer) $core->blog->settings->dcLibTwitter->optionsForComment_enable;
		
		echo '<fieldset><legend>'.__('Twitter login').'</legend>';
		
		# Hey install oAuthManager
		if (!$core->plugins->moduleExists('oAuthManager'))
		{
			echo 
			'<p class="failed">'.
			__('To use this option you must install plugin called "oAutManager".').
			'</p>';
		}
		else
		{
			echo '
			<p><label class="classic">'.
			form::checkbox(array('optionsForComment_enable'),'1',$p).
			__('Enable Twitter login on comments').'</label></p>';
		}
		echo '</fieldset>';
	}
	
	
	public static function optionsForCommentPublicPrepend($core,$rs)
	{
		if (!$core->blog->settings->dcLibTwitter->optionsForComment_enable 
		 || $rs['c_content'] === null 
		 || $rs['preview']) {
			return;
		}
		
		global $_ctx;
		
		if (!$_ctx->exists('ofcTwitter_has_registry')) {
			if (!libOfcTwitter::loadContext($core,$_ctx)) {
				return;
			}
		}
		
		if (!$_ctx->ofcTwitter_oauth) {
			return;
		}
		
		$rs['c_name'] = $_ctx->ofcTwitter_user;
		$rs['c_mail'] = 'dcLibTwitter@optionsForComment';
		$rs['c_site'] = 'http://twitter.com/'.$_ctx->ofcTwitter_user;
	}
	
	public static function optionsForCommentPublicCreate($cur,$preview)
	{
		if ($GLOBALS['core']->blog->settings->dcLibTwitter->optionsForComment_enable 
		 && $cur->comment_email == 'dcLibTwitter@optionsForComment')
		{
			# set tpl fields
			$preview['name'] = '';
			$preview['mail'] = '';
			$preview['site'] = '';
			
			# set db fields
			$cur->comment_author= $_POST['c_name'];
			$cur->comment_email = $_POST['c_name'].'@twitter';
			$cur->comment_site = $_POST['c_site'];
		}
	}
	
	public static function optionsForCommentPublicHead($core,$_ctx,$js_vars)
	{
		if (!$core->blog->settings->dcLibTwitter->optionsForComment_enable) {
			return;
		}
		
		if (!$_ctx->exists('ofcTwitter_oauth')) {
			if (!libOfcTwitter::loadContext($core,$_ctx)) {
				return;
			}
		}
		
		$js_vars['ofcTwitter_access'] = (integer) $_ctx->ofcTwitter_access;
		
		echo self::jsLoad($core->blog->getQmarkURL().'pf=dcLibTwitter/js/ofc.twitter.js');
	}
	
	public static function optionsForCommentPublicForm($core,$_ctx)
	{
		if (!$core->blog->settings->dcLibTwitter->optionsForComment_enable) { 
			return;
		}
		
		if (!$_ctx->exists('ofcTwitter_oauth')) {
			if (libOfcTwitter::loadContext($core,$_ctx)) {
				return ;
			}
		}
		
		if (!$_ctx->ofcTwitter_oauth) {
			return;
		}
		
		$redir = urlencode($_ctx->posts->getURL());
		
		if (!$_ctx->exists('ofcTwitter_user')) {
			echo 
			'<p class="ofc-twitterlogin">'.
			'<a href="'.$core->blog->getQmarkURL().$core->url->getBase('ofcTwitter').'&amp;do=login&amp;redir='.$redir.'" title="Sign in with Twitter">'.
			'<img src="'.$core->blog->getQmarkURL().'pf=dcLibTwitter/inc/img/twitter_connect_a.png" alt="Sign in with Twitter" />'.
			'</a></p>';
		}
		else {
			echo 
			'<p class="field"><label>'.
			'<img class="ofc-twitterimage" src="http://img.tweetimag.es/i/'.$_ctx->ofcTwitter_user.'_m" alt="Twitter avatar" />'.
			'</label>'.sprintf(__('Logged in as %s'),$_ctx->ofcTwitter_user).'<br />'.
			'<a class="ofc-twitterlogout" href="'.$core->blog->getQmarkURL().$core->url->getBase('ofcTwitter').'&amp;do=logout&amp;redir='.$redir.'">'.
			__('Disconnect').'</a></p>';
		}
	}
}

class libOfcTwitter
{
	# Use plugin oAuthManager
	private static function oAuth($core,$user_id='')
	{
		# Required plugin oAuthManager
		# Used name of parent plugin
		if (!$core->plugins->moduleExists('oAuthManager')) return false;
		
		return oAuthClient::load($core,'twitter',
			array(
				'user_id' => $user_id,
				'plugin_id' => 'optionsForComment',
				'plugin_name' => __('Option for comment'),
				'token' => 'tqzapL37XIJd5G7TMdtqHQ',
				'secret' => 'ZVeUg1La2ZwxjJpQyp1L6tL1wqyFfJ3MYBpbaf1eEk',
				'expiry' => 1296000 // 2 weeks
			)
		);
	}
	
	# Commons for public side
	public static function loadContext($core,$_ctx)
	{
		if ('' == session_id()) { session_start(); }
		
		# Get a user id
		$user = self::getUser($core->blog->id);
		
		# Set a user id
		self::setUser($core->blog->id,$user);
		
		# Launch oAuth
		$_ctx->ofcTwitter_oauth = self::oAuth($core,$user);
		
		$access = false;
		if ($_ctx->ofcTwitter_oauth)
		{
			# Check user access
			if ($_ctx->ofcTwitter_oauth->state() == 2)
			{
				$user_info = $_ctx->ofcTwitter_oauth->getScreenName();
				if ($user_info)
				{
					$_ctx->ofcTwitter_user = $user_info;
					$access = true;
				}
			}
			$_ctx->ofcTwitter_access  = $access;
			return true;
		}
		
		# Put plugin info into context
		$_ctx->ofcTwitter_access = false;
		
		return false;
	}
	
	public static function getUser($b)
	{
		$k = 'dc_ofctwitter_'.$b;
		$v = '';
		
		if (!empty($_SESSION[$k])) {
			$v = $_SESSION[$k];
		}
		elseif (!empty($_COOKIE[$k])) {
			$v = $_COOKIE[$k];
		}
		if (strlen($v) != 32) {
			$v = md5(uniqid());
		}
		return $v;
	}
	
	public static function setUser($b,$v)
	{
		$k = 'dc_ofctwitter_'.$b;
		
		$_SESSION[$k] = $v;
		setcookie($k,$v,time() + 2592000,'/');
	}
	
	public static function delUser($b)
	{
		$k = 'dc_ofctwitter_'.$b;
		
		$_SESSION[$k] = '';
		setcookie($k,'',time() -3600,'/');
	}
	
	# Special plugin noodles
	public static function noodlesNoodleImageInfo($core,$rs)
	{
		if (preg_match('#^(.+)@twitter$#',$rs['mail'],$m)) {
		
			$size = (integer) $rs['size'];
			if ($size < 48) {
				$s = 'm';
			}
			elseif ($size > 48) {
				$s = 'b';
			}
			else {
				$s = 'n';
			}
			// use a third part service for twitter avatar...
			$rs['url'] = 'http://img.tweetimag.es/i/'.$m[1].'_'.$s;
		}
	}
}

class urlOfcTwitter extends dcUrlHandlers
{
	public static function login($args)
	{
		global $core,$_ctx;
		$cookie_name = 'dc_ofctwitter_'.$core->blog->id;
		
		# Check settings
		if (!$core->blog->settings->optionsForComment->active) {
			throw new Exception ("Not found",404);
		}
		
		# Check url
		if (empty($_GET['redir']) 
		 || empty($_GET['do'])
		 || !in_array($_GET['do'],array('login','logout','grant'))) {
			throw new Exception ("Method Not Allowed",405);
		}
		
		# Load plugin context
		if (!libOfcTwitter::loadContext($core,$_ctx)) {
			throw new Exception ("Internal Server Error",500); //!
		}
		
		# Check plugin
		if (!$_ctx->ofcTwitter_oauth) {
			throw new Exception ("Not Implemented",501); //!
		}
		
		# Clean url
		$redir = urldecode($_GET['redir']);
		
		# Use plugin oAuthManager
		if ($_ctx->ofcTwitter_oauth)
		{
			# Login (request access)
			if ($_GET['do'] == 'login') {
				try {
					$_ctx->ofcTwitter_oauth->getRequestToken(
						$core->blog->getQmarkURL().
						$core->url->getbase('ofcTwitter').
						'&do=grant&redir='.$_GET['redir']
					);
				}
				catch(Exception $e) {
					throw new Exception ('Unauthorized ('.$e->getMessage().')',401);
				}
			}
			# Grant access (redirect from twitter)
			elseif ($_GET['do'] == 'grant') {
				try {
					if ($_ctx->ofcTwitter_oauth->state() == 1) {
						$_ctx->ofcTwitter_oauth->getAccessToken();
					}
					else {
						$_ctx->ofcTwitter_oauth->removeToken();
						libOfcTwitter::delUser($core->blog->id);
					}
					http::redirect($redir);
				}
				catch(Exception $e) {
					throw new Exception ('Unauthorized ('.$e->getMessage().')',401);
				}
			}
			# Logout
			elseif ($_GET['do'] == 'logout') {
				try {
					$_ctx->ofcTwitter_oauth->deleteRecord();
					libOfcTwitter::delUser($core->blog->id);
					
					http::redirect($redir);
				}
				catch(Exception $e) {
					throw new Exception ("Internal Server Error",500);
				}
			}
		}
	}
}
?>