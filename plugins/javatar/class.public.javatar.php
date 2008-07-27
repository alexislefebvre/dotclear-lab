<?php /* -*- tab-width: 5; indent-tabs-mode: t; c-basic-offset: 5 -*- */
/***************************************************************\
 *  This is 'Javatar', a plugin for Dotclear 2                 *
 *                                                             *
 *  Copyright (c) 2008                                         *
 *  Osku and contributors.                                     *
 *                                                             *
 *  This is an open source software, distributed under the GNU *
 *  General Public License (version 2) terms and  conditions.  *
 *                                                             *
 *  You should have received a copy of the GNU General Public  *
 *  License along with 'Javatar' (see COPYING.txt);            *
 *  if not, write to the Free Software Foundation, Inc.,       *
 *  59 Temple Place, Suite 330, Boston, MA  02111-1307  USA    *
\***************************************************************/

class JabberHandler extends dcUrlHandlers
{
	public static function post($args)
	{
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
		$_ctx->comment_preview['jabber'] = '';
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
			isset($_POST['c_jabber']) &&
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
			$jabber = $_POST['c_jabber'];
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
			$_ctx->comment_preview['jabber'] = $jabber;
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
				$cur->comment_jabber = html::clean($jabber);
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
		exit;
	}
}
class publicJavatar
{
	public static $c_info = array();
	
	public static function publicHeadContent()
	{
		global $core;
		
		if (!$core->blog->settings->javatar_active) {
			return;
		}
		
		$custom_css = $core->blog->settings->javatar_custom_css;
				
		if (!empty($custom_css))
		{
			if (strpos('/',$custom_css) === 0) {
				$css = $custom_css;
			}
			else {
				$css =
					$core->blog->settings->themes_url."/".
					$core->blog->settings->theme."/".
					$custom_css;
			}
		}
		else
		{
			$css = html::stripHostURL($core->blog->getQmarkURL().'pf=javatar/default/javatar-default.css');
		}
		
		echo
		'<style type="text/css" media="screen">@import url('.$css.');</style>'."\n";
	}

	public static function coreBlogGetComments(&$c_rs)
	{
		$ids = array();
		while ($c_rs->fetch())
		{
			if (!$c_rs->comment_trackback) {
				$ids[] = $c_rs->comment_id;
			}
		}
		
		if (empty($ids)) {
			return;
		}

		$ids = implode(', ',$ids);

		$strReq =
		'SELECT comment_id, comment_jabber '.
		'FROM '.$c_rs->core->prefix.'comment '.
		'WHERE comment_id  IN ('.$ids.')';
		$rs = $c_rs->core->con->select($strReq);

		while ($rs->fetch())
		{
			self::$c_info[$rs->comment_id] = array(
			'javatar'=>$rs->comment_jabber
			);
		}
		
		$c_rs->extend('rsExtCommentJavatar');
	}
}
?>