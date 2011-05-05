<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of specificsTemplates, a plugin for Dotclear.
# 
# Copyright (c) 2009 Thierry Poinot
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------
if (!defined('DC_RC_PATH')) { return; }	
l10n::set(dirname(__FILE__).'/locales/'.$_lang.'/main');

//*/

// réécriture du gestionnaire d'url de catégories et de pages de Dotclear pour aller chercher le fichier category-##.html comme sous spip
// via http://aiguebrun.adjaya.info/post/20080707/Template-personnalise-par-categorie (mot de passe : pep)
// via http://forum.dotclear.net/viewtopic.php?id=34414


class specificsTemplatesURLHandlers extends dcUrlHandlers // /inc/public/lib.urlhandlers.php
{
	public static function category($args) // /inc/public/lib.urlhandlers.php
	{
		$_ctx =& $GLOBALS['_ctx'];
		$core =& $GLOBALS['core'];
		
		$n = self::getPageNumber($args);
		
		if ($args == '' && !$n) {
			self::p404();
		}
		
		$params['cat_url'] = $args;
		$params['post_type'] = 'post';
		
		$_ctx->categories = $core->blog->getCategories($params);
		
		if ($_ctx->categories->isEmpty()) {
			self::p404();
		} else {
			if ($n) {
				$GLOBALS['_page_number'] = $n;
			}

			$tpl = 'category-'.$_ctx->categories->cat_url.'.html'; //category-##.html où ## est l'url de la catégorie
			if (!$core->tpl->getFilePath($tpl)) {
				$tpl = 'category-'.$_ctx->categories->cat_id.'.html'; //category-##.html où ## est l'id de la catégorie
				if (!$core->tpl->getFilePath($tpl)) {
					$tpl = 'category.html';
				}
			}
			self::serveDocument($tpl);
			exit;
		}
	}
	public static function pages($args) // /plugins/pages/_public.php
	{
		if ($args == '') {
			self::p404();
		}
		
		$_ctx =& $GLOBALS['_ctx'];
		$core =& $GLOBALS['core'];
		
		$core->blog->withoutPassword(false);
		
		$params = new ArrayObject();
		$params['post_type'] = 'page';
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
		
		$post_id = $_ctx->posts->post_id;
		$post_url = $_ctx->posts->post_url;
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
		$core->tpl->setPath($core->tpl->getPath(), dirname(__FILE__).'/default-templates');
		
		$tpl = 'page-'.$post_url.'.html'; //page-##.html où ## est l'url de la page
		if (!$core->tpl->getFilePath($tpl)) {
			$tpl = 'page-'.$post_id.'.html'; //page-##.html où ## est l'id de la page
			if (!$core->tpl->getFilePath($tpl)) {
				$tpl = 'page.html';
			}
		}
		
		self::serveDocument($tpl);
		exit;
	}
}
?>
