<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of Dotclear 2 Gallery plugin.
#
# Copyright (c) 2004-2008 Bruno Hondelatte, and contributors. 
# Many, many thanks to Olivier Meunier and the Dotclear Team.
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------

class urlGallery extends dcUrlHandlers
{
	public static function serveThemeDocument($theme,$page,$content_type='text/html',$http_cache=true,$http_etag=true) {
		global $core;
		if ($theme == '')
			self::serveDocument($page,$content_type,$http_cache,$http_etag);
		elseif ($GLOBALS['core']->tpl->getFilePath("gal_".$theme.'/'.$page) !== false)
			self::serveDocument('gal_'.$theme.'/'.$page,$content_type,$http_cache,$http_etag);
		else
			self::serveDocument('gal_simple/'.$page,$content_type,$http_cache,$http_etag);

	}
	public static function gallery($args)
	{
		$n = self::getPageNumber($args);
		$theme='';
		$type='';
		if (preg_match('%(^|/)feed/(mediarss|rss2|atom|custom)/([0-9]+)$%',$args,$m)){
			$args = preg_replace('#(^|/)feed/(mediarss|rss2|atom|custom)/([0-9]+)$#','',$args);
			$type = $m[2];
			if ($type == 'custom') {
				$theme=$GLOBALS['core']->blog->settings->gallery_default_theme;
				$page = "image_feed.xml";
			} else {
				$page = "gal_feed/img-".$type.".xml";
			}
			$mime = 'application/xml';
			$params['post_id'] = $m[3];
		} elseif (preg_match('%(^|/)feed/(mediarss|rss2|atom)/comments/([0-9]+)$%',$args,$m)){
			$args = preg_replace('#(^|/)feed/(mediarss|rss2|atom)/comments/([0-9]+)$#','',$args);
			$type = $m[2];
			$page = "gal_feed/gal-".$type."-comments.xml";
			$mime = 'application/xml';
			$params['post_id'] = $m[3];
		} elseif ($args != '') {
			//$page=$GLOBALS['core']->blog->settings->gallery_default_theme.'/gallery.html';
			$page='gallery.html';
			$params['post_url'] = $args;
			$mime='text/html';
			$theme=$GLOBALS['core']->blog->settings->gallery_default_theme;
		} else {
			self::p404();
		}
		if ($n) {
			$GLOBALS['_page_number'] = $n;
			$GLOBALS['core']->url->type = $n > 1 ? 'gal-page' : 'gal';
		}

		$GLOBALS['core']->blog->withoutPassword(false);
		$GLOBALS['core']->meta = new dcMeta($GLOBALS['core']);;
		$GLOBALS['core']->gallery = new dcGallery($GLOBALS['core']);;
		$GLOBALS['_ctx']->posts = $GLOBALS['core']->gallery->getGalleries($params);
		/*$GLOBALS['_ctx']->posts->extend('rsExtGallery');*/
		$gal_params = $GLOBALS['core']->gallery->getGalOrder($GLOBALS['_ctx']->posts);
		$gal_params['gal_url']=$GLOBALS['_ctx']->posts->post_url;
		$GLOBALS['_ctx']->gal_params = $gal_params;
		$GLOBALS['_ctx']->gallery_url = $GLOBALS['_ctx']->posts->post_url;
		$GLOBALS['_ctx']->gallery_theme = $theme;
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
		if ($theme != '') {
			$theme = $GLOBALS['core']->gallery->getGalTheme($GLOBALS["_ctx"]->posts);
			$GLOBALS['_ctx']->gallery_theme = $theme;
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
						throw new Exception(__('You must provide a valid email address.'));
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
		self::serveThemeDocument($theme,$page,$mime);
		exit;
	}
	
	public static function galleries($args)
	{
		$n = self::getPageNumber($args);
		if (preg_match('#(^|/)category/(.+)$#',$args,$m)){
			$params['cat_url']=$m[2];
			$GLOBALS['_ctx']->categories = $GLOBALS['core']->blog->getCategories($params);
		}
		if (preg_match('#(^|/)nocat$#',$args,$m)){
			$GLOBALS['_ctx']->nocat = true;
		}
		if ($n) {
			$GLOBALS['_page_number'] = $n;
			$GLOBALS['core']->url->type = $n > 1 ? 'galleries-page' : 'galleries';
		}
		$GLOBALS['core']->meta = new dcMeta($GLOBALS['core']);;
		$GLOBALS['core']->gallery = new dcGallery($GLOBALS['core']);
		$GLOBALS['_ctx']->nb_entry_per_page= $GLOBALS['core']->blog->settings->gallery_nb_galleries_per_page;
		self::serveThemeDocument('gal_'.$GLOBALS['core']->blog->settings->gallery_default_theme,'/galleries.html');
		exit;
	}

	public static function image($args)
	{
		$theme='';
		if (preg_match('%(^|/)feed/(mediarss|rss2|atom)/comments/([0-9]+)$%',$args,$m)){
			$args = preg_replace('#(^|/)feed/(mediarss|rss2|atom)/comments/([0-9]+)$#','',$args);
			$type = $m[2];
			$page = "gal_feed/img-".$type."-comments.xml";
			$mime = 'application/xml';
			$params['post_id'] = $m[3];
		} elseif ($args != '') {
			$theme=$GLOBALS['core']->blog->settings->gallery_default_theme;
			$page='image.html';
			$params['post_url'] = $args;
			$mime='text/html';
		} else {
			self::p404();
		}
		/*if ($args == '') {
			self::p404();
		}*/
		
		$GLOBALS['core']->blog->withoutPassword(false);
		
		$params['post_type'] = 'galitem';
		//$params['post_url'] = $args;
		$GLOBALS['core']->meta = new dcMeta($GLOBALS['core']);;
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
		
		$GLOBALS['_ctx']->current_item_url=$GLOBALS['_ctx']->posts->post_url;
		$post_id = $GLOBALS['_ctx']->posts->post_id;
		$post_password = $GLOBALS['_ctx']->posts->post_password;
		
		if ($GLOBALS['_ctx']->gallery_url != null) {
			$galtemp = $GLOBALS['core']->gallery->getGalleries(array('post_url'=>$GLOBALS['_ctx']->gallery_url));
			$meta = $GLOBALS['core']->meta->getMetaArray($galtemp->post_meta);
			if (isset($meta['galtheme'])) {
				$theme = $meta['galtheme'][0];
			} else {
				$theme=$GLOBALS['core']->blog->settings->gallery_default_theme;
			}
		} elseif ($theme != '') {
			$theme=$GLOBALS['core']->blog->settings->gallery_default_theme;
		}
		$GLOBALS['_ctx']->gallery_theme=$theme;

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
						throw new Exception(__('You must provide a valid email address.'));
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
		//self::serveDocument('image.html');
		self::serveThemeDocument($theme,$page,$mime);
		exit;
	}

	public static function images($args)
	{
		$n = self::getPageNumber($args);
		if (preg_match('#(^|/)([A-Za-z]+)/(.+)$#',$args,$m)) {
			$filter_type = $m[2];
			$filter = $m[3];
		} else {
			self::p404();
		}
		switch ($filter_type) {
			case "tag":
				$gal_params['tag']=$filter;
				break;
			case "category":
				$gal_params['cat_url']=$filter;
				break;
			default:
				self::p404();
				return;
		}
		
		if ($n) {
			$GLOBALS['_page_number'] = $n;
			$GLOBALS['core']->url->type = $n > 1 ? 'img-page' : 'img';
		}

		$GLOBALS['core']->blog->withoutPassword(false);
		$GLOBALS['core']->meta = new dcMeta($GLOBALS['core']);;
		$GLOBALS['core']->gallery = new dcGallery($GLOBALS['core']);;
		
		$params['post_url'] = $args;
		$GLOBALS['_ctx']->posts = $GLOBALS['core']->gallery->getGalleries($params);
		$GLOBALS['_ctx']->gal_params = $gal_params;
		
		$post_comment =
			isset($_POST['c_name']) && isset($_POST['c_mail']) &&
			isset($_POST['c_site']) && isset($_POST['c_content']);
		
		
		
		# The entry
		self::serveDocument('gal_simple/images.html');
		exit;
	}
	public static function browse($args)
	{
		self::serveDocument('gal_simple/browser.html');
		exit;
	}
	public static function imagepreview($args)
	{
		$core = $GLOBALS['core'];
		if (!preg_match('#^(.+?)/([0-9a-z]{40})/(.+?)$#',$args,$m)) {
			self::p404();
		}
		$user_id = $m[1];
		$user_key = $m[2];
		$post_url = $m[3];
		if (!$core->auth->checkUser($user_id,null,$user_key)) {
			self::p404();
		}
		
		self::image($post_url);
		exit;
	}

	public static function gallerypreview($args)
	{
		$core = $GLOBALS['core'];
		if (!preg_match('#^(.+?)/([0-9a-z]{40})/(.+?)$#',$args,$m)) {
			self::p404();
		}
		$user_id = $m[1];
		$user_key = $m[2];
		$post_url = $m[3];
		if (!$core->auth->checkUser($user_id,null,$user_key)) {
			self::p404();
		}
		
		self::gallery($post_url);
		exit;
	}


}

class urlGalleryProxy extends dcUrlHandlers
{
	public static function galtheme($args) {
		if (preg_match('#([^/]+)/(.+)$#',$args,$m)) {
			$theme = $m[1];
			$res = $m[2];
			if (strstr($res,"..") !== false) {
				self::p404();
				exit;
			}
			$full_path = path::fullFromRoot($GLOBALS['core']->blog->settings->gallery_themes_path.'/gal_'.$theme.'/'.$res,DC_ROOT);
			if (!file_exists($full_path)) {
			$full_path = path::fullFromRoot($GLOBALS['core']->blog->settings->gallery_themes_path.'/gal_simple/'.$res,DC_ROOT);
				$theme="simple";
			}

			$allowed_types = array('png','jpg','jpeg','gif','css','js','swf');
			if (!file_exists($full_path) || !in_array(files::getExtension($full_path),$allowed_types)) {
				self::p404();
				exit;
			}
			http::cache(array_merge(array($full_path),get_included_files()));
			$type = files::getMimeType($full_path);
			header('Content-Type: '.$type);
			header('Content-Length: '.filesize($full_path));
			if ($type != "text/css" || $GLOBALS['core']->blog->settings->url_scan == 'path_info') {
				readfile($full_path);
			} else {
				$str = file_get_contents($full_path);
				echo preg_replace('#url\((?!(http:)|/)#','url('.$GLOBALS['core']->blog->url."gallerytheme/".$theme."/",$str);
			}
			exit;

		} else {
			self::p404();
		}

	}

}
?>
