<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of agora, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2010 Osku, Tomtom and contributors
#
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------

class agoraBehaviors
{
	public static function coreBlogGetPosts($rs)
	{
		$rs->extend('rsExtThread');
	}
	
	public static function agoraGetMessages($rs)
	{
		$rs->extend('rsExtMessagePublic');
	}

	public static function coreInitWikiPost($wiki2xhtml)
	{
		global $core;
		
		$wiki2xhtml->setOpts(array(
			'active_title' => 0,
			'active_auto_br' => 0,
			'active_auto_urls' => 1,
			'active_urls' => 1,
			'active_auto_img' => 0,
			'active_img' => 0,
			'active_footnotes' => 0
		));
		return;
	}

	public static function autoLogIn()
	{
		global $core, $_ctx;

		$core->session = new sessionDB(
			$core->con,
			$core->prefix.'session',
			'dc_agora_sess_'.$core->blog->id,
			''
		);

		if (isset($_COOKIE['dc_agora_sess_'.$core->blog->id]))
		{
			# If we have a session we launch it now
			if (!$core->auth->checkSession())
			{
				# Avoid loop caused by old cookie
				$p = $core->session->getCookieParameters(false,-600);
				$p[3] = '/';
				call_user_func_array('setcookie',$p);
			}
		}

		if (!isset($_SESSION['sess_user_id']))
		{
			if (isset($_COOKIE['dc_agora_'.$core->blog->id])
			&& strlen($_COOKIE['dc_agora_'.$core->blog->id]) == 104)
			{
				# If we have a remember cookie, go through auth process with key
				$login = substr($_COOKIE['dc_agora_'.$core->blog->id],40);
				$login = @unpack('a32',@pack('H*',$login));
				if (is_array($login))
				{
					$login = $login[1];
					$key = substr($_COOKIE['dc_agora_'.$core->blog->id],0,40);
					$passwd = null;
				}
				else
				{
					$login = null;
				}
				
				$_ctx->agora->userlogIn($login,$passwd,$key);
			}
		}

		return;
	}

	public static function cleanSession()
	{
		global $core;

		$strReq = 'DELETE FROM '.$core->prefix.'session '.
				"WHERE ses_time < ".(time() - 3600*24*14);

		$core->con->execute($strReq);
	}
	
	public static function adminPostsActions($core,$posts,$action,$redir)
	{
		if ($action == 'messagescount')
		{
			try
			{		
				while ($posts->fetch())
				{
					$core->blog->agora->countMessages($posts->post_id);
				}
				
				http::redirect($redir);
			}
			catch (Exception $e)
			{
				$core->error->add($e->getMessage());
			}
		}
	}
}
?>