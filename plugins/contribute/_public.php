<?php 
# ***** BEGIN LICENSE BLOCK *****
#
# This file is part of Contribute.
# Copyright 2008 Moe (http://gniark.net/)
#
# Contribute is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 3 of the License, or
# (at your option) any later version.
#
# Contribute is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program.  If not, see <http://www.gnu.org/licenses/>.
#
# ***** END LICENSE BLOCK *****

if (!defined('DC_RC_PATH')) { return; }

$core->addBehavior('coreBlogGetPosts',array('contributeBehaviors','coreBlogGetPosts'));

/**
@ingroup Contribute
@brief Document
*/
class contributeDocument extends dcUrlHandlers
{
	/**
	serve the document
	@param	args	<b>string</b>	Argument
	*/
	public static function page($args)
	{
		global $core;

		if (!$core->blog->settings->contribute_active) {self::p404();}

		# start session
		$session_id = session_id();
		if (empty($session_id)) {session_start();}
		
		$_ctx =& $GLOBALS['_ctx'];
		
		$_ctx->contribute = new ArrayObject();
		$_ctx->contribute->message = '';
		$_ctx->contribute->preview = false;
		$_ctx->contribute->form = true;
		
		$_ctx->comment_preview = new ArrayObject();
		$_ctx->comment_preview['name'] = __('Anonymous');
		$_ctx->comment_preview['site'] = '';
		
		if ((isset($_GET['message'])) && ($_GET['message'] == 'ok'))
		{
			$_ctx->contribute->message = __('The post has been saved.').' '.
				__('It needs to be approved by the administrator to appear on the blog.');
			$_ctx->contribute->preview = false;
			$_ctx->contribute->form = false;
		}
		else
		{
			try
			{
				# get default post
				$_ctx->posts = $core->auth->sudo(array($core->blog,'getPosts'),
					array('post_id' => $core->blog->settings->contribute_default_post));
				
				# modify $_ctx->posts for preview
				$post =& $_ctx->posts;
				
				$post->post_excerpt_wiki = $post->post_excerpt;
				$post->post_content_wiki = $post->post_content;
				$post->post_dt = dt::str('%Y-%m-%d %T',null,
					$core->blog->settings->blog_timezone);
				
				if (isset($_POST['post_title']))
				{
					$post->post_title = $_POST['post_title'];
				}
				if (isset($_POST['cat_id']))
				{
					$post->cat_id = $_POST['cat_id'];
				}
				
				# excerpt and content
				if (isset($_POST['post_excerpt'])) 
				{
					$post->post_excerpt_xhtml =
						$core->wikiTransform($_POST['post_excerpt']);
				}
				if (isset($_POST['post_excerpt']))
				{
					$post->post_excerpt_wiki = $_POST['post_excerpt'];
				}
				if (isset($_POST['post_content']))
				{
					$post->post_content_xhtml =
						$core->wikiTransform($_POST['post_content']);
				}
				if (isset($_POST['post_content']))
				{
					$post->post_content_wiki = $_POST['post_content'];
				}
				
				if (($post->cat_id != '') && (!preg_match('/^[0-9]+$/',$post->cat_id)))
				{
					$_ctx->form_error = __('Invalid cat_id');
				}
				
				$post_title = $post->post_title;
				$post_content = $post->post_content;
				if (empty($post_title))
				{
					$_ctx->form_error = __('No entry title');
				} elseif (empty($post_content))
				{
					$_ctx->form_error = __('No entry content');
				} else {
					$_ctx->contribute->preview = true;
					$_ctx->contribute->message =__('This is a preview.').
						__(' Save it when the post is ready to be published.');
				}
				
				if (isset($_POST['c_name']))
				{
					$_ctx->comment_preview['name'] = $post->user_displayname = $_POST['c_name'];
					
				}
				if (isset($_POST['c_site']))
				{
					$_ctx->comment_preview['site'] = $post->user_url = $_POST['c_site'];
				}
				
				if (isset($_POST['add']))
				{
					$core->auth->checkUser($core->blog->settings->contribute_user);
					
					$cur = $core->con->openCursor($core->prefix.'post');
					
					$cur->user_id = $core->auth->userID();
					$cur->cat_id = $post->cat_id;
					if (empty($post->cat_id))
					{
						$cur->cat_id = NULL;
					}
					$cur->post_dt = $post->post_dt;
					$cur->post_format = 'wiki';
					$cur->post_status = -2;
					$cur->post_title = $post->post_title;
					$cur->post_excerpt = $post->post_excerpt_wiki;
					$cur->post_content = $post->post_content_wiki;
					$cur->post_lang = $core->auth->getInfo('user_lang');
					$cur->post_open_comment = (integer) $core->blog->settings->allow_comments;
					$cur->post_open_tb = (integer) $core->blog->settings->allow_trackbacks;
					
					# --BEHAVIOR-- adminBeforePostCreate
					$core->callBehavior('adminBeforePostCreate',$cur);
					
					$post_id = $core->blog->addPost($cur);
					
					# --BEHAVIOR-- adminAfterPostCreate
					$core->callBehavior('adminAfterPostCreate',$cur,$post_id);
					
					# inspirated by planet/insert_feeds.php
					$meta = new dcMeta($core);
					
					$meta->setPostMeta($post_id,'contribute_author',
						$_ctx->comment_preview['name']);
					$meta->setPostMeta($post_id,'contribute_site',
						$_ctx->comment_preview['site']);
					
					if (is_int($post_id))
					{
						$separator = '?';
						if ($core->blog->settings->url_scan == 'query_string')
						{$separator = '&';}
						
						http::redirect($core->blog->url.$core->url->getBase('contribute').
							$separator.'message=ok');
					}
				}
			}
			catch (Exception $e)
			{
				$_ctx->form_error = $e->getMessage();
			}
		}

		$core->tpl->setPath($core->tpl->getPath(),
			dirname(__FILE__).'/default-templates/');

		self::serveDocument('contribute.html','text/html');
	}
}

# message
$core->tpl->addBlock('ContributeIfMessage',array('contributeTpl','ifMessage'));
$core->tpl->addValue('ContributeMessage',array('contributeTpl','message'));

$core->tpl->addBlock('ContributePreview',array('contributeTpl','preview'));
$core->tpl->addBlock('ContributeForm',array('contributeTpl','form'));
$core->tpl->addBlock('ContributeSelectedCategory',array('contributeTpl','selectedCategory'));

$core->tpl->addValue('ContributeEntryExcerptWiki',array('contributeTpl','entryExcerptWiki'));
$core->tpl->addValue('ContributeEntryContentWiki',array('contributeTpl','entryContentWiki'));

$core->tpl->addValue('CategoryID',array('contributeTpl','categoryID'));

/**
@ingroup Contribute
@brief Template
*/
class contributeTpl
{
	/**
	if there is a message
	@param	attr	<b>array</b>	Attribute
	@param	content	<b>string</b>	Content
	@return	<b>string</b> PHP block
	*/
	public static function ifMessage($attr,$content)
	{
		return
		"<?php if (\$_ctx->contribute->message != '') : ?>"."\n".
		$content.
		"<?php endif; ?>";
	}

	/**
	display a message
	@return	<b>string</b> PHP block
	*/
	public static function message()
	{
		return("<?php if (\$_ctx->contribute->message != '') :"."\n".
		"echo(\$_ctx->contribute->message);".
		"endif; ?>");
	}
	
	/**
	display preview
	@param	attr	<b>array</b>	Attribute
	@param	content	<b>string</b>	Content
	@return	<b>string</b> PHP block
	*/
	public static function preview($attr,$content)
	{
		return
		'<?php if ($_ctx->contribute->preview === true) : ?>'."\n".
		$content.
		'<?php endif; ?>';
	}
	
	/**
	display form
	@param	attr	<b>array</b>	Attribute
	@param	content	<b>string</b>	Content
	@return	<b>string</b> PHP block
	*/
	public static function form($attr,$content)
	{
		return
		'<?php if ($_ctx->contribute->form === true) : ?>'."\n".
		$content.
		'<?php endif; ?>';
	}
	
	/**
	if the category is selected
	@param	attr	<b>array</b>	Attribute
	@param	content	<b>string</b>	Content
	@return	<b>string</b> PHP block
	*/
	public static function selectedCategory($attr,$content)
	{
		return
		'<?php if ($_ctx->categories->cat_id == $_ctx->posts->cat_id) : ?>'."\n".
		$content.
		'<?php endif; ?>';
	}
	
	public static function entryExcerptWiki($attr)
	{		
		return('<?php echo($_ctx->posts->post_excerpt_wiki); ?>');
	}
	
	public static function entryContentWiki($attr)
	{		
		return('<?php echo($_ctx->posts->post_content_wiki); ?>');
	}
	
	public static function categoryID($attr)
	{		
		return('<?php echo($_ctx->categories->cat_id); ?>');
	}
}

/**
@ingroup Contribute
@brief Widget
*/
class contributeWidget
{
	/**
	show widget
	@param	w	<b>object</b>	Widget
	@return	<b>string</b> XHTML
	*/
	public static function show(&$w)
	{
		global $core;

		if ($w->homeonly && $core->url->type != 'default') {
			return;
		}
		
		# output
		$header = (strlen($w->title) > 0)
			? '<h2>'.html::escapeHTML($w->title).'</h2>' : null;
		$text = (strlen($w->text) > 0)
			? '<p class="text"><a href="'.$core->blog->url.$core->url->getBase('contribute').
				'">'.html::escapeHTML($w->text).'</a></p>' : null;

		return '<div class="dlmanager">'.$header.$text.'</div>';
	}
}

/**
@ingroup Contribute
@brief Behaviors
@see planet/insert_feeds.php
*/
class contributeBehaviors
{
	public static function coreBlogGetPosts(&$rs)
	{
		$rs->extend('rsExtContributePosts');
	}
}

class rsExtContributePosts extends rsExtPost
{
	public static function contributeInfo(&$rs,$info)
	{
		return dcMeta::getMetaRecord($rs->core,$rs->post_meta,'contribute_'.$info)->meta_id;
	}
	
	public static function getAuthorLink(&$rs)
	{
		$author = $rs->contributeInfo('author');
		$site = $rs->contributeInfo('site');
		
		# default display
		if (empty($author))
		{
			return(parent::getAuthorLink($rs));
		}
		else
		{
			$str = $author;
			if (!empty($site))
			{
				$str = '<a href="'.$site.'">'.$str.'</a> ('.__('contributor').')';
			}
			return $str;
		}
	}
	
	public static function getAuthorCN(&$rs)
	{
		$author = $rs->contributeInfo('author');
		if (empty($author))
		{
			# default display
			return(parent::getAuthorCN($rs));
		} else {
			return $author;
		}
	}
}
?>
