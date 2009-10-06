<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of pageMaker, a plugin for Dotclear.
# 
# Copyright (c) 2009 Tomtom
# http://blog.zenstyle.fr/
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')) { return; }

$core->tpl->addValue('PostPagination',array('pageMakerTpl','PostPagination'));
$core->tpl->addValue('EntryContent',array('pageMakerTpl','EntryContent'));
$core->tpl->addValue('CommentPagination',array('pageMakerTpl','CommentPagination'));
$core->tpl->addValue('CommentOrderNumber',array('pageMakerTpl','CommentOrderNumber'));
$core->tpl->addBlock('Comments',array('pageMakerTpl','Comments'));

$core->url->register('post','post','^post/(.+)$',array('pageMakerUrl','post'));

$core->addBehavior('coreBlogGetPosts',array('pageMakerBehaviors','coreBlogGetPosts'));

class pageMakerUrl extends dcUrlHandlers
{
	public static function post($args)
	{
		if ($args == '') {
			self::p404();
		}
		
		$_ctx = $GLOBALS['_ctx'];
		$core = $GLOBALS['core'];
		
		$core->blog->withoutPassword(false);
		
		# Create pagination pattern
		for ($i=0 ; $i < count(explode('/',$core->blog->settings->post_url_format)) ; $i++) {
			$post_format[] = '[^/]*';
		}
		
		$pattern = '#^('.implode('/',$post_format).')((/page/([0-9]+))?(/c/([0-9]+))?)$#';
		
		preg_match($pattern,$args,$matches);

		$params = new ArrayObject();
		$params['post_url'] = $matches[1];
		
		$_ctx->posts = $core->blog->getPosts($params);
		
		# Post page
		if ($core->blog->settings->pagemaker_post_enable) {
			$_ctx->post_page_count = count(preg_split($core->post_page_pattern,$_ctx->posts->post_content_xhtml));
			$_ctx->post_page_current = !empty($matches[4]) ? (int) $matches[4] : 1;
			if (
				$_ctx->post_page_current === 0 ||
				$_ctx->post_page_current > $_ctx->post_page_count
			) {
				self::p404();
				return;
			}
		}
		# Comment page
		if ($core->blog->settings->pagemaker_comment_enable) {
			$_ctx->post_comment_count = ceil($core->blog->getComments(array('no_content' => 1,'comment_trackback' => 0, 'post_id' => $_ctx->posts->post_id),true)->f(0)/$core->blog->settings->pagemaker_comment_nb_per_page);
			$_ctx->post_comment_current = !empty($matches[6]) ? (int) $matches[6] : 1;
			if (
				$_ctx->post_comment_count === 0 ||
				$_ctx->post_comment_current > $_ctx->post_comment_count
			) {
				self::p404();
				return;
			}
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
			return;
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

class pageMakerTpl
{
	public static function EntryContent($attr)
	{
		$urls = '0';
		if (!empty($attr['absolute_urls'])) {
			$urls = '1';
		}
		
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		
		$res = '';
		
		
		if (!empty($attr['full'])) {
			$res = '<?php echo '.sprintf($f,
				'$_ctx->posts->getExcerpt('.$urls.')." ".$_ctx->posts->getContent('.$urls.')').'; ?>';
		} else {
			$res = '<?php echo '.sprintf($f,'$_ctx->posts->getContent('.$urls.')').'; ?>';
		}
		
		$res .= "<?php if (\$core->blog->settings->pagemaker_post_auto_insert) : ?>\n";
		$res .= pageMakerTpl::PostPagination($attr);
		$res .= "<?php endif; ?>\n";

		return $res;
	}
	
	public static function Comments($attr,$content)
	{
		$p =
		"if (\$_ctx->posts !== null) { ".
			"\$params['post_id'] = \$_ctx->posts->post_id; ".
			"\$core->blog->withoutPassword(false);\n".
		"}\n";
		
		if (empty($attr['with_pings'])) {
			$p .= "\$params['comment_trackback'] = false;\n";
		}
		
		$lastn = 0;
		if (isset($attr['lastn'])) {
			$lastn = abs((integer) $attr['lastn'])+0;
		}
		
		if ($lastn > 0) {
			$p .= "\$params['limit'] = ".$lastn.";\n";
		} else {
			$p .= "if (\$_ctx->nb_comment_per_page !== null) { \$params['limit'] = \$_ctx->nb_comment_per_page; }\n";
		}
		
		if (empty($attr['no_context']))
		{
			$p .=
			'if ($_ctx->exists("categories")) { '.
				"\$params['cat_id'] = \$_ctx->categories->cat_id; ".
			"}\n";
			
			$p .=
			'if ($_ctx->exists("langs")) { '.
				"\$params['sql'] = \"AND P.post_lang = '\".\$core->blog->con->escape(\$_ctx->langs->post_lang).\"' \"; ".
			"}\n";
		}
		
		$order = 'asc';
		if (isset($attr['order']) && preg_match('/^(desc|asc)$/i',$attr['order'])) {
			$order = $attr['order'];
		}
		
		$p .= "\$params['order'] = 'comment_dt ".$order."';\n";
		
		if (isset($attr['no_content']) && $attr['no_content']) {
			$p .= "\$params['no_content'] = true;\n";
		}
	
		$p .= 'if ($_ctx->post_comment_current !== null) {'. 
			$p .= "\$params['limit'] = array(((\$_ctx->post_comment_current-1)*\$core->blog->settings->pagemaker_comment_nb_per_page),\$core->blog->settings->pagemaker_comment_nb_per_page); ";
		$p .= "}\n";
		
		$res = "<?php\n";
		$res .= $p;
		$res .= '$_ctx->comments = $core->blog->getComments($params); unset($params);'."\n";
		$res .= "if (\$_ctx->posts !== null) { \$core->blog->withoutPassword(true);}\n";
		
		if (!empty($attr['with_pings'])) {
			$res .= '$_ctx->pings = $_ctx->comments;'."\n";
		}
		
		$res .= "?>\n";
		
		$res .= '<?php while ($_ctx->comments->fetch()) : ?>'.$content.'<?php endwhile; ?>';
		$res .= "<?php if (\$core->blog->settings->pagemaker_comment_auto_insert) : ?>\n";
		$res .= pageMakerTpl::CommentPagination($attr);
		$res .= "<?php endif; ?>\n";
		$res .= '<?php $_ctx->comments = null; ?>';
		
		return $res;
	}
	
	public static function CommentOrderNumber($attr)
	{
		return '<?php echo $_ctx->comments->index()+1+((\$_ctx->post_comment_current-1)*\$core->blog->settings->pagemaker_comment_nb_per_page); ?>';
	}
	
	public static function PostPagination($attr)
	{
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		
		$max = isset($attr['max']) ? (int) $attr['max'] : 20;
		
		$p = "\$params = array();\n";
		if (isset($attr['current'])) {
			$p .= "\$params['current'] = '".$attr['current']."';\n";
		}
		if (isset($attr['prev'])) {
			$p .= "\$params['prev'] = '".$attr['prev']."';\n";
		}
		if (isset($attr['next'])) {
			$p .= "\$params['next'] = '".$attr['next']."';\n";
		}
		
		$p .= "\$params['post_comment_current'] = \$_ctx->post_comment_current;\n";
		$p .= "\$params['type'] = 'post';\n";
		
		$res = "<?php\n";
		$res .= $p;
		$res .= "\$pager = new pageMakerPager(\$_ctx->post_page_current,\$_ctx->post_page_count,".$max.");\n";
		$res .= "\$pager->init(\$params);\n";
		$res .= "echo ".sprintf($f,'$pager->getLinks()').";\n";
		$res .= "?>\n";
		
		return $res;
	}

	public static function CommentPagination($attr)
	{
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		
		$max = isset($attr['max']) ? (int) $attr['max'] : 20;
		
		$p = "\$params = array();\n";
		if (isset($attr['current'])) {
			$p .= "\$params['current'] = '".$attr['current']."';\n";
		}
		if (isset($attr['prev'])) {
			$p .= "\$params['prev'] = '".$attr['prev']."';\n";
		}
		if (isset($attr['next'])) {
			$p .= "\$params['next'] = '".$attr['next']."';\n";
		}
		
		$p .= "\$params['post_page_current'] = \$_ctx->post_page_current;\n";
		$p .= "\$params['type'] = 'comment';\n";
		
		$res = "<?php\n";
		$res .= $p;
		$res .= "\$pager = new pageMakerPager(\$_ctx->post_comment_current,\$_ctx->post_comment_count,".$max.");\n";
		$res .= "\$pager->init(\$params);\n";
		$res .= "echo ".sprintf($f,'$pager->getLinks()').";\n";
		$res .= "?>\n";
		
		return $res;
	}
}

?>