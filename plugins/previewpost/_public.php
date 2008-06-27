<?php
# ***** BEGIN LICENSE BLOCK *****
# This file is part of DotClear Preview plugin.
# Copyright (c) 2008 Bruno Hondelatte,  and contributors. 
# Many, many thanks to Olivier Meunier and the Dotclear Team.
# All rights reserved.
#
# Preview plugin for DC2 is free sofwtare; you can redistribute it and/or modify
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
if (!defined('DC_RC_PATH')) { return; }

require dirname(__FILE__).'/_widgets.php';
$core->addBehavior('publicPrepend',array('PreviewBehavior','publicPrepend'));

$core->tpl->addValue('EntryIfOffline',array('tplPreview','EntryIfOffline'));

class urlPreview extends dcUrlHandlers
{
	public static function post($args)
	{
		if ($args == '') {
			self::p404();
		}
		
		$_ctx =& $GLOBALS['_ctx'];
		$core =& $GLOBALS['core'];
		
		if (isset($_COOKIE[DC_SESSION_NAME]))
		{
			$core->session->start();
			$core->auth->checkUser($_SESSION['sess_user_id']);
		}

		$core->blog->withoutPassword(false);
		
		$params = new ArrayObject();
		$params['post_url'] = $args;
		
		$_ctx->posts = $core->blog->getPosts($params);
		
		$_ctx->comment_preview = new ArrayObject();
		$_ctx->comment_preview['content'] = '';
		$_ctx->comment_preview['rawcontent'] = '';
		$_ctx->comment_preview['name'] = '';
		$_ctx->comment_preview['mail'] = '';
		$_ctx->comment_preview['site'] = '';
		$_ctx->comment_preview['preview'] = false;
		$_ctx->comment_preview['remember'] = false;
		
		$core->blog->withoutPassword(true);
		
		
		if ($_ctx->posts->isEmpty())
		{
			# No entry
			self::p404();
		}
		
		# The entry
		self::serveDocument('post.html');
		exit;
	}

	public static function login($args)
	{
		global $core;
		if (!isset($_POST['user_id']) || !isset($_POST['user_pwd'])) {
			header('Location: '.$core->blog->url);
			return;
		}
		$user_id=$_POST['user_id'];
		$user_pwd = $_POST['user_pwd'];
		if ($core->auth->checkUser($user_id,$user_pwd) === true) {
			$core->session->start();
			$_SESSION['sess_user_id'] = $user_id;
			$_SESSION['sess_browser_uid'] = http::browserUID(DC_MASTER_KEY);
			
			if (!empty($_POST['blog'])) {
				$_SESSION['sess_blog_id'] = $_POST['blog'];
			}
			if (!empty($_POST['user_remember']))
			{
				$cookie_admin =
					http::browserUID(DC_MASTER_KEY.$user_id.crypt::hmac(DC_MASTER_KEY,$user_pwd)).
					bin2hex(pack('a32',$user_id));
					
				setcookie('dc_admin',$cookie_admin,strtotime('+15 days'),'','',DC_ADMIN_SSL);
			}
			header('Location: '.$core->blog->url);
			return;
		};

	}
}


class PreviewBehavior {
	public static function publicPrepend(&$core) {
		if (isset($_COOKIE[DC_SESSION_NAME]))
		{
			$core->session->start();
			$core->auth->checkUser($_SESSION['sess_user_id']);
		}
		
	}
}

class tplPreview {
	public static function EntryIfOffline($attr) {
		$ret = isset($attr['return']) ? $attr['return'] : 'offline';
		$ret = html::escapeHTML($ret);
		
		return '<?php if ($_ctx->posts->post_status != 1) { '. "echo '".addslashes($ret)."'; } ?".">";
	}

	public static function authWidget(&$w) {
		global $core;
                $title = $w->title ? html::escapeHTML($w->title) : __('Connection');
		$is_authenticated=isset($_COOKIE[DC_SESSION_NAME]);
			
		$res = '<div id="auth">'.
			'<h2>'.$title.'</h2>';

		if (!$is_authenticated) {
			$res .= '<form action="'.$core->blog->url.'login" method="post">'.
				'<p><label>'.__('Login').' '.
				form::field("user_id",20,32).'</label></p>'.
				'<p><label>'.__('Password').' '.
				form::password("user_pwd",20,255).'</label></p>'.
				'<p><input type="submit" value="'.__('login').'" tabindex="3" />'.
				'</form>';
		} else {
			$res .= "You are authenticated";
		}
		$res .= '</div>';
		return $res;

	}
}
