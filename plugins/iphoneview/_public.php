<?php
# ***** BEGIN LICENSE BLOCK *****
# This file is part of iPhoneView.
# Copyright (c) 2009 Hadrien Lanneau.
# All rights reserved.
#
# Pixearch is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 2 of the License, or
# (at your option) any later version.
# 
# iPhoneView is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
# 
# You should have received a copy of the GNU General Public License
# along with iPhoneView; if not, write to the Free Software
# Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
#
# http://www.alti.info/pages/iPhoneView
#
# ***** END LICENSE BLOCK *****

$core->addBehavior(
	'publicPrepend', //publicPrepend
	array(
		'iPhoneViewUrls',
		'detect'
	)
);


$core->tpl->addValue(
	'iPhoneThemeURL',
	array(
		'iPhoneViewUrls',
		'iPhoneThemeURL'
	)
);
$core->tpl->addValue(
	'CurrentURL',
	array(
		'iPhoneViewUrls',
		'CurrentURL'
	)
);

$core->tpl->addValue(
	'BlogiPhoneURL',
	array(
		'iPhoneViewUrls',
		'BlogiPhoneURL'
	)
);
$core->tpl->addValue(
	'EntryiPhoneURL',
	array(
		'iPhoneViewUrls',
		'EntryiPhoneURL'
	)
);
$core->tpl->addValue(
	'EntryCategoryiPhoneURL',
	array(
		'iPhoneViewUrls',
		'EntryCategoryiPhoneURL'
	)
);
$core->tpl->addValue(
	'MetaiPhoneURL',
	array(
		'iPhoneViewUrls',
		'MetaiPhoneURL'
	)
);
$core->tpl->addValue(
	'CategoryiPhoneURL',
	array(
		'iPhoneViewUrls',
		'CategoryiPhoneURL'
	)
);

/**
* iPhoneView
*/
class iPhoneViewUrls extends dcUrlHandlers
{
	/**
	 * Detect by user agent or cookie if we have to display normal or iphone view
	 *
	 * @return redirect
	 * @author Hadrien Lanneau (contact at hadrien dot eu)
	 **/
	public static function detect()
	{
		global $core;
		
		$iphoneUrl = $core->blog->url . 'iphone';
		
		if (preg_match(
				'/iPhone|iPod/',
				$_SERVER['HTTP_USER_AGENT']
			) and
			$_COOKIE['iphoneview'] != 'no')
		{
			if (stripos(
					http::getSelfURI(),
					$iphoneUrl
				) === false)
			{
				http::redirect(
					$iphoneUrl . $_SERVER['REQUEST_URI']
				);
			}
		}
	}

	/**
	 * Convert iphone url to standard url
	 *
	 * @return string
	 * @author Hadrien Lanneau (contact at hadrien dot eu)
	 **/
	public static function convertUrlToStandard()
	{
		return $core->blog->normalurl . $_SERVER['REQUEST_URI'];
	}
	
	/**
	 * Get iPhone tpl path
	 *
	 * @return array
	 * @author Hadrien Lanneau (contact at hadrien dot eu)
	 **/
	public static function getTplPath()
	{
		global $core;
		$pathes = $core->tpl->getPath();
		foreach ($pathes as $k => $p)
		{
			$pathes[$k] = $p . '/iphone';
		}
		return $pathes;
	}
	
	/**
	 * Set iPhone cookie on
	 *
	 * @return void
	 * @author Hadrien Lanneau (contact at hadrien dot eu)
	 **/
	public static function setiPhoneCookieON()
	{
		setCookie(
			'iphoneview', null
		);
	}
	
	//--------------------------------------------------------------------------
	// Pages
	//--------------------------------------------------------------------------
	
	/**
	 * iPhone Home page
	 */
	public static function home($args)
	{
		self::setiPhoneCookieON();
		
		$n = $args;
		
		if ($args && !$n)
		{
			self::p404();
		}
		else
		{
			$core =& $GLOBALS['core'];
			
			$core->tpl->setPath(
				self::getTplPath(),
				dirname(__FILE__) . '/default-templates'
			);
			
			if ($n)
			{
				$GLOBALS['_page_number'] = $n;
				$core->url->type = $n > 1 ? 'default-page' : 'default';
				
				self::serveDocument('_entries.html');
				$core->blog->publishScheduledEntries();
				exit;
			}
			
			if (empty($_GET['q']))
			{
				self::serveDocument('home.html');
				$core->blog->publishScheduledEntries();
				exit;
			}
			else
			{
				self::search();
			}
		}
	}
	
	/**
	 * iPhone Post page
	 */
	public static function post($args)
	{
		self::setiPhoneCookieON();
		
		if ($args == '') {
			self::p404();
		}
		
		$_ctx =& $GLOBALS['_ctx'];
		$core =& $GLOBALS['core'];
		
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
		
		$post_id = $_ctx->posts->post_id;
		$post_password = $_ctx->posts->post_password;
		
		# Password protected entry
		if ($post_password != '')
		{
			http::redirect(
				self::convertUrlToStandard()
			);
			exit();
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
		
		$core->tpl->setPath(
			self::getTplPath(),
			dirname(__FILE__) . '/default-templates'
		);
		
		# The entry
		self::serveDocument('post.html');
		exit;
	}
	
	public static function category($args)
	{
		self::setiPhoneCookieON();
		
		if (preg_match(
				'/(\w*)\/page\/(.*?)$/',
				$args,
				$m
			))
		{
			$cat = $m[1];
			$n = intval($m[2]);
		}
		else
		{
			$cat = $args;
			$n = null;
		}
		
		$_ctx =& $GLOBALS['_ctx'];
		$core =& $GLOBALS['core'];
		
		$core->tpl->setPath(
			self::getTplPath(),
			dirname(__FILE__) . '/default-templates'
		);
		
		if ($args == '' && !$n) {
			self::p404();
		}
		
		$params['cat_url'] = $cat;
		$params['post_type'] = 'post';
		
		$_ctx->categories = $core->blog->getCategories($params);
		
		if ($_ctx->categories->isEmpty()) {
			self::p404();
		}
		elseif ($n)
		{
			$GLOBALS['_page_number'] = $n;
			$core->url->type = $n > 1 ? 'default-page' : 'default';
			
			self::serveDocument('_entries.html');
			$core->blog->publishScheduledEntries();
			exit;
		}
		else
		{
			if ($n) {
				$GLOBALS['_page_number'] = $n;
			}
			
			$core->tpl->setPath(
				self::getTplPath(),
				dirname(__FILE__) . '/default-templates'
			);
			
			self::serveDocument('category.html');
			exit;
		}
	}
	
	public static function search()
	{
		self::setiPhoneCookieON();
		
		$_ctx =& $GLOBALS['_ctx'];
		$core =& $GLOBALS['core'];
		
		$GLOBALS['_search'] = !empty($_GET['q']) ? rawurldecode($_GET['q']) : '';
		if ($GLOBALS['_search']) {
			$GLOBALS['_search_count'] = $core->blog->getPosts(array('search' => $GLOBALS['_search']),true)->f(0);
		}
		
		$core->tpl->setPath(
			self::getTplPath(),
			dirname(__FILE__) . '/default-templates'
		);
		
		self::serveDocument('search.html');
	}
	
	public static function tag($args)
	{
		self::setiPhoneCookieON();
		
		if (preg_match(
				'/(\w*)\/page\/(.*?)$/',
				$args,
				$m
			))
		{
			$tag = $m[1];
			$n = intval($m[2]);
		}
		else
		{
			$tag = $args;
			$n = null;
		}
		
		global $core;
		$core->tpl->setPath(
			self::getTplPath(),
			dirname(__FILE__) . '/default-templates'
		);
		
		if ($tag == '' && !$n)
		{
			self::p404();
		}
		else
		{
			$objMeta = new dcMeta($GLOBALS['core']);
			
			$GLOBALS['_ctx']->meta = $objMeta->getMeta('tag',null,$tag);
			
			if ($GLOBALS['_ctx']->meta->isEmpty()) {
				self::p404();
			}
			elseif ($n)
			{
				$GLOBALS['_page_number'] = $n;
				$core->url->type = $n > 1 ? 'default-page' : 'default';
				
				self::serveDocument('_entries.html');
				$core->blog->publishScheduledEntries();
				exit;
			}
			else
			{
				self::serveDocument('tag.html');
			}
		}
		exit;
	}
	
	//--------------------------------------------------------------------------
	// Templates vars
	//--------------------------------------------------------------------------
	
	public function iPhoneThemeURL()
	{
		return '<?php
		if (file_exists(
				$core->blog->themes_path . "/" .
				$core->blog->settings->theme . "/iphone/"
			))
		{
			echo $core->blog->settings->themes_url."/".$core->blog->settings->theme . "/iphone/";
		}
		else
		{
			echo "/plugins/iphoneview/default-templates/";
		}
		?>';
	}
	
	/**
	 * Get current URL
	 *
	 * @return string
	 * @author Hadrien Lanneau (contact at hadrien dot eu)
	 **/
	public static function CurrentURL($attr)
	{
		global $core;
		
		return str_replace(
			$core->blog->url,
			$core->blog->normalurl,
			http::getSelfURI()
		);
	}
	
	/**
	 *
	 */
	public function BlogiPhoneURL($attr)
	{
		global $core;
		
		$f = $core->tpl->getFilters($attr);
		return '<?php
		echo $core->blog->url . "iphone/";
		?>';
	}
	
	/**
	 *
	 */
	public function EntryiPhoneURL($attr)
	{
		global $core;
		
		$f = $core->tpl->getFilters($attr);
		return '<?php
		echo str_replace(
			"/post/",
			"/iphone/post/",
			'.sprintf($f,'$_ctx->posts->getURL()').'
		);
		?>';
	}
	
	/**
	 *
	 */
	public function EntryCategoryiPhoneURL($attr)
	{
		global $core;
		
		$f = $core->tpl->getFilters($attr);
		return '<?php
		echo str_replace(
			"/category/",
			"/iphone/category/",
			'.sprintf($f,'$_ctx->posts->getCategoryURL()').'
		);
		?>';
	}
	
	public function CategoryiPhoneURL($attr)
	{
		global $core;
		
		$f = $core->tpl->getFilters($attr);
		return '<?php
		echo str_replace(
			"/category/",
			"/iphone/category/",
			'.sprintf($f,'$core->blog->url.$core->url->getBase("category")."/".$_ctx->categories->cat_url').'
		);
		?>';
	}
	
	
	/**
	 *
	 */
	public function MetaiPhoneURL($attr)
	{
		global $core;
		
		$f = $core->tpl->getFilters($attr);
		return '<?php
		echo str_replace(
			"/tag/",
			"/iphone/tag/",
			'.sprintf($f,'$core->blog->url.$core->url->getBase("tag").'.
			'"/".rawurlencode($_ctx->meta->meta_id)').'
		);
		?>';
	}
}
