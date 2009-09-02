<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of splitPost, a plugin for Dotclear.
# 
# Copyright (c) 2009 Tomtom
# http://blog.zenstyle.fr/
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')) { return; }

$core->tpl->addValue('PostPagination',array('splitPostTpl','PostPagination'));

$core->url->register('post','post','^post/(.+)$',array('splitPostUrl','post'));

$core->addBehavior('coreBlogGetPosts',array('splitPostBehaviors','coreBlogGetPosts'));

class splitPostUrl extends dcUrlHandlers
{
	public static function post($args)
	{
		if ($args == '') {
			self::p404();
		}
		
		$_ctx = $GLOBALS['_ctx'];
		$core = $GLOBALS['core'];
		
		$core->blog->withoutPassword(false);
		
		$args = preg_split('#/page/#',$args);
		
		$params = new ArrayObject();
		$params['post_url'] = $args[0];
		
		$_ctx->posts = $core->blog->getPosts($params);
		
		# Post pages
		$_ctx->post_page_count = count(preg_split($core->post_page_pattern,$_ctx->posts->post_content_xhtml));
		$_ctx->post_page_current = isset($args[1]) ? (int) $args[1] : null;
		
		if (
			$_ctx->post_page_current === 0 ||
			$_ctx->post_page_current > $_ctx->post_page_count
		) {
			self::p404();
			exit;
		}
		
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
			exit;
		}
		
		$post_id = $_ctx->posts->post_id;
		$post_password = $_ctx->posts->post_password;
		
		# Password protected entry
		if ($post_password != '')
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
				exit;
			}
		}
		
		$post_comment =
			isset($_POST['c_name']) && isset($_POST['c_mail']) &&
			isset($_POST['c_site']) && isset($_POST['c_content']) &&
			$_ctx->posts->commentsActive();
		
		# Posting a comment
		if ($post_comment)
		{
			# Spam trap
			if (!empty($_POST['f_mail'])) {
				http::head(412,'Precondition Failed');
				header('Content-Type: text/plain');
				echo "So Long, and Thanks For All the Fish";
				exit;
 			}
			
			$name = $_POST['c_name'];
			$mail = $_POST['c_mail'];
			$site = $_POST['c_site'];
			$content = $_POST['c_content'];
			$preview = !empty($_POST['preview']);
			
			if ($content != '')
			{
				if ($core->blog->settings->wiki_comments) {
					$core->initWikiComment();
				} else {
					$core->initWikiSimpleComment();
				}
				$content = $core->wikiTransform($content);
				$content = $core->HTMLfilter($content);
			}
			
			$_ctx->comment_preview['content'] = $content;
			$_ctx->comment_preview['rawcontent'] = $_POST['c_content'];
			$_ctx->comment_preview['name'] = $name;
			$_ctx->comment_preview['mail'] = $mail;
			$_ctx->comment_preview['site'] = $site;
			
			if ($preview)
			{
				# --BEHAVIOR-- publicBeforeCommentPreview
				$core->callBehavior('publicBeforeCommentPreview',$_ctx->comment_preview);
				
				$_ctx->comment_preview['preview'] = true;
			}
			else
			{
				# Post the comment
				$cur = $core->con->openCursor($core->prefix.'comment');
				$cur->comment_author = $name;
				$cur->comment_site = html::clean($site);
				$cur->comment_email = html::clean($mail);
				$cur->comment_content = $content;
				$cur->post_id = $_ctx->posts->post_id;
				$cur->comment_status = $core->blog->settings->comments_pub ? 1 : -1;
				$cur->comment_ip = http::realIP();
				
				$redir = $_ctx->posts->getURL();
				$redir .= strpos($redir,'?') !== false ? '&' : '?';
				
				try
				{
					if (!text::isEmail($cur->comment_email)) {
						throw new Exception(__('You must provide a valid email address.'));
					}

					# --BEHAVIOR-- publicBeforeCommentCreate
					$core->callBehavior('publicBeforeCommentCreate',$cur);
					if ($cur->post_id) {					
						$comment_id = $core->blog->addComment($cur);
					
						# --BEHAVIOR-- publicAfterCommentCreate
						$core->callBehavior('publicAfterCommentCreate',$cur,$comment_id);
					}
					
					if ($cur->comment_status == 1) {
						$redir_arg = 'pub=1';
					} else {
						$redir_arg = 'pub=0';
					}
					
					header('Location: '.$redir.$redir_arg);
					exit;
				}
				catch (Exception $e)
				{
					$_ctx->form_error = $e->getMessage();
					$_ctx->form_error;
				}
			}
		}
		
		# The entry
		self::serveDocument('post.html');
	}
}

class splitPostTpl
{
	public static function PostPagination($attr)
	{
		$res = "<?php\n";
		$res .= "\$pager = new splitPostPager(\$_ctx->post_page_current,\$_ctx->post_page_count,20);\n";
		$res .= "\$pager->setBaseUrl();\n";
		$res .= "echo \$pager->getLinks();\n";
		$res .= "?>\n";
		
		return $res;
	}
}

?>