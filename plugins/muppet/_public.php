<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of muppet, a plugin for Dotclear 2.
# 
# Copyright (c) 2010 Osku and contributors
#
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------
if (!defined('DC_RC_PATH')) { return; }

$core->tpl->setPath($core->tpl->getPath(), dirname(__FILE__).'/default-templates');
$core->addBehavior('templateBeforeBlock',array('behaviorsMuppet','templateBeforeBlock'));
$core->addBehavior('publicBeforeSearchCount',array('behaviorsMuppet','publicBeforeSearchCount'));

class urlMuppet extends dcUrlHandlers
{
	public static function singlepost($args)
	{
		if ($args == '') {
			# No page was specified.
			self::p404();
		}
		else
		{
			$_ctx =& $GLOBALS['_ctx'];
			$core =& $GLOBALS['core'];
			
			$core->blog->withoutPassword(false);
			
			$params = new ArrayObject();
			$params['post_type'] = str_replace('preview', '', $core->url->type);
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
				# The specified page does not exist.
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
						# Exits immediately the application to preserve the server.
						exit;
					}
					
					$name = $_POST['c_name'];
					$mail = $_POST['c_mail'];
					$site = $_POST['c_site'];
					$content = $_POST['c_content'];
					$preview = !empty($_POST['preview']);
					
					if ($content != '')
					{
						if ($core->blog->settings->system->wiki_comments) {
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
						$cur->comment_status = $core->blog->settings->system->comments_pub ? 1 : -1;
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
						}
						catch (Exception $e)
						{
							$_ctx->form_error = $e->getMessage();
							$_ctx->form_error;
						}
					}
				}
				
				$mytpl = $params['post_type'];
				
				# The entry
				$tpl = 'single-'.$mytpl.'.html';
				if (!$core->tpl->getFilePath($tpl)) {
					$tpl = 'post.html';
				}
				self::serveDocument($tpl);
			}
		}
	}
	
	public static function singlepreview($args)
	{
		$core = $GLOBALS['core'];
		$_ctx = $GLOBALS['_ctx'];
		
		if (!preg_match('#^(.+?)/([0-9a-z]{40})/(.+?)$#',$args,$m)) {
			# The specified Preview URL is malformed.
			self::p404();
		}
		else
		{
			$user_id = $m[1];
			$user_key = $m[2];
			$post_url = $m[3];
			if (!$core->auth->checkUser($user_id,null,$user_key)) {
				# The user has no access to the entry.
				self::p404();
			}
			else
			{
				$_ctx->preview = true;
				self::single($post_url);
			}
		}
	}

	public static function listpost($args)
	{	
		$core = $GLOBALS['core'];
		$_ctx = $GLOBALS['_ctx'];
		
          $n = self::getPageNumber($args);
         
          if ($args && !$n)
          {
               # "Then specified URL went unrecognized by all URL handlers and
               # defaults to the home page, but is not a page number.
               self::p404();
          }
          else
          {
               $params['post_type'] = substr($core->url->type, 0, -1);
			if ($n) {
                    $GLOBALS['_page_number'] = $n;
               }
			
			$mytpl = $params['post_type'];
			
			$_ctx->posts = $core->blog->getPosts($params);
				
			# The entry
			$tpl = 'list-'.$mytpl.'.html';
			if (!$core->tpl->getFilePath($tpl)) {
				$tpl = 'muppet-list.html';
			}
			self::serveDocument($tpl);
          }		
	}
}

class widgetsMuppet
{
	public static function bestofWidget($w)
	{
		global $core;
		
		if ($w->homeonly && $core->url->type != 'default') {
			return;
		}
		
		$params = array(
			'post_type' => $w->posttype,
			'post_selected'=>true,
			'no_content'=>true,
			'order'=>'post_dt desc');
		
		$rs = $core->blog->getPosts($params);
		
		if ($rs->isEmpty()) {
			return;
		}
		
		$res =
		'<div class="selected">'.
		($w->title ? '<h2>'.html::escapeHTML($w->title).'</h2>' : '').
		'<ul>';
		
		while ($rs->fetch()) {
			$res .= ' <li><a href="'.$rs->getURL().'">'.html::escapeHTML($rs->post_title).'</a></li> ';
		}
		
		$res .= '</ul></div>';
		
		return $res;
	}

	public static function lastpostsWidget($w)
	{
		global $core;
		
		if ($w->homeonly && $core->url->type != 'default') {
			return;
		}
		
		$params['post_type'] = $w->posttype;
		$params['limit'] = abs((integer) $w->limit);
		$params['order'] = 'post_id desc';
		$params['no_content'] = true;
		
		if ($w->category)
		{
			if ($w->category == 'null') {
				$params['sql'] = ' AND p.cat_id IS NULL ';
			} elseif (is_numeric($w->category)) {
				$params['cat_id'] = (integer) $w->category;
			} else {
				$params['cat_url'] = $w->category;
			}
		}

		if ($w->tag)
		{
			$params['meta_id'] = $w->tag;
			$rs = $core->meta->getPostsByMeta($params);
		}
		else
		{
			$rs = $core->blog->getPosts($params);
		}
		
		if ($rs->isEmpty()) {
			return;
		}
		
		$res =
		'<div class="lastposts">'.
		($w->title ? '<h2>'.html::escapeHTML($w->title).'</h2>' : '').
		'<ul>';
		
		while ($rs->fetch()) {
			$res .= '<li><a href="'.$rs->getURL().'">'.
			html::escapeHTML($rs->post_title).'</a></li>';
		}
		
		$res .= '</ul></div>';
		
		return $res;
	}
}

class behaviorsMuppet
{
	public static function templateBeforeBlock($core,$b,$attr)
	{
		// Url->type : default, default-page, category, archive, tag, feed
		if (($b == 'Entries' || $b == 'Comments' || $b == 'Archives') && !isset($attr['post_type']))
		{
			return
			"<?php\n".
			'if (!isset($params)) $params=array();'."\n".
			'toolsmuppet::typesToInclude($core->url->type,$params);'."\n".
			"?>\n";
		}
	}
	
     public static function publicBeforeSearchCount($s_params) 
	{ 
          global $core;
		$types = muppet::getPostTypes();

		if (!empty($types)) {
			$post_types = array();

			foreach ($types as $k => $v) {
				if ($v['integration'] === true) {
					$post_types[] = $k;
				}
			}

			if (count($post_types) > 0) {
				if (!isset($s_params['post_type'])) {
					$s_params['post_type']=array('post');
				}
				$s_params['post_type'] = array_merge($s_params['post_type'],$post_types);
			}
		}
     }
}
?>