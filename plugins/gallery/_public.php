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

require dirname(__FILE__).'/_widgets.php';


/* Galleries list management */
$core->tpl->addBlock('GalleryEntries',array('tplGallery','GalEntries'));
$core->tpl->addBlock('GalleryEntryNext',array('tplGallery','GalEntryNext'));
$core->tpl->addBlock('GalleryEntryPrevious',array('tplGallery','GalEntryPrevious'));

/* Galleries items management */
$core->tpl->addBlock('GalleryItemEntries',array('tplGallery','GalItemEntries'));
$core->tpl->addBlock('GalleryPagination',array('tplGallery','GalPagination'));
$core->tpl->addValue('GalleryItemThumbURL',array('tplGallery','GalItemThumbURL'));
$core->tpl->addValue('GalleryItemURL',array('tplGallery','GalItemURL'));


/* StyleSheets URL */
$core->tpl->addValue('GalleryStyleURL',array('tplGallery','GalStyleURL'));

/* Templates dir */
$core->addBehavior('publicBeforeDocument',array('behaviorsGallery','addTplPath'));

class behaviorsGallery
{
  public static function addTplPath(&$core)
  {
    $core->tpl->setPath($core->tpl->getPath(),dirname(__FILE__).'/default-templates');
  }

}

class tplGallery
{
	/* Misc functions -------------------------------------------- */
	public static function GalStyleURL($attr,$content)
	{
		global $core;
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		$css = $core->blog->url.(($core->blog->settings->url_scan == 'path_info')?'?':'').'pf=gallery/default-templates/gallery.css';
		$res = "\n<?php echo '<style type=\"text/css\" media=\"screen\">@import url(".$css.");</style>';\n?>";
                return $res;

	}

	/* Gallery lists templates */

	# Lists galleries
	public static function GalEntries($attr,$content)
	{
		$lastn = 0;
		if (isset($attr['lastn'])) {
			$lastn = abs((integer) $attr['lastn'])+0;
		}
		
		$p = 'if (!isset($_page_number)) { $_page_number = 1; }'."\n";
		
		if ($lastn > 0) {
			$p .= "\$params['limit'] = ".$lastn.";\n";
		} else {
			$p .= "\$params['limit'] = \$core->blog->settings->nb_post_per_page;\n";
		}
		
		$p .= "\$params['limit'] = array(((\$_page_number-1)*\$params['limit']),\$params['limit']);\n";
		
		if (isset($attr['category'])) {
			$p .= "\$params['cat_url'] = '".addslashes($attr['category'])."';\n";
			$p .= "context::categoryPostParam(\$params);\n";
		}
		
		
		$p .=
		'if ($_ctx->exists("categories")) { '.
			"\$params['cat_id'] = \$_ctx->categories->cat_id; ".
		"}\n";
	
		$p .= "\$params['post_type'] ='gal';\n";
		

		if (isset($attr['no_content']) && $attr['no_content']) {
			$p .= "\$params['no_content'] = true;\n";
		}
		
		
		$res = "<?php\n";
		$res .= $p;
		$res .= '$_ctx->post_params = $params;'."\n";
		$res .= '$_ctx->posts = $core->blog->getPosts($params); 		$_ctx->posts->extend("rsExtGallery"); unset($params);'."\n";
		$res .= "?>\n";
		
		$res .=
		'<?php while ($_ctx->posts->fetch()) : ?>'.$content.'<?php endwhile; '.
		'$_ctx->posts = null; $_ctx->post_params = null; ?>';
		
		return $res;
	}

	# Retrieve next gallery
	public static function GalEntryNext($attr,$content)
	{
		return
		'<?php $next_post = $core->galtool->getNextGallery($_ctx->posts->post_id,strtotime($_ctx->posts->post_dt),1); ?>'."\n".
		'<?php if ($next_post !== null) : ?>'.
			
			'<?php $_ctx->posts = $next_post; unset($next_post);'."\n".
			'while ($_ctx->posts->fetch()) : ?>'.
			$content.
			'<?php endwhile; $_ctx->posts = null; ?>'.
		"<?php endif; ?>\n";
	}

	# Retrieve previous gallery
	public static function GalEntryPrevious($attr,$content)
	{
		return
		'<?php $prev_post = $core->galtool->getNextGallery($_ctx->posts->post_id,strtotime($_ctx->posts->post_dt),-1); ?>'."\n".
		'<?php if ($prev_post !== null) : ?>'.
			
			'<?php $_ctx->posts = $prev_post; unset($prev_post);'."\n".
			'while ($_ctx->posts->fetch()) : ?>'.
			$content.
			'<?php endwhile; $_ctx->posts = null; ?>'.
		"<?php endif; ?>\n";
	}

	/* Entries -------------------------------------------- */
	
	# List all items from a gallery
	public static function GalItemEntries($attr,$content)
	{
		$lastn = 0;
		if (isset($attr['lastn'])) {
			$lastn = abs((integer) $attr['lastn'])+0;
		}
		
		$p = 'if (!isset($_page_number)) { $_page_number = 1; }'."\n";
		
		if ($lastn > 0) {
			$p .= "\$params['limit'] = ".$lastn.";\n";
		} else {
/*			$p .= "\$params['limit'] = \$core->blog->settings->nb_post_per_page;\n";*/
			$p .= "\$params['limit'] = 24;\n";
		}
		
		$p .= "\$params['limit'] = array(((\$_page_number-1)*\$params['limit']),\$params['limit']);\n";
		
		if (isset($attr['category'])) {
			$p .= "\$params['cat_url'] = '".addslashes($attr['category'])."';\n";
			$p .= "context::categoryPostParam(\$params);\n";
		}
		
		$p .= "\$params['post_type'] ='gal';\n";
		
		if (isset($attr['no_content']) && $attr['no_content']) {
			$p .= "\$params['no_content'] = true;\n";
		}
		
		$res = "<?php\n";
		$res .= $p;
		$res .= '$_ctx->post_params = $params;'."\n";
		$res .= '$galtool = new dcGallery($core);'."\n";
		$res .= '$_ctx->gal_id = $_ctx->posts->post_id;'."\n";
		
		$res .= '$_ctx->posts = $galtool->getGalImageMedia($params,$_ctx->posts->post_id); unset($params);'."\n";
		$res .= "/*\$_ctx->posts->extend('rsExtImage'); */?>\n";
		
		$res .=
		'<?php while ($_ctx->posts->fetch()) : '."\n".
		' $_ctx->media = $core->media->fileRecord($_ctx->posts);?>'.$content.'<?php endwhile; '.
		'$_ctx->posts = null; $_ctx->post_params = null; ?>';
		
		return $res;
	}

	# Enable paging for galleries items lists
	public static function GalPagination($attr,$content)
	{
		$p = "<?php\n";
		$p .= '$params = $_ctx->post_params;'."\n";
		$p .= '$_ctx->pagination = $galtool->getGalImageMedia($params,$_ctx->gal_id, true);  unset($params);'."\n";
		$p .= "?>\n";
		
		return
		$p.
		'<?php if ($_ctx->pagination->f(0) > $_ctx->posts->count()) : ?>'.
		$content.
		'<?php endif; ?>';
	}

	# Retrieve URL for a given gallery item thumbnail
	# attributes :
	#   * size : gives the size of requested thumb (default : 's')
	#   * bestfit : retrieve standard URL if thumbnail does not exist
	public static function GalItemThumbURL($attr) 
	{
		$size = isset($attr['size']) ? addslashes($attr['size']) : 's';
		$bestfit = isset($attr['bestfit']);
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		if ($bestfit) {
			$append=' else echo '.sprintf($f,'$_ctx->media->file_url').';';
		} else {
			$append='';
		}
                return '<?php '.
                'if (isset($_ctx->media->media_thumb[\''.$size.'\'])) {'.
                        'echo '.sprintf($f,'$_ctx->media->media_thumb[\''.$size.'\']').';'.
                '}'.$append.
                '?>';
	}

	# Retrieve URL for a given gallery item 
	public static function GalItemURL($attr) {
		$f = $GLOBALS['core']->tpl->getFilters($attr);
                return
                '<?php '.
                        'echo '.sprintf($f,'$_ctx->media->file_url').';'.
                '?>';
	}

	# Widget function
	public static function listgalWidget(&$w)
	{
                global $core;


                $title = $w->title ? html::escapeHTML($w->title) : __('Galleries');

                $params = array(
			'post_type'=>'gal',
                        'no_content'=>true,
                        'order'=>'post_dt desc');

                $rs = $core->blog->getPosts($params);

                if ($rs->isEmpty()) {
                        return;
		}
		$rs->extend('rsExtGallery');

                $res =
                '<div id="galleries">'.
                '<h2>'.$title.'</h2>'.
                '<ul>';

                while ($rs->fetch()) {
                        $res .= ' <li><a href="'.$rs->getURL().'">'.html::escapeHTML($rs->post_title).'</a></li> ';
                }

                $res .= '</ul></div>';

                return $res;
	}



}

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
		$GLOBALS['core']->galtool = new dcGallery($GLOBALS['core']);;
		
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
		$galtool = new dcGallery($GLOBALS['core']);
		$GLOBALS['_ctx']->posts = $galtool->getGalImageMedia($params);
		
		$GLOBALS['_ctx']->comment_preview = new ArrayObject();
		$GLOBALS['_ctx']->comment_preview['content'] = '';
		$GLOBALS['_ctx']->comment_preview['rawcontent'] = '';
		$GLOBALS['_ctx']->comment_preview['name'] = '';
		$GLOBALS['_ctx']->comment_preview['mail'] = '';
		$GLOBALS['_ctx']->comment_preview['site'] = '';
		$GLOBALS['_ctx']->comment_preview['preview'] = false;
		$GLOBALS['_ctx']->comment_preview['remember'] = false;
		
		$GLOBALS['core']->blog->withoutPassword(true);
		$GLOBALS['_ctx']->media=$GLOBALS['core']->media->fileRecord($GLOBALS['_ctx']->posts);
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