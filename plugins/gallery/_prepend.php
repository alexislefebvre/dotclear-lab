<?php
# ***** BEGIN LICENSE BLOCK *****
# This file is part of DotClear Gallery plugin.
# Copyright (c) 2007 Bruno Hondelatte,  and contributors. 
# Many, many thanks to Olivier Meunier and the Dotclear Team.
# All rights reserved.
#
# DotClear is free software; you can redistribute it and/or modify
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
global $__autoload, $core;

if (!$core->plugins->moduleExists('metadata')) return false;

require (dirname(__FILE__).'/class.dc.rs.gallery.php');
$GLOBALS['__autoload']['dcGallery'] = dirname(__FILE__).'/class.dc.gallery.php';
$GLOBALS['__autoload']['dcRsGallery'] = dirname(__FILE__).'/class.dc.rs.gallery.php';

/* URL Handlers for galleries lists, galleries and images */
$GLOBALS['core']->url->register('gallery','gallery','^gallery/(.+)$',array('urlGallery','gallery'));
$GLOBALS['core']->url->register('galleries','galleries','^galleries.*$',array('urlGallery','galleries'));
$GLOBALS['core']->url->register('image','image','^image/(.+)$',array('urlGallery','image'));

class urlGallery extends dcUrlHandlers
{
	public static function gallery($args)
	{
		$n = self::getPageNumber($args);
		
		if ($args == '') {
			self::p404();
		}
		if ($n) {
			$GLOBALS['_page_number'] = $n;
			$GLOBALS['core']->url->type = $n > 1 ? 'defaut-page' : 'default';
		}

		$GLOBALS['core']->blog->withoutPassword(false);
		$GLOBALS['core']->gallery = new dcGallery($GLOBALS['core']);;
		
		$params['post_url'] = $args;
		$params['post_type'] = 'gal';
		$GLOBALS['_ctx']->posts = $GLOBALS['core']->blog->getPosts($params);
		$GLOBALS['_ctx']->posts->extend('rsExtGallery');
		$GLOBALS['_ctx']->comment_preview = new ArrayObject();
		$GLOBALS['_ctx']->comment_preview['content'] = '';
		$GLOBALS['_ctx']->comment_preview['rawcontent'] = '';
		$GLOBALS['_ctx']->comment_preview['name'] = '';
		$GLOBALS['_ctx']->comment_preview['mail'] = '';
		$GLOBALS['_ctx']->comment_preview['site'] = '';
		$GLOBALS['_ctx']->comment_preview['preview'] = false;
		$GLOBALS['_ctx']->comment_preview['remember'] = false;
		
		$GLOBALS['core']->blog->withoutPassword(true);
		
		$post_comment =
			isset($_POST['c_name']) && isset($_POST['c_mail']) &&
			isset($_POST['c_site']) && isset($_POST['c_content']);
		
		
		if ($GLOBALS['_ctx']->posts->isEmpty())
		{
			# No entry
			self::p404();
		}
		
		$post_id = $GLOBALS['_ctx']->posts->post_id;
		$post_password = $GLOBALS['_ctx']->posts->post_password;
		
		# Getting commenter informations from cookie
		if (!empty($_COOKIE['comment_info'])) {
			$c_cookie = unserialize($_COOKIE['comment_info']);
			foreach ($c_cookie as $k => $v) {
				$GLOBALS['_ctx']->comment_preview[$k] = $v;
			}
			$GLOBALS['_ctx']->comment_preview['remember'] = true;
		}
		
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
			
			# Storing commenter informations in cookie
			if (!empty($_POST['c_remember'])) {
				$c_cookie = array('name' => $name,'mail' => $mail,
				'site' => $site);
				
				$c_cookie = serialize($c_cookie);
				setcookie('comment_info',$c_cookie,strtotime('+3 month'),'/');
			}
			
			if ($content != '')
			{
				if ($GLOBALS['core']->blog->settings->wiki_comments) {
					$GLOBALS['core']->initWikiComment();
				} else {
					$GLOBALS['core']->initWikiSimpleComment();
				}
				$content = $GLOBALS['core']->wikiTransform($content);
				$content = $GLOBALS['core']->HTMLfilter($content);
			}
			
			$GLOBALS['_ctx']->comment_preview['content'] = $content;
			$GLOBALS['_ctx']->comment_preview['rawcontent'] = $_POST['c_content'];
			$GLOBALS['_ctx']->comment_preview['name'] = $name;
			$GLOBALS['_ctx']->comment_preview['mail'] = $mail;
			$GLOBALS['_ctx']->comment_preview['site'] = $site;
			
			if ($preview)
			{
				$GLOBALS['_ctx']->comment_preview['preview'] = true;
			}
			else
			{
				# Post the comment
				$cur = $GLOBALS['core']->con->openCursor($GLOBALS['core']->prefix.'comment');
				$cur->comment_author = $name;
				$cur->comment_site = html::clean($site);
				$cur->comment_email = html::clean($mail);
				$cur->comment_content = $content;
				$cur->post_id = $GLOBALS['_ctx']->posts->post_id;
				$cur->comment_status = $GLOBALS['core']->blog->settings->comments_pub ? 1 : -1;
				$cur->comment_ip = http::realIP();
				
				$redir = $GLOBALS['_ctx']->posts->getURL();
				$redir .= strpos($redir,'?') !== false ? '&' : '?';
				
				try
				{
					if (!text::isEmail($cur->comment_email)) {
						throw new Exception(__('You must provide a valid email adress.'));
					}
					
					# --BEHAVIOR-- publicBeforeCommentCreate
					$GLOBALS['core']->callBehavior('publicBeforeCommentCreate',$cur);
					
					$comment_id = $GLOBALS['core']->blog->addComment($cur);
					
					# --BEHAVIOR-- publicAfterCommentCreate
					$GLOBALS['core']->callBehavior('publicAfterCommentCreate',$cur,$comment_id);
					
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
					$GLOBALS['_ctx']->form_error = $e->getMessage();
					$GLOBALS['_ctx']->form_error;
				}
			}
		}
		
		# The entry
		self::serveDocument('gallery.html');
		exit;
	}
	
	public static function galleries($args)
	{
		self::serveDocument('galleries.html');
		exit;
	}

	public static function image($args)
	{
		if ($args == '') {
			self::p404();
		}
		
		$GLOBALS['core']->blog->withoutPassword(false);
		
		$params['post_type'] = 'galitem';
		$params['post_url'] = $args;
		$GLOBALS['core']->gallery = new dcGallery($GLOBALS['core']);
		/*$GLOBALS['core']->meta = new dcMeta($GLOBALS['core']);*/
		$GLOBALS['_ctx']->gallery_url = isset($_GET['gallery'])?$_GET['gallery']:null;
		$GLOBALS['_ctx']->posts = $GLOBALS['core']->gallery->getGalImageMedia($params);
		
		$GLOBALS['_ctx']->comment_preview = new ArrayObject();
		$GLOBALS['_ctx']->comment_preview['content'] = '';
		$GLOBALS['_ctx']->comment_preview['rawcontent'] = '';
		$GLOBALS['_ctx']->comment_preview['name'] = '';
		$GLOBALS['_ctx']->comment_preview['mail'] = '';
		$GLOBALS['_ctx']->comment_preview['site'] = '';
		$GLOBALS['_ctx']->comment_preview['preview'] = false;
		$GLOBALS['_ctx']->comment_preview['remember'] = false;
		
		$GLOBALS['core']->blog->withoutPassword(true);
		$GLOBALS['_ctx']->media=$GLOBALS['core']->gallery->readMedia($GLOBALS['_ctx']->posts);
/*		$GLOBALS['_ctx']->galitems = $GLOBALS['core']->media->getPostMedia($GLOBALS['_ctx']->posts->post_id);
		$GLOBALS['_ctx']->galitem=$GLOBALS['_ctx']->galitems[0];*/
		$post_comment =
			isset($_POST['c_name']) && isset($_POST['c_mail']) &&
			isset($_POST['c_site']) && isset($_POST['c_content']);
		
		
		if ($GLOBALS['_ctx']->posts->isEmpty())
		{
			# No entry
			self::p404();
		}
		
		$post_id = $GLOBALS['_ctx']->posts->post_id;
		$post_password = $GLOBALS['_ctx']->posts->post_password;
		
		# Getting commenter informations from cookie
		if (!empty($_COOKIE['comment_info'])) {
			$c_cookie = unserialize($_COOKIE['comment_info']);
			foreach ($c_cookie as $k => $v) {
				$GLOBALS['_ctx']->comment_preview[$k] = $v;
			}
			$GLOBALS['_ctx']->comment_preview['remember'] = true;
		}
		
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
			
			# Storing commenter informations in cookie
			if (!empty($_POST['c_remember'])) {
				$c_cookie = array('name' => $name,'mail' => $mail,
				'site' => $site);
				
				$c_cookie = serialize($c_cookie);
				setcookie('comment_info',$c_cookie,strtotime('+3 month'),'/');
			}
			
			if ($content != '')
			{
				if ($GLOBALS['core']->blog->settings->wiki_comments) {
					$GLOBALS['core']->initWikiComment();
				} else {
					$GLOBALS['core']->initWikiSimpleComment();
				}
				$content = $GLOBALS['core']->wikiTransform($content);
				$content = $GLOBALS['core']->HTMLfilter($content);
			}
			
			$GLOBALS['_ctx']->comment_preview['content'] = $content;
			$GLOBALS['_ctx']->comment_preview['rawcontent'] = $_POST['c_content'];
			$GLOBALS['_ctx']->comment_preview['name'] = $name;
			$GLOBALS['_ctx']->comment_preview['mail'] = $mail;
			$GLOBALS['_ctx']->comment_preview['site'] = $site;
			
			if ($preview)
			{
				$GLOBALS['_ctx']->comment_preview['preview'] = true;
			}
			else
			{
				# Post the comment
				$cur = $GLOBALS['core']->con->openCursor($GLOBALS['core']->prefix.'comment');
				$cur->comment_author = $name;
				$cur->comment_site = html::clean($site);
				$cur->comment_email = html::clean($mail);
				$cur->comment_content = $content;
				$cur->post_id = $GLOBALS['_ctx']->posts->post_id;
				$cur->comment_status = $GLOBALS['core']->blog->settings->comments_pub ? 1 : -1;
				$cur->comment_ip = http::realIP();
				
				$redir = $GLOBALS['_ctx']->posts->getURL();
				$redir .= strpos($redir,'?') !== false ? '&' : '?';
				
				try
				{
					if (!text::isEmail($cur->comment_email)) {
						throw new Exception(__('You must provide a valid email adress.'));
					}
					
					# --BEHAVIOR-- publicBeforeCommentCreate
					$GLOBALS['core']->callBehavior('publicBeforeCommentCreate',$cur);
					
					$comment_id = $GLOBALS['core']->blog->addComment($cur);
					
					# --BEHAVIOR-- publicAfterCommentCreate
					$GLOBALS['core']->callBehavior('publicAfterCommentCreate',$cur,$comment_id);
					
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
					$GLOBALS['_ctx']->form_error = $e->getMessage();
					$GLOBALS['_ctx']->form_error;
				}
			}
		}
		self::serveDocument('image.html');
		exit;
	}

}
?>
