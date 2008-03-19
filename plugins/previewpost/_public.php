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
}

