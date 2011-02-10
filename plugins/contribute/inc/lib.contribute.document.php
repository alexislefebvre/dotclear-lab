<?php
# ***** BEGIN LICENSE BLOCK *****
#
# This file is part of Contribute, a plugin for Dotclear 2
# Copyright (C) 2008,2009,2010 Moe (http://gniark.net/)
#
# Contribute is free software; you can redistribute it and/or
# modify it under the terms of the GNU General Public License v2.0
# as published by the Free Software Foundation.
#
# Contribute is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program; if not, see <http://www.gnu.org/licenses/>.
#
# Icon (icon.png) is from Silk Icons :
# <http://www.famfamfam.com/lab/icons/silk/>
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
		# from /dotclear/inc/public/lib.urlhandlers.php
		# Spam trap
		if (!empty($_POST['f_mail']))
		{
			http::head(412,'Precondition Failed');
			header('Content-Type: text/plain');
			echo "So Long, and Thanks For All the Fish";
			exit;
		}
		# /from /dotclear/inc/public/lib.urlhandlers.php
		
		global $core;

		$core->blog->settings->addNamespace('contribute');
		$settings =& $core->blog->settings->contribute;

		if (!$settings->contribute_active)
		{
			self::p404();
			return;
		}
		
		$_ctx =& $GLOBALS['_ctx'];
		
		$_ctx->contribute = new ArrayObject();
		$_ctx->contribute->help = @base64_decode($settings->contribute_help);
		$_ctx->contribute->message = '';
		$_ctx->contribute->preview = false;
		$_ctx->contribute->form = true;
		$_ctx->contribute->choose_format = false;
		# selected tags
		$_ctx->contribute->selected_tags = array();
		
		# Compatibility test
		if (version_compare(DC_VERSION,'2.2-alpha1','>='))
		{
			$meta =& $core->meta;
		} else {
			# Metadata
			if ($core->plugins->moduleExists('metadata'))
			{
				$meta = new dcMeta($core);
			}
			else
			{
				$meta = false;
			}
		}
		
		# My Meta
		if ($core->plugins->moduleExists('mymeta')
			&& ($settings->contribute_allow_mymeta))
		{
			$mymeta_values = array();
		
			$_ctx->contribute->mymeta = new myMeta($core);
			
			if ($_ctx->contribute->mymeta->hasMeta())
			{
				$mymeta_values = @unserialize(@base64_decode(
					$settings->contribute_mymeta_values));
				
				if (!is_array($mymeta_values)) {$mymeta_values = array();}
				
				$mymeta = array();
				$mymeta_sections = array();
				
				if (version_compare(DC_VERSION,'2.2-alpha1','>='))
				{
					foreach ($_ctx->contribute->mymeta->getAll() as $meta_tmp)
					{
						# ignore sections
						if ($meta_tmp instanceof myMetaSection)
						{
							$mymeta[] = $meta_tmp->id;
							$mymeta_sections[] = $meta_tmp->id;
						}
						elseif (((bool) $meta_tmp->enabled)
							&& in_array($meta_tmp->id,$mymeta_values))
						{
							$mymeta[] = $meta_tmp->id;
						}
					}
					
					unset($meta_tmp,$mymeta_values);
				}
				else
				{
					foreach ($_ctx->contribute->mymeta->getAll() as $k => $v)
					{
						if (((bool) $v->enabled) && in_array($k,$mymeta_values))
						{
							$mymeta[] = $k;
						}
					}
					
					unset($mymeta_values);
				}
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
		
		# name, mail and site of the contributor
		
		$name = '';
		$mail = '';
		$site = '';
		
		$_ctx->comment_preview = new ArrayObject();
		$_ctx->comment_preview['name'] = '';
		$_ctx->comment_preview['mail'] = '';
		$_ctx->comment_preview['site'] = '';
		
		
		# inspired by contactMe/_public.php
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
				
				unset($default_post);
				
				# formats
				$formaters_combo = array();
				# Formaters combo
				foreach ($core->getFormaters() as $v)
				{
					$formaters_combo[] = array('format' => $v);
				}
				$_ctx->contribute->formaters =
						staticRecord::newFromArray($formaters_combo);
						
				unset($formaters_combo);
				
				# current date
				$post->post_dt = dt::str('%Y-%m-%d %T',null,
					$settings->blog_timezone);
				# remove URL
				$post->post_url = '';
				
				if (isset($_POST['post_title']))
				{
					$post->post_title = $_POST['post_title'];
				}
				
				# excerpt
				if (($settings->contribute_allow_excerpt)
					&& (isset($_POST['post_excerpt'])))
				{
					$post->post_excerpt = $_POST['post_excerpt'];
				}
				# content
				if (isset($_POST['post_content']))
				{
					$post->post_content = $_POST['post_content'];
				}
				
				# avoid Notice: Indirect modification of overloaded property
				# record::$post_excerpt has no effect in [this file]
				# on line [...]
				
				# filter to remove JavaScript
				$post_excerpt = contribute::HTMLfilter($post->post_excerpt);
				$post_excerpt_xhtml = contribute::HTMLfilter($post->post_excerpt_xhtml);
				$post_content = contribute::HTMLfilter($post->post_content);
				$post_content_xhtml = contribute::HTMLfilter($post->post_content_xhtml);
				
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
				if (($settings->contribute_allow_category)
					&& (isset($_POST['cat_id'])))
				{
					if (empty($_POST['cat_id']))
					{
						$post->cat_id = '';
						$post->cat_title = '';
						$post->cat_url = '';
					}
					# check category
					elseif (($_POST['cat_id'] != '')
						&& (!preg_match('/^[0-9]+$/',$_POST['cat_id'])))
					{
						throw new Exception(__('Invalid cat_id'));
					}
					else
					{
						$cat = $core->blog->getCategories(array(
							'start' => $_POST['cat_id'],
							'level' => 1,
							'cat_id' => $_POST['cat_id']
						));
						
						while ($cat->fetch())
						{
							# set category 
							$post->cat_id = $cat->cat_id;
							$post->cat_title = $cat->cat_title;
							$post->cat_url = $cat->cat_url;
							break;
						}
						
						unset($cat);
					}
				}
				else
				# no category
				{
					$post->cat_id = '';
					$post->cat_title = '';
					$post->cat_url = '';
				}
				# /category
				
				if ($meta !== false)
				{
					# tags
					if (($settings->contribute_allow_tags)
						&& (isset($_POST['post_tags'])))
					{
						$post_meta = unserialize($_ctx->posts->post_meta);
						
						# remove post tags
						unset($post_meta['tag']);
						
						# get all the existing tags
						$available_tags = contribute::getTags();
						
						# set post tags
						# from /dotclear/plugins/metadata/_admin.php
						foreach ($meta->splitMetaValues($_POST['post_tags'])
							as $k => $tag)
						{
						# /from /dotclear/plugins/metadata/_admin.php
							$tag = dcMeta::sanitizeMetaID($tag);
							
							if ($settings->contribute_allow_new_tags)
							{
								$post_meta['tag'][] = $tag;
								$_ctx->contribute->selected_tags[] = $tag;
							}
							else
							{
								# check that this tag already exists
								if (in_array($tag,$available_tags))
								{
									$post_meta['tag'][] = $tag;
									$_ctx->contribute->selected_tags[] = $tag;
								}
							}
						}
						
						unset($available_tags);
						
						$_ctx->posts->post_meta = serialize($post_meta);
						unset($post_meta);
						
					}
					
					# My Meta
					if ($_ctx->contribute->mymeta !== false)
					{
						foreach ($mymeta as $k => $v)
						{
							# ignore sections
							if (array_key_exists($k,$mymeta_sections)) {continue;}
							$post->mymeta[$v] = (isset($_POST['mymeta_'.$v])
								? $_POST['mymeta_'.$v] : '');
						}
					}
					# /My Meta
				}
				
				# notes
				if (($settings->contribute_allow_notes)
					&& (isset($_POST['post_notes'])))
				{
					$post->post_notes = contribute::HTMLfilter($_POST['post_notes']);
				}
				
				# author
				if (($meta !== false)
					&& ($settings->contribute_allow_author))
				{
					$post_meta = unserialize($_ctx->posts->post_meta);
					
					if (isset($_POST['c_name']) && (!empty($_POST['c_name'])))
					{
						$name = $_POST['c_name'];
						$post_meta['contribute_author'][] = $name;
						$_ctx->comment_preview['name'] = $name;
					}
					if (isset($_POST['c_mail']) && (!empty($_POST['c_mail'])))
					{
						$mail = $_POST['c_mail'];
						$post_meta['contribute_mail'][] = $mail;
						$_ctx->comment_preview['mail'] = $mail;
					}
					# inspired by dcBlog > getCommentCursor()
					if (isset($_POST['c_site']) && (!empty($_POST['c_site'])))
					{
						$site = $_POST['c_site'];
						
						if (!preg_match('|^http(s?)://|',$site))
						{
							$site = 'http://'.$site;
						}
						# /inspired by dcBlog > getCommentCursor()
						
						$post_meta['contribute_site'][] = $site;
						$_ctx->comment_preview['site'] = $site;
					}
					
					$_ctx->posts->post_meta = serialize($post_meta);
					unset($post_meta);
				}
				
				# check some inputs
				if ((isset($_POST['preview'])) || (isset($_POST['add'])))
				{
					# these fields can't be empty
					if (isset($_POST['post_content']) && empty($post->post_content))
					{
						throw new Exception(__('No entry content'));
					} elseif (isset($_POST['post_title']) && empty($post->post_title))
					{
						throw new Exception(__('No entry title'));
					}
					
					# if name and email address are required
					if (($settings->contribute_allow_author)
						&& ($settings->contribute_require_name_email))
					{
						if (empty($name))
						{
							$_ctx->contribute->preview = false;
							$_ctx->contribute->message = '';
							throw new Exception(__('You must provide an author name'));
						} elseif (!text::isEmail($mail))
						{
							$_ctx->contribute->preview = false;
							$_ctx->contribute->message = '';
							throw new Exception(
								__('You must provide a valid email address.'));
						}
					}
				}
				
				if (isset($_POST['preview']))
				{ 
					$_ctx->contribute->preview = true;
					$_ctx->contribute->message = 'preview';
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
					
					$post_status = 0;
					
					# antispam
					if ($settings->contribute_enable_antispam
						&& $core->plugins->moduleExists('antispam'))
					{
						$cur = $core->con->openCursor($core->prefix.'comment');
						
						$cur->comment_trackback = 0;
						$cur->comment_author = $name;
						$cur->comment_email = $mail;
						$cur->comment_site = $site;
						$cur->comment_ip = http::realIP();
						$cur->comment_content = $post->post_excerpt."\n".
							$post->post_content;
						$cur->post_id = $core->con->select(
						'SELECT MAX(post_id) '.
						'FROM '.$core->prefix.'post ')->f(0) + 1;
						$cur->comment_status = 0;
						
						$is_spam = contributeAntispam::isSpam($cur);
						
						if ($is_spam === true)
						{
							$post_status = -2;
							
							# if the auto deletion is enable, don't save the post and exit
							# empty() doesn't work with $cur->comment_content
							$comment_content = $cur->comment_content;
							if (empty($comment_content))
							{
								http::redirect($core->blog->url.
									$core->url->getBase('contribute').'/sent');
							}
							unset($comment_content,$cur);
						}	
					}
					# /antispam
					
					$cur = $core->con->openCursor($core->prefix.'post');
					
					$cur->user_id = $core->auth->userID();
					$cur->cat_id = ((empty($post->cat_id)) ? NULL : $post->cat_id);
					$cur->post_dt = $post->post_dt;
					$cur->post_status = $post_status;
					$cur->post_title = $post->post_title;
					$cur->post_format = $post->post_format;
					$cur->post_excerpt = $post->post_excerpt;
					$cur->post_content = $post->post_content;
					$cur->post_notes = $post->post_notes;
					$cur->post_lang = $core->auth->getInfo('user_lang');
					$cur->post_open_comment = (integer) $settings->allow_comments;
					$cur->post_open_tb = (integer) $settings->allow_trackbacks;
					
					unset($post_status);
					
					# --BEHAVIOR-- publicBeforePostCreate
					$core->callBehavior('publicBeforePostCreate',$cur);
					
					$post_id = $core->blog->addPost($cur);
					
					# l10n from $core->blog->addPost();
					__('You are not allowed to create an entry');
					
					# --BEHAVIOR-- publicAfterPostCreate
					$core->callBehavior('publicAfterPostCreate',$cur,$post_id);
					
					if ($meta !== false)
					{
						# inspired by planet/insert_feeds.php
						if (!empty($name))
						{
							$meta->setPostMeta($post_id,'contribute_author',$name);
						}
						if (!empty($mail))
						{
							$meta->setPostMeta($post_id,'contribute_mail',$mail);
						}
						if (!empty($site))
						{
							$meta->setPostMeta($post_id,'contribute_site',$site);
						}
						
						# tags
						$post_meta = unserialize($_ctx->posts->post_meta);
						if (($settings->contribute_allow_tags)
							&& (isset($post_meta['tag']))
							&& (is_array($post_meta['tag'])))
						{
							foreach ($post_meta['tag'] as $k => $tag)
							{
								# from /dotclear/plugins/metadata/_admin.php
								$meta->setPostMeta($post_id,'tag',$tag);
							}
							
							unset($post_meta);
						}
						# /tags
						
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
							'X-Mailer: Dotclear'
						);
						
						$subject = sprintf(__('New post submitted on %s'),
							$core->blog->name);
						
						$content = sprintf(__('Title: %s'),$post->post_title);
						$content .= "\n\n";
						
						if (!empty($name))
						{
							$content .= sprintf(__('Author: %s'),$name);
							$content .= "\n\n";
						}
						
						if (!empty($mail))
						{
							$headers[] = 'Reply-To: '.$mail;
							
							$content .= sprintf(__('Email address: %s'),$mail);
							$content .= "\n\n";
						}

						# IP address
						$content .= sprintf(__('IP Address: %s'),http::realIP());
						$content .= "\n\n";
						
						$params = array();
						$params['post_id'] = $post_id;
						
						$post = $core->blog->getPosts($params);

						$content .= sprintf(__('URL: %s'),$post->getURL());
						unset($post);
						$content .= "\n\n";
							
						$content .= sprintf(__('Edit this entry: %s'),DC_ADMIN_URL.
							((substr(DC_ADMIN_URL,-1) == '/') ? '' : '/').
							'post.php?id='.$post_id.'&switchblog='.$core->blog->id);
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
			catch (Exception $e)
			{
				$_ctx->form_error = $e->getMessage();
			}
		}
		
		$core->tpl->setPath($core->tpl->getPath(),
			dirname(__FILE__).'/../default-templates/');
		
		self::serveDocument('contribute.html','text/html');
	}
}

?>