<?php 
# ***** BEGIN LICENSE BLOCK *****
#
# This file is part of Contribute.
# Copyright 2008,2009 Moe (http://gniark.net/)
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
# Icon (icon.png) is from Silk Icons : http://www.famfamfam.com/lab/icons/silk/
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
		
		$settings =& $core->blog->settings;

		if (!$settings->contribute_active) {self::p404();}
		
		$_ctx =& $GLOBALS['_ctx'];
		
		$_ctx->contribute = new ArrayObject();
		$_ctx->contribute->message = '';
		$_ctx->contribute->preview = false;
		$_ctx->contribute->form = true;
		$_ctx->contribute->choose_format = false;
		# selected tags
		$_ctx->contribute->selected_tags = array();
		
		# Metadata
		if ($core->plugins->moduleExists('metadata'))
		{
			$meta = new dcMeta($core);
		}
		else
		{
			$meta = false;
		}
		
		# My Meta
		if ($core->plugins->moduleExists('mymeta'))
		{
			$mymeta_values = array();
		
			$_ctx->contribute->mymeta = new myMeta($core);
			
			if (($_ctx->contribute->mymeta->hasMeta())
				&& ($settings->contribute_allow_mymeta === true))
			{
				$mymeta_values = @unserialize(@base64_decode(
					$settings->contribute_mymeta_values));
				
				if (!is_array($mymeta_values)) {$mymeta_values = array();}
				
				$mymeta = array();
				
				foreach ($_ctx->contribute->mymeta->getAll() as $k => $v)
				{
					if (((bool) $v->enabled) && in_array($k,$mymeta_values))
					{
						$mymeta[] = $k;
					}
				}
				
				unset($mymeta_values);
			}
			else
			{
				$_ctx->contribute->mymeta = false;
			}
		}
		else
		{
			$_ctx->contribute->mymeta = false;
		}
		# /My Meta
		
		$_ctx->comment_preview = new ArrayObject();
		$_ctx->comment_preview['name'] = __('Anonymous');
		$_ctx->comment_preview['mail'] = '';
		$_ctx->comment_preview['site'] = '';
		
		# inspirated by contactMe/_public.php
		if ($args == 'sent')
		{
			$_ctx->contribute->message = 'sent';
			$_ctx->contribute->preview = false;
			$_ctx->contribute->form = false;
			# avoid error with <tpl:ContributeIf format="xhtml">
			$_ctx->posts = new ArrayObject();
			$_ctx->posts->post_format = '';
		}
		else
		{
			try
			{
				# default post
				$default_post = $settings->contribute_default_post;
				if (is_int($default_post) && ($default_post > 0))
				{
					# get default post
					$_ctx->posts = $core->auth->sudo(array($core->blog,'getPosts'),
						array('post_id' => $default_post));
					
					if ($_ctx->posts->isEmpty())
					{
						throw new Exception(__('No default post.'));
					}
					
					# modify $_ctx->posts for preview
					$post =& $_ctx->posts;
					
					# tags
					# remove selected tags
					$post_meta = unserialize($_ctx->posts->post_meta);
					
					if (isset($post_meta['tag']) && !empty($post_meta['tag']))
					{
						foreach ($post_meta['tag'] as $k => $tag)
						{
								$_ctx->contribute->selected_tags[] = $tag;
						}
					}
					
					# My Meta
					$post->mymeta = array();
					
					if ($_ctx->contribute->mymeta !== false)
					{
						foreach ($mymeta as $k => $v)
						{
							$post->mymeta[$v] = $meta->getMetaStr($post->post_meta,$v);
						}
					}
					# /My Meta
				}
				# empty post
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
					
					# modify $_ctx->posts for preview
					$post =& $_ctx->posts;
					
					# My Meta
					$post->mymeta = array();
					# /My Meta
					
					# formats
					# default format setting
					$post->post_format = $settings->contribute_format;
					
					# contributor can choose the post format, 
					# it overrides the default format
					if ($settings->contribute_format == '')
					{
						$_ctx->contribute->choose_format = true;
						
						if ((isset($_POST['post_format']))
							&& in_array($_POST['post_format'],$core->getFormaters()))
						{
							$post->post_format = $_POST['post_format'];
						}
					}
				}
				
				# from /dotclear/inc/public/lib.urlhandlers.php
				# Spam trap
				if (!empty($_POST['f_mail'])) {
					http::head(412,'Precondition Failed');
					header('Content-Type: text/plain');
					echo "So Long, and Thanks For All the Fish";
					exit;
				}
				# /from /dotclear/inc/public/lib.urlhandlers.php
				
				$formaters_combo = array();
				# Formaters combo
				foreach ($core->getFormaters() as $v) {
					$formaters_combo[] = array('format' => $v);
				}
				$_ctx->contribute->formaters =
						staticRecord::newFromArray($formaters_combo);
						
				unset($formaters_combo);
				
				# current date
				$post->post_dt = dt::str('%Y-%m-%d %T',null,
					$settings->blog_timezone);
				$post->post_url = '';
				
				if (isset($_POST['post_title']))
				{
					$post->post_title = $_POST['post_title'];
				}
				
				# HTML filter
				# get the setting value
				$enable_html_filter = $settings->enable_html_filter;
				# set the setting to true
				$settings->enable_html_filter = true;
				# excerpt
				if (($settings->contribute_allow_excerpt === true)
					&& (isset($_POST['post_excerpt'])))
				{
					$post->post_excerpt = $core->HTMLfilter($_POST['post_excerpt']);
				}
				# content
				if (isset($_POST['post_content']))
				{
					$post->post_content = $core->HTMLfilter($_POST['post_content']);
				}
				# set the old value to the setting
				$settings->enable_html_filter = $enable_html_filter;
				unset($enable_html_filter);
				
				# avoid Notice: Indirect modification of overloaded property
				# record::$post_excerpt has no effect in .../contribute/_public.php
				# on line 146
				$post_excerpt = $post->post_excerpt;
				$post_excerpt_xhtml = $post->post_excerpt_xhtml;
				$post_content = $post->post_content;
				$post_content_xhtml = $post->post_content_xhtml;
				
				$core->blog->setPostContent(
					'',$post->post_format,$settings->lang,
					$post_excerpt,$post_excerpt_xhtml,
					$post_content,$post_content_xhtml
				);
				
				$post->post_excerpt = $post_excerpt;
				$post->post_excerpt_xhtml = $post_excerpt_xhtml;
				$post->post_content = $post_content;
				$post->post_content_xhtml = $post_content_xhtml;
				
				unset($post_excerpt,$post_excerpt_xhtml,$post_content,
					$post_content_xhtml);
				
				if ($_ctx->contribute->choose_format
					&& (isset($_POST['convert-xhtml']))
					&& ($post->post_format != 'xhtml'))
				{
					$post->post_excerpt = $post->post_excerpt_xhtml;
					$post->post_content = $post->post_content_xhtml;
					$post->post_format = 'xhtml';
				}
				
				$_ctx->formaters = new ArrayObject;
				$_ctx->formaters->format = $post->post_format;
				
				# category
				if (($settings->contribute_allow_category === true)
					&& (isset($_POST['cat_id'])))
				{
					# check category
					if (($_POST['cat_id'] != '')
						&& (!preg_match('/^[0-9]+$/',$_POST['cat_id'])))
					{
						throw new Exception(__('Invalid cat_id'));
					}
					
					$cat = $core->blog->getCategories(array(
						'start' => $_POST['cat_id'],
						'level' => 1,
						'cat_id' => $_POST['cat_id']
					));
					
					while ($cat->fetch())
					{
						$post->cat_id = $cat->cat_id;
						$post->cat_title = $cat->cat_title;
						$post->cat_url = $cat->cat_url;
						break;
					}
					
					unset($cat);
				}
				# /category
				
				# tags
				# from /dotclear/plugins/metadata/_admin.php
				if ($meta !== false)
				{
					if (($settings->contribute_allow_tags === true)
					&& (isset($_POST['post_tags'])))
					{
						$post_meta = unserialize($_ctx->posts->post_meta);
						
						# remove default tags
						unset($post_meta['tag']);
						
						if ($settings->contribute_allow_new_tags === true)
						{
							foreach ($meta->splitMetaValues($_POST['post_tags']) as $k => $tag)
							{
								$tag = dcMeta::sanitizeMetaID($tag);
								
								$post_meta['tag'][] = $tag;
								$_ctx->contribute->selected_tags[] = $tag;
							}
						}
						else
						{
							# check that this tag already exists
							# get all the existing tags
							# $meta->getMeta('tag') break the login when adding a post,
							# we avoid it
							$available_tags = contribute::getTags();
							
							foreach ($meta->splitMetaValues($_POST['post_tags'])
								as $k => $tag)
							{
								$tag = dcMeta::sanitizeMetaID($tag);
								
								# insert it if the tag already exists
								if (in_array($tag,$available_tags))
								{
									$post_meta['tag'][] = $tag;
									$_ctx->contribute->selected_tags[] = $tag;
								}
							}
						}
						
						$_ctx->posts->post_meta = serialize($post_meta);
						unset($post_meta);
						# /from /dotclear/plugins/metadata/_admin.php
					}
					
					# My Meta
					if ($_ctx->contribute->mymeta !== false)
					{
						foreach ($mymeta as $k => $v)
						{
							$post->mymeta[$v] = (isset($_POST['mymeta_'.$v])
								? $_POST['mymeta_'.$v] : '');
						}
					}
					# /My Meta
				}
				
				# notes
				if (($settings->contribute_allow_notes === true)
					&& (isset($_POST['post_notes'])))
				{
					$post->post_notes = $_POST['post_notes'];
				}
				
				# author
				if (($meta !== false)
					&& ($settings->contribute_allow_author === true))
				{
					$post_meta = unserialize($_ctx->posts->post_meta);
					
					if (isset($_POST['c_name']))
					{
						$post_meta['contribute_author'][] =
							$_ctx->comment_preview['name'] = $_POST['c_name'];
					}
					if (isset($_POST['c_mail']))
					{
						$post_meta['contribute_mail'][] =
							$_ctx->comment_preview['mail'] = $_POST['c_mail'];
					}
					if (isset($_POST['c_site']))
					{
						$post_meta['contribute_site'][] =
							$_ctx->comment_preview['site'] = $_POST['c_site'];
					}
					
					$_ctx->posts->post_meta = serialize($post_meta);
					unset($post_meta);
				}
				
				# these fields can't be empty
				$post_title = $post->post_title;
				$post_content = $post->post_content;
				
				if (isset($_POST['post_content']) && empty($post_content))
				{
					throw new Exception(__('No entry content'));
				} elseif (isset($_POST['post_title']) && empty($post_title))
				{
					throw new Exception(__('No entry title'));
				} else {
					if (isset($_POST['preview']))
					{ 
						$_ctx->contribute->preview = true;
						$_ctx->contribute->message = 'preview';
					}
				}
				
				unset($post_title,$post_content);
				
				if ($settings->contribute_require_name_email)
				{
					if (empty($_ctx->comment_preview['name']))
					{
						$_ctx->contribute->preview = false;
						$_ctx->contribute->message = '';
						throw new Exception(__('You must provide an author name'));
					} elseif (!text::isEmail($_ctx->comment_preview['mail']))
					{
						$_ctx->contribute->preview = false;
						$_ctx->contribute->message = '';
						throw new Exception(
							__('You must provide a valid email address.'));
					}
				}
				
				if (isset($_POST) && empty($_POST))
				{
					$_ctx->contribute->preview = false;
					$_ctx->contribute->message = '';
				}
				
				if (isset($_POST['add']))
				{
					# log in as the user
					# usage OR contentadmin permission is needed
					$core->auth->checkUser($settings->contribute_user);
					
					if (!$core->auth->check('usage,contentadmin',$core->blog->id))
					{
						throw new Exception(
							__('The user is not allowed to create an entry'));
					}
					
					$cur = $core->con->openCursor($core->prefix.'post');
					
					$cur->user_id = $core->auth->userID();
					$cur->cat_id = ((empty($post->cat_id)) ? NULL : $post->cat_id);
					$cur->post_dt = $post->post_dt;
					$cur->post_status = -2;
					$cur->post_title = $post->post_title;
					$cur->post_format = $post->post_format;
					$cur->post_excerpt = $post->post_excerpt;
					$cur->post_content = $post->post_content;
					$cur->post_notes = $post->post_notes;
					$cur->post_lang = $core->auth->getInfo('user_lang');
					$cur->post_open_comment = (integer) $settings->allow_comments;
					$cur->post_open_tb = (integer) $settings->allow_trackbacks;
					
					# --BEHAVIOR-- publicBeforePostCreate
					$core->callBehavior('publicBeforePostCreate',$cur);
					
					$post_id = $core->blog->addPost($cur);
					
					# l10n
					__('You are not allowed to create an entry');
					
					# --BEHAVIOR-- publicAfterPostCreate
					$core->callBehavior('publicAfterPostCreate',$cur,$post_id);
					
					if (is_int($post_id))
					{
						if ($meta !== false)
						{
							# inspirated by planet/insert_feeds.php
							$meta->setPostMeta($post_id,'contribute_author',
								$_ctx->comment_preview['name']);
							$meta->setPostMeta($post_id,'contribute_mail',
								$_ctx->comment_preview['mail']);
							$meta->setPostMeta($post_id,'contribute_site',
								$_ctx->comment_preview['site']);
							
							if (isset($_POST['post_tags']))
							{
								if ($settings->contribute_allow_new_tags === true)
								{
									foreach ($meta->splitMetaValues($_POST['post_tags']) as $k => $tag)
									{
										$tag = dcMeta::sanitizeMetaID($tag);
										
										$meta->setPostMeta($post_id,'tag',$tag);
									}
								}
								else
								{
									# check that this tag already exists
									foreach ($meta->splitMetaValues($_POST['post_tags'])
										as $k => $tag)
									{
										$tag = dcMeta::sanitizeMetaID($tag);
										
										# insert it if the tag already exists
										if (in_array($tag,$available_tags))
										{
											$meta->setPostMeta($post_id,'tag',$tag);
										}
									}
								}
							}
							unset($available_tags);
							# /from /dotclear/plugins/metadata/_admin.php
							
							# My Meta
							if ($_ctx->contribute->mymeta !== false)
							{							
								foreach ($post->mymeta as $k => $v)
								{
									$meta->setPostMeta($post_id,$k,$v);
								}
							}
							# /My Meta
						}
						
						# send email notification
						if ($settings->contribute_email_notification)
						{
							$headers = array(
								'From: '.'dotclear@'.$_SERVER['HTTP_HOST'],
								'MIME-Version: 1.0',
								'Content-Type: text/plain; charset=UTF-8;',
								'X-Mailer: Dotclear',
								'Reply-To: '.$_ctx->comment_preview['mail']
							);
							
							$subject = sprintf(__('New post submitted on %s'),
								$core->blog->name);
							
							$content = sprintf(__('Title : %s'),$post->post_title);
							$content .= "\n\n";
							
							if ($settings->contribute_allow_author === true)
							{
								if (!empty($_ctx->comment_preview['name']))
								{
									$content .= sprintf(__('Author : %s'),
										$_ctx->comment_preview['name']);
										$content .= "\n\n";
								}
								
								if (!empty($_ctx->comment_preview['mail']))
								{
									$content .= sprintf(__('Email address : %s'),
										$_ctx->comment_preview['mail']);
										$content .= "\n\n";
								}
							}
							
							$params = array();
							$params['post_id'] = $post_id;
							
							$post = $core->blog->getPosts($params);
	
							$content .= __('URL:').' '.$post->getURL();
							unset($post);
							$content .= "\n\n";
								
							$content .= __('Edit this entry:').' '.DC_ADMIN_URL.
								((substr(DC_ADMIN_URL,-1) == '/') ? '' : '/').
								'post.php?id='.$post_id.'&switchblog='.$core->blog->id;
							$content .= "\n\n".
								__('You must log in on the backend before clicking on this link to go directly to the post.');
							
							foreach(explode(',',
								$settings->contribute_email_notification)
								as $to)
							{
								$to = trim($to);
								if (text::isEmail($to))
								{
									# don't display errors
									try {
										# from /dotclear/admin/auth.php : mail::B64Header($subject)
										mail::sendMail($to,mail::B64Header($subject),
											wordwrap($content,70),$headers);
									} catch (Exception $e) {}
								}
							}
						}
						# /send email notification
						
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

$core->tpl->addBlock('ContributeIf',
	array('contributeTpl','ContributeIf'));

$core->tpl->addBlock('ContributeFormaters',
	array('contributeTpl','ContributeFormaters'));

$core->tpl->addValue('ContributeFormat',
	array('contributeTpl','ContributeFormat'));

$core->tpl->addValue('ContributeEntryExcerpt',
	array('contributeTpl','ContributeEntryExcerpt'));
$core->tpl->addValue('ContributeEntryContent',
	array('contributeTpl','ContributeEntryContent'));

$core->tpl->addBlock('ContributeIfSelected',
	array('contributeTpl','ContributeIfSelected'));

$core->tpl->addValue('ContributeCategoryID',
	array('contributeTpl','ContributeCategoryID'));

$core->tpl->addValue('ContributeCategorySpacer',
	array('contributeTpl','ContributeCategorySpacer'));

$core->tpl->addBlock('ContributeEntryTagsFilter',
	array('contributeTpl','ContributeEntryTagsFilter'));

$core->tpl->addBlock('ContributeEntryMyMeta',
	array('contributeTpl','ContributeEntryMyMeta'));

$core->tpl->addBlock('ContributeEntryMyMetaIf',
	array('contributeTpl','ContributeEntryMyMetaIf'));

$core->tpl->addValue('ContributeEntryMyMetaValue',
	array('contributeTpl','ContributeEntryMyMetaValue'));

$core->tpl->addBlock('ContributeEntryMyMetaValues',
	array('contributeTpl','ContributeEntryMyMetaValues'));
$core->tpl->addValue('ContributeEntryMyMetaValuesID',
	array('contributeTpl','ContributeEntryMyMetaValuesID'));
$core->tpl->addValue('ContributeEntryMyMetaValuesDescription',
	array('contributeTpl','ContributeEntryMyMetaValuesDescription'));
	
$core->tpl->addValue('ContributeEntryMyMetaID',
	array('contributeTpl','ContributeEntryMyMetaID'));
$core->tpl->addValue('ContributeEntryMyMetaPrompt',
	array('contributeTpl','ContributeEntryMyMetaPrompt'));
	
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
	
	we can't use <tpl:ContributeIf> in another <tpl:ContributeIf> block yet
	
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
		$operator = isset($attr['operator']) ? self::getOperator($attr['operator']) : '&&';
		
		if (isset($attr['message']))
		{
			$if[] = '$_ctx->contribute->message == \''.$attr['message'].'\'';
		}
		
		if (isset($attr['choose_format']))
		{
			if ($attr['choose_format'] == '1')
			{
				$if[] = '$_ctx->contribute->choose_format === true';
			}
			else
			{
				$if[] = '$_ctx->contribute->choose_format !== true';
			}
		}
		
		if (isset($attr['format']))
		{
			$format = trim($attr['format']);
			$sign = '=';
			if (substr($format,0,1) == '!')
			{
				$sign = '!';
				$format = substr($format,1);
			}
			foreach (explode(',',$format) as $format)
			{
				$if[] = '$_ctx->posts->post_format '.$sign.'= "'.$format.'"';
			}
		}
		
		if (isset($attr['excerpt']))
		{
			$if[] = '$core->blog->settings->contribute_allow_excerpt === true';
		}
		
		if (isset($attr['category']))
		{
			$if[] = '$core->blog->settings->contribute_allow_category === true';
		}
		
		if (isset($attr['tags']))
		{
			$if[] = '$core->blog->settings->contribute_allow_tags === true';
		}
		
		if (isset($attr['mymeta']))
		{
			$if[] = '$core->blog->settings->contribute_allow_mymeta === true';
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
	
	/**
	Get operator
	@param	op	<b>string</b>	Operator
	@return	<b>string</b> Operator
	\see /dotclear/inc/public/class.dc.template.php > getOperator()
	*/
	protected static function getOperator($op)
	{
		switch (strtolower($op))
		{
			case 'or':
			case '||':
				return '||';
			case 'and':
			case '&&':
			default:
				return '&&';
		}
	}
	
	/**
	if an element is selected
	@param	attr	<b>array</b>	Attribute
	@param	content	<b>string</b>	Content
	@return	<b>string</b> PHP block
	*/
	public static function ContributeIfSelected($attr,$content)
	{
		$if = array();
		$operator = '&&';
		
		if (isset($attr['format']))
		{
			$if[] = '$_ctx->formaters->format === $_ctx->posts->post_format';
		}
		
		if (isset($attr['category']))
		{
			$if[] = '$_ctx->categories->cat_id == $_ctx->posts->cat_id';
		}
		
		if (isset($attr['mymeta']))
		{
			$if[] = 'isset($_ctx->posts->mymeta[$_ctx->mymeta->id])';
			$if[] = '$_ctx->mymetavalues->id == $_ctx->posts->mymeta[$_ctx->mymeta->id]';
		}
		
		if (!empty($if)) {
			return '<?php if('.implode(' '.$operator.' ',$if).') : ?>'.
				$content."\n".
				'<?php endif; ?>';
		} else {
			return $content;
		}
	}
	
	/**
	Formaters
	@param	attr	<b>array</b>	Attribute
	@param	content	<b>string</b>	Content
	@return	<b>string</b> PHP block
	*/
	public static function ContributeFormaters($attr,$content)
	{
		return
		'<?php '.
		# initialize for <tpl:LoopPosition>
		'$_ctx->formaters = $_ctx->contribute->formaters;'.
		'while ($_ctx->formaters->fetch()) : ?>'."\n".
		$content."\n".
		'<?php endwhile; ?>';
	}
	
	/**
	Format
	@param	attr	<b>array</b>	Attribute
	@param	content	<b>string</b>	Content
	@return	<b>string</b> PHP block
	*/
	public static function ContributeFormat($attr,$content)
	{
		return('<?php echo(html::escapeHTML($_ctx->formaters->format)); ?>');
	}
	
	/**
	Entry Excerpt
	@param	attr	<b>array</b>	Attribute
	@return	<b>string</b> PHP block
	*/
	public static function ContributeEntryExcerpt($attr)
	{
		return('<?php echo(html::escapeHTML($_ctx->posts->post_excerpt)); ?>');
	}
	
	/**
	Entry Content
	@param	attr	<b>array</b>	Attribute
	@return	<b>string</b> PHP block
	*/
	public static function ContributeEntryContent($attr)
	{
		return('<?php echo(html::escapeHTML($_ctx->posts->post_content)); ?>');
	}
	
	/**
	Category ID
	@param	attr	<b>array</b>	Attribute
	@return	<b>string</b> PHP block
	*/
	public static function ContributeCategoryID($attr)
	{
		return('<?php echo($_ctx->categories->cat_id); ?>');
	}
	
	/**
	Category spacer
	@param	attr	<b>array</b>	Attribute
	@return	<b>string</b> PHP block
	*/
	public static function ContributeCategorySpacer($attr)
	{
		$string = '&nbsp;&nbsp;';
		
		if (isset($attr['string'])) {$string = $attr['string'];}
		
		return('<?php echo(str_repeat(\''.$string.'\','.
			'$_ctx->categories->level-1)); ?>');
	}
	
	/**
	Filter to display only unselected tags
	@param	attr	<b>array</b>	Attribute
	@param	content	<b>string</b>	Content
	@return	<b>string</b> PHP block
	*/
	public static function ContributeEntryTagsFilter($attr,$content)
	{
		return
		'<?php '.
		'if (!in_array($_ctx->meta->meta_id,$_ctx->contribute->selected_tags)) : ?>'."\n".
		$content."\n".
		'<?php endif; ?>';
	}
	
	/**
	Loop on My Meta values
	@param	attr	<b>array</b>	Attribute
	@param	content	<b>string</b>	Content
	@return	<b>string</b> PHP block
	*/
	public static function ContributeEntryMyMeta($attr,$content)
	{
		return
		'<?php '.
		# initialize for <tpl:LoopPosition>
		'$_ctx->mymeta = contribute::getMyMeta($_ctx->contribute->mymeta);'.
		'while ($_ctx->mymeta->fetch()) : ?>'."\n".
		$content."\n".
		'<?php endwhile; ?>';
	}
	
	/**
	test on My Meta values
	@param	attr	<b>array</b>	Attribute
	@param	content	<b>string</b>	Content
	@return	<b>string</b> PHP block
	*/
	public static function ContributeEntryMyMetaIf($attr,$content)
	{
		$if = array();
		$operator = '&&';
		
		if (isset($attr['type']))
		{
			$if[] = '$_ctx->mymeta->type === \''.$attr['type'].'\'';
		}
		
		if (!empty($if)) {
			return '<?php if('.implode(' '.$operator.' ',$if).') : ?>'.
				$content."\n".
				'<?php endif; ?>';
		} else {
			return $content;
		}
	}
	
	/**
	My Meta ID
	@param	attr	<b>array</b>	Attribute
	@return	<b>string</b> PHP block
	*/
	public static function ContributeEntryMyMetaID($attr)
	{
		return('<?php echo($_ctx->mymeta->id); ?>');
	}
	
	/**
	My Meta Prompt
	@param	attr	<b>array</b>	Attribute
	@return	<b>string</b> PHP block
	*/
	public static function ContributeEntryMyMetaPrompt($attr)
	{
		return('<?php echo($_ctx->mymeta->prompt); ?>');
	}
	
	/**
	My Meta value
	@param	attr	<b>array</b>	Attribute
	@return	<b>string</b> PHP block
	*/
	public static function ContributeEntryMyMetaValue($attr)
	{
		return('<?php '.
		'if (isset($_ctx->posts->mymeta[$_ctx->mymeta->id])) :'.
		'echo($_ctx->posts->mymeta[$_ctx->mymeta->id]);'.
		'endif; ?>');
	}
	
	/**
	My Meta values
	@param	attr	<b>array</b>	Attribute
	@return	<b>string</b> PHP block
	*/
	public static function ContributeEntryMyMetaValues($attr,$content)
	{
		return
		'<?php '.
		# initialize for <tpl:LoopPosition>
		'$_ctx->mymetavalues = contribute::getMyMetaValues($_ctx->mymeta->values);'.
		'while ($_ctx->mymetavalues->fetch()) : ?>'."\n".
		$content."\n".
		'<?php endwhile; ?>';
	}
	
	/**
	My Meta values : ID
	@param	attr	<b>array</b>	Attribute
	@return	<b>string</b> PHP block
	*/
	public static function ContributeEntryMyMetaValuesID($attr)
	{
		return('<?php echo($_ctx->mymetavalues->id); ?>');
	}
	
	/**
	My Meta values : Description
	@param	attr	<b>array</b>	Attribute
	@return	<b>string</b> PHP block
	*/
	public static function ContributeEntryMyMetaValuesDescription($attr)
	{
		return('<?php echo($_ctx->mymetavalues->description); ?>');
	}
	
	/**
	Entry notes
	@param	attr	<b>array</b>	Attribute
	@return	<b>string</b> PHP block
	*/
	public static function ContributeEntryNotes($attr)
	{
		return('<?php echo(html::escapeHTML($_ctx->posts->post_notes)); ?>');
	}
}

$core->addBehavior('coreBlogGetPosts',array('contributeBehaviors',
	'coreBlogGetPosts'));

/**
@ingroup Contribute
@brief Behaviors
@see planet/public.php
*/
class contributeBehaviors
{
	public static function coreBlogGetPosts(&$rs)
	{
		if (!$GLOBALS['core']->blog->settings->contribute_active) {return;}
		$rs->extend('rsExtContributePosts');
	}
}

/**
@ingroup Contribute
@brief Extend posts

EntryAuthorDisplayName and EntryAuthorURL can't be modified

@see planet/public.php
*/
class rsExtContributePosts extends rsExtPost
{
	/**
	Get metadata of Contribute
	@param	rs	<b>recordset</b>	Recordset
	@param	info	<b>str</b>	Information
	@return	<b>string</b> Value
	*/
	public static function contributeInfo(&$rs,$info)
	{
		$rs = dcMeta::getMetaRecord($rs->core,$rs->post_meta,'contribute_'.$info);
		if (!$rs->isEmpty())
		{
			return $rs->meta_id;
		}
		# else
		return;
	}
	
	/**
	getAuthorLink
	@param	rs	<b>recordset</b>	Recordset
	@return	<b>string</b> String
	*/
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
			$author_format = 
				$GLOBALS['core']->blog->settings->contribute_author_format;
			
			if (empty($author_format)) {$author_format = '%s';}
			
			if (!empty($site))
			{
				$str = sprintf($author_format,'<a href="'.$site.'">'.$author.'</a>');
			}
			else
			{
				$str = sprintf($author_format,$author);
			}
			return $str;
		}
	}
	
	/**
	getAuthorCN
	@param	rs	<b>recordset</b>	Recordset
	@return	<b>string</b> String
	*/
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
			
			return sprintf($author_format,$author);
		}
	}
	
	/**
	getAuthorEmail
	@param	rs	<b>recordset</b>	Recordset
	@param	encoded	<b>boolean</b>	Return encoded email address ?
	@return	<b>string</b> String
	*/
	public static function getAuthorEmail(&$rs,$encoded=true)
	{
		$mail = $rs->contributeInfo('mail');
		if (empty($mail))
		{
			# default display
			return(parent::getAuthorEmail($rs,$encoded));
		} else {
			if ($encoded) {
				return strtr($mail,array('@'=>'%40','.'=>'%2e'));
			}
			# else
			return $email;
		}
	}
	
	/**
	getAuthorURL
	@param	rs	<b>recordset</b>	Recordset
	@return	<b>string</b> String
	*/
	public static function getAuthorURL(&$rs)
	{
		$mail = $rs->contributeInfo('site');
		if (empty($mail))
		{
			# default display
			return(parent::getAuthorEmail($rs,$encoded));
		} else {
			if ($encoded) {
				return strtr($mail,array('@'=>'%40','.'=>'%2e'));
			}
			# else
			return $email;
		}
	}
}
?>