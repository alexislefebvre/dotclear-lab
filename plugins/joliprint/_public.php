<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of joliprint, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2011 JC Denis and contributors
# jcdenis@gdwd.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')){return;}

# Settings NS
$core->blog->settings->addNamespace('joliprint');

# Widget
require_once dirname(__FILE__).'/_widgets.php';

# behaviors
$core->addBehavior('publicHeadContent',array('joliprintBhv','publicheadContent'));
$core->addBehavior('publicEntryBeforeContent',array('joliprintBhv','publicEntryBeforeContent'));
$core->addBehavior('publicEntryAfterContent',array('joliprintBhv','publicEntryAfterContent'));

# Template
$core->tpl->addValue('joliprintButton',array('joliprintTpl','joliprintButton'));

# Tpl
$core->tpl->setPath($core->tpl->getPath(),dirname(__FILE__).'/default-templates');

class joliprintBhv
{
	public static function publicHeadContent($core)
	{
		if (!$core->blog->settings->joliprint->active){return;}
		
		# CSS
		$btn_css = $core->blog->settings->joliprint->btn_css;
		if (!empty($btn_css)) {
			echo 
			"\n<!-- Style for plugin joliprint --> \n".
			'<style type="text/css">'."\n".
			html::escapeHTML($btn_css)."\n".
			"</style>\n";
		}
/* JS not yet implemented
		# JS
		echo 
		"\n<!-- JS for joliprint --> \n".
		"<script charset='ISO-8859-1' src='http://api.joliprint.com/joliprint/js/joliprint.js' type='text/javascript'></script> \n";
//*/
	}
	
	public static function publicEntryBeforeContent($core,$_ctx)
	{
		self::publicEntryContent($core,$_ctx,true);
	}
	
	public static function publicEntryAfterContent($core,$_ctx)
	{
		self::publicEntryContent($core,$_ctx,false);
	}
	
	private static function publicEntryContent($core,$_ctx,$before)
	{
		# Settings
		$s = $core->blog->settings->joliprint;
		
		# Conditions
		if (!$s->active // plugin is active
		 || !$_ctx->exists('posts') // on post
		 || $s->btn_place != 'both' 
		 && ($before && $s->btn_place != 'before' // before content
		 || !$before && $s->btn_place != 'after')) // after content
		{
			return;
		}
		
		# Pages
		$btn_pages = @unserialize($s->btn_pages);
		if (!is_array($btn_pages) || !in_array($core->url->type,$btn_pages))
		{
			return;
		}
		
		# Params
		$params = array();
		if ($s->btn_cleanpost) {
			$params['url'] = $core->blog->url.$core->url->getBase('joliprint').'/'.$core->getPostPublicURL($_ctx->posts->post_type,html::sanitizeURL($_ctx->posts->post_url));
		}
		else {
			$params['url'] = $_ctx->posts->getURL();
		}
		$params['server'] = (string) $s->btn_server;
		$params['button'] = (string) $s->btn_button;
		$params['text'] = (string) $s->btn_text;
		
		# Display
		echo  
		'<div class="postjoliprint">'.
		joliprint::toHTML($params).
		'</div>';
	}
}

class joliprintUrl extends dcUrlHandlers
{
	public static function joliprint($args)
	{
		if ($args == '') {
			# No entry was specified.
			self::p404();
		}
		elseif (!preg_match('#^([^/]+)/(.*)$#',$args,$m))
		{
			self::p404();
		}
		else
		{
			$_ctx =& $GLOBALS['_ctx'];
			$core =& $GLOBALS['core'];
			
			$core->blog->withoutPassword(false);
			
			$params = new ArrayObject();
			$params['post_type'] = $m[1];
			$params['post_url'] = $m[2];
			
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
				# The specified entry does not exist.
				self::p404();
			}
			else
			{
				$post_id = $_ctx->posts->post_id;
				$post_password = $_ctx->posts->post_password;
				
				# Password protected entry
				if ($post_password != '' && !$_ctx->preview)
				{
					# Get passwords cookie
					if (isset($_COOKIE['dc_passwd'])) {
						$pwd_cookie = unserialize($_COOKIE['dc_passwd']);
					} else {
						$pwd_cookie = array();
					}
					
					# Check for match
					if ((!empty($_POST['password']) && $_POST['password'] == $post_password)
					|| (isset($pwd_cookie[$post_id]) && $pwd_cookie[$post_id] == $post_password))
					{
						$pwd_cookie[$post_id] = $post_password;
						setcookie('dc_passwd',serialize($pwd_cookie),0,'/');
					}
					else
					{
						self::serveDocument('password-form.html','text/html',false);
						return;
					}
				}
				
				# The entry
				self::serveDocument('joliprint.html');
			}
		}
	}
}

class joliprintTpl
{
	public static function joliprintButton($attr)
	{
		global $core, $_ctx;

		if (!$core->blog->settings->joliprint->active) return;
		
		$res = '';
		
		# URL
		if (isset($attr['url'])) {
			$res .= "\$joliprint_params['url'] = \"".html::escapeHTML($attr['url'])."\"; \n";
		}
		elseif ($_ctx->exists('posts')) {
			$res .= "\$joliprint_params['url'] = \$core->blog->settings->joliprint->btn_cleanpost ? \n";
			$res .= " \$core->blog->url.\$core->url->getBase('joliprint').'/'.\$core->getPostPublicURL(\$_ctx->posts->post_type,html::sanitizeURL(\$_ctx->posts->post_url)) : \n";
			$res .= " \$_ctx->posts->getURL(); } \n";
		}
		else {
			return;
		}
		# Server
		if (isset($attr['server'])) {
			$res .= "\$joliprint_params['server'] = \"".html::escapeHTML($attr['server'])."\"; \n";
		}
		# Picture
		if (isset($attr['button'])) {
			$res .= "\$joliprint_params['button'] = \"".html::escapeHTML($attr['button'])."\"; \n";
		}
		# Text
		if (isset($attr['text'])) {
			$res .= "\$joliprint_params['text'] = \"".html::escapeHTML($attr['text'])."\"; \n";
		}
		
		return 
		"<?php \n".
		"\$joliprint_params = array(); \n".
		$res.
		"echo joliprint::toHTML(\$joliprint_params); \n".
		"unset(\$joliprint_params); \n".
		"?>\n";
	}
}
?>