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

if (!defined('DC_RC_PATH')) {return;}

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
		
		$_ctx =& $GLOBALS['_ctx'];
		
		$_ctx->contribute = new ArrayObject();
		$_ctx->contribute->message = '';
		$_ctx->contribute->preview = false;
		$_ctx->contribute->form = true;
		
		$_ctx->comment_preview = new ArrayObject();
		$_ctx->comment_preview['name'] = __('Anonymous');
		$_ctx->comment_preview['site'] = '';
		
		# inspirated by contactMe/_public.php
		if ($args == 'sent')
		{
			$_ctx->contribute->message = __('The post has been saved.').' '.
				__('It needs to be approved by the administrator to be published.');
			$_ctx->contribute->preview = false;
			$_ctx->contribute->form = false;
		}
		else
		{
			try
			{
				$meta = new dcMeta($core);
				
				$default_post = $core->blog->settings->contribute_default_post;
				if (is_int($default_post) && $default_post > 0)
				{
					# get default post
					$_ctx->posts = $core->auth->sudo(array($core->blog,'getPosts'),
						array('post_id' => $default_post));
					
					if ($_ctx->posts->isEmpty())
					{
						throw new Exception(__('No default post.'));
					}
				}
				else
				{
					# create empty fields from default columns 
					$record = array();
					$empty_post = $core->auth->sudo(array($core->blog,'getPosts'),
						array('post_id' => -1));
					$record[0] = array();
					foreach ($empty_post->columns() as $k => $v)
					{
						$record[0][$v] = '';
					}
					$_ctx->posts = staticRecord::newFromArray($record);
					
					unset($empty_post,$record);
					
					$_ctx->posts->core = $core;
					$_ctx->posts->extend('rsExtPost');
					
					# --BEHAVIOR-- coreBlogGetPosts
					$core->callBehavior('coreBlogGetPosts',$_ctx->posts);
				}
				
				# modify $_ctx->posts for preview
				$post =& $_ctx->posts;
				
				$post->post_dt = dt::str('%Y-%m-%d %T',null,
					$core->blog->settings->blog_timezone);
				$post->post_url = '';
				
				if (isset($_POST['post_title']))
				{
					$post->post_title = $_POST['post_title'];
				}
				
				# excerpt and content
				if (isset($_POST['post_excerpt'])) 
				{
					$post->post_excerpt_xhtml =
						$core->wikiTransform($_POST['post_excerpt']);
					$post->post_excerpt = $_POST['post_excerpt'];
				}
				if (isset($_POST['post_content']))
				{
					$post->post_content_xhtml =
						$core->wikiTransform($_POST['post_content']);
					$post->post_content = $_POST['post_content'];
				}
				
				if (($core->blog->settings->contribute_allow_category === true)
					&& (isset($_POST['cat_id'])))
				{
					$post->cat_id = $_POST['cat_id'];
				}
				
				if (($post->cat_id != '') && (!preg_match('/^[0-9]+$/',$post->cat_id)))
				{
					throw new Exception(__('Invalid cat_id'));
				}
					
				# tags
				# from /dotclear/plugins/metadata/_admin.php
				if (($core->blog->settings->contribute_allow_tags === true)
					&& (isset($_POST['post_tags'])))
				{
					$post_meta = unserialize($_ctx->posts->post_meta);
					
					# remove default tags
					unset($post_meta['tag']);
					
					foreach ($meta->splitMetaValues($_POST['post_tags']) as $k => $tag)
					{
						$post_meta['tag'][] = $tag;
					}
					
					$_ctx->posts->post_meta = serialize($post_meta);
					unset($post_meta);
				}
				# /from /dotclear/plugins/metadata/_admin.php
				
				if (($core->blog->settings->contribute_allow_notes === true)
					&& (isset($_POST['post_notes'])))
				{
					$post->post_notes = $_POST['post_notes'];
				}
				
				if (isset($_POST['c_name']))
				{
					$_ctx->comment_preview['name'] = $_POST['c_name'];
					
					$post_meta = unserialize($_ctx->posts->post_meta);
					
					$post_meta['contribute_author'][] = $_ctx->comment_preview['name'];
					
					$_ctx->posts->post_meta = serialize($post_meta);
					unset($post_meta);
				}
				if (isset($_POST['c_site']))
				{
					$_ctx->comment_preview['site'] = $_POST['c_site'];
					
					$post_meta = unserialize($_ctx->posts->post_meta);
					
					$post_meta['contribute_site'][] = $_ctx->comment_preview['site'];
					
					$_ctx->posts->post_meta = serialize($post_meta);
					unset($post_meta);
				}
				
				# these fields can't be empty
				$post_title = $post->post_title;
				$post_content = $post->post_content;
				
				if (empty($post_title))
				{
					throw new Exception(__('No entry title'));
				} elseif (empty($post_content))
				{
					throw new Exception(__('No entry content'));
				} else {
					$_ctx->contribute->preview = true;
					$_ctx->contribute->message =__('This is a preview.').' '.
						__('Save it when the post is ready to be published.');
				}
				
				if (isset($_POST['add']))
				{
					# log in as the user
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
					$cur->post_excerpt = $post->post_excerpt;
					$cur->post_content = $post->post_content;
					$cur->post_notes = $post->post_notes;
					$cur->post_lang = $core->auth->getInfo('user_lang');
					$cur->post_open_comment = (integer) $core->blog->settings->allow_comments;
					$cur->post_open_tb = (integer) $core->blog->settings->allow_trackbacks;
					
					# --BEHAVIOR-- adminBeforePostCreate
					$core->callBehavior('adminBeforePostCreate',$cur);
					
					$post_id = $core->blog->addPost($cur);
					
					# --BEHAVIOR-- adminAfterPostCreate
					$core->callBehavior('adminAfterPostCreate',$cur,$post_id);
					
					# inspirated by planet/insert_feeds.php
					$meta->setPostMeta($post_id,'contribute_author',
						$_ctx->comment_preview['name']);
					$meta->setPostMeta($post_id,'contribute_site',
						$_ctx->comment_preview['site']);
					
					# from /dotclear/plugins/metadata/_admin.php
					if (isset($_POST['post_tags']))
					{
						$tags = $_POST['post_tags'];
						
						foreach ($meta->splitMetaValues($tags) as $k => $tag)
						{
							$meta->setPostMeta($post_id,'tag',$tag);
						}
					}
					# /from /dotclear/plugins/metadata/_admin.php
					
					if (is_int($post_id))
					{
						$separator = '?';
						if ($core->blog->settings->url_scan == 'query_string')
						{$separator = '&';}
						
						# send email notification
						if ($core->blog->settings->contribute_email_notification)
						{
							$headers = array(
								'From: '.'dotclear@'.$_SERVER['HTTP_HOST'],
								'MIME-Version: 1.0',
								'Content-Type: text/plain; charset=UTF-8;',
								'X-Mailer: Dotclear'
							);
							
							$subject = sprintf(__('New post submitted on %s'),
								$core->blog->name);
							
							$content = sprintf(__('Title : %s'),$post->post_title);
							$content .= "\n\n";
							$content .= sprintf(__('Author : %s'),
								$_ctx->comment_preview['name']);
							$content .= "\n\n";
							$content .= DC_ADMIN_URL.
								((substr(DC_ADMIN_URL,-1) == '/') ? '' : '/').
								'post.php?id='.$post_id.'&switchblog='.$core->blog->id;
							
							foreach(explode(',',
								$core->blog->settings->contribute_email_notification)
								as $to)
							{
								$to = trim($to);
								if (text::isEmail($to))
								{
									# don't display errors
									//try {
										# from /dotclear/admin/auth.php : mail::B64Header($subject)
										mail::sendMail($to,mail::B64Header($subject),
											wordwrap($content,70),$headers);
									//} catch (Exception $e)
									//{
									//}
								}
							}
						}
						
						http::redirect($core->blog->url.
							$core->url->getBase('contribute').'/sent');
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
$core->tpl->addValue('ContributeMessage',
	array('contributeTpl','ContributeMessage'));

$core->tpl->addBlock('ContributePreview',
	array('contributeTpl','ContributePreview'));
$core->tpl->addBlock('ContributeForm',
	array('contributeTpl','ContributeForm'));

$core->tpl->addBlock('ContributeIf',array('contributeTpl','ContributeIf'));

$core->tpl->addValue('ContributeEntryExcerpt',
	array('contributeTpl','ContributeEntryExcerpt'));
$core->tpl->addValue('ContributeEntryContent',
	array('contributeTpl','ContributeEntryContent'));

$core->tpl->addBlock('ContributeSelectedCategory',
	array('contributeTpl','ContributeSelectedCategory'));

$core->tpl->addValue('ContributeCategoryID',
	array('contributeTpl','ContributeCategoryID'));

$core->tpl->addValue('ContributeEntryNotes',
	array('contributeTpl','ContributeEntryNotes'));

/**
@ingroup Contribute
@brief Template
*/
class contributeTpl
{
	/**
	display a message
	@return	<b>string</b> PHP block
	*/
	public static function ContributeMessage()
	{
		return('<?php echo($_ctx->contribute->message); ?>');
	}
	
	/**
	display preview
	@param	attr	<b>array</b>	Attribute
	@param	content	<b>string</b>	Content
	@return	<b>string</b> PHP block
	*/
	public static function ContributePreview($attr,$content)
	{
		return
		'<?php if ($_ctx->contribute->preview === true) : ?>'."\n".
		$content."\n".
		'<?php endif; ?>';
	}
	
	/**
	display form
	@param	attr	<b>array</b>	Attribute
	@param	content	<b>string</b>	Content
	@return	<b>string</b> PHP block
	*/
	public static function ContributeForm($attr,$content)
	{
		return
		'<?php if ($_ctx->contribute->form === true) : ?>'."\n".
		$content."\n".
		'<?php endif; ?>';
	}
	
	/**
	if
	@param	attr	<b>array</b>	Attribute
	@param	content	<b>string</b>	Content
	@return	<b>string</b> PHP block
	
	we can't use ContributeIf in another ContributeIf block
	
	<tpl:ContributeIf something="1">
		<tpl:ContributeIf something_again="1">
		</tpl:ContributeIf>
	</tpl:ContributeIf>
	
	will return :
	
	<?php if () : ?>
		<tpl:ContributeIf something_again="1">
		<?php endif; ?>>
	</tpl:ContributeIf>
	*/
	public static function ContributeIf($attr,$content)
	{
		$if = array();
		$operator = '&&';
		
		if (isset($attr['message']))
		{
			$if[] = '$_ctx->contribute->message != \'\'';
		}
		
		if (isset($attr['category']))
		{
			$if[] = '$core->blog->settings->contribute_allow_category === true';
		}
		
		if (isset($attr['tags']))
		{
			$if[] = '$core->blog->settings->contribute_allow_tags === true';
		}
		
		if (isset($attr['notes']))
		{
			$if[] = '$core->blog->settings->contribute_allow_notes === true';
		}
		
		if (isset($attr['author']))
		{
			$if[] = '$core->blog->settings->contribute_allow_author === true';
		}
		
		if (!empty($if)) {
			return '<?php if('.implode(' '.$operator.' ',$if).') : ?>'.
				$content."\n".
				'<?php endif; ?>';
		} else {
			return $content;
		}
	}
	
	public static function ContributeEntryExcerpt($attr)
	{		
		return('<?php echo(html::escapeHTML($_ctx->posts->post_excerpt)); ?>');
	}
	
	public static function ContributeEntryContent($attr)
	{		
		return('<?php echo(html::escapeHTML($_ctx->posts->post_content)); ?>');
	}
	
	/**
	if the category is selected
	@param	attr	<b>array</b>	Attribute
	@param	content	<b>string</b>	Content
	@return	<b>string</b> PHP block
	*/
	public static function ContributeSelectedCategory($attr,$content)
	{
		return
		'<?php if ($_ctx->categories->cat_id == $_ctx->posts->cat_id) : ?>'."\n".
		$content."\n".
		'<?php endif; ?>';
	}
	
	public static function ContributeCategoryID($attr)
	{		
		return('<?php echo($_ctx->categories->cat_id); ?>');
	}
	
	public static function ContributeEntryNotes($attr)
	{		
		return('<?php echo($_ctx->posts->post_notes); ?>');
	}
}

$core->addBehavior('coreBlogGetPosts',array('contributeBehaviors',
	'coreBlogGetPosts'));

/**
@ingroup Contribute
@brief Behaviors
@see planet/insert_feeds.php
*/
class contributeBehaviors
{
	public static function coreBlogGetPosts(&$rs)
	{
		if (!$GLOBALS['core']->blog->settings->contribute_active) {return;}
		$rs->extend('rsExtContributePosts');
	}
}

class rsExtContributePosts extends rsExtPost
{
	public static function contributeInfo(&$rs,$info)
	{
		$rs = dcMeta::getMetaRecord($rs->core,$rs->post_meta,'contribute_'.$info);
		if (!$rs->isEmpty())
		{
			return $rs->meta_id;
		}
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
			
			$author_format = $GLOBALS['core']->blog->settings->contribute_author_format;
			
			if (empty($author_format)) {$author_format = '%s';}
			
			if (!empty($site))
			{
				$str = sprintf($author_format,'<a href="'.$site.'">'.$str.'</a>');
			}
			else
			{
				$str = sprintf($author_format,$str);
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
			$author_format = $GLOBALS['core']->blog->settings->contribute_author_format;
			
			if (empty($author_format)) {$author_format = '%s';}
			
			return sprintf($author_format,$str);
		}
	}
}
?>